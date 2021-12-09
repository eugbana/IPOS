<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<div id="page_title"><?php echo 'Sales Markup Report Parameters'; //$this->lang->line('reports_report_input'); 
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

        <div class="form-group form-group-sm">
			<?php echo form_label('Markup Range', 'report_markup_label', array('class' => 'control-label col-xs-2 ')); ?>
			(negative values allowed, e.g -11)
			<div class="col-xs-1">
				<?php echo form_input(array('name' => 'start_markup', 'id' => 'start_markup', 'class' => 'form-control input-sm', 'placeholder' => 'Start', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
			</div>

            <div class="col-xs-1">
				<?php echo form_input(array('name' => 'end_markup', 'id' => 'end_markup', 'class' => 'form-control input-sm',  'placeholder' => 'End', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
			</div>
		</div>

			<!-- <div class="form-group form-group-sm">
				<?php echo form_label("Category", 'reports_sale_category_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('sale_category', $categories, 'all', array('id' => 'category', 'class' => ' form-control')); ?>
				</div>
			</div> -->

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', array(
						'all' => $this->lang->line('reports_all'),
						'sales' => $this->lang->line('reports_sales'),
						'returns' => $this->lang->line('reports_returns')
					), 'all', array('id' => 'sale_type', 'class' => 'form-control')); ?>
				</div>
			</div>
			<!-- <div class="form-group form-group-sm">
				<?php echo form_label("Category", 'reports_sale_category_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('sale_category', $categories, 'all', array('id' => 'category', 'class' => ' form-control')); ?>
				</div>
			</div> -->
			<div class="form-group form-group-sm">
				<?php echo form_label("VATed", 'reports_sale_vat_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_vat' class="col-xs-3">
					<?php echo form_dropdown('sale_vat', $vatable, 'all', array('id' => 'vat', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Payment Type", 'reports_sale_payment_type_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_payment_type' class="col-xs-3">
					<?php echo form_dropdown('payment_type', $payment_types, 'all', array('id' => 'payment_type', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Discounted", 'reports_sale_discount_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_discount' class="col-xs-3">
					<?php echo form_dropdown('sale_discount', array('all' => 'All', 'YES' => 'YES', 'NO' => 'NO'), 'all', array('id' => 'discount', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Credit", 'reports_sale_credit_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_credit' class="col-xs-3">
					<?php echo form_dropdown('sale_credit', array('all' => 'All', 'YES' => 'YES', 'NO' => 'NO'), 'all', array('id' => 'credit', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Customer', 'reports_sale_customer_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_customer_type' class="col-xs-3">
					<?php echo form_dropdown('customer_id', $customer, 'all', array('id' => 'customer_id', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
			<input type="hidden" name="stock_location" value="<?=$current_location?>"/>
				<?php


				// echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class' => ' control-label col-xs-2'));


				?>
				<div id='report_stock_location' class="col-xs-3">
					<?php 
					// echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control')); ?>
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


        $("#generate_report").click(function() {
			window.location = ["<?php echo site_url('reports/display_sales_markup_report');  ?>", start_date, end_date, $("#start_markup").val(), $("#end_markup").val(), $("#employee_id").val(), $("#location_id").val(), $("#sale_type").val() || 0, $("#credit").val(), $("#vat").val(), $("#customer_id").val(), $("#discount").val(), $("#payment_type").val()].join("/");
            // window.location = ["<?php echo site_url('reports/display_data');  ?>", start_date, end_date, $("#employee_id").val(), $("#location_id").val(), $("#sale_type").val() || 0, $("#credit").val(), $("#vat").val(), $("#customer_id").val(), $("#discount").val(), $("#payment_type").val()].join("/");
        });

		
	});
</script>