<?php $this->load->view("partial/header_print"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div class="content-page">
                <!-- Start content -->
                <div class="content">
		<h3 class="text-center"></h3>
		<div id="home_module_list">

				<?php if($status==1){?>
				<div class="module_item" title="Check Result" id="search">
					Not Ready
				</div>
				<?php 
				}elseif($status==2){
				?>
				<div>
					Result Pending
				</div>
				<?php 
				}elseif($status==3){
				?>
				<div class="row" style="margin-left:20px">
					<table>
						<tr>
							
							<td></td>
							<td colspan="2"style="margin:20px"><h3><?php echo $customer;?></h3></td>
							<td></td>
							<td><button class="btn btn-primary update" id='<?php echo $sale_id?>'>Print Result</button></td>
						</tr>
					</table>
					
				</div>
				<?php 
				}else{
				?>
				<div>
					Does not exist
				</div>
				<?php 
				}
				?>
		</div>
	</div>
</div>

<script>
      $(document).ready(function() {
		  	dialog_support.init("a.modal-dlg, button.modal-dlg");
		  
		$('#addButton').click(function(){
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");
			
			
		});
		$(document).on('click','.update', function(){
			var user_id=$(this).attr("id");
			//$('#userModal').modal('show');
			$('#sale_id').val(user_id);
			$('.modal-title').text("Process");
			$('#action').val("Add");
			$('#action').submit();
			$('#item_form').submit();
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
						{ "mData": "sale_id" },
						{ "mData": "customer_id" },{ 
						"mData": "doctor_name" },{ 
						"mData": "edit" },{ 
						"mData": "print" }
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
		  "sAjaxSource": "<?php echo site_url('account/pending_result_items');?>",
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
			<?php echo form_open('laboratory/print_result_info', array('id'=>'item_form', 'enctype'=>'multipart/form-data')); ?>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Test</h4>
					</div>
					<div class="modal-body">
						<label>Enter Test Code</label>
						<input type="text" name="sale_id" id="sale_id" class="form-control" />
			
						<label>Choose Payment</label>
						<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
					</div>
					<div class="modal-footer">
						
						
						<input type="submit" name="action" id="action" class="btn btn-success" value="Submit" />
					</div>
					
				</div>
			
			<?php echo form_close(); ?>
		</div>
		
	</div>



<?php $this->load->view("partial/footer"); ?>