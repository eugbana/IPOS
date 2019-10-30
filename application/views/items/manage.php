<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#generate_barcodes').click(function() {
			window.open(
				'index.php/items/generate_barcodes/' + table_support.selected_ids().join(':'),
				'_blank' // <- This is what makes it open in a new window.
			);
		});

		// when any filter is clicked and the dropdown window is closed
		$('#filters').on('hidden.bs.select', function(e) {
			table_support.refresh();
		});

		// load the preset datarange picker
		<?php $this->load->view('partial/daterangepicker'); ?>
		// set the beginning of time as starting date
		$('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0, 0, 0, 01, 01, 2010)); ?>");
		// update the hidden inputs with the selected dates before submitting the search data
		var start_date = "<?php echo date('Y-m-d', mktime(0, 0, 0, 01, 01, 2010)); ?>";
		$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
			table_support.refresh();
		});

		$("#stock_location").change(function() {
			table_support.refresh();
		});

		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			employee_id: <?php echo $this->Employee->get_logged_in_employee_info()->person_id; ?>,
			resource: '<?php echo site_url($controller_name); ?>',
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

		<div style="padding-top:1rem;">
			<?php if (($user_info->role) != 10) { ?>
				<div class="row" style="margin-left:5px;margin-right:5px;">
					<div id="title_bar" class="btn-toolbar print_hide">
						<a href='<?php echo site_url("items/push"); ?>'><button class='btn btn-info btn-sm pull-left' title='<?php echo $this->lang->line('common_push'); ?>'>
								<span class="glyphicon glyphicon-new-window">&nbsp</span><?php echo 'Transfer'; ?>
							</button></a>

						<a href='<?php echo site_url("items/pull"); ?>'><button class='btn btn-info btn-sm pull-left' title='<?php echo $this->lang->line('common_pull'); ?>'>
								<span class="glyphicon glyphicon-new-window">&nbsp</span><?php echo 'Request'; ?>
							</button></a>

						<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/excel_import"); ?>' title='<?php echo $this->lang->line('items_import_items_excel'); ?>'>
							<span class="glyphicon glyphicon-import">&nbsp</span><?php echo $this->lang->line('common_import_excel'); ?>
						</button>

						<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-new='<?php echo $this->lang->line('common_new') ?>' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view"); ?>' title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
							<span class="glyphicon glyphicon-tag">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
						</button>
					</div>
				</div>
			<?php } ?>
			<div class="row">
				<div id="toolbar">
					<div class="pull-left form-inline" role="toolbar">
						<button id="delete" class="btn btn-default btn-sm print_hide">
							<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete"); ?>
						</button>
						<button id="bulk_edit" class="btn btn-default btn-sm modal-dlg print_hide" , data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' , data-href='<?php echo site_url($controller_name . "/bulk_edit"); ?>' title='<?php echo $this->lang->line('items_edit_multiple_items'); ?>'>
							<span class="glyphicon glyphicon-edit">&nbsp</span><?php echo $this->lang->line("items_bulk_edit"); ?>
						</button>
						<button id="generate_barcodes" class="btn btn-default btn-sm print_hide" data-href='<?php echo site_url($controller_name . "/generate_barcodes"); ?>' title='<?php echo $this->lang->line('items_generate_barcodes'); ?>'>
							<span class="glyphicon glyphicon-barcode">&nbsp</span><?php echo $this->lang->line("items_generate_barcodes"); ?>
						</button>
						<?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
						<?php echo form_multiselect('filters[]', $filters, '', array('id' => 'filters', 'class' => 'selectpicker show-menu-arrow', 'data-none-selected-text' => $this->lang->line('common_none_selected_text'), 'data-selected-text-format' => 'count > 1', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
						<?php
						if (count($stock_locations) > 1) {
							echo form_dropdown('stock_location', $stock_locations, $stock_location, array('id' => 'stock_location', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit'));
						}
						?>
					</div>
				</div>
			</div>
			<div class="row">
				<div id="table_holder">
					<table id="table"></table>
				</div>
			</div>

			<?php echo form_open($controller_name . "/cancel", array('id' => 'butons_form')); ?>
			<?php echo form_close(); ?>




		</div> <!-- container -->

	</div> <!-- content -->

	<script>
		$(document).ready(function() {
			$(document).on('click', '.push_check', function() {
				var user_id = $(this).attr("id");
				//$('#noticeModal').modal('show');
				$('#transfer_id').val(user_id);
				$('.modal-title').text("Process");
				$('#action').val("Add");
				$('#action').submit();
				$('#item_transfer').submit();
				/*$.post('<?php echo site_url("laboratory/view"); ?>', {user: user_id},function(){
					$('#userModal').modal('show');
					$('.modal-title').text("Edit");
					$('#test_code').val("Hello");
					$('#test_name').val("<?php echo $test_info->test_name; ?>");
					$('#action').val("Edit");
					$('#operation').val("Edit");
				});*/

			});
			$('.count').html('<?php echo $transfer; ?>');
			var added = "<li class='text-center notifi-title'>Notification</li>";
			var others = "<?php foreach ($notice as $lin => $item) {
								echo "<a href='javascript:void(0);' class='list-group-item push_check' id='" . $item["transfer_id"] . "'><div class='media'><div class='pull-left'><em class='fa fa-user-plus fa-2x text-info'></em> </div><div class='media-body clearfix'><div class='media-heading'>" . $item["transfer_type"] . " Request</div><p class='m-0'><small>You have 10 unread messages</small></p></div></div></a>";
							}
							?>";
			$('#notification').html(added + others);
		});
	</script>

	<?php $this->load->view("partial/footer"); ?>