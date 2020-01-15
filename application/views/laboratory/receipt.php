<?php $this->load->view("partial/header_print"); ?>

<?php
if (isset($error_message)) {
	echo "<div class='alert alert-dismissible alert-danger'>" . $error_message . "</div>";
	exit;
}
?>

<?php if (!empty($customer_email)) : ?>
	<script type="text/javascript">
		$(document).ready(function() {
			var send_email = function() {
				$.get('<?php echo site_url() . "/sales/send_receipt/" . $sale_id_num; ?>',
					function(response) {
						$.notify(response.message, {
							type: response.success ? 'success' : 'danger'
						});
					}, 'json'
				);
			};

			$("#show_email_button").click(send_email);

			<?php if (!empty($email_receipt)) : ?>
				send_email();
			<?php endif; ?>
		});
	</script>
<?php endif; ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<?php $this->load->view('partial/print_receipt', array('print_after_sale' => $print_after_sale, 'selected_printer' => 'receipt_printer')); ?>

		<div class="print_hide" id="control_buttons" style="text-align:right">
			<div><select class="custom-select" id="service_challenge" name="service_challenge">
					<option value="header">With Header</option>
					<option value="headout">Without Header</option>
				</select></div>
			<a href="javascript:printdoc();">
				<div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
			</a>
			<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
			<?php if (isset($customer_email) && !empty($customer_email)) : ?>
				<a href="javascript:void(0);">
					<div class="btn btn-info btn-sm" id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . 'Email Receipt'; ?></div>
				</a>
			<?php endif; ?>
			<a href="<?php echo site_url("/"); ?>">
				<div class="btn btn-info btn-sm"><?php echo '<span class="glyphicon glyphicon-new-window">&nbsp</span>' . 'Dashboard'; ?></div>
			</a>
		</div>

		<?php $this->load->view("laboratory/receipt_result"); ?>
	</div>
</div>
<script>
	$(document).on('input change', '#service_challenge', function() {
		//$('.input-range').html($(this).val());
		//var value_change=$(this).val();
		//alert($(this).val());
		if (this.value == "header") {
			$('#receipt_header').css("display", "block");
		} else {
			$('#receipt_header').css("display", "none");
		}
	});
</script>

<?php $this->load->view("partial/footer"); ?>