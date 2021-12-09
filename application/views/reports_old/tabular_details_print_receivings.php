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
            <a class="btn btn-info btn-sm" href="<?php echo site_url('reports/print_filtered_report_receivings_export/' . $start_date . '/' . $end_date . '/' . $receiving_type . '/' . $location_id . '/' . $employee_id . '/' . $supplier); ?>">Export Excel</a> |

        </div>

        <div id="receipt_wrapper">
            <div id="receipt_header">
                <?php if ($this->config->item('company_logo') != '') { ?>
                    <div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
                <?php } ?>
                <?php if ($branch_address) { ?>
                    <div id="company_name"><?php echo $branch_number; ?></div>
                <?php } ?>

                <div id="company_address"><?php echo $branch_address; ?></div>
                <div id="company_phone"><?php echo $branch_number; ?></div>
                <h5><?php echo $title; ?></h5>
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
                        <th>Date</th>
                        <th>Quantity Ordered</th>
                        <th>Quantity Received</th>

                        <th>Supplier</th>


                        <th>Employee </th>
                        <th>Total Cost</th>
                        <th>Total Price</th>

                        <!-- <th>Payment Type</th> -->
                        <th>Reference</th>
                        <th>Comment</th>
                    </tr>

                </thead>
                <tbody>
                    <?php
                    $sn = 1;
                    foreach ($summary_data as $d) : ?>
                        <tr>
                            <td><?= $sn++; ?></td>
                            <td><?php echo 'RECV ' . $d['receiving_id']; ?></td>
                            <td><?php echo $d['receiving_time']; ?></td>
                            <td><?php echo $d['quantity_ordered']; ?></td>
                            <td><?php echo $d['quantity_purchased']; ?></td>

                            <td><?php echo $d['supplier_name'] ? $d['supplier_name'] : "N/A"; ?></td>
                            <td><?php echo $d['employee_name'] ? $d['employee_name'] : "N/A"; ?></td>


                            <td><?php echo to_currency($d['cost']); ?></td>
                            <td><?php echo to_currency($d['price']); ?></td>
                            <!-- <td><?php // echo $d['payment_type']; 
                                        ?></td> -->
                            <td><?php echo $d['reference']; ?></td>
                            <td><?php echo $d['comment']; ?></td>
                        </tr>
                    <?php endforeach; ?>
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
                                    <td align="left"><?php echo $name; ?></td>
                                    <td align="left"><?php echo to_currency($value); ?></td>
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