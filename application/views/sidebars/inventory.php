<?php foreach ($allowed_modules->result() as $module) { ?>
    <?php if ($this->lang->line("module_" . $module->module_id) == "Items") { ?>
        <!-- <li class="has_sub">
            <a href="#" <?php if ($_SERVER['REQUEST_URI'] == "/items" || $_SERVER['REQUEST_URI'] == "/items/categories") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-mail"></i><span>Inventory </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled"> -->
        <li>
            <a href="<?php echo site_url("$module->module_id"); ?>"><i class="md md-label"> </i><?php echo $this->lang->line("module_" . $module->module_id) ?></a>
        </li>
        <li>
            <a href="<?php echo site_url("items/categories"); ?>"><i class="md md-label"> </i>Categories</a>
        </li>
        <li>
            <a href="<?php echo site_url("items/push"); ?>"><i class="md md-label"> </i>Product Transfer</a>
        </li>
        <!-- </ul>
        </li> -->
        <!--<li class="has_sub">
           <a href="#" class="waves-effect waves-light"><i class="md md-redeem"></i><span>Receiving </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                    <ul class="list-unstyled"> -->
        <li><a href="<?php echo site_url("receivings"); ?>"><i class="md md-label"> </i> Update Inventory</a></li>
        <li><a href="<?php echo site_url("receivings/history"); ?>"><i class="md md-label"> </i>Inventory History</a></li>
        <li><a href="<?php echo site_url("receivings/transfer_history"); ?>"><i class="md md-label"> </i>Transfer History</a></li>
        <!-- <li><a href="<?php echo site_url("receivings"); ?>"><i class="md md-label"> </i>Returns</a></li> -->
        <!-- </ul>
                                </li> -->
        <li>
            <a href="<?php echo site_url("suppliers"); ?>" class="waves-effect waves-light"><i class="md md-invert-colors-on"></i><span>Suppliers</span></a>
        </li>
<?php
    }
}
?>






</ul>
</li>