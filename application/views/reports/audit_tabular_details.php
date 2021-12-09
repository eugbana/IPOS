<?php $this->load->view("partial/header"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<!-- <div id="page_title">
			PRINT SALES REPORT

			<p style="font-size:16px;">
				<a href="<?php echo site_url('reports/print_filtered_report/' . $start . '/' . $end . '/' . $employee_id . '/' . $location_id . '/' . $sale_type . '/'  . $credit . '/' . $vatable . '/' . $customer_id . '/' . $discount . '/' . $payment_type); ?>">Print Sales Items</a> |
				<a href="<?php echo site_url('reports/print_filtered_report_items/' . $start . '/' . $end . '/' . $employee_id . '/' . $location_id  . '/' . $sale_type .  '/' . $credit . '/' . $vatable . '/' . $customer_id . '/' . $discount . '/' . $payment_type); ?>">Print Detailed Sales Items</a> |
				<a href="<?php echo site_url('reports/print_filtered_summary_report_items/' . $start . '/' . $end . '/' . $employee_id . '/' . $location_id . '/' . $sale_type .  '/' . $credit . '/' . $vatable . '/' . $customer_id . '/' . $discount . '/' . $payment_type); ?>">Print Sales Summary</a>

			</p>
		</div> -->

		<!-- <div>
			<?php
			foreach ($report_title_data as $key => $value) {
				echo '<div><b>' . $key . ': </b>' . $value . ' </div>';
			}
			?>
		</div> -->

		<div id="table_holder">
			<table id="table"></table>
		</div>

		<!-- <div id="report_summary">
			<?php
			foreach ($overall_summary_data as $name => $value) {
			?>
				<div class="summary_row"><?php echo $this->lang->line('reports_' . $name) . ': ' . to_currency($value); ?></div>
			<?php
			}
			?>
		</div> -->
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>
		?>
		// var init_dialog = function() {
		// 	<?php if (isset($editable)) : ?>
		// 		table_support.submit_handler('<?php echo site_url("reports/get_detailed_" . $editable . "_row") ?>');
		// 		dialog_support.init("a.modal-dlg");
		// 	<?php endif; ?>
		// };

		$('#table').bootstrapTable({
			columns: <?php echo transform_headers($headers['summary'], TRUE); ?>,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			striped: true,
			pagination: true,
			sortable: true,
			showColumns: true,
			uniqueId: 'id',
			showExport: true,
			data: <?php echo json_encode($summary_data); ?>,
			iconSize: 'sm',
			paginationVAlign: 'bottom',
			// detailView: true,

			escape: false,
			onPageChange: init_dialog,
			onPostBody: function() {
				dialog_support.init("a.modal-dlg");
			}
		});

		// init_dialog();
	});
</script>

<?php $this->load->view("partial/footer"); ?>