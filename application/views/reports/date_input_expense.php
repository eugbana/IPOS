<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<div id="page_title"><?php echo 'Expense Account Report Parameters'; //$this->lang->line('reports_report_input'); 
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
				<?php echo form_label("Category ", 'reports_sale_category_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('category', $categories, 'all', array('id' => 'category', 'class' => ' form-control')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label("Employees", 'reports_sale_suppliers_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('employee', $employees, 'all', array('id' => 'employee', 'class' => ' form-control')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label("Category Type", 'reports_presc_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_sale_vat' class="col-xs-3">
					<?php echo form_dropdown('expense_category_type', $expense_type, 'all', array('id' => 'expense_category_type', 'class' => ' form-control')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label("Type", 'reports_presc_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_sale_vat' class="col-xs-3">
					<?php echo form_dropdown('types', $type, 'all', array('id' => 'type', 'class' => ' form-control')); ?>
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

		$("#dept").on("change", function() {
			$("#dept_check").submit();
		});


		$("#generate_report").click(function() {
            window.location = ["<?php echo site_url('reports/display_expense');  ?>", start_date, end_date, $("#category").val(), $("#employee").val(), $("#type").val(), $("#expense_category_type").val(), $("#location_id").val()].join("/");
        });
	});
</script>