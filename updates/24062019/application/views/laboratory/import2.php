<?php $this->load->view("partial/header"); ?>


      <link rel="stylesheet" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.2/css/buttons.dataTables.min.css"/> 
      <link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.dataTables.min.css"/>
      <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css"/> 
	  <script type="text/javascript">
$(document).ready(function()
{
	
    $('#generate_barcodes').click(function()
    {
        window.open(
            'index.php/items/generate_barcodes/'+table_support.selected_ids().join(':'),
            '_blank' // <- This is what makes it open in a new window.
        );
    });
	
	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidden.bs.select', function(e)
	{
        table_support.refresh();
    });

	// load the preset datarange picker
	<?php $this->load->view('partial/daterangepicker'); ?>
    // set the beginning of time as starting date
    $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>");
	// update the hidden inputs with the selected dates before submitting the search data
    var start_date = "<?php echo date('Y-m-d', mktime(0,0,0,01,01,2010));?>";
	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
        table_support.refresh();
    });

    $("#stock_location").change(function() {
       table_support.refresh();
    });

    <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

    table_support.init({
        employee_id: <?php echo $this->Employee->get_logged_in_employee_info()->person_id; ?>,
        resource: '<?php echo site_url($controller_name);?>',
        headers: <?php echo $table_headers; ?>,
        pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
        uniqueId: 'items.item_id',
        queryParams: function() {
            return $.extend(arguments[0], {
                start_date: start_date,
                end_date: end_date,
                stock_location: $("#stock_location").val(),
                filters: $("#filters").val() || [""]
            });
        },
        onLoadSuccess: function(response) {
            $('a.rollover').imgPreview({
				imgCSS: { width: 200 },
				distanceFromCursor: { top:10, left:-210 }
			})
        }
    });
});
</script>


<?php print_r($labv);?>
<?php echo form_open("laboratory/view/test_id", array('id'=>'butons_form')); ?>
<div class="container">
<button id="addButton"class="btn btn-info pull-left" data-toggle="modal" data-target="#userModal">Add</button>
<button class='btn btn-info btn-sm pull-left modal-dlg' data-btn-new='<?php echo $this->lang->line('common_new') ?>' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url("laboratory/view"); ?>'
            title='<?php echo $this->lang->line('_new'); ?>'>
        <span class="glyphicon glyphicon-tag">&nbsp</span><?php echo $this->lang->line('lab_new'); ?>
    </button>
      <br>
      <table class="table table-striped table-bordered" id="table">
		<thead>
            <tr>
                <th width="10%">Test Id</th>
                <th width="10%">Test Code</th>
				<th width="30%">Name</th>
				<th width="20%">Amount</th>
				<th width="20%">Type</th>
				<th width="10%">Edit</th>
               
            </tr>
        </thead>
      </table>
      <br>
	 
    </div>
<?php echo form_close(); ?>	
<?php echo form_open($controller_name."/cancel", array('id'=>'butons_form')); ?>
<?php echo form_close(); ?>
<script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
    <script src="js/altEditor/dataTables.altEditor.free.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.1.2/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js"></script>

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
			$.post('<?php echo site_url("laboratory/view");?>', {user: user_id},function(){
				$('#item_form').modal('show');
				$('.modal-title').text("Edit");
				$('#test_code').val("<?php echo $test_info->test_code;?>");
				$('#test_name').val("<?php echo $test_info->test_name;?>");
				$('#action').val("Edit");
				$('#operation').val("Edit");
			});
			
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
						{ "mData": "test_id" },
						{ "mData": "test_code" },{ 
						"mData": "test_name" },{ 
						"mData": "test_amount" },{ 
						"mData": "test_type" },{ 
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
          title: "Test Code"
        }, {
          title: "Name"
        }, {
          title: "Amount."
        }];

        var myTable;

        myTable = $('#table').DataTable({
          "sPaginationType": "full_numbers",
		  "sAjaxSource": "<?php echo site_url('laboratory/laboratory_items');?>",
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
			<?php echo form_open('laboratory/lab_save/'.$test_info->test_id, array('id'=>'item_form', 'enctype'=>'multipart/form-data')); ?>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Test</h4>
					</div>
					<div class="modal-body">
						<label>Enter Test Code</label>
						<input type="text" name="test_code" id="test_code" class="form-control" />
						<label>Enter Name</label>
						<input type="text" name="test_name" id="test_name" class="form-control" />
						<label>Enter Amount</label>
						<input type="text" name="test_amount" id="test_amount" class="form-control" />
						<label>Choose Type</label>
						<?php echo form_dropdown('test_type', $teste, $tesote, array('id'=>'test_type','class'=>'form-control')); ?>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="user_id" id="user_id"/>
						<input type="hidden" name="operation" id="operation" />
						<input type="submit" name="action" id="action" class="btn btn-success" value="Add" />
					</div>
					
				</div>
			
			<?php echo form_close(); ?>
		</div>
	</div>
<?php $this->load->view("partial/footer"); ?>
