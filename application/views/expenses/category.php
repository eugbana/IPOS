<?php $this->load->view("partial/header"); ?>



<link rel="stylesheet" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css" />



<?php /*foreach($invoice as $row=>$value){
		echo $value['item_id'];
	}*/ ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<div class="container" style="width:70%">
			<div class="col-sm-6 offset-sm-3">
				<?php
				if (isset($message)) {
					?>
					<div class='alert alert-dismissible alert-success'><?php echo $message; ?></div>
				<?php
				}
				?>
			</div>
			<div class="row">
			</div>
			<div class="row" style="margin-left:5px;margin-bottom:20px;margin-top:20px">
				<button class="btn btn-small btn-primary" id="add_button" data-toggle="modal" data-target="#userModaltime">Add New/Update Expense Category <i class="fa fa-plus"></i></button>&nbsp;&nbsp;
			</div>

			<br>
			<table class="table table-striped table-bordered" id="table">
				<thead>
					<tr>
						<th>ID</th>
						<!-- <th>Category Type</th> -->
						<th>Category Name</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($categories as $key => $cat) {
						$id = $cat['id'];
						$type = $cat['type'];
						$name = $cat['name'];
						?>
						<tr>
							<td><?= $id ?></td> 
							<!-- <td><?= $type ?></td> -->
							<td><?= $name . "  &nbsp; &nbsp; &nbsp;  <b>(" . $type . " )</b>" ?></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
			<br>

		</div>
	</div>
	<?php echo form_open('items/category_apply_vat', array('id' => 'apply_vat_form', 'enctype' => 'multipart/form-data')); ?>
	<input type="hidden" name="vat_cate_id" id="vat_cate_id">

	<?php echo form_close(); ?>
</div>
<script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="js/altEditor/dataTables.altEditor.free.js"></script>
<script src="https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/select/1.1.2/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js"></script>

<script>
	$(document).ready(function() {

		$(".apply_vat_btn").click(function() {
			var id = $(this).data('id');
			$("#vat_cate_id").val(id);
			if (window.confirm("VAT will be apply to all products of this category. You still want to go ahead?")) {
				$("#apply_vat_form").submit();
			}
		});



		dialog_support.init("a.modal-dlg, button.modal-dlg");
		var colum = [{
			"mData": "Empid"
		}, {
			"mData": "Name"
		}, {
			"mData": "Salary"
		}, {
			"mData": "Competency"
		}];

		var columlab = [{
				"mData": "id"
			},
			{
				"mData": "name"
			}, {
				"mData": "edit"
			}
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
			"sAjaxSource": "<?php echo site_url('expense/categories_list'); ?>",
			//aoColumns: colum,
			//data:dataSetter,
			//data:dataSet,
			aoColumns: columlab,
			columns: columnDe,
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
	$(document).on('click', '#add_button', function() {
		//var user_id=$(this).attr("id");
		$('#userCategory').modal('show');
		//$('#invoice_id').val(user_id);
		$('.modal-title').text("Add/Update Category");
		$('#action').val("Submit");

	});
	$(document).on('click', '.edit_cat', function() {
		//var user_id=$(this).attr("id");
		$('#userCategory').modal('show');
		//$('#invoice_id').val(user_id);
		$('.modal-title').text("Edit Category");
		$('#action').val("Edit");

	});

	$(document).on('change', '#category', function() {
		//var user_id=$(this).attr("id");
		// $('#userCategory').modal('show');
		cat = $('#category').val();
		if(cat != ''){
			$('#label_cat_name').text('Edit Category Name');
			$('#label_cat_select').text('Choosen Category');
			$('.modal-title').text("Edit Category");
			$('#action').val("Update");
			$('#category_name').val(cat);

			//set default category here
			// $('select option[value="1"]').attr("selected",true);
			// $("select option:contains(cat)").prop('selected',true);
		}else{
			$('#label_cat_name').text('Category Name');
			$('#label_cat_select').text('Create New Category');
			$('.modal-title').text("Add/Update Category");
			$('#action').val("Submit");
			$('#category_name').val('');
		}
	});


	//category
</script>

<div id="userCategory" class="modal fade">
	<div class="modal-dialog">
		<?php echo form_open('expense/save_expense_category', array('id' => 'item_form', 'enctype' => 'multipart/form-data')); ?>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Add/Update Category</h4>
			</div>
			<div class="modal-body">

				<div class="form-group">
					<label id="label_cat_select">Create New Category</label>
					<select name="category" id="category" class="form-control">
						<option value="">Create New Category</option>
						<?php
						foreach ($categories as $key => $cat) {
							echo '<option value="' . $cat['name'] . '">' . $cat['name'] . '</option>';
						}
						?>
					</select>
				</div>
				<div class="form-group">
					<label id="label_cat_name">Category Name</label>
					<input type="text" name="category_name" id="category_name" class="form-control" />
				</div>
				<div class="form-group">
					<label id="label_cat_type">Expense Type</label>
					<select name="category_type" id="category_type" class="form-control">
						<!-- <option value="">Create New Category</option> -->
						<?php
						foreach ($types as $key => $type) {
							echo '<option value="' . $key . '">' . $type . '</option>';
						}
						?>
					</select>
				</div>

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