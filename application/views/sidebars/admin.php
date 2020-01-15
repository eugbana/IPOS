                            <?php foreach ($allowed_modules->result() as $module) { ?>
                                <?php if ($this->lang->line("module_" . $module->module_id) == "Employees") { ?>

                                    <li>
                                        <a href="<?php echo site_url("$module->module_id"); ?>" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?></span></a>
                                    </li>

                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Config") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li><a href="<?php echo site_url("$module->module_id"); ?>">Settings</a></li>

                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Account") { ?>

                                    <li class="has_sub">
                                        <a href="" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("reports/account_report"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/reports/account_report") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo 'Reports' ?></span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("account/unprocessed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/unprocessed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("account/processed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/processed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Laboratory") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("laboratory/new_results"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/laboratory/new_results") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed Results</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("laboratory/pending_results"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/laboratory/pending_results") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Pending Results</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("laboratory/completed_results"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/laboratory/completed_results") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Completed Results</span></a>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Items") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("$module->module_id"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/$module->module_id") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-layers"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?></span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("items/categories"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/items/categories") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-extension"></i><span>Categories</span></a>
                                            </li>

                                            <!-- <li>
											<a href="<?php echo site_url("receivings"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/receivings") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-fast-rewind"></i><span>Returns</span></a>
										</li> -->
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Receivings") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">


                                            <li>
                                                <a href="<?php echo site_url("receivings/transfer_history"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/receivings/transfer_history") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-assessment"></i><span>Transfer History</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("receivings/history"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/receivings/history") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-subject"></i><span>Inventory History</span></a>
                                            </li>
                                            <!-- <li>
											<a href="<?php echo site_url("receivings"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/receivings") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-fast-rewind"></i><span>Returns</span></a>
										</li> -->
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Sales") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <!-- <li>
                                                <a href="<?php echo site_url("sales"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> New Sale </span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("sales/pill"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales/pill") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Pill Reminder </span></a>
                                            </li> -->
                                            <li>
                                                <a href="<?php echo site_url("sales/detailed_sales"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales/detailed_sales") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Sale History </span></a>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Customers") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("customers"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/customers") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> <?php echo $this->lang->line("module_" . $module->module_id); ?> </span></a>
                                            </li>

                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Suppliers") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("suppliers"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/suppliers") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> <?php echo $this->lang->line("module_" . $module->module_id); ?> </span></a>
                                            </li>

                                        </ul>
                                    </li>

                                <?php } else { ?>
                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("$module->module_id"); ?>" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                            </li>

                                        </ul>
                                    </li>
                            <?php
                                }
                            }
                            ?>