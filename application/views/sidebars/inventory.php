<?php foreach ($allowed_modules as $module) { ?>
    <?php if ($this->lang->line("module_" . $module->module_id) == "Items") { ?>
        <li class="has_sub">
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/items")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-shop"></i><span>Inventory/Items </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li>
                    <a href="<?php echo site_url("$module->module_id"); ?>"><?php echo $this->lang->line("module_" . $module->module_id) ?></a>
                </li>
                <li>
                    <a href="<?php echo site_url("items/categories"); ?>">Categories</a>
                </li>
                <li>
                    <a href="<?php echo site_url("items/check_price"); ?>" class="waves-effect waves-light"> <i class="md md-call-split"></i> <span> Check Price </span></a>
                </li>

                <li>
                    <a href="<?php echo site_url("sales/check_receipt"); ?>" class="waves-effect waves-light"> <i class="md md-call-split"></i> <span> Print Receipt </span></a>
                </li>

                <li>
                    <a href="<?php echo site_url("items/global_search"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/items/global_search") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-extension"></i><span>Global Search</span></a>
                </li>



            </ul>
        </li>
        <li class="has_sub">
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/receivings")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-send"></i><span>Product Transfer </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li>
                    <a href="<?php echo site_url("items/push"); ?>">New Transfer</a>
                </li>
                <li><a href="<?php echo site_url("receivings/transfer_history"); ?>">Transfer History</a></li>
            </ul>
        </li>

        <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Stock Taking </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">

                <!-- <li>
                    <a href="<?php echo site_url("stockintake/new"); ?>"><i class="md md-label"> </i>New Stock Intake</a>
                </li> -->

                <!-- <li>
                    <a href="<?php echo site_url("stockintake/history"); ?>"><i class="md md-label"> </i>View Stock Intakes</a>
                </li> -->

                <?php $stkid = $this->Receiving->get_inprogress_stock_taking()->stock_id;
                if ($stkid > 0) { ?>
                    <li>
                        <a href="<?php echo site_url("stockintake"); ?>"><i class="md md-label"> </i> Join Stock Taking </a>
                    </li>
                <?php  } ?>
            </ul>
        </li>

        <li class="has_sub">
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/receivings")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-receipt"></i><span>Receiving </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li><a href="<?php echo site_url("receivings"); ?>"> New Stock/Inventory</a></li>
                <li><a href="<?php echo site_url("receivings/history"); ?>">Inventory History</a></li>
            </ul>
        </li>

        <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> LPO </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li><a href="lpo">Create LPO</a></li>
                <li><a href="lpo/history">View LPOs</a></li>
            </ul>
        </li>

        <!-- <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Transfers </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li><a href="items/request_item">Request Items</a></li>
                <li><a href="items/pending_requests">Pending Requests</a></li>
                <li><a href="items/incoming_transfers">Incoming Transfers</a></li>
                <?php
                if ($this->config->item('is_warehouse') != 'YES') {  ?>

                    <li><a href="items/pending_items">Pending Items</a></li>

                <?php } ?>
            </ul>
        </li> -->
        <?php
        $pendingRequestsCount = count($this->Receiving->get_pending_requests($this->config->item('branch_name')));
        $incomingTransfersCount = count($this->Receiving->get_incoming_transfers($this->config->item('branch_name')));
        $pendingItemsCount = count($this->Receiving->get_pending_items($this->config->item('branch_name')));

        $totalCount = $pendingRequestsCount + $incomingTransfersCount + $pendingItemsCount;
        ?>

        <style>
            .dot {
                height: 10px;
                width: 10px;
                background-color: #ff0000;
                border-radius: 50%;
                display: inline-block;
            }
        </style>

        <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Transfers <?php echo $totalCount > 0 ? '<span class="dot"></span>' : '';  ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li><a href="items/request_item">Request Items </a></li>
                <li><a href="items/pending_requests">Pending Requests <?php echo $pendingRequestsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a></li>
                <li><a href="items/incoming_transfers">Incoming Transfers <?php echo $incomingTransfersCount > 0 ? '<span class="dot"></span>' : '';  ?> </a></li>
                <?php
                if ($this->config->item('is_warehouse') != 'YES') {  ?>
                    <li><a href="items/pending_items">Pending Items <?php echo $pendingItemsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a></li>
                <?php } ?>
            </ul>
        </li>

        <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Inventory Reports </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">
                <li><a href="reports/all_items">Items Reports</a></li>
                <li><a href="reports/item_inventory_report">Item Specific Reports</a></li>
                <li><a href="reports/stock_value">Stock Value</a></li>
                <li><a href="reports/expiry_items">Items Expiry Reports</a></li>
                <li><a href="reports/expired_items">Items Expired Reports</a></li>
                <li><a href="reports/out_of_stock">Out of Stock / Minimum Stock Level Reports</a></li>
                <li><a href="items/stock_report">Opening / Closing Stock</a></li>
        </li> -->
        </ul>
        </li>


    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Sales") { ?>

        <li class="has_sub">
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/sales")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-shopping-cart"></i><span>Sales </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">


                <li>
                    <a href="<?php echo site_url("sales"); ?>" class="waves-effect waves-light"><span> New Sales </span></a>
                </li>

                <li>
                    <a href="<?php echo site_url("sales/manage"); ?>" class="waves-effect waves-light"><span> Sales History </span></a>
                </li>
                <li>
                    <a href="<?php echo site_url("items/check_price"); ?>" class="waves-effect waves-light"><i class="md md-call-split"></i> <span> Check Price </span></a>
                </li>

                <li>
                    <a href="<?php echo site_url("sales/check_receipt"); ?>" class="waves-effect waves-light"> <i class="md md-call-split"></i> <span> Print Receipt </span></a>
                </li>
            </ul>
        </li>


    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Suppliers") { ?>


        <li>
            <a href="<?php echo site_url("suppliers"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/suppliers"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Suppliers</span></a>
        </li>



    <?php
    }
    ?>

<?php
}
?>

<!-- <li> 
            <a href="<?php echo site_url("reports/expiry_items"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/reports/expiry_items"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Items Expiry Reports</span></a>
            <a href="<?php echo site_url("reports/expired_items"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/reports/expired_items"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Items Expired Reports</span></a>
            <a href="<?php echo site_url("reports/out_of_stock"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/reports/out_of_stock"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Out of Stock / Minimum Stock Level Reports</span></a>
        </li> -->






</ul>
</li>
