<?php $this->load->view("partial/header_print"); ?>
    <div class="content-page">
        <!-- Start content -->
        <div class="content">
            <?php
                if (isset($error_message)) {
                    echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
                    exit;
                }
            ?>

            <?php $this->load->view('partial/print_receipt', array('print_after_sale', $print_after_sale, 'selected_printer'=>'receipt_printer')); ?>

            <div class="print_hide" id="control_buttons" style="text-align:right">
                <?php echo anchor("receivings/history_view/" . $meta->receiving_id, '<span class="md md-reply">&nbsp</span> Back', array('class'=>'btn btn-info btn-sm')); ?>
                <a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div></a>
                <?php echo anchor("receivings", '<span class="glyphicon glyphicon-save">&nbsp</span>' . $this->lang->line('receivings_register'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_sales_button')); ?>
            </div>

        <div id="receipt_wrapper">
            <div id="receipt_header">
                <?php if ($this->config->item('company_logo') != '')  { ?>
                    <div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
                <?php } ?>
                <?php if ($this->config->item('receipt_show_company_name')) { ?>
                    <div id="company_name"><?php echo $this->config->item('company'); ?></div>
                <?php } ?>

                <div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
                <div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
                <div id="sale_receipt"><?php echo $receipt_title; ?></div>
                <div id="sale_time"><?php echo $transaction_time ?></div>
            </div>

            <div id="receipt_general_info">
                <?php if(isset($meta->company_name)) { ?>
                    <div id="customer"><?php echo $this->lang->line('suppliers_supplier').": " . $meta->company_name; ?></div>
                <?php } ?>
                <div id="sale_id"><?php echo $this->lang->line('receivings_id').": ".$meta->receiving_id; ?></div>
                <?php if (!empty($reference)) { ?>
                    <div id="reference"><?php echo $this->lang->line('receivings_reference').": ".$meta->reference; ?></div>	
                <?php  } ?>
                <div id="employee"><?php echo $this->lang->line('employees_employee').": " . $meta->first_name . ' ' . $meta->last_name; ?></div>
            </div>

            <table class="table table-bordered table-hover" id="receipt_items">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Quantity Purchased</th>
                    <th>Cost Price</th>
                    <th>Unit Price</th>
                    <th>Discount Percent</th>
                    <th>Location</th>
                    <!-- <th>Receiving Quantity</th> -->
                </tr>
                <tbody>
                    <?php foreach($items as $i => $val): ?>
                        <?php $sumTotal += ($val->item_cost_price * $val->quantity_purchased); ?>
                        <?php $quantityTotal += $val->quantity_purchased; ?>
                        <?php $costTotal += $val->item_cost_price; ?>
                        <tr>
                            <td><?php echo ($i+=1); ?></td>
                            <td><?php echo $val->name; ?></td>
                            <td><?php echo $val->quantity_purchased; ?></td>
                            <td><?php echo $val->item_cost_price; ?></td>
                            <td><?php echo number_format($val->item_cost_price * $val->quantity_purchased, 2); ?></td>
                            <td><?php echo $val->discount_percent; ?></td>
                            <td><?php echo $val->location_name; ?></td>
                            <!-- <td><?php echo $val->receiving_quantity; ?></td> -->
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($items) > 0): ?>
                        <tr>
                            <td><b>#</b></td>
                            <td colspan="3"><b>TOTAL</b></td>
                            <!-- <td><b><?php echo number_format($quantityTotal, 2); ?></b></td> -->
                            <!-- <td><b><?php echo number_format($costTotal, 2); ?></b></td> -->
                            <td><b><?php echo number_format($sumTotal, 2); ?></b></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>
