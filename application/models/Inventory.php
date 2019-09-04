<?php
class Inventory extends CI_Model 
{	
	function insert($inventory_data)
	{
		if(is_numeric($inventory_data['trans_inventory']))
		{
			if (!isset($inventory_data['trans_date']))
			{
				$inventory_data['trans_date'] = date('Y-m-d H:i:s');
			}
			return $this->db->insert('inventory',$inventory_data);
		}
		
		return TRUE;
	}
	
	function set_comment_for_inventory_log($trans_id, $comment)
	{
		$this->db->where('trans_id', $trans_id);
		$this->db->update('inventory',array('trans_comment' =>$comment));
	}
	
	function get_inventory_data_for_item($item_id, $limit, $offset, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//get all variations for an item
		$this->load->model('Item_variations');
		//include deleted
		$variations = $this->Item_variations->get_variations($item_id, true);
				
		$this->db->from('inventory');
		$this->db->where('trans_items',$item_id);
		$this->db->where('location_id',$location_id);
		$this->db->order_by("trans_id", "desc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$result = $this->db->get()->result_array();
		
		foreach($result as &$row)
		{
			if(isset($variations[$row['item_variation_id']]['attributes']))
			{
				if ($variations[$row['item_variation_id']]['name'])
				{
					$row['variation'] = $variations[$row['item_variation_id']]['name'];
				}
				else
				{
					$row['variation'] = implode(', ', array_column($variations[$row['item_variation_id']]['attributes'], 'label'));
				}
			}
			else
			{
				$row['variation']= '';
			}
		}
		
		return $result;
	}
	
	function count_all($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory');
		$this->db->where('trans_items',$item_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->count_all_results();
	}
	
	function get_count_by_status($status, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory_counts');
		$this->db->where('status',$status);
		$this->db->where('location_id',$location_id);
		
		return $this->db->count_all_results();
	}
	
	function get_counts_by_status($status, $limit = 100, $offset = 0, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory_counts');
		$this->db->where('status',$status);
		$this->db->where('location_id',$location_id);
		$this->db->order_by("count_date", "desc");
		$this->db->limit($limit);
		$this->db->offset($offset);

		return $this->db->get();		
	}
	
	function get_number_of_items_counted($inventory_counts_id)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id',$inventory_counts_id);
		return $this->db->count_all_results();
	}
	
	function create_count($date = false, $status = false, $comment = false, $employee_id = false, $location_id = false)
	{
		if (!$date)
		{
			$date = date('Y-m-d H:i:s');	
		}
		
		if (!$status)
		{
			$status = 'open';
		}
		
		if (!$comment)
		{
			$comment = '';
		}
		
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
		
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		
		$count_data = array(
		'count_date'=>$date,
		'employee_id' => $employee_id,
		'location_id'=> $location_id,
		'status' => $status,
		'comment' => $comment,
		);


		if($this->db->insert('inventory_counts', $count_data))
		{
			return $this->db->insert_id();
		}
		
		return FALSE;
	}
	
	function validate_count($count_id)
	{
		$counted_items = $this->get_items_counted($count_id, NULL, 0);
		
		foreach ($counted_items as $item)
		{
			if(isset($item["variations"]))
			{
				if(count($item["variations"]) > 0 && $item["item_variation_id"] == NULL)
				{
					return false;
				}
			}
		}
		
		return true;
	}
		
	function get_count_info($count_id)
	{
		$this->db->from('inventory_counts');
		$this->db->where('id',$count_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return NULL;
	}
	
	function get_count_info_from_count_item_id($count_item_id)
	{
		$this->db->select('inventory_counts.*,inventory_counts_items.item_id');
		$this->db->from('inventory_counts');
		$this->db->join('inventory_counts_items','inventory_counts.id=inventory_counts_items.inventory_counts_id');
		$this->db->where('inventory_counts_items.id',$count_item_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return NULL;
	}
	
	function set_count($count_id, $status = false, $comment = false)
	{
		$data = array();
		
		if ($status !== FALSE)
		{
			$data['status'] = $status;
		}
		
		if ($comment !== FALSE)
		{
			$data['comment'] = $comment;
		}
		
		$this->db->where('id', $count_id);
		return $this->db->update('inventory_counts', $data);
		
	}
	
	function update_count_item($count_item_id, $item_variation_id = false, $count = false, $comment = false, $actual_quantity = FALSE)
	{
		$this->db->where('id', $count_item_id);
		
		if ($item_variation_id)
		{
			if($item_variation_id == -1)
			{
				$update_data['item_variation_id'] = NULL;
			}
			else
			{
				$update_data['item_variation_id'] = $item_variation_id;
			}
		}
		
		if ($count)
		{
			$update_data['count'] = $count;
		}
		
		if ($comment)
		{
			$update_data['comment'] = $comment;
		}
		
		if ($actual_quantity)
		{
			$update_data['actual_quantity'] = $actual_quantity;
		}
		
		
		if ($this->db->update('inventory_counts_items',$update_data))
		{
			return $count_item_id;
		}
		
	}
	
	function set_count_item($count_id, $item_id, $item_variation_id = false, $count = false, $actual_quantity = false, $comment = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		
		if($item_variation_id)
		{
			$this->db->where('item_variation_id',$item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id',null);
		}
		
		$this->db->where('inventory_counts_id', $count_id);
		$query = $this->db->get();

		$exists = ($query->num_rows()==1);	
		
		$data = array(
			'inventory_counts_id' => $count_id,
			'item_id' => $item_id,
			'item_variation_id' => $item_variation_id ? $item_variation_id : null,
		);
		
		if ($count !== FALSE)
		{
			$data['count'] = $count;			
		}
		
		if ($actual_quantity !== FALSE)
		{
			$data['actual_quantity'] = $actual_quantity;			
		}
		
		if ($comment !== FALSE)
		{
			$data['comment'] = $comment;
		}
		
		
		if ($exists)
		{
			$mode = $this->session->userdata('count_mode') ? $this->session->userdata('count_mode') : 'scan_and_set';
			
			if ($mode == 'scan_and_set')
			{
				$data['actual_quantity'] = $this->get_count_item_actual_quantity($count_id, $item_id, $item_variation_id);
				
				if ($comment === FALSE)
				{
					$data['comment'] = $this->get_count_item_actual_comment($count_id, $item_id, $item_variation_id);
				}
				
				//Remove previous item
				$this->db->where('item_id', $item_id);
				if($item_variation_id)
				{
					$this->db->where('item_variation_id',$item_variation_id);
				}
				else
				{
					$this->db->where('item_variation_id',null);
				}
				$this->db->where('inventory_counts_id', $count_id);
				$this->db->delete('inventory_counts_items');
				
				return $this->db->insert('inventory_counts_items', $data);					
			}
			
			$this->db->where('item_id',$item_id);
			if($item_variation_id)
			{
				$this->db->where('item_variation_id',$item_variation_id);
			}
			else
			{
				$this->db->where('item_variation_id',null);
			}
			
			$this->db->where('inventory_counts_id', $count_id);
			return $this->db->update('inventory_counts_items', $data);
		}
		
		return $this->db->insert('inventory_counts_items', $data);	
	}
	
	function delete_count_item($count_id, $item_id, $item_variation_id = false)
	{		
		if(empty($item_variation_id))
		{
			$item_variation_id = null;
		}
		
		$this->db->delete('inventory_counts_items', array('inventory_counts_id' => $count_id, 'item_id' => $item_id, 'item_variation_id' => $item_variation_id));
	}
	
	function get_count_item_current_quantity($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->count;
		}
		
		return 0;
	}
	
	function get_count_item_actual_quantity($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->actual_quantity;
		}
		
		return NULL;
	}
	
	function get_count_item_actual_comment($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->comment;
		}
		
		return NULL;
	}
	
	function get_counted_variations_for_item($count_id, $item_id)
	{
		$this->db->select('item_variation_id');
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id', $count_id);
		$this->db->where('item_id', $item_id);
		
		$result = $this->db->get()->result_array();
				
		return $result;
	}
	
	function get_items_not_counted_count($count_id,$in_stock = 0)
	{
		$count_info = $this->get_count_info($count_id);
		
		$this->db->from('items');
		$this->db->join('item_variations', 'item_variations.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where('items.item_id NOT IN(SELECT COALESCE(item_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->or_where('item_variations.id NOT IN(SELECT COALESCE(item_variation_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->group_end();
		$this->db->where('items.deleted',0);
		$this->db->where('items.is_service',0);
		
		if ($in_stock)
		{
			$location_id = $count_info->location_id;
			$location_item_variations_quantity_col =$this->db->dbprefix('location_item_variations').'.quantity';
			$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';

			$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';

			$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id IN('.$location_id.')', 'left');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN('.$location_id.')', 'left');
		
			$this->db->where($quantity_query.' > 0');
		}
		$this->db->order_by('category_id');
		
		return $this->db->count_all_results();
	}
	
	function get_items_not_counted($count_id,$category_ids,$in_stock = 0,$limit = 100, $offset = 0)
	{
		$count_info = $this->get_count_info($count_id);
		
		$this->db->select('items.*,item_variations.id as item_variation_id');
		$this->db->from('items');
		$this->db->join('item_variations', 'item_variations.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where('items.item_id NOT IN(SELECT COALESCE(item_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->or_where('item_variations.id NOT IN(SELECT COALESCE(item_variation_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->group_end();
		$this->db->where('items.deleted',0);
		$this->db->where('items.is_service',0);
		
		if ($category_ids)
		{
			$this->db->where_in('items.category_id',$category_ids);
		}
		
		if ($in_stock)
		{
			$location_id = $count_info->location_id;
			$location_item_variations_quantity_col =$this->db->dbprefix('location_item_variations').'.quantity';
			$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';

			$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';

			$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id IN('.$location_id.')', 'left');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN('.$location_id.')', 'left');
		
			$this->db->where($quantity_query.' > 0');
		}
		
		
		if ($limit !== NULL)
		{
			$this->db->limit($limit);
		}
		
		if ($offset !== NULL)
		{
			$this->db->offset($offset);
		}
		
		return $this->db->get()->result_array();
	}
	
	
	function get_items_counted($count_id,$limit = 100, $offset = 0)
	{
		$this->load->model('Item_variations');
			
		$this->db->select('items.*, inventory_counts_items.*, item_variations.id as item_variation_id, inventory_counts.location_id, inventory_counts.employee_id, categories.name as category');
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id', $count_id);
		$this->db->join('inventory_counts', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'items.item_id = inventory_counts_items.item_id');
		$this->db->join('item_variations', 'item_variations.id = inventory_counts_items.item_variation_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		
		if ($limit !== NULL)
		{
			$this->db->limit($limit);
		}
		
		if ($offset !== NULL)
		{
			$this->db->offset($offset);
		}
		
		$this->db->order_by('inventory_counts_items.id', 'DESC');
		$result = $this->db->get()->result_array();
		
		
		$variation_ids = array();
		$item_ids = array();
		
		foreach($result as $row)
		{
			$item_ids[] = $row['item_id'];
			$item_variation_ids[] = $row['item_variation_id'];
		}
		
		$variations = $this->Item_variations->get_variations(array_unique($item_ids));
		
		foreach($result as &$row)
		{
			if(isset($variations[$row['item_id']]))
			{
				foreach($variations[$row['item_id']] as $item_variation_id => $variation)
				{
					if(!in_array($item_variation_id, $item_variation_ids) || $item_variation_id == $row['item_variation_id'])
					{
						if ($variation['name'])
						{
							$row['variations'][$item_variation_id] = $variation['name'];
						}
						else
						{
							$row['variations'][$item_variation_id] = implode(', ',array_column($variation['attributes'], 'label'));
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function delete_inventory_count($count_id)
	{
		$this->db->delete('inventory_counts_items', array('inventory_counts_id' => $count_id));
		$this->db->delete('inventory_counts', array('id' => $count_id));
		
		return TRUE;
	}
	
	function update_inventory_from_count($count_id)
	{
		$this->load->model('Item_location');
		$this->load->model('Item_variation_location');
		
		foreach($this->get_items_counted($count_id, NULL,NULL) as $count_item)
		{
			$current_inventory_value = $count_item['actual_quantity'];
			$counted_inventory_value = $count_item['count'];
	
			if ($current_inventory_value != $counted_inventory_value)
			{
				$inv_data = array
				(
					'trans_date'=>date('Y-m-d H:i:s'),
					'trans_items'=>$count_item['item_id'],
					'item_variation_id'=>$count_item['item_variation_id'],
					'trans_user'=>$count_item['employee_id'],
					'trans_comment'=>lang('items_inventory_count_update'),
					'trans_inventory'=>$counted_inventory_value - $current_inventory_value,
					'location_id' => $count_item['location_id'],
				);
				
				
				if(isset($count_item['item_variation_id']) && $count_item['item_variation_id'])
				{
					$cur_quantity = $this->Item_variation_location->get_location_quantity($count_item['item_variation_id'],$count_item['location_id']);
					$this->Item_variation_location->save_quantity($cur_quantity + ($counted_inventory_value - $current_inventory_value), $count_item['item_variation_id'],$count_item['location_id']);
				} 
				else
				{
					$cur_quantity = $this->Item_location->get_location_quantity($count_item['item_id'],$count_item['location_id']);
					$this->Item_location->save_quantity($cur_quantity + ($counted_inventory_value - $current_inventory_value), $count_item['item_id'],$count_item['location_id']);
				}
				$inv_data['trans_current_quantity'] = $cur_quantity + ($counted_inventory_value - $current_inventory_value);
				
				$this->insert($inv_data);
				
			}
		}
	}
}

?>