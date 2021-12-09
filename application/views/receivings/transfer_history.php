<?php $this->load->view("partial/header"); ?>
<script type="text/javascript" src="dataTables/dataTables.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="dataTables/JSZip-2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="dist/swal/cdnjs/sweetalert.min.js"></script>
<script type="text/javascript" src="dataTables/pdfmake-0.1.36/pdfmake.min.js"></script>
<script type="text/javascript">
    function switchTo(elFrom,elTo){
        console.log('type: ',typeof elFrom)
        $('#'+elFrom).toggle();
        $('#'+elTo).toggle();
    }
    function recall_transfer(ref){
        // alert($(ref).attr('data-transref'));

        if($(ref).attr('clicked') === undefined || $(ref).attr('clicked') === null){
            ref.setAttribute('disabled','disabled');
            ref.setAttribute('clicked','1');
            var rec_rem = $('#remark-'+ref.getAttribute('data-transref')).val();
            var paramData = {
                'reference':ref.getAttribute('data-transref'),
                'remarks': rec_rem
            }
            if(rec_rem === ''|| rec_rem=== null || rec_rem===undefined){
                swal({
                    title:"Oops..",
                    icon:'error',
                    text:'Remark cannot be empty'
                },function () {
                    $(ref).removeAttr('disabled');
                    $(ref).removeAttr('clicked');
                });
                return false;
            }
            $.post("<?=site_url('transfer/recall')?>",paramData,function (data) {
                // alert(data.message);
                if(data.error || data.status !== '00'){
                    swal({
                        title:"Oops...",
                        icon:'error',
                        text:data.message,
                        button: "Ok"
                    });
                }else {
                    swal({
                        title:"All good",
                        icon:'success',
                        text:data.message,
                        button: "Good"
                    },function () {
                        location.reload(true);
                    });
                }

            },'json').done().fail(function(error){
                swal({
                    title:"Oops..",
                    icon:'error',
                    text:'An error occurred',
                    button: "Ok"
                });
                $(ref).removeAttr('disabled');
                $(ref).removeAttr('clicked');
            });
        }
        return false;
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
        $lastweek = strtotime('today');
        $hr = date("H", $lastweek);
        $mi = date("i", $lastweek);
        $se = date("s", $lastweek);
        $mo = date("m", $lastweek);
        $da = date("d", $lastweek);
        $yr = date("Y", $lastweek);
        $fetch_longer = 0;
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
            resource: '<?php echo site_url($controller_name); ?>' + '/transfer_history_data',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'transfer.transfer_id',
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

                <?php echo anchor("items/push", '<span class="glyphicon glyphicon-new-window">&nbsp</span>New Transfer', array('class' => 'btn btn-info btn-sm pull-right', 'id' => 'show_receiving_button')); ?>
            </div>
            <div id="toolbar" style="margin-left:2px;">
                <div class="pull-left form-inline" role="toolbar">
                    <?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="table_holder" style="margin:0 auto;margin-left:25px;margin-right:25px;">
                <table id="table"></table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view("partial/footer"); ?>
