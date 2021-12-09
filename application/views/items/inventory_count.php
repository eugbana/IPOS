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
                <div class="btn btn-info btn-sm" , id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
            </a>
            <?php //echo anchor("items", '<span class="glyphicon glyphicon-back">&nbsp</span> Go Back To Items', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
            <?php //echo anchor("javascript:window.history.go(-1);", '<span class="glyphicon glyphicon-back">&nbsp</span> Go Back ', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>

            <!-- <div class="print_hide" id="control_buttons" style="text-align:right"> -->
                <a href="javascript:window.history.go(-1);">
                    <div class="btn btn-info btn-sm" id="show_print_button">
                        <span class="glyphicon glyphicon-arrow-left">&nbsp;Back</span>
                    </div>
                </a>
            <!-- </div> -->
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
                    <h4><?php echo 'PRODUCT ITEM INVENTORY TRACKING'; ?></h4>
                    <?php if ($start_date && $end_date) {  ?>
                        <h6>Between <?= $start_date ?> and <?= $end_date ?></h6>
                    <?php } ?>
                </div>
                <div id="sale_time"><b><?php echo date("Y-m-d H:i:s"); ?></b></div>

            </div>

            <div id="receipt_general_info">
                <div class="clearfix">
                    <div class="pull-left">

                        <div id="customer"><?php echo '<b>Product Name: </b>' . $item_info->name; ?></div>
                        <div id="customer"><?php echo '<b>Product Number: </b>' . $item_info->item_number; ?></div>

                        <div id="reference"><?php echo '<b>Stock Location</b>: ' . $stock_location_name; ?></div>
                        <div id="reference"><?php echo '<b>Current Quantity</b>: ' ?><span id="current_quantity"></span></div>

                    </div>

                    <div class="pull-left">


                    </div>
                </div>
            </div>
            <!--end of general info-->







            <table id="items_count_details" class="table table-bordered table-hover">
                <thead>
                    <tr style="background-color: #999 !important;">
                        <th colspan="6">Inventory Data Tracking</th>
                    </tr>
                    <tr>
                        <th width="20%">Date</th>
                        <th width="20%">Employee</th>
                        <th width="20%">In/Out Qty</th>
                        <th width="10%">Remaining Qty</th>
                        <th width="10%">Price</th>
                        <th width="30%">Remarks</th>
                    </tr>
                </thead>
                <tbody id="inventory_result">
                    <?php
                    /*
                        * the tbody content of the table will be filled in by the javascript (see bottom of page)
                        */

                    $inventory_array = $this->Inventory->get_inventory_data_for_item($item_info->item_id, $stock_location_id, $start_date, $end_date)->result_array();


                    foreach ($inventory_array as $index => $data) {


                    ?>
                        <tr>
                            <td><?php echo $data['trans_date'];  ?></td>
                            <td><?php echo $data['firstname'] . ' ' . $data['lastname'];  ?></td>
                            <td style="text-align:center"><?php echo $data['trans_inventory'];  ?></td>
                            <td style="text-align:center"><?php echo $data['trans_remaining'];  ?></td>
                            <td style="text-align:center"><?php echo to_currency($data['selling_price']);  ?></td>
                            <td ><?php echo $data['trans_comment'];  ?></td>
                        </tr>



                    <?php
                    }
                    ?>

                </tbody>
            </table>







        </div>
    </div>
</div>


<?php $this->load->view("partial/footer"); ?>
<script type="text/javascript">
    $(document).ready(function() {


        //display_stock(<?php echo $stock_location_id; ?>);
    });

    function display_stock(location_id) {
        var item_quantities = <?php echo json_encode($item_quantities); ?>;
        $("#current_quantity").html(parseFloat(item_quantities[location_id]).toFixed(<?php echo quantity_decimals(); ?>));

        var inventory_data = <?php echo json_encode($inventory_array); ?>;


        var table = document.getElementById("inventory_result");

        // Remove old query from tbody
        var rowCount = table.rows.length;
        for (var index = rowCount; index > 0; index--) {
            table.deleteRow(index - 1);
        }

        // Add new query to tbody
        for (var index = 0; index < inventory_data.length; index++) {
            var data = inventory_data[index];
            if (data['trans_location'] == location_id) {
                var tr = document.createElement('tr');

                var td = document.createElement('td');
                td.appendChild(document.createTextNode(data['trans_date']));
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(data['firstname'] + ' ' + data['lastname']));
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(parseFloat(data['trans_inventory']).toFixed(<?php echo quantity_decimals(); ?>)));
                td.setAttribute("style", "text-align:center");
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(parseFloat(data['trans_remaining']).toFixed(<?php echo quantity_decimals(); ?>)));
                td.setAttribute("style", "text-align:center");
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(data['trans_comment']));
                tr.appendChild(td);

                table.appendChild(tr);
            }
        }
    }
</script>