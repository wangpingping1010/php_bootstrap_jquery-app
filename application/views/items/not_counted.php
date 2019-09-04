<?php $this->load->view("partial/header"); ?>
<div class="container-fluid">
	<div class="row manage-table">
		<div class="panel panel-piluku">
			<div class="panel-heading hidden-print">
				<button class="btn btn-primary text-white hidden-print print_button pull-left" onclick="window.location='<?php echo site_url('items/do_count/'.$count_id); ?>'"> &laquo; <?php echo lang('common_back'); ?> </button>
				
				<h3 class="panel-title">
						<div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
							<?php echo $pagination;?>		
						</div>
					</span>
				</h3>
				<button class="btn btn-primary text-white hidden-print print_button pull-right" onclick="window.print();"> <?php echo lang('common_print'); ?> </button>
				
			<?php
			$categories = array();
			$categories[''] =lang('common_all').' '.lang('reports_categories');

			$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());

			foreach($categories_phppos as $key=>$value)
			{
				$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
				$categories[$key] = $name;
			}
			
			?>
			<br /><br />
				<form action="" method="get" id="filter_form" style="display: inline;">
						<?php echo form_dropdown('category',$categories, $this->input->get('category'), 'id="category" class=""'); ?>
					</form>
			</div>
			<div class="panel-body nopadding table_holder table-responsive" >
				<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
					<thead>
						<tr>
							<?php foreach ($headers as $header) { ?>
							<th align="<?php echo $header['align'];?>"><?php echo $header['data']; ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data as $row) { ?>
						<tr>
							<?php foreach ($row as $cell) { ?>
							<td align="<?php echo $cell['align'];?>"><?php echo $cell['data']; ?></td>
							<?php } ?>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				
			</div>		
			
		</div>
	</div>

<div class="row pagination hidden-print alternate text-center" id="pagination_bottom" >
	<?php echo $pagination;?>
</div>

</div>
<?php $this->load->view("partial/footer"); ?>
<script>
$("#category").change(function()
{
	$("#filter_form").submit();
});
$("#category").select2({dropdownAutoWidth : true});
</script>