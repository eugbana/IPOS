<div id="receipt_wrapper">
	<div id="receipt_header">
		<?php
		if ($this->config->item('company_logo') != '') 
		{ 
		?>
		<div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>	
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_company_name')) 
		{ 
		?>
			<div id="company_name"><?php echo $this->config->item('company');?></div>
		<?php
		}
		?>
		
		<b><div id="company_address"><?php echo nl2br($branch_address); ?></div>
		<div id="company_phone"><?php echo $branch_number; ?></div>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>
		<div id="sale_time"><?php echo $transaction_time ?></div></b>
		
	</div>

	<div id="receipt_general_info">
		<?php
		if(isset($transaction_type))
		{
		?>
			<div id="customer"><b><?php echo 'Transaction'.": ". $transaction_type; ?></b></div>
		<?php
		}
		?>
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><b><?php echo $this->lang->line('customers_customer').": ".$customer; ?></b></div>
		<?php
		}
		?>
		
		<div id="sale_id"><b><?php echo $this->lang->line('sales_id').": ".$sale_id; ?></b></div>

		<?php
		if (!empty($invoice_number))
		{
		?>
			<div id="invoice_number"><b><?php echo $this->lang->line('sales_invoice_number').": ".$invoice_number; ?></b></div>	
		<?php 
		}
		?>

		<div id="employee"><b><?php echo $this->lang->line('employees_employee').": ".$employee; ?></b></div>
	</div>
<?php// print_r($cart);?>
	<table id="receipt_items">
		<tr>
			<b><th style="width:40%;"><?php echo $this->lang->line('sales_description_abbrv'); ?></th>
			<th style="width:20%;"><?php echo $this->lang->line('sales_price'); ?></th>
			<th style="width:20%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
			<th style="width:20%;" class="total-value"><?php echo $this->lang->line('sales_total'); ?></th></b>
		</tr>
		<?php
		foreach($cart as $line=>$item)
		{
		?>
			<?php if($item['reference']==0){ ?>
			<tr>
				<td><b><?php echo ucfirst($item['name']); ?></b></td>
				<td><b><?php echo to_currency($item['price']); ?></b></td>
				<td><?php echo to_quantity_decimals($item['quantity']); ?></td>
				<td class="total-value"><b><?php echo to_currency($item[($this->config->item('receipt_show_total_discount') ? 'total' : 'discounted_total')]); ?></b></td>
			</tr>
			<?php foreach($cart as $let=>$unline){?>
				<?php if($unline['item_id']==$item['item_id'] && $unline['reference']==1){?>
				<tr>
					<td><b><?php echo ucfirst($unline['name'].' '.'reminder'); ?></b></td>
					<td><?php echo to_currency($unline['price']); ?></td>
					<td><?php echo ''; ?></td>
					<td class="total-value"><b><?php echo to_currency($unline[($this->config->item('receipt_show_total_discount') ? 'total' : 'discounted_total')]); ?></b></td>
				</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
				
			<tr>
				
			</tr>
			<?php
			if ($item['discount'] > 0)
			{
			?>
				<tr>
					<td colspan="3" class="discount"><?php echo number_format($item['discount'], 0) . " " . $this->lang->line("sales_discount_included")?></td>
					<td class="total-value"><?php echo to_currency($item['discounted_total']) ; ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>
	
		<?php
		if ($this->config->item('receipt_show_total_discount') && $discount > 0)
		{
		?> 
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal); ?></td>
			</tr>
			<tr>
				<td colspan="3" class="total-value"><?php echo $this->lang->line('sales_discount'); ?>:</td>
				<td class="total-value"><?php echo to_currency($discount * -1); ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_taxes'))
		{
		?> 
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $discounted_subtotal); ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index=>$sales_tax)
			{
			?>
				<tr>
					<td colspan="3" class="total-value"><?php echo $sales_tax['tax_group']; ?>:</td>
					<td class="total-value"><?php echo to_currency($sales_tax['sale_tax_amount']); ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<tr>
		</tr>
		
		<?php $border = (!$this->config->item('receipt_show_taxes') && !($this->config->item('receipt_show_total_discount') && $discount > 0)); ?> 
		<tr>
			<td colspan="3" style="text-align:right;<?php echo $border? 'border-top: 2px solid black;' :''; ?>"><b><?php echo $this->lang->line('sales_total'); ?></b></td>
			<td style="text-align:right;<?php echo $border? 'border-top: 2px solid black;' :''; ?>"><b><?php echo to_currency($total); ?></b></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr></b>

		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach($payments as $payment_id=>$payment)
		{ 
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
				<td><b><?php echo to_currency( $payment['payment_amount'] ); ?></b></td>
			</tr>
			
		<?php
		}
		?>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php 
		if (isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
		<tr>
			<td colspan="3" style="text-align:right;"><?php echo $this->lang->line('sales_giftcard_balance'); ?></td>
			<td class="total-value"><b><?php echo to_currency($cur_giftcard_value); ?></b></td>
		</tr>
		<?php 
		}
		?>
		<tr>
			<td> <b><?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due') ; ?></b> </td>
			<td><b><?php echo to_currency($amount_change); ?></b></td>
		</tr>
	</table>

	<div id="sale_return_policy">
		<?php // echo nl2br($this->config->item('return_policy')); ?>
	</div>

	<div id="barcode">
		<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
		<?php echo $sale_id; ?>
	</div>
	<div id="sale_return_policy">
		<i><?php  echo 'Thank you, please call again'; ?></i>
	</div>
	<div id="sale_return_policy">
		<i><?php  echo 'No returns of drugs/items purchased after 24 hours please.'; ?></i>
	</div>
</div>
