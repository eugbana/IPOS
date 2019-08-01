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
                <h5><?php echo $title . ' | ' . $start . ' - ' . $end; ?></h5>
            </div>

            <table id="receipt_items" class="table table-hover table-bordered">
                <thead style="background-color: #CCC;color:#FFF;">
                    <tr>
                        <th>Receipt No.</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Sub Total</th>
                        <th>Tax </th>
                        <th>Total</th>
                        <th>Cost</th>
                        <th>Profit</th>
                        <th>Discount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details_data as $key => $dd): ?>
                        <?php foreach($dd as $d): ?>
                            <tr>
                                <td><?php echo $key; ?></td>
                                <td><?php echo $d[0]; ?></td>
                                <td><?php echo $d[1]; ?></td>
                                <td><?php echo $d[4]; ?></td>
                                <td><?php echo $d[5]; ?></td>
                                <td><?php echo $d[6]; ?></td>
                                <td><?php echo $d[7]; ?></td>
                                <td><?php echo $d[8]; ?></td>
                                <td><?php echo $d[9]; ?></td>
                                <td><?php echo $d[10]; ?></td>
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
                            <?php foreach($overall_summary_data as $name=>$value): ?>
                                <tr>
                                    <td><?php echo $this->lang->line('reports_'.$name); ?></td>
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
