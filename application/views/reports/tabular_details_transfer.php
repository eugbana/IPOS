<?php $this->load->view("partial/header"); ?>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <div id="page_title">
            <?php echo $title  ?>
            <p style="font-size:16px;">
                <a href="<?php echo site_url('reports/print_filtered_report_transfer/' . $start_date . '/' . $end_date . '/' . $employee_id . '/' . $to_branch); ?>">Print all</a> |
                <a href="<?php echo site_url('reports/print_filtered_report_items_transfers/' . $start_date . '/' . $end_date . '/' . $employee_id . '/' . $to_branch); ?>">Print all items</a>
            </p>
        </div>

        <div id="page_subtitle"><?php echo $subtitle ?></div>

        <div id="table_holder">
            <table id="table"></table>
        </div>

        <div id="report_summary">
            <?php
            foreach ($overall_summary_data as $name => $value) {
                ?>
                <div class="summary_row"><?php echo $name . ': ' . to_currency($value); ?></div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {





        $('#table').bootstrapTable({
            columns: <?php echo transform_headers($headers['summary'], TRUE); ?>,
            pageSize: 25,
            striped: true,
            pagination: true,
            sortable: true,
            showColumns: true,
            uniqueId: 'id',
            showExport: true,
            data: <?php echo json_encode($summary_data); ?>,
            iconSize: 'sm',
            paginationVAlign: 'bottom',
            detailView: true,

            escape: false,


        });


    });
</script>

<?php $this->load->view("partial/footer"); ?>