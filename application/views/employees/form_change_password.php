<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('employees/save/' . $person_info->person_id, array('id' => 'employee_form', 'class' => 'form-horizontal')); ?>
<div class="tab-content">
	<div class="tab-pane fade in active" id="employee_login_info">
		<fieldset>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('employees_username'), 'username', array('class' => 'required control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
						<?php echo form_input(
							array(
								'name' => 'username',
								'id' => 'username',
								'class' => 'form-control input-sm',
								'value' => $person_info->username,
								'readonly' => 'true'
							)
						); ?>
					</div>
				</div>
			</div>

			<?php $password_label_attributes = $person_info->person_id == "" ? array('class' => 'required') : array(); ?>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('employees_current_password'), 'current_password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
				<div class='col-xs-8'>
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
						<?php echo form_password(
							array(
								'name' => 'current_password',
								'id' => 'current_password',
								'class' => 'form-control input-sm'
							)
						); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('employees_password'), 'password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
				<div class='col-xs-8'>
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
						<?php echo form_password(
							array(
								'name' => 'password',
								'id' => 'password',
								'class' => 'form-control input-sm'
							)
						); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('employees_repeat_password'), 'repeat_password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
				<div class='col-xs-8'>
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
						<?php echo form_password(
							array(
								'name' => 'repeat_password',
								'id' => 'repeat_password',
								'class' => 'form-control input-sm'
							)
						); ?>
					</div>
				</div>
			</div>


			<div class="form-group form-group-sm">
				<?php echo form_label('Image', 'items_image', array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<div class="fileinput <?php echo $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
						<div class="fileinput-new thumbnail" style="width: 100px; height: 100px;"></div>
						<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;">
							<img data-src="holder.js/100%x100%" alt="<?php echo $this->lang->line('items_image'); ?>" src="<?php echo $image_path; ?>" style="max-height: 100%; max-width: 100%;">
						</div>
						<div>
							<span class="btn btn-default btn-sm btn-file">
								<span class="fileinput-new"><?php echo $this->lang->line("items_select_image"); ?></span>
								<span class="fileinput-exists"><?php echo $this->lang->line("items_change_image"); ?></span>
								<input type="file" name="item_image" accept="image/*">
							</span>
							<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("items_remove_image"); ?></a>
						</div>
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
		$.validator.setDefaults({
			ignore: []
		});

		$.validator.addMethod("notEqualTo", function(value, element, param) {
			return this.optional(element) || value != $(param).val();
		}, '<?php echo $this->lang->line('employees_password_not_must_match'); ?>');

		$('#employee_form').validate($.extend({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						dialog_support.hide();
						if (!response.success) {
							$.notify(response.message, {
								type: 'danger'
							});
						} else {
							$.notify(response.message, {
								type: 'success'
							});
						}
					},
					dataType: 'json'
				});
			},
			rules: {
				current_password: {
					required: true,
					minlength: 8
				},
				password: {
					required: true,
					minlength: 8,
					notEqualTo: "#current_password"
				},
				repeat_password: {
					equalTo: "#password"
				}
			},
			messages: {
				password: {
					required: "<?php echo $this->lang->line('employees_password_required'); ?>",
					minlength: "<?php echo $this->lang->line('employees_password_minlength'); ?>"
				},
				repeat_password: {
					equalTo: "<?php echo $this->lang->line('employees_password_must_match'); ?>"
				}
			}
		}, form_support.error));
	});
</script>