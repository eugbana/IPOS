<?php echo form_open('config/save_expiry_period/', array('id' => 'reorder_level_form', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
    <fieldset id="config_info">
        <!-- <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div> -->
        <span id="zery_qty_error_message_box" class="error_message_box text-center"></span>
        <span class="text-success" id="success-msg"></span>

        <div class="form-group form-group-sm">
            <?php echo form_label("Stock Location", 'theme', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-2'>
                <?php echo form_dropdown('stock_location', $locations, $employee_location, array('class' => 'form-control input-sm', 'id' => 'stock_location')); ?>
            </div>
        </div>


        <div class="form-group form-group-sm">
            <?php echo form_label('Expiry Warning period', 'reorder_default_label', array('class' => 'control-label col-xs-2 required')); ?>
            <div class='col-xs-2'>
                <div class="input-group">
                    <?php echo form_input(array(
                        'type' => 'text',
                        'name' => 'reorder_default',
                        'id' => 'reorder_default',
                        'class' => 'form-control input-sm required',
                        'value' => $this->config->item('reorder_default')
                    )); ?>
                    <span class="input-group-addon input-sm"></span>
                </div>
            </div>
        </div>

        <?php echo form_submit(array(
            'name' => 'reorder_level_btn',
            'id' => 'reorder_level_btn',
            'value' => $this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm text-center'
        )); ?>
    </fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {

        $("form#reorder_level_form").submit(function(e) {
            e.preventDefault();
            console.log('submitted');

            var reorder_level = $("#reorder_default").val();
            // var loc = $("#stock_location").val();

            var pattern = /^[0-9][1-9]{0,3}$/; //from 0-9999
            // if (pattern.test(reorder_level)) {
                $.post("config/save_expiry_period", {
                    "reorder_default": reorder_level
                    // "loc": loc

                }, function(data, status) {
                    console.log(data.message);
                    if (data.status) {
                        //window.alert("All product quantity successfully set to " + qty + "\n" + data.message);
                        $("#success-msg").html(data.message);
                    }
                }, "json");
            // } else {
            //     $("span#zery_qty_error_message_box").html("Wrong value entered: " + reorder_level);
            //     setTimeout(function() {
            //         $("span#zery_qty_error_message_box").html("");

            //     }, 5000);
            // }
        });


    });
</script>