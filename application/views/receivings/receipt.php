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
				<div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
			</a>
			<?php echo anchor("receivings", '<span class="glyphicon glyphicon-save">&nbsp</span>' . $this->lang->line('receivings_register'), array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
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


				<div id="company_address"><?php echo nl2br($branch_address); ?></div>
				<div id="company_phone"><?php echo $branch_number; ?></div>
				<div id="sale_receipt"><b><?php echo $receipt_title; ?></b></div>
				<div id="sale_time"><b><?php echo $transaction_time ?></b></div>
			</div>

			<div id="receipt_general_info">
				<div class="clearfix">
					<div class="pull-right">
						<?php
						if (isset($supplier)) {
						?>
							<div id="customer"><?php echo '<b>' . $this->lang->line('suppliers_supplier') . "</b>: " . $supplier; ?></div>
						<?php
						}
						?>
						<?php
						if (!empty($reference)) {
						?>
							<div id="reference"><?php echo '<b>Supplier/Invoice No</b>: ' . $reference; ?></div>
						<?php
						}
                        if (!empty($receiving_type)) {
                            ?>
                            <div id="reference"><?php echo '<b>Type</b>: ' . $receiving_type; ?></div>
                            <?php
                        }
						?>
					</div>

					<div class="pull-left">
						<div id="sale_id"><?php echo '<b>' . $this->lang->line('receivings_id') . "</b>: " . $receiving_id; ?></div>

						<div id="employee"><?php echo '<b>' . $user_role . "</b>: " . $employee; ?></div>
						<div id="employee"><?php echo '<b>Mode</b>: ' . ucfirst($mode); ?></div>
						<div id="employee"><?php echo '<b>Date</b>: ' . ucfirst($transaction_time); ?></div>

					</div>
				</div>
			</div>
			<!--end of general info-->
			<div class="table-responsive">
				<table id="receipt_items" class="table">
					<thead class="thead-light">
						<tr>
							<th style=" color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'S/N.'; ?></th>
							<th style=" color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'Item No.'; ?></th>
							<th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo $this->lang->line('items_item'); ?></th>
							<th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Price</th>
							<th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Retail Price</th>
							<!-- <th style="width:10%;"><?php //echo $this->lang->line('sales_unit_price'); 
														?></th> -->
							<?php if($controller_name != 'stockintake'){ ?>
							<th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Qty Ordered</th>
							<?php } ?>
							<th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Qty Received</th>
							<th style="text-align:right;color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'Cost Total' ?></th>
						</tr>
					</thead>
					<?php
					$sn = 1;
					$total = 0;
//					echo "Na here";
					foreach (array_reverse($cart, TRUE) as $line => $item) {
						// $totalCost = $item['quantity'] * $item['price'];
						$cur_item_info = $this->Item->get_info($item['item_id']);
						$totalCost = ($item['quantity'] * $cur_item_info->cost_price);
						//$cur_item_info->cost_price
						$total += $totalCost;
					?>
						<tr>
							<td><?php echo $sn++; ?></td>
							<td><?php echo $item['item_number']; ?></td>
							<td><?php echo wordwrap($item['name'],50,"<br />"); ?>
							</td>
							<!-- <td><?php echo to_currency($item['price']); ?></td>
							<td><?php echo to_currency($item['unit_price']); ?></td> -->

							<td><?php echo to_currency($cur_item_info->cost_price); ?></td>
							<td><?php echo to_currency($cur_item_info->unit_price); ?></td>

							<?php if($controller_name != 'stockintake'){ ?>
							<td>
								<?php echo to_quantity_decimals(abs($item['receiving_quantity'])); ?>
							</td>
							<?php } ?>
							<td>
								<?php echo to_quantity_decimals($item['quantity']);
								?></td>
							<td>
							<?php if($controller_name != 'stockintake'){ ?>
								<!-- <div class="total-value"><?php echo to_currency(abs($item['total'])); ?></div> -->
								<div class="total-value"><?php echo to_currency($totalCost); ?></div>
							<?php }else{ ?>
								<div class="total-value"><?php echo to_currency($totalCost); ?></div>
							<?php } ?>
							</td>

						</tr>

					<?php
					}
					?>
					<tr>
						<th colspan="5" style='text-align:right;border-top:2px solid #000000;'>Grand Cost Total</th>
						<td colspan="3" style='border-top:2px solid #000000;'>
							<?php if($controller_name != 'stockintake'){ ?>
								<div class="total-value"><?php echo to_currency(abs($total)); ?></div>
							<?php }else{ ?>
								<div class="total-value"><?php echo to_currency($total); ?></div>
							<?php } ?>
						</td>
					</tr>
					<?php
					if ($mode != 'requisition') {
					?>
						<tr>
							<!-- <th colspan="3" style='text-align:right;'><?php echo $this->lang->line('sales_payment'); ?></th> -->
							<!-- <td colspan="2"> -->
							<!-- <div class="total-value"><?php echo $payment_type; ?></div> -->
							<!-- </td> -->
						</tr>

						<?php if (isset($amount_change)) {
						?>
							<tr>
								<th colspan="4" style='text-align:right;'><?php echo $this->lang->line('sales_amount_tendered'); ?></th>
								<td colspan="2">
									<div class="total-value"><?php echo to_currency(abs($amount_tendered)); ?></div>
								</td>
							</tr>

							<tr>
								<th colspan="4" style='text-align:right;'><?php echo $this->lang->line('sales_change_due'); ?></th>
								<td colspan="2">
									<div class="total-value"><?php echo $amount_change; ?></div>
								</td>
							</tr>
						<?php
						}
						?>
					<?php
					}
					?>
				</table>
			</div>
			<?php //print_r($cart)
			?>

			<div id='barcode'>
				<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
				<?php echo $receiving_id; ?>
			</div>
		</div>
	</div>
</div>


<?php $this->load->view("partial/footer"); ?>
