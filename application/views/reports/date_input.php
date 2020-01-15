<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<div id="page_title"><?php echo $this->lang->line('reports_report_input'); ?></div>

		<?php
		if (isset($error)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
		}
		?>

		<?php echo form_open('#', array('id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class' => 'control-label col-xs-2 required')); ?>
			<div class="col-xs-3">
				<?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
			</div>
		</div>


		<?php
		if ($mode == 'sale') {
			?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class' => 'required control-label col-xs-2')); ?>

				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', array(
							'all' => $this->lang->line('reports_all'),
							'sales' => $this->lang->line('reports_sales'),
							'returns' => $this->lang->line('reports_returns')
						), 'all', array('id' => 'input_type', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Category", 'reports_sale_category_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('sale_category', $categories, 'all', array('id' => 'category', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Vatable", 'reports_sale_vat_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_vat' class="col-xs-3">
					<?php echo form_dropdown('sale_vat', $vatable, 'all', array('id' => 'vat', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => 'required control-label col-xs-2'));
					?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		} elseif ($mode == 'receiving') {
			?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_receiving_type'), 'reports_receiving_type_label', array('class' => 'required control-label col-xs-2')); ?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('receiving_type', array(
							'all' => $this->lang->line('reports_all'),
							'receiving' => $this->lang->line('reports_receivings'),
							'returns' => $this->lang->line('reports_returns'),
							//'requisitions' => $this->lang->line('reports_requisitions')

						), 'all', array('id' => 'input_type', 'class' => 'form-control')); ?>
				</div>
			</div>

		<?php
		} elseif ($mode == 'transfer') {
			?>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => 'required control-label col-xs-2'));
					?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'input_type', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		}
		?>

		<?php
		if ($mode == 'receiving') {


			?>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'employee', array('class' => 'required control-label col-xs-2'));
					?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		}
		?>
		<?php
		if (!empty($stock_locations) && count($stock_locations) > 1) {
			?>
			<div class="form-group form-group-sm">
				<?php

					if ($mode == "transfer") {
						echo form_label('Receiving Branch', 'reports_stock_location_label', array('class' => 'required control-label col-xs-2'));
					} else {
						echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class' => 'required control-label col-xs-2'));
					}

					?>
				<div id='report_stock_location' class="col-xs-3">
					<?php echo form_dropdown('stock_location', $stock_locations, 'all', array('id' => 'location_id', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		}
		?>

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

		$("#generate_report").click(function() {
			<?php
			if ($mode == 'receiving') {
				?>
				window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val(), $('#employee_id').val()].join("/");
			<?php
			} elseif ($mode == 'sale') {
				?>
				window.location = ["<?php echo site_url('reports/specific_employee');  ?>", start_date, end_date, $("#employee_id").val(), $("#input_type").val() || 0, $("#category").val(), $("#vat").val()].join("/");
			<?php
			} else {
				?>
				window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val()].join("/");

			<?php } ?>
		});
	});
</script>