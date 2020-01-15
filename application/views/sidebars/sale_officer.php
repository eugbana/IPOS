<?php
foreach ($allowed_modules->result() as $module) {
    ?>
    <?php if ($this->lang->line("module_" . $module->module_id) == "Sales") { ?>

        <!-- <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled"> -->
        <li><a href="<?php echo site_url("sales"); ?>"><i class="md md-credit-card"></i> Sales</a></li>
        <li><a href="<?php echo site_url("sales/manage"); ?>"><i class="md md-history"></i>Sales Transactions</a></li>
        <!-- </ul>
        </li> -->

    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Customers") { ?>


        <!-- <li class="has_sub">
            <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
            <ul class="list-unstyled"> -->
        <li>
            <a href="<?php echo site_url("customers"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/customers") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="fa fa-users"></i><span>Customer</span></a>
        </li>
        <!-- </ul>
        </li> -->
    <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Employees") {
            continue;
        } else { ?>
        <li class="has_sub">

            <a href="<?php echo site_url("$module->module_id"); ?>" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>

        </li>
<?php
    }
}
?>