<?php $this->load->view("partial/header"); ?>


      
	  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.2/css/buttons.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.dataTables.min.css"/>
      <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css"/> 



<?php /*foreach($invoice as $row=>$value){
		echo $value['item_id'];
	}*/?>
     <div class="content-page" >
                <!-- Start content -->
                <div class="content">
		<div class="container" style="width:70%">
		<div class="row" style="margin-left:5px;margin-bottom:20px;margin-top:20px">
									<button class="btn btn-small btn-primary" id="add_button" data-toggle="modal" data-target="#userModaltime">Add New <i class="fa fa-plus"></i></button>&nbsp;&nbsp;
								</div>

			  <br>
			  <table class="table table-striped table-bordered" id="table">
				<thead>
					<tr>
						<th width="10%">Id</th>
						<th width="30%">Category Name</th>
						<th width="30%"></th>
						
					   
					</tr>
				</thead>
			  </table>
			  <br>
			 
			</div>
		</div>
	</div>
	<script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
    <script src="js/altEditor/dataTables.altEditor.free.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.1.2/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js"></script>

    <script>
      $(document).ready(function() {
		  	dialog_support.init("a.modal-dlg, button.modal-dlg");
		var colum= [
						{ "mData": "Empid" },{ 
						"mData": "Name" },{ 
						"mData": "Salary" },{ 
						"mData": "Competency" }
                ];
		
		var columlab= [
						{ "mData": "id" },
						{ "mData": "name" },{ 
						"mData": "edit" }
                ];
		var columnDe = [{
          title: "Test Id"
        }, {
          title: "Test Code",
		  className: "text-center"
        }, {
          title: "Name"
        }, {
          title: "Amount."
        }];

        var myTable;
		
        myTable = $('#table').DataTable({
          "sPaginationType": "full_numbers",
		  "sAjaxSource": "<?php echo site_url('items/categories_list');?>",
           //aoColumns: colum,
		   //data:dataSetter,
		  //data:dataSet,
		  aoColumns: columlab,
		  columns:columnDe,
          //columns: columnDefs,
		  //columns: columneDef,
          //dom: 'Bfrtip',        // Needs button container
          //select: 'single',
          //responsive: true,
          

        });
		/*$('#add_button').click(function(){
			$('#userCategory')[0].reset();
			$('#userModal').modal('show');
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");
			
			
		});*/
		/*$().on('','', function(){
			event.preventDefault
		});*/
        
      });
    </script>
	<script>
		$(document).on('click','#add_button', function(){
			//var user_id=$(this).attr("id");
			$('#userCategory').modal('show');
			//$('#invoice_id').val(user_id);
			$('.modal-title').text("Add Category");
			$('#action').val("Add");
			
		});
		$(document).on('click','.edit_cat', function(){
			//var user_id=$(this).attr("id");
			$('#userCategory').modal('show');
			//$('#invoice_id').val(user_id);
			$('.modal-title').text("Edit Category");
			$('#action').val("Edit");
			
		});
	</script>
	
 <div id="userCategory" class="modal fade">
		<div class="modal-dialog">
			<?php echo form_open('items/save_category', array('id'=>'item_form', 'enctype'=>'multipart/form-data')); ?>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Category</h4>
					</div>
					<div class="modal-body">
						<label>Category Name</label>
						<input type="text" name="category_name" id="category_name" class="form-control" />
						
					</div>
					<div class="modal-footer">
						
						<input type="hidden" name="operation" id="operation" />
						<input type="submit" name="action" id="action" class="btn btn-success" value="Submit" />
					</div>
					
				</div>
			
			<?php echo form_close(); ?>
		</div>
		
	</div>
<?php $this->load->view("partial/footer"); ?>
