                            <?php
//                            var_dump($allowed_modules->result());
                            foreach ($allowed_modules as $module) { ?>
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

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Audits </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li><a href="audit">Audit Trail</a></li>
                                        </ul>
                                    </li>

                                    <li class="has_sub">    
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> LPO </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li><a href="lpo">Create LPO</a></li>
                                            <li><a href="lpo/history">View LPOs</a></li>
                                        </ul>
                                    </li>

                                    <!-- Jude, Transfer Mode here -->

                                    <?php
                                        $pendingRequests = $this->Receiving->get_pending_requests($this->config->item('branch_name'));
                                        $pendingRequestsCount = $pendingRequests != null ? count($pendingRequests):0;
                                        $incomingTransfers = $this->Receiving->get_incoming_transfers($this->config->item('branch_name'));
                                        $incomingTransfersCount = $incomingTransfers != null ? count($incomingTransfers) : 0;
                                        $pendingItems = $this->Receiving->get_pending_items($this->config->item('branch_name'));
                                        $pendingItemsCount = $pendingItems != null ? count($pendingItems) : 0;

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
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Transfers <?php echo $totalCount > 0 ? '<span class="dot"></span>' : '';  ?>  </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">
                                            <li><a href="items/request_item">Request Items  </a></li>
                                            <li><a href="items/pending_requests">Pending Requests <?php echo $pendingRequestsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a></li>
                                            <li><a href="items/incoming_transfers">Incoming Transfers <?php echo $incomingTransfersCount > 0 ? '<span class="dot"></span>' : '';  ?>  </a></li>  
                                            <?php 
                                            if($this->config->item('is_warehouse') != 'YES'){  ?>
                                                <li><a href="items/pending_items">Pending Items <?php echo $pendingItemsCount > 0 ? '<span class="dot"></span>' : '';  ?>  </a></li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Account") {

                                            $expiryItemsCount = $this->Specific_employee->getExpiryItemsCount();
                                            // $expiryItemsCount = 0;
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
                                        <a href="" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span> <?php echo $expiryItemsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="<?php echo site_url("reports/account_report"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/reports/account_report") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo 'Reports' ?></span> <?php echo $expiryItemsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a>
                                            </li>
                                            <!-- <li>
                                                <a href="<?php echo site_url("expense/categories"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/expense/categories") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Expense Categories</span></a>
                                            </li> -->
                                            <li>
                                                <a href="<?php echo site_url("expense"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/expense") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Expense Management</span></a>
                                            </li>
                                            <!-- <li>
                                                <a href="<?php echo site_url("account/processed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/processed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
                                            </li> -->
                                            <li>
                                                <a href="<?php echo site_url("account/sales_day_book"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/sales_day_book") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Sales Day Book</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("account/purchase_day_book"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/purchase_day_book") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Purchase Day Book</span></a>
                                            </li>

                                            <li>
                                                <a href="<?php echo site_url("profit_and_loss"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/profit_and_loss") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Profit and Loss</span></a>
                                            </li>

                                            <!-- <li>
                                                <a href="<?php echo site_url("account/unprocessed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/unprocessed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed Payments</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("account/processed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/processed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
                                            </li> -->
                                          
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
                                            <li> <a href="<?php echo site_url("laboratory"); ?>"><i class="md md-label"> </i>Available Test</a></li>
                                            <li><a href="<?php echo site_url("laboratory/test_start"); ?>"><i class="md md-label"> </i>New Test</a></li>
                                            <!-- <li id="search"><a><i class="md md-label"> </i>Test Results Status</a></li> -->
                                            <li><a href="<?php echo site_url("laboratory/search_patients"); ?>"><i class="md md-label"> </i>Search Patient</a></li>
                                            <li>
                                                <a href="<?php echo site_url("account/unprocessed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/unprocessed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed Payments</span></a>
                                            </li>
                                            <li>
                                                <a href="<?php echo site_url("account/processed_payment"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/account/processed_payment") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
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

                                            <li>
                                                <a href="<?php echo site_url("items/global_search"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/items/global_search") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-extension"></i><span>Global Search</span></a>
                                            </li>

                                            <!-- <li>
											<a href="<?php echo site_url("receivings"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/receivings") : ?>class="waves-effect waves-light active" <?php else :  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-fast-rewind"></i><span>Returns</span></a>
										</li> -->
                                        </ul>
                                    </li>
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Receivings") { ?>

                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span> Stock Taking </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">

                                            <li>
                                                <a href="<?php echo site_url("stockintake/new"); ?>"><i class="md md-label"> </i>New Stock Intake</a>
                                            </li>

                                            <li>
                                                <a href="<?php echo site_url("stockintake/history"); ?>"><i class="md md-label"> </i>View Stock Intakes</a>
                                            </li>

                                            <?php $stkid = $this->Receiving->get_inprogress_stock_taking()->stock_id;
                                                if($stkid > 0){ ?>
                                                <li>
                                                    <a href="<?php echo site_url("stockintake"); ?>"><i class="md md-label"> </i> Join Stock Taking </a>
                                                </li>
                                                <?php  } ?>
                                        </ul>
                                    </li>


                                    <li class="has_sub">
                                        <a href="#" class="waves-effect waves-light"><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_" . $module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
                                        <ul class="list-unstyled">

                                            <li>
                                                <a href="<?php echo site_url("items/push"); ?>"><i class="md md-label"> </i>Product Transfer</a>
                                            </li>
                                            <li><a href="<?php echo site_url("receivings"); ?>"><i class="md md-label"> </i> Update Inventory</a></li>
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
                                            <li>
                                                <a href="<?php echo site_url("sales"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> New Sales </span></a>
                                            </li>
                                            <!-- <li>
                                                <a href="<?php echo site_url("sales/pill"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales/pill") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Pill Reminder </span></a>
                                            </li> -->
                                            <!-- <li>
                                                <a href="<?php echo site_url("sales/manage"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/sales/manage") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Sale History </span></a>
                                            </li> -->

                                            <li>
                                                <a href="<?php echo site_url("items/check_price"); ?>" class="waves-effect waves-light"><i class="md md-call-split"></i>  <span> Check Price </span></a>
                                            </li>

                                            <li>
                                                <a href="<?php echo site_url("sales/check_receipt"); ?>" class="waves-effect waves-light"> <i class="md md-call-split"></i> <span> Print Receipt </span></a>
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

                                            <li>
                                                <a href="<?php echo site_url("companies"); ?>" <?php if ($_SERVER['REQUEST_URI'] == "/companies") : ?>class="waves-effect waves-light active" <?php else : ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Companies </span></a>
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
                                <?php } elseif ($this->lang->line("module_" . $module->module_id) == "Reports") {
                                    continue;
                                ?>
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
