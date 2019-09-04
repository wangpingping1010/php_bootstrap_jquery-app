<?php $this->load->view("partial/header"); ?>
<div class="panel panel-piluku">
	<div class="panel-heading">
		<h3 class="panel-title">
			<?php echo lang('items_inventory_print_list'); ?>
		</h3>
		<div class="text-right"><?php echo anchor('items/inventory_print_list/1',lang('common_excel_export'));?></div>
	</div>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
				<thead>
					<tr>
						<th><?php echo lang('common_name')?></th>
						<th><?php echo lang('common_category')?></th>
						<th><?php echo lang('common_product_id')?></th>
						<th><?php echo lang('common_item_number')?></th>
						<th><?php echo lang('common_quantity')?></th>
					</tr>
				</thead>
					<tbody>
						<?php foreach($items as $row) { ?>
							<tr <?php echo $row['is_variation'] ? 'style="background-color: #eee;"' : '';?>>
								<td><?php echo $row['name'];?></td>
								<td><?php echo $this->Category->get_full_path($row['category_id']);?></td>
								<td><?php echo $row['product_id'];?></td>
								<td><?php echo $row['item_number'];?></td>
								<td><?php echo to_quantity($row['quantity']);?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
		</div>
	</div>
</div>
<script type='text/javascript'>

</script>
<?php $this->load->view("partial/footer"); ?>