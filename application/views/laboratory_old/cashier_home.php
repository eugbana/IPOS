<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>


<h3 class="text-center"></h3>
<div id="home_module_list">
	<div class="module_item" title="New Test">
		<a href="<?php echo site_url("laboratory/lab_sales");?>"><img src="<?php echo base_url().'images/menubar/items.png';?>" border="0" alt="Menubar Image" /></a>
		<a href="<?php echo site_url("laboratory/lab_sales");?>">New Test</a>
	</div
	<div class="module_item" title="Check Result" id="search">
		<a><img src="<?php echo base_url().'images/menubar/customers.png';?>" border="0" alt="Menubar Image" /></a>
		<a>Check Result</a>
	</div>
	
</div>

<script>
      $(document).ready(function() {
		  	dialog_support.init("a.modal-dlg, button.modal-dlg");
		  
		$('#addButton').click(function(){
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Search");
			
			
		});
		$(document).on('click','#search', function(){
			//var user_id=$(this).attr("id");
			$('#userModal').modal('show');
			//$('#invoice_id').val(user_id);
			$('.modal-title').text("Search Result");
			$('#action').val("Search");
			/*$.post('<?php echo site_url("laboratory/view");?>', {user: user_id},function(){
				$('#userModal').modal('show');
				$('.modal-title').text("Edit");
				$('#test_code').val("Hello");
				$('#test_name').val("<?php echo $test_info->test_name;?>");
				$('#action').val("Edit");
				$('#operation').val("Edit");
			});*/
			
		});
		/*$('#addButton').click(function(){
			
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");
			
		});*/
		

       
		

        
      });
    </script>

<div id="userModal" class="modal fade">
		<div class="modal-dialog">
			<?php echo form_open('laboratory/result_info', array('id'=>'item_form', 'enctype'=>'multipart/form-data')); ?>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Test</h4>
					</div>
					<div class="modal-body">
			
						<label>Insert Result Id</label>
						<input type="text" name="sale_id" id="sale_id" class="form-control" />
					</div>
					<div class="modal-footer">
						
						<input type="hidden" name="invoice_id" id="invoice_id" />
						<input type="submit" name="action" id="action" class="btn btn-success" value="Submit" />
					</div>
					
				</div>
			
			<?php echo form_close(); ?>
		</div>
		
	</div>

<?php $this->load->view("partial/footer"); ?>