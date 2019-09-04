<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Appointments extends REST_Controller {
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],
        'batch_post' => ['level' => 2, 'limit' => 20],
      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
				$this->load->model('Appointment');
    }
					
		private function _appointments_result_to_array($appointments)
		{
			$this->load->helper('date');
				$appointments_return = array(
					'id' => (int)$appointments->id,
					'location_id' => (int)$appointments->location_id,
					'start_time' => date_as_display_datetime($appointments->start_time),
					'end_time' => date_as_display_datetime($appointments->end_time),
					'customer_id' => $appointments->customer_id ? (int)$appointments->customer_id : NULL,
					'employee_id' => $appointments->employee_id ? (int)$appointments->employee_id : NULL,
					'appointments_type_id' => (int)$appointments->appointments_type_id,
					'notes' => $appointments->notes,
					
				);
				return $appointments_return;
		}
		
		function index_delete($appointments_id)
		{
  		$appointments = $this->Appointment->get_info($appointments_id);
  					
  		if ($appointments->id && !$appointments->deleted)
  		{
				$this->Appointment->delete($appointments->id);
		    $appointments_return = $this->_appointments_result_to_array($appointments);
				
				$this->response($appointments_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($appointments_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($appointments_id === NULL)
      {
      	$search = $this->input->get('search');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				$location_id = $this->input->get('location_id') ? $this->input->get('location_id') : 1;
				if ($search)
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$appointments = $this->Appointment->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Appointment->search_count_all($search, 0,10000,$location_id);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$appointments = $this->Appointment->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Appointment->count_all(0,$location_id);
				}
				
				$appointments_return = array();
				foreach($appointments as $appointments)
				{
						$appointments_return[] = $this->_appointments_result_to_array($appointments);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($appointments_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$appointments = $this->Appointment->get_info($appointments_id);
      							
      		if ($appointments->id)
      		{
      			$appointments_return = $this->_appointments_result_to_array($appointments);
						$this->response($appointments_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($appointments_id = NULL)
    {
			$appointments_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($appointments_id!== NULL)
			{
				$appointments_id = $this->_update_appointments($appointments_id,$appointments_request);
				$appointment_return = $this->_appointments_result_to_array($this->Appointment->get_info($appointments_id));
				$this->response($appointment_return, REST_Controller::HTTP_OK);
			}
			
			if ($appointment_id = $this->_create_appointments($appointments_request))
			{
				$appointment_return = $this->_appointments_result_to_array($this->Appointment->get_info($appointment_id));
				$this->response($appointment_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Appointment');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $appointments_request)
    			{
    				if ($id = $this->_create_appointments($appointments_request))
						{
							$appointments_return = $this->_appointments_result_to_array($this->Appointment->get_info($id));
						}
						else
						{
							$appointments_return = array('error' => TRUE);
						}
						$response['create'][] = $appointments_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $appointments_request)
    				{
							if ($this->_update_appointments($appointments_request['id'],$appointments_request))
							{
								$appointments_return = $this->_appointments_result_to_array($this->Appointment->get_info($appointments_request['id']));
							}
							else
							{
								$appointments_return = array('error' => TRUE);
							}
							$response['update'][] = $appointments_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $id)
    			{
							if ($id === NULL)
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$appointments = $this->Appointment->get_info($id);
										
							if ($appointments->id && !$appointments->deleted)
							{	
									$this->Appointment->delete($appointments->id);
									$appointments_return = $this->_appointments_result_to_array($appointments);
									$response['delete'][] = $appointments_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_appointments($appointments_request)
    {
    	 $this->load->model('Appointment');
 			date_default_timezone_set($this->Location->get_info_for_key('timezone',isset($appointments_request['location_id']) && $appointments_request['location_id'] ? $appointments_request['location_id'] : 1));



			$appointments_data=array(
				'location_id' => isset($appointments_request['location_id']) && $appointments_request['location_id'] ? $appointments_request['location_id'] : 1,
				'start_time' => isset($appointments_request['start_time']) && $appointments_request['start_time'] ? date('Y-m-d H:i:s',strtotime($appointments_request['start_time'])) : date('Y-m-d H:i:s'),
				'end_time' => isset($appointments_request['start_time']) && $appointments_request['start_time'] ? date('Y-m-d H:i:s',strtotime($appointments_request['start_time'])) : date('Y-m-d H:i:s'),
				'customer_id' => isset($appointments_request['customer_id']) && $appointments_request['customer_id'] ? $appointments_request['customer_id'] : NULL,
				'employee_id' => isset($appointments_request['employee_id']) && $appointments_request['employee_id'] ? $appointments_request['employee_id'] : NULL,
				'appointments_type_id' => isset($appointments_request['appointments_type_id']) && $appointments_request['appointments_type_id'] ? $appointments_request['appointments_type_id'] : NULL,
				'notes' => isset($appointments_request['notes']) && $appointments_request['notes'] ? $appointments_request['notes'] : '',
				
			);
			
			$this->Appointment->save($appointments_data);
			return $appointments_data['id'];
    }
    
    private function _update_appointments($appointments_id,$appointments_request)
    {
 			date_default_timezone_set($this->Location->get_info_for_key('timezone',isset($appointments_request['location_id']) && $appointments_request['location_id'] ? $appointments_request['location_id'] : 1));
			
  		$appointments = $this->Appointment->get_info($appointments_id);
						
			//Don't allow appointments primary key to change
			if (isset($appointments_request['id']))
			{
				unset($appointments_request['id']);
			}
			
			if (isset($appointments_request['start_time']))
			{
				$appointments_request['start_time'] = date('Y-m-d H:i:s',strtotime($appointments_request['start_time']));
			}
			
			if (isset($appointments_request['end_time']))
			{
				$appointments_request['end_time'] = date('Y-m-d H:i:s',strtotime($appointments_request['end_time']));
			}
			
			if ($this->Appointment->save($appointments_request,$appointments_id))
			{
				return $appointments_id;
			}
			
			return NULL;
    }
		
}