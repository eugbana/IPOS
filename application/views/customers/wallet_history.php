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

        <?php $this->load->view('partial/print_receipt', array('print_after_sale', 0, 'selected_printer' => 'receipt_printer')); ?>

        <div class="print_hide" id="control_buttons" style="text-align:right">
            <a href="javascript:printdoc();">
                <div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
            </a>
            <?php echo anchor("customers", '<span class="glyphicon glyphicon-back">&nbsp</span> Go Back To Customers', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
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

                <div id="company_address"><b><?php echo nl2br($this->config->item('address')); ?></b></div>
                <div id="company_phone"><b><?php echo $this->config->item('phone'); ?></b></div>
                <div id="sale_receipt">
                    <h4><?= $is_ledger?"CUSTOMER WALLET LEDGER":'CUSTOMER WALLET TRANSACTIONS HISTORY' ?></h4>
                    <?php if ($start_date && $end_date) {  ?>
                        <h6>Between <?= $start_date ?> and <?= $end_date ?></h6>
                    <?php } ?>
                </div>
                <div id="sale_time"><b><?php echo date("Y-m-d H:i:s"); ?></b></div>

            </div>

            <div id="receipt_general_info">
                <div class="clearfix">
                    <div class="pull-left">

                        <div id="customer"><?php echo '<b>Customer Name: </b>' . $customer_info->last_name . ' ' . $customer_info->first_name; ?></div>
                        <div id="customer1"><b>Type: </b><?php echo $customer_info->staff ? 'Staff' : 'Customer'; ?></div>

                        <div id="reference2"><?php echo '<b>Current Wallet Balance</b>: ' . to_currency($customer_info->wallet); ?></div>
                        <div id="reference6"><b><?php echo $customer_info->staff ? "Monthly " : "";  ?><?php echo 'Credit Limit</b>: ' . to_currency($customer_info->credit_limit); ?></div>
                        <?php
                        if ($customer_info->staff) {
                        ?>
                            <div id="reference1"><?php echo '<b>This Month Credit Purchases</b>: ' . $credit_sales; ?></div>
                        <?php
                        }
                        ?>
                        <div id="reference8"><?php echo '<b>Total Spent</b>: ' . to_currency($customer_total); ?></div>

                    </div>

                    <?php
                    if($is_ledger){
                        ?>
                        <div class="pull-right">
                            <div><?="<b>Balance Brought Forward: </b>". to_currency($brought_forward);?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <!--end of general info-->







            <table id="items_count_details" class="table table-bordered table-hover">
                <thead>

                    <tr>
                        <?php
                        if(!$is_ledger){
                            ?>
                            <th width="20%">Date</th>
                            <th width="15%">Employee</th>
                            <th width="15%">Type</th>
                            <th width="15%">Receipt/Invoice</th>
                            <th width="20%">Amount</th>
                            <th width="15%">Balance</th>
                        <?php
                        }else{
                            ?>
                            <th width="10%">Date</th>
                            <th width="10%">Employee</th>
                            <th width="10%">Receipt/Invoice</th>
                            <th width="10%">Amount</th>
                            <th width="15%">Credit</th>
                            <th width="15%">Debit</th>
                            <th width="15%">Balance</th>
                            <th width="20%">Narration</th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead>
                <tbody id="">
                    <?php
                    foreach ($wallet_info as $index => $info) {
                        if($is_ledger){
                            ?>
                            <tr>
                                <td><?= $info->date ?></td>
                                <td><?= $info->lastname . ' ' . $info->firstname ?></td>
                                <td><?php
                                    if($info->system > 0){
                                        echo "System balance update";
                                    }elseif($info->sale_id < 0){
                                        echo "Reconciliation";
                                    }elseif ($info->sale_id == 0){
                                        echo "Wallet funding";
                                    }else{
                                        echo "<a href='sales/receipt/$info->sale_id'>Purchase $info->sale_id</a>";
                                    }
                                    ?></td>
                                <td><?php
                                        echo to_currency($info->amount);
                                    ?></td>
                                <td><?php
                                    echo to_currency($info->credit);
                                    ?></td>
                                <td><?php
                                    echo to_currency($info->debit);
                                    ?></td>
                                <td><?= to_currency($info->balance) ?></td>
                                <td><?= $info->narration?></td>
                            </tr>
                            <?php
                        }else{
                            ?>
                            <tr>
                                <td><?= $info->date ?></td>
                                <td><?= $info->lastname . ' ' . $info->firstname ?></td>
                                <td><?= $info->sale_id == 0 ? "Wallet Funding" : "Purchase" ?></td>
                                <td><?= $info->sale_id > 0 ? "<a href='sales/receipt/$info->sale_id'>POS $info->sale_id</a>" : "N/A" ?></td>
                                <td><?= to_currency($info->amount) ?></td>
                                <td><?= to_currency($info->balance) ?></td>
                            </tr>

                            <?php
                        }
                    }
                    ?>

                </tbody>
            </table>







        </div>
    </div>
</div>


<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">


</script>
