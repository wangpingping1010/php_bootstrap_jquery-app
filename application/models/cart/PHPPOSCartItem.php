<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItemBase.php');

abstract class PHPPOSCartItem extends PHPPOSCartItemBase
{
	public $item_id;
	
	public $type;
	public $variation_id;
	public $variation_name;
	public $variation_choices;
	public $variation_choices_model;
	
	public $quantity_units = array();
	public $quantity_unit_id;
	public $quantity_unit_quantity;
	
	public function __construct(array $params = array())
	{		
		parent::__construct($params);
		if (!($this->type == 'sale' || $this->type == 'receiving'))
		{
		   trigger_error("A PHPPOSCartItem MUST have a type of sale or receiving", E_USER_ERROR);
		}
		
		//If we pass in a scan then we need to parse it and load up data into object
		if($this->scan)
		{
			$CI =& get_instance();			
			$CI->load->helper('items');
			if ($data = parse_item_scan_data($this->scan))
			{
				$this->item_id = $data['item_id'];
				$this->variation_id = $data['variation_id'];
				$this->variation_name = $data['variation_name'];
				$this->variation_choices = $data['variation_choices'];		
				$this->variation_choices_model = $data['variation_choices_model'];	
				
				$quantity_units = $CI->Item->get_quantity_units($this->item_id);
				
				foreach($quantity_units as $qu)
				{
					$this->quantity_units[$qu->id] = $qu->unit_name;
				}
							
			}
			elseif($CI->config->item('enable_scale') && $data = parse_scale_data($this->scan))
			{
				$this->item_id = $data['item_id'];
				
				if ($this->type == 'receiving')
				{
					$this->unit_price = $data['cost_price'];
					$this->quantity = $data['cost_quantity'];
				}
				elseif($this->type == 'sale')
				{
					$this->unit_price = $data['sell_price'];
					$this->quantity = $data['sell_quantity'];
				}
			}
			$this->load_item_defaults($params);
		}
	}
	
	private function load_item_defaults(array $params)
	{
		$CI =& get_instance();			
		
		$cur_item_info = $CI->Item->get_info($this->item_id);
		$cur_item_location_info = $CI->Item_location->get_info($this->item_id);
		
		//Load up this object with any properties in cur_item_info that also exist in this class or parent
		foreach($cur_item_info as $key=>$value)
		{
			//Only write to properties that exist and not already set
			if (property_exists($this,$key) && $this->$key === NULL && !isset($params[$key]))
			{
				$this->$key = $value;
			}
		}
		$CI->load->model('Item_variations');
		$CI->load->model('Item_variation_location');
		
		$cur_item_variation_info = $CI->Item_variations->get_info($this->variation_id);
		$cur_item_variation_location_info = $CI->Item_variation_location->get_info($this->variation_id);
		
		
		if(!isset($params['discount']))
		{
			$this->discount = 0;
		}
		
		if ($this->type == 'receiving')
		{	
			if ($cur_item_info->expire_days)
			{
				if(!isset($params['expire_date']))
				{
					$this->expire_date = date(get_date_format(), strtotime('+ '.$cur_item_info->expire_days. ' days'));			
				}
			}			
			//Selling price is just for receivings and unit_price before next line is the selling price
			
			if(!isset($params['selling_price']))
			{
					$this->selling_price = $this->unit_price;
			}
			
			if(!isset($params['location_selling_price']))
			{
					$this->location_selling_price = $cur_item_location_info->unit_price;
			}
			
			//Unit price is now the cost price....this is odd but for legacy reasons
			if(!isset($params['unit_price']))
			{
				if ($cur_item_variation_info && $cur_item_variation_info->cost_price)
				{
					$this->unit_price = $cur_item_variation_info->cost_price;
				}
				else
				{
					$this->unit_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
				}
			}
			
			if(!isset($params['cost_price_preview']))
			{
				$this->cost_price_preview = calculate_average_cost_price_preview($this->item_id, $this->variation_id, $this->unit_price, $this->quantity,$this->discount);
			}
		}
		elseif($this->type == 'sale')
		{
			if(!isset($params['max_discount_percent']))
			{
				$this->max_discount_percent = $cur_item_info->max_discount_percent;
			}
			
			if(!isset($params['max_edit_price']))
			{
				$this->max_edit_price = $cur_item_info->max_edit_price;
			}
			if(!isset($params['min_edit_price']))
			{
				$this->min_edit_price = $cur_item_info->min_edit_price;
			}
			
			$today =  strtotime(date('Y-m-d'));
			if(!isset($params['unit_price']))
			{
				$this->unit_price = $this->get_price_for_item();
			}
			if(!isset($params['cost_price']))
			{
				if (($cur_item_variation_info && $cur_item_variation_info->cost_price) || ($cur_item_variation_location_info&& $cur_item_variation_location_info->cost_price))
				{
					$this->cost_price = $cur_item_variation_location_info->cost_price ? $cur_item_variation_location_info->cost_price : $cur_item_variation_info->cost_price;
				}
				else
				{
					$this->cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
				}
			}			
			if(!isset($params['regular_price']))
			{
				if ($cur_item_variation_info && $cur_item_variation_info->unit_price)
				{
					$this->regular_price = $cur_item_variation_info->unit_price;
				}
				else
				{
					$this->regular_price = ($cur_item_location_info && $cur_item_location_info->unit_price) ? $cur_item_location_info->unit_price : $cur_item_info->unit_price;
				}
			}

			if(!isset($params['is_ebt_item']))
			{
				$this->is_ebt_item = $cur_item_info->is_ebt_item;
			}

			if(!isset($params['disable_loyalty']))
			{
				$this->disable_loyalty = $cur_item_info->disable_loyalty;
			}
			
			if (!isset($params['verify_age']))
			{
				$this->verify_age = $cur_item_info->verify_age; 			
			}

			if (!isset($params['required_age']))
			{
				$this->required_age = $cur_item_info->required_age; 			
			}			
		}
	
		if(!isset($params['taxable']))
		{
			$tax_info = $CI->Item_taxes_finder->get_info($this->item_id,$this->type);
			$this->taxable = !empty($tax_info);	
		}
		
		if (!isset($params['system_item']))
		{
			$this->system_item = $cur_item_info->system_item; 			
		}
		
		if (!isset($params['tag_ids']))
		{
			$CI->load->model('Tag');
			$this->tag_ids = $CI->Tag->get_tag_ids_for_item($this->item_id); 			
		}
		
	}
	
	public function validate()
	{
		$CI =& get_instance();
		$CI->load->model('Item');
		return $this->get_id() && !$CI->Item->get_info($this->get_id())->deleted;
	}
	
	public function get_id()
	{
		return $this->item_id;
	}
}