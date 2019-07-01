		</div>
		</div>
		</div>
	</div>
	</div>
	</div>
	

	<div id="footer">
		
	</div>
	
	
    <!-- Core plugin JavaScript-->
    <script src="dist/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Page level plugin JavaScript-->
    <script src="dist/vendor/chart.js/Chart.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="dist/js/sb-admin.min.js"></script>
	<script type="text/javascript">
$(document).ready(function()
{
	$("#global_item").autocomplete(
	{
		source: '<?php echo site_url("sales/item_search"); ?>',
		minChars: 0,
		autoFocus: false,
	   	delay: 500,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$("#global_add_item_form").submit();
			return false;
		}
	});

	$('#global_item').focus();

	$('#global_item').keypress(function (e) {
		if(e.which == 13) {
			$('#global_add_item_form').submit();
			return false;
		}
	});

	
});



</script>


   
</body>
</html>
