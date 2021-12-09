<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<?php
if (isset($error)) {
	echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
}
?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<div class="row">


			<div class="col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span><?php echo $this->lang->line('reports_detailed_reports'); ?></h3>
					</div>
					<div class="list-group">
						<?php
						$person_id = $this->session->userdata('person_id');
						//show_report_if_allowed('detailed', 'sales', $person_id);

						show_report_if_allowed('detailed', 'receivings', $person_id);
						//show_report_if_allowed('specific', 'customer', $person_id, 'reports_customers');
						//show_report_if_allowed('specific', 'discount', $person_id, 'reports_discounts');
						//show_report_if_allowed('specific', 'employee', $person_id, 'reports_employees');
						//show_report_if_allowed('specific', 'expiry', $person_id, 'reports_expiry');
						?>
						<a class="list-group-item" href="<?php echo site_url('reports/detailed_sales'); ?>">Sales</a>
						<a class="list-group-item" href="<?php echo site_url('reports/detailed_transfers'); ?>">Transfers</a>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>