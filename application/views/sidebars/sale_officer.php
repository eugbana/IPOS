<?php foreach ($allowed_modules as $module) { ?>


    <?php if ($this->lang->line("module_" . $module->module_id) == "Sales") { ?>

        <li class="has_sub">
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/sales")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-shopping-cart"></i><span>Sales </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">


                <li>
                    <a href="<?php echo site_url("sales"); ?>" class="waves-effect waves-light"><span> New Sales </span></a>
                </li>

                <!-- <li>
                    <a href="<?php echo site_url("sales/manage"); ?>" class="waves-effect waves-light"><span> Sales History </span></a>
                </li> -->

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
    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Customers") { ?>

        <li>
            <a href="<?php echo site_url("customers"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/customers"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Customer</span></a>
        </li>

    <?php
    }
    ?>


<?php
}
?>

        <li> 
            <a href="<?php echo site_url("reports/expiry_items"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/reports/expiry_items"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Items Expiry Reports</span></a>
            <a href="<?php echo site_url("reports/expired_items"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/reports/expired_items"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Items Expired Reports</span></a>
        </li>


</ul>
</li>
