<?php $this->load->view("partial/header"); ?>


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="dist/css/dataTables.bootstrap.min.css">
<script>

var myTable;
var startDate = "<?php echo "01-" . date("m") ."-" . date("Y"); ?>";
var endDate = "<?php echo date("t") . "-" . date("m"). "-" . date("Y"); ?>";
var type;

var url = function () { return `<?php echo site_url('receivings/get_pending_requests'); ?>`};

	$(document).ready(function() {
		
	});
</script>


<div class="content-page">
  
  <!-- Start content -->
  <div class="content">
  
 
    <div class="container">
    <!-- <div class="form-group row">
      <div class="col-xs-3">
        <?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
      </div>
   
      <div class="col-xs-3">
        <?php echo form_dropdown('type', ['sales' => 'Sales',"returns" => 'Returning'], 'small',[
            'id'       => 'type',
            'class' =>'form-control show-menu-arrow'
          ]); 
        ?>
      </div>
      <div class="col-xs-3"></div>
      <div class="col-xs-3">
        <a href="<?php echo site_url('reports/detailed_sales');?>" class="btn btn-primary" id="viewReport">Generate Report</a>
      </div>
    
    </div> -->
      <table class="table table-striped table-bordered" id="table">
        <thead>
          <tr>
            <th width="15%">Date</th>
            <th width="13%">From Branch</th>
            <th width="25%">Status</th>
            <th width="25%">View</th>
          </tr>
        </thead>
      </table>
      <br>
    </div>
  </div>
</div>

<script>
  <?php $this->load->view('partial/daterangepicker'); ?>
	// set the beginning of time as starting date
	// $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0, 0, 0,  date("m"), 01,  date("Y") ) ); ?>");
	
	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
    
    startDate = picker.startDate._i.replace(/\//gi,"-");
    endDate = picker.endDate._i.replace(/\//gi,"-");
    myTable.ajax.url(url());
    myTable.clear();
    myTable.ajax.reload();
     
	});

</script>

<script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="dist/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="dist/js/dataTables.bootstrap.min.js" type="text/javascript"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>



<script>
 	
  $(document).ready(function() {


    type = $('#type').val();

    $('#type').change(function (event) {

      type = $('#type').val();
      myTable.ajax.url(url());
      myTable.clear();
      myTable.ajax.reload();    

      
      if(type == "sales") {
        $('#employee_action').html("Sold By");
      }else {
        $('#employee_action').html("Returned By");
      }
      
      
    });

    $('#viewReport').click(function () {
      var startDateArray = startDate.split("-");
      var reformatedStartDate = startDateArray[2] + "-" + startDateArray[1] + "-" + startDateArray[0];

      var endDateArray = endDate.split("-");
      var reformatedEndDate = endDateArray[2] + "-" + endDateArray[1] + "-" + endDateArray[0];
     //window.location.replace( `<?php echo site_url('reports/specific_employee');?>/${reformatedStartDate}/${reformatedEndDate}/all/all/${type}/all/all//all/all`);
   
      console.log(window.location);
    });
    
    

    dialog_support.init("a.modal-dlg, button.modal-dlg");

    $('#addButton').click(function() {
      $('#user_form')[0].reset();
      $('.modal-title').text("Add User");
      $('#action').val("Add");
      $('#operation').val("Add");
    });
    $(document).on('click', '.update', function() {
      var user_id = $(this).attr("id");
      $('#userModal').modal('show');
      $('#invoice_id').val(user_id);
      $('.modal-title').text("Process");
      $('#action').val("Add");
    });
   
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
        "mData": "created_at"
      },
      {
        "mData": "from_branch"
      },
      {
        "mData": "status"
      },
      {
        "mData": "id"
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

  $('#table').click(function() {
    console.log(myTable.columns( 0 ));
  
  });

    myTable = $('#table').DataTable({
      select: true,
      "sPaginationType": "full_numbers",
      "sAjaxSource": url(),
      aoColumns: columlab,
      columns: columlab,
      order: [
        [0, 'desc']
      ],
      columnDefs: [  
        {
          "targets": 3,//index of column starting from 0
          "data": "id", //this name should exist in your JSON response
          "render": function ( data, type, full, meta ) {
            var formated = data;
            return `<a href='receivings/view_request/${formated}'>View Items</a>`;
              
          }
        }, 
          
      ],
      'processing': true,
      'language': {
          'loadingRecords': '&nbsp;',
          'processing': 'Loading...'
      }
    });

    $('#daterangepicker').appendTo("#table_length");

    $('#action').click(function(e){
			e.preventDefault();
			$("#action").attr("disabled", true);
      document.getElementById("action").innerHTML = 'Please wait..';
      $('#item_form').submit();
		});


  

  });
</script>


<?php $this->load->view("partial/footer"); ?>