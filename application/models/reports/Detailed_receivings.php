<?php
require_once ("Report.php");
class Detailed_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Receiving');
	}
	
	public function getDataColumns()
	{
		$return = array('summary' => array(
		array('data'=>lang('reports_receiving_id'), 'align'=>'left'), 
		array('data'=>lang('common_location'), 'align'=> 'left'),
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
		
		if ($this->config->item('track_shipping_cost_recv'))
		{
			$return['summary'][] = array('data'=>lang('common_shipping_cost'), 'align'=> 'right');			
		}
	  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$custom_field = $this->Receiving->get_custom_field($k);
			if($custom_field !== FALSE)
			{
				$return['summary'][] = array('data'=>$custom_field, 'align'=> 'right');
			}
		}
		
		if(isset($this->params['show_summary_only']) && $this->params['show_summary_only'])
		{
			return $return['summary'];
		}
		
		return $return;
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data['specific_input_name'] = 'supplier_id';
		$specific_entity_data['specific_input_label'] = lang('reports_supplier');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/supplier_search/1');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_show_summary_only'), 'checkbox_name' => 'show_summary_only');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
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
			array('target' => '_blank')).']'.$transfer_info.'<br />'.anchor('receivings/clone_receiving/'.$row['receiving_id'], lang('common_clone'), 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=> 'left', 'detail_id' => $row['receiving_id'] ), 
			array('data'=>$row['location_name'], 'align'=> 'left'),
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
			
			if ($this->config->item('track_shipping_cost_recv'))
			{
				$summary_data[$key][] = array('data'=>to_currency($row["shipping_cost"]), 'align'=>'right');					
			}
		  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
			{
				$custom_field = $this->Receiving->get_custom_field($k);
				if($custom_field !== FALSE)
				{
					if ($this->Receiving->get_custom_field($k,'type') == 'checkbox')
					{
						$format_function = 'boolean_as_string';
					}
					elseif($this->Receiving->get_custom_field($k,'type') == 'date')
					{
						$format_function = 'date_as_display_date';				
					}
					elseif($this->Receiving->get_custom_field($k,'type') == 'email')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Receiving->get_custom_field($k,'type') == 'url')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Receiving->get_custom_field($k,'type') == 'phone')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Receiving->get_custom_field($k,'type') == 'image')
					{
						$this->load->helper('url');
						$format_function = 'file_id_to_image_thumb';					
					}					
					elseif($this->Receiving->get_custom_field($k,'type') == 'file')
					{
						$this->load->helper('url');
						$format_function = 'file_id_to_download_link';					
					}					
					else
					{
						$format_function = 'strsame';
					}
					
					$summary_data[$key][] = array('data'=>$format_function($row["custom_field_${k}_value"]), 'align'=>'right');					
				}
			}
			
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

		
		if(isset($this->params['show_summary_only']) && $this->params['show_summary_only'])
		{
			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_detailed_receivings_report'),
				"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
				"headers" => $this->getDataColumns(),
				"data" => $summary_data,
				"summary_data" => $this->getSummaryData(),
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links()
			);
			
		}
		else
		{
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
		}
		return $data;
	}
	
	
	public function getData()
	{
		$this->db->select('locations.location_id,suspended,shipping_cost,receivings.custom_field_1_value,receivings.custom_field_2_value,receivings.custom_field_3_value,receivings.custom_field_4_value,receivings.custom_field_5_value,receivings.custom_field_6_value,receivings.custom_field_7_value,receivings.custom_field_8_value,receivings.custom_field_9_value,receivings.custom_field_10_value,receivings.transfer_to_location_id, locations.name as location_name, receiving_id, date(receiving_time) as receiving_date, total_quantity_purchased as items_purchased,total_quantity_received as items_received, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(supplier.company_name, " (",people.first_name," ",people.last_name, ")") as supplier_name, subtotal, total, tax, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('receivings');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('people as people', 'people.person_id = supplier.person_id', 'left');
		
		if ($this->params['receiving_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_id', $this->params['supplier_id']);			
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
		if ($this->params['receiving_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_id', $this->params['supplier_id']);			
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
		if ($this->params['receiving_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_id', $this->params['supplier_id']);			
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