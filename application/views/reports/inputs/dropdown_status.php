<div class="form-group">
	<?php echo form_label(lang('deliveries_status').':', 'deliveries_status', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?> 
	<div class="col-sm-9 col-md-2 col-lg-2">
		<?php 
		$dropdown_options['all'] = lang('common_all');
		$dropdown_options['not_scheduled'] = lang('deliveries_not_scheduled');
		$dropdown_options['scheduled'] = lang('deliveries_scheduled');
		$dropdown_options['shipped'] = lang('delivieries_shipped');
		$dropdown_options['delivered'] = lang('deliveries_delivered');
		echo form_dropdown('deliveries_status',$dropdown_options, $this->input->get('deliveries_status') ? $this->input->get('deliveries_status') : '', 'id="'.'deliveries_status'.'" class="form-control"'); 
		?>
	</div>
</div>