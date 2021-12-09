<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/save_update/' . $item_info->item_id, array('id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
<fieldset id="item_basic_info">
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_item_number'), 'item_number', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
				<?php echo form_input(
					array(
						'name' => 'item_number',
						'id' => 'item_number',
						'class' => 'form-control input-sm',
						'value' => $item_info->item_number
					)
				); ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_pname'), 'name', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_input(
				array(
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm',
					'value' => $item_info->name
				)
			); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label('Category', 'category', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('category', $categories, $selected_category, array('class' => 'form-control')); ?>
		</div>

	</div>

    <div class="form-group form-group-sm">
        <?php echo form_label('Department', 'dept', array('class' => 'required control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php echo form_dropdown('dept', $departments, strtolower($selected_dept), array('class' => 'form-control')); ?>
        </div>

    </div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_category'), 'company', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
				<?php echo form_input(
					array(
						'name' => 'company',
						'id' => 'company',
						'value' => $item_info->company,
						'class' => 'form-control input-sm'
					)
				); ?>
				<?php echo form_hidden('stock_type', 0); ?>
				<?php echo form_hidden('item_type', 0); ?>
				<?php echo form_hidden('current_apply_vat', !$item_info->apply_vat ? 'NO' : $item_info->apply_vat); ?>
			</div>
		</div>
	</div>
	<div class="form-group form-group-sm">
		<?php echo form_label('Apply V.A.T.', 'apply_vat_label', array('class' => 'control-label col-xs-3 ')); ?>
		<div class="col-xs-4">
			<label class="radio-inline">
				<?php echo form_radio(
					array(
						'name'		=>	'apply_vat',
						'type'		=>	'radio',
						'id'		=>	'apply_vat',
						'value'		=>	'YES',
						'checked'	=> $item_info->apply_vat == 'YES'
					)
				);
				?> <?php echo 'Yes'; ?>
			</label>
			<label class="radio-inline">
				<?php echo form_radio(
					array(
						'name'		=>	'apply_vat',
						'type'		=>	'radio',
						'id'		=>	'apply_vat2',
						'value'		=>	'NO',
						'checked'	=> $item_info->apply_vat == 'NO' || !$item_info->apply_vat ? 'checked' : ''
					)
				);
				?> <?php echo 'No'; ?>
			</label>
		</div>
	</div>
	<div class="form-group form-group-sm">
		<?php echo form_label('prescription', 'prescriptions_label', array('class' => 'control-label col-xs-3')); ?>
		<div class="col-xs-4">
			<label class="radio-inline">
				<?php echo form_radio(
					array(
						'name' => 'prescriptions',
						'type' => 'radio',
						'id' => 'prescriptions',
						'value' => 'YES',
						'checked' => $item_info->prescriptions === 'YES'
					)
				); ?> <?php echo 'Yes'; ?>
			</label>
			<label class="radio-inline">
				<?php echo form_radio(
					array(
						'name' => 'prescriptions',
						'type' => 'radio',
						'id' => 'prescriptions2',
						'value' => 'NO',
						'checked' => $item_info->prescriptions === 'NO' || empty($item_info->prescriptions)
					)
				); ?> <?php echo 'No'; ?>
			</label>

		</div>
		<?php echo form_label('Shelf', 'shelf', array('class' => 'control-label col-xs-1')); ?>
		<div class='col-xs-3'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
				<?php echo form_input(
					array(
						'name' => 'shelf',
						'id' => 'shelf',
						'class' => 'form-control input-sm',
						'value' => $item_info->shelf
					)
				); ?>
			</div>
		</div>
	</div>
	<div class="form-group form-group-sm">
		<?php echo form_label('Type', 'type_label', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-3'>
			<div class="input-group">
				<?php echo form_dropdown('product_type', $product_type, $selected_product_type, array('class' => 'form-control')); ?>
			</div>
		</div>
		<?php echo form_label('Grammage', 'grammage', array('class' => 'control-label col-xs-2')); ?>
		<div class='col-xs-3'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
				<?php echo form_input(
					array(
						'name' => 'grammage',
						'id' => 'grammage',
						'class' => 'form-control input-sm',
						'value' => $item_info->grammage
					)
				); ?>
			</div>
		</div>
	</div>


	<div class="form-group form-group-sm">
		<?php echo form_label("Expiry Warning Period", 'expiry_warning_period', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-5'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
				<?php echo form_input(
					array(
						'name' => 'expiry_days',
						'id' => 'expiry_days',
						'placeholder' => 'numeric value',
						'value' => $item_info->expiry_days,
						'class' => 'form-control input-sm'
					)
				); ?>
			</div>
		</div>
		<div class='col-xs-3'>
			<div class="input-group">

				<?php echo form_dropdown('period', $period, $selected_period, array('class' => 'form-control')); ?>
			</div>
		</div>
	</div>
	<div class="form-group form-group-sm">

		<?php echo form_label($this->lang->line('items_per_pack'), 'items_per_pack_label', array('class' => ' control-label col-xs-2')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
				<?php echo form_input(
					array(
						'name' => 'items_per_pack',
						'id' => 'items_per_pack',
						'class' => 'form-control input-sm',
						'value' => $item_info->pack
					)
				); ?>
			</div>
		</div>
	</div>



	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_supplier'), 'supplier', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier, array('class' => 'form-control')); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_cost_price'), 'cost_price_label', array('class' => 'required control-label col-xs-3')); ?>
		<div class="col-xs-8">
			<div class="input-group input-group-sm">
				<?php if (!currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
				<?php echo form_input(
					array(
						'name' => 'cost_price',
						'id' => 'cost_price',
						'class' => 'form-control input-sm',
						'value' => $item_info->cost_price
					)
				); ?>
				<?php if (currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
			</div>
		</div>
	</div>




	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_unit_price'), 'unit_price_label', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">
				<?php if (!currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
				<?php echo form_input(
					array(
						'name' => 'unit_price',
						'id' => 'unit_price',
						'class' => 'form-control input-sm',
						// 'readonly' => 'readonly',
						'value' => to_currency_no_money($item_info->unit_price)
					)
				); ?>
				<?php if (currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>

			</div>

		</div>
	</div>
	<div class="form-group form-group-sm">
		<?php echo form_label('Retail Price Markup', 'unit_price_markup_label', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">

				<?php echo form_input(
					array(
						'name' => 'unit_price_markup',
						'id' => 'unit_price_markup',

						'class' => 'form-control input-sm',
						'value' =>  $item_info->unit_price_markup
					)
				); ?>


			</div>

		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_whole_price'), 'unit_price', array('class' => ' control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">
				<?php if (!currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
				<?php echo form_input(
					array(
						'name' => 'whole_price',
						'id' => 'whole_price',
						'class' => 'form-control input-sm',
						'readonly' => 'readonly',
						'value' => to_currency_no_money($item_info->whole_price)
					)
				); ?>
				<?php if (currency_side()) : ?>
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="form-group form-group-sm">
		<?php echo form_label('Wholesale Price Markup', 'wholesale_price_markup_label', array('class' => ' control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">

				<?php echo form_input(
					array(
						'name' => 'wholesale_price_markup',
						'id' => 'wholesale_price_markup',
						'class' => 'form-control input-sm',

						'value' => $item_info->wholesale_price_markup
					)
				); ?>


			</div>

		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label("Zero Quantity", 'zero_quantity', array('class' => ' control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">

				<?php echo form_checkbox("zero_quantity",  1, FALSE); ?>


			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label("Delete", 'is_deleted', array('class' => ' control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">

				<?php echo form_checkbox("is_deleted",  1, $item_info->deleted ? TRUE : FALSE); ?>


			</div>
		</div>
	</div>






	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_reorder_level'), 'reorder_level', array('class' => ' control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_input(
				array(
					'name' => 'reorder_level',
					'id' => 'reorder_level',
					'class' => 'form-control input-sm',
					'value' => isset($item_info->item_id) ? to_quantity_decimals($item_info->reorder_level) : to_quantity_decimals(0)
				)
			); ?>
		</div>
	</div>



	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('items_description'), 'description', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_textarea(
				array(
					'name' => 'description',
					'id' => 'description',
					'class' => 'form-control input-sm',
					'value' => $item_info->description
				)
			); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label('Image', 'items_image', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="fileinput <?php echo $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
				<div class="fileinput-new thumbnail" style="width: 100px; height: 100px;"></div>
				<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;">
					<img data-src="holder.js/100%x100%" alt="<?php echo $this->lang->line('items_image'); ?>" src="<?php echo $image_path; ?>" style="max-height: 100%; max-width: 100%;">
				</div>
				<div>
					<span class="btn btn-default btn-sm btn-file">
						<span class="fileinput-new"><?php echo $this->lang->line("items_select_image"); ?></span>
						<span class="fileinput-exists"><?php echo $this->lang->line("items_change_image"); ?></span>
						<input type="file" name="item_image" accept="image/*">
					</span>
					<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("items_remove_image"); ?></a>
				</div>
			</div>
		</div>
	</div>




	<?php
	for ($i = 1; $i <= 10; ++$i) {
	?>
		<?php
		if ($this->config->item('custom' . $i . '_name') != null) {
			$item_arr = (array) $item_info;
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->config->item('custom' . $i . '_name'), 'custom' . $i, array('class' => 'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_input(
						array(
							'name' => 'custom' . $i,
							'id' => 'custom' . $i,
							'class' => 'form-control input-sm',
							'value' => $item_arr['custom' . $i]
						)
					); ?>
				</div>
			</div>
	<?php
		}
	}
	?>
</fieldset>
<?php echo form_close();
//print_r($suppliers);
//  print_r($selected_supplier);
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
		$("#wholesale_price_markup").on('input', function() {
			var cost = parseFloat($("#cost_price").val());
			var pack = parseFloat($("#items_per_pack").val());
			var whole_markup = parseFloat($("#wholesale_price_markup").val());
			var whole = Math.ceil(cost * whole_markup * pack * 100); //rounding up to 2 decimal places
			//$("#whole_price").val(whole / 100);
			set_nearest_five((whole / 100), $("#whole_price"));
		});


		// $("#unit_price_markup").on('input', function() {

		// 	var cost = parseFloat($("#cost_price").val());

		// 	var unit_markup = parseFloat($("#unit_price_markup").val());
		// 	var unit = Math.ceil(cost * unit_markup * 100); //rounding up to 2 decimal places
		// 	//$("#unit_price").val(unit / 100);
		// 	set_nearest_five((unit / 100), $("#unit_price"));
		// });

		//update made here


		$("#unit_price_markup").on('input', function() {
			var cost = parseFloat($("#cost_price").val());

			console.log('click');

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
				url: "<?php echo site_url("$controller_name/remove_logo/$item_info->item_id"); ?>",
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
								'#reference_number, #name, #cost_price, #unit_price,').val('');
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
							"item_id": "<?php echo $item_info->item_id; ?>",
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
