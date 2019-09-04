<script type="text/print" id="print_output">-</script>
<?php $this->load->view("partial/header"); ?>
<h2 class="text-center hidden-print"><?php echo lang('sales_printing_opens_cash_drawer');?></h2>
<div class="text-center">
	<button class="btn btn-primary btn-lg hidden-print" id="print_button" onclick="print_pop()" > <?php echo lang('common_print'); ?> </button>		
</div>

<script type="text/javascript">
function print_pop()
{
	window.print();
	setTimeout(window.close, 0);
}

$(window).bind("load", function() {
	print_pop();
});


</script>



<?php $this->load->view("partial/footer"); ?>
