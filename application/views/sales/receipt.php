<?php
//var_dump($this->_ci_cached_vars);
//eixt(0);
$this->load->view("partial/header_print"); ?>

<style>
	table {
		table-layout: fixed;
	}

	td {
		word-wrap: break-word;
	}

	#receipt_wrapper {
		width: 100% !important;
	}

	th.second-col,
	th.forth-col,
	td.second-col,
	td.forth-col {
		width: 20%;
	}

	th.first-col,
	td.first-col {
		width: 50%;
	}

	th.third-col,
	td.third-col {
		width: 10%;
	}

	th.third-col,
	th.first-col,
	th.second-col,
	th.forth-col,
	td.third-col,
	td.first-col,
	td.second-col,
	td.forth-col {
		/* word-wrap: break-word; */
		text-align: justify;
		text-justify: inter-word;
	}
</style>
<style type="text/css">
	.avoid {
		page-break-inside: avoid !important;
		margin: 4px 0 4px 0;
		/* to keep the page break from cutting too close to the text in the div */
	}
</style>
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
			<a href="javascript:printdoc();">
				<div class="btn btn-info btn-sm"  id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
			</a>
			<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
			<?php if (isset($customer_email) && !empty($customer_email)) : ?>
				<a href="javascript:void(0);">
					<div class="btn btn-info btn-sm"  id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . $this->lang->line('sales_send_receipt'); ?></div>
				</a>
			<?php endif; ?>
			<?php echo anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_register'), array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
			<?php //echo anchor("sales/manage", '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . $this->lang->line('sales_takings'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_takings_button')); 
			?>
		</div>

		<?php $this->load->view("sales/" . $this->config->item('receipt_template')); ?>

	</div>
</div>

<script type="text/javascript">
	window.onafterprint = (event) => {
		 window.location = '<?=base_url("sales")?>';
	};
</script>

<?php $this->load->view("partial/footer"); ?>
