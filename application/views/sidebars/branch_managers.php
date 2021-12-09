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
            <a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/receivings")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-receipt"></i><span>Stock Intake </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled">


                <li><a href="<?php echo site_url("receivings"); ?>"> New Stock/Inventory</a></li>
                <li><a href="<?php echo site_url("receivings/history"); ?>">Inventory History</a></li>
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
            </ul>
        </li>
    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Customers") { ?>


        <li>
            <a href="<?php echo site_url("customers"); ?>" class="waves-effect waves-light <?php echo ((strstr($_SERVER['REQUEST_URI'], "/customers"))) ? "active" : ""; ?>"><i class="fa fa-users"></i><span>Customer</span></a>
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






</ul>
</li>
