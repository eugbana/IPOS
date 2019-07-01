<div id="receipt_wrapper">
	<div id="receipt_header">
		
		<?php
		//print_r($cart);
		if ($this->config->item('receipt_show_company_name')) 
		{ 
		?>
			<div id="company_name"><?php echo $this->config->item('company'); ?></div>
		<?php
		}
		?>

		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>
		<div><h3>Push Transaction</h3></div>
		
	</div>

	<div id="receipt_general_info">
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><?php echo $this->lang->line('customers_customer').": ".$customer; ?></div>
		<?php
		}
		?>
		
		<div id="sale_id"><?php echo "Transfer ID".": ".$transfer_id; ?></div>

		<?php
		if (!empty($invoice_number))
		{
		?>
			<div id="invoice_number"><?php echo $this->lang->line('sales_invoice_number').": ".$invoice_number; ?></div>	
		<?php 
		}
		?>
		<div id="employee"><?php echo "Received by".": ".$employee; ?></div>
	</div>

	<table id="receipt_items">
		<tr>
			<th style="width:20%;">Name</th>
			<th style="width:40%;">Quantity Pushed</th>
			<th style="width:20%;">Quantity Received</th>
			<th style="width:10%;"></th>
			<th style="width:5%;" class="total-value"></th>
		</tr>
		<?php
		foreach($cart as $line=>$item)
		{
			
		?>
			<?php if($item['reference']==0){?>
			<tr>
				<td><?php echo($item['name']); ?></td>
				<td><?php echo to_quantity_decimals(($item['quantity'])); ?></td>
				<td><?php echo($item['received_quantity']); ?></td>
				<td></td>
				<td></td>
				
			</tr>
			
			
		<?php
			}
		}
		?>
	
	
	</table>

	

	
</div>
