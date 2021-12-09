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
		?>

		<div id="register_wrapper">

			<!-- Top register controls -->

			<?php echo form_open($controller_name . "/change_mode", array('id' => 'mode_form', 'class' => 'form-horizontal panel panel-default')); ?>
			<div class="panel-body form-group">
				<ul>
					<!-- <li class="pull-left first_li">
						<label class="control-label"><?php echo $this->lang->line('receivings_mode'); ?></label>
					</li> -->
					<!-- <li class="pull-left">
						<?php echo form_dropdown('mode', $modes, $mode, array('onchange' => "$('#mode_form').submit();", 'id' => 'mode_change', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
					</li> -->
					<li class="pull-left">
						<?php echo anchor("transfer/history", '<span class="md md-history">&nbsp</span>View Transfer History', array('class' => 'btn btn-info btn-sm pull-right', 'id' => 'show_receiving_history_button')); ?>
					</li>
					<?php
					if ($show_stock_locations && false) {
					?>
						<li class="pull-left">
							<label class="control-label"><?php echo $this->lang->line('receivings_stock_source'); ?></label>
						</li>
						<li class="pull-left">
							<?php echo form_dropdown('stock_source', $stock_locations, $stock_source, array('onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
						</li>


						<?php
						if ($mode == 'requisition') {
						?>
							<li class="pull-left">
								<label class="control-label"><?php echo $this->lang->line('receivings_stock_destination'); ?></label>
							</li>
							<li class="pull-left">
								<?php echo form_dropdown('stock_destination', $stock_locations, $stock_destination, array('onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
							</li>
					<?php
						}
					}
					?>
				</ul>
			</div>
			<?php echo form_close(); ?>

			<?php echo form_open($controller_name . "/request", array('id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default')); ?>
			<div class="panel-body form-group">
				<ul>
					<li class="pull-left first_li">
						<label for="item" , class='control-label'>
							<?php
							if ($mode == 'receive' or $mode == 'requisition') {
							?>
								<?php echo $this->lang->line('receivings_find_or_scan_item'); ?>
							<?php
							} else {
							?>
								<?php echo $this->lang->line('receivings_find_or_scan_item_or_receipt'); ?>
							<?php
							}
							?>
						</label>
					</li>
					<li class="pull-left">
						<?php echo form_input(array('name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => '1')); ?>
					</li>
					<li class="pull-right">
						<button id='new_item_button' class='btn btn-info btn-sm pull-right modal-dlg' data-btn-new='<?php echo $this->lang->line('common_new') ?>' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url("items/view"); ?>' title='<?php echo $this->lang->line('sales_new_item'); ?>'>
							<span class="glyphicon glyphicon-tag">&nbsp</span><?php echo $this->lang->line('sales_new_item'); ?>
						</button>
					</li>
				</ul>
			</div>
			<?php echo form_close(); ?>

			<!-- Receiving Items List -->

			<table class="sales_table_100" id="register">
				<thead>
					<tr>
						<th style="width:3%;"><?php echo $this->lang->line('common_delete'); ?></th>
						<th style="width:25%;"><?php echo $this->lang->line('receivings_item_name'); ?></th>

						<!-- <th style="width:15%;"><?php echo $this->lang->line('receivings_cost'); ?></th> -->
						<!-- <th style="width:10%;"><?php //echo $this->lang->line('receivings_cost_retail'); 
													?></th> -->
						<!-- <th style="width:7%;">Qty Received</th> -->
						<th style="width:7%;">Qty Ordered</th>


						<!-- <th style="width:15%;"><?php echo $this->lang->line('receivings_total'); ?></th> -->

						<th style="width:4%;"><?php echo 'Refresh'; ?></th>
						<!-- <th style="width:6%;"><?php echo 'Edit Item'; ?></th> -->
					</tr>
				</thead>

				<tbody id="cart_contents">
					<?php
					$update_cost = 0; //used to keep track of zero cost, or empty batch/expiry. if there is any zero cost, we show reminder on submission
					$empty_batch = 0; //
					if (count($cart) == 0) {
					?>
						<tr>
							<td colspan='10'>
								<div class='alert alert-dismissible alert-info'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
							</td>
						</tr>
						<?php
					} else {
						foreach (array_reverse($cart, TRUE) as $line => $item) {
							if ($item['price'] == 0) {
								$update_cost++;
							}
							//check if there is any empty expiry or batch_no
							if ($item['batch_no'] == '' || $item['expiry'] == '') {
								$empty_batch++;
							}

						?>
							<?php echo form_open($controller_name . "/edit_item/$line", array('class' => 'form-horizontal', 'id' => 'cart_' . $line)); ?>
							<tr>
								<td><?php echo anchor($controller_name . "/delete_item/$line", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
								<!-- <td><?php echo form_input(array('name' => 'batch_no', 'class' => 'form-control input-sm', 'value' => $item['batch_no'])); ?></td> -->
								<td style="align:center;">
									<?php echo $item['name']; ?><br /> <?php echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']'; ?>
									<?php echo form_hidden('location', $item['item_location']); ?>
								</td>
								<!-- <td>
									<div class="input-group  date datetimepicker1" id="datetimepicker1">
										<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>

										<?php echo form_input(
											array(
												'name' => 'expiry',
												'id' => 'expiry',
												'class' => 'form-control input-sm',
												'autocomplete' => 'off',
												'value' => $item['expiry']
											)
										); ?>
									</div>
								</td> -->


								<?php
								//if ($items_module_allowed && $mode != 'requisition') {

								?>
								<!-- <td><?php echo form_input(array('name' => 'price', 'readonly' => 'readonly', 'class' => 'form-control input-sm', 'value' => to_currency_no_money($item['price']))); ?></td> -->
								<?php
								//} else {
								?>
								<!-- <td>
										<?php echo $item['price']; ?>
										<?php echo form_hidden('price', to_currency_no_money($item['price'])); ?>
									</td> -->
								<?php
								//}
								?>

								<!-- Next line is no longer needed having fixed item price updating in items section -->
								<!-- <td><?php //echo form_input(array('name'=>'unit_price', 'class'=>'form-control input-sm', 'value'=>to_quantity_decimals($item['unit_price']))); 
											?></td> -->
								<!-- <td><?php echo form_input(array('name' => 'quantity', 'readonly' => 'readonly',  'class' => 'form-control input-sm', 'value' => $item['quantity'])); ?></td> -->
								<td><?php echo form_input(array('name' => 'receiving_quantity', 'class' => 'form-control input-sm', 'value' => to_quantity_decimals($item['receiving_quantity']))); ?></td>

								<!-- <td><?php echo to_currency($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100); ?><?php echo form_hidden('discount', $item['discount']); ?></td> -->
								<td><a href="javascript:$('#<?php echo 'cart_' . $line ?>').submit();" title=<?php echo $this->lang->line('receivings_update') ?>><span class="glyphicon glyphicon-refresh"></span></a></td>

								<!-- <td>
									<a href="items/view_update/<?php echo $item['item_id']; ?>" class="modal-dlg" data-btn-submit="Submit" title="Update Item"><span class="glyphicon glyphicon-edit"></span> </a>
								</td> -->
							</tr>

							<?php echo form_close(); ?>
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<!-- Overall Receiving -->

		<div id="overall_sale" class="panel panel-default">
			<div class="panel-body">

					<?php echo form_open($controller_name . "/change_location", array('class' => 'form-horizontal bg-dark', 'id' => 'change_location_form')); ?>

					<?php echo form_dropdown('location', $locator, $item['location'], array('id' => 'location', 'style' => 'width:100%;', 'class' => 'form-control',)); ?>
					<?php echo form_close();  ?>
				

				<table class="sales_table_100" id="sale_totals">
					<tr>
						<?php
						if ($mode != 'requisition') {
						?>
							<th style="width: 55%;"><?php echo $this->lang->line('sales_total'); ?></th>
							<th style="width: 45%; text-align: right;"><?php echo to_currency($total); ?></th>
						<?php
						} else {
						?>
							<th style="width: 55%;"></th>
							<th style="width: 45%; text-align: right;"></th>
						<?php
						}
						?>
					</tr>
				</table>

				<?php
				if (count($cart) > 0) {
				?>
					<div id="finish_sale">
						<?php
						if ($mode == 'requisition') {
						?>
							<?php echo form_open($controller_name . "/requisition_complete", array('id' => 'finish_receiving_form', 'class' => 'form-horizontal')); ?>
							<div class="form-group form-group-sm">
								<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?></label>
								<?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'class' => 'form-control input-sm', 'value' => $comment, 'rows' => '4')); ?>

								<div class="btn btn-sm btn-danger pull-left" id='cancel_receiving_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo $this->lang->line('receivings_cancel_receiving'); ?></div>

								<div class="btn btn-sm btn-success pull-right" id='finish_receiving_button'><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo $this->lang->line('receivings_complete_receiving'); ?></div>
							</div>
							<?php echo form_close(); ?>
						<?php
						} else {
						?>
							<?php echo form_open($controller_name . "/complete", array('id' => 'finish_receiving_form', 'class' => 'form-horizontal')); ?>
							<div class="form-group form-group-sm">
								<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?></label>
								<?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'class' => 'form-control input-sm', 'value' => $comment, 'rows' => '4'));
								?>

								<table class="sales_table_100" id="payment_details">
									<tr>
										<td>Print after Creation</td>
										<td>
											<?php echo form_checkbox(array('name' => 'recv_print_after_sale', 'id' => 'recv_print_after_sale', 'class' => 'checkbox', 'value' => 1, 'checked' => $print_after_sale));
											?>
										</td>
									</tr>

									<?php
									if ($mode == "receive") {
									?>
										<!-- <tr>
											<td><?php echo 'Supplier/Invoice No:'; //$this->lang->line('receivings_reference'); 
												?></td>
											<td>
												<?php echo form_input(array('name' => 'recv_reference', 'id' => 'recv_reference', 'class' => 'form-control input-sm', 'value' => $reference, 'size' => 5)); ?>
											</td>
										</tr> -->
									<?php
									}
									?>
									<!-- <tr>
										<td><?php //echo $this->lang->line('sales_payment'); 
											?></td>
										<td>
											<?php //echo form_dropdown('payment_type', $payment_options, array(), array('id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'auto')); 
											?>
										</td>
									</tr> -->

									<!-- <tr>
										<td><?php echo $this->lang->line('sales_amount_tendered'); ?></td>
										<td>
											<?php echo form_input(array('name' => 'amount_tendered', 'value' => '', 'class' => 'form-control input-sm', 'size' => '5')); ?>
										</td>
									</tr> -->
								</table>

								<div class='btn btn-sm btn-danger pull-left' id='cancel_receiving_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo $this->lang->line('receivings_cancel_receiving') ?></div>

								<div class='btn btn-sm btn-success pull-right' id='finish_receiving_button'><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo $this->lang->line('receivings_complete_receiving') ?></div>
							</div>
							<?php echo form_close(); ?>
						<?php
						}
						?>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {

		$("#item").autocomplete({
			source: '<?php echo site_url('items/item_search'); ?>',
			minChars: 1,
			delay: 10,
			autoFocus: false,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				$("#add_item_form").submit();
				return false;
			}
		});
		//$('#datetimepicker1').datetimepicker();

		$('#item').focus();
		$('.datetimepicker1').datetimepicker();

		$('#item').keypress(function(e) {
			if (e.which == 13) {
				$('#add_item_form').submit();
				return false;
			}
		});

		$('#item').blur(function() {
			$(this).attr('value', "<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
		});

		$('#comment').keyup(function() {
			$.post('<?php echo site_url($controller_name . "/set_comment"); ?>', {
				comment: $('#comment').val()
			});
		});

		$('#recv_reference').keyup(function() {
			$.post('<?php echo site_url($controller_name . "/set_reference"); ?>', {
				recv_reference: $('#recv_reference').val()
			});
		});

		$("#recv_print_after_sale").change(function() {
			$.post('<?php echo site_url($controller_name . "/set_print_after_sale"); ?>', {
				recv_print_after_sale: $(this).is(":checked")
			});
		});

		$('#item,#supplier').click(function() {
			$(this).attr('value', '');
		});

		$("#supplier").autocomplete({
			source: '<?php echo site_url("suppliers/suggest"); ?>',
			minChars: 1,
			delay: 10,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				$("#select_supplier_form").submit();
			}
		});

		dialog_support.init("a.modal-dlg, button.modal-dlg");

		$('#supplier').blur(function() {
			$(this).attr('value', "<?php echo $this->lang->line('receivings_start_typing_supplier_name'); ?>");
		});

		$("#finish_receiving_button").click(function() {
			var invoice_no = $("#recv_reference").val(); //not available on
			// var mode_change = $("#mode_change").val().trim();
			var update_cost = <?php echo $update_cost; ?>;
			var empty_batch = <?php echo $empty_batch; ?>;
			var supplier = <?php echo $supplier_id; ?>;
			console.log(supplier);

			if (supplier == '-1') {
				window.alert("Supplier must be selected");
			} else {
				$('#finish_receiving_form').submit();
			}

			// if (invoice_no == '') {
			// 	window.alert("Supplier's invoice cannot be empty.");
			// } else {

			// 	if (update_cost > 0 || empty_batch > 0) {
			// 		if (update_cost > 0)
			// 			window.alert("You must update and refresh all zero cost before submitting. There are " + update_cost + " items showing zero quantity.");
			// 		else
			// 			window.alert("You must insert all items batch and expiry dates. There are " + empty_batch + " items without batch no or expiry date.");
			// 	} else {
			// 		$('#finish_receiving_form').submit();
			// 	}
			// }

		});

		$("#cancel_receiving_button").click(function() {
			if (confirm('<?php echo $this->lang->line("receivings_confirm_cancel_receiving"); ?>')) {
				$('#finish_receiving_form').attr('action', '<?php echo site_url($controller_name . "/cancel_receiving"); ?>');
				$('#finish_receiving_form').submit();
			}
		});


		$("#cart_contents input").keypress(function(event) {
			if (event.which == 13) {
				$(this).parents("tr").prevAll("form:first").submit();
			}
		});

		table_support.handle_submit = function(resource, response, stay_open) {
			if (response.success) {
				if (resource.match(/suppliers$/)) {
					$("#supplier").attr("value", response.id);
					$("#select_supplier_form").submit();
				} else {
					$("#item").attr("value", response.id);
					if (stay_open) {
						$("#add_item_form").ajaxSubmit();
					} else {
						//$("#add_item_form").submit();
					}
				}
			}
		}

		$('[name="price"],[name="quantity"],[name="receiving_quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="batch_no"]').focusout(function() {
			// var index = $(this).parents("tr").index() - 1;
			// $("#cart_" + index).submit();
			$(this).parents("tr").prevAll("form:first").submit()
		});
		$('[name="expiry"]').change(function() {
			// var index = $(this).parents("tr").index() - 1;
			// $("#cart_" + index).submit();
			$(this).parents("tr").prevAll("form:first").submit()
		});


	});
</script>

<?php $this->load->view("partial/footer", array('close_side' => true)); ?>