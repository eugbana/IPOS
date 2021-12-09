<?php
$e_info = $this->Employee->get_logged_in_employee_info();
$extraConfig = $this->Appconfig->get_extra_config(['company_id'=>$e_info->branch_id,'company_branch_id'=>$e_info->branch_id]);

echo form_open(empty($extraConfig)?'config/set_extra_app_config':'config/update_app_config_extra', array('id' => 'extra_config_form', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
    <ul id="info_error_message_box" class="error_message_box"></ul>
    <fieldset id="config_info">
        <legend>Sales:</legend>
        <div class="form-group form-group-sm">
            <?php echo form_label("Minimum Sale Value", 'm-s-v', array('class' => 'control-label col-xs-2 required')); ?>
            <div class="col-xs-6">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tint"></span></span>
                    <input type="number" name="min_sale" required id="min_sale" class="form-control input-sm required" value="<?=$extraConfig[0]->minimum_sale_value?>"/>
<!--                    --><?php //echo form_input(array(
//                        'name' => 'min_sale',
//                        'id' => 'min_sale',
//                        'class' => 'form-control input-sm required',
//                        'value'=>$extraConfig[0]->minimum_sale_value
//                    )); ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?php echo form_label('Map Sales to Customer', 'website', array('class' => 'control-label col-xs-2 required')); ?>
            <div class="col-xs-6">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-star"></span></span>
                    <select name="map_sales_to_customers" required id="map_sales_to_customers" class="form-control input-sm">
                        <option value="0" <?=$extraConfig[0]->customer_details_mandated==0?"selected":''?>>No</option>
                        <option value="1" <?=$extraConfig[0]->customer_details_mandated==1?"selected":''?>>Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <?php echo form_submit(array(
            'name' => 'submit_form',
            'id' => 'submit_form',
            'value'=>$this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm pull-right')); ?>
    </fieldset>
    <fieldset>
        <legend>Upstream</legend>
        <div>
            <button id='pushsales' class="btn btn-primary upstream" type="button">Push Sales</button>
            <button id='pushtransfers' class="btn btn-primary upstream" type="button">Push Transfers</button>
            <button id='pushstock' class="btn btn-primary upstream" type="button">Push Stock</button>
        </div>
    </fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function()
    {
        $(".upstream").on('click',function(){
            var url = "<?php echo site_url('synccontroller')?>";
            var el = $(this);
            el.attr('disabled','disabled');
            $.get(url+'/'+el.attr('id'),function(data){
                alert(data.message);
                el.attr('disabled','');
            });
        });
        $("a.fileinput-exists").click(function() {
            $.ajax({
                type: "GET",
                url: "<?php echo site_url("$controller_name/remove_logo"); ?>",
                dataType: "json"
            })
        });

        //$('#extra_config_form').validate($.extend(form_support.handler, {
        //
        //    errorLabelContainer: "#info_error_message_box",
        //
        //    rules:
        //        {
        //            minimum_sale_value: "required",
        //            map_sales_to_customers: "required"
        //        },
        //
        //    messages:
        //        {
        //            minimum_sale_value: "<?php //echo 'Provide valid sale value or enter 0 if you have no clue what you doing!'; ?>//",
        //            map_sales_to_customers: "<?php //echo 'Select yes or no naa, na wa for you oo'; ?>//"
        //        }
        //}));
        $('#extra_config_form').submit(function (e) {
            e.preventDefault();
            let url = '<?=count($extraConfig)> 0 ?"update_app_config_extra":"set_extra_app_config"?>';
            $.post("<?php echo site_url($controller_name); ?>/"+url,$(this).serializeArray(),function(data){
                if(data.message){
                    alert(data.message);
                }else{
                    alert(data.error);
                }
            },'json');
        })
    });
</script>
