
			<div id="footers" class="col-md-12 hidden-print text-center">
				<?php echo lang('common_please_visit_my'); ?> 
					<a tabindex="-1" href="http://phppointofsale.com" target="_blank"><?php echo lang('common_website'); ?></a> <?php echo lang('common_learn_about_project'); ?>.
					<span class="text-info"><?php echo lang('common_you_are_using_phppos')?> <span class="badge bg-primary"> <?php echo APPLICATION_VERSION; ?></span></span> <?php echo lang('common_built_on'). ' '.BUILT_ON_DATE;?>
			</div>
		</div>
		<!---content -->
	</div>		
</body>
<?php
if (($this->uri->segment(1) == 'sales' || $this->uri->segment(1) == 'receivings'))
{	
?>
	<script>
		
	function getBodyScrollTop () 
	{ 
		var el = document.scrollingElement || document.documentElement;
		
		return el.scrollTop; 
	}

	$(window).on("beforeunload", function() {
		
			var scroll_top = 
	    $.ajax(<?php echo json_encode(site_url('home/save_scroll')); ?>, {
	        async: false,
	        data: {scroll_to: getBodyScrollTop()}
	    });
	});
	</script>
	<?php
	if ($this->session->userdata('scroll_to'))
	{
		?>
		<script>
		$([document.documentElement, document.body]).animate({
			scrollTop: <?php echo json_encode($this->session->userdata('scroll_to')); ?>
		    }, 100);
				</script>
		<?php
		$this->session->unset_userdata('scroll_to');
	}
}
?>
</html>