<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
    <style>
        .glyphicon.glyphicon-remove {
            font-size: 155px;
        }
    </style>
    
    <a href="javascript:window.history.go(-1);" class="btn btn-info btn-sm"> <span class="glyphicon glyphicon-arrow-left">&nbsp; Go back</a>
    <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>


	<div id="config_wrapper">
    <span class="glyphicon glyphicon-remove text-center"></span>
		<fieldset id="config_info">
        
        <p class="text-center">
            <h1 class="text-center"><b><?php echo $message ?> </b></h1>

        </p>

        <br/>
		</fieldset>
	</div>

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
