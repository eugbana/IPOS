<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?php echo site_url($controller_name); ?>',
			headers: <?php echo $table_headers; ?>,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			uniqueId: 'laboratory.test_id',
			enableActions: function() {
				var email_disabled = $("td input:checkbox:checked").parents("tr").find("td a[href^='mailto:']").length == 0;
				$("#email").prop('disabled', email_disabled);
			}
		});

		$("#email").click(function(evvent) {
			var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element) {
				return $(element).attr('href').replace(/^mailto:/, '');
			});
			location.href = "mailto:" + recipients.join(",");
		});

	});
</script>


<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<div id="title_bar" class="btn-toolbar">

			<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view"); ?>' title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
				<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
			</button>
			<button class='btn btn-info btn-sm pull-left modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view_category"); ?>' title='<?php echo $this->lang->line($controller_name . '_category'); ?>'>
				<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_test_category'); ?>
			</button>

			<button class='btn btn-info btn-sm pull-left modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view_testunit"); ?>' title='<?php echo $this->lang->line($controller_name . '_unit'); ?>'>
				<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_unit'); ?>
			</button>
			<button class='btn btn-info btn-sm pull-left modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view_subgroup"); ?>' title='<?php echo $this->lang->line($controller_name . '_subgroup'); ?>'>
				<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_subgroup'); ?>
			</button>

		</div>

		<div id="toolbar">
			<div class="pull-left btn-toolbar">
				<button id="delete" class="btn btn-default btn-sm">
					<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete"); ?>
				</button>

			</div>

		</div>


		<div id="table_holder">
			<table id="table"></table>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>