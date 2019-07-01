
		<!-- bower:css -->
				
		
		<!-- end css template tags -->
		<!-- bower:js -->
		
       
<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save_role/' . $person_info->person_id, array('id'=>'employee_form', 'class'=>'form-horizontal')); ?>
	

	<div>
		

			<fieldset>
				<div class="form-group form-group-sm">	
					<?php echo form_label('Role Name', 'username', array('class'=>'required control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
							<?php echo form_input(array(
									'name'=>'role',
									'id'=>'role',
									'class'=>'form-control input-sm',
									'value'=>$person_info->role)
									);?>
						</div>
					</div>
				</div>
				
				
				
				<p><?php echo $this->lang->line("employees_permission_desc"); ?></p>
				 
						
				
				<ul id="permission_list" class="check_default">
					
					<?php
					foreach($all_modules as $module)
					{
					?>
						<li>	
							<?php echo form_checkbox("grants[]", $module->module_id, $module->grant, "class='module'"); ?>
							<span class="medium"><?php echo $this->lang->line('module_'.$module->module_id);?>:</span>
							<span class="small"><?php echo $this->lang->line('module_'.$module->module_id.'_desc');?></span>
							
						</li>
					<?php
					}
					?>
				</ul>
			</fieldset>
		
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$.validator.setDefaults({ ignore: [] });

	$.validator.addMethod("module", function (value, element) {
		var result = $("#permission_list input").is(":checked");
		$(".module").each(function(index, element)
		{
			var parent = $(element).parent();
			var checked =  $(element).is(":checked");
			if ($("ul", parent).length > 0 && result)
			{
				result &= !checked || (checked && $("ul > li > input:checked", parent).length > 0);
			}
		});
		return result;
	}, '<?php echo $this->lang->line('employees_subpermission_required'); ?>');

	$("ul#permission_list > li > input[name='grants[]']").each(function() 
	{
	    var $this = $(this);
	    $("ul > li > input", $this.parent()).each(function() 
	    {
		    var $that = $(this);
	        var updateCheckboxes = function (checked) 
	        {
				$that.prop("disabled", !checked);
	         	!checked && $that.prop("checked", false);
	        }
	       $this.change(function() {
	            updateCheckboxes($this.is(":checked"));
	        });
			updateCheckboxes($this.is(":checked"));
	    });
	});
	
	$("#roles").on("change", function() {
		if(this.value=="cashier"){
			$('.check_cashier').css("display","block");
			$('.check_lab_cashier').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_default').css("display","none");
			$('.check_ceo').css("display","none");
		}else if(this.value=="admin_dash"){
			$('.check_default').css("display","none");
			$('.check_lab_cashier').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_admin').css("display","block");
			$('.check_ceo').css("display","none");
		}else if(this.value=="lab_cashier"){
			$('.check_lab_cashier').css("display","block");
			$('.check_default').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_ceo').css("display","none");
		}else if(this.value=="lab_account"){
			$('.check_lab_account').css("display","block");
			$('.check_default').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_ceo').css("display","none");
		}else if(this.value=="lab_result"){
			$('.check_lab_cashier').css("display","block");
			$('.check_default').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_ceo').css("display","none");
		}else if(this.value=="ceo"){
			$('.check_ceo').css("display","block");
			$('.check_default').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_default').css("display","none");
		}else if(this.value=="invent"){
			$('.check_advent').css("display","block");
			$('.check_admin').css("display","none");
			$('.check_lab_cashier').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_default').css("display","none");
			$('.check_ceo').css("display","none");
		}else if(this.value=="custom"){
			$('.check_default').css("display","block");
			$('.check_lab_cashier').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_cashier').css("display","none");
			$('.check_ceo').css("display","none");
		}else{
			$('.check_cashier').css("display","none");
			$('.check_lab_cashier').css("display","none");
			$('.check_admin').css("display","none");
			$('.check_advent').css("display","none");
			$('.check_default').css("display","none");
			$('.check_ceo').css("display","none");
		}
   //alert(this.value); 
});
	
	$('#employee_form').validate($.extend({
		submitHandler:function(form) 
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					dialog_support.hide();
					table_support.handle_submit('<?php echo site_url('employees'); ?>', response);
				},
				dataType:'json'
			});
		},
		rules:
		{
			first_name: "required",
			role: "required",
			username:
			{
				required:true,
				minlength: 5
			},
			
			password:
			{
				<?php
				if($person_info->person_id == "")
				{
				?>
				required:true,
				<?php
				}
				?>
				minlength: 8
			},	
			repeat_password:
			{
 				equalTo: "#password"
			},
    		email: "email"
   		},
		messages: 
		{
     		first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
     		last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
     		username:
     		{
     			required: "<?php echo $this->lang->line('employees_username_required'); ?>",
     			minlength: "<?php echo $this->lang->line('employees_username_minlength'); ?>"
     		},
     		
			password:
			{
				<?php
				if($person_info->person_id == "")
				{
				?>
				required:"<?php echo $this->lang->line('employees_password_required'); ?>",
				<?php
				}
				?>
				minlength: "<?php echo $this->lang->line('employees_password_minlength'); ?>"
			},
			repeat_password:
			{
				equalTo: "<?php echo $this->lang->line('employees_password_must_match'); ?>"
     		},
     		email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>"
		}
	}, form_support.error));
});
</script>
 

