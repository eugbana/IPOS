<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
	<!-- Start content -->
	<div class="content">

		<div id="page_title"><?php echo 'Report Parameters'; //$this->lang->line('reports_report_input'); 
								?></div>

		<?php
		if (isset($error)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
		}
		?>

		<?php echo form_open('#', array('id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
			<div class="col-xs-3">
				<?php echo form_input(array('name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker')); ?>
			</div>
		</div>


		<!-- <div class="form-group form-group-sm">
			<?php //echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
			<div class="col-xs-3">
				<?php //cho form_input(array('name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
			</div>
		</div> -->

		<?php
		if ($mode == 'sale') {
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', array(
						'all' => $this->lang->line('reports_all'),
						'sales' => $this->lang->line('reports_sales'),
						'returns' => $this->lang->line('reports_returns'),
                        'irecharge'=>"iRecharge"
					), 'all', array('id' => 'sale_type', 'class' => 'form-control')); ?>
				</div>
			</div>
			<!-- <div class="form-group form-group-sm">
				<?php echo form_label("Category", 'reports_sale_category_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_category' class="col-xs-3">
					<?php echo form_dropdown('sale_category', $categories, 'all', array('id' => 'category', 'class' => ' form-control')); ?>
				</div>
			</div> -->
			<div class="form-group form-group-sm">
				<?php echo form_label("VATed", 'reports_sale_vat_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_vat' class="col-xs-3">
					<?php echo form_dropdown('sale_vat', $vatable, 'all', array('id' => 'vat', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Payment Type", 'reports_sale_payment_type_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_payment_type' class="col-xs-3">
					<?php echo form_dropdown('payment_type', $payment_types, 'all', array('id' => 'payment_type', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Discounted", 'reports_sale_discount_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_discount' class="col-xs-3">
					<?php echo form_dropdown('sale_discount', array('all' => 'All', 'YES' => 'YES', 'NO' => 'NO'), 'all', array('id' => 'discount', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label("Credit", 'reports_sale_credit_label', array('class' => ' control-label col-xs-2')); ?>

				<div id='report_sale_credit' class="col-xs-3">
					<?php echo form_dropdown('sale_credit', array('all' => 'All', 'YES' => 'YES', 'NO' => 'NO'), 'all', array('id' => 'credit', 'class' => ' form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>

			<!-- <div class="form-group form-group-sm">
				<?php //echo form_label('Customer', 'reports_sale_customer_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_customer_type' class="col-xs-3">
					<?php// echo form_dropdown('customer_id', $customer, 'all', array('id' => 'customer_id', 'class' => 'form-control')); ?>
				</div>
			</div> -->

			<div class="form-group form-group-sm">
				<?php echo form_label("Customer", 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
				<div class="col-xs-3">
					<?php echo form_input(array('name' => 'customer_id', 'id' => 'customer_id', 'placeholder'=>'all oo', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php


				echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class' => ' control-label col-xs-2'));


				?>
				<div id='report_stock_location' class="col-xs-3">
                    <?php
                    //                    echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control'));
                    // if(isset($logged_in_role)){
                        // if($logged_in_role == 3 || $logged_in_role == 10){
                            // echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control'));
                        // }else{
                            // echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control','readonly'=>'readonly'));
                        // }
                    // }
                    //                    echo form_dropdown('stock_location', $stock_locations, $cur_loc, array('id' => 'location_id', 'class' => 'form-control','disabled'=>'disabled'));
                    ?>
					<input type="hidden" name="stock_location" value="<?=$current_location?>"/>
<!--					--><?php //echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		}elseif ($mode == 'irecharge') {
            ?>
            <div class="form-group form-group-sm">
<!--                --><?php //echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class' => ' control-label col-xs-2')); ?>
                <input type="hidden" id="report_sale_type" class="form-control" value="irecharge" />
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => ' control-label col-xs-2'));
                ?>
                <div id='report_receiving_type' class="col-xs-3">
                    <?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php

                echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class' => ' control-label col-xs-2'));

                ?>
                <div id='report_stock_location' class="col-xs-3">
                    <?php
                    //                    echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control'));
                    if(isset($logged_in_role)){
                        if($logged_in_role == 3 || $logged_in_role == 10){
                            echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control'));
                        }else{
                            echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control','readonly'=>'readonly'));
                        }
                    }
                    //                    echo form_dropdown('stock_location', $stock_locations, $cur_loc, array('id' => 'location_id', 'class' => 'form-control','disabled'=>'disabled'));
                    ?>
                    <!--					--><?php //echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control')); ?>
                </div>
            </div>
            <?php
        }
		elseif ($mode == 'receiving') {
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('reports_receiving_type'), 'reports_receiving_type_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('receiving_type', array(
						'all' => $this->lang->line('reports_all'),
						'receiving' => $this->lang->line('reports_receivings'),
						'returns' => $this->lang->line('reports_returns'),
						//'requisitions' => $this->lang->line('reports_requisitions')

					), 'all', array('id' => 'input_type', 'class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label("Supplier", 'report_date_range_label', array('class' => 'control-label col-xs-2 ')); ?>
				<div class="col-xs-3">
					<?php echo form_input(array('name' => 'supplier', 'id' => 'supplier', 'placeholder'=>'all', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex)); ?>
				</div>
			</div>


			<!-- <div class="form-group form-group-sm">
				<?php echo form_label('Supplier', 'reports_supplier_label', array('class' => ' control-label col-xs-2')); ?>
				<div id='report_supplier' class="col-xs-3">
					<?php echo form_dropdown('supplier', $suppliers, 'all', array('id' => 'supplier', 'class' => 'form-control')); ?>
				</div>
			</div> -->

			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'employee', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php


				echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class' => ' control-label col-xs-2'));


				?>
				<div id='report_stock_location' class="col-xs-3">
					<?php
//                    echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control'));
                    if(isset($logged_in_role)){
                        if($logged_in_role == 3 || $logged_in_role == 10){
                            echo form_dropdown('stock_location', $stock_locations, $cur_loc, array('id' => 'location_id', 'class' => 'form-control'));
                        }else{
                            echo form_dropdown('stock_location', $stock_locations, $current_location, array('id' => 'location_id', 'class' => 'form-control','readonly'=>'readonly'));
                        }
                    }
//                    echo form_dropdown('stock_location', $stock_locations, $cur_loc, array('id' => 'location_id', 'class' => 'form-control','disabled'=>'disabled'));
                    ?>
				</div>
			</div>

		<?php
		} elseif ($mode == 'transfer') {
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label('Employee', 'reports_transfer_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('employee_id', $employee, 'all', array('id' => 'employee_id', 'class' => 'form-control')); ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Transfering Branch', 'reports_transfer_from_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_from_type' class="col-xs-3">

					<?php
                    if(isset($logged_in_role)){
                        if($logged_in_role == 3 || $logged_in_role == 10){
                            echo form_dropdown('from_branch', $stock_locations, $current_location, array('id' => 'from_branch', 'class' => 'form-control'));
                        }else{
                            echo form_dropdown('from_branch', $stock_locations, $current_location, array('id' => 'from_branch', 'class' => 'form-control','readonly'=>'readonly'));
                        }
                    }
                    ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?php echo form_label('Receiving Branch', 'reports_transfer_to_type_label', array('class' => ' control-label col-xs-2'));
				?>
				<div id='report_receiving_to_type' class="col-xs-3">
				<?php echo form_dropdown('to_branch', $stock_locations, 'all', array('id' => 'to_branch', 'class' => 'form-control')); ?>
				</div>
			</div>
		<?php
		}
		echo form_button(
			array(
				'name' => 'generate_report',
				'id' => 'generate_report',
				'content' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm'
			)
		);
		?>
		<?php echo form_close(); ?>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/daterangepicker'); ?>

		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#item").autocomplete({
			source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				//window.alert(ui.item.value); this will show the item_id of the selected item
				$("#add_item_form").submit();
				return false;
			}
		});

		$('#item').focus();

		$('#item').keypress(function(e) {
			if (e.which == 13) {
				$('#add_item_form').submit();
				return false;
			}
		});

		$('#item').blur(function() {
			$(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
		});



		//customers search
		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#customer_id").autocomplete({
			//source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			source: '<?php echo site_url("sales/customer_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				console.log(ui);
				// window.alert(ui.item.value); //this will show the item_id of the selected item
				// $("#add_item_form").submit();
				return false;
			}
		});

		// $('#customer_id').focus();

		$('#customer_id').keypress(function(e) {
			if (e.which == 13) {
				$(this).val(ui.item.value);
				// $('#add_item_form').submit();
				return false;
			}
		});

		$('#customer_id').blur(function() {
			// $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
			// $(this).val("all");
		});

		//suppliers search
		//this is when a name is typed on the item input box and suggestion appear, when u click, this function is exected in order to add the item to the cart
		$("#supplier").autocomplete({
			//source: '<?php echo site_url($controller_name . "/item_search"); ?>',
			source: '<?php echo site_url("sales/supplier_search"); ?>',
			minChars: 2,
			autoFocus: false,
			delay: 500,
			select: function(a, ui) {
				$(this).val(ui.item.value);
				console.log(ui);
				// window.alert(ui.item.value); //this will show the item_id of the selected item
				// $("#add_item_form").submit();
				return false;
			}
		});

		// $('#customer_id').focus();

		$('#supplier').keypress(function(e) {
			if (e.which == 13) {
				$(this).val(ui.item.value);
				// $('#add_item_form').submit();
				return false;
			}
		});

		$('#supplier').blur(function() {
			// $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
			// $(this).val("all");
		});



		$("#generate_report").click(function() {
			<?php
			if ($mode == 'receiving') {
			?>
				window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val() || <?=$current_location?>, $('#employee_id').val(), $("#supplier").val()].join("/");
			<?php
			} elseif ($mode == 'sale') {
			?>
            console.log(start_date,end_date, $("#employee_id").val(), $("#location_id").val() || <?=$current_location?>, $("#sale_type").val() || 0, $("#credit").val(), $("#vat").val()||'all', $("#customer_id").val() || 'all', $("#discount").val(), $("#payment_type").val());
			// return false;
            window.location = ["<?php echo site_url('reports/specific_employee');  ?>", start_date, end_date, $("#employee_id").val(), $("#location_id").val()||<?=$current_location?>, $("#sale_type").val() || 0, $("#credit").val(), $("#vat").val()||'all', $("#customer_id").val() || 'all', $("#discount").val(), $("#payment_type").val()].join("/");
			<?php
			} elseif ($mode == 'transfer') {
			?>

				window.location = [window.location, start_date, end_date, $("#employee_id").val() || 0, $("#from_branch").val(), $("#to_branch").val()].join("/");
			<?php
			} elseif ($mode == 'irecharge') {
            ?>
            window.location = "<?php echo site_url('reports/fetch_irecharge_trans');?>";
            <?php
            } else {
			?>
				window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val() || <?=$current_location?>].join("/");

			<?php } ?>
		});
	});
</script>
