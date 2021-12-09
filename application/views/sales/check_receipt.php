<?php //$this->load->view("partial/headero"); ?>
<?php $this->load->view("partial/header_print"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

    <div class="print_hide" id="control_buttons" style="text-align:right">
            <a href="javascript:window.history.go(-1);">
                <div class="btn btn-info btn-sm" id="show_print_button">
                    <span class="glyphicon glyphicon-arrow-left">&nbsp;Back</span>
                </div>
            </a>
        </div>


		<div id="required_fields_message"> Enter Sale Receipt NO</div>

		<ul id="error_message_box" class="error_message_box"></ul>

		<?php echo form_open('sales/view_receipt', array('id' => 'iterem_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
		<fieldset id="item_basic_info">
			<?php
			if (isset($error)) {
				echo '<div class="form-group form-group-sm text-center">
				<span class="text-danger">' . $message . '</span>
			</div>';
			}
			?>
			<div class="form-group form-group-sm">
				<?php echo form_label('Receipt NO', 'receipt', array('class' => 'required control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<div class="input-group">
						<span class="input-group-addon input-sm"> POS </span>
						<?php echo form_input(
							array(
								'name' => 'receipt',
								'id' => 'receipt',
								// 'type' => 'numeric',
								'class' => 'form-control input-sm',
							)
						); ?>
					</div>
                    
				</div>


			</div>

           
			<div class="form-group form-group-sm">

				<div class='col-xs-9'>
					<?php echo form_submit(array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => $this->lang->line('common_submit'),
						'class' => 'btn btn-primary btn-sm pull-right'
					)); ?>
				</div>
			</div>


		</fieldset>
		<?php echo form_close();
		//print_r($suppliers);
		//print_r($selected_supplier);
		?>

		<?php
			if(!empty($message)){
				echo "<h1 class='text-center'> ". $message . "</h1> ";
			}
		
		?>
	</div>
</div>


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

        //this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#item").autocomplete({
			source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
                console.log(ui.item);
                // document.getElementById("price").innerHTML=ui.item.label;
				// window.alert(ui.item); //this will show the item_id of the selected item
				$("#iterem_form").submit();
				return false;
			}
		});



		// $("#item").autocomplete({
		// 	source: '<?php echo site_url($controller_name . "/item_search"); ?>',
		// 	minChars: 2,
		// 	autoFocus: false,
		// 	delay: 500,
		// 	response: function(a, ui){
		// 		if(ui.content.length == 1){
		// 			document.getElementById("price").innerHTML=ui.content[0].item.label;
		// 		}
		// 	},
		// 	select: function(a, ui) {
		// 		$(this).val(ui.item.value);
        //         console.log(ui.item);
        //         document.getElementById("price").innerHTML=ui.item.label;
		// 		// window.alert(ui.item); //this will show the item_id of the selected item
		// 		// $("#iterem_form").submit();
		// 		return false;
		// 	}
		// });

		// $('#item').keypress(function(e) {
		// 	if (e.which == 13) {
		// 		// console,
		// 		$('#iterem_form').submit();
		// 		return false;
		// 	}
		// });

		$('#item').focus();

		$('#item').keypress(function(e) {
			if (e.which == 13) {
				$('#iterem_form').submit();
				return false;
			}
		});

		$('#item').blur(function() {
			$(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
		});






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
							$(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1,' +
								'#tax_percent_name_1, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price').val('');
							// de-select any checkboxes, radios and drop-down menus
							$(':input', '#item_form').not('#item_category_id').removeAttr('checked').removeAttr('selected');
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

				quantity: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},

				receiving_quantity: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},
				reorder_level: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},
				tax_percent: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				}
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
				<?php
				if(isset($stock_locations) && count($stock_locations)>0){
                    foreach ($stock_locations as $key => $location_detail) {
                    ?>
                    <?php echo 'quantity_' . $key ?>: {
                        required: "<?php echo $this->lang->line('items_quantity_required'); ?>",
                            number: "<?php echo $this->lang->line('items_quantity_number'); ?>"
                    },
                    <?php
                    }
                }
				?>
				receiving_quantity: {
					required: "<?php echo $this->lang->line('items_quantity_required'); ?>",
					number: "<?php echo $this->lang->line('items_quantity_number'); ?>"
				},
				reorder_level: {
					required: "<?php echo $this->lang->line('items_reorder_level_required'); ?>",
					number: "<?php echo $this->lang->line('items_reorder_level_number'); ?>"
				},
				tax_percent: {
					required: "<?php echo $this->lang->line('items_tax_percent_required'); ?>",
					number: "<?php echo $this->lang->line('items_tax_percent_number'); ?>"
				}
			}
		}, form_support.error));
	});
</script>

<?php $this->load->view("partial/footer"); ?>
