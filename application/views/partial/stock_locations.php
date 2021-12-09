<?php
$i = 0;
//var_dump($stock_locations[0]);
foreach($stock_locations as $location=>$location_data)
{
	$location_id = $location_data['location_id'];
	$location_name = $location_data['location_name'];
	$location_address = $location_data['location_address'];
	$location_number = $location_data['location_number'];
	++$i;
?>
	<div class="row justify-content-center" style="margin-top: 5px">
<!--        <div class="form-group form-group-sm" style="--><?php //echo $location_data['deleted'] ? 'display:none;' : 'display:block;' ?><!--">-->
            <?php
//            echo form_label($this->lang->line('config_stock_location') . ' ' . $i, 'stock_location_' . $i, array('class'=>'required control-label'));
            if($user_info->branch_id == $location_id){
                ?>
                <div class="col-xs-2">
                    <?php
            echo form_label($this->lang->line('config_stock_location') . ' ' . $i, 'stock_location_' . $i, array('class'=>'required control-label'));
                if($location_data['brid'] != null){
                    ?>
                    <button class="btn btn-primary" id="sync-loc-btn">Sync Others</button>
                    <?php
                }else{

            ?>
                    <button class="btn btn-primary" id="reg-loc-btn">Register</button>
                    <?php
                }?>
                </div>

        <?php
            }else{

                echo form_label($this->lang->line('config_stock_location') . ' ' . $i, 'stock_location_' . $i, array('class' => 'required control-label col-xs-2'));
            }
            ?>
            <div class='col-xs-3'>
                <?php
                if($user_info->branch_id == $location_id){
                    echo form_open('config/edit_location/', array('class' => 'form-horizontal location-edit','method'=>'post'));
                }
                $form_data = array(
//					'name'=>'stock_name_' . $location_id,
                    'id'=>'stock_name_' . $location_id,
                    'class'=>'stock_location valid_chars form-control input-sm required',
                    'value'=>$location_name,
                    'placeholder'=>'Insert Location Name'
                );
                if($user_info->branch_id == $location_id){
                    $form_data['name'] = 'stock_name';
                }else{
                    $form_data['readonly'] = 'readonly';
                }
                $location_data['deleted'] && $form_data['disabled'] = 'disabled';
                ?>
                <div class="row justify-content-center">
                    <?php
                    if($user_info->branch_id == $location_id){
                        ?>
                        <div class="col-xs-8">
                            <?=form_input($form_data)?>
                        </div>
                        <div class="col-xs-4">
                            <?php
                            echo form_hidden('type','name');
                            echo form_submit(array(
                                'name' => 'submitMan',
                                'value' => 'Update Name',
                                'class' => 'btn btn-primary btn-sm'
                            ));
                            ?>
                        </div>
                    <?php
                    }else{
                        ?>
                        <div class="col-xs-12">
                            <?=form_input($form_data)?>
                        </div>
                    <?php
                    }
                    echo form_close();
                    ?>
                </div>
            </div>
            <div class='col-xs-4'>
                <?php
                if($user_info->branch_id == $location_id){
                    echo form_open('config/edit_location/', array('class' => 'form-horizontal location-edit','method'=>'post'));
                }
                $form_data = array(
//					'name'=>'stock_address_' . $location_id,
                    'id'=>'stock_address' . $location_id,
                    'class'=>'stock_location valid_chars form-control input-sm required another',
                    'value'=>$location_address,
                    'type'=>'text',
                    'rows'=>3,
                    'placeholder'=>'Insert Address'
                );
                if($user_info->branch_id == $location_id){
                    $form_data['name'] = 'stock_address';
                }else{
                    $form_data['readonly'] = 'readonly';
                }
                $location_data['deleted'] && $form_data['disabled'] = 'disabled';
                ?>
                <div class="row justify-content-center">
                    <?php
                    if($user_info->branch_id == $location_id){
                        ?>
                        <div class="col-xs-8">
                            <?=form_textarea($form_data)?>
                        </div>
                        <div class="col-xs-4">
                            <?php
                            echo form_hidden('type','address');
                            echo form_submit(array(
                                'name' => 'submitMan',
                                'value' => 'Update Address',
                                'class' => 'btn btn-primary btn-sm'
                            ));
                            ?>
                        </div>
                        <?php
                    }else{
                        ?>
                        <div class="col-xs-12">
                            <?=form_textarea($form_data)?>
                        </div>
                        <?php
                    }
                    echo form_close();
                    ?>
                </div>
            </div>
            <div class='col-xs-3'>
                <?php
                if($user_info->branch_id == $location_id){
                    echo form_open('config/edit_location/', array('class' => 'form-horizontal location-edit','method'=>'post'));
                }
                $form_data = array(
//					'name'=>'stock_number_' . $location_id,
                    'id'=>'stock_number_' . $location_id,
                    'class'=>'stock_location valid_chars form-control input-sm required',
                    'value'=>$location_number,
                    'placeholder'=>'Insert Phone Number'
                );
                if($user_info->branch_id == $location_id){
                    $form_data['name'] = 'stock_number';
                }else{
                    $form_data['readonly'] = 'readonly';
                }
                $location_data['deleted'] && $form_data['disabled'] = 'disabled';
                ?>
                <div class="row justify-content-center">
                    <?php
                    if($user_info->branch_id == $location_id){
                        ?>
                        <div class="col-xs-8">
                            <?=form_input($form_data)?>
                        </div>
                        <div class="col-xs-4">
                            <?php
                            echo form_hidden('type','phone');
                            echo form_submit(array(
                                'name' => 'submitMan',
                                'value' => 'Update Phone',
                                'class' => 'btn btn-primary btn-sm'
                            ));
                            ?>
                        </div>
                        <?php
                    }else{
                        ?>
                        <div class="col-xs-12">
                            <?=form_input($form_data)?>
                        </div>
                        <?php
                    }
                    echo form_close();
                    ?>
                </div>
            </div>
            <!--		<span class="add_stock_location glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>-->
            <!--		<span>&nbsp;&nbsp;</span>-->
            <!--		<span class="remove_stock_location glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>-->
<!--        </div>-->
    </div>
<?php
}
?>
