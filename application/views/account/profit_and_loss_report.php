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
            <a class="btn btn-info btn-sm" href="<?php echo site_url('profit_and_loss/export_profit_and_loss/' . $start . '/' . $end . '/' . $type ); ?>">Export Excel</a>

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
                <h2><?php echo $title ?></h2>

                <h3><b> <?php echo date('d D, F Y' , strtotime($start)); ?>  -  <?php echo date('d D, F Y' , strtotime($end)); ?> </b></h3>
            </div>

            <table id="receipt_items" class="table table-hover table-bordered">
                <thead style="background-color: #CCC; color:#FFF;">
                    <tr>
                        <th wi style="font-size: 17px"dth="20%" align="center"> </th>
                        <th style="font-size: 17px">Revenue</th>
                        <th style="font-size: 17px">Returns</th>
                        <th style="font-size: 17px">Total Net Revenue</th>
                        <th style="font-size: 17px">Cost of Good Sold</th>
                        <th style="font-size: 17px">Gross Profit</th>
                        <th style="font-size: 17px">Total Expenses</th>
                        <th style="font-size: 17px">Earning before Tax</th>
                        <th style="font-size: 17px">Income Tax</th>
                        <th style="font-size: 17px">Net Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                     $totalRevenue = 0;
                     $totalReturns = 0;
                     $allTotalNetRevenue = 0;
                     $totalCostOfGoods = 0;
                     $totalGrossProfit = 0;
                     $totalExpenses = 0;
                     $totalEarningsBeforeTax = 0;
                     $allTotalVat = 0;
                     $totalNetEarnings = 0;
                    foreach ($headers as $key => $header) : ?>
                    <?php 
                        $totalNetRevenue = $rows[$key][0]["total_revenue"] - $rows[$key][0]["total_returns"];
                        $grossProfit = $totalNetRevenue - $rows[$key][0]["cost_of_goods"];
                        $earningsBeforeTax = $grossProfit -  $rows[$key][0]["total_expenses"];
                        $netEarning = $earningsBeforeTax - $rows[$key][0]["total_vat"];

                        //totals
                        $totalRevenue += $rows[$key][0]["total_revenue"];
                        $totalReturns += $rows[$key][0]["total_returns"];
                        $allTotalNetRevenue += $totalNetRevenue;
                        $totalCostOfGoods += $rows[$key][0]["cost_of_goods"];
                        $totalGrossProfit += $grossProfit;
                        $totalExpenses += $rows[$key][0]["total_expenses"];
                        $totalEarningsBeforeTax += $earningsBeforeTax;
                        $allTotalVat += $rows[$key][0]["total_vat"];
                        $totalNetEarnings += $netEarning;                                       

                    ?>
                            <tr>
                                <td width="20%"><h4><?php echo $header ?></h4></td>
                                <td><?php echo to_currency($rows[$key][0]["total_revenue"]); ?></td>
                                <td><?php echo to_currency($rows[$key][0]["total_returns"]);  ?></td>
                                <td><?php echo to_currency($totalNetRevenue); ?></td>
                                <td><?php echo to_currency($rows[$key][0]["cost_of_goods"]);  ?></td>
                                <td><?php echo to_currency($grossProfit); ?></td>
                                <td><?php echo to_currency($rows[$key][0]["total_expenses"]); ?></td>
                                <td><?php echo to_currency($earningsBeforeTax); ?></td>
                                <td><?php echo to_currency($rows[$key][0]["total_vat"]); ?></td>
                                <td><?php echo to_currency($netEarning); ?></td>
                            </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: 900; font-size: 14px;">
                                <td width="20%"><h4><?php echo "Total" ?></h4></td>
                                <td><?php echo to_currency($totalRevenue); ?></td>
                                <td><?php echo to_currency($totalReturns);  ?></td>
                                <td><?php echo to_currency($allTotalNetRevenue); ?></td>
                                <td><?php echo to_currency($totalCostOfGoods);  ?></td>
                                <td><?php echo to_currency($totalGrossProfit); ?></td>
                                <td><?php echo to_currency($totalExpenses); ?></td>
                                <td><?php echo to_currency($totalEarningsBeforeTax); ?></td>
                                <td><?php echo to_currency($allTotalVat); ?></td>
                                <td><?php echo to_currency($totalNetEarnings); ?></td>
                            </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>