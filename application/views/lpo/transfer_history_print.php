<?php $this->load->view("partial/header_print"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<?php
		if (isset($error_message)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error_message . "</div>";
			exit;
		}
		?>

		<?php $this->load->view('partial/print_receipt', array('print_after_sale', $print_after_sale, 'selected_printer' => 'receipt_printer')); ?>

		<div class="print_hide" id="control_buttons" style="text-align:right">
			<a href="javascript:printdoc();">
				<div class="btn btn-info btn-sm" , id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
			</a>
			<?php echo anchor("items/push", '<span class="glyphicon glyphicon-save">&nbsp</span> New Transfer', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
		</div>

		<div id="receipt_wrapper">
			<div id="receipt_header">
				<?php
				if ($this->config->item('company_logo') != '') {
				?>
					<div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
				<?php
				}
				?>

				<?php
				if ($this->config->item('receipt_show_company_name')) {
				?>
					<div id="company_name"><?php echo $this->config->item('company'); ?></div>
				<?php
				}
				?>

				<div id="company_address"><b><?php echo nl2br($this->config->item('address')); ?></b></div>
				<div id="company_phone"><b><?php echo $this->config->item('phone'); ?></b></div>
				<div id="sale_receipt"><b><?php echo $receipt_title; ?></b></div>
				<div id="sale_time"><b><?php echo $transaction_time ?></b></div>
			</div>

			<div id="receipt_general_info">
				<div class="clearfix">
					<div class="pull-right">


						<div id="customer"><?php echo "<b>TO</b>: " . $to_branch; ?></div>

					</div>

					<div class="pull-left">
						<div id="sale_id"><?php echo "<b>Transfer ID</b>: PUSH " . $transfer_id; ?></div>

						<div id="employee"><?php echo '<b>' . $user_role . "</b>: " . $employee; ?></div>

						<div id="customer"><?php echo "<b>Date</b>: " . $date; ?></div>

					</div>
				</div>
			</div>
			<!--end of general info-->
			<div class="table-responsive">
				<table id="receipt_items" class="table">
					<thead class="thead-light">
						<tr>

							<th style="width:10%; color: #495057; background-color: #e9ecef;border-color: #dee2e6;">S/N</th>
							<th style="width:40%; color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Product Name</th>
							<th style="width:20%; color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item Number</th>
							<th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Qty</th>

							<th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Price</th>
							<th style="width:10%;text-align:right;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Total</th>
						</tr>
					</thead>
					<?php
					$grand_total = 0;
					$sn = 1;
					foreach ($items as $line => $item) {
						$grand_total += $item->pushed_quantity * $item->transfer_price;
					?>
						<tr>
							<td><?php echo $sn++; ?></td>
							<td><?php echo $item->name; ?></td>
							<td><?php echo $item->item_number; ?></td>
							<td><?php echo to_quantity_decimals(abs($item->pushed_quantity)); ?>
							</td>
							<td><?php echo to_currency($item->transfer_price); ?></td>
							<!-- <td><?php //echo to_currency($item['unit_price']); 
										?></td> -->

							<td>
								<div class="total-value"><?php echo to_currency($item->pushed_quantity * $item->transfer_price); ?></div>
							</td>
						</tr>


					<?php
					}
					?>
					<tr>
						<th colspan="4" style='text-align:right;border-top:2px solid #000000;'>Grand Total</th>
						<td colspan="2" style='border-top:2px solid #000000;'>
							<div class="total-value"><?php echo to_currency(abs($grand_total)); ?></div>
						</td>
					</tr>

				</table>
			</div>
			<?php //print_r($cart)
			?>

			<div id='barcode'>
				<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
				<?php echo 'PUSH ' . $transfer_id; ?>
			</div>
		</div>
	</div>
</div>


<?php $this->load->view("partial/footer"); ?>