<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

    <?php echo form_open('stockintake/create_stock_intake', array(action => 'POST', 'id' => 'stockintake_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
        <p class="text-center">
            <h3 class="text-center"><b>Starting new Stock Taking.</b></h3>
        </p>
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="info_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">	
				<?php echo form_label('Title', 'title', array('class' => 'control-label col-xs-2 required')); ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-home"></span></span>
						<?php echo form_input(array(
							'name' => 'title',
							'id' => 'title',
							'class' => 'form-control input-sm required',
                            )); ?>
					</div>
				</div>
			</div>
			
			<div class="form-group form-group-sm">	
				<?php echo form_label('Description', 'description', array('class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-6'>
					<?php echo form_textarea(array(
						'name' => 'description',
						'id' => 'description',
                        'class' => 'form-control input-sm',
                        'placeholder' => 'Optional'
                        )); ?>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_form',
				'id' => 'submit_form',
				'value'=> 'Start Stock Taking',
				'class' => 'btn btn-primary btn-lg pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{

	$('#stockintake_form').validate($.extend(form_support.handler, {

		errorLabelContainer: "#info_error_message_box",

		rules:
		{
			title: "required",
			// description: "required",
   		},

		messages: 
		{
			title: "Title for this Stcok Taking is required",
		}
	}));
});
</script>



    </div>

</div>
<?php $this->load->view("partial/footer"); ?>
