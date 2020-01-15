<div id="receipt_wrapper" style="width:100% !important;">
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

		<b>
			<div id="company_address"><?php echo nl2br($branch_address); ?></div>
			<div id="company_phone"><?php echo $branch_number; ?></div>
			<div id="sale_receipt"><?php echo $receipt_title; ?></div>
			<div id="sale_time"><?php echo $transaction_time ?></div>
		</b>

	</div>

	<div id="receipt_general_info">
		<?php
		if (isset($transaction_type)) {
			?>
			<div id="customer"><b><?php echo 'Transaction' . ": " . $transaction_type; ?></b></div>
		<?php
		}
		?>
		<?php
		if (isset($customer)) {
			?>
			<div id="customer"><b><?php echo $this->lang->line('customers_customer') . ": " . $customer; ?></b></div>
		<?php
		}
		?>

		<div id="sale_id"><b><?php echo $this->lang->line('sales_id') . ": " . $sale_id; ?></b></div>

		<?php
		if (!empty($invoice_number)) {
			?>
			<div id="invoice_number"><b><?php echo $this->lang->line('sales_invoice_number') . ": " . $invoice_number; ?></b></div>
		<?php
		}
		?>

		<div id="employee"><b><?php echo $user_role . ": " . $employee; ?></b></div>

		<?php if (isset($mode)) { ?>
			<div id="employee"><b><?php echo "Mode : " . ucfirst($mode); ?></b></div>
		<?php } ?>
		<div id="employee"><b>Date: <?php echo $transaction_date; ?></b></div>
	</div>
	<?php // print_r($cart);
	?>
	<table id="receipt_items">
		<tr>

			<th class="first-col" style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><strong><?php echo $this->lang->line('sales_description_abbrv'); ?></strong></th>
			<th class="second-col" style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><strong><?php echo $this->lang->line('sales_price'); ?></strong></th>
			<th class="third-col" style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><strong><?php echo $this->lang->line('sales_quantity'); ?></strong></th>
			<th class="total-value forth-col" style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><strong><?php echo $this->lang->line('sales_total'); ?></strong></th>

		</tr>
		<?php
		foreach ($cart as $line => $item) {
			?>
			<?php if ($item['reference'] == 0) { //reference 0 means item is not pill reminder 
					?>
				<tr>
					<td class="first-col">
						<?php echo ucfirst($item['name']); ?>


					</td>
					<td class="second-col">
						<?php echo to_currency($item['price']); ?>


					</td>
					<td class="third-col">
						<?php echo to_quantity_decimals(abs($item['quantity'])) . ((strtolower($item['qty_selected']) == 'wholesale') ? '(' . $item['qty_selected'] . ')' : ""); ?>


					</td>
					<td class="total-value forth-col">
						<?php echo to_currency(abs($item['total'])); ?>


					</td>
				</tr>
				<?php foreach ($cart as $let => $unline) { ?>
					<?php if ($unline['item_id'] == $item['item_id'] && $unline['reference'] == 1) { ?>
						<tr>
							<td><b><?php echo ucfirst($unline['name'] . ' ' . 'reminder'); ?></b></td>
							<td><?php echo to_currency($unline['price']); ?></td>
							<td><?php echo ''; ?></td>
							<td class="total-value"><b><?php echo to_currency($unline[($this->config->item('receipt_show_total_discount') ? 'total' : 'discounted_total')]); ?></b></td>
						</tr>
					<?php } ?>
				<?php } ?>





			<?php } ?>

			<?php if ($item['vat'] > 0) { ?>
				<tr>
					<td colspan="3"><b>V.A.T.</b></td>
					<td><b><?php echo '+' . to_currency($item['vat']); ?></b></td>
				</tr>
			<?php } ?>
			<?php
				if ($item['discount'] > 0) {
					?>
				<tr>
					<td colspan="3" class="discount"><?php echo number_format($item['discount'], 0) . " " . $this->lang->line("sales_discount_included") ?></td>
					<td><?php echo '-' . to_currency(abs($item['total'] - $item['discounted_total'])); ?></td>
				</tr>
			<?php
				}
				?>
		<?php
		}
		?>
		<tr>
			<td colspan="3" style='text-align:right;border-top:2px solid #000000;font-weight:bold;'>Total:</td>
			<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($initial_cost); ?></td>
		</tr>
		<?php
		if ($discount > 0) {
			?>

			<tr>
				<td colspan="3" style="text-align:right;font-weight:bold;" class="total-value">Total Discount:</td>
				<td class="total-value" style="text-align:right;"><?php echo '-' . to_currency($discount); ?></td>
			</tr>
		<?php
		}
		?>
		<?php
		if ($total_vat > 0) {
			?>
			<tr>
				<td colspan="3" style='text-align:right;font-weight:bold;'>Total VAT:</td>
				<td style="text-align:right;"><?php echo '+' . to_currency($total_vat); ?></td>
			</tr>
		<?php
		}
		?>
		<?php
		if ($discount > 0 || $total_vat > 0) {
			?>
			<tr>
				<td colspan="3" style='text-align:right;font-weight:bold;'>Grand Total:</td>
				<td style="text-align:right;"><?php echo to_currency($total + $total_vat); ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_taxes')) { //this is not going to used here. but if it's to be used, i will rewrite it
			?>
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $discounted_subtotal); ?></td>
			</tr>
			<?php
				foreach ($taxes as $tax_group_index => $sales_tax) {
					?>
				<tr>
					<td colspan="3" style="text-align:right;" class="total-value"><?php echo $sales_tax['tax_group']; ?>:</td>
					<td class="total-value"><?php echo to_currency($sales_tax['sale_tax_amount']); ?></td>
				</tr>
			<?php
				}
				?>
		<?php
		}
		?>





		<tr>
			<td colspan="4">&nbsp;</td>
		</tr></b>

		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach ($payments as $payment_id => $payment) {
			$only_sale_check |= $payment['payment_type'] == $this->lang->line('sales_check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == $this->lang->line('sales_giftcard');
			?>
			<tr>
				<td><b><?php echo 'Payment Mode'; ?></b> </td>
				<td><b><?php echo $splitpayment[0]; ?></b></td>
			</tr>
			<tr>
				<td><b><?php echo 'Amount Tendered'; ?> </b></td>
				<td><b><?php echo to_currency(abs($payment['payment_amount'])); ?></b></td>
			</tr>

		<?php
		}
		?>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		if (isset($cur_giftcard_value) && $show_giftcard_remainder) {
			?>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo $this->lang->line('sales_giftcard_balance'); ?></td>
				<td class="total-value"><b><?php echo to_currency($cur_giftcard_value); ?></b></td>
			</tr>
		<?php
		}
		?>
		<?php
		if ($mode != 'return') {
			?>
			<tr>
				<td> <b><?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due'); ?></b> </td>
				<td><b><?php echo to_currency(abs($amount_change)); ?></b></td>
			</tr>
		<?php
		}
		?>
	</table>

	<!-- <div id="sale_return_policy">
		<?php echo nl2br($this->config->item('return_policy'));
		?>
	</div> -->

	<div id="barcode">
		<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
		<?php echo $sale_id; ?>
	</div>
	<div id="sale_return_policy">
		<i><?php echo 'Thank you, please call again'; ?></i>
	</div>
	<div id="sale_return_policy">
		<i><?php //echo 'No returns of drugs/items purchased after 24 hours please.'; 
			?></i>
	</div>
</div>