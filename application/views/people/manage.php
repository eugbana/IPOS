<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?php echo site_url($controller_name); ?>',
			headers: <?php echo $table_headers; ?>,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			uniqueId: 'people.person_id',
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
			<?php
			if ($controller_name == 'customers') {
				?>
				<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/excel_import"); ?>' title='<?php echo $this->lang->line('customers_import_items_excel'); ?>'>
					<span class="glyphicon glyphicon-import">&nbsp</span><?php echo $this->lang->line('common_import_excel'); ?>
				</button>

			<?php
			}
			?>


			<a class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view"); ?>' title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
				<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
			</a>
			<?php
			if ($controller_name == 'employees') {
				?>
				<button id="delete" class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view_role"); ?>'>
					<span class="glyphicon glyphicon-new">&nbsp</span><?php echo "Add Role"; ?>
				</button>
			<?php
			}
			?>
		</div>

		<div id="toolbar">
			<div class="pull-left btn-toolbar">
				<?php
				if ($controller_name == 'employees') {
					?>
					<button id="delete" class="btn btn-default btn-sm">
						<span class="fa fa-close">&nbsp</span><?php echo $this->lang->line("common_terminate"); ?>
					</button>
					<?php
					} else {
						if ($user_info->role == 3) {
							?>
						<button id="delete" class="btn btn-default btn-sm">
							<span class="fa fa-close">&nbsp</span><?php echo $this->lang->line("common_delete"); ?>
						</button>
				<?php
					}
				}
				?>
				<button id="email" class="btn btn-default btn-sm">
					<span class="glyphicon glyphicon-envelope">&nbsp</span><?php echo $this->lang->line("common_email"); ?>
				</button>
			</div>
		</div>

		<div id="table_holder">
			<table id="table"></table>
		</div>

	</div>
</div>
<script>
	$(document).ready(function() {
		$("#roles").on("change", function() {
			var role = ("#roles").val();
			$.post('<?php echo site_url($controller_name . "/set_role"); ?>', {
				role: role
			});

			//alert(this.value); 
		});
	});
</script>

<?php $this->load->view("partial/footer"); ?>