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
            <a href="javascript:window.history.go(-1);">
                <div class="btn btn-info btn-sm" id="show_print_button">
                    <span class="glyphicon glyphicon-arrow-left">&nbsp;Back</span>
                </div>
            </a>
            <a href="javascript:printdoc();">
                <div class="btn btn-info btn-sm" id="show_print_button">
                    <?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?>
                </div>
            </a>
            <a class="btn btn-info btn-sm" href="<?php echo site_url('reports/print_filtered_report_items_export/' . $start . '/' . $end . '/' . $employee_id . '/' . $location_id . '/' . $sale_type . '/'  . $credit . '/' . $vatable . '/' . $customer_id . '/' . $discount . '/' . $payment_type); ?>">Export Excel</a>

        </div>

        <div id="receipt_wrapper">
            <div id="receipt_header">
                <?php if ($this->config->item('company_logo') != '') { ?>
                    <div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
                <?php } ?>
                <?php if ($this->config->item('receipt_show_company_name')) { ?>
                    <div id="company_name"><?php echo $this->config->item('company'); ?></div>
                <?php } ?>

                <div id="company_address"><?php echo nl2br($branch_address); ?></div>
                <div id="company_phone"><?php echo $branch_number; ?></div>
                <h4><?php echo $title ?></h4>
            </div>
            <div>
                <?php
                foreach ($report_title_data as $key => $value) {
                    echo '<div><b>' . $key . ': </b>' . $value . ' </div>';
                }
                ?>
            </div>
            <table id="receipt_items" class="table table-hover table-bordered">
                <thead style="background-color: #CCC;color:#FFF;">
                    <tr>
                        <th>S/N</th>
                        <th>Receipt No.</th>
                        <th>Item Name</th>
                        <th>Item Number</th>
                        <th>Employee</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Sales Type</th>
                        <th>Cost Price</th>
                        <th>Unit Sale Price</th>
                        <th>Quantity</th>
                        <th>Discounted Total</th>
                        <th>Total Cost</th>
                        <th>Discount(%)</th>
                        <th>Discount</th>
                        <th>VAT</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sn = 1;
                    foreach ($details_data as $key => $dd) : ?>
                        <?php foreach ($dd as $d) : ?>
                            <tr>

                                <td><?php echo $sn++; ?></td>
                                <td><?php echo 'POS ' . $d['id']; ?></td>
                                <td><?php echo $d['name']; ?></td>
                                <td><?php echo $d['item_number']; ?></td>
                                <td><?php echo $d['employee_name']; ?></td>
                                <td><?php echo $d['customer_name']; ?></td>
                                <td><?php echo $d['category']; ?></td>
                                <td><?php echo $d['sales_type']; ?></td>
                                <td><?php echo $d['cost_price']; ?></td>
                                <td><?php echo $d['unit_price']; ?></td>
                                <td><?php echo $d['quantity']; ?></td>
                                <td><?php echo $d['total']; ?></td>
                                <td><?php echo $d['cost']; ?></td>

                                <td><?php echo $d['discount_percent']; ?></td>
                                <td><?php echo $d['discount']; ?></td>
                                <td><?php echo $d['vat']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div id="report_summary">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overall_summary_data as $name => $value) : ?>
                                <tr>
                                    <td><?php echo strtoupper($name) ?></td>
                                    <td><?php echo to_currency($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>