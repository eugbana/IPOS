<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save/' . $company_info->company_id, array('id' => 'customer_form', 'class' => 'form-horizontal')); ?>
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
			<?php $this->load->view("company/form_basic_info"); ?>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('customers_discount'), 'discount_percent_label', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?php echo form_input(
							array(
								'name' => 'discount_percent',
								'id' => 'discount_percent',
								'class' => 'form-control input-sm',
								'value' => $company_info->discount
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
								'placeholder' => 'Example (1.5)',
								'value' => $company_info->markup
							)
						); ?>
						<span class="input-group-addon input-sm"><b></b></span>
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
								'value' => $company_info->credit_limit
							)
						); ?>
						<span class="input-group-addon input-sm"><b>₦</b></span>
					</div>
				</div>
			</div>

		</fieldset>
	</div>

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
				company_name: "required",
				// cac: "required",
				// tin: "required",
				email: {
					remote: {
						url: "<?php echo site_url($controller_name . '/ajax_check_email') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"company_id": "<?php echo $company_info->company_id; ?>",
							// email is posted by default
						})
					}
				},
				phone_number: {
					required: true,
					minlength: 11,
					maxlength: 11,
					remote: {
						url: "<?php echo site_url($controller_name . '/ajax_check_phone') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"company_id": "<?php echo $company_info->company_id; ?>",
							// phone is posted by default
						})
					}
				},
                cac: {
					remote: {
						url: "<?php echo site_url($controller_name . '/ajax_check_cac') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"company_id": "<?php echo $company_info->company_id; ?>",
							// cac is posted by default
						})
					}
				},
				tin: {
					remote: {
						url: "<?php echo site_url($controller_name . '/ajax_check_tin') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"company_id": "<?php echo $company_info->company_id; ?>",
							// tin is posted by default
						})
					}
				}

			},

			messages: {
				company_name: "Company Name is required",
				cac: "C.A.C. is required",
				tin: "Tax Identitification Number is required",
				email: "<?php echo $this->lang->line('customers_email_duplicate'); ?>",
				phone_number: {
					required: "Phone number is required",
					remote: "Phone number already exists",
					minlength: "Phone number should be exactly 11 characters long",
					maxlength: "Phone number should be exactly 11 characters long",
				} ,
				cac: "C.A.C. Number already exists",
				tin: "Tax Identitification Number already exists",

			}
		}, form_support.error));
	});
</script>