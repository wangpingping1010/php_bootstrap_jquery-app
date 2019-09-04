<?php
require_once ("Report.php");
class Detailed_transfers extends Report
{
	function __construct()
	{
		$this->lang->load('receivings');
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array('summary' => array(
		array('data'=>lang('reports_receiving_id'), 'align'=>'left'), 
		array('data'=>lang('receivings_transfer_from'), 'align'=> 'left'),
		array('data'=>lang('receivings_transfer_to'), 'align'=> 'left'),
		array('data'=>lang('reports_date'), 'align'=>'left'), 
		array('data'=>lang('reports_items_ordered'), 'align'=>'left'),
		array('data'=>lang('common_qty_received'), 'align'=>'left'), 
		array('data'=>lang('reports_received_by'), 'align'=>'left'), 
		array('data'=>lang('reports_supplied_by'), 'align'=>'left'),  
		array('data'=>lang('reports_subtotal'), 'align'=>'right'), 
		array('data'=>lang('reports_total'), 'align'=>'right'),  
		array('data'=>lang('common_tax'), 'align'=>'right'), 
		array('data'=>lang('reports_payment_type'), 'align'=>'left'), 
		array('data'=>lang('reports_comments'), 'align'=>'left')),
		'details' => $this->get_details_data_column_recv(),
		);		
		
		return $return;
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			
			$locations = array();
			
			foreach($this->Location->get_all()->result() as $location)
			{
				$locations[$location->location_id] = $location->name;
			}
			
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('receivings_transfer_to'),'dropdown_name' => 'transfer_to','dropdown_options' =>$locations,'dropdown_selected_value' => '');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$this->load->model('Category');
		$this->setupDefaultPagination();
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$summary_data = array();
		$details_data = array();
		$location_count = count(Report::get_selected_location_ids());
		
		
		foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			
			$transfer_info = '';
			if ($row['transfer_to_location_id'])
			{
				$this->lang->load('receivings');
				$transfer_info=' <strong style="color: red;">'.lang('receivings_transfer').'</strong>';
				
				if ($row['suspended'])
				{
					$transfer_info.=' '.anchor('receivings/switch_location_and_unsuspend/'.$row['location_id'].'/'.$row['receiving_id'], lang('reports_complete_pending_transfer'));
				}
				
			}
			
			$summary_data[$key] = array( array('data'=>anchor('receivings/receipt/'.$row['receiving_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], lang('common_edit').' '.$row['receiving_id'], array('target' => '_blank')).' ['.anchor('items/generate_barcodes_from_recv/'.$row['receiving_id'], lang('common_barcode_sheet'), array('target' => '_blank', 'class' => 'generate_barcodes_from_recv')).' / '.anchor('items/generate_barcodes_labels_from_recv/'.$row['receiving_id'], lang('common_barcode_labels'), 
			array('target' => '_blank')).' / '.anchor('reports/export_recv/'.$row['receiving_id'], lang('common_excel_export'), 
			array('target' => '_blank')).']'.$transfer_info, 'align'=> 'left', 'detail_id' => $row['receiving_id'] ), 
			array('data'=>$row['transfer_from'], 'align'=> 'left'),
			array('data'=>$row['transfer_to'], 'align'=> 'left'),
			array('data'=>date(get_date_format(), strtotime($row['receiving_date'])), 'align'=> 'left'), 
			array('data'=>to_quantity($row['items_purchased']), 'align'=> 'left'),
			array('data'=>to_quantity($row['items_received']), 'align'=> 'left'), 
			array('data'=>$row['employee_name'], 'align'=> 'left'), 
			array('data'=>$row['supplier_name'], 'align'=> 'left'), 
			array('data'=>to_currency($row['subtotal']), 'align'=> 'right'), 
			array('data'=>to_currency($row['total']), 'align'=> 'right'),
			array('data'=>to_currency($row['tax']), 'align'=> 'right'), 
			array('data'=>$row['payment_type'], 'align'=> 'left'), 
			array('data'=>$row['comment'], 'align'=> 'left'));
						
			if($export_excel == 1)				
			{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data[$key][] = array(
					array('data'=>$drow['name'], 'align'=> 'left'),
					array('data'=>$drow['product_id'], 'align'=> 'left'), 
					array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=> 'left'), 
					array('data'=>$drow['size'], 'align'=> 'left'), 
					array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left'),
					array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left'), 
					array('data'=>to_currency($drow['subtotal']), 'align'=> 'right'), 
					array('data'=>to_currency($drow['total']), 'align'=> 'right'),
					array('data'=>to_currency($drow['tax']), 'align'=> 'right'), 
					array('data'=>$drow['discount_percent'].'%', 'align'=> 'left'));
				}
			}
		}

		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" =>lang('reports_detailed_receivings_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
			"report_model" => get_class($this),
			
		);
				
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		
		return $data;
	}
	
	
	public function getData()
	{
		$transfer_from  = lang('common_none');
		
		if ($this->params['transfer_to'])
		{
			$transfer_from = $this->Location->get_info($this->params['transfer_to'])->name;
		}
		
		$this->db->select('locations.location_id,suspended,receivings.transfer_to_location_id, locations.name as transfer_from, '.$this->db->escape($transfer_from).' as transfer_to, receiving_id, date(receiving_time) as receiving_date, total_quantity_purchased as items_purchased,total_quantity_received as items_received, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(supplier.company_name, " (",people.first_name," ",people.last_name, ")") as supplier_name, subtotal, total, tax, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('receivings');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('people as people', 'people.person_id = supplier.person_id', 'left');
		if ($this->params['transfer_to'])
		{
			$this->db->where('transfer_to_location_id', $this->params['transfer_to']);
		}
				
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			
			$this->db->limit($this->report_limit);
			$this->db->offset(isset($this->params['offset']) ? $this->params['offset'] : 0);
			return $this->db->get()->result_array();
			
		}		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			
			$data=array();
			$data['summary']=array();
			$data['details']=array();
			
		foreach($this->db->get()->result_array() as $receiving_summary_row)
		{
			$data['summary'][$receiving_summary_row['receiving_id']] = $receiving_summary_row; 
		}
		$receiving_ids = array();
		
		foreach($data['summary'] as $receiving_row)
		{
			$receiving_ids[] = $receiving_row['receiving_id'];
		}
		$result = $this->get_report_details($receiving_ids,1);
		
		foreach($result as $receiving_item_row)
		{
			
			$data['details'][$receiving_item_row['receiving_id']][] = $receiving_item_row;
		}

		return $data;
		
		}
	}
	
	public function getTotalRows()
	{		
		$this->db->select("COUNT(receiving_id) as receiving_count");
		$this->db->from('receivings');
		
		if ($this->params['transfer_to'])
		{
			$this->db->where('transfer_to_location_id', $this->params['transfer_to']);			
		}
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		$ret = $this->db->get()->row_array();
		return $ret['receiving_count'];

	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(tax) as tax, sum(total) as total', false);
		$this->db->from('receivings');
		if ($this->params['transfer_to'])
		{
			$this->db->where('transfer_to_location_id', $this->params['transfer_to']);			
		}
		
		$this->receiving_time_where();
		$this->db->where('deleted', 0);
		return $this->db->get()->row_array();
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		return $this->get_report_details_recv($ids,$export_excel);
	}
}
?>