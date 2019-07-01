<?php echo form_open('config/save_reminders/', array('id' => 'location_config_form', 'class' => 'form-horizontal')); ?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="stock_error_message_box" class="error_message_box"></ul>

            <div id="stock_locations">
				<?php $this->load->view('partial/customer_remind', array('stock_locations' => $stock_locations)); ?>
			</div>
            
            <?php echo form_submit(array(
                'name' => 'submit',
                'id' => 'submit',
                'value'=>$this->lang->line('common_submit'),
                'class' => 'btn btn-primary btn-sm pull-right')); ?>
        </fieldset>
    </div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{

	$('#location_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)	{
					$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
					//$("#stock_locations").load('<?php echo site_url("config/stock_locations"); ?>', init_add_remove_locations);
				},
				dataType: 'json'
			});
		},
		

		
	}));
});
</script>
