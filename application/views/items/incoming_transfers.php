<?php $this->load->view("partial/header"); ?>
    <script type="text/javascript" src="dataTables/dataTables.min.js"></script>
    <script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="dataTables/Buttons-1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="dataTables/JSZip-2.5.0/jszip.min.js"></script>
    <script type="text/javascript" src="dist/swal/cdnjs/sweetalert.min.js"></script>
    <script type="text/javascript" src="dataTables/pdfmake-0.1.36/pdfmake.min.js"></script>
<script type="text/javascript">
    function clickMe(el){
        if($(el).attr('clicked') === undefined || $(el).attr('clicked') === null){
            var val = el.getAttribute('data-transref');
            var rej_rem = '';
            var urll = '';
            var paramData = {
                'reference':el.getAttribute('data-transref')
            }
            if($(el).hasClass('accept-transfers')){
                urll += 'acceptt';
            }else {
                urll += 'reject';
                rej_rem = $('#remark-'+val).val();
                if(rej_rem === ''|| rej_rem=== null || rej_rem===undefined){
                    swal({
                        title:"Oops..",
                        icon:'error',
                        text:'Remark cannot be empty'
                    });
                    console.log('val: ',val);
                    return false;
                }
                paramData['remarks'] = rej_rem;
            }
            el.setAttribute('disabled','disabled');
            $.post("<?=site_url('transfer')?>"+'/'+urll,paramData,function (data) {
                swal({
                    title:"All good",
                    icon:'success',
                    text:data.message,
                    button: "Good"
                },function () {
                    location.reload(true);
                });
            },'json').fail(function(error){
                // alert("An error occurred");
                swal({
                    title:"Oops..",
                    icon:'error',
                    text:'An error occurred'
                },function(){
                    $(el).removeAttr('disabled');
                    $(el).removeAttr('clicked');
                });
            });
        }
        return false;
    }
    function switchTo(elFrom,elTo){
        console.log('type: ',typeof elFrom)
        $('#'+elFrom).toggle();
        $('#'+elTo).toggle();
    }
    $(document).ready(function() {
        $('#filters').on('hidden.bs.select', function(e) {
            table_support.refresh();
        });
        // load the preset datarange picker
        <?php $this->load->view('partial/daterangepicker'); ?>
        // set the beginning of time as starting date
        <?php
        //$lastweek = strtotime("last week");
        $lastweek = strtotime("today");
        $hr = date("H", $lastweek);
        $mi = date("i", $lastweek);
        $se = date("s", $lastweek);
        $mo = date("m", $lastweek);
        $da = date("d", $lastweek);
        $yr = date("Y", $lastweek);
        $fetch_longer = 0; // fetch only for today
        ?>

        <?php if ($fetch_longer) : ?>
            $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0, 0, 0, 01, 01, 2010)); ?>");
            var start_date = "<?php echo date('Y-m-d', mktime(0, 0, 0, 01, 01, 2010)); ?>";
        <?php else : ?>
            $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime($hr, $mi, $se, $mo, $da, $yr)); ?>");
            var start_date = "<?php echo date('Y-m-d', mktime($hr, $mi, $se, $mo, $da, $yr)); ?>";
        <?php endif; ?>

        // update the hidden inputs with the selected dates before submitting the search data
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>
        table_support.init({
            resource: '<?php echo site_url($controller_name); ?>' + '/incoming_transfers_data',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'lpos.lpo_id',
            queryParams: function() {
                return $.extend(arguments[0], {
                    start_date: start_date,
                    end_date: end_date,
                });
            },
            onLoadSuccess: function(response) {
                $('a.rollover').imgPreview({
                    imgCSS: {
                        width: 200
                    },
                    distanceFromCursor: {
                        top: 10,
                        left: -210
                    }
                })
            }
        });
        $.get("<?=site_url('transfer/get_incoming')?>",function (data) {
            var table_data = [];
            if(data !== null && !data.error){
                if(Object.keys(data[0].data).length > 0){
                    $.each(data[0].data,function (i,v) {
                        var butt = '';
                        var pref = 'reject_';
                        if(v.status === 'pending'){
                            butt = '<div><div id="'+v.transfer_reference+'"> <button onclick="clickMe(this)" class="btn btn-primary mr-5 ml-5 accept-transfers" data-transref="'+v.transfer_reference+'">Accept</button>' +
                                '<button onclick="switchTo(\''+v.transfer_reference+'\',\''+pref+v.transfer_reference+'\')" class="btn btn-danger reject-transfers" >Reject</button></div>' +
                                '<div class="form-group" id="reject_'+v.transfer_reference+'" style="display: none"><textarea name="remark" id="remark-'+v.transfer_reference+'" class="form-control" placeholder="Rejection remarks"></textarea>' +
                                '<button class="btn" onclick="switchTo(\''+pref+v.transfer_reference+'\',\''+v.transfer_reference+'\')">Cancel</button><button class="btn btn-primary" data-transref="'+v.transfer_reference+'" onclick="clickMe(this)">Continue</button></div></div>'
                        }else {
                            if(v.status === 'Accepted'){
                                butt = 'Accepted on '+v.date_approved
                            }else if(v.status === 'Recalled'){
                                butt = 'Recalled on '+v.date_recalled
                            }else{
                                butt = 'Rejected on '+v.date_rejected
                            }
                        }
                        table_data[i] = [
                            i+1,
                            v.transfer_reference,
                            v.from_branch,
                            v.total_price,
                            v.date_transferred,
                            v.sent_by,
                            v.status,
                            '<a href="<?=site_url()?>/transfer/view_incoming/'+v.transfer_reference+'">view</a>',
                            butt
                        ]
                    });
                    $('#incoming-transfers').DataTable({
                        "destroy": true,
                        data:table_data,
                        "autoWidth": false,
                    });
                }else{
                    $('#incoming-transfers').DataTable({
                        "destroy": true,
                        "autoWidth": false,
                    });
                }
            }else{
                $('#incoming-transfers').DataTable({
                    "destroy": true,
                    "autoWidth": false,
                });
            }
        },'json');
    });
</script>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <?php
        if (isset($error)) {
            echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
        }

        if (!empty($warning)) {
            echo "<div class='alert alert-dismissible alert-warning'>" . $warning . "</div>";
        }

        if (isset($success)) {
            echo "<div class='alert alert-dismissible alert-success'>" . $success . "</div>";
        }
        ?>
        <div class="row">


            <div id="title_bar" class="print_hide btn-toolbar">

                <?php //echo anchor("lpo", '<span class="md md-add-shopping-cart">&nbsp</span>Create an LPO', array('class' => 'btn btn-info btn-sm pull-right', 'id' => 'show_receiving_button')); ?>
            </div>
            <div id="toolbar" style="margin-left:2px;">
                <div class="pull-left form-inline" role="toolbar">
                    <?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="table_holder" style="margin:0 auto;margin-left:25px;margin-right:25px;">
                <table id="table">
                    <caption><b>Transfers Requested From Other Branches</b></caption>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="table-responsive" style="margin:0 auto;margin-top: 50px;margin-left:20px;margin-right:20px;">
                <table class="table table-bordered" id="incoming-transfers">
                    <caption><b>Transfers From Other Branches</b></caption>
                    <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Transfer Reference</th>
                        <th>From Branch</th>
                        <th>Total Value</th>
                        <th>Date Transferred</th>
                        <th>Transferred By</th>
                        <th>Status</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


<?php $this->load->view("partial/footer"); ?>
