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


		<div id="required_fields_message"> <b> Opening & Closing Stock report </b> <br><br> </div>

        <ul id="error_message_box" class="error_message_box"></ul>
        

		<fieldset id="item_basic_info">
			<?php
			if (isset($error)) {
				echo '<div class="form-group form-group-sm text-center">
				<span class="text-danger">' . $message . '</span>
			</div>';
			}
            ?>
<!--            <form action="items/stock_report" method="get">-->
            <?php echo form_open('items/stock_report',['method'=>'post','id'=>'report-form'])?>
                <div class="form-group form-group-sm">

					<div class='col-xs-4'>
						<?php echo form_label('Year', 'year', array('class' => 'control-label col-xs-2 required')); ?>
						<?php echo form_dropdown('year', 
							$years,
							isset($selected_year)? $selected_year: date('Y'), array('class' => 'form-control input-sm required'));
							?>
					</div>

					<div class='col-xs-4'>
						<?php echo form_label('Month', 'month', array('class' => 'control-label col-xs-2 required')); ?>
						<?php echo form_dropdown('month', 
							$months,
                            isset($selected_month)? $selected_month: date('m'), array('class' => 'form-control input-sm required'));
							?>
					</div>

					<div class='col-xs-4'>
						<?php echo form_label('Type', 'type', array('class' => 'control-label col-xs-2 required')); ?>
						<?php echo form_dropdown('type', 
							$stock_types,
							$selected_type, array('class' => 'form-control input-sm required'));
							?>
					</div>

                        
                        
                        <div class="form-group form-group-sm">
						<br/>
                        <br/>
                        <br/>
							<div class="col-xs-5"></div>
								<div class='col-xs-2'>
								<input type="submit" value="Submit" class="btn btn-primary btn-md pull-right pt-5">
								</div>
							<div class="col-xs-5"></div>
                        </div> 

                        <br/><br/>

                </div>


                </div>
            </form>


		</fieldset>
                <?php
                            if($value){

							    $val = '';
                                if($value == 'No report for the selected Date'){
                                    $val = 'No report for the selected Date';
                                }else{
                                    $val = to_currency($value);
                                }
							// if($search_query){
								echo "<div class='col-md-3'></div>";

								echo "<div class='col-md-6'>";
								echo "<h5>Stock Value:</h5><br><br>";
                                echo "<h1>  " .$val . "</h1><br><br> </div>";
                                echo "<div class='col-md-3'></div>";

							// }
                            }
                        ?>
<!--    //echo form_close();-->
<!--		//print_r($suppliers);-->
<!--		//print_r($selected_supplier);-->
<!--		?>-->
    <canvas id="chart-zone" width="400" height="200"></canvas>
	</div>
</div>

<script type="text/javascript" src="chartjs/chart.js"></script>
<script type="text/javascript">
	//validation and submit handling
    <?php
        $label = [];
        $labelData = [];
        if(isset($value_range) && count($value_range)>0){
            foreach ($value_range as $val){
                $label[] = $months[$val->month].'-'.$val->year;
                $labelData[] = $val->stock_value;
            }
        }
    ?>
    var label = <?php echo json_encode($label)?>;
    var labelData = <?php echo json_encode($labelData) ?>;
    var ctx = document.getElementById('chart-zone');
    var chart = new Chart(ctx,{
        type : 'bar',
        data: {
            labels: label,
            datasets:[{
                label: "Stock values",
                data: labelData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options:{
            scales:{
                y:{
                    beginAtZero:true
                }
            }
        }
    });
    // alert(JSON.stringify(labelData));
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
		}, form_support.error))
	});
</script>

<?php $this->load->view("partial/footer"); ?>
