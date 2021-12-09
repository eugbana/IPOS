<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('expense/save/' . $expense_info->id, array('id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
<fieldset id="item_basic_info">

<div class="form-group form-group-sm">
		<?php echo form_label('Type', 'type', array('class' => 'required control-label col-xs-3')); //categorization ?>
		<?php //echo form_label('Category', 'category', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('type', $types, $selected_type, array('class' => 'form-control', 'id' => 'type')); ?>
		</div>
	</div>

	

	<div class="form-group form-group-sm" id='category_exp'>
		<?php echo form_label('Expense Category', 'category', array('class' => 'required control-label col-xs-3', )); //categorization ?>
		<?php //echo form_label('Category', 'category', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('category', $categories, $selected_category, array('class' => 'form-control', 'id' => 'expense_category')); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label('Amount', 'amount', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">
				<?php if (!currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
				<?php echo form_input(
					array(
						'name' => 'amount',
						'id' => 'amount',
						'class' => 'form-control input-sm',
						// 'readonly' => 'readonly',
						'value' => to_currency_no_money($expense_info->amount)
					)
				); ?>
				<?php if (currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>

			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label('Reference NO', 'receipt_no', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
				<?php echo form_input(
					array(
						'name' => 'receipt_no',
						'id' => 'receipt_no',
						'class' => 'form-control input-sm',
						'value' => $expense_info->receipt_no,
						'placeholder' => 'Cheque, Receipt, Invoice, etc'
					)
				); ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label('Details / Description', 'description', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_textarea(
				array(
					'name' => 'description',
					'id' => 'description',
					'class' => 'form-control input-sm',
					'value' => $expense_info->details
				)
			); ?>
		</div>
	</div>

	<!-- <div class="form-group form-group-sm">
		<?php echo form_label('Type', 'type', array('class' => 'required control-label col-xs-3')); //categorization ?>
		<?php //echo form_label('Category', 'category', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('type', $types, $selected_type, array('class' => 'form-control', 'id' => 'type')); ?>
		</div>
	</div> -->

	

	

</fieldset>
<?php echo form_close();
//print_r($suppliers);
//print_r($selected_supplier);
?>


<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {

		$('#datetimepicker2').datetimepicker({
			locale: "ar"
		});
		$('#datetimepicker3').datetimepicker();
		$("#new").click(function() {
			stay_open = true;
			$("#item_form").submit();
		});


		//////////////////////////
		//UPDATE THE WHOLE SALE PRICE WHILE INPUTING THE WHOLE SALE PRICE MARKUP
		$("#type").on('change', function() {
			var curr_type = $("#type").val();
			if(curr_type == 'INFLOW'){
				$("#category_exp").hide();
			}else{
				$("#category_exp").show();
			}
		});


		$("#unit_price_markup").on('input', function() {
			var cost = parseFloat($("#cost_price").val());

			var unit_markup = parseFloat($("#unit_price_markup").val());
			var unit = Math.ceil(cost * unit_markup * 100); //rounding up to 2 decimal places
			//$("#unit_price").val(unit / 100);
			set_nearest_five((unit / 100), $("#unit_price"));
		});

		$("#unit_price").on('input', function() {
			var cost = parseFloat($("#cost_price").val());
			var retail_price = parseFloat($("#unit_price").val());

			var unit_markup = parseFloat($("#unit_price_markup").val());
			var markup = 0;
			markup = retail_price / cost;
			if(! Number.isNaN(markup) && markup && markup % 1 !== 0) markup = markup.toFixed(9);
			var newMarkUp = Number.isNaN(markup) ? 0 : markup;
			$("#unit_price_markup").val(newMarkUp);
		});


		//also update unit and whole price while typing the cost if the markups are available
		$("#cost_price").on('input', function() {
			var cost = parseFloat($(this).val());
			var pack = parseFloat($("#items_per_pack").val());
			var whole_markup = parseFloat($("#wholesale_price_markup").val());
			var unit_markup = parseFloat($("#unit_price_markup").val());

			var whole = Math.ceil(cost * whole_markup * pack * 100); //rounding up to 2 decimal places
			//$("#whole_price").val(whole / 100);
			set_nearest_five((whole / 100), $("#whole_price"));

			var unit = Math.ceil(cost * unit_markup * 100); //rounding up to 2 decimal places
			//$("#unit_price").val(unit / 100);
			set_nearest_five((unit / 100), $("#unit_price"));
		});




		$("#submit").click(function() {
			stay_open = false;
		});




		var no_op = function(event, data, formatted) {};
		$("#category").autocomplete({
			source: "<?php echo site_url('items/suggest_category'); ?>",
			delay: 10,
			appendTo: '.modal-content'
		});

		<?php for ($i = 1; $i <= 10; ++$i) {
		?>
			$("#custom" + <?php echo $i; ?>).autocomplete({
				source: function(request, response) {
					$.ajax({
						type: "POST",
						url: "<?php echo site_url('items/suggest_custom'); ?>",
						dataType: "json",
						data: $.extend(request, $extend(csrf_form_base(), {
							field_no: <?php echo $i; ?>
						})),
						success: function(data) {
							response($.map(data, function(item) {
								return {
									value: item.label
								};
							}))
						}
					});
				},
				delay: 10,
				appendTo: '.modal-content'
			});
		<?php
		}
		?>

		$("a.fileinput-exists").click(function() {
			$.ajax({
				type: "GET",
				url: "<?php echo site_url("$controller_name/remove_logo/$expense_info->item_id"); ?>",
				dataType: "json"
			})
		});

		$('#item_form').validate($.extend({
			submitHandler: function(form, event) {
				$(form).ajaxSubmit({
					success: function(response) {
						var stay_open = dialog_support.clicked_id() != 'submit';
						if (stay_open) {
							// set action of item_form to url without item id, so a new one can be created
							$("#item_form").attr("action", "<?php echo site_url("items/save/") ?>");
							// use a whitelist of fields to minimize unintended side effects
							$(':text, :password, :file, #description, #item_form').not('#reorder_level' +
								'#reference_number, #name, #cost_price, #unit_price').val('');
							// de-select any checkboxes, radios and drop-down menus
							$(':input', '#item_form').removeAttr('checked').removeAttr('selected');
						} else {
							dialog_support.hide();
						}
						table_support.handle_submit('<?php echo site_url('items'); ?>', response, stay_open);
					},
					dataType: 'json'
				});
			},

			rules: {
				name: "required",
				category: "required",
				item_number: {
					required: false,
					remote: {
						url: "<?php echo site_url($controller_name . '/check_item_number') ?>",
						type: "post",
						data: $.extend(csrf_form_base(), {
							"item_id": "<?php echo $expense_info->item_id; ?>",
							"item_number": function() {
								return $("#item_number").val();
							},
						})
					}
				},
				cost_price: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},
				unit_price: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},

				unit_price_markup: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},



			},

			messages: {
				name: "<?php echo $this->lang->line('items_name_required'); ?>",
				item_number: "<?php echo $this->lang->line('items_item_number_duplicate'); ?>",
				category: "<?php echo $this->lang->line('items_category_required'); ?>",
				cost_price: {
					required: "<?php echo $this->lang->line('items_cost_price_required'); ?>",
					number: "<?php echo $this->lang->line('items_cost_price_number'); ?>"
				},
				unit_price: {
					required: "<?php echo $this->lang->line('items_unit_price_required'); ?>",
					number: "<?php echo $this->lang->line('items_unit_price_number'); ?>"
				},
				unit_price_markup: {
					required: "<?php echo 'Retail Price Markup is required'; ?>",
					number: "<?php echo 'Retail Price Markup must be a number'; ?>"
				},




			}
		}, form_support.error));
	});

	function set_nearest_five(amount, input) {
		$.post("items/set_nearest_five", {
			"amount": amount
		}, function(data, status) {
			input.val(data.amount);
		}, "json");
	}
</script>