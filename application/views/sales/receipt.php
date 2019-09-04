<?php $this->load->view("partial/header"); ?>

<?php
	$this->load->helper('sale');
	$return_policy = ($loc_return_policy = $this->Location->get_info_for_key('return_policy', isset($override_location_id) ? $override_location_id : FALSE)) ? $loc_return_policy : $this->config->item('return_policy');
	$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
	$website = ($website = $this->Location->get_info_for_key('website', isset($override_location_id) ? $override_location_id : FALSE)) ? $website : $this->config->item('website');
	$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
	
	$is_integrated_credit_sale = is_sale_integrated_cc_processing($cart);
	$is_sale_integrated_ebt_sale = is_sale_integrated_ebt_sale($cart);
	$is_credit_card_sale = is_credit_card_sale($cart);
	$is_debit_card_sale = is_debit_card_sale($cart);
	
	$signature_needed = ($this->config->item('enable_tips') && ($is_credit_card_sale || $is_debit_card_sale)) || $this->config->item('capture_sig_for_all_payments') || (($is_credit_card_sale && !$is_integrated_credit_sale) ||  is_store_account_sale($cart));
	$item_custom_fields_to_display = array();
	$sale_custom_fields_to_display = array();
	$item_kit_custom_fields_to_display = array();
	$customer_custom_fields_to_display = array();
	$employee_custom_fields_to_display = array();
	
	
 for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
 {
	 $item_custom_field = $this->Item->get_custom_field($k,'show_on_receipt');
	 $sale_custom_field = $this->Sale->get_custom_field($k,'show_on_receipt');
	 $item_kit_custom_field = $this->Item_kit->get_custom_field($k,'show_on_receipt');
	 $customer_custom_field = $this->Customer->get_custom_field($k,'show_on_receipt');
	 $employee_custom_field = $this->Employee->get_custom_field($k,'show_on_receipt');
	 
	 if ($item_custom_field)
	 {
	 	$item_custom_fields_to_display[] = $k;
	 }

	 if ($sale_custom_field)
	 {
	 	$sale_custom_fields_to_display[] = $k;
	 }
	 
	 if ($item_kit_custom_field)
	 {
 	 	$item_kit_custom_fields_to_display[] = $k;
	 }
	 
	 if ($customer_custom_field)
	 {
  	 	$customer_custom_fields_to_display[] = $k;
	 }
	 
	 if ($employee_custom_field)
	 {
	 		$employee_custom_fields_to_display[] = $k;
	 }
 }
	
	//Check for EMV signature for non pin verified
	if (!$signature_needed && $is_integrated_credit_sale)
	{
		foreach($payments as $payment_id=>$payment)
		{
			if ($payment->cvm != 'PIN VERIFIED')
			{
				$signature_needed = TRUE;
				break;
			}
		}
	}
	
	if (isset($error_message))
	{
		echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
		exit;
	}
?>

<div class="manage_buttons hidden-print">
	<div class="row">
		<div class="col-md-6">
			<div class="hidden-print search no-left-border">
				<ul class="list-inline print-buttons">
					<li></li>
					
						<?php
						if ((empty($deleted) || (!$deleted))) { ?>
						<li>
							<?php 
							 if ($sale_id_raw != lang('sales_test_mode_transaction','',array(),TRUE) && !$store_account_payment && !$is_purchase_points && !$is_ecommerce && $this->Employee->has_module_action_permission('sales', 'edit_sale', $this->Employee->get_logged_in_employee_info()->person_id)){

						   		$edit_sale_url = (isset($sale_type) && ($sale_type == ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway','',array(),TRUE)) || $sale_type == lang('common_estimate','',array(),TRUE))) ? 'unsuspend' : 'change_sale';
								echo form_open("sales/$edit_sale_url/".$sale_id_raw,array('id'=>'sales_change_form')); ?>
								<button class="btn btn-primary btn-lg hidden-print" id="edit_sale"> <?php echo lang('sales_edit','',array(),TRUE); ?> </button>

							<?php }	?>
							</form>		
						</li>
					<?php } ?>
						
					<?php 
					if ($sale_id_raw != lang('sales_test_mode_transaction','',array(),TRUE)){
					?>	
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="fufillment_sheet_button" onclick="window.open('<?php echo site_url("sales/fulfillment/$sale_id_raw"); ?>', 'blank');" > <?php echo lang('sales_fulfillment_sheet','',array(),TRUE); ?></button>
						</li>
					<?php } ?>
					
					<li>
						<button class="btn btn-primary btn-lg hidden-print gift_receipt" id="gift_receipt_button" onclick="toggle_gift_receipt()" > <?php echo lang('sales_gift_receipt','',array(),TRUE); ?> </button>
					</li>
						<?php if ($sale_id_raw != lang('sales_test_mode_transaction','',array(),TRUE) && !empty($customer_email)) { ?>
							<li>
									<?php echo anchor('sales/email_receipt/'.$sale_id_raw, lang('common_email_receipt','',array(),TRUE), array('id' => 'email_receipt','class' => 'btn btn-primary btn-lg hidden-print'));?>
							</li>
						<?php }?>
					
					<?php if ($sale_id_raw != lang('sales_test_mode_transaction','',array(),TRUE)) { ?>
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="fufillment_sheet_button" onclick="window.open('<?php echo site_url("sales/create_po/$sale_id_raw"); ?>', 'blank');" > <?php echo lang('common_create_po','',array(),TRUE); ?></button>
						</li>
						<?php } ?>			
						
						<?php if ($sale_id_raw != lang('sales_test_mode_transaction','',array(),TRUE)) { ?>
							<li>
									<?php echo anchor('sales/download_receipt/'.$sale_id_raw, '<span class="ion-arrow-down-a"></span>', array('id' => 'download_pdf','class' => 'btn btn-primary btn-lg hidden-print'));?>
							</li>
							<?php } ?>					
								
				</ul>
			</div>
		</div>
		<div class="col-md-6">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<ul class="list-inline print-buttons">
						<li>
							<?php
							echo form_checkbox(array(
								'name'        => 'print_duplicate_receipt',
								'id'          => 'print_duplicate_receipt',
								'value'       => '1',
							)).'&nbsp;<label for="print_duplicate_receipt"><span></span>'.lang('sales_duplicate_receipt','',array(),TRUE).'</label>';
								?>		
						</li>
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="print_button" onclick="print_receipt()" > <?php echo lang('common_print','',array(),TRUE); ?> </button>		
						</li>
						<li>
							<?php echo anchor_popup(site_url('sales/open_drawer'), '<i class="ion-android-open"></i> '.lang('common_pop_open_cash_drawer','',array(),TRUE),array('class'=>'btn btn-primary btn-lg hidden-print', 'target' => '_blank')); ?>
						</li>
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="new_sale_button_1" onclick="window.location='<?php echo site_url('sales'); ?>'" > <?php echo lang('sales_new_sale','',array(),TRUE); ?> </button>	
						</li>
					</ul>
				</div>
			</div>				
		</div>
	</div>
</div>
<div class="row manage-table receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
			    <div class="row">
			        <!-- from address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-address" style="margin-bottom:2px;">
			                <?php if($company_logo) {?>
			                	<li class="invoice-logo">
									<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
			                	</li>
			                <?php } ?>
			                <li class="company-title"><?php echo H($company); ?></li>
							
							<?php if ($this->Location->count_all() > 1) { ?>
								<li><?php echo H($this->Location->get_info_for_key('name', isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
							<?php } ?>
							
			                <li class="nl2br"><?php echo H($this->Location->get_info_for_key('address', isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
			                <li><?php echo H($this->Location->get_info_for_key('phone', isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
			                <?php if($website) { ?>
											<li><?php echo H($website);?></li>
											<?php } ?>
			            </ul>
			        </div>
			        <!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-detail" style="margin-bottom:2px;">
							<li>
								<?php if ($receipt_title) {?>
								 <?php echo H($receipt_title); ?><?php echo ($total) < 0 ? ' ('.lang('sales_return','',array(),TRUE).')': '';?>
								 <br>
								 <?php } ?>
								 <strong><?php echo H($transaction_time) ?></strong>
							</li>
			            <li><span><?php echo lang('common_sale_id','',array(),TRUE).":"; ?></span><?php echo H($sale_id); ?><?php if($return_sale_id){echo ' ('.lang('sales_return').' '.($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS').' '.$return_sale_id.')';}?></li>
							<?php if (isset($deleted) && $deleted) {?>
			            	<li><span class="text-danger" style="color: #df6c6e;"><strong><?php echo lang('sales_deleted_voided','',array(),TRUE); ?></strong></span></li>
							<?php } ?>
							<?php if (isset($sale_type)) { ?>
								<li><?php echo H($sale_type); ?></li>
							<?php } ?>
							
							<?php if ($is_ecommerce) { ?>
								<li><?php echo lang('common_ecommerce','',array(),TRUE); ?></li>
							<?php } ?>
							
							<?php
							if ($this->Register->count_all(isset($override_location_id) ? $override_location_id : FALSE) > 1 && $register_name)
							{
							?>
								<li><span><?php echo lang('common_register_name','',array(),TRUE).':'; ?></span><?php echo H($register_name); ?></li>		
							<?php
							}
							?>				
							
							<?php
							if ($tier && !$this->config->item('hide_tier_on_receipt'))
							{
							?>
								<li><span><?php echo $this->config->item('override_tier_name') ? $this->config->item('override_tier_name') : lang('common_tier_name','',array(),TRUE).':'; ?></span><?php echo H($tier); ?></li>		
							<?php
							}
							?>
							
							<?php if (!$this->config->item('remove_employee_from_receipt')) { ?>
							<li><span><?php echo lang('common_employee','',array(),TRUE).":"; ?></span><?php echo H($employee); ?></li>
							<?php
							foreach($employee_custom_fields_to_display as $custom_field_id)
							{
							?>
									<?php 
									
										$employee_info = $this->Employee->get_info($sold_by_employee_id);
										
										if ($employee_info->{"custom_field_${custom_field_id}_value"})
										{
										?>
                  	<div class="invoice-desc">
										<?php

										if ($this->Employee->get_custom_field($custom_field_id,'type') == 'checkbox')
										{
											$format_function = 'boolean_as_string';
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'date')
										{
											$format_function = 'date_as_display_date';				
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'email')
										{
											$format_function = 'strsame';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'url')
										{
											$format_function = 'strsame';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'phone')
										{
											$format_function = 'strsame';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'image')
										{
											$this->load->helper('url');
											$format_function = 'file_id_to_image_thumb_right';					
										}
										elseif($this->Employee->get_custom_field($custom_field_id,'type') == 'file')
										{
											$this->load->helper('url');
											$format_function = 'file_id_to_download_link';					
										}
										else
										{
											$format_function = 'strsame';
										}
										
										echo '<li><span>'.lang('common_employee','',array(),TRUE).' '.($this->Employee->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Employee->get_custom_field($custom_field_id,'name').':').'</span> '.$format_function($employee_info->{"custom_field_${custom_field_id}_value"}).'</li>';
										?>
									</div>
									<?php
								}
							}
							?>
							<?php } ?>
							<?php 
							if(H($this->Location->get_info_for_key('enable_credit_card_processing',isset($override_location_id) ? $override_location_id : FALSE)))
							{
								if (!$this->config->item('hide_merchant_id_from_receipt'))
								{
									echo '<li id="merchant_id"><span>'.lang('common_merchant_id','',array(),TRUE).':</span> '.H($this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE)).'</li>';
								}
							}
							?>
			            </ul>
			        </div>
			        <!-- to address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
						
			          <?php if(isset($customer)) { ?>
				        <ul class="list-unstyled invoice-address invoiceto" style="margin-bottom:2px;">
								
								<?php if (!$this->config->item('remove_customer_name_from_receipt')) { ?>
									<li class="invoice-to"><?php echo lang('sales_invoice_to','',array(),TRUE);?>:</li>
									<li><?php echo lang('common_customer','',array(),TRUE).": ".H($customer); ?></li>
									
								<?php } ?>

								<?php if (!$this->config->item('remove_customer_company_from_receipt')) { ?>
									<?php if(!empty($customer_company)) { ?><li><?php echo lang('common_company','',array(),TRUE).": ".H($customer_company); ?></li><?php } ?>
								<?php } ?>
									
									<?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
										<?php if(!empty($customer_address_1) || !empty($customer_address_2)){ ?><li><?php echo lang('common_address','',array(),TRUE); ?> : <?php echo H($customer_address_1. ' '.$customer_address_2); ?></li><?php } ?>
										<?php if (!empty($customer_city)) { echo '<li>'.H($customer_city.' '.$customer_state.', '.$customer_zip).'</li>';} ?>
										<?php if (!empty($customer_country)) { echo '<li>'.H($customer_country).'</li>';} ?>			
										<?php if(!empty($customer_phone)){ ?><li><?php echo lang('common_phone_number','',array(),TRUE); ?> : <?php echo H($customer_phone); ?></li><?php } ?>
										<?php if(!empty($customer_email)){ ?><li><?php echo lang('common_email','',array(),TRUE); ?> : <?php echo H($customer_email); ?></li><?php } ?>
									<?php } ?>
				
									<?php
									foreach($customer_custom_fields_to_display as $custom_field_id)
									{
									?>
											<?php 
												$customer_info = $this->Customer->get_info($customer_id);
												
												if ($customer_info->{"custom_field_${custom_field_id}_value"})
												{
												?>
	                    	<div class="invoice-desc">
												<?php

												if ($this->Customer->get_custom_field($custom_field_id,'type') == 'checkbox')
												{
													$format_function = 'boolean_as_string';
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'date')
												{
													$format_function = 'date_as_display_date';				
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'email')
												{
													$format_function = 'strsame';					
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'url')
												{
													$format_function = 'strsame';					
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'phone')
												{
													$format_function = 'strsame';					
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'image')
												{
													$this->load->helper('url');
													$format_function = 'file_id_to_image_thumb_right';					
												}
												elseif($this->Customer->get_custom_field($custom_field_id,'type') == 'file')
												{
													$this->load->helper('url');
													$format_function = 'file_id_to_download_link';					
												}												
												else
												{
													$format_function = 'strsame';
												}
												
												echo '<li>'.($this->Customer->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Customer->get_custom_field($custom_field_id,'name').':').' '.$format_function($customer_info->{"custom_field_${custom_field_id}_value"}).'</li>';
												?>
											</div>
											<?php
										}
									}
									?>
				        </ul>
								<?php } ?>
			        </div>
							
			        <!-- delivery address-->
			        <div class="col-md-12 col-sm-12 col-xs-12">
					
			          <?php if(isset($delivery_person_info)) { ?>
				        <ul class="list-unstyled invoice-address" style="margin-bottom:10px;">
								
								
									<li class="invoice-to"><?php echo lang('deliveries_shipping_address','',array(),TRUE);?>:</li>
									<li><?php echo lang('common_name','',array(),TRUE).": ".H($delivery_person_info['first_name'].' '.$delivery_person_info['last_name']); ?></li>
									
									<?php if(!empty($delivery_person_info['address_1']) || !empty($delivery_person_info['address_2'])){ ?><li><?php echo lang('common_address','',array(),TRUE); ?> : <?php echo H($delivery_person_info['address_1']. ' '.$delivery_person_info['address_2']); ?></li><?php } ?>
									<?php if (!empty($delivery_person_info['city'])) { echo '<li>'.H($delivery_person_info['city'].' '.$delivery_person_info['state'].', '.$delivery_person_info['zip']).'</li>';} ?>
									<?php if (!empty($delivery_person_info['country'])) { echo '<li>'.H($delivery_person_info['country']).'</li>';} ?>			
									<?php if(!empty($delivery_person_info['phone_number'])){ ?><li><?php echo lang('common_phone_number','',array(),TRUE); ?> : <?php echo H($delivery_person_info['phone_number']); ?></li><?php } ?>
									<?php if(!empty($delivery_person_info['email'])){ ?><li><?php echo lang('common_email','',array(),TRUE); ?> : <?php echo H($delivery_person_info['email']); ?></li><?php } ?>
												
				        </ul>
								<?php } ?>
								
								<?php if(!empty($delivery_info['estimated_delivery_or_pickup_date']) || !empty($delivery_info['tracking_number']) ||  !empty($delivery_info['comment'])) {?>
									<ul class="list-unstyled invoice-address" style="margin-bottom:10px;">
										<li class="invoice-to"><?php echo lang('deliveries_delivery_information','',array(),TRUE);?>:</li>
										<?php if(!empty($delivery_info['estimated_delivery_or_pickup_date'])){ ?><li><?php echo lang('deliveries_estimated_delivery_or_pickup_date','',array(),TRUE); ?> : <?php echo date(get_date_format().' '.get_time_format(),strtotime($delivery_info['estimated_delivery_or_pickup_date'])); ?></li><?php } ?>
										<?php if(!empty($delivery_info['tracking_number'])){ ?><li><?php echo lang('deliveries_tracking_number','',array(),TRUE); ?> : <?php echo H($delivery_info['tracking_number']); ?></li><?php } ?>
										<?php if(!empty($delivery_info['comment'])){ ?><li><?php echo lang('common_comment','',array(),TRUE); ?> : <?php echo H($delivery_info['comment']); ?></li><?php } ?>
											
											
									</ul>
								<?php } ?>
			        </div>
							
			    </div>
					<?php
		    		$x_col = 6;
		    		$xs_col = 4;
		    		if($discount_exists)
		    		{
		    			$x_col = 4;
		    			$xs_col = 3;

							if($this->config->item('wide_printer_receipt_format'))
							{
				    		$x_col = 4;
								$xs_col = 2;
							}
		    		}
						else
						{
							if($this->config->item('wide_printer_receipt_format'))
							{
				    		$x_col = 6;
								$xs_col = 2;
							}
						}
					?>
			    <!-- invoice heading-->
			    <div class="invoice-table">
			        <div class="row">
			            <div class="<?php echo $this->config->item('wide_printer_receipt_format') ? 'col-md-'.$x_col . ' col-sm-' .$x_col . ' col-xs-'.$x_col : 'col-md-12 col-sm-12 col-xs-12' ?>">
			                <div class="invoice-head item-name"><?php echo lang('common_item_name','',array(),TRUE); ?></div>
			            </div>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
			                <div class="invoice-head text-right item-price"><?php echo lang('common_price','',array(),TRUE).($this->config->item('show_tax_per_item_on_receipt') ? '/'.lang('common_tax','',array(),TRUE) : ''); ?></div>
			            </div>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head text-right item-qty"><?php echo lang('common_quantity','',array(),TRUE); ?></div>
			            </div>

						<?php if($discount_exists) { ?>
				            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
				                <div class="invoice-head text-right item-discount"><?php echo lang('common_discount_percent','',array(),TRUE); ?></div>
				            </div>
				           
			      <?php } ?>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head pull-right item-total gift_receipt_element"><?php echo lang('common_total','',array(),TRUE).($this->config->item('show_tax_per_item_on_receipt') ? '/'.lang('common_tax','',array(),TRUE) : ''); ?></div>
			            </div>
						
			        </div>
			    </div>
			    <?php
					if ($discount_item_line = $cart->get_index_for_flat_discount_item())
					{
						$discount_item = $cart->get_item($discount_item_line);
						$cart->delete_item($discount_item_line);
						$cart->add_item($discount_item,false);
						$cart_items = $cart->get_items();
					}
				 
				$number_of_items_sold = 0;
				$number_of_items_returned = 0;
					
				foreach(array_reverse($cart_items, true) as $line=>$item)
				{
					if ($item->tax_included)
					{
						if (get_class($item) == 'PHPPOSCartItemSale')
						{
							if ($item->tax_included)
							{
								$this->load->helper('items');
								$unit_price = to_currency_no_money(get_price_for_item_including_taxes($item->item_id, $item->unit_price));
								$price_including_tax = $unit_price;
								$price_excluding_tax = get_price_for_item_excluding_taxes($item->item_id, $unit_price);
							}
						}
						else
						{
							if ($item->tax_included)
							{
								$this->load->helper('item_kits');
								$unit_price = to_currency_no_money(get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price));
								$price_including_tax = $unit_price;
								$price_excluding_tax = get_price_for_item_kit_excluding_taxes($item->item_kit_id, $unit_price);
							}
						}
					}
					else
					{
						$unit_price = $item->unit_price;
						
						//item
						if (get_class($item) == 'PHPPOSCartItemSale')
						{
							$this->load->helper('items');
							$price_excluding_tax = $unit_price;
							$price_including_tax = get_price_for_item_including_taxes($item->item_id, $item->unit_price);
							
						}
						else //Kit
						{
							$this->load->helper('item_kits');
							$price_excluding_tax = $unit_price;
							$price_including_tax = get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price);
						}						
					}
					$price_including_tax = $price_including_tax*(1-($item->discount/100));
					$price_excluding_tax = $price_excluding_tax*(1-($item->discount/100));
					$item_tax_amount = ($price_including_tax - $price_excluding_tax);
					
					 if ($item->quantity > 0 && $item->name != lang('common_store_account_payment','',array(),FALSE) && $item->name != lang('common_discount','',array(),FALSE) && $item->name != lang('common_refund','',array(),FALSE) && $item->name != lang('common_fee','',array(),FALSE))
					 {
				 		 $number_of_items_sold = $number_of_items_sold + $item->quantity;
					 }
					 elseif ($item->quantity < 0 && $item->name != lang('common_store_account_payment','',array(),FALSE) && $item->name != lang('common_discount','',array(),FALSE) && $item->name != lang('common_refund','',array(),FALSE) && $item->name != lang('common_fee','',array(),FALSE))
					 {
				 		 $number_of_items_returned = $number_of_items_returned + abs($item->quantity);
					 }
					 
					$item_number_for_receipt = false;
					
					if ($this->config->item('show_item_id_on_receipt'))
					{
						switch($this->config->item('id_to_show_on_sale_interface'))
						{
							case 'number':
							$item_number_for_receipt = array_key_exists('item_number', $item) ? H($item->item_number) : H($item->item_kit_number);
							break;
						
							case 'product_id':
							$item_number_for_receipt = array_key_exists('product_id', $item) ? H($item->product_id) : ''; 
							break;
						
							case 'id':
							$item_number_for_receipt = array_key_exists('item_id', $item) ? H($item->item_id) : 'KIT '.H($item->item_kit_id); 
							break;
						
							default:
							$item_number_for_receipt = array_key_exists('item_number', $item) ? H($item->item_number) : H($item->item_kit_number);
							break;
						}
					}
					
				?>
			    <!-- invoice items-->
			    <div class="invoice-table-content">
			        <div class="row">
			            <div class="<?php echo $this->config->item('wide_printer_receipt_format') ? 'col-md-'.$x_col . ' col-sm-' .$x_col . ' col-xs-'.$x_col : 'col-md-12 col-sm-12 col-xs-12' ?>">
			                <div class="invoice-content invoice-con">
			                    <div class="invoice-content-heading"><?php echo H($item->name); ?><?php if ($item_number_for_receipt){ ?> - <?php echo $item_number_for_receipt; ?><?php } ?><?php if ($item->size){ ?> (<?php echo H($item->size); ?>)<?php } ?>
													</div>
													
													<?php
													if (property_exists($item,'quantity_unit_quantity') && $item->quantity_unit_quantity !== NULL)
													{													?>
		                    	<div class="invoice-desc">
															<?php 
																echo 	lang('common_quantity_units').': '.H(to_quantity($item->quantity_unit_quantity)); 
            									?>
														</div>
													
													<?php } ?>
													
														<div class="invoice-desc">
															<?php 
																echo isset($item->variation_name) && $item->variation_name ? H($item->variation_name) : '';
															?>
														</div>
			                    	<div class="invoice-desc">
															<?php if (!$this->config->item('hide_desc_on_receipt') && !$item->description=="" ) { ?>
																<?php 
																	echo H($item->description); 
              									}	?>
															</div>
			                 			 <div class="invoice-desc">
					                    <?php 
																if(isset($item->serialnumber) && $item->serialnumber !="")
																{
																	echo H($item->serialnumber); 
																}
																
															?>
													</div>
													
													
													<?php
													foreach($item_custom_fields_to_display as $custom_field_id)
													{
													?>
															<?php 
															if(get_class($item) == 'PHPPOSCartItemSale' && $this->Item->get_custom_field($custom_field_id) !== false)
															{
																$item_info = $this->Item->get_info($item->item_id);
																
																if ($item_info->{"custom_field_${custom_field_id}_value"})
																{
																?>
					                    	<div class="invoice-desc">
																<?php
			
																if ($this->Item->get_custom_field($custom_field_id,'type') == 'checkbox')
																{
																	$format_function = 'boolean_as_string';
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'date')
																{
																	$format_function = 'date_as_display_date';				
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'email')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'url')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'phone')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'image')
																{
																	$this->load->helper('url');
																	$format_function = 'file_id_to_image_thumb_right';					
																}
																elseif($this->Item->get_custom_field($custom_field_id,'type') == 'file')
																{
																	$this->load->helper('url');
																	$format_function = 'file_id_to_download_link';					
																}
																else
																{
																	$format_function = 'strsame';
																}
																
																echo ($this->Item->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Item->get_custom_field($custom_field_id,'name').':').' '.$format_function($item_info->{"custom_field_${custom_field_id}_value"});
																?>
															</div>
															<?php
															}
														}
													}
														
													if (get_class($item) == 'PHPPOSCartItemKitSale' && $this->config->item('show_item_kit_items_on_receipt'))
													{
														$this->load->model('Item_kit_items');
													?>
		                    	<div class="invoice-desc">
														<?php 
														foreach($this->Item_kit_items->get_info_kits($item->get_id()) as $ikik)
														{
															$item_kit_info = $this->Item_kit->get_info($ikik->item_kit_id);
															echo to_quantity($ikik->quantity).'- '.$item_kit_info->name.'<br />';
														}
														
														foreach($this->Item_kit_items->get_info($item->get_id()) as $iki) 
														{ 
															$item_info = $this->Item->get_info($iki->item_id);
															echo to_quantity($iki->quantity).'- '.$item_info->name.'<br />';
														} 
														?>
													</div>
													<?php
													}
													
													foreach($item_kit_custom_fields_to_display as $custom_field_id)
													{
														if(get_class($item) == 'PHPPOSCartItemKitSale' && $this->Item_kit->get_custom_field($custom_field_id) !== false && $this->Item_kit->get_custom_field($custom_field_id) !== false)
														{
																$item_info = $this->Item_kit->get_info($item->item_kit_id);
																
																if ($item_info->{"custom_field_${custom_field_id}_value"})
																{
																?>
					                    	<div class="invoice-desc">
																<?php
			
																if ($this->Item_kit->get_custom_field($custom_field_id,'type') == 'checkbox')
																{
																	$format_function = 'boolean_as_string';
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'date')
																{
																	$format_function = 'date_as_display_date';				
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'email')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'url')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'phone')
																{
																	$format_function = 'strsame';					
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'image')
																{
																	$this->load->helper('url');
																	$format_function = 'file_id_to_image_thumb_right';					
																}
																elseif($this->Item_kit->get_custom_field($custom_field_id,'type') == 'file')
																{
																	$this->load->helper('url');
																	$format_function = 'file_id_to_download_link';					
																}
																else
																{
																	$format_function = 'strsame';
																}
																
																echo ($this->Item_kit->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Item_kit->get_custom_field($custom_field_id,'name').':').' '.$format_function($item_info->{"custom_field_${custom_field_id}_value"});
																?>
															</div>
															<?php
															}
														}
													?>
													<?php
													}
													
													
													if(isset($item->rule['type']))
													{	
														
														echo '<br class="gift_receipt_element"><i class="gift_receipt_element">'.H($item->rule['name']).'</i>';
														if(isset($item->rule['rule_discount']))
														{
															echo '<br class="gift_receipt_element"><i class="gift_receipt_element"><u class="gift_receipt_element">'.lang('common_discount','',array(),TRUE). '</u>: ' .to_currency($item->rule['rule_discount']) . '</i>';
														}																	
													}
														
													?>
			                </div>
			            </div>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
			                <div class="invoice-content item-price text-right">
												
								<?php if ($this->config->item('show_orig_price_if_marked_down_on_receipt') && $item->regular_price > $unit_price) { ?>
									<span class="strikethrough"><?php echo to_currency($item->regular_price,10);?></span>
								<?php } ?>
								
								<?php echo to_currency($unit_price,10).($this->config->item('show_tax_per_item_on_receipt') ? '/'.to_currency($item_tax_amount) : ''); ?>
							</div>
			            </div>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> ">
			                <div class="invoice-content item-qty text-right">
												<?php 
												if ($this->config->item('number_of_decimals_for_quantity_on_receipt') && floor($item->quantity) != $item->quantity)
												{
													echo to_currency_no_money($item->quantity,$this->config->item('number_of_decimals_for_quantity_on_receipt')); 
												}
												else
												{
													echo to_quantity($item->quantity); 
												}
												?>
											
											</div>
			            </div>
			      <?php if($discount_exists) { ?>
									<div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
			              <div class="invoice-content item-discount text-right"><?php echo to_quantity($item->discount); ?></div>
			            </div>
						<?php } ?>
									<div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">      
					          <div class="invoice-content item-total pull-right">
											
											<?php if ($this->config->item('indicate_taxable_on_receipt') && $item->taxable && !empty($taxes))
											{
												echo '<small>*'.lang('common_taxable','',array(),TRUE).'</small>';
											}
											?>
											
											<?php echo to_currency($unit_price*$item->quantity-$unit_price*$item->quantity*$item->discount/100,10).($this->config->item('show_tax_per_item_on_receipt') ? '/'.to_currency($item_tax_amount*$item->quantity) : ''); ?>
										
										</div>
						      </div>
						
			     </div>					
			    </div>
			    <?php } ?>
					
					<?php
					foreach($sale_custom_fields_to_display as $custom_field_id)
					{
						if($this->Sale->get_custom_field($custom_field_id) !== false && $this->Sale->get_custom_field($custom_field_id) !== false)
						{											
								if ($cart->{"custom_field_${custom_field_id}_value"})
								{
								?>						
								<?php

								if ($this->Sale->get_custom_field($custom_field_id,'type') == 'checkbox')
								{
									$format_function = 'boolean_as_string';
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'date')
								{
									$format_function = 'date_as_display_date';				
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'email')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'url')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'phone')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'image')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_image_thumb_right';					
								}
								elseif($this->Sale->get_custom_field($custom_field_id,'type') == 'file')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_download_link';					
								}
								else
								{
									$format_function = 'strsame';
								}
								?>
								<div class="invoice-table-content">
								<div class="row">
									<div class="col-md-6 col-sm-6 col-xs-6">
			       			 	<div class="invoice-content invoice-con">
			         			 <div class="invoice-content-heading"><?php
											 if (!$this->Sale->get_custom_field($custom_field_id,'hide_field_label'))
											 {
												 echo $this->Sale->get_custom_field($custom_field_id,'name');
											 }
											 else
											 {
											 	echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
											 }
										 
			         			 ?></div>
												<div class="invoice-desc"><?php 
													if (!$this->Sale->get_custom_field($custom_field_id,'hide_field_label'))
													{
														echo $format_function($cart->{"custom_field_${custom_field_id}_value"}); 
													}	
													?>
													</div>
											</div>
			       			 </div>
							</div>
						</div>
								<?php
							}
						}
					?>
					<?php
					}
					?>
					
			    <div class="invoice-footer gift_receipt_element">
						<?php if ($exchange_name) { ?>
						
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo lang('common_exchange_to','',array(),TRUE).' '.H($exchange_name); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value">x <?php echo to_currency_no_money($exchange_rate); ?></div>
					            </div>
					        </div>
											
						<?php } ?>
						
			        <div class="row">
			            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
			                <div class="invoice-footer-heading"><?php echo lang('common_sub_total','',array(),TRUE); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value">
			                	
												<?php if (isset($exchange_name) && $exchange_name) { 
													echo to_currency_as_exchange($cart,$subtotal);
												?>
												<?php } else {  ?>
												<?php echo to_currency($subtotal); ?>				
												<?php
												}
												?>
			                </div>
			            </div>
			        </div>
			        <?php if ($this->config->item('group_all_taxes_on_receipt')) { ?>
						<?php 
						$total_tax = 0;
						foreach($taxes as $name=>$value) 
						{
							$total_tax+=$value;
					 	}
						?>	
						<div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_tax','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value">
								
												<?php if (isset($exchange_name) && $exchange_name) { 
													echo to_currency_as_exchange($cart,$total_tax*$exchange_rate);					
												?>
												<?php } else {  ?>
												<?php echo to_currency($total_tax*$exchange_rate); ?>				
												<?php
												}
												?>
												
												</div>
				            </div>
				        </div>
						
					<?php }else {?>
						<?php foreach($taxes as $name=>$value) { ?>
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo H($name); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value">
													
													
													<?php if (isset($exchange_name) && $exchange_name) { 
														echo to_currency_as_exchange($cart,$value*$exchange_rate);					
													?>
													<?php } else {  ?>
													<?php echo to_currency($value); ?>				
													<?php
													}
													?>
													
													
													</div>
					            </div>
					        </div>
						<?php }; ?>
					<?php } ?>
			        <div class="row">
			            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
			                <div class="invoice-footer-heading"><?php echo lang('common_total','',array(),TRUE); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value invoice-total">
																							
											
											<?php if (isset($exchange_name) && $exchange_name) { 
												?>
												<?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($total)) : to_currency_as_exchange($cart,$total); ?>				
											<?php } else {  ?>
											<?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($total)) : to_currency($total); ?>				
											<?php
											}
											?>
											
											</div>
			            </div>
			        </div> 
					
			        <div class="row">
						<?php if ($number_of_items_sold) { ?>
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_items_sold','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_sold); ?></div>
				            </div>
						<?php } ?>
						
						<?php if ($number_of_items_returned) { ?>
							
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_items_returned','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_returned); ?></div>
				            </div>
						<?php } ?>
						
			        </div> 
					
			        <?php
						foreach($payments as $payment_id=>$payment)
						{ 
					?>
						<div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-xs-offset-4 col-md-4 col-sm-4 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				            	<?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit','',array(),TRUE) ||  $payment->payment_type == lang('sales_partial_credit','',array(),TRUE) || $payment->payment_type == lang('common_ebt','',array(),TRUE) || $payment->payment_type == lang('common_partial_ebt','',array(),TRUE) ||  $payment->payment_type == lang('common_ebt_cash','',array(),TRUE) ||  $payment->payment_type == lang('common_partial_ebt_cash','',array(),TRUE))) { ?>
									<div class="invoice-footer-value"><?php echo $is_sale_integrated_ebt_sale ? 'EBT ' : '';?><?php echo H($payment->card_issuer. ': '.$payment->truncated_card); ?></div>
								<?php } else { ?>
									<div class="invoice-footer-value"><?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?></div>																				
								<?php } ?>								
				            </div>
							
				            <div class="col-md-2 col-sm-2 col-xs-4">
								<div class="invoice-footer-value invoice-payment">
									
									
									
									<?php 
									
									if (isset($exchange_name) && $exchange_name) { 
										?>
										<?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash','',array(),TRUE) ?  to_currency_as_exchange($cart,round_to_nearest_05($payment->payment_amount)) : to_currency_as_exchange($cart,$payment->payment_amount); ?>				
									<?php } else {  ?>
									<?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash','',array(),TRUE) ?  to_currency(round_to_nearest_05($payment->payment_amount)) : to_currency($payment->payment_amount); ?>				
									<?php
									}
									
									
									?>
								
								
								</div>
				            </div>
							
			            	<?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit','',array(),TRUE) ||  $payment->payment_type == lang('sales_partial_credit','',array(),TRUE) || $payment->payment_type == lang('common_ebt','',array(),TRUE) || $payment->payment_type == lang('common_partial_ebt','',array(),TRUE) ||  $payment->payment_type == lang('common_ebt_cash','',array(),TRUE) ||  $payment->payment_type == lang('common_partial_ebt_cash','',array(),TRUE))) { ?>
							
				           <div class="col-md-offset-6 col-sm-offset-6 col-xs-offset-3 col-md-6 col-sm-6 col-xs-9">
								<?php if ($payment->entry_method) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_entry_method','',array(),TRUE). ': '.H($payment->entry_method); ?></div>
								<?php } ?>

								<?php if ($payment->tran_type) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_transaction_type','',array(),TRUE). ': '.($is_sale_integrated_ebt_sale ? 'EBT ' : '').H($payment->tran_type); ?></div>
								<?php } ?>
							
								<?php if ($payment->application_label) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_application_label','',array(),TRUE).': '.H($payment->application_label); ?></div>
								<?php } ?>
							
								<?php if ($payment->ref_no) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_ref_no','',array(),TRUE). ': '.H($payment->ref_no); ?></div>
								<?php } ?>
								<?php if ($payment->auth_code) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_auth_code','',array(),TRUE). ': '.H($payment->auth_code); ?></div>
								<?php } ?>
															
							
								<?php if ($payment->aid) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'AID: '.H($payment->aid); ?></div>
								<?php } ?>
							
								<?php if ($payment->tvr) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TVR: '.H($payment->tvr); ?></div>
								<?php } ?>
							
							
								<?php if ($payment->tsi) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TSI: '.H($payment->tsi); ?></div>
								<?php } ?>
							
							
								<?php if ($payment->arc) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'ARC: '.H($payment->arc); ?></div>
								<?php } ?>

								<?php if ($payment->cvm) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'CVM: '.H($payment->cvm); ?></div>
								<?php } ?>
							</div>
							<?php } ?>							
							
						</div>
					<?php
						}
					?>

					<?php foreach($payments as $payment) {?>
						<?php if (strpos($payment->payment_type, lang('common_giftcard','',array(),TRUE))=== 0) {?>
							<?php $giftcard_payment_row = explode(':', $payment->payment_type); ?>
							
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-4 col-sm-4 col-xs-4">
					                <div class="invoice-footer-heading"><?php echo lang('sales_giftcard_balance','',array(),TRUE); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
										<div class="invoice-footer-value"><?php echo H($payment->payment_type);?></div>											
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
									<div class="invoice-footer-value invoice-payment"><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row))); ?></div>
					            </div>
					        </div>
						<?php }?>
					<?php }?> 
					
					<?php 
					foreach($integrated_gift_card_balances as $integrated_giftcard_number => $balance) { ?>
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-4 col-sm-4 col-xs-4">
					                <div class="invoice-footer-heading"><?php echo lang('sales_giftcard_balance','',array(),TRUE); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
										<div class="invoice-footer-value"><?php echo H($integrated_giftcard_number);?></div>											
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
									<div class="invoice-footer-value invoice-payment"><?php echo to_currency($balance); ?></div>
					            </div>
					        </div>
					<?php } ?>
					
					<?php if ($amount_change >= 0) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_change_due','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total">
													
													<?php if (isset($exchange_name) && $exchange_name) { 
														$amount_change_default_currency = $amount_change*pow($exchange_rate,-1);
														
														?>
														
														<?php
															
														if ($amount_change_default_currency != $amount_change) {
														?>
														<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($amount_change)) : to_currency_as_exchange($cart,$amount_change); ?>
														<br /><?php echo lang('common_or','',array(),TRUE);?><br />
														<?php
													}
														?>
														<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change_default_currency)) : to_currency($amount_change_default_currency); ?>				
														
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change)) : to_currency($amount_change); ?>				
													<?php
													}
													?>
													
												
												</div>
				            </div>
				        </div>
					<?php
					}
					else
					{
					?>
						<?php if (!$is_ecommerce) { ?>
						<div class="row">
							
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('common_amount_due','',array(),TRUE); ?></div>
				            </div>
										
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total">
													<?php if (isset($exchange_name) && $exchange_name) { 
														?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange($cart,round_to_nearest_05($amount_change * -1)) : to_currency_as_exchange($cart,$amount_change * -1); ?>
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change * -1)) : to_currency($amount_change * -1); ?>
													<?php
													}
													?>
												
												</div>
				            </div>
				        </div>
					<?php
					} 
				}
					?>  
					
					<?php if (isset($ebt_balance) && ($ebt_balance) !== FALSE) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('sales_ebt_balance_amount','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_currency($ebt_balance); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>					
					
					<?php if (isset($customer_balance_for_sale) && (float)$customer_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('sales_customer_account_balance','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_currency($customer_balance_for_sale); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					
					<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($sales_until_discount) && !$this->config->item('hide_sales_to_discount_on_receipt') && $this->config->item('loyalty_option') == 'simple') {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_sales_until_discount','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo $sales_until_discount <= 0 ? lang('sales_redeem_discount_for_next_sale','',array(),TRUE) : to_quantity($sales_until_discount); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					

					<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($customer_points) && !$this->config->item('hide_points_on_receipt') && $this->config->item('loyalty_option') == 'advanced') {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_points','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($customer_points); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>


					<?php
					if ($ref_no)
					{
					?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('sales_ref_no','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo H($ref_no); ?></div>
				            </div>
				        </div>
					<?php
					}
					if (isset($auth_code) && $auth_code)
					{
					?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('sales_auth_code','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo H($auth_code); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					
					<?php
					if ($this->config->item('show_total_discount_on_receipt') && !$store_account_payment && $cart->get_total_discount()) { ?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('sales_total_discount','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_currency($cart->get_total_discount()); ?></div>
				            </div>
				        </div>
						
					<?php
					}
					?>

					<div class="row">
			            <div class="col-md-12 col-sm-12 col-xs-12">
			                <div class="text-center">
			                	<?php if($show_comment_on_receipt==1)
									{
										echo H($comment);
									}
								?>
			                </div>
			            </div>
			        </div>
			    </div>
			    <!-- invoice footer-->						 
			    <div class="row">
			        <div class="col-md-12 col-sm-12 col-xs-12">
			            <div class="invoice-policy" id="invoice-policy-return">
			                <?php echo nl2br(H($return_policy)); ?>
			            </div>
									
			            <div class="invoice-policy" id="invoice-policy-return-mobile" style="display: none;line-height:1;">
			                <?php echo H($return_policy); ?>
			            </div>
									
			            <div id="receipt_type_label" style="display: none;" class="receipt_type_label invoice-policy">
							<?php echo lang('sales_merchant_copy','',array(),TRUE); ?>
						</div>
			            <?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
							<div id='barcode' class="invoice-policy">
							<?php echo "<img src='".site_url('barcode/index/svg')."?barcode=$sale_id&text=$sale_id' alt=''/>"; ?>
							</div>
						<?php } ?>
						
						<?php 
						$this->load->model('Price_rule');
						$coupons = $this->Price_rule->get_coupons_for_receipt($total);
						if (count($coupons) > 0)
						{
							?>
							
					    <div class="row">
					        <div class="col-md-12 col-sm-12 col-xs-12">
					            <div class="invoice-policy">
												<h3 class='text-center'><?php echo lang('common_coupons','',array(),TRUE);?></h3>
												
					            </div>
									</div>
							</div>
							<?php
								
						
							foreach($coupons as $coupon)
							{
								?>
								<div class="invoice-policy coupon">
									<?php
									$coupon_text = H($coupon['name'].' - '.$coupon['description']);
									$coupon_barcode = H($coupon['coupon_code']);
									$begins = date(get_date_format(),strtotime($coupon['start_date']));
									$expires = date(get_date_format(),strtotime($coupon['end_date']));
									?>
									<div><strong><?php echo H($coupon_text);?></strong></div>
									<?php echo "<img src='".site_url('barcode/index/svg')."?barcode=$coupon_barcode' alt=''/>"; ?>
									<div><?php echo lang('common_coupon_code','',array(),TRUE).': '.H($coupon_barcode);?></div>
									<div><?php echo lang('common_begins','',array(),TRUE).': '.H($begins);?></div>
									<div><?php echo lang('common_expires','',array(),TRUE).': '.H($expires);?></div>
								</div><br />
								
								<?php
							}
						?>
							
						<?php
						}?>
						<div id="announcement" class="invoice-policy">
							<?php echo $this->config->item('announcement_special') ?>
						</div>
							<?php if ($signature_needed && !$this->config->item('hide_signature')) {?>
								<button class="btn btn-primary text-white hidden-print" id="capture_digital_sig_button"> <?php echo lang('sales_capture_digital_signature','',array(),TRUE); ?> </button>
								<br />
							<?php
							}
							?>
			      </div>
					
					<?php if(!$this->config->item('hide_signature')) { ?>
			        <div class="col-md-6 col-sm-6" style="margin-top: 30px;">
						<div id="signature">
								<?php if ($signature_needed) {?>
									
									<div id="digital_sig_holder">
										<canvas id="sig_cnv" name="sig_cnv" class="signature" width="500" height="100"></canvas>
										<div id="sig_actions_container" class="pull-right">
											<?php
											if ($this->agent->is_mobile()) //Display done button first
											{
											?>
												<button class="btn btn-primary btn-radius btn-lg hidden-print" id="capture_digital_sig_done_button"> <?php echo lang('sales_done_capturing_sig','',array(),TRUE); ?> </button>
												<button class="btn btn-primary btn-radius btn-lg hidden-print" id="capture_digital_sig_clear_button"> <?php echo lang('sales_clear_signature','',array(),TRUE); ?> </button>
											<?php
											}
											else  //Display done button 2nd
											{
											?>
												<button class="btn btn-primary btn-radius btn-lg hidden-print" id="capture_digital_sig_clear_button"> <?php echo lang('sales_clear_signature','',array(),TRUE); ?> </button>
												<button class="btn btn-primary btn-radius btn-lg hidden-print" id="capture_digital_sig_done_button"> <?php echo lang('sales_done_capturing_sig','',array(),TRUE); ?> </button>
											<?php	
											}
											?>
										</div>
									</div>
								
								<div id="signature_holder" style="text-align:left;">
									<?php 
										if(isset($signature_file_id) && $signature_file_id)
										{
							      		echo img(array('src' => app_file_url($signature_file_id), 'width' => 250));
										}
										else
										{
											if ($this->config->item('enable_tips') && ($is_credit_card_sale || $is_debit_card_sale))
											{
												echo lang('common_total','',array(),TRUE); ?>: <?php echo to_currency_as_exchange($cart,$total);?><br /><br /><br />
												<span style='width:70px; display: inline-block;'><?php echo lang('common_tip', '', array(), true); ?></span> ____________________________________	<br /><br /><br />
												<span style='width:70px; display: inline-block;'><?php echo lang('sales_total_with_tip','',array(),TRUE); ?></span> ____________________________________	<br /><br /><br />
											<?php
											}
											?>
											<span style='width:70px; display: inline-block; margin-bottom: 20px;'><?php echo lang('sales_signature','',array(),TRUE); ?></span> ____________________________________
										
										<?php
										}
									?>
									
								</div>
								<?php } ?>
								
								<?php 
								$this->load->helper('sale');
								if ($is_credit_card_sale)
								{	
									echo lang('sales_card_statement','',array(),TRUE);
								}
								?>
								
						</div>
			        </div>
			        <?php } ?>
			    </div>
			</div>
			<!--container-->
		</div>		
	</div>
</div>
</div>


<div id="duplicate_receipt_holder" style="display: none;">
	
</div>

<?php if ($this->config->item('print_after_sale') && $this->uri->segment(2) == 'complete')
{
?>
<script type="text/javascript">
$(window).bind("load", function() {
	<?php
	if ($this->agent->browser() == 'Chrome')
	{
	?>
		setTimeout(function(){ print_receipt(); }, 1500);
	<?php	
	}
	else
	{
	?>
		print_receipt();	
	<?php
	}
	?>
});
</script>
<?php }  ?>

<script type="text/javascript">

$(document).ready(function(){
	
	<?php if (isset($email_sent) && $email_sent) { ?>
		show_feedback('success', <?php echo json_encode(lang('common_receipt_sent','',array(),TRUE)); ?>, <?php echo json_encode(lang('common_success','',array(),TRUE)); ?>);	
	<?php } ?>
	$("#edit_sale").click(function(e)
	{
		e.preventDefault();
		bootbox.confirm(<?php echo json_encode(lang('sales_sale_edit_confirm','',array(),TRUE)); ?>,function(result)
		{
			if (result)
			{
				$("#sales_change_form").submit();
			}
		});
	});
	$("#email_receipt").click(function()
	{
		$.get($(this).attr('href'), function()
		{
			show_feedback('success', <?php echo json_encode(lang('common_receipt_sent','',array(),TRUE)); ?>, <?php echo json_encode(lang('common_success','',array(),TRUE)); ?>);
			
		});
		
		return false;
	});
});

$('#print_duplicate_receipt').click(function()
{
	if ($('#print_duplicate_receipt').prop('checked'))
	{
	   var receipt = $('#receipt_wrapper').clone();
	   $('#duplicate_receipt_holder').html(receipt);
		$("#duplicate_receipt_holder").addClass('visible-print-block');
		$("#duplicate_receipt_holder #signature_holder").addClass('hidden');
		$("#duplicate_receipt_holder .receipt_type_label").text(<?php echo json_encode(lang('sales_duplicate_receipt','',array(),TRUE)); ?>);
		$(".receipt_type_label").show();		
		$(".receipt_type_label").addClass('show_receipt_labels');
	}
	else
	{
		$("#duplicate_receipt_holder").empty();
		$("#duplicate_receipt_holder").removeClass('visible-print-block');
		$("#duplicate_receipt_holder #signature_holder").removeClass('hidden');
		$(".receipt_type_label").hide();
		$(".receipt_type_label").removeClass('show_receipt_labels');	
	}
});

<?php
$this->load->helper('sale');
if ($this->config->item('always_print_duplicate_receipt_all') || ($this->config->item('automatically_print_duplicate_receipt_for_cc_transactions') && $is_credit_card_sale))
{
?>
	$("#print_duplicate_receipt").trigger('click');
<?php
}
?>

<?php
if ($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt'))
{
	if ($this->agent->browser() == 'Chrome' || $this->agent->browser() == 'Edge' || $this->agent->browser() == 'Internet Explorer')
	{
		?>
 		window.onafterprint = function()
 		{
 			window.location = '<?php echo site_url('sales'); ?>';		
 		}
	<?php
	}
}
?>

function print_receipt()
 {
 	window.print();
	<?php
	if($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt'))
	{
		if ($this->agent->browser() == 'Safari' || $this->agent->browser() == 'Firefox')
		{
		?>
		window.location = '<?php echo site_url('sales'); ?>';
		<?php
		}
	}
	?>
 }
 
 function toggle_gift_receipt()
 {
	 var gift_receipt_text = <?php echo json_encode(lang('sales_gift_receipt','',array(),TRUE)); ?>;
	 var regular_receipt_text = <?php echo json_encode(lang('sales_regular_receipt','',array(),TRUE)); ?>;
	 
	 if ($("#gift_receipt_button").hasClass('regular_receipt'))
	 {
		 $('#gift_receipt_button').addClass('gift_receipt');	 	
		 $('#gift_receipt_button').removeClass('regular_receipt');
		 $("#gift_receipt_button").text(gift_receipt_text);	
		 $('.gift_receipt_element').show();	
	 }
	 else
	 {
		 $('#gift_receipt_button').removeClass('gift_receipt');	 	
		 $('#gift_receipt_button').addClass('regular_receipt');
		 $("#gift_receipt_button").text(regular_receipt_text);
		 $('.gift_receipt_element').hide();	
	 }
 	
 }
 
//timer for sig refresh
var refresh_timer;
var sig_canvas = document.getElementById('sig_cnv');

<?php
//Only use Sig touch on mobile
if ($this->agent->is_mobile())
{
?>
	var signaturePad = new SignaturePad(sig_canvas);
<?php
}
?>
$("#capture_digital_sig_button").click(function()
{	
	<?php
	//Only use Sig touch on mobile
	if ($this->agent->is_mobile())
	{
	?>
		signaturePad.clear();
	<?php
	}
	else
	{
	?>
		try
		{
			if (TabletConnectQuery()==0)
			{
				bootbox.alert(<?php echo json_encode(lang('sales_unable_to_connect_to_signature_pad','',array(),TRUE)); ?>);
				return;
			}	
		}
		catch(exception) 
		{
			bootbox.alert(<?php echo json_encode(lang('sales_unable_to_connect_to_signature_pad','',array(),TRUE)); ?>);
			return;			
		}
		
	   var ctx = document.getElementById('sig_cnv').getContext('2d');
	   SigWebSetDisplayTarget(ctx);
	   SetDisplayXSize( 500 );
	   SetDisplayYSize( 100 );
	   SetJustifyMode(0);
	   refresh_timer = SetTabletState(1,ctx,50);
	   KeyPadClearHotSpotList();
	   ClearSigWindow(1);
	   ClearTablet();
	<?php
	}
	?>
	
	$("#capture_digital_sig_button").hide();
	$("#digital_sig_holder").show();
});

$("#capture_digital_sig_clear_button").click(function()
{
	<?php
	//Only use Sig touch on mobile
	if ($this->agent->is_mobile())
	{
	?>
		signaturePad.clear();
	<?php
	}
	else
	{
	?>
   	ClearTablet();	
	<?php
	}
	?>
});

$("#capture_digital_sig_done_button").click(function()
{
	<?php
	//Only use Sig touch on mobile
	if ($this->agent->is_mobile())
	{
	?>
	   if(signaturePad.isEmpty())
	   {
	      bootbox.alert(<?php echo json_encode(lang('sales_no_sig_captured','',array(),TRUE)); ?>);
	   }
	   else
	   {
			SigImageCallback(signaturePad.toDataURL().split(",")[1]);
			$("#capture_digital_sig_button").show();
	   }	
	<?php
	}
	else
	{
	?>
		if(NumberOfTabletPoints() == 0)
		{
		   bootbox.alert(<?php echo json_encode(lang('sales_no_sig_captured','',array(),TRUE)); ?>);
		}
		else
		{
		   SetTabletState(0,refresh_timer);
		   //RETURN TOPAZ-FORMAT SIGSTRING
		   SetSigCompressionMode(1);
			var sig = GetSigString();

		   //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
		   SetImageXSize(500);
		   SetImageYSize(100);
		   SetImagePenWidth(5);
		   GetSigImageB64(SigImageCallback);
			$("#capture_digital_sig_button").show();
		}
	<?php
	}
	?>
});

function SigImageCallback( str )
{
 $("#digital_sig_holder").hide();
 $.post('<?php echo site_url('sales/sig_save'); ?>', {sale_id: <?php echo json_encode($sale_id_raw); ?>, image: str}, function(response)
 {
	 $("#signature_holder").empty();
	 $("#signature_holder").append('<img src="'+SITE_URL+'/app_files/view/'+response.file_id+'?timestamp='+response.file_timestamp+'" width="250" />');
 }, 'json');

}
 
<?php
//EMV Usb Reset
if (isset($reset_params))
{
?>
 var data = {};
 <?php
 foreach($reset_params['post_data'] as $name=>$value)
 {
	 if ($name && $value)
	 {
	 ?>
	 data['<?php echo $name; ?>'] = '<?php echo $value; ?>';
 	 <?php 
	 }
 }
 ?>	

 mercury_emv_pad_reset(<?php echo json_encode($reset_params['post_host']); ?>, <?php echo $this->Location->get_info_for_key('listener_port'); ?>, data);
<?php
}
if (isset($trans_cloud_reset) && $trans_cloud_reset)
{
?>
	$.get(<?php echo json_encode(site_url('sales/reset_pin_pad')); ?>);
<?php
}
?>

<?php if ($this->config->item('auto_capture_signature')) { ?>
	$("#capture_digital_sig_button").click();	
<?php } ?>
</script>

<?php if(($is_integrated_credit_sale || $is_sale_integrated_ebt_sale) && $is_sale) { ?>
<script type="text/javascript">
show_feedback('success', <?php echo json_encode(lang('sales_credit_card_processing_success','',array(),TRUE)); ?>, <?php echo json_encode(lang('common_success','',array(),TRUE)); ?>);	
</script>
<?php } ?>

<script>
html2canvas(document.querySelector("#receipt_wrapper"),{windowWidth: 280, onclone: function(doc)
	{
		doc.querySelector('#invoice-policy-return').style.display = 'none';
		doc.querySelector('#invoice-policy-return-mobile').style.display = 'block';
	}}).then(canvas => {
	document.getElementById("print_image_output").innerHTML = canvas.toDataURL();
});
</script>
<script type="text/print-image" id="print_image_output"></script>
<!-- This is used for mobile apps to print receipt-->
<script type="text/print" id="print_output"><?php echo $company; ?>

<?php echo H($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?>

<?php echo H($this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE)); ?>

<?php if($website) { ?>
<?php echo H($website); ?>
	
<?php } ?>

<?php echo H($receipt_title); ?>

<?php echo H($transaction_time); ?>

<?php if(isset($customer))
{
?>
<?php echo lang('common_customer','',array(),TRUE).": ".H($customer); ?>
<?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
	
<?php if(!empty($customer_address_1)){ ?><?php echo lang('common_address','',array(),TRUE); ?>: <?php echo H($customer_address_1. ' '.$customer_address_2); ?>
	
<?php } ?>
<?php if (!empty($customer_city)) { echo H($customer_city.' '.$customer_state.', '.$customer_zip); ?>

<?php } ?>
<?php if (!empty($customer_country)) { echo H($customer_country); ?>
	
<?php } ?>
<?php if(!empty($customer_phone)){ ?><?php echo lang('common_phone_number','',array(),TRUE); ?> : <?php echo H($customer_phone); ?>
	
<?php } ?>
<?php if(!empty($customer_email)){ ?><?php echo lang('common_email','',array(),TRUE); ?> : <?php echo H($customer_email); ?><?php } ?>

<?php
}
else
{
?>
	
<?php
}
}
?>
<?php echo lang('common_sale_id','',array(),TRUE).": ".$sale_id; ?>
<?php if (isset($sale_type)) { ?>
<?php echo $sale_type; ?>
<?php } ?>
	
<?php if (!$this->config->item('remove_employee_from_receipt')) { ?>
<?php echo lang('common_employee','',array(),TRUE).": ".$employee; ?>
<?php }?>
	
<?php 
if($this->Location->get_info_for_key('enable_credit_card_processing',isset($override_location_id) ? $override_location_id : FALSE))
{
	echo lang('common_merchant_id','',array(),TRUE).': '.H($this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE));
}
?>

<?php echo lang('common_item','',array(),TRUE); ?>            <?php echo lang('common_price','',array(),TRUE); ?> <?php echo lang('common_quantity','',array(),TRUE); ?><?php if($discount_exists){echo ' '.lang('common_discount_percent','',array(),TRUE);}?> <?php echo lang('common_total','',array(),TRUE); ?>

---------------------------------------
<?php
foreach(array_reverse($cart_items, true) as $line=>$item)
{
?>
<?php echo character_limiter(H($item->name), 14,'...'); ?><?php echo strlen($item->name) < 14 ? str_repeat(' ', 14 - strlen(H($item->name))) : ''; ?> <?php echo str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($item->unit_price,10)); ?> <?php echo to_quantity($item->quantity); ?><?php if($discount_exists){echo ' '.$item->discount;}?> <?php echo str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100,10)); ?>

  <?php echo H($item->description); ?>  <?php echo isset($item->serialnumber) ? H($item->serialnumber) : ''; ?>
	

<?php
}
?>

<?php echo lang('common_sub_total','',array(),TRUE); ?>: <?php echo str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($subtotal)); ?>


<?php foreach($taxes as $name=>$value) { ?>
<?php echo $name; ?>: <?php echo str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($value)); ?>

<?php }; ?>

<?php echo lang('common_total','',array(),TRUE); ?>: <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency(round_to_nearest_05($total))) : str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($total)); ?>

<?php echo lang('common_items_sold','',array(),TRUE); ?>: <?php echo to_quantity($number_of_items_sold); ?>

<?php
	foreach($payments as $payment_id=>$payment)
{ ?>

<?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment','',array(),TRUE); ?>  <?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit','',array(),TRUE) ||  $payment->payment_type == lang('sales_partial_credit','',array(),TRUE) || $payment->payment_type == lang('common_ebt','',array(),TRUE) || $payment->payment_type == lang('common_partial_ebt','',array(),TRUE) ||  $payment->payment_type == lang('common_ebt_cash','',array(),TRUE) ||  $payment->payment_type == lang('common_partial_ebt_cash','',array(),TRUE))) { echo $payment->card_issuer. ': '.$payment->truncated_card; ?> <?php } else { ?><?php $splitpayment=explode(':',$payment->payment_type); echo $splitpayment[0]; ?> <?php } ?><?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash','',array(),TRUE) ?  str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency(round_to_nearest_05($payment->payment_amount))) : str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($payment->payment_amount)); ?>

<?php if ($payment->entry_method) { ?>
	
<?php echo lang('sales_entry_method','',array(),TRUE). ': '.H($payment->entry_method); ?>
	
<?php } ?>
<?php if ($payment->tran_type) { ?><?php echo lang('sales_transaction_type','',array(),TRUE). ': '.H($payment->tran_type); ?>
	
<?php } ?>
<?php if ($payment->application_label) { ?><?php echo lang('sales_application_label','',array(),TRUE). ': '.H($payment->application_label); ?>
	
<?php } ?>
<?php if ($payment->ref_no) { ?><?php echo lang('sales_ref_no','',array(),TRUE). ': '.H($payment->ref_no); ?>
	
<?php } ?>
<?php if ($payment->auth_code) { ?><?php echo lang('sales_auth_code','',array(),TRUE). ': '.H($payment->auth_code); ?>
	
<?php } ?>
<?php if ($payment->aid) { ?><?php echo 'AID: '.H($payment->aid); ?>
	
<?php } ?>
<?php if ($payment->tvr) { ?><?php echo 'TVR: '.H($payment->tvr); ?>

<?php } ?>
<?php if ($payment->tsi) { ?><?php echo 'TSI: '.H($payment->tsi); ?>
	
<?php } ?>
<?php if ($payment->arc) { ?><?php echo 'ARC: '.H($payment->arc); ?>
	
<?php } ?>
<?php if ($payment->cvm) { ?><?php echo 'CVM: '.H($payment->cvm); ?>
<?php } ?>
<?php
}
?>	
<?php foreach($payments as $payment) { $giftcard_payment_row = explode(':', $payment->payment_type);?>
<?php if (strpos($payment->payment_type, lang('common_giftcard','',array(),TRUE))=== 0) {?><?php echo lang('sales_giftcard_balance','',array(),TRUE); ?>  <?php echo $payment->payment_type;?>: <?php echo str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row)))); ?>
	<?php }?>
<?php }?>
<?php if ($amount_change >= 0) {?>
<?php echo lang('common_change_due','',array(),TRUE); ?>: <?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency(round_to_nearest_05($amount_change))) : str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($amount_change)); ?>
<?php
}
else
{
?>
<?php echo lang('common_amount_due','',array(),TRUE); ?>: <?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency(round_to_nearest_05($amount_change * -1))) : str_replace('<span style="white-space:nowrap;">-</span>', '-', to_currency($amount_change * -1)); ?>
<?php
} 
?>
<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($customer_points) && !$this->config->item('hide_points_on_receipt')) {?>
	
<?php echo lang('common_points','',array(),TRUE); ?>: <?php echo to_currency_no_money($customer_points); ?>
<?php } ?>

<?php if (isset($customer_balance_for_sale) && (float)$customer_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) {?>

<?php echo lang('sales_customer_account_balance','',array(),TRUE); ?>: <?php echo to_currency($customer_balance_for_sale); ?>
<?php
}
?>
<?php
if ($ref_no)
{
?>

<?php echo lang('sales_ref_no','',array(),TRUE); ?>: <?php echo $ref_no; ?>
<?php
}
if (isset($auth_code) && $auth_code)
{
?>

<?php echo lang('sales_auth_code','',array(),TRUE); ?>: <?php echo H($auth_code); ?>
<?php
}
?>
<?php if($show_comment_on_receipt==1){echo H($comment);} ?>

<?php if(!$this->config->item('hide_signature')) { ?>
<?php if ($signature_needed) {?>
			
<?php echo lang('sales_signature','',array(),TRUE); ?>: 
------------------------------------------------



<?php 
if ($is_credit_card_sale)
{
	echo lang('sales_card_statement','',array(),TRUE);
}
?><?php }?><?php } ?>
<?php  if ($return_policy) { echo wordwrap(H($return_policy),40);} ?></script>
<?php $this->load->view("partial/footer"); ?>
