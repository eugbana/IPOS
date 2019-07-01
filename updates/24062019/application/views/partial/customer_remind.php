<?php
$i = 0;

foreach($get_reminders as $location=>$location_data)
{
	$location_id = $location_data['reminder_id'];
	$location_name = $location_data['reminder_name'];
	$reminder_amount = $location_data['reminder_amount'];
	$reminder_value = $location_data['reminder_value'];
	++$i;
?>
	<div class="form-group form-group-sm" style="<?php echo $location_data['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?php echo form_label('Reminder' . ' ' . $i, 'stock_location_' . $i, array('class'=>'required control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'reminder_name_' . $location_id,
					'id'=>'reminder_name_' . $location_id,
					'class'=>'stock_location valid_chars form-control input-sm required',
					'value'=>$location_name
				); 
				$location_data['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
				echo form_hidden('reminder_value_' . $location_id, $reminder_value); 
			?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'reminder_amount_' . $location_id,
					'id'=>'reminder_amount_' . $location_id,
					'class'=>'customer_reward valid_chars form-control input-sm required',
					'value'=>$reminder_amount
				); 
				$location_data['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		
	</div>
<?php
}
?>
