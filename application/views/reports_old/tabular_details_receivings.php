<?php $this->load->view("partial/header"); ?>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <div id="page_title">
            <?php echo $title  ?>
            <p style="font-size:16px;">
                <a href="<?php echo site_url('reports/print_filtered_report_receivings/' . $start_date . '/' . $end_date . '/' . $receiving_type . '/' . $location_id . '/' . $employee_id . '/' . $supplier); ?>">Print Items</a> |
                <a href="<?php echo site_url('reports/print_filtered_report_items_receivings/' . $start_date . '/' . $end_date . '/' . $receiving_type . '/' . $location_id . '/' . $employee_id . '/' . $supplier); ?>">Print Detailed Items</a>
            </p>
        </div>

        <div>
			<?php
			foreach ($report_title_data as $key => $value) {
				echo '<div><b>' . $key . ': </b>' . $value . ' </div>';
			}
			?>
		</div>

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
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        var detail_data = <?php echo json_encode($details_data); ?>;
        <?php
        if ($this->config->item('customer_reward_enable') == TRUE && !empty($details_data_rewards)) {
        ?>
            var details_data_rewards = <?php echo json_encode($details_data_rewards); ?>;
        <?php
        }
        ?>
        var init_dialog = function() {

            <?php if (isset($editable)) : ?>
                table_support.submit_handler('<?php echo site_url("reports/get_detailed_" . $editable . "_row") ?>');
                dialog_support.init("a.modal-dlg");
            <?php endif; ?>
        };

        $('#table').bootstrapTable({
            columns: <?php echo transform_headers($headers['summary'], TRUE); ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
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
            onPageChange: init_dialog,
            onPostBody: function() {
                dialog_support.init("a.modal-dlg");
            },
            onExpandRow: function(index, row, detail) {
                detail.html('<table></table>').find("table").bootstrapTable({
                    columns: <?php echo transform_headers_readonly($headers['details']); ?>,
                    data: detail_data[(!isNaN(row.id) && row.id) || $(row[0] || row.id).text().replace(/(POS|RECV)\s*/g, '')]
                });
                <?php
                if ($this->config->item('customer_reward_enable') == TRUE && !empty($details_data_rewards)) {
                ?>
                    $detail.append('<table></table>').find("table").bootstrapTable({
                        columns: <?php echo transform_headers_readonly($headers['details_rewards']); ?>,
                        data: details_data_rewards[(!isNaN(row.id) && row.id) || $(row[0] || row.id).text().replace(/(POS|RECV)\s*/g, '')]
                    });
                <?php
                }
                ?>
            }
        });

        init_dialog();
    });
</script>

<?php $this->load->view("partial/footer"); ?>