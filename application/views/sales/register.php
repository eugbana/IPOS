<?php $this->load->view("partial/header"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<?php
		if (isset($error)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
		}

		if (!empty($warning)) {
			echo "<div class='alert alert-dismissible alert-warning'>" . $warning . "</div>";
		}

		if (isset($success)) {
			echo "<div class='alert alert-dismissible alert-success'>" . $success . "</div>";
		}
        $is_return = false;
		$mode = $this->CI->sale_lib->get_mode();
		if($mode == 'return'){
		    $is_return = true;
        }
		if(isset($expiry)){
		    var_dump($expiry);
        }
//		var_dump($this->_ci_cached_vars);
		?>

		<div id="register_wrapper">

			<!-- Top register controls -->

			<?php echo form_open($controller_name . "/change_mode", array('id' => 'mode_form', 'class' => 'form-horizontal panel panel-default')); ?>
			<div class="panel-body form-group">
				<ul>
					<li class="pull-left first_li">
						<label class="control-label"><?php echo $this->lang->line('sales_mode'); ?></label>
					</li>
					<li class="pull-left">
						<?php echo form_dropdown('mode', $modes, $mode, array('onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
					</li>
					<?php
					if ($this->config->item('dinner_table_enable') == TRUE) {
					?>
						<!-- <li class="pull-left first_li">
							<label class="control-label"><?php echo $this->lang->line('sales_table'); ?></label>
						</li>
						<li class="pull-left">
							<?php echo form_dropdown('dinner_table', $empty_tables, $selected_table, array('onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
						</li> -->
					<?php
					}
					echo form_hidden('stock_location',$stock_location);
//					if (count($stock_locations) > 1) {
//					?>
<!--						<li class="pull-left">-->
<!--							<label class="control-label">--><?php //echo $this->lang->line('sales_stock_location'); ?><!--</label>-->
<!--						</li>-->
<!--						<li class="pull-left">-->
<!--							--><?php //echo form_dropdown('stock_location', $stock_locations, $stock_location, array('onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
<!--						</li>-->
<!--					--><?php
//					}
//                    var_dump($is_return);
                    if($can_vend){
                        ?>
                        <li class="pull-right">
                            <button class='btn btn-default btn-sm modal-dlg' id='show_irecharge_button' data-href='<?php echo site_url($controller_name . "/viewIRecharge"); ?>' title='Power purchase, data, airtime and others'>
                                <span class="glyphicon glyphicon-flash">&nbsp</span>IRecharge
                            </button>
                        </li>
                    <?php

                    }
					?>



					<li class="pull-right">
						<button class='btn btn-default btn-sm modal-dlg' id='show_suspended_sales_button' data-href='<?php echo site_url($controller_name . "/suspended"); ?>' title='<?php echo $this->lang->line('sales_suspended_sales') . ' Sales (Wait for data..)' ?>'>
							<span class="glyphicon glyphicon-align-justify">&nbsp</span><?php echo $this->lang->line('sales_suspended_sales'); ?>
						</button>
					</li>

					<?php
					//if ($this->Employee->has_grant('reports_sales', $this->session->userdata('person_id'))) {
					?>
					<!-- <li class="pull-right">
						<?php echo anchor(
							$controller_name . "/manage",
							'<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . $this->lang->line('sales_takings'),
							array('class' => 'btn btn-primary btn-sm', 'id' => 'sales_takings_button', 'title' => $this->lang->line('sales_takings'))
						); ?>
					</li> -->
					<?php
					//}
					?>
				</ul>
			</div>
			<?php echo form_close(); ?>

			<?php $tabindex = 0; ?>

			<?php
            $url = $controller_name . "/add";
            $form_id = "add_item_form";
            if($is_return){
                $url = $controller_name."/fetch_sale_items";
//                $form_id = "return_item_form";
            }
            echo form_open($url, array('id' => $form_id, 'class' => 'form-horizontal panel panel-default')); ?>
            <?php echo form_hidden('sale_type', 'retail'); ?>
            <div class="panel-body form-group">
                <ul>
                    <li class="pull-left first_li">
                        <label for="item" class='control-label'><?php echo $this->lang->line('sales_find_or_scan_item_or_receipt'); ?></label>
                    </li>
                    <li class="pull-left">
                        <?php
                        echo form_input(array('name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex));
                        ?>
                        <span class="ui-helper-hidden-accessible" role="status"></span>
                    </li>
                    <?php
                    if ($this->Employee->get_logged_in_employee_info()->role == 5) {
                        $disable_new = "disabled";
                        //sale officer cannot enter new item
                    }
                    ?>
                    <li class="pull-right">
                        <button id='new_item_button' <?= $disable_new ?> class='btn btn-info btn-sm pull-right modal-dlg' data-btn-new='<?php echo $this->lang->line('common_new') ?>' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url("items/view"); ?>' title='<?php echo $this->lang->line($controller_name . '_new_item'); ?>'>
                            <span class="glyphicon glyphicon-tag">&nbsp</span><?php echo $this->lang->line($controller_name . '_new_item'); ?>
                        </button>
                    </li>
                </ul>
            </div>
            <?php echo form_close();
             ?>


			<!-- Sale Items List -->

			<table class="sales_table_100" id="register">
				<thead>
					<tr>
                        <?php
                        if($is_return){
                            ?>
                            <th></th>
                        <?php

                        }
                        ?>
                        <th style="width: 5%;"><?php echo $this->lang->line('common_delete'); ?></th>
                        <th style="width: 12%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
                        <th style="width: 28%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
                        <th style="width: 14%;"><?php echo $this->lang->line('sales_price'); ?></th>
                        <th style="width: 10%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
                        <th style="width: 9%;"><?php echo "Batch" ?></th>
                        <th style="width: 7%;"><?php echo $this->lang->line('sales_discount'); ?></th>
                        <th style="width: 10%;"><?php echo $this->lang->line('sales_total'); ?></th>
                        <th style="width: 5%;"><?php echo 'Sales Type'; //$this->lang->line('sales_update');
                            ?></th>
                        <th style="width: 3%;"></th>

					</tr>
				</thead>



				<tbody id="cart_contents">
					<?php if (!isset($cart) || count($cart) <= 0) { ?>
						<tr>
							<td colspan='10'>
								<div class='alert alert-dismissible alert-info'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
							</td>
						</tr>
						<?php } else {
						foreach (array_reverse($cart, TRUE) as $line => $item) {
						     if ($item['reference'] == 0) { ?>
									<?php echo form_open($controller_name . "/edit_item/$line", array('class' => 'form-horizontal', 'id' => 'cart_' . $line)); ?>
									<tr>
                                        <?php
                                        if($is_return){
                                            ?>
                                            <td><input type="checkbox" class="return-check" data-itemid="<?=$item['item_id']?>" data-check="<?=$item['line']?>"/></td>
                                            <?php
                                        }
                                        ?>
										<td><?php echo anchor($controller_name . "/delete_item/$line", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
										<td><?php echo $item['item_number']; ?></td>
										<td style="align: center;">
											<?php echo $item['name']; ?><br /> <?php if ($item['stock_type'] == '0') : echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']';
											endif; ?>
											<?php echo form_hidden('location', $item['item_location']);
											 echo form_hidden('stockno', $item['in_stock']);
											 echo form_hidden('itemid', $item['item_id']);
											 echo form_hidden('line', $item['line']); ?>
										</td>

										<?php
										if(isset($items_module_allowed)) {
										?>
											<td><?php if($item['stock_name'] == $this->lang->line('wholesale')) {
													echo form_input(array('name' => 'price', 'class' => 'form-control input-sm', 'readonly' => 'readonly', 'value' => to_currency_no_money($item['wholeprice']), 'tabindex' => ++$tabindex));
												} else {
													echo form_input(array('name' => 'price', 'class' => 'form-control input-sm', 'readonly' => 'readonly', 'value' => to_currency_no_money($item['price']), 'tabindex' => ++$tabindex));
												} ?></td>
										<?php
										} else {
										?>
											<td>
												<?php if ($item['stock_name'] == $this->lang->line('wholesale')) {
													echo to_currency($item['wholeprice']);
												} else {
													echo to_currency($item['price']);
												} ?>
												<?php
                                                if($is_return){
                                                    echo form_hidden('price', to_currency_no_money($item['price']));
                                                }else{
                                                    echo form_hidden('price', to_currency_no_money($item['price']));
                                                }
                                                 ?>
											</td>
										<?php
										}
										?>

										<td>
											<?php
											if ($item['is_serialized'] == 1) {
												echo to_quantity_decimals($item['quantity']);
												echo form_hidden('quantity', $item['quantity']);
											} else {
												if($is_return){
                                                    echo form_input(array('name' => 'quantity', 'class' => 'form-control input-sm return-item-'.$line, 'value' => $item['quantity'],'max'=>$item['quantity'],
                                                        'tabindex' => ++$tabindex,'min'=>0,'data-index'=>$line,'disabled'=>'disabled'));
                                                }else{
                                                    echo form_input(array('name' => 'quantity', 'class' => 'form-control input-sm', 'value' => $item['quantity'], 'tabindex' => ++$tabindex,'data-index'=>$line));
                                                }
											}

											?>
										</td>
                                        <td>
                                            <?php
                                            if($is_return){
                                                ?>
                                                <input type="text" required="required" value="<?=$item['batch_no']?>" class="form-control return-item-<?=$line?>" disabled data-index="<?=$line?>" name="batch_no"/>
                                                <?php
                                            }
                                            ?>
                                        </td>

										<td><?php echo form_input(array('name' => 'discount','data-index'=>$line, 'class' => 'form-control input-sm', 'value' => to_decimals($item['discount'], 0), 'tabindex' => ++$tabindex)); ?></td>
										<td><p id="item-total-price<?=$line?>"><?php if ($item['stock_name'] == $this->lang->line('wholesale')) {
                                                    echo to_currency($item['wholeprice'] * $item['quantity'] - $item['wholeprice'] * $item['quantity'] * $item['discount'] / 100);
                                                    $price = $item['wholeprice'];
                                                } else {
                                                    echo to_currency($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100);
                                                    $price = $item['price'];
                                                } ?></p>
                                            <?php
                                            if($is_return){
                                                ?>
                                                <input type="hidden" id="item-price<?=$line?>" class="return-item-<?=$line?>" disabled value="<?=$price?>">
                                                <?php
                                            }else{
                                                ?>
                                                <input type="hidden" id="item-price<?=$line?>" value="<?=$price?>">
                                                <?php
                                            }
                                            ?>
                                        </td>
										<td><?php
                                            if($is_return){
                                                echo form_dropdown('qty_type', $qtytypes, $item['qty_selected'], array('id' => 'qty_type','disabled'=>'disabled','class'=>'return-item-'.$line, 'data-index'=>$line));
                                            }else{
                                                echo form_dropdown('qty_type', $qtytypes, $item['qty_selected'], array('id' => 'qty_type','data-index'=>$line));
                                            }
                                             ?></td>
										 <td><?php $piller = $item['item_id'];
													$liner = $item['line'];
													//uncomment to add pill reminder button
													//echo anchor($controller_name . "/add_sale_pill/$piller", '<span class="glyphicon glyphicon-time"></span>'); ?>
											</td>
										<?php ?>
									</tr>
									<?php echo form_close(); ?>



									<?php foreach ($cart as $let => $unline) { ?>

										<?php if ($unline['item_id'] == $item['item_id'] && $unline['reference'] == 1) { ?>
											<?php echo form_open($controller_name . "/edit_salepill_item/$let", array('class' => 'form-horizontal', 'id' => 'cart_' . $let)); ?>
											<tr>
												<td><?php echo anchor($controller_name . "/delete_item/$let", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
												<td><?php echo 'Pill Reminder'; ?>
												</td>
												<td>
													<div class="input-group  date datetimepicker3">
														 <span class="input-group-addon input-sm">Start <span class="glyphicon glyphicon-calendar"></span></span>

														<?php echo form_input(
															array(
																'name' => 'time_started',
																'id' => 'time_started',
																'class' => 'form-control input-sm',
																'value' => $unline['time_started'],

															)
														); ?>
													</div>

													<div class="input-group  date datetimepicker3">
														 <span class="input-group-addon input-sm">End <span class="glyphicon glyphicon-calendar"></span> </span>

														<?php echo form_input(
															array(
																'name' => 'time_ended',
																'id' => 'time_ended',
																'class' => 'form-control input-sm',
																'value' => $unline['time_ended'],

															)
														); ?>
													</div>
												</td>
												<td>
													<!-- <?php echo to_currency($unline['price']); ?> -->
													<?php echo to_currency('3.50'); //get current price from API ?>
													<?php echo form_hidden('price', to_currency_no_money($unline['price'])); ?>

													<?php echo form_hidden('item_id', $unline['item_id']); ?>
													<!-- <?php echo form_hidden('reference', $unline['reference']); ?> -->
													<?php echo form_hidden('reference', 1); ?>
													<?php echo form_hidden('line', $unline['line']); ?>
												</td>

												<td><?php echo form_dropdown('reminder_value', $pill_period, $unline['reminder_value'], array('id' => 'reminder_value')); ?>

												</td>
												<td>
													<?php echo form_input(
														array(
															'name' => 'no_of_days',
															'id' => 'no_of_days',
															'class' => 'form-control input-sm',
															'value' => $unline['no_of_days'],
															'placeholder' => 'No of Doses'
														)
													); ?>

												</td>
												<td>
													<!-- <?php echo to_currency($unline['price'] * $unline['reminder_value'] * $unline['no_of_days']); ?> -->
													<?php echo to_currency(3.50 * $unline['reminder_value'] * $unline['no_of_days']); ?>
												</td>

											</tr>

							<?php
											echo form_close();
										}
									}
								}
							
							?>
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<!-- Overall Sale -->

		<div id="overall_sale" class="panel panel-default">
			<div class="panel-body">
				<?php
//                $e_info = $this->Employee->get_logged_in_employee_info();
//                $extraConfig = $this->Appconfig->get_extra_config(['company_id'=>$e_info->branch_id,'company_branch_id'=>$e_info->branch_id]);
				if(isset($customer)) {
				?>
					<table class="sales_table_100">
						<tr>
							<th style='width: 55%;'><?php echo $this->lang->line("sales_customer"); ?></th>
							<th style="width: 45%; text-align: right;"><?php echo $customer; ?></th>
						</tr>
						<?php
						if (!empty($customer_email)) {
						?>
							<tr>
								<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_email"); ?></th>
								<th style="width: 45%; text-align: right;"><?php echo $customer_email; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($company_name)) {
						?>
							<tr>
								<th style='width: 55%;'> Company </th>
								<th style="width: 45%; text-align: right;"><?php echo $company_name; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($company_discount)) {
						?>
							<tr>
								<th style='width: 55%;'> Company Discount </th>
								<th style="width: 45%; text-align: right;"><?php echo $company_discount; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($company_markup)) {
						?>
							<tr>
								<th style='width: 55%;'> Company Sales Markup </th>
								<th style="width: 45%; text-align: right;"><?php echo $company_markup; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($company_wallet)) {
						?>
							<tr>
								<th style='width: 55%;'> Company Wallet </th>
								<th style="width: 45%; text-align: right;"><?php echo $company_wallet; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($company_credit)) {
						?>
							<tr>
								<th style='width: 55%;'> Company Credit Limit </th>
								<th style="width: 45%; text-align: right;"><?php echo $company_credit; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($customer_address)) {
						?>
							<tr>
								<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_address"); ?></th>
								<th style="width: 45%; text-align: right;"><?php echo $customer_address; ?></th>
							</tr>
						<?php
						}
						?>
						<?php
						if (!empty($customer_location)) {
						?>
							<tr>
								<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_location"); ?></th>
								<th style="width: 45%; text-align: right;"><?php echo $customer_location; ?></th>
							</tr>
						<?php
						}
						?>
						<tr>
							<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_discount"); ?></th>
							<th style="width: 45%; text-align: right;"><?php echo $customer_discount_percent . ' %'; ?></th>
						</tr>
						<tr>
							<th style='width: 55%;'>Wallet</th>
							<th style="width: 45%; text-align: right;"><?php echo to_currency($customer_wallet); ?></th>
						</tr>
						<tr>
							<th style='width: 55%;'>Credit Limit</th>
							<th style="width: 45%; text-align: right;"><?php echo to_currency($customer_credit_limit); ?></th>
						</tr>

						<?php
						if ($customer_sale_markup > 0) {
						?>
							<tr>
								<th style='width: 55%;'>Sale Markup</th>
								<th style="width: 45%; text-align: right;"><?php echo $customer_sale_markup; ?></th>
							</tr>
						<?php
						}
						?>

						<?php
						if ($customer_is_staff) {

						?>

							<tr>
								<th style='width: 55%;'>This Month Credit</th>
								<th style="width: 45%; text-align: right;"><?php echo to_currency($already_used_credit); ?></th>
							</tr>
						<?php
						}
						?>
						<?php if ($this->config->item('customer_reward_enable') == TRUE) : ?>
							<?php
							if (!empty($customer_rewards)) {
							?>
								<tr>
									<th style='width: 55%;'><?php echo $this->lang->line("rewards_package"); ?></th>
									<th style="width: 45%; text-align: right;"><?php echo $customer_rewards['package_name']; ?></th>
								</tr>
								<tr>
									<th style='width: 55%;'><?php echo $this->lang->line("customers_available_points"); ?></th>
									<th style="width: 45%; text-align: right;"><?php echo $customer_rewards['points']; ?></th>
								</tr>
							<?php
							}
							?>
						<?php endif; ?>
						<tr>
							<th style='width: 55%;'>Total Spent</th>
							<th style="width: 45%; text-align: right;"><?php echo to_currency($customer_total); ?></th>
						</tr>
						<?php if (!empty($mailchimp_info)) { ?>
							<tr>
								<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_mailchimp_status"); ?></th>
								<th style="width: 45%; text-align: right;"><?php echo $mailchimp_info['status']; ?></th>
							</tr>
						<?php } ?>
					</table>

					<?php echo anchor(
						$controller_name . "/remove_customer",
						'<span class="glyphicon glyphicon-remove">&nbsp</span>' . $this->lang->line('common_remove') . ' ' . $this->lang->line('customers_customer'),
						array('class' => 'btn btn-danger btn-sm', 'id' => 'remove_customer_button', 'title' => $this->lang->line('common_remove') . ' ' . $this->lang->line('customers_customer'))
					);
					?><input type="hidden" id="customer-set" value="1"/>
				<?php
				} else {
				?>
					<?php echo form_open($controller_name . "/select_customer", array('id' => 'select_customer_form', 'class' => 'form-horizontal')); ?>
					<div class="form-group" id="select_customer">
                        <input type="hidden" id="customer-set" value="0"/>
					<p><b>SHORT KEYS</b></p>
					- End & Print Sale:  <b>Alt + E</b> <br/>
					- Item / Price Search:  <b>Alt + P</b> <br/>
					- Reprint last Sales:  <b>Alt + S</b><br/>
					- Check Receipt: <b>Alt + R</b><br/>
					<hr/>

						<label id="customer_label" for="customer" class="control-label" style="margin-bottom: 1em; margin-top: -1em;"><?php echo $this->lang->line('sales_select_customer'); ?></label>
						<?php echo form_input(array('name' => 'customer', 'id' => 'customer', 'class' => 'form-control input-sm', 'value' => $this->lang->line('sales_start_typing_customer_name'))); ?>

						<button class='btn btn-info btn-sm modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url("customers/view"); ?>' title='<?php echo $this->lang->line($controller_name . '_new_customer'); ?>'>
							<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_new_customer'); ?>
						</button>
					</div>
					<?php echo form_close(); ?>
				<?php
				}
                if(isset($extraConfig) && !empty($extraConfig)){
                    ?>
                    <input type="hidden" id="customer-required" value="<?=$extraConfig[0]->customer_details_mandated?>"/>
                <?php
                }

				?>

				<table class="sales_table_100" id="sale_totals">
					<tr>
						<th style="width: 20%;">Total Cost</th>
						<th style="width: 20%; text-align: right;" id="initial-cost"><?php echo to_currency($initial_cost); ?><p></p>
						</th>
					</tr>
					<tr>
						<th style="width: 20%;">Total Discount</th>
						<th style="width: 20%; text-align: right;" id="discount-given"><?php echo to_currency($discount); ?><p></p>
						</th>
					</tr>
					<tr>
						<th style="width: 20%;">VAT <?php echo $this->config->item('vat') . '%'; ?> </th>
						<th style="width: 20%; text-align: right;" id="total-vat"><?php echo to_currency($total_vat); ?><p></p>
						</th>
					</tr>
					<tr>
						<th style="width: 20%;"><?php echo $this->lang->line('sales_sub_total'); ?></th>
						<th style="width: 20%; text-align: right; " id="sub-total"><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $subtotal); ?><p></p>
						</th>
					</tr>

					<?php
					foreach ($taxes as $tax_group_index => $sales_tax) {
					?>
						<!-- <tr>
							<th style='width: 20%;'><?php echo $sales_tax['tax_group']; ?></th>
							<th style="width: 20%; text-align: right;"><?php echo to_currency($sales_tax['sale_tax_amount']); ?></th>
						</tr> -->
					<?php
					}
					?>

					<tr>
						<th style='width: 55%;'>Total</th>
						<th style="width: 45%; text-align: right;"><span id="sale_total"><?php echo to_currency($total + $total_vat); ?></span></th>
					</tr>
				</table>

				<?php
				// Only show this part if there are Items already in the sale.
				if (count($cart) > 0) {
				?>
					<table class="sales_table_100" id="payment_totals">
						<tr>
							<th style="width: 55%;"><?php echo $this->lang->line('sales_payments_total'); ?><p></p>
							</th>
							<th style="width: 45%; text-align: right;" id="payments-total"><?php echo to_currency($payments_total); ?></th>
						</tr>
						<tr>
							<th style="width: 55%;"><?php echo $this->lang->line('sales_amount_due'); ?></th>
							<th style="width: 45%; text-align: right;"><span id="sale_amount_due"><?php echo to_currency($amount_due); ?></span></th>
						</tr>
					</table>

					<div id="payment_details">
						<?php
						// Show Complete sale button instead of Add Payment if there is no amount due left
						if ($payments_cover_total) {
						?>
							<?php echo form_open($controller_name . "/add_payment", array('id' => 'add_payment_form', 'class' => 'form-horizontal')); ?>
							<table class="sales_table_100">
								<tr>
									<td><?php echo $this->lang->line('sales_payment'); ?></td>
									<td>
										<?php echo form_dropdown('payment_type', $payment_options, $selected_payment_type, array('id' => 'payment_type', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'auto', 'disabled' => 'disabled')); ?>
									</td>
								</tr>
                                <tr>
                                    <td><?php echo "Payment cover total" ?></td>
                                    <td>
                                        <?php echo $payments_cover_total?'Yes':'No'; ?>
                                    </td>
                                </tr>
								<tr>
									<td><span id="amount_tendered_label"><?php
                                            if($is_return){echo "Amount Returned"; }
                                            else{
                                                echo $this->lang->line('sales_amount_tendered');}
                                            ?></span></td>
									<td>
										<?php echo form_input(array('name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm disabled', 'disabled' => 'disabled', 'value' => '0', 'size' => '5', 'tabindex' => ++$tabindex)); ?>
									</td>
								</tr>
                                <?php
                                if ($lower_sale_auth_required) : ?>
                                    <tr>
                                        <td><span>Sale Authorization code:</span></td>
                                        <td>
                                            <?php echo form_input(array('name' => 'lower_sale_auth','required'=>'required', 'id' => 'lower_sale_auth', 'class' => 'form-control input-sm disabled', 'type' => 'password', 'value' => '', 'size' => '20', 'maxlength' => 20, 'placeholder' => 'lower sale auth code', 'tabindex' => ++$tabindex)); ?>
                                        </td>
                                    </tr>
                                <?php endif;
//                                if ($return_approved == FALSE) : ?>
<!--                                    <tr>-->
<!--                                        <td>-->
<!--												<span id="discount_authorization">-->
<!--													Return Authorization Code-->
<!--												</span>-->
<!--                                        </td>-->
<!--                                        <td>-->
<!--                                            --><?php //echo form_input(array('name' => 'discount_authorization_val', 'id' => 'discount_authorization_val', 'class' => 'form-control input-sm disabled', 'type' => 'password', 'value' => '', 'size' => '20', 'maxlength' => 20, 'placeholder' => 'password/code', 'tabindex' => ++$tabindex)); ?>
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                --><?php //endif;
                                ?>
							</table>
							<?php echo form_close(); ?>
							<!-- Only show this part if the payment cover the total and in sale or return mode -->

							<?php if ($sales_or_return_mode == '1') { ?>
                                <?php if ($is_return && $return_approved == FALSE){
                                    ?>
                                    <div class='btn btn-sm btn-success pull-right' id='approve_returns' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-credit-card">&nbsp</span>Approve</div>
                                    <?php
                                }else{?>
                                    <div class='btn btn-sm btn-success pull-right' id='finish_sale_button' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo $this->lang->line('sales_complete_sale'); ?></div>
                                    <?php
                                }
							}
						 }
						else {
						    echo form_open($controller_name . "/add_payment", array('id' => 'add_payment_form', 'class' => 'form-horizontal')); ?>
							<table class="sales_table_100">
								<tr>
									<td><?php echo $this->lang->line('sales_payment'); ?></td>
									<td>
										<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id' => 'payment_type', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
									</td>
								</tr>
								<tr>
									<td><span id="amount_tendered_label">
                                            <?php
                                            if($is_return){echo "Amount Returned"; }
                                            else{
									            echo $this->lang->line('sales_amount_tendered');}
                                            ?>
                                        </span></td>
									<td>
										<?php echo form_input(array('name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm non-giftcard-input b', 'value' => to_currency_no_money($amount_due), 'size' => '5', 'tabindex' => ++$tabindex)); ?>
									</td>
								</tr>
								<?php if ($total_discount > 0 || $mode == 'return') :
                                    if($total_discount > 0 && (!$discount_approved || $discount_approved == FALSE)){
                                        ?>
                                        <tr>
                                            <td>
												<span id="discount_authorization">
													Disc. Authorization Code
												</span>
                                            </td>
                                            <td>
                                                <?php echo form_input(array('name' => 'discount_authorization_val', 'id' => 'discount_authorization_val', 'class' => 'form-control input-sm disabled', 'type' => 'password', 'value' => '', 'size' => '20', 'maxlength' => 20, 'placeholder' => 'password/code', 'tabindex' => ++$tabindex)); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    if ($mode == 'return' && $return_approved == false) : ?>
										<tr>
											<td>
												<span id="discount_authorization">
													Authorization Code (Returns)
												</span>
											</td>
											<td>
												<?php echo form_input(array('name' => 'discount_authorization_val', 'id' => 'discount_authorization_val', 'class' => 'form-control input-sm disabled', 'type' => 'password', 'value' => '', 'size' => '20', 'maxlength' => 20, 'placeholder' => 'password/code', 'tabindex' => ++$tabindex)); ?>
											</td>
										</tr>
									<?php endif;
									endif; ?>
							</table>
							<?php echo form_close();
//							echo $this->sale_lib->get_auth_code(true);

                            if ($total_discount == 0  && $mode != 'return') : ?>
								<div class='btn btn-sm btn-success pull-right' id='add_payment_button' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-credit-card">&nbsp</span><?php echo $this->lang->line('sales_add_payment'); ?></div>
                            <?php elseif ($is_return && $return_approved == FALSE) : ?>
                                <div class='btn btn-sm btn-success pull-right' id='approve_returns' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-credit-card">&nbsp</span>Approve</div>
                            <?php else : ?>
								<?php if ($total_discount > 0 && $discount_approved == FALSE) : ?>
									<div class='btn btn-sm btn-success pull-right' id='verify_discount' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-credit-card">&nbsp</span>Approve Discount</div>
								<?php else : ?>
									<div class='btn btn-sm btn-success pull-right' id='add_payment_button' tabindex='<?php echo ++$tabindex; ?>'><span class="glyphicon glyphicon-credit-card">&nbsp</span><?php echo $this->lang->line('sales_add_payment'); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						<?php } ?>

						<!-- Only show this part if there is at least one payment entered. -->
						<?php if (count($payments) > 0) { ?>
							<table class="sales_table_100" id="register">
								<thead>
									<tr>
										<th style="width: 10%;"><?php echo $this->lang->line('common_delete'); ?></th>
										<th style="width: 60%;"><?php echo $this->lang->line('sales_payment_type'); ?></th>
										<th style="width: 20%;"><?php echo $this->lang->line('sales_payment_amount'); ?></th>
									</tr>
								</thead>

								<tbody id="payment_contents">
									<?php foreach ($payments as $payment_id => $payment) { ?>
										<tr>
											<td><?php echo anchor($controller_name . "/delete_payment/$payment_id", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
											<td><?php echo $payment['payment_type']; ?></td>
											<td style="text-align: right;"><?php echo to_currency($payment['payment_amount']); ?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						<?php } ?>
					</div>

					<?php echo form_open($controller_name . "/cancel", array('id' => 'buttons_form')); ?>
                    <input type="hidden" id="l_auth_code" name="l_auth_code"/>
					<div class="form-group" id="buttons_sale">
						<div class='btn btn-sm btn-default pull-left' id='suspend_sale_button'><span class="glyphicon glyphicon-align-justify">&nbsp</span><?php echo $this->lang->line('sales_suspend_sale'); ?></div>
						<!-- Only show this part if the payment cover the total -->
						<?php if ($quote_or_invoice_mode && isset($customer)) { ?>
							<div class='btn btn-sm btn-success' id='finish_invoice_quote_button'><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo $mode_label; ?></div>
						<?php } ?>
						<div class='btn btn-sm btn-danger pull-right' id='cancel_sale_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo $this->lang->line('sales_cancel_sale'); ?></div>
					</div>
					<?php echo form_close(); ?>

					<!--Only show this part if the payment cover the total -->
					<?php if ($payments_cover_total || $quote_or_invoice_mode) { ?>
						<div class="container-fluid">
							<div class="no-gutter row">
								<div class="form-group form-group-sm">
									<div class="col-xs-12">
										<?php echo form_label($this->lang->line('common_comments'), 'comments', array('class' => 'control-label', 'id' => 'comment_label', 'for' => 'comment')); ?>
										<?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'class' => 'form-control input-sm', 'value' => $comment, 'rows' => '2')); ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-group form-group-sm">
									<div class="col-xs-6">
										<label for="sales_print_after_sale" class="control-label checkbox">
											<?php echo form_checkbox(array('name' => 'sales_print_after_sale', 'id' => 'sales_print_after_sale', 'value' => 1, 'checked' => $print_after_sale)); ?>
											<?php echo $this->lang->line('sales_print_after_sale') ?>
										</label>
									</div>

									<?php if (!empty($customer_email)) { ?>
										<div class="col-xs-6">
											<label for="email-receipt" class="control-label checkbox">
												<?php echo form_checkbox(array('name' => 'email_receipt', 'id' => 'email_receipt', 'value' => 1, 'checked' => $email_receipt)); ?>
												<?php echo $this->lang->line('sales_email_receipt'); ?>
											</label>
										</div>
									<?php } ?>
								</div>
							</div>
							<?php if (($mode == "sale") && $this->config->item('invoice_enable') == TRUE) { ?>
								<div class="row">
									<div class="form-group form-group-sm">

										<div class="col-xs-6">
											<label class="control-label checkbox" for="sales_invoice_enable">
												<?php echo form_checkbox(array('name' => 'sales_invoice_enable', 'id' => 'sales_invoice_enable', 'value' => 1, 'checked' => $invoice_number_enabled)); ?>
												<?php echo $this->lang->line('sales_invoice_enable'); ?>
											</label>
										</div>

										<div class="col-xs-6">
											<div class="input-group input-group-sm">
												<span class="input-group-addon input-sm">#</span>
												<?php echo form_input(array('name' => 'sales_invoice_number', 'id' => 'sales_invoice_number', 'class' => 'form-control input-sm', 'value' => $invoice_number)); ?>
											</div>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>

		<?php // print_r($register_id); 
		?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
	    let item_el = $('#item');
	    let body_el = $("body");

		$('.datetimepicker3').datetimepicker();
		$('.return-check').change(function () {
            var ite = $(this).attr('data-check');
		    if($(this).is(':checked')){
		        $('.return-item-'+ite).removeAttr('disabled');
		        $.get('<?=site_url($controller_name."/register_for_returns")?>',{"line":$(this).attr('data-itemid'),"type":1},function () {
                });
            }else{
                $('.return-item-'+ite).attr('disabled','disabled');
                $.get('<?=site_url($controller_name."/register_for_returns")?>',{"line":$(this).attr('data-itemid'),"type":2},function () {
                });
            }
        })
		$("#roles").change(function() {
			// $.ajax({
			// 	url: '<?php echo site_url("sales/set_role"); ?>',
			//     method: 'POST',
			//     dataType: 'text',
			//     data: {
			//         role: $('#roles').val(),
			//     }, 
			// 	success: function (response) {
			//         alert($('#roles').val());
			//     }
			// });
			//alert($('#roles').val());
			$.post('<?php echo site_url($controller_name . "/set_role"); ?>', {
				role: $('#roles').val()
			});
		});

		body_el.keydown(function(e){
			if ((e.metaKey || e.altKey) && ( String.fromCharCode(e.which).toLowerCase() === 'e') ) {
				$('#add_payment_button').click();
			}
		});


		body_el.keydown(function(e){
         var keyCode = e.keyCode || e.which;
		 if(keyCode === 113){
				$('#add_payment_button').click();
		 }
    	});


		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is executed in order to add the item to the cart

        <?php
            if($is_return){
                ?>
            item_el.autocomplete({
            source: '<?php echo site_url($controller_name . "/item_search"); ?>',
            minChars: 2,
            autoFocus: false,
            delay: 500,
            select: function(a, ui) {
                if(ui.item.value){
                    $(this).val(ui.item.value);
                    //window.alert(ui.item.value); this will show the item_id of the selected item
                    // $("#return_item_form").submit();
                    $("#add_item_form").submit();
                }

                return false;
            }
        });
        <?php
            }else{
                ?>
        item_el.autocomplete({
            source: '<?php echo site_url($controller_name . "/item_search"); ?>',
            minChars: 2,
            autoFocus: false,
            search: function(event, ui) {
                $('.spinner').show();
            },
            response: function(event, ui) {
                $('.spinner').hide();
            },
            delay: 500,
            select: function(a, ui) {
                $(this).val("item "+ui.item.value);
                //window.alert(ui.item.value); this will show the item_id of the selected item
                $("#add_item_form").submit();
                return false;
            }
        });
        <?php

            }
        ?>

		item_el.focus();

		item_el.keypress(function(e) {
			if (e.which == 13) {
				$('#add_item_form').submit();
				return false;
			}
		});

		item_el.blur(function() {
			$(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
		});

		var clear_fields = function() {
			if ($(this).val().match("<?php echo $this->lang->line('sales_start_typing_item_name') . '|' . $this->lang->line('sales_start_typing_customer_name'); ?>")) {
				$(this).val('');
			}
		};

		$("#customer").autocomplete({
			source: '<?php echo site_url($controller_name . "/customer_search");
						?>',
			// minChars: 1,
            minLength:3,
			delay: 100,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				$("#select_customer_form").submit();
                //$.post('<?//=site_url($controller_name."/select_customer")?>//',$('#select_customer_form').serializeArray(),function (data) {
                //    if(data.customer_details){
                //        $('#selected_customer_label').html(data.customer_details.name);
                //    }else{}
                //},'json');
                // $("#select_customer_form");
			}

		});

		$(".giftcard-input").autocomplete({
			source: '<?php echo site_url("giftcards/suggest"); ?>',
			minChars: 0,
			delay: 10,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				$("#add_payment_form").submit();
			}
		});

		$('#item, #customer').click(clear_fields).dblclick(function(event) {
			$(this).autocomplete("search");
		});

		$('#customer').blur(function() {
			$(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
		});

		$('#comment').keyup(function() {
			$.post('<?php echo site_url($controller_name . "/set_comment"); ?>', {
				comment: $('#comment').val()
			});
		});

		<?php
		if ($this->config->item('invoice_enable') == TRUE) {
		?>
			$('#sales_invoice_number').keyup(function() {
				$.post('<?php echo site_url($controller_name . "/set_invoice_number"); ?>', {
					sales_invoice_number: $('#sales_invoice_number').val()
				});
			});

			var enable_invoice_number = function() {
				var enabled = $("#sales_invoice_enable").is(":checked");
				$("#sales_invoice_number").prop("disabled", !enabled).parents('tr').show();
				return enabled;
			}

			enable_invoice_number();

			$("#sales_invoice_enable").change(function() {
				var enabled = enable_invoice_number();
				$.post('<?php echo site_url($controller_name . "/set_invoice_number_enabled"); ?>', {
					sales_invoice_number_enabled: enabled
				});
			});
		<?php
		}
		?>

		$("#sales_print_after_sale").change(function() {
			$.post('<?php echo site_url($controller_name . "/set_print_after_sale"); ?>', {
				sales_print_after_sale: $(this).is(":checked")
			});
		});

		$('#email_receipt').change(function() {
			$.post('<?php echo site_url($controller_name . "/set_email_receipt"); ?>', {
				email_receipt: $('#email_receipt').is(':checked') ? '1' : '0'
			});
		});

		$("#finish_sale_button").click(function() {
		    $('#l_auth_code').val($('#lower_sale_auth').val());
			$('#buttons_form').attr('action', '<?= site_url($controller_name."/complete_receipt"); ?>');
			$('#buttons_form').submit();
		});

		$("#finish_invoice_quote_button").click(function() {
			$('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/complete"); ?>');
			$('#buttons_form').submit();
		});

		$("#suspend_sale_button").click(function() {
			$('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/suspend"); ?>');
			$('#buttons_form').submit();
		});

		$("#cancel_sale_button").click(function() {
			if (confirm('<?php echo $this->lang->line("sales_confirm_cancel_sale"); ?>')) {
				$('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/cancel"); ?>');
				$('#buttons_form').submit();
			}
		});

		$("#add_payment_button").click(function() {
            <?php
                if($is_return){
                    ?>
            var cf = confirm("If no item is selected, all item shown will be returned. Continue?");
            if(cf){
                $('#add_payment_form').submit();
            }else{
                return false;
            }
            <?php
                }else{?>
                    $('#add_payment_form').submit();
            <?php
        }
            ?>

		});
		$("#approve_returns").on('click',function(){
		    let c_ele = $(this);
            $(this).html('Please wait...').attr('disabled', true);
            $.post("<?=site_url($controller_name.'/approve_discount/1')?>",{code: $('#discount_authorization_val').val(),},function (data,status) {
                data = JSON.parse(data);
                //console.log('data', data);
                if (status === "success") {
                    if (data.success) {
                        $.notify({
                            title: 'Authorization successful.',
                            message: data.message,
                            type: 'success'
                        });
                        setTimeout(() => {
                            //window.location.reload();
                            window.location.href = "<?php echo site_url('sales') ?>";
                        }, 1500);
                    } else {
                        c_ele.html('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;Approve return').attr('disabled', false);
                        $.notify({
                            title: 'Authorization failed.',
                            message: data.message,
                        }, {
                            type: 'danger'
                        });
                        return false;
                    }
                } else {
                    c_ele.html('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;Approve Return').attr('disabled', false);
                    $.notify({
                        title: 'Request failed!',
                        message: data.message,
                        type: 'danger'
                    });
                    return false;
                }
            });
        });
		var disc_approved = false;
		<?php
            if($disount_approved){
                ?>
        disc_approved = true;
        <?php
            }
        ?>

		$("#verify_discount").click(function() {
			$(this).html('Please wait...').attr('disabled', true);
			//console.log('code: ' + $('#discount_authorization_val').val());
			$.post('<?php echo site_url($controller_name . "/approve_discount"); ?>', {
				code: $('#discount_authorization_val').val(),
			}, function(data, status) {
				data = JSON.parse(data);
				//console.log('data', data);
				if (status === "success") {
					if (data.success) {
						$.notify({
							title: 'Authorization successful.',
							message: data.message,
							type: 'success'
						});
						setTimeout(() => {
							//window.location.reload();
							window.location.href = "<?php echo site_url('sales') ?>";
						}, 1500);
					} else {
						$("#verify_discount").html('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;Approve discount').attr('disabled', false);
						$.notify({
							title: 'Authorization failed.',
							message: data.message,
						}, {
							type: 'danger'
						});
						return;
					}
				} else {
					$("#verify_discount").html('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;Approve discount').attr('disabled', false);
					$.notify({
						title: 'Request failed!',
						message: data.message,
						type: 'danger'
					});
					return;
				}
				//console.log('status' + status);
				//console.log('resp', data);
			});
		});

		$("#payment_type").change(check_payment_type).ready(check_payment_type);

		$("#cart_contents input").keypress(function(event) {
			if (event.which == 13) {
				$(this).parents("tr").prevAll("form:first").submit();
			}
		});

		$("#amount_tendered").keypress(function(event) {
			if (event.which == 13) {
				$('#add_payment_form').submit();
			}
		});

		$("#finish_sale_button").keypress(function(event) {
			if (event.which == 13) {
				$('#finish_sale_form').submit();
			}
		});

		dialog_support.init("a.modal-dlg, button.modal-dlg");

		table_support.handle_submit = function(resource, response, stay_open) {
			// alert('table support handle function called!');
			if (response.success) {
				if (resource.match(/customers$/)) {
					$("#customer").val(response.id);
					$("#select_customer_form").submit();
				} else {
					var $stock_location = $("select[name='stock_location']").val();
					$("#item_location").val($stock_location);
					$("#item").val(response.id);
					if (stay_open) {
						$("#add_item_form").ajaxSubmit();
					} else {
						$("#add_item_form").submit();
					}
				}
			}
		}

		$('[name="price"],[name="quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="reminder_value"],[name="no_of_days"]').change(function() {
			let lin = $(this).attr('data-index');
			let this_el = $(this);
		    $.ajax({
                url:"<?=site_url($controller_name.'/edit_item')?>"+'/'+lin+'/1',
                type: 'post',
                data: $(this).parents("tr").prevAll("form:first").serializeArray(),
                dataType: 'json',
                success:function(res){
                    console.log('response: ',res);
                    if(res.error || res.cart_items.warning || res.cart_items.error){
                        const err = res.error || res.cart_items.warning || res.cart_items.error || 'error occured during operation';
                        this_el.css({"border":"#FF0000 1px solid"});
                        alert(err);
                    }else{
                        const data = res.cart_items;
                        $('#initial-cost').html(data.initial_cost);
                        $('#discount-given').html(data.total_discount);
                        $('#total-vat').html(data.total_vat);
                        $('#sale_total').html(data.sale_total);
                        $('#sub-total').html(data.subtotal);
                        $('#payments-total').html(data.payments_total);
                        $('#sale_amount_due').html(data.amount_due);
                        const edited_item = data.cart[lin];
                        const edited_item_price = edited_item['quantity']*$('#item-price'+lin).val() - (edited_item['quantity']*$('#item-price'+lin).val()*0.01*edited_item['discount'])
                        $('#item-total-price'+lin).text(edited_item_price);
                        $('#amount_tendered').val(0);
                        const t_d = data.total_discount.replace(/[^0-9]/gi,'')
                        if(this_el.attr('name') === 'discount' && parseFloat(t_d) > 0 && disc_approved === false){
                            location.assign('<?php echo site_url($controller_name); ?>');
                        }
                    }
                },
                error:function(x,h,er){
                    alert(er);
                }
            })
		    // $(this).parents("tr").prevAll("form:first").submit()
		});
		$('[name="qty_type"],[name="time_started"],[name="batch_no"]').change(function() {
			$(this).parents("tr").prevAll("form:first").submit();
		});


	});

	function check_payment_type() {
		//update the payment type in the session


		//var cash_rounding = <?php echo json_encode($cash_rounding); ?>;

		// if ($("#payment_typs").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>") {
		// 	$("#sale_total").html("<?php echo to_currency($total + $total_vat); ?>");
		// 	$("#sale_amount_due").html("<?php echo to_currency($amount_due); ?>");
		// 	$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
		// 	$("#amount_tendered:enabled").val('').focus();
		// 	$(".giftcard-input").attr('disabled', false);
		// 	$(".non-giftcard-input").attr('disabled', true);
		// 	$(".giftcard-input:enabled").val('').focus();
		// } else if ($("#payment_type").val() == "<?php echo $this->lang->line('sales_cash'); ?>" && cash_rounding) {
		// 	$("#sale_total").html("<?php echo to_currency($cash_total + $total_vat); ?>");
		// 	$("#sale_amount_due").html("<?php echo to_currency($cash_amount_due); ?>");
		// 	$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
		// 	$("#amount_tendered:enabled").val('<?php echo to_currency_no_money($cash_amount_due); ?>');
		// 	$(".giftcard-input").attr('disabled', true);
		// 	$(".non-giftcard-input").attr('disabled', false);
		// } else {
		// 	$("#sale_total").html("<?php echo to_currency($non_cash_total); ?>");
		// 	$("#sale_amount_due").html("<?php echo to_currency($non_cash_amount_due); ?>");
		// 	$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
		// 	$("#amount_tendered:enabled").val('<?php echo to_currency_no_money($non_cash_amount_due); ?>');
		// 	$(".giftcard-input").attr('disabled', true);
		// 	$(".non-giftcard-input").attr('disabled', false);
		// }
		// $("#sale_total").html("<?php echo to_currency($cash_total + $total_vat); ?>");
		// $("#sale_amount_due").html("<?php echo to_currency($cash_amount_due); ?>");
		// $("#amount_tendered_label").html("<?php
        //     if($is_return){echo "Amount Returned"; }
        //     else{
        //         echo $this->lang->line('sales_amount_tendered');}
        //     ?>");
		// $("#amount_tendered:enabled").val('<?php echo to_currency_no_money($cash_amount_due); ?>');
		// $(".giftcard-input").attr('disabled', true);
		// $(".non-giftcard-input").attr('disabled', false);
	}
</script>

<?php $this->load->view("partial/footer", array('close_side' => true)); ?>
