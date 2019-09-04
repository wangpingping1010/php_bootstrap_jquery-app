<?php
class Item_serial_number extends CI_Model
{

	function get_all($item_id)
	{
		$this->db->from('items_serial_numbers');
		$this->db->where('item_id',$item_id);
		$this->db->order_by('id');
		
		return $this->db->get();
	}
	
	function save($item_id, $serial_numbers, $serial_number_cost_prices = array(), $serial_number_prices = array())
	{
		$this->db->trans_start();
		
		if (empty($serial_number_prices) || count($serial_numbers) != count($serial_number_prices))
		{
			$serial_number_prices = array_fill(0,count($serial_numbers),'');
		}
		
		if (empty($serial_number_cost_prices) || count($serial_number_cost_prices) != count($serial_number_cost_prices))
		{
			$serial_number_cost_prices = array_fill(0,count($serial_number_cost_prices),'');
		}
		
		
		$this->delete($item_id);
		
		for($k=0;$k<count($serial_numbers);$k++)
		{
			$serial_number = $serial_numbers[$k];
			if ($serial_number != '')
			{
				$unit_price = $serial_number_prices[$k];
				$cost_price = $serial_number_cost_prices[$k];
				
				if($unit_price === '')
				{
					$unit_price = NULL;
				}
				
				if($cost_price === '')
				{
					$cost_price = NULL;
				}
				
				$this->add_serial($item_id, $serial_number,$cost_price, $unit_price);
			}
		}
				
		$this->db->trans_complete();
		
		return TRUE;
	}
	
	function get_price_for_serial($serial_number)
	{
		$this->db->from('items_serial_numbers');
		$this->db->where('serial_number',$serial_number);
		$row = $this->db->get()->row_array();
		
		if (isset($row['unit_price']) && $row['unit_price'] !== NULL)
		{
			return $row['unit_price'];
		}
		
		return FALSE;
	}
	
	function get_cost_price_for_serial($serial_number)
	{
		$this->db->from('items_serial_numbers');
		$this->db->where('serial_number',$serial_number);
		$row = $this->db->get()->row_array();
		
		if (isset($row['cost_price']) && $row['cost_price'] !== NULL)
		{
			return $row['cost_price'];
		}
		
		return FALSE;
	}
	
	/*
	Deletes one item
	*/
	function delete($item_id)
	{		
		return $this->db->delete('items_serial_numbers', array('item_id' => $item_id));
	}
	
	function delete_serial($item_id, $serial_number)
	{
		return $this->db->delete('items_serial_numbers', array('item_id' => $item_id, 'serial_number' => $serial_number));		
	}
	
	function add_serial($item_id, $serial_number, $cost_price = NULL, $unit_price = NULL)
	{
		return $this->db->insert('items_serial_numbers', array('item_id' => $item_id, 'serial_number' => $serial_number,'cost_price' => $cost_price, 'unit_price' => $unit_price));
	}
	
	function get_item_id($serial_number)
	{
		$this->db->from('items_serial_numbers');
		$this->db->where('serial_number',$serial_number);

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_id;
		}
		
		return FALSE;
	}
	
	function cleanup()
	{
		$item_serial_numbers_table = $this->db->dbprefix('items_serial_numbers');
		$items_table = $this->db->dbprefix('items');
		return $this->db->query("DELETE FROM $item_serial_numbers_table WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
	}	
}
?>