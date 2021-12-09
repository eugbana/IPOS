</div>
</div>
<!-- ============================================================== -->
<!-- End Right content here -->
<!-- ============================================================== -->



<!-- /Right-bar -->
</div>
</div>

<!-- <footer class=" text-center">
	<div class="text-center">
		© 2018 - <?php echo date("Y") ?> <a href="www.istrategytech.com">Powered By Infostrategy.</a>
	</div>
</footer> -->

<div id="noticeModal" class="modal fade">
	<div class="modal-dialog">
		<?php echo form_open('items/receive_transfer', array('id' => 'item_transfer', 'enctype' => 'multipart/form-data')); ?>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Add Test</h4>
			</div>
			<div class="modal-body">
				<label>Enter Test Code</label>
				<input type="text" name="transfer_id" id="transfer_id" class="form-control" />

				<label>Choose Payment</label>
				<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
			</div>
			<div class="modal-footer">

				<input type="hidden" name="sale_id" id="sale_id" />
				<input type="submit" name="action" id="action" class="btn btn-success" value="Submit" />
			</div>

		</div>

		<?php echo form_close(); ?>
	</div>

</div>


<script>
	$("#close_register").click(function() {
		if (confirm('<?php echo 'Are you sure you want to close the Register? you will need to open another Register to continue'; ?>')) {
			$('#register_form').submit();
		}
	});

	// $("body").keydown(function(e){
    //      var keyCode = e.keyCode || e.which;
	// 	//  console.log(keyCode);      
	// 	 if(keyCode == 114){
	// 		window.location = 'sales/receipt/<?php echo $this->Sale->get_latest_sale_id() ?>';
	// 	 }else if(keyCode == 115){
	// 		window.location = 'items/check_price';
	// 	 }else if(keyCode == 116){
	// 		window.location = 'sales/check_receipt';
	// 	 }
    // 	});

	$("body").keydown(function(e){

		if ((e.metaKey || e.altKey) && ( String.fromCharCode(e.which).toLowerCase() === 'p') ) {
			window.location = 'items/check_price';
		}

		if ((e.metaKey || e.altKey) && ( String.fromCharCode(e.which).toLowerCase() === 's') ) {
			window.location = 'sales/receipt/<?php echo $this->Sale->get_latest_sale_id() ?>';
		}

		if ((e.metaKey || e.altKey) && ( String.fromCharCode(e.which).toLowerCase() === 'r') ) {
			window.location = 'sales/check_receipt';
		}
	});

	


</script>













<!-- moment js  -->








<!-- dashboard  -->


<!-- END wrapper -->
<script src="dist/assets/js/jquery.app.js"></script>
<script>
	$(document).ready(function() {
		<?php
		if ($close_side) {
		?>
			$(".open-left").click();
		<?php
		}
		?>


	});
</script>

</body>

<!-- Mirrored from moltran.coderthemes.com/dark/index.html by HTTrack Website Copier/3.x [XR&CO'2013], Thu, 14 Jul 2016 12:23:45 GMT -->

</html>
