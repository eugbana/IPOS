<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/save_update/'.$item_info->item_id, array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_item_number'), 'item_number', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?php echo form_input(array(
							'name'=>'item_number',
							'id'=>'item_number',
							'class'=>'form-control input-sm',
							'value'=>$item_info->item_number)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_pname'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'value'=>$item_info->name)
						);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label('Category', 'category', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('category', $categories, $selected_category, array('class'=>'form-control')); ?>
			</div>
			
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_category'), 'company', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'company',
							'id'=>'company',
							'value'=>$item_info->company,
							'class'=>'form-control input-sm')
							);?>
							<?php echo form_hidden('stock_type', 0); ?>
					<?php echo form_hidden('receiving_quantity', to_quantity_decimals(0)); ?>
					<?php echo form_hidden('item_type', 0); ?>
				</div>
			</div>
		</div>
		<div class="form-group form-group-sm">	
	<?php echo form_label('prescription', 'prescriptions', array('class'=>'control-label col-xs-3')); ?>
	<div class="col-xs-4">
		<label class="radio-inline">
			<?php echo form_radio(array(
					'name'=>'prescriptions',
					'type'=>'radio',
					'id'=>'prescriptions',
					'value'=>'YES',
					'checked'=>$item_info->prescriptions === 'YES')
					); ?> <?php echo 'Yes'; ?>
		</label>
		<label class="radio-inline">
			<?php echo form_radio(array(
					'name'=>'prescriptions',
					'type'=>'radio',
					'id'=>'prescriptions',
					'value'=>'NO',
					'checked'=>$item_info->prescriptions === 'NO' || empty($item_info->prescriptions))
					); ?> <?php echo 'No'; ?>
		</label>

	</div>
	<?php echo form_label('Shelf', 'shelf', array('class'=>'control-label col-xs-1')); ?>
			<div class='col-xs-3'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'shelf',
							'id'=>'shelf',
							'class'=>'form-control input-sm',
							'value'=>$item_info->shelf)
							);?>
				</div>
			</div>
</div>
		<div class="form-group form-group-sm">
			<?php echo form_label('Type', 'category', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<div class="input-group">
					<?php echo form_dropdown('product_type', $product_type, $selected_product_type, array('class'=>'form-control')); ?>
				</div>
			</div>
			<?php echo form_label('Grammage', 'grammage', array('class'=>'control-label col-xs-2')); ?>
			<div class='col-xs-3'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'grammage',
							'id'=>'grammage',
							'class'=>'form-control input-sm',
							'value'=>$item_info->grammage)
							);?>
				</div>
			</div>
		</div>
		
		
		<div class="form-group form-group-sm">
			<?php echo form_label("Expiry Warning Period", 'category', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?php echo form_input(array(
							'name'=>'expiry_days',
							'id'=>'expiry_days',
							'placeholder'=>'numeric value',
							'value'=>$item_info->expiry_days,
							'class'=>'form-control input-sm')
							);?>
				</div>
			</div>
			<div class='col-xs-3'>
				<div class="input-group">
					
					<?php echo form_dropdown('period', $period, $selected_period, array('class'=>'form-control')); ?>
				</div>
			</div>
		</div>
		<div class="form-group form-group-sm">
			
			<?php echo form_label($this->lang->line('items_per_pack'), 'category', array('class'=>'required control-label col-xs-2')); ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'items_per_pack',
							'id'=>'items_per_pack',
							'class'=>'form-control input-sm',
							'value'=>$item_info->pack)
							);?>
				</div>
			</div>
		</div>
		

		
        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier, array('class'=>'form-control')); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_cost_price'), 'cost_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class="col-xs-8">
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'cost_price',
							'id'=>'cost_price',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($item_info->cost_price))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
		
		
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_unit_price'), 'unit_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'unit_price',
							'id'=>'unit_price',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($item_info->unit_price))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_whole_price'), 'unit_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'whole_price',
							'id'=>'whole_price',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($item_info->whole_price))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

        

		<?php if($customer_sales_tax_enabled) { ?>
            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('taxes_tax_category'), 'tax_category', array('class'=>'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
					<?php echo form_dropdown('tax_category_id', $tax_categories, $selected_tax_category, array('class'=>'form-control')); ?>
                </div>
            </div>
		<?php } ?>

        
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_reorder_level'), 'reorder_level', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'reorder_level',
						'id'=>'reorder_level',
						'class'=>'form-control input-sm',
						'value'=>isset($item_info->item_id) ? to_quantity_decimals($item_info->reorder_level) : to_quantity_decimals(0))
						);?>
			</div>
		</div>

		

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_description'), 'description', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control input-sm',
						'value'=>$item_info->description)
						);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label('Image', 'items_image', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="fileinput <?php echo $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
					<div class="fileinput-new thumbnail" style="width: 100px; height: 100px;"></div>
					<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;">
						<img data-src="holder.js/100%x100%" alt="<?php echo $this->lang->line('items_image'); ?>"
							 src="<?php echo $image_path; ?>"
							 style="max-height: 100%; max-width: 100%;">
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
		for ($i = 1; $i <= 10; ++$i)
		{
		?>
			<?php
			if($this->config->item('custom'.$i.'_name') != null)
			{
				$item_arr = (array)$item_info;
			?>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->config->item('custom'.$i.'_name'), 'custom'.$i, array('class'=>'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_input(array(
								'name'=>'custom'.$i,
								'id'=>'custom'.$i,
								'class'=>'form-control input-sm',
								'value'=>$item_arr['custom'.$i])
								);?>
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
	$(document).ready(function()
	{
		
		$('#datetimepicker2').datetimepicker( {
			locale: "ar"
		} );
		$('#datetimepicker3').datetimepicker();
		$("#new").click(function() {
			stay_open = true;
			$("#item_form").submit();
		});
		
		
		


		$("#submit").click(function() {
			stay_open = false;
		});
		$('#items_per_pack').focusout(function(){
			
			var items_per_pack=parseInt($('#items_per_pack').val());
			var pack=parseInt($('#pack').val());
			var total=items_per_pack*pack;
			//var unit=round5(selling);
			$('#quantity').val(total);
		});
		$('#pack').focusout(function(){
			var items_per_pack=parseInt($('#items_per_pack').val());
			var pack=parseInt($('#pack').val());
			var total=items_per_pack*pack;
			//var unit=round5(selling);
			$('#quantity').val(total);
		});
		$('#quantity').focusout(function(){
			
			var items_per_pack=parseInt($('#items_per_pack').val());
			var pack=parseInt($('#pack').val());
			var total=items_per_pack*pack;
			//var unit=round5(selling);
			$('#quantity').val(total);
			
			
		});

		var no_op = function(event, data, formatted){};
		$("#category").autocomplete({source: "<?php echo site_url('items/suggest_category');?>",delay:10,appendTo: '.modal-content'});

		<?php for ($i = 1; $i <= 10; ++$i)
		{
		?>
			$("#custom"+<?php echo $i; ?>).autocomplete({
				source:function (request, response) {
					$.ajax({
						type: "POST",
						url: "<?php echo site_url('items/suggest_custom');?>",
						dataType: "json",
						data: $.extend(request, $extend(csrf_form_base(), {field_no: <?php echo $i; ?>})),
						success: function(data) {
							response($.map(data, function(item) {
								return {
									value: item.label
								};
							}))
						}
					});
				},
				delay:10,
				appendTo: '.modal-content'});
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
						if (stay_open)
						{
							// set action of item_form to url without item id, so a new one can be created
							$("#item_form").attr("action", "<?php echo site_url("items/save/")?>");
							// use a whitelist of fields to minimize unintended side effects
							$(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1,' +
								'#tax_percent_name_1, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price').val('');
							// de-select any checkboxes, radios and drop-down menus
							$(':input', '#item_form').not('#item_category_id').removeAttr('checked').removeAttr('selected');
						}
						else
						{
							dialog_support.hide();
						}
						table_support.handle_submit('<?php echo site_url('items'); ?>', response, stay_open);
					},
					dataType: 'json'
				});
			},

			rules:
			{
				name:"required",
				category:"required",
				item_number:
				{
					required: false,
					remote:
					{
						url: "<?php echo site_url($controller_name . '/check_item_number')?>",
						type: "post",
						data: $.extend(csrf_form_base(),
						{
							"item_id" : "<?php echo $item_info->item_id; ?>",
							"item_number" : function()
							{
								return $("#item_number").val();
							},
						})
					}
				},
				cost_price:
				{
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
				unit_price:
				{
					required:true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
				
				quantity:
				{
					required:true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
				
				receiving_quantity:
				{
					required:true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
				reorder_level:
				{
					required:true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
				tax_percent:
				{
					required:true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				}
			},

			messages:
			{
				name:"<?php echo $this->lang->line('items_name_required'); ?>",
				item_number: "<?php echo $this->lang->line('items_item_number_duplicate'); ?>",
				category:"<?php echo $this->lang->line('items_category_required'); ?>",
				cost_price:
				{
					required:"<?php echo $this->lang->line('items_cost_price_required'); ?>",
					number:"<?php echo $this->lang->line('items_cost_price_number'); ?>"
				},
				unit_price:
				{
					required:"<?php echo $this->lang->line('items_unit_price_required'); ?>",
					number:"<?php echo $this->lang->line('items_unit_price_number'); ?>"
				},
				<?php
				foreach($stock_locations as $key=>$location_detail)
				{
				?>
					<?php echo 'quantity_' . $key ?>:
					{
						required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
						number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
					},
				<?php
				}
				?>
				receiving_quantity:
				{
					required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
					number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
				},
				reorder_level:
				{
					required:"<?php echo $this->lang->line('items_reorder_level_required'); ?>",
					number:"<?php echo $this->lang->line('items_reorder_level_number'); ?>"
				},
				tax_percent:
				{
					required:"<?php echo $this->lang->line('items_tax_percent_required'); ?>",
					number:"<?php echo $this->lang->line('items_tax_percent_number'); ?>"
				}
			}
		}, form_support.error));
	});
</script>

