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
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-4"></div>
					<div class="col-xs-4">
						<h3>Push Notification</h3>
					</div>

					<div class="col-xs-1">
					</div>

					<div class="col-xs-1"><a href='<?php echo site_url("items/receive_transfer_receipt"); ?>'><button class='btn btn-info btn-sm' id="submitButton">
								Receive
							</button></a></div>
				</div>



			</div>

		</div>


		<br>
		<table id="tfable">
			<thead>
				<tr>
					<th width="10%"></th>
					<th width="20%">Product Name</th>
					<th width="10%">Pushed Quantity</th>
					<th width="5%"></th>
					<th width="20%">Received Quantity</th>
					<th width="20%"></th>


				</tr>
			</thead>
			<tbody>
				<?php foreach ($cart as $row => $value) { ?>
					<?php echo form_open($controller_name . "/edit_item_received/$row", array('class' => 'form-horizontal', 'id' => 'cart_' . $row)); ?>

					<?php if ($value['reference'] == 0) { ?>

						<tr>
							<td></td>
							<td>
								<h4><?php echo $value['name'] ?></h4>
							</td>
							<td><?php echo to_quantity_decimals($value['quantity']) ?></td>
							<td></td>
							<td>
								<div class='col-xs-8'>
									<?php echo form_input(
												array(
													'name' => 'received_quantity',
													'id' => 'received_quantity',
													'class' => 'form-control input-sm',
													'value' => to_quantity_decimals($value['received_quantity'])
												)
											); ?>
								</div>

							</td>


							<td>



							</td>
							<td></td>
							<td>
								<?php echo form_hidden('quantity', $value['quantity']); ?>
								<?php echo form_hidden('item_id', $value['item_id']); ?>
								<?php echo form_hidden('request_from_branch_id', $value['item_location']); ?>
								<?php echo form_hidden('request_to_branch_id', $value['location']); ?>
								<?php echo form_hidden('transfer_id', $value['transfer_id']); ?>
								<?php echo form_hidden('unaccounted', $value['unaccounted']); ?>
								<?php echo form_hidden('reference', $value['reference']); ?>
								<?php echo form_hidden('line', $value['line']); ?>

							</td>
						</tr>

						<?php

								echo form_close();
								?>
						<?php foreach ($cart as $let => $unline) { ?>
							<?php echo form_open($controller_name . "/edit_item_received/$let", array('class' => 'form-horizontal', 'id' => 'cart_' . $let)); ?>
							<?php if ($unline['reference'] == $value['line']) { ?>

								<tr>
									<td>Product Detail:</td>

									<td align="center">
										<?php echo $unline['batch_no'] ?>
									</td>
									<td>
										<h4><?php echo $unline['name'] ?></h4>
									</td>
									<td colspan="2"><?php

																	$dt = new DateTime($unline['expiry']);

																	$date = $dt->format('m/d/Y');
																	$time = $dt->format('H:i:s');

																	echo $date;

																	?>
									</td>
									<td>

									</td>


									<td></td>
									<td>
										<?php echo form_hidden('quantity', $unline['quantity']); ?>
										<?php echo form_hidden('item_id', $unline['item_id']); ?>
										<?php echo form_hidden('request_from_branch_id', $unline['item_location']); ?>
										<?php echo form_hidden('request_to_branch_id', $unline['location']); ?>
										<?php echo form_hidden('transfer_id', $unline['transfer_id']); ?>
										<?php echo form_hidden('unaccounted', $unline['unaccounted']); ?>
										<?php echo form_hidden('reference', $unline['reference']); ?>
										<?php echo form_hidden('line', $unline['line']); ?>

									</td>

								</tr>

							<?php
											echo form_close();
										}
										?>
						<?php
								}
								?>

					<?php
						}
						?>




				<?php
				}
				?>
			</tbody>
		</table>
		<?php // print_r($cart);
		?>
		<br>
		<?php //print_r($value_changed);
		?>
		<?php //print_r($no_of_batch);
		?>
		<?php //print_r($reference);
		?>
		<? php // print_r($received_quantity);
		?>
	</div>
</div>
</div>


<?php echo form_open($controller_name . "/cancel", array('id' => 'butons_form')); ?>
<?php echo form_close(); ?>


<script>
	$(document).ready(function() {
		dialog_support.init("a.modal-dlg, button.modal-dlg");

		$('[name="received_quantity"],[name="batch_no"],[name="received_batch_quantity"]').focusout(function() {
			$(this).parents("tr").prevAll("form:first").submit()
		});



		$('[name="no_of_batch"],[name="expiry"]').change(function() {
			//$('#value_changed').val(1);
			$(this).parents("tr").prevAll("form:first").submit()
		});

		$('#addButton').click(function() {
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");


		});
		$('#datetimepicker3').datetimepicker();
		$(document).on('click', '.update', function() {
			var user_id = $(this).attr("id");
			$('#userModal').modal('show');
			$('#invoice_id').val(user_id);
			$('.modal-title').text("Process");
			$('#action').val("Add");
			/*$.post('<?php echo site_url("laboratory/view"); ?>', {user: user_id},function(){
				$('#userModal').modal('show');
				$('.modal-title').text("Edit");
				$('#test_code').val("Hello");
				$('#test_name').val("<?php echo $test_info->test_name; ?>");
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


		var dataSet = [
			["Tiger Nixon", "System Architect", "Edinburgh", "5421", "2011/04/25", "$320,800"],
			["Garrett Winters", "Accountant", "Tokyo", "8422", "2011/07/25", "$170,750"],
			["Ashton Cox", "Junior Technical Author", "San Francisco", "1562", "2009/01/12", "$86,000"],
			["Cedric Kelly", "Senior Javascript Developer", "Edinburgh", "6224", "2012/03/29", "$433,060"],
			["Airi Satou", "Accountant", "Tokyo", "5407", "2008/11/28", "$162,700"],
			["Brielle Williamson", "Integration Specialist", "New York", "4804", "2012/12/02", "$372,000"],
			["Herrod Chandler", "Sales Assistant", "San Francisco", "9608", "2012/08/06", "$137,500"],
			["Rhona Davidson", "Integration Specialist", "Tokyo", "6200", "2010/10/14", "$327,900"],
			["Colleen Hurst", "Javascript Developer", "San Francisco", "2360", "2009/09/15", "$205,500"],
			["Sonya Frost", "Software Engineer", "Edinburgh", "1667", "2008/12/13", "$103,600"],
			["Jena Gaines", "Office Manager", "London", "3814", "2008/12/19", "$90,560"],
			["Quinn Flynn", "Support Lead", "Edinburgh", "9497", "2013/03/03", "$342,000"],
			["Charde Marshall", "Regional Director", "San Francisco", "6741", "2008/10/16", "$470,600"],
			["Haley Kennedy", "Senior Marketing Designer", "London", "3597", "2012/12/18", "$313,500"],
			["Tatyana Fitzpatrick", "Regional Director", "London", "1965", "2010/03/17", "$385,750"],
			["Michael Silva", "Marketing Designer", "London", "1581", "2012/11/27", "$198,500"]
		];
		var dataSetter = [
			["1", "MBG", "Myoglobin", "2000", "check"],
			["2", "TER", "Troponin", "5000", "text"],
			["3", "TGH", "TotalCK", "2500", "text"],
			["4", "OUY", "Opiates", "4000", "check"],
			["5", "IUH", "Myoglobin", "2000", "check"],
			["6", "OCS", "On Call Strip", "2000", "text"]

		];
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
				"mData": "invoice_id"
			},
			{
				"mData": "person_id"
			}, {
				"mData": "doctor_name"
			}, {
				"mData": "status"
			}, {
				"mData": "edit"
			}
		];

		var columnDefs = [{
			title: "Name"
		}, {
			title: "Position"
		}, {
			title: "Office"
		}, {
			title: "Extn."
		}, {
			title: "Start date"
		}, {
			title: "Salary"
		}];

		var columneDef = [{
			title: "Name"
		}, {
			title: "Position"
		}, {
			title: "Office"
		}, {
			title: "Extn."
		}];
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
			"sAjaxSource": "<?php echo site_url('account/laboratory_items'); ?>",
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
		/*$().on('','', function(){
			event.preventDefault
		});*/

	});
</script>

<div id="userModal" class="modal fade">
	<div class="modal-dialog">
		<?php echo form_open('Account/lab_account_sales', array('id' => 'item_form', 'enctype' => 'multipart/form-data')); ?>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Add Test</h4>
			</div>
			<div class="modal-body">
				<label>Enter Test Code</label>
				<input type="text" name="invoice_id" id="invoice_id" class="form-control" />
				<label>Enter Name</label>
				<input type="text" name="test_name" id="test_name" class="form-control" />
				<label>Choose Payment</label>
				<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
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