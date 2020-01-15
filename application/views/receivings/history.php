<?php $this->load->view("partial/header"); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#filters').on('hidden.bs.select', function(e) {
                table_support.refresh();
            });
            // load the preset datarange picker
            <?php $this->load->view('partial/daterangepicker'); ?>
            // set the beginning of time as starting date
            <?php
                $lastweek = strtotime("last week");
                $hr = date("H", $lastweek);
                $mi = date("i", $lastweek);
                $se = date("s", $lastweek);
                $mo = date("m", $lastweek);
                $da = date("d", $lastweek);
                $yr = date("Y", $lastweek);
                $fetch_longer = 1;
            ?>
            <?php if ($fetch_longer): ?>
                $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>");
                var start_date = "<?php echo date('Y-m-d', mktime(0,0,0,01,01,2010));?>";
            <?php else: ?>
                $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime($hr,$mi, $se, $mo, $da, $yr));?>");
                var start_date = "<?php echo date('Y-m-d', mktime($hr,$mi, $se, $mo, $da, $yr));?>";
            <?php endif; ?>
            // update the hidden inputs with the selected dates before submitting the search data
            $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
                table_support.refresh();
            });
            <?php $this->load->view('partial/bootstrap_tables_locale'); ?>
            table_support.init({
                resource: '<?php echo site_url($controller_name);?>' + '/history_data',
                headers: <?php echo $table_headers; ?>,
                pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
                uniqueId: 'receivings.receiving_id',
                queryParams: function() {
                    return $.extend(arguments[0], {
                        start_date: start_date,
                        end_date: end_date,
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
    <div class="content-page">
        <!-- Start content -->
        <div class="content">
            <?php
                if (isset($error)) {
                    echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
                }

                if (!empty($warning)) {
                    echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
                }

                if (isset($success)) {
                    echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
                }
            ?>
            <div class="row">
                <div id="toolbar" style="margin-left:2px;">
                    <div class="pull-left form-inline" role="toolbar">
                        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
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
