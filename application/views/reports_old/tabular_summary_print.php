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
            <a class="btn btn-info btn-sm" href="<?php echo site_url('reports/print_filtered_summary_report_items_export/' . $start . '/' . $end . '/' . $employee_id . '/' . $location_id . '/' . $sale_type .  '/' . $credit . '/' . $vatable . '/' . $customer_id . '/' . $discount . '/' . $payment_type); ?>">Export Excel</a>
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
                <h4><b><?php echo $title ?></b></h4>
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
                        <th>Employee</th>
                        <th>Total Cost</th>

                        <th>Total VAT</th>
                        <th>Total Discount</th>
                        <th>Discounted Total</th>
                        <th>Total Payment</th>
                        <th>Total Change due</th>

                        <th>Payment Modes</th>





                    </tr>

                </thead>
                <tbody>
                    <?php

                    foreach ($summary_data as $emp => $d) :

                    ?>
                        <tr>


                            <td rowspan="5"><?php echo ucwords($emp); ?></td>
                            <td rowspan="5"><?php echo to_currency($d['cost']); ?></td>


                            <td rowspan="5"><?php echo to_currency($d['vat']); ?></td>
                            <td rowspan="5"><?php echo to_currency($d['discount']); ?></td>
                            <td rowspan="5"><?php echo to_currency($d['total']); ?></td>

                            <td rowspan="5"><?php echo to_currency($d['payment_amount']); ?></td>
                            <td rowspan="5"><?php echo to_currency($d['change_due']); ?>

                            </td>

                            <td>Cash: <?php


                                        echo to_currency($d['cash']);



                                        ?></td>
                        </tr>
                        <tr>
                            <td>POS: <?php echo to_currency($d['pos']); ?></td>
                        </tr>
                        <tr>
                            <td>Transfer: <?php echo to_currency($d['transfer']); ?></td>
                        </tr>
                        <tr>
                            <td>Wallet: <?php echo to_currency($d['wallet']); ?></td>
                        </tr>
                        <tr>
                            <td>Check: <?php echo to_currency($d['check']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="8" style="border:1px solid gray;"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div id="report_summary">
                <div class="table-responsive">

                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>