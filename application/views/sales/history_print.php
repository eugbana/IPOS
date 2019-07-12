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
                <?php echo anchor("sales/history_view/" . $meta->sale_id, '<span class="md md-reply">&nbsp</span> Back', array('class'=>'btn btn-info btn-sm')); ?>
                <a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div></a>
                <?php //echo anchor("receivings", '<span class="glyphicon glyphicon-save">&nbsp</span>' . $this->lang->line('receivings_register'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_sales_button')); ?>
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
                <p class="text-muted">Payments:</p>
                <?php foreach($payments as $i => $val): ?>
                    <?php echo "<p>$i - &#8358;$val</p>"; ?>
                <?php endforeach; ?>
            </div>

            <div id="receipt_general_info">
                <?php if (!empty($meta->sale_id)) { ?>
                    <div id="reference"><?php echo $this->lang->line('receivings_reference').": ".$meta->sale_id; ?></div>	
                <?php  } ?>
                <div id="employee"><?php echo $this->lang->line('employees_employee').": " . $meta->first_name . ' ' . $meta->last_name; ?></div>
            </div>

            <table id="receipt_items">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Quantity Purchased</th>
                    <th>Cost Price</th>
                    <th>Unit Price</th>
                    <th>Discount Percent</th>
                    <th>Location</th>
                    <th>Purchase Type</th>
                </tr>
                <tbody>
                    <?php foreach($items as $i => $val): ?>
                        <tr>
                            <td><?php echo ($i+=1); ?></td>
                            <td><?php echo $val->name; ?></td>
                            <td><?php echo $val->quantity_purchased; ?></td>
                            <td><?php echo $val->item_cost_price; ?></td>
                            <td><?php echo $val->item_unit_price; ?></td>
                            <td><?php echo $val->discount_percent; ?></td>
                            <td><?php echo $val->location; ?></td>
                            <td><?php echo $val->qty_selected; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>
