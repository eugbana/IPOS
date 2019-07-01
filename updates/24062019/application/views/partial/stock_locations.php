<?php
$i = 0;

foreach($stock_locations as $location=>$location_data)
{
	$location_id = $location_data['location_id'];
	$location_name = $location_data['location_name'];
	$location_address = $location_data['location_address'];
	$location_number = $location_data['location_number'];
	++$i;
?>
	<div class="form-group form-group-sm" style="<?php echo $location_data['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?php echo form_label($this->lang->line('config_stock_location') . ' ' . $i, 'stock_location_' . $i, array('class'=>'required control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'stock_name_' . $location_id,
					'id'=>'stock_name_' . $location_id,
					'class'=>'stock_location valid_chars form-control input-sm required',
					'value'=>$location_name,
					'placeholder'=>'Insert Location Name'
				); 
				$location_data['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'stock_address_' . $location_id,
					'id'=>'stock_address_' . $location_id,
					'class'=>'stock_location valid_chars form-control input-sm required another',
					'value'=>$location_address,
					'type'=>'text',
					'placeholder'=>'Insert Address'
				); 
				$location_data['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_textarea($form_data);
			?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'stock_number_' . $location_id,
					'id'=>'stock_number_' . $location_id,
					'class'=>'stock_location valid_chars form-control input-sm required',
					'value'=>$location_number,
					'placeholder'=>'Insert Phone Number'
				); 
				$location_data['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		<span class="add_stock_location glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_stock_location glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
	</div>
<?php
}
?>
