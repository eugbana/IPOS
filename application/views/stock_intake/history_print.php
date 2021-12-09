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
            <?php echo anchor("receivings/history_view/" . $meta->receiving_id, '<span class="md md-reply">&nbsp</span> Back', array('class' => 'btn btn-info btn-sm')); ?>
            <a href="javascript:printdoc();">
                <div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
            </a>
            <?php echo anchor("stockintake", '<span class="glyphicon glyphicon-save">&nbsp</span> Continue Stock Taking', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
        </div>

        <div id="receipt_wrapper">
            <div id="receipt_header">
                <?php if ($this->config->item('company_logo') != '') { ?>
                    <div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
                <?php } ?>
                <?php if ($this->config->item('receipt_show_company_name')) { ?>
                    <div id="company_name"><?php echo $this->config->item('company'); ?></div>
                <?php } ?>

                <div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
                <div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
                <div id="sale_receipt"><b><?php echo $receipt_title; ?></b></div>
                <div id="sale_time"><b><?php echo $transaction_time ?></b></div>
            </div>

            <div id="receipt_general_info">
                <div class="clearfix">
                    <div class="pull-right">
                    <?php
						if (isset($title)) {
						?>
							<div id="customer"><?php echo '<b>' . " Title</b>: " . $title; ?></div>
						<?php
						}
						?>
						<?php
						if (!empty($reference)) {
						?>
							<div id="reference"><?php echo '<b>Description</b>: ' . $description; ?></div>
						<?php
						}
					?>
                    </div>

                    <div class="pull-left">
                    <div id="sale_id"><?php echo '<b>'. " Stock ID</b>: " . $meta->stock_id; ?></div>

                        <div id="employee"><b><?php echo $user_role . "</b>: " . $meta->first_name . ' ' . $meta->last_name; ?></div>
                        <div id="employee"><b><?php echo   "Date</b>: " . $date; ?></div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="receipt_items">
                    <tr>
                        <th style="width:3%; color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'S/N.'; ?></th>
                        <th style="width:10%; color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'Item No.'; ?></th>
                        <th style="width:24%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo $this->lang->line('items_item'); ?></th>

                        <th style="width:3%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Current Quantity</th>
                        <th style="width:3%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Stock Taking Quantity</th>
                        <th style="width:3%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Variance Quantity</th>


                        <th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Price</th>
                        <th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Retail Price</th>

                        <th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Current Stock Total</th>
                        <th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Stock Taking Total</th>
                        <th style="width:10%;color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Variance Amount</th>

                        <!-- <th style="width:7%;text-align:right;color: #495057; background-color: #e9ecef;border-color: #dee2e6;"><?php echo 'Cost Total' ?></th> -->
                    </tr>

                    <tbody>
                        <?php
                        $currentTotal = 0;
                        $stockTotal = 0;
                        $sumTotal = 0;
                        $totalVariance = 0;
                        $totalCurrent = 0;
                        $totalStock = 0;
                        $sn = 1;
                        foreach ($items as $i => $val) : ?>
                            <?php 
                                $variance_qty = $val->current_quantity - $val->quantity_purchased;
                                $grandTotal = ($val->item_cost_price * $val->quantity_purchased);

                                $currentTotal = ($val->item_cost_price * $val->current_quantity);
                                $stockTotal = ($val->item_cost_price * $val->quantity_purchased);
                                $varianceTotal = ($val->item_cost_price * $variance_qty);
                                // $sumTotal += ($currentTotal - $stockTotal);
                                $totalVariance += $varianceTotal;
                                $totalCurrent += $currentTotal;
                                $totalStock += $stockTotal;
                            ?>

                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><?php echo $val->name; ?></td>
                                <td><?php echo $val->item_number; ?></td>

                                <td><?php echo $val->current_quantity; ?></td>
                                <td><?php echo $val->quantity_purchased; ?></td>
                                <td><?php echo ($val->current_quantity - $val->quantity_purchased); ?></td>


                                <td><?php echo to_currency($val->item_cost_price); ?></td>
                                <td><?php echo to_currency($val->item_unit_price); ?></td>
                                
                                <td><?php echo to_currency($currentTotal); ?></td>
                                <td><?php echo to_currency($stockTotal); ?></td>
                                <td><?php echo to_currency($varianceTotal); ?></td>

                                <!-- <td>&#8358;<?php echo number_format(abs($val->item_cost_price * ($val->quantity_purchased)), 2); ?></td> -->


                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <th colspan="8" style='text-align:right;border-top:2px solid #000000;'>Current Stock Total</th>
                            <td colspan="4" style='border-top:2px solid #000000;'>
                                <div class="total-value"><?php echo to_currency($totalCurrent); ?></div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="8" style='text-align:right;border-top:2px solid #000000;'>Stock Taking Total</th>
                            <td colspan="4" style='border-top:2px solid #000000;'>
                                <div class="total-value"><?php echo to_currency($totalStock); ?></div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="8" style='text-align:right;border-top:2px solid #000000;'>Variance Total</th>
                            <td colspan="4" style='border-top:2px solid #000000;'>
                                <div class="total-value"><?php echo to_currency($totalVariance); ?></div>
                            </td>
                        </tr>
                        <!-- <tr>
                            <th colspan="3" style='text-align:right;'><?php echo $this->lang->line('sales_payment'); ?></th>
                            <td colspan="2">
                                <div class="total-value"><?php echo $meta->payment_type; ?></div>
                            </td>
                        </tr> -->
                        <!-- <tr>
                            <th colspan="4" style='text-align:right;'><?php echo $this->lang->line('sales_amount_tendered'); ?></th>
                            <td colspan="2">
                                <div class="total-value"><?php echo to_currency(abs($sumTotal)); ?></div>
                            </td>
                        </tr> -->

                        <!-- <tr>
                            <th colspan="3" style='text-align:right;'><?php echo $this->lang->line('sales_change_due'); ?></th>
                            <td colspan="2">
                                <div class="total-value"><?php echo to_currency(0.00); ?></div>
                            </td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
            <div id='barcode'>
                <img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
                <?php echo 'STCK ' . $meta->stock_id; ?>
            </div>
        </div>

    </div>
</div>
<?php $this->load->view("partial/footer"); ?>