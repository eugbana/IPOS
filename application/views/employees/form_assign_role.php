<?php $this->load->view("partial/header"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content" style="margin-left:20%;margin-right:20%">
		<!-- bower:css -->


		<!-- end css template tags -->
		<!-- bower:js -->
		<?php //echo $person_info->person_id;
		?>

		<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); //print_r($special_module) 
											?></div>

		<ul id="error_message_box" class="error_message_box"></ul>



		<div class="tab-pane" id="employee_permission_info">
			<fieldset>
				<?php echo form_open($controller_name . '/view_assign/' . $person_info->person_id, array('id' => 'role_check', 'class' => 'form-horizontal')); ?>

				<div class="form-group form-group-sm" style="margin-top:10px">
					<?php echo form_label('', 'role', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('role_input', $roles, $selected_role, array('id' => 'role_input', 'class' => 'form-control')); ?>
					</div>
				</div>

				<p><?php echo $this->lang->line("employees_permission_desc"); ?></p>
				<?php echo form_close(); ?>

				<?php echo form_open($controller_name . '/save_employee_role/' . $person_info->person_id, array('id' => 'employee_rolform', 'class' => 'form-horizontal')); ?>
				<?php echo form_hidden('role', $selected_role, array('id' => 'role')); ?>
				<ul id="permission_list" class="check_default">

					<?php
					foreach ($all_modules as $module) {
						?>
						<li>
							<?php echo form_checkbox("grants[]", $module->module_id, $module->grant, "class='module'"); ?>
							<span class="medium"><?php echo $this->lang->line('module_' . $module->module_id); ?>:</span>
							<span class="small"><?php echo $this->lang->line('module_' . $module->module_id . '_desc'); ?></span>
							<?php
								foreach ($all_subpermissions as $permission) {
									$exploded_permission = explode('_', $permission->permission_id);
									if ($permission->module_id == $module->module_id) {
										$lang_key = $module->module_id . '_' . $exploded_permission[1];
										$lang_line = $this->lang->line($lang_key);
										$lang_line = ($this->lang->line_tbd($lang_key) == $lang_line) ? $exploded_permission[1] : $lang_line;
										if (!empty($lang_line)) {
											?>
										<ul>
											<li>
												<?php echo form_checkbox("grants[]", $permission->permission_id, $permission->grant); ?>
												<span class="medium"><?php echo $lang_line ?></span>
											</li>
										</ul>
							<?php
										}
									}
								}
								?>
						</li>
					<?php
					}
					?>
				</ul>

				<?php echo form_submit(array(
					'name' => 'role_submit',
					'id' => 'role_submit',
					//'value'=>$this->lang->line('common_submit'),
					'value' => 'Comm',
					'class' => 'btn btn-primary btn-sm pull-right'
				)); ?>
			</fieldset>
		</div>

	</div>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		$.validator.setDefaults({
			ignore: []
		});

		$.validator.addMethod("module", function(value, element) {
			var result = $("#permission_list input").is(":checked");
			$(".module").each(function(index, element) {
				var parent = $(element).parent();
				var checked = $(element).is(":checked");
				if ($("ul", parent).length > 0 && result) {
					result &= !checked || (checked && $("ul > li > input:checked", parent).length > 0);
				}
			});
			return result;
		}, '<?php echo $this->lang->line('employees_subpermission_required'); ?>');

		$("ul#permission_list > li > input[name='grants[]']").each(function() {
			var $this = $(this);
			$("ul > li > input", $this.parent()).each(function() {
				var $that = $(this);
				var updateCheckboxes = function(checked) {
					$that.prop("disabled", !checked);
					!checked && $that.prop("checked", false);
				}
				$this.change(function() {
					updateCheckboxes($this.is(":checked"));
				});
				updateCheckboxes($this.is(":checked"));
			});
		});

		/*$("#roles").on("change", function() {
		var role=("#roles").val();
      $.post('<?php echo site_url($controller_name . "/set_role"); ?>', {role: role});
		
   alert(this.value); 
});*/
		$("#role_input").on("change", function() {
			//var role=("#roles").val();
			$("#role_check").submit();
		});
		$("#role_submit").on("click", function() {
			event.preventDefault();
			//$("#role")=("#role_input").val();
			// alert($("#role_input").val());
			$('#role').val($('#role_input').val())
			$("#employee_rolform").submit();
		});

		$('#employee_form').validate($.extend({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						dialog_support.hide();
						table_support.handle_submit('<?php echo site_url('employees'); ?>', response);
					},
					dataType: 'json'
				});
			},
			rules: {
				first_name: "required",
				last_name: "required",
				username: {
					required: true,
					minlength: 5
				},

				password: {
					<?php
					if ($person_info->person_id == "") {
						?>
						required: true,
					<?php
					}
					?>
					minlength: 8
				},
				repeat_password: {
					equalTo: "#password"
				},
				email: "email"
			},
			messages: {
				first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
				last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
				username: {
					required: "<?php echo $this->lang->line('employees_username_required'); ?>",
					minlength: "<?php echo $this->lang->line('employees_username_minlength'); ?>"
				},

				password: {
					<?php
					if ($person_info->person_id == "") {
						?>
						required: "<?php echo $this->lang->line('employees_password_required'); ?>",
					<?php
					}
					?>
					minlength: "<?php echo $this->lang->line('employees_password_minlength'); ?>"
				},
				repeat_password: {
					equalTo: "<?php echo $this->lang->line('employees_password_must_match'); ?>"
				},
				email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>"
			}
		}, form_support.error));
	});
</script>

<?php //$this->load->view("partial/footer"); 
?>