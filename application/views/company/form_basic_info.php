<div class="form-group form-group-sm">	
	<?php echo form_label('Company Name', 'company_name', array('class'=>'required control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'company_name',
				'id'=>'company_name',
				'class'=>'form-control input-sm',
				'value'=>$company_info->company_name)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label('C.A.C.', 'cac', array('class'=>'required control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'cac',
				'id'=>'cac',
				'class'=>'form-control input-sm',
				'value'=>$company_info->cac)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label('Tax Identitification Number (T.I.N.)', 'tin', array('class'=>'required control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'tin',
				'id'=>'tin',
				'class'=>'form-control input-sm',
				'value'=>$company_info->tin)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_email'), 'email', array('class'=>'required control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-envelope"></span></span>
			<?php echo form_input(array(
					'name'=>'email',
					'id'=>'email',
					'class'=>'form-control input-sm',
					'value'=>$company_info->contact_email)
					);?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_phone_number'), 'phone_number', array('class'=>'required control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
			<?php echo form_input(array(
					'name'=>'phone_number',
					'id'=>'phone_number',
					'class'=>'form-control input-sm',
					'value'=>$company_info->contact_phone)
					);?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_address_1'), 'address_1', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'address_1',
				'id'=>'address_1',
				'class'=>'form-control input-sm',
				'value'=>$company_info->address1)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_address_2'), 'address_2', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'address_2',
				'id'=>'address_2',
				'class'=>'form-control input-sm',
				'value'=>$company_info->address2)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_city'), 'city', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'city',
				'id'=>'city',
				'class'=>'form-control input-sm',
				'value'=>$company_info->city)
				);?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_state'), 'state', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'state',
				'id'=>'state',
				'class'=>'form-control input-sm',
				'value'=>$company_info->state)
				);?>
	</div>
</div>

<!-- <div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_zip'), 'zip', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'zip',
				'id'=>'postcode',
				'class'=>'form-control input-sm',
				'value'=>$company_info->zip)
				);?>
	</div>
</div> -->

<div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_country'), 'country', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_input(array(
				'name'=>'country',
				'id'=>'country',
				'class'=>'form-control input-sm',
				'value'=>$company_info->country)
				);?>
	</div>
</div>

<!-- <div class="form-group form-group-sm">	
	<?php echo form_label($this->lang->line('common_comments'), 'comments', array('class'=>'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_textarea(array(
				'name'=>'comments',
				'id'=>'comments',
				'class'=>'form-control input-sm',
				'value'=>$company_info->comments)
				);?>
	</div>
</div> -->

<script type="text/javascript">
$('#datetimepicker2').datetimepicker( {
	locale: "ar"
} );
$('#datetimepicker3').datetimepicker();
//validation and submit handling
$(document).ready(function()
{
	nominatim.init({
		fields : {
			postcode : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					field : 'postalcode',
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},

			city : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},
			state : {
				dependencies :  ["state", "country"]
			},
			country : {
				dependencies :  ["state", "country"]
			}
		},
		language : '<?php echo current_language_code();?>',
		country_codes: '<?php echo $this->config->item('country_codes'); ?>'
	});
});
</script>