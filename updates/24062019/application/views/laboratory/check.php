<?php $this->load->view("partial/header"); ?>


      <link rel="stylesheet" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.2/css/buttons.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.dataTables.min.css"/>
      <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css"/> 
	  



<?php /*foreach($invoice as $row=>$value){
		echo $value['item_id'];
	}*/?>
     <div class="content-page">
                <!-- Start content -->
                <div class="content">
		<div class="container">
		<div class="row">
		<div class="col-xs-12">
		<div class="col-xs-4"></div>
		<div class="col-xs-4"><h3>Result Sheet</h3></div>
<?php //print_r($cart);?>
		<div class="col-xs-1">
			<a href='<?php echo site_url("laboratory/lab_result_saved"); ?>'><button class='btn btn-info btn-sm' id="saveButton">
				Save Result
			</button></a></div>
			
			<div class="col-xs-1"><a href='<?php echo site_url("laboratory/lab_result_receipt"); ?>'><button class='btn btn-info btn-sm' id="submitButton">
				Submit
			</button></a></div>
		</div>



		</div>
			
		</div>

<?php //print_r($cart)?>
			  <br>
			  <table id="tfable">
				<thead>
					<tr>
						<th width="10%"></th>
						<th width="15%"></th>
						<th width="20%"></th>
						<th width="10%"></th>
						<th width="20%"></th>
						<th width="10%"></th>
						
					   
					</tr>
				</thead>
				<tbody>
				<?php foreach($cart as $row=>$value){?>
							<?php echo form_open($controller_name."/edit_item/$row", array('class'=>'form-horizontal', 'id'=>'cart_'.$row)); ?>
								<?php if(strstr($value['test_kind'], 'special')){?>
											<?php if($value['reference']==0){?>
														<tr>
															<td></td>
															<td><?php echo $value['test_name'];?></td>
															<td></td>
															
															

															
															<td></td>
							
														
															<td><?php echo form_input(array('name'=>'batch', 'id'=>'batch', 'class'=>'input-sm','width=>5px','placeholder'=>'Add')); ?>
																	<?php echo form_hidden('test_name', $value['test_name']); ?>
																	<?php echo form_hidden('item_id', $value['item_id']); ?>
																	<?php echo form_hidden('reference', $value['reference']); ?>
																	<?php echo form_hidden('line', $value['line']); ?>
															</td>
															
														</tr>
														<tr>
																	<td></td>
																		<td><h4><b>Test Code</b></h4></td>
																		<td align="center"><h4><b>Test Name</b></h4></td>
																		<td><h4><b>Patient Value</b></h4></td>	
																		<td align="center"><h4><b>Normal Value</b></h4></td>
														</tr>
											
														<?php echo form_close(); ?>
														<?php foreach($cart as $let=>$unline){?>
															<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
															<?php if($unline['reference']==$value['line']){?>
																		<tr>
																			<td><?php echo anchor($controller_name."/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>');?></td>
																						<td><?php echo form_input(array(
																									'name'=>'test_comment',
																									'id'=>'test_comment',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['test_comment'])
																									);?>
																						</td>
																						<td><?php echo form_input(array(
																									'name'=>'extra_name',
																									'id'=>'extra_name',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['extra_name'])
																									);?>
																						</td>
																						<td><?php echo form_input(array(
																									'name'=>'o_name',
																									'id'=>'o_name',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['o_name'])
																									);?>
																						<?php echo form_hidden('test_name', $unline['test_name']); ?>
																								<?php echo form_hidden('item_id', $unline['item_id']); ?>
																								<?php echo form_hidden('reference', $unline['reference']); ?>
																								<?php echo form_hidden('line', $unline['line']); ?>
																						</td>
																						
																						

																						
																							
																						<td><?php echo form_input(array(
																									'name'=>'h_name',
																									'id'=>'h_name',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['h_name'])
																									);?>
																						
																						</td>
																		</tr>
															
														<?php 
															echo form_close();
																	}
																}
															}
														?>
								
								<?php }elseif(strstr($value['test_subgroup'], 'Serology') && $value['test_name']!='Widal Test' ){?>
										<tr>
											<td><?php echo $value['test_code']?></td>
											<td><h4><?php echo $value['test_name']?></h4></td>
											<td><?php echo form_input(array(
															'name'=>'test_comment',
															'id'=>'test_comment',
															'class'=>'form-control input-sm',
															'value'=>$value['test_comment'])
															);?></td>
											<td></td>
											<td>
												<?php echo form_hidden('test_name', $value['test_name']); ?>
														<?php echo form_hidden('item_id', $value['item_id']); ?>
														<?php echo form_hidden('reference', $value['reference']); ?>
														<?php echo form_hidden('line', $value['line']); ?>
											</td>
											<td></td>
										</tr>
								<?php }elseif(strstr($value['test_subgroup'], 'Microbiology') && $value['test_name']!='Urinalysis' ){?>
										<?php if($value['reference']==0){?>
														<tr>
															<td></td>
															<td><?php echo $value['test_name'];?></td>
															<td></td>
															
															

															
															<td></td>
							
														
															<td><?php echo form_input(array('name'=>'batch', 'id'=>'batch', 'class'=>'input-sm','width=>5px','placeholder'=>'Add')); ?>
																	<?php echo form_hidden('test_name', $value['test_name']); ?>
																	<?php echo form_hidden('item_id', $value['item_id']); ?>
																	<?php echo form_hidden('reference', $value['reference']); ?>
																	<?php echo form_hidden('line', $value['line']); ?>
															</td>
															
														</tr>
											
														<?php echo form_close(); ?>
														<?php foreach($cart as $let=>$unline){?>
															<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
															<?php if($unline['reference']==$value['line']){?>
																		<tr>
																			<td><?php echo anchor($controller_name."/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>');?></td>
																						<td><?php echo form_input(array(
																									'name'=>'extra_name',
																									'id'=>'extra_name',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['extra_name'])
																									);?>
																						</td>
																						<td><?php echo form_input(array(
																									'name'=>'test_comment',
																									'id'=>'test_comment',
																									'class'=>'form-control input-sm',
																									'value'=>$unline['test_comment'])
																									);?>
																						<?php echo form_hidden('test_name', $unline['test_name']); ?>
																								<?php echo form_hidden('item_id', $unline['item_id']); ?>
																								<?php echo form_hidden('reference', $unline['reference']); ?>
																								<?php echo form_hidden('line', $unline['line']); ?>
																						</td>
																						
																						

																						
																						<td></td>		
																		</tr>
															
														<?php 
															echo form_close();
																	}
																}
															}
														?>
								<?php }else{?>
											<tr></tr>
											<?php if(strstr($value['test_name'], 'Urinalysis')){?>
									   <?php echo form_open($controller_name."/edit_item/$row", array('class'=>'form-horizontal', 'id'=>'cart_'.$row)); ?>
												<?php if($value['reference']==0){?>
													<tr>
														<td></td>
														<td><?php echo $value['test_name'];?></td>
														<td></td>
														
														

														
														<td></td>
						
													
														<td><?php echo form_input(array('name'=>'batch', 'id'=>'batch', 'class'=>'input-sm','width=>5px','placeholder'=>'Add')); ?>
																<?php echo form_hidden('test_name', $value['test_name']); ?>
																<?php echo form_hidden('item_id', $value['item_id']); ?>
																<?php echo form_hidden('reference', $value['reference']); ?>
																<?php echo form_hidden('line', $value['line']); ?>
														</td>
														
													</tr>
						
													
												<?php echo form_close(); ?>
												<?php foreach($cart as $let=>$unline){?>
												<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
												<?php if($unline['reference']==$value['line']){?>
													<tr>
														<td><?php echo anchor($controller_name."/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>');?></td>
																	<td><?php echo form_input(array(
																				'name'=>'extra_name',
																				'id'=>'extra_name',
																				'class'=>'form-control input-sm',
																				'value'=>$unline['extra_name'])
																				);?>
																	</td>
																	<td><?php echo form_input(array(
																				'name'=>'o_name',
																				'id'=>'o_name',
																				'class'=>'form-control input-sm',
																				'value'=>$unline['o_name'])
																				);?>
																			<?php echo form_hidden('test_name', $unline['test_name']); ?>
																			<?php echo form_hidden('item_id', $unline['item_id']); ?>
																			<?php echo form_hidden('reference', $unline['reference']); ?>
																			<?php echo form_hidden('line', $unline['line']); ?>
																	</td>
																	<td></td>	
																	<td>
																	</td>
													</tr>
												
											<?php 
												echo form_close();
															}
														}
														?>
														<tr>
														<td></td>
																	<td></td>
																	<td></td>
																	<td></td>	
																	<td></td>
													</tr>
												<?php	
													}
										}elseif(strstr($value['test_name'], 'Malaria Parasite')){?>
											   <?php echo form_open($controller_name."/edit_item/$row", array('class'=>'form-horizontal', 'id'=>'cart_'.$row)); ?>
														<tr>
																<td></td>
																<td><h4><?php echo $value['test_name']?></h4></td>
																<td><?php echo form_input(array(
																				'name'=>'test_comment',
																				'id'=>'test_comment',
																				'class'=>'form-control input-sm',
																				'value'=>$value['test_comment'])
																				);?></td>
																<td></td>
																<td><?php echo $value['test_unit']?>
																	<?php echo form_hidden('test_name', $value['test_name']); ?>
																			<?php echo form_hidden('item_id', $value['item_id']); ?>
																			<?php echo form_hidden('reference', $value['reference']); ?>
																			<?php echo form_hidden('line', $value['line']); ?>
																</td>
																<td></td>
														</tr>
														
													<?php 
														echo form_close();
															
										}elseif(strstr($value['test_name'], 'Widal Test')){?>
									   <?php echo form_open($controller_name."/edit_item/$row", array('class'=>'form-horizontal', 'id'=>'cart_'.$row)); ?>
												<?php if($value['reference']==0){?>
													<tr>
														<td></td>
														<td><?php echo $value['test_name'];?></td>
														<td></td>
														
														

														
														<td></td>
						
													
														<td><?php echo form_input(array('name'=>'batch', 'id'=>'batch', 'class'=>'input-sm','width=>5px','placeholder'=>'Add')); ?>
																<?php echo form_hidden('test_name', $value['test_name']); ?>
																<?php echo form_hidden('item_id', $value['item_id']); ?>
																<?php echo form_hidden('reference', $value['reference']); ?>
																<?php echo form_hidden('line', $value['line']); ?>
														</td>
														
													</tr>
													<tr>
														<td></td>
																	<td></td>
																	<td align="center"><h4><b>O</b></h4></td>
																	<td></td>	
																	<td align="center"><h4><b>H</b></h4></td>
													</tr>
													
												<?php echo form_close(); ?>
												<?php foreach($cart as $let=>$unline){?>
												<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
												<?php if($unline['reference']==$value['line']){?>
													<tr>
														<td><?php echo anchor($controller_name."/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>');?></td>
																	<td><?php echo form_input(array(
																				'name'=>'extra_name',
																				'id'=>'extra_name',
																				'class'=>'form-control input-sm',
																				'value'=>$unline['extra_name'])
																				);?>
																	</td>
																	<td><?php echo form_input(array(
																				'name'=>'o_name',
																				'id'=>'o_name',
																				'class'=>'form-control input-sm',
																				'value'=>$unline['o_name'])
																				);?>
																			<?php echo form_hidden('test_name', $unline['test_name']); ?>
																			<?php echo form_hidden('item_id', $unline['item_id']); ?>
																			<?php echo form_hidden('reference', $unline['reference']); ?>
																			<?php echo form_hidden('line', $unline['line']); ?>
																	</td>
																	<td></td>	
																	<td><?php echo form_input(array(
																				'name'=>'h_name',
																				'id'=>'h_name',
																				'class'=>'form-control input-sm',
																				'value'=>$unline['h_name'])
																				);?>
																	</td>
													</tr>
												
											<?php 
												echo form_close();
															}
														}
														?>
														<tr>
														<td></td>
																	<td>>= 1/180 is Significant titre</td>
																	<td></td>
																	<td></td>	
																	<td></td>
													</tr>
												<?php	
													}
										}elseif(strstr($value['test_name'], 'Full Blood Count')){?>
									   <?php echo form_open($controller_name."/edit_item/$row", array('class'=>'form-horizontal', 'id'=>'cart_'.$row)); ?>
										<?php if($value['reference']==0){?>
											<tr>
												<td><h5><b><?php echo $value['test_code'];?></b></h5></td>
												<td><h5><b><?php echo $value['test_name'];?></b></h5></td>
												<td></td>
												
												

												
												<td></td>
				
											
												<td>
														<?php echo form_hidden('test_name', $value['test_name']); ?>
														<?php echo form_hidden('item_id', $value['item_id']); ?>
														<?php echo form_hidden('reference', $value['reference']); ?>
														<?php echo form_hidden('line', $value['line']); ?>
												</td>
												
											</tr>
											<tr>
												<td></td>
															<td></td>
															<td align="center"><h4><b>Patient Value</b></h4></td>
															<td align="center"><h4><b>Units</b></h4></td>	
															<td align="center"><h4><b>Normal value</b></h4></td>
											</tr>
											
										<?php echo form_close(); ?>
										<?php foreach($cart as $let=>$unline){?>
										<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
										<?php if($unline['reference']==$value['line']){?>
											<tr>
												<td></td>
															<td><?php echo $unline['extra_name'];?></td>
															<td><?php echo form_input(array(
																		'name'=>'test_comment',
																		'id'=>'test_comment',
																		'class'=>'form-control input-sm',
																		'value'=>$unline['test_comment'])
																		);?>
																	<?php echo form_hidden('test_name', $unline['test_name']); ?>
																	<?php echo form_hidden('extra_name', $unline['extra_name']); ?>
																	<?php echo form_hidden('h_name', $unline['h_name']); ?>
																	<?php echo form_hidden('o_name', $unline['o_name']); ?>
																	<?php echo form_hidden('item_id', $unline['item_id']); ?>
																	<?php echo form_hidden('reference', $unline['reference']); ?>
																	<?php echo form_hidden('line', $unline['line']); ?>
															</td>
															<td><?php echo $unline['o_name'];?></td>	
															<td><?php echo $unline['h_name'];?></td>
											</tr>
										
									<?php 
										echo form_close();
													}
												}
											}
										}else{
									?>
									<?php echo form_open($controller_name."/edit_item/$let", array('class'=>'form-horizontal', 'id'=>'cart_'.$let)); ?>	
									<tr>
											<td></td>
											<td><h4><?php echo $value['test_name']?></h4></td>
											<td><?php echo form_input(array(
															'name'=>'test_comment',
															'id'=>'test_comment',
															'class'=>'form-control input-sm',
															'value'=>$value['test_comment'])
															);?></td>
											<td></td>
											<td><?php echo $value['test_unit']?>
												<?php echo form_hidden('test_name', $value['test_name']); ?>
														<?php echo form_hidden('item_id', $value['item_id']); ?>
														<?php echo form_hidden('reference', $value['reference']); ?>
														<?php echo form_hidden('line', $value['line']); ?>
											</td>
											<td></td>
									</tr>
									<?php
									}
								}
									?>
									
							
					<?php
					
					echo form_close();
				}
					?>

				</tbody>
			  </table>
			  <br>
			 			<?php //echo $reference;echo $batch_id;echo $line// print_r($cart);?>
			</div>
					</div>
					</div>



    <script>
      $(document).ready(function() {
		  	dialog_support.init("a.modal-dlg, button.modal-dlg");
		
		$('[name="test_comment"],[name="batch"],[name="extra_name"],[name="o_name"],[name="h_name"]').focusout(function() {
			$(this).parents("tr").prevAll("form:first").submit();
		});
		  
		$('#addButton').click(function(){
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");
			
			
		});
		$(document).on('click','.update', function(){
			var user_id=$(this).attr("id");
			$('#userModal').modal('show');
			$('#invoice_id').val(user_id);
			$('.modal-title').text("Process");
			$('#action').val("Add");
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
          ["1", "MBG", "Myoglobin", "2000","check"],
          ["2", "TER", "Troponin", "5000","text"],
          ["3", "TGH", "TotalCK", "2500","text"],
          ["4", "OUY", "Opiates", "4000","check"],
          ["5", "IUH", "Myoglobin", "2000","check"],
          ["6", "OCS", "On Call Strip", "2000","text"]
          
        ];
		var colum= [
						{ "mData": "Empid" },{ 
						"mData": "Name" },{ 
						"mData": "Salary" },{ 
						"mData": "Competency" }
                ];
		
		var columlab= [
						{ "mData": "invoice_id" },
						{ "mData": "person_id" },{ 
						"mData": "doctor_name" },{ 
						"mData": "status" },{ 
						"mData": "edit" }
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
		  "sAjaxSource": "<?php echo site_url('account/laboratory_items');?>",
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
		/*$().on('','', function(){
			event.preventDefault
		});*/
        
      });
    </script>
	
 <div id="userModal" class="modal fade">
		<div class="modal-dialog">
			<?php echo form_open('Account/lab_account_sales', array('id'=>'item_form', 'enctype'=>'multipart/form-data')); ?>
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
						<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
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
