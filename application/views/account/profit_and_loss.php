<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<div id="page_title"><?php echo 'Profit & Loss Report Parameters'; //$this->lang->line('reports_report_input'); 
								?></div>

		<?php
		if (isset($error)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
		}
		?>

		<?php echo form_open('#', array('id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
			<div class="col-xs-3">
				<?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
			</div>
		</div>


		<!-- <div class="form-group form-group-sm">
			<?php //echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
			<div class="col-xs-3">
				<?php //cho form_input(array('name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
			</div>
		</div> -->

		
			<div class="form-group form-group-sm">
				<?php echo form_label('Report Type', 'reports_sale_type_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', array(
						'monthly' => 'Monthly',
						'quaterly' => 'Quaterly',
						'yearly' => 'Yearly',
					), 'monthly', array('id' => 'type', 'class' => 'form-control')); ?>
				</div>
			</div>

		<?php
		echo form_button(
			array(
				'name' => 'generate_report',
				'id' => 'generate_report',
				'content' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm'
			)
		);
		?>
		<?php echo form_close(); ?>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		console.log('dffff')
		<?php $this->load->view('partial/daterangepicker'); ?>

		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#item").autocomplete({
			source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				//window.alert(ui.item.value); this will show the item_id of the selected item
				$("#add_item_form").submit();
				return false;
			}
		});

		$('#item').focus();

		$('#item').keypress(function(e) {
			if (e.which == 13) {
				$('#add_item_form').submit();
				return false;
			}
		});

		$('#item').blur(function() {
			$(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
		});



		//customers search
		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#customer_id").autocomplete({
			//source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			source: '<?php echo site_url("sales/customer_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				console.log(ui);
				// window.alert(ui.item.value); //this will show the item_id of the selected item
				// $("#add_item_form").submit();
				return false;
			}
		});

		// $('#customer_id').focus();

		$('#customer_id').keypress(function(e) {
			if (e.which == 13) {
				$(this).val(ui.item.value);
				// $('#add_item_form').submit();
				return false;
			}
		});

		$('#customer_id').blur(function() {
			// $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
			// $(this).val("all");
		});

		//suppliers search
		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#supplier").autocomplete({
			//source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			source: '<?php echo site_url("sales/supplier_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				console.log(ui);
				// window.alert(ui.item.value); //this will show the item_id of the selected item
				// $("#add_item_form").submit();
				return false;
			}
		});

		// $('#customer_id').focus();

		$('#supplier').keypress(function(e) {
			if (e.which == 13) {
				$(this).val(ui.item.value);
				// $('#add_item_form').submit();
				return false;
			}
		});

		$('#supplier').blur(function() {
			// $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
			// $(this).val("all");
		});



		$("#generate_report").click(function() {
				window.location = ["<?php echo site_url('profit_and_loss/get_data');  ?>", start_date, end_date, $("#type").val()].join("/");
			
		});
	});
</script>