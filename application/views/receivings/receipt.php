<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}

$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');

$item_custom_fields_to_display = array();
$supplier_custom_fields_to_display = array();
$receiving_custom_fields_to_display = array();

for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
{
 $item_custom_field = $this->Item->get_custom_field($k,'show_on_receipt');
 $supplier_custom_field = $this->Supplier->get_custom_field($k,'show_on_receipt');
 $recv_custom_field = $this->Receiving->get_custom_field($k,'show_on_receipt');
 
 if ($recv_custom_field)
 {
 	$receiving_custom_fields_to_display[] = $k;
 }
 
 if ($item_custom_field)
 {
 	$item_custom_fields_to_display[] = $k;
 }
 
 if ($supplier_custom_field)
 {
 	 	$supplier_custom_fields_to_display[] = $k;
 }
 
}

?>

<div class="manage_buttons hidden-print">
	<div class="row">
		<div class="col-md-6">
			<span class="hidden-print search no-left-border">
				<ul class="list-inline print-buttons">
					<li></li>
					
					<?php 
					if ((empty($deleted) || (!$deleted))) { ?>
					
					<?php
					 if ($this->Employee->has_module_action_permission('receivings', 'edit_receiving', $this->Employee->get_logged_in_employee_info()->person_id)){
				   		$edit_recv_url = $suspended ? 'unsuspend' : 'change_recv';
						echo '<li>';
						echo form_open("receivings/$edit_recv_url/".$receiving_id_raw,array('id'=>'receivings_change_form')); ?>
						<button class="btn btn-primary btn-lg hidden-print" id="edit_recv" > <?php echo lang('receivings_edit','',array(),TRUE); ?> </button>
							</form>		
						</li>
				
					<?php }	
					
					}	?>
					<li>
						<button class="btn btn-primary btn-lg hidden-print" id="barcode_labels_button" onClick="window.location='<?php echo site_url('items/generate_barcodes_labels_from_recv/'.$receiving_id_raw); ?>'"; > <?php echo lang('common_barcode_labels','',array(),TRUE); ?> </button>						
					</li>
					<li>
						<button class="btn btn-primary btn-lg hidden-print" id="barcode_sheet_button" onClick="window.open('<?php echo site_url('items/generate_barcodes_from_recv/'.$receiving_id_raw); ?>','_blank');" > <?php echo lang('common_barcode_sheet','',array(),TRUE); ?> </button>						
					</li>
					<li>
						<button class="btn btn-primary btn-lg hidden-print" id="barcode_sheet_button" onClick="window.open('<?php echo site_url('reports/export_recv/'.$receiving_id_raw); ?>','_blank');" > <?php echo lang('common_excel_export','',array(),TRUE); ?> </button>						
					</li>
					<li>
						<?php if (!empty($supplier_email)) { ?>
							<?php echo anchor('receivings/email_receipt/'.$receiving_id_raw, $is_po ? lang('receivings_email_po','',array(),TRUE) : lang('common_email_receipt','',array(),TRUE), array('id' => 'email_receipt','class' => 'btn btn-primary btn-lg hidden-print'));?>
						<?php }?>
					</li>
					
				</ul>
			</span>
		</div>
		<div class="col-md-6">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<ul class="list-inline print-buttons">
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="print_button" onClick="do_print()" > <?php echo lang('common_print','',array(),TRUE); ?> </button>							
						</li>
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="new_receiving_button_1" onclick="window.location='<?php echo site_url('receivings'); ?>'" > <?php echo lang('receivings_new_receiving','',array(),TRUE); ?> </button>
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
					<div class="col-md-4 col-sm-4 col-xs-12">
						<ul class="list-unstyled invoice-address">
							<?php if($company_logo) {?>
								<li id="company_logo" class="invoice-logo">
									<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
								</li>
							<?php } ?>
							<li id="company_name"  class="company-title"><?php echo H($company); ?></li>
							<li id="company_address" class="nl2br"><?php echo H($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
							<li id="company_phone"><?php echo H($this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
							<li id="sale_receipt"><?php echo H($is_po ? lang('receivings_purchase_order','',array(),TRUE) : $receipt_title); ?></li>
							<li id="sale_time"><?php echo H($transaction_time); ?></li>
						</ul>
					</div>
					<!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-detail">
										<?php if (!isset($transfer_to_location)) {?>
											<li id="sale_id"><span><?php echo $is_po ? lang('receivings_purchase_order','',array(),TRUE) : lang('receivings_id','',array(),TRUE).": "; ?></span><?php echo $is_po ? H($receiving_id_raw) : H($receiving_id); ?></li>
											<?php } else { 
												?>
											<li id="sale_id"><span><?php echo lang('receivings_transfer_id','',array(),TRUE).": "; ?></span><?php echo H($receiving_id_raw); ?></li>
											<?php
											} ?>
							<li id="employee"><span><?php echo lang('common_employee','',array(),TRUE).": "; ?></span><?php echo H($employee); ?></li>
			            </ul>
			        </div>
			        <?php if(isset($supplier) || isset($transfer_to_location)) { ?>
			        <div class="col-md-4 col-sm-4 col-xs-12">
						<ul class="list-unstyled invoice-address invoiceto">
							<?php if(isset($supplier)) { ?>
								<li id="supplier"><?php echo lang('common_supplier','',array(),TRUE).": ".H($supplier); ?></li>
								<?php if(!empty($supplier_address_1)){ ?><li><?php echo lang('common_address','',array(),TRUE); ?> : <?php echo H($supplier_address_1. ' '.$supplier_address_2); ?></li><?php } ?>
								<?php if (!empty($supplier_city)) { echo '<li>'.H($supplier_city.' '.$supplier_state.', '.$supplier_zip).'</li>';} ?>
								<?php if (!empty($supplier_country)) { echo '<li>'.H($supplier_country).'</li>';} ?>			
								<?php if(!empty($supplier_phone)){ ?><li><?php echo lang('common_phone_number','',array(),TRUE); ?> : <?php echo H($supplier_phone); ?></li><?php } ?>
								<?php if(!empty($supplier_email)){ ?><li><?php echo lang('common_email','',array(),TRUE); ?> : <?php echo H($supplier_email); ?></li><?php } ?>
								
								<?php
								foreach($supplier_custom_fields_to_display as $custom_field_id)
								{
								?>
										<?php 
									
											$supplier_info = $this->Supplier->get_info($supplier_id);
										
											if ($supplier_info->{"custom_field_${custom_field_id}_value"})
											{
											?>
	                  	<div class="invoice-desc">
											<?php

											if ($this->Supplier->get_custom_field($custom_field_id,'type') == 'checkbox')
											{
												$format_function = 'boolean_as_string';
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'date')
											{
												$format_function = 'date_as_display_date';				
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'email')
											{
												$format_function = 'strsame';					
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'url')
											{
												$format_function = 'strsame';					
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'phone')
											{
												$format_function = 'strsame';					
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'image')
											{
												$this->load->helper('url');
												$format_function = 'file_id_to_image_thumb_right';					
											}
											elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'file')
											{
												$this->load->helper('url');
												$format_function = 'file_id_to_download_link';					
											}
											else
											{
												$format_function = 'strsame';
											}
										
											echo '<li><span>'.lang('common_supplier','',array(),TRUE).' '.($this->Supplier->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Supplier->get_custom_field($custom_field_id,'name').':').'</span> '.$format_function($supplier_info->{"custom_field_${custom_field_id}_value"}).'</li>';
											?>
										</div>
										<?php
									}
								}
								?>
								
								
								
							<?php } ?>
							<?php if(isset($transfer_to_location)) { ?>
								<li id="transfer_from"><span><?php echo lang('receivings_transfer_from','',array(),TRUE).': ' ?></span><?php echo H($transfer_from_location); ?></li>
								<li id="transfer_to"><span><?php echo lang('receivings_transfer_to','',array(),TRUE).': ' ?></span><?php echo H($transfer_to_location); ?></li>
							<?php } ?>
						</ul>
			        </div>
			        <?php } ?>
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
									
									<?php
									if (!$this->config->item('hide_all_prices_on_recv')) {
									?>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
			                <div class="invoice-head text-right item-price"><?php echo lang('common_price','',array(),TRUE); ?></div>
			            </div>
									<?php } ?>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head text-right item-qty"><?php echo lang('common_quantity','',array(),TRUE); ?></div>
			            </div>
									<?php
									if (!$this->config->item('hide_all_prices_on_recv')) {
									?>
									
									<?php if($discount_exists) { ?>
				            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
				                <div class="invoice-head text-right item-discount"><?php echo lang('common_discount_percent','',array(),TRUE); ?></div>
				            </div>
			            <?php } ?>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head pull-right item-total gift_receipt_element"><?php echo lang('common_total','',array(),TRUE); ?></div>
			            </div>
									<?php } ?>
			        </div>
			    </div>
			    <?php 
					$number_of_items_sold = 0;
					$number_of_items_returned = 0;
					
					foreach(array_reverse($cart_items, true) as $line=>$item) { ?>
					
					<?php
					
				 if ($item->quantity > 0 && $item->name != lang('common_store_account_payment','',array(),TRUE) && $item->name != lang('common_discount','',array(),TRUE) && $item->name != lang('common_refund','',array(),TRUE) && $item->name != lang('common_fee','',array(),TRUE))
				 {
			 		 $number_of_items_sold = $number_of_items_sold + $item->quantity;
				 }
				 elseif ($item->quantity < 0 && $item->name != lang('common_store_account_payment','',array(),TRUE) && $item->name != lang('common_discount','',array(),TRUE) && $item->name != lang('common_refund','',array(),TRUE) && $item->name != lang('common_fee','',array(),TRUE))
				 {
			 		 $number_of_items_returned = $number_of_items_returned + abs($item->quantity);
				 }
					
					
					
					$item_number_for_receipt = false;
					
					if ($this->config->item('show_item_id_on_recv_receipt'))
					{
						switch($this->config->item('id_to_show_on_sale_interface'))
						{
							case 'number':
							$item_number_for_receipt = array_key_exists('item_number', $item) ? H($item->item_number) : '';
							break;
						
							case 'product_id':
							$item_number_for_receipt = array_key_exists('product_id', $item) ? H($item->product_id) : ''; 
							break;
						
							case 'id':
							$item_number_for_receipt = array_key_exists('item_id', $item) ? H($item->item_id) : ''; 
							break;
						
							default:
							$item_number_for_receipt = array_key_exists('item_number', $item) ? H($item->item_number) : '';
							break;
						}
					}
					?>
				    <!-- invoice items-->
				    <div class="invoice-table-content">
				        <div class="row">
			            	<div class="<?php echo $this->config->item('wide_printer_receipt_format') ? 'col-md-'.$x_col . ' col-sm-' .$x_col . ' col-xs-'.$x_col : 'col-md-12 col-sm-12 col-xs-12' ?>">
				                <div class="invoice-content invoice-con">
				                    <div class="invoice-content-heading"><?php echo H($item->name); ?><?php if ($item->size){ ?> (<?php echo H($item->size); ?>)<?php } ?></div>
														
														<?php if ($item_number_for_receipt){ ?>
														<div class="invoice-desc">
															<?php 
																echo $item_number_for_receipt;
																?>
														</div>
															
														<?php } ?>
														
														<div class="invoice-desc">
															<?php 
																echo $item->variation_name ? H($item->variation_name) : '';
															?>
														</div>
									<?php if (!$this->config->item('hide_desc_on_receipt') && !$item->description=="" ) {?>
				                    	<div class="invoice-desc"><?php echo H($item->description); ?></div>
				                    <?php } ?>
									<?php if (isset($item->serialnumber) && $item->serialnumber !="") { ?>
				                    	<div class="invoice-desc"><?php echo H($item->serialnumber); ?></div>
									<?php } ?>
									
									<?php
									foreach($item_custom_fields_to_display as $custom_field_id)
									{
									?>
											<?php 
											if(get_class($item) == 'PHPPOSCartItemRecv' && $this->Item->get_custom_field($custom_field_id) !== false)
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
									}?>
				                </div>
				            </div>
										
										<?php
										if (!$this->config->item('hide_all_prices_on_recv')) {
										?>
										
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
				                <div class="invoice-content item-price text-right"><?php echo to_currency($item->unit_price,10); ?></div>
				            </div>
										<?php } ?>
			            <div class="col-md-<?php echo $xs_col; ?> col-sm-<?php echo $xs_col; ?> col-xs-<?php echo $xs_col; ?> gift_receipt_element">
				                <div class="invoice-content item-qty text-right"><?php echo to_quantity($item->quantity); ?></div>
				            </div>
							
							<?php
							if (!$this->config->item('hide_all_prices_on_recv')) {
							?>
							
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
													
													<?php echo to_currency($item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100,10); ?>
												
												</div>
			            	</div>
										<?php } ?>
										
				        </div>					
				    </div>
			    <?php } ?>
					
					<?php
					foreach($receiving_custom_fields_to_display as $custom_field_id)
					{
						if($this->Receiving->get_custom_field($custom_field_id) !== false && $this->Receiving->get_custom_field($custom_field_id) !== false)
						{											
								if ($cart->{"custom_field_${custom_field_id}_value"})
								{
								?>						
								<?php

								if ($this->Receiving->get_custom_field($custom_field_id,'type') == 'checkbox')
								{
									$format_function = 'boolean_as_string';
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'date')
								{
									$format_function = 'date_as_display_date';				
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'email')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'url')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'phone')
								{
									$format_function = 'strsame';					
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'image')
								{
									$this->load->helper('url');
									$format_function = 'file_id_to_image_thumb_right';					
								}
								elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'file')
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
											 if (!$this->Receiving->get_custom_field($custom_field_id,'hide_field_label'))
											 {
												 echo $this->Receiving->get_custom_field($custom_field_id,'name');
											 }
											 else
											 {
											 	echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
											 }
										 
			         			 ?></div>
												<div class="invoice-desc"><?php 
													if (!$this->Receiving->get_custom_field($custom_field_id,'hide_field_label'))
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
				 
					<div class="row">
			            <div class="col-md-12 col-sm-12 col-xs-12">
			                <div class="text-center">
										<?php echo H($comment); ?>
			                </div>
			            </div>
			        </div>
			    </div>
				 
			    <div class="invoice-footer panel-pad">
						
						<?php
						if (!$this->config->item('hide_all_prices_on_recv')) {
						?>
						
			    	<?php if (!empty($taxes)) {?>
				        <div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading sub-total-heading"><?php echo lang('common_sub_total','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value"><?php echo to_currency($subtotal); ?></div>
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
						            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
						                <div class="invoice-footer-heading tax-heading"><?php echo lang('common_tax','',array(),TRUE); ?></div>
						            </div>
						            <div class="col-md-2 col-sm-2 col-xs-4">
						                <div class="invoice-footer-value"><?php echo to_currency($total_tax); ?></div>
						            </div>
						        </div>						
						<?php }else {?>
							<?php foreach($taxes as $name=>$value) { ?>
								<div class="row">
						            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
						                <div class="invoice-footer-heading tax-heading"><?php echo H($name); ?></div>
						            </div>
						            <div class="col-md-2 col-sm-2 col-xs-4">
						                <div class="invoice-footer-value"><?php echo to_currency($value); ?></div>
						            </div>
						        </div>
							<?php } ?>
						<?php } ?>
				    <?php } ?>
				    <div class="row">
			            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-heading total-heading"><?php echo lang('common_total','',array(),TRUE); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value"><?php echo to_currency($total); ?></div>
			            </div>
			        </div>
							<?php } //End hide all prices on recv?>
				        <div class="row">
							<?php if ($number_of_items_sold) { ?>
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo lang('common_items_purchased','',array(),TRUE); ?></div>
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
							if (!$this->config->item('hide_all_prices_on_recv')) {
							?>
								
			        <?php
						foreach($payments as $payment_id=>$payment)
						{ 
					?>
						<div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-4 col-sm-4 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-4 col-xs-4">
								<div class="invoice-footer-value"><?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?></div>																				
				            </div>
							
				            <div class="col-md-2 col-sm-2 col-xs-4">
								<div class="invoice-footer-value invoice-payment"><?php echo to_currency($payment->payment_amount); ?></div>
				            </div>							
						</div>
					<?php
						}
					?>
										
			        <?php if(isset($amount_change)) { ?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('common_amount_tendered','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value"><?php echo to_currency($amount_tendered); ?></div>
				            </div>
				        </div>
				        <div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('common_change_due','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value"><?php echo H($amount_change); ?></div>
				            </div>
				        </div>
					<?php } ?>
					<?php } //end hide all prices recv?>
					
					<?php if (isset($supplier_balance_for_sale) && (double)$supplier_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) {?>
					
						<div class="row">						
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-48">
				                <div class="invoice-footer-value"><?php echo lang('receivings_supplier_account_balance','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-payment"><?php echo to_currency($supplier_balance_for_sale); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					
			    </div>
								
				
			    <!-- invoice footer -->
			    <div class="row">
			        <div class="col-md-12 col-sm-12 col-xs-12">
			            <?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
				            <div class="invoice-policy" id="barcode">
				            	<?php echo "<img src='".site_url('barcode/index/svg')."?barcode=$receiving_id&text=$receiving_id' />"; ?>
				            </div>
				        <?php } ?>
			        </div>
			    </div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>
<script>
html2canvas(document.querySelector("#receipt_wrapper"),{windowWidth: 280}).then(canvas => {
	document.getElementById("print_image_output").innerHTML = canvas.toDataURL();
});
</script>
<script type="text/print-image" id="print_image_output"></script>

<script type="text/javascript">

$("#edit_recv").click(function(e)
{
	e.preventDefault();
	bootbox.confirm(<?php echo json_encode(lang('receivings_edit_confirm','',array(),TRUE)); ?>, function(result)
	{
		if (result)
		{
			$("#receivings_change_form").submit();
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

<?php if ($this->config->item('print_after_receiving') && $this->uri->segment(2) == 'complete')
{
?>
$(window).load(function()
{
	<?php
	if ($this->agent->browser() == 'Chrome')
	{
	?>
		setTimeout(function(){ do_print(); }, 1500);
	<?php	
	}
	else
	{
	?>
		do_print();	
	<?php
	}
	?>
});
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
	 		window.location = '<?php echo site_url('receivings'); ?>';	
		}		
		<?php
	}
}
?>

function do_print()
{	
	window.print();
	<?php
	if($this->config->item('redirect_to_sale_or_recv_screen_after_printing_receipt'))
	{
		if ($this->agent->browser() == 'Safari' || $this->agent->browser() == 'Firefox')
		{
		?>
		window.location = '<?php echo site_url('receivings'); ?>';
		<?php
		}
	}
	?>
}
</script>
