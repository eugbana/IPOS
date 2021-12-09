<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save/' . $person_info->person_id, array('id' => 'customer_form', 'class' => 'form-horizontal')); ?>
<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#customer_basic_info"><?php echo $this->lang->line("customers_basic_information"); ?></a>
	</li>
	<?php
	if (!empty($stats)) {
	?>
		<li role="presentation">
			<a data-toggle="tab" href="#customer_stats_info"><?php echo $this->lang->line("customers_stats_info"); ?></a>
		</li>
	<?php
	}
	?>

</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="customer_basic_info">
		<fieldset>
			<?php $this->load->view("people/form_basic_info"); ?>

			<!-- <div class="form-group form-group-sm">
				<?php echo form_label('Family', 'family', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_dropdown('family_id', $families, $selected_family, array('class' => 'form-control')); ?>
				</div>
			</div> -->

			<div class="form-group form-group-sm">
				<?php echo form_label('Company', 'company', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_dropdown('company_id', $companies, $selected_company, array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('customers_discount'), 'discount_percent_label', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?php echo form_input(
							array(
								'name' => 'discount_percent',
								'id' => 'discount_percent',
								'class' => 'form-control input-sm',
								'value' => $person_info->discount_percent
							)
						); ?>
						<span class="input-group-addon input-sm"><b>%</b></span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label('Sale Markup', 'sale_markup_label', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?php echo form_input(
							array(
								'name' => 'sale_markup',
								'id' => '',
								'class' => 'form-control input-sm',
								'value' => $person_info->sale_markup
							)
						); ?>
						<!-- <span class="input-group-addon input-sm"><b>₦</b></span> -->
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label('Credit Limit', 'credit_limit_label', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?php echo form_input(
							array(
								'name' => 'credit_limit',
								'id' => 'credit_limit',
								'class' => 'form-control input-sm',
								'value' => $person_info->credit_limit
							)
						); ?>
						<span class="input-group-addon input-sm"><b>₦</b></span>
					</div>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Age', 'company_name', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_input(
						array(
							'name' => 'age',
							'id' => 'age',
							'class' => 'form-control input-sm',
							'value' => $person_info->age
						)
					); ?>
				</div>
			</div>

			<!-- <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('customers_company_name'), 'company_name', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_input(
						array(
							'name' => 'company_name',
							'id' => 'company_name',
							'class' => 'form-control input-sm',
							'value' => $person_info->company_name
						)
					); ?>
				</div>
			</div> -->

			<?php if ($this->config->item('customer_reward_enable') == TRUE) : ?>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('rewards_package'), 'rewards', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_dropdown('package_id', $packages, $selected_package, array('class' => 'form-control')); ?>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_available_points'), 'available_points', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_input(
							array(
								'name' => 'available_points',
								'id' => 'available_points',
								'class' => 'form-control input-sm',
								'value' => $person_info->points,
								'disabled' => ''
							)
						); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php


			?>
			<div class="form-group form-group-sm">
				<?php echo form_label('Staff?', 'type-label', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox('type', '1', $person_info->staff ? TRUE : FALSE); ?>
				</div>
			</div>
		</fieldset>
	</div>

	<?php
	if (!empty($stats)) {
	?>
		<div class="tab-pane" id="customer_stats_info">
			<fieldset>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_total'), 'total', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php if (!currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
							<?php echo form_input(
								array(
									'name' => 'total',
									'id' => 'total',
									'class' => 'form-control input-sm',
									'value' => to_currency_no_money($stats->total),
									'disabled' => ''
								)
							); ?>
							<?php if (currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_max'), 'max', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php if (!currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
							<?php echo form_input(
								array(
									'name' => 'max',
									'id' => 'max',
									'class' => 'form-control input-sm',
									'value' => to_currency_no_money($stats->max),
									'disabled' => ''
								)
							); ?>
							<?php if (currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_min'), 'min', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php if (!currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
							<?php echo form_input(
								array(
									'name' => 'min',
									'id' => 'min',
									'class' => 'form-control input-sm',
									'value' => to_currency_no_money($stats->min),
									'disabled' => ''
								)
							); ?>
							<?php if (currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_average'), 'average', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php if (!currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
							<?php echo form_input(
								array(
									'name' => 'average',
									'id' => 'average',
									'class' => 'form-control input-sm',
									'value' => to_currency_no_money($stats->average),
									'disabled' => ''
								)
							); ?>
							<?php if (currency_side()) : ?>
								<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_quantity'), 'quantity', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php echo form_input(
								array(
									'name' => 'quantity',
									'id' => 'quantity',
									'class' => 'form-control input-sm',
									'value' => $stats->quantity,
									'disabled' => ''
								)
							); ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_avg_discount'), 'avg_discount', array('class' => 'control-label col-xs-3')); ?>
					<div class="col-xs-8">
						<div class="input-group input-group-sm">
							<?php echo form_input(
								array(
									'name' => 'avg_discount',
									'id' => 'avg_discount',
									'class' => 'form-control input-sm',
									'value' => $stats->avg_discount,
									'disabled' => ''
								)
							); ?>
							<span class="input-group-addon input-sm"><b>%</b></span>
						</div>
					</div>
				</div>

			</fieldset>
		</div>
	<?php
	}
	?>


</div>


<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		$('#customer_form').validate($.extend({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						dialog_support.hide();
						table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
					},
					dataType: 'json'
				});
			},

			rules: {
				first_name: "required",
				last_name: "required",
				email: {
					remote: {
						url: "<?php echo site_url($controller_name . '/ajax_check_email') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"person_id": "<?php echo $person_info->person_id; ?>",
							// email is posted by default
						})
					}
				},
				phone_number: {
					remote: {
						// url: "<?php echo site_url($controller_name . '/ajax_check_phone') ?>",
						url: "<?php echo $is_lab ? site_url('laboratory/ajax_check_phone') : site_url($controller_name . '/ajax_check_phone') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"person_id": "<?php echo $person_info->person_id; ?>",
							// phone is posted by default
						})
					}
				}

			},

			messages: {
				first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
				last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
				email: "<?php echo $this->lang->line('customers_email_duplicate'); ?>",
				phone_number: "Phone number already exists",

			}
		}, form_support.error));

		//companies search
		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#company_id").autocomplete({
			source: '<?php echo site_url("companies/companies_search_suggestions"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				console.log(ui);
				// window.alert(ui.item.value); //this will show the item_id of the selected item
				// $("#add_item_form").submit();
				return false;
			}
		});

		// $('#customer_id').focus();

		$('#company_id').keypress(function(e) {
			if (e.which == 13) {
				$(this).val(ui.item.value);
				// $('#add_item_form').submit();
				return false;
			}
		});

		$('#company_id').blur(function() {
			// $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
			// $(this).val("all");
		});

	});
</script>