<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
                <!-- Start content -->
                <div class="content">

		<div id="page_title"><?php echo $this->lang->line('reports_report_input'); ?></div>

		<?php
		if(isset($error))
		{
			echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
		}
		?>

		<?php echo form_open('#', array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class'=>'control-label col-xs-2 required')); ?>
				<div class="col-xs-3">
						<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker1')); ?>
				</div>
			</div>

			

			<?php 
			echo form_button(array(
					'name'=>'generate_report',
					'id'=>'generate_report',
					'content'=>$this->lang->line('common_submit'),
					'class'=>'btn btn-primary btn-sm')
			);
			?>
		<?php echo form_close(); ?>
		
	</div>
			</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	<?php $this->load->view('partial/daterangepicker1'); ?>

	$("#generate_report").click(function()
	{
		window.location = [window.location, start_date, end_date, $('#specific_input_data').val(), $("#input_type").val() || 0].join("/");
	});
});
</script>
