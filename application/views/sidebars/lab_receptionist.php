<?php
foreach ($allowed_modules as $module) {
?>
	<?php if ($this->lang->line("module_" . $module->module_id) == "Laboratory") { ?>
		<li class="has_sub">
			<a href="#" <?php if (strstr($_SERVER['REQUEST_URI'], "/laboratory")) : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-send"></i><span>Laboratory </span><span class="pull-right"><i class="md md-add"></i></span></a>
			<ul class="list-unstyled">
				<li> <a href="<?php echo site_url("laboratory"); ?>"><i class="md md-label"> </i>Available Test</a></li>
				<li><a href="<?php echo site_url("laboratory/test_start"); ?>"><i class="md md-label"> </i>New Test</a></li>
				<li id="search"><a><i class="md md-label"> </i>Test Results Status</a></li>
				<li><a href="<?php echo site_url("laboratory/search_patients"); ?>"><i class="md md-label"> </i>Search Patient</a></li>
			</ul>
		</li>

	<?php } elseif ($this->lang->line("module_" . $module->module_id) == "Customers") { ?>

	<?php } else { ?>
		<li>

			<a href="<?php echo site_url("$module->module_id"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/$module->module_id") : ?>class="waves-effect waves-light active" <?php else : ?> class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>

		</li>
<?php
	}
}
?>
