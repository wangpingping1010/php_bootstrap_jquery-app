<?php
class Appfile extends CI_Model 
{		
	function get_file_info($file_id)
	{
		$this->db->select('file_name,file_id,timestamp');
		$this->db->from('app_files');
		$this->db->where('file_id',$file_id);
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return FALSE;
		
	}
	
	function get($file_id)
	{
		$query = $this->db->get_where('app_files', array('file_id' => $file_id), 1);
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return "";
		
	}
	
	function get_file_timestamp($file_id)
	{
		$this->db->select('timestamp');
		$query = $this->db->get_where('app_files', array('file_id' => $file_id), 1);
		
		if($query->num_rows()==1)
		{
			return strtotime($query->row()->timestamp);
		}
		
		return "";
	}
	
	function get_file_extension($file_id)
	{
		$this->db->select('file_name');
		$query = $this->db->get_where('app_files', array('file_id' => $file_id), 1);
		
		if($query->num_rows()==1)
		{
			
			return '.'.pathinfo($query->row()->file_name, PATHINFO_EXTENSION);
		}
		
		return "";
	}
	
	function get_url_for_file_with_extension($file_id)
	{
		return app_file_url_with_extension($file_id);
	}
	
	function get_url_for_file($file_id)
	{
		return app_file_url($file_id);
	}
	
	function save($file_name,$file_raw_data, $file_expires = NULL, $file_id = false)
	{
		if ($file_expires !== NULL)
		{
			$cur_timezone = date_default_timezone_get();
			//We are doing this to make sure same timezone is used for expiration date
			date_default_timezone_set('America/New_York');
		
		 	$file_expires = date('Y-m-d H:i:s', strtotime($file_expires));
			date_default_timezone_set($cur_timezone);
		}
		
		$file_data=array(
		'file_name'=>$file_name,
		'file_data'=>$file_raw_data,
		'expires'=> $file_expires,
		'timestamp' =>  date('Y-m-d H:i:s'),
		);
		
		//if exists update
		if($this->db->where('file_id', $file_id)->count_all_results('app_files') == 1)
		{
			return $this->update($file_id, $file_data);
		}
		
		if ($this->db->insert('app_files', $file_data))
		{
			return $this->db->insert_id();
		}
		
		return false;
	}
	
	private function update($file_id, $file_data)
	{
		$this->db->where('file_id', $file_id);
		if ($this->db->update('app_files',$file_data))
		{
			return $file_id;
		}
		
		return false;
	}
		
	function delete($file_id)
	{
		return $this->db->delete('app_files', array('file_id' => $file_id)); 
	}
	
	function get_files_with_name($file_name,$limit = 20)
	{
		return $this->db->order_by('timestamp', 'DESC')->limit($limit)->get_where('app_files', array('file_name' => $file_name))->result_array();
	}
}

?>