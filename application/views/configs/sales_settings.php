<?php echo form_open('config/sales_settings/', array('id' => 'sales_settings', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
    <fieldset id="config_info">
        <!-- <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div> -->
        <span id="sales_error_message_box" class="error_message_box text-center"></span>










        <!-- <div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_print_footer'), 'print_footer', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
                        'name' => 'print_footer',
                        'id' => 'print_footer',
                        'value' => 'print_footer',
                        'checked' => $this->config->item('print_footer')
                    )); ?>
				</div>
			</div> -->





        <!-- <div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_takings_printer'), 'config_takings_printer', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('takings_printer', array(), ' ', 'id="takings_printer" class="form-control"'); ?>
				</div>
			</div> -->

        <div class="form-group form-group-sm">
            <?php echo form_label('Sale/Unit Price Markup', 'unit_price_markup_label', array('class' => 'control-label col-xs-2 required')); ?>
            <div class='col-xs-2'>
                <div class="input-group">
                    <?php echo form_input(array(
                        'type' => 'text',

                        'name' => 'unit_price_markup',
                        'id' => 'unit_price_markup',
                        'class' => 'form-control input-sm required',
                        'value' => $this->config->item('unit_price_markup')
                    )); ?>
                    <span class="input-group-addon input-sm"></span>
                </div>
            </div>
        </div>



        <div class="form-group form-group-sm">
            <?php echo form_label('Wholesale Markup', 'wholesale_price_markup_label', array('class' => 'control-label col-xs-2 required')); ?>
            <div class='col-xs-2'>
                <div class="input-group">
                    <?php echo form_input(array(
                        'type' => 'text',

                        'name' => 'wholesale_price_markup',
                        'id' => 'wholesale_price_markup',
                        'class' => 'form-control input-sm',
                        'value' => $this->config->item('wholesale_price_markup')
                    )); ?>
                    <span class="input-group-addon input-sm"></span>
                </div>
            </div>
        </div>



        <?php echo form_submit(array(
            'name' => 'sales_settings_form',
            'id' => 'sales_settings_form',
            'value' => $this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm text-center'
        )); ?>
    </fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {

        $("form#sales_settings").submit(function(e) {
            e.preventDefault();

            var unit = $("#unit_price_markup").val();
            var wholesale = $("#wholesale_price_markup").val();

            var pattern = /^[0-9]{1,}[\.]?[0-9]{0,4}$/;
            if (pattern.test(unit) && pattern.test(wholesale)) {
                $.post("config/sales_settings", {
                    "unit": unit,
                    "wholesale": wholesale
                }, function(data, status) {
                    if (data.status) {
                        window.alert("markups successfully set")
                    }
                }, "json");
            } else {
                $("span#sales_error_message_box").html("Wrong markup values entered.");
                setTimeout(function() {
                    $("span#sales_error_message_box").html("");

                }, 5000);
            }
        });


    });
</script>