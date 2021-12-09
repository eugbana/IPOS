<?php $this->load->view("partial/header"); ?>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <div style="width: 60%;display: none" id="error-div"  class='alert alert-dismissible alert-danger'>No Error</div>
        <div style="width: 60%;display: none" id="warning-div"  class='alert alert-dismissible alert-warning'>No warning</div>
        <div style="width: 60%;display: none" id="success-div"  class='alert alert-dismissible alert-success'>Not yet</div>
        <?php
        if (isset($error)) {
            echo "<div style='width:60%;' class='alert alert-dismissible alert-danger'>" . $error . "</div>";
        }

        if (!empty($warning)) {
            echo "<div style='width:60%;' class='alert alert-dismissible alert-warning'>" . $warning . "</div>";
        }

        if (isset($success)) {
            echo "<div style='width:60%;' class='alert alert-dismissible alert-success'>" . $success . "</div>";
        }
        ?>
        <center>

            <div id="register_wrapper" style="width:100%;">


                <!-- Top register controls -->
                <?php echo form_open($controller_name . "/cancel", array('id' => 'buttons_form')); ?>
                <div class="form-group" id="buttons_sale">
                    <input type="hidden" name="transfer_to_location" id="transfer_to_location">


                </div>
                <?php echo form_close(); ?>

                <?php $tabindex = 0; ?>

                <?php echo form_open($controller_name . "/add_push", array('id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default')); ?>
                <div class="panel-body form-group">

                    <ul>
                        <li class="pull-center">


                            <?php echo anchor("receivings/transfer_history", '<span class="md md-history">&nbsp</span>Transfer History', array('class' => 'btn btn-info btn-sm pull-right', 'id' => 'show_transfer_history_button')); ?>


                        </li>

                        <li class="pull-center">
                            <?php echo form_input(array('name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50',
                                'tabindex' => ++$tabindex,'placeholder'=>$this->lang->line('sales_start_typing_item'))); ?>
                            <span class="ui-helper-hidden-accessible" role="status"></span>
                        </li><br>


                        <!--<div class="clearfix">
							<div class="float-left">
								<?php //echo form_dropdown('to_branch', ['one' => "One", 'two' => 'Two', 'three' => 'Three'], 'two', array('class' => '', 'style' => 'width:50%;height:40px;', 'id' => 'to_branch'));
                        ?>
							</div>
							<div class="float-right">
								<li class="pull-right">
									<div class='btn btn-sm btn-danger pull-right' id='cancel_sale_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo $this->lang->line('sales_cancel_sale'); ?></div>
								</li>
								<li class="pull-right">
									<div class='btn btn-sm btn-success pull-right' id='push_button'><?php echo $this->lang->line('common_push'); ?></div>
								</li>
							</div>
						</div> End of the clearfix -->
                    </ul>
                </div>
                <?php echo form_close(); ?>


                <!-- Sale Items List -->

                <table class="sales_table_100" id="register">
                    <thead>
                    <tr>
                        <th style="width: 5%;"><?php echo $this->lang->line('common_delete'); ?></th>
                        <th style="width: 25%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
                        <th style="width: 5%;">Item Number</th>
                        <th style="width: 10%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
                        <th style="width: 12%;">Transfer Unit Price</th>
                        <th style="width: 15%;">Transfer Price</th>
                        <th style="width: 13%;">Qty Type </th>
                        <th style="width: 10%;">Batch</th>
                        <th style="width: 5%;"></th>
                    </tr>
                    </thead>

                    <tbody id="cart_contents">
                        <tr id="no-item-tr">
                            <td colspan='5'>
                                <div class='alert alert-dismissible alert-info'><?php echo $this->lang->line('sales_no_items_in_push'); ?></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="submit-selected-items" class="row " style="background:#BBB;padding-top:5px;padding-bottom:5px; display: none">

                    <div class="col-sm-6">
                        <?php echo form_open($controller_name . "/change_location", array('class' => 'form-horizontal bg-dark', 'id' => 'change_location_form')); ?>

                        <?php echo form_dropdown('location', $locator, $item['location'], array('id' => 'location', 'style' => 'width:55%;', 'class' => 'form-control',)); ?>
                        <?php echo form_close();  ?>
                    </div>
                    <div class="col-sm-6">
                        <li class="pull-right">
                            <div class='btn btn-sm btn-danger pull-right' id='cancel_sale_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo $this->lang->line('sales_cancel_sale'); ?></div>
                        </li>
                        <li class="pull-right">
                            <div class='btn btn-sm btn-success pull-right' id='push_button'>Transfer</div>
                        </li>
                    </div>
                </div>
            </div>
        </center>
    </div>
</div>

<script type="text/javascript">
    function addTableRow(data,inde){
        const rItem = data;
        console.log('ritem',rItem);
        let t_avail = (rItem.stock_type==="0")?'<p><b>Total available:</b>'+rItem.in_stock+'</p>':'';
        let q_el = (rItem.is_serialized === "1") ? rItem.quantity : '<input data-itemInd="'+inde+'" type="number" min="1" name="quantity"' +
            ' class="form-control input-sm selected-item-quantity" data-itemId="'+rItem.item_id+'" value="'+(rItem.quantity===0 ? 1 : rItem.quantity)+'" id="selected-item-quantity-'+rItem.item_id+'" onchange="updateQuantity($(this))" required/>';
        $('#cart_contents').prepend(
            '<tr id="'+rItem.item_id+'">' +
                '<td>' +
                    '<a onclick="removeRow($(this))" class="selected-items-del-anchor" data-itemInd="'+inde+'" data-itemId="'+rItem.item_id+'"><span class="glyphicon glyphicon-trash"></span></a>'+
                '</td>'+
                '<td>'
                    +rItem.name + ' ' + t_avail+
                '</td>'+
                '<td>' +
                    rItem.item_number+
                '</td>'+
                '<td>' +
                    q_el
                        +
                '</td>'+
                '<td>' +
                    '<select class="form-control selected-item-transfer-price" onchange="updatePriceType($(this))" data-itemInd="'+inde+'" data-itemId="'+rItem.item_id+'" id="selected-item-transfer-price-'+rItem.item_id+'">' +
                        '<option value="'+rItem.cost_price+'">Cost: '+rItem.cost_price+'</option>'+
                        '<option value="'+rItem.unit_price+'">Sell: '+rItem.unit_price+'</option>'+
                    '</select>'+
                '</td>'+
                '<td>' +
                    '<label id="item-price-'+rItem.item_id+'">'+rItem.quantity*rItem.transfer_price+'</label>'+
                '</td>'+
                '<td>' +
                    '<select class="form-control selected-item-pack-type" data-itemInd="'+inde+'" data-itemId="'+rItem.item_id+'" id="selected-item-pack-type-'+rItem.item_id+'">' +
                        '<option value="single">Single</option>'+
                    '</select>'+
                '</td>'+
                '<td>' +
                    '<select class="form-control selected-item-pack-type" name="batch_no" data-itemInd="'+inde+'" data-itemId="'+rItem.item_id+'" id="selected-item-pack-type-'+rItem.item_id+'">' +
                        $.map(rItem.batches,function(v){
                            return '<option value="'+v.batch_no+'" data-action="'+v.quantity+'" data-bexpiry="'+v.expiry+'">'+v.batch_no+'  rem: '+v.quantity+'</option>'
                        })+
                    '</select>'+
                '</td>'+
                '<td></td>'+
            '</tr>'
        );
        // console.log($('.selected-items-del-anchor'));
    }
    function removeRow(el){
        const thisId = el.attr('data-itemId');
        const item_ind = el.attr('data-itemInd');
        let selectedItems = JSON.parse(localStorage.getItem('items_selected'));
        // console.log('item_ind',item_ind);
        if(delete selectedItems[item_ind]){
            localStorage.setItem('items_selected',JSON.stringify(selectedItems))
            $('#'+thisId).remove();
        }else {
            alert("Item could not be removed!");
        }
        if(Object.keys(selectedItems).length < 1){
            $('#submit-selected-items').css('display','none');
        }
    }
    function updateQuantity(el){
        const thisId = el.attr('data-itemId');
        const item_ind = el.attr('data-itemInd');
        let selectedItems = JSON.parse(localStorage.getItem('items_selected'));
        let updatingItem = selectedItems[item_ind];
        const quant = el.val();
        const uPrice = $('#selected-item-transfer-price-'+thisId).val();
        const tPrice = uPrice * quant;
        updatingItem['quantity'] = parseInt(quant);
        updatingItem['transfer_price'] = uPrice;
        selectedItems[item_ind] = updatingItem;
        localStorage.setItem('items_selected',JSON.stringify(selectedItems));
        $('#item-price-'+thisId).text(tPrice);
    }
    function updateBatch(el,quanti = false) {
        const thisId = el.attr('data-itemId');
        const item_ind = el.attr('data-itemInd');
        let selectedItems = JSON.parse(localStorage.getItem('items_selected'));
        let updatingItem = selectedItems[item_ind];
        const quant = $('#selected-item-quantity-'+thisId).val();
        let batchQuantity = el.find(':selected').data('action');
        if(quant > batchQuantity){
            alert("The selected batch quantity is less than the quantity entered, the difference will be deducted from other batch with closest expiry date");
        }
        updatingItem['batch_no'] = el.val();
        updatingItem['selected_batch_quantity'] = batchQuantity;
        updatingItem['selected_batch_expiry'] = el.find(':selected').data('bexpiry');
        selectedItems[item_ind] = updatingItem;
        localStorage.setItem('items_selected',JSON.stringify(selectedItems));
    }
    function updatePriceType(el){
        const thisId = el.attr('data-itemId');
        const item_ind = el.attr('data-itemInd');
        let selectedItems = JSON.parse(localStorage.getItem('items_selected'));
        let updatingItem = selectedItems[item_ind];
        const uPrice = parseFloat(el.val());
        const quant = $('#selected-item-quantity-'+thisId);
        let tPrice = uPrice;
        if(quant.length !== 0){
            tPrice = uPrice * quant.val();
            updatingItem['transfer_price'] = uPrice;
            selectedItems[item_ind] = updatingItem;
            localStorage.setItem('items_selected',JSON.stringify(selectedItems));
        }
        $('#item-price-'+thisId).text(tPrice);
    }
    function addItem(item_id){
        $.post('<?php echo site_url("items/add_push/1"); ?>',{'item':item_id},
            function (data) {
                const data_pushed = data.pushed_items;
                if(data_pushed.length < 1){
                    alert("No such item or quantity is less than 1");
                    return false;
                }
                var last_selected_items = data.last_inserted;
                var selected_items_arr = JSON.parse(localStorage.getItem('items_selected'));
                var already_added = JSON.parse(localStorage.getItem('already_added'));
                console.log('initialized added',already_added);
                // let current_item = data_pushed[last_selected_items];

                const item_batches = data_pushed[last_selected_items].batches;
                if(item_batches.length > 0){
                    data_pushed[last_selected_items].batch_no = item_batches[0].batch_no;
                    data_pushed[last_selected_items]['selected_batch_quantity'] = item_batches[0].quantity;
                    data_pushed[last_selected_items]['selected_batch_expiry'] = item_batches[0].expiry;
                }else{
                    data_pushed[last_selected_items].batch_no = '';
                    data_pushed[last_selected_items]['selected_batch_quantity'] = 0;
                    data_pushed[last_selected_items]['selected_batch_expiry'] = '';
                }
                if(already_added === undefined||already_added===null || already_added.length === 0){
                    already_added = new Object();
                    already_added[item_id] = last_selected_items;
                }

                if (selected_items_arr === undefined || selected_items_arr === null || selected_items_arr.length === 0)
                {
                    // items is undefined, null, [] or '' (empty string)
                    selected_items_arr = data_pushed;
                }else {
                    // let server_selected_items_arr = data.pushed_items;
                    selected_items_arr[last_selected_items] = data_pushed[last_selected_items];
                }
                localStorage.setItem('items_selected',JSON.stringify(selected_items_arr));
                console.log("sel_before: ",already_added)
                if(!(item_id in already_added)){
                    // addTableRow(selected_items_arr[last_selected_items],last_selected_items);
                    // already_added.push(last_selected_items);
                    already_added[item_id] = last_selected_items;
                    console.log("sel_after: ",already_added)
                }else{
                    console.log("already added");
                    $('#'+item_id).remove();
                    console.log("item: ",item_id);
                    console.log("sel: ",already_added);
                }
                localStorage.setItem('already_added',JSON.stringify(already_added));
                addTableRow(selected_items_arr[already_added[item_id]],already_added[item_id]);
                if(Object.keys(selected_items_arr).length > 0){
                    $('#submit-selected-items').css('display','block');
                }
            },
            'json'
        );
        $('#item').val('');
    }
    $(document).ready(function() {
        let selected_items_arr = {};
        let csr = "<?=$this->security->get_csrf_token_name()?>";
        let csrv = "<?=$this->security->get_csrf_hash()?>";
        selected_items_arr = JSON.parse(localStorage.getItem('items_selected'));
        if (selected_items_arr !== undefined && selected_items_arr !== null && selected_items_arr.length !== 0)
        {
            $.each(selected_items_arr,function (i,v) {
                addTableRow(v,i);
            });
            if(Object.keys(selected_items_arr).length >0){
                $('#no-item-tr').remove();
                $('#submit-selected-items').css('display','block');
            }
        }
        let last_selected_items = 0;
        $('#item').focus();
        $('#item').keypress(function(e) {
            // return false;
            if (e.which === 13) {
                // alert("hi");
                var s_term = $(this).val();
                console.log('s_term',s_term);
                $(this).val('');
                if(s_term !== '') {
                    // $('#add_item_form').submit();
                    $('#no-item-tr').remove();
                    // $(this).val(ui.item.value);
                    addItem(s_term);
                }else{
                    alert("Enter a search term");
                }
                return false;
            }else{
                $(this).autocomplete({
                    //source: '<?php //echo site_url("items/item_search"); ?>//',
                    source: '<?php echo site_url("items/item_search/1"); ?>',
                    // minChars: 0,
                    minLength: 3,
                    autoFocus: false,
                    delay: 500,
                    select: function(a, ui) {
                        $('#no-item-tr').remove();
                        $(this).val(ui.item.value);
                        addItem(ui.item.value);

                        return false;
                    }
                });
            }
        });
        $("#batch").autocomplete({
            source: '<?php echo site_url("items/batch_search"); ?>',
            // minChars: 0,
            minLength:2,
            autoFocus: false,
            delay: 500,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $(this).parents("tr").prevAll("form:first").submit();
                return false;
            }
        });
        var clear_fields = function() {
            if ($(this).val().match("<?php echo $this->lang->line('sales_start_typing_item') . '|' . $this->lang->line('sales_start_typing_customer_name'); ?>")) {
                $(this).val('');
            }
        };
        $('[name="location"]').on("change", function() {
            // $(this).parents("tr").prevAll("form:first").submit()

            //alert($(this).val());
        });

        $("#push_button").click(function() {
            $(this).attr('disabled',true);
            const location = $('#location').val();
            if (location === '') {
                window.alert("You forgot to select receiving branch.");
            } else {
                $.post('<?php echo site_url($controller_name . "/global_item_push_transfer/1"); ?>',{'transfer_to_location':location,
                    'selected_items':localStorage.getItem('items_selected')},function (data){
                    if(data.success){
                        localStorage.removeItem('items_selected');
                        localStorage.removeItem('already_added');
                        $('#success-div').css('display','block').text(data.success);
                        let rForm = $('<form></form>');
                        let path = '<?php echo site_url($controller_name . "/push_receipt/"); ?>'+data.page+"/"+data.folder_name;
                        rForm.attr("method", "post");
                        rForm.attr("action", path);
                        let field = $('<input />');
                        field.attr("type", "hidden");
                        field.attr("name", 'data');
                        field.attr("value", JSON.stringify(data.receipt_data));
                        rForm.append(field);
                        let field2 = $('<input />');
                        field2.attr("type", "hidden");
                        field2.attr("name", csr);
                        field2.attr("value", csrv);
                        rForm.append(field2);
                        $(document.body).append(rForm);
                        rForm.submit();
                    }
                    if(data.warning){
                        $('#warning-div').css('display','block').text(data.warning);
                    }
                    if(data.error){
                        $('#error-div').css('display','block').text(data.error);
                    }
                },'json').fail(function(xhr,st,error){
                    alert(error);
                });
            }

        });

        $("#customer").autocomplete({
            source: '<?php echo site_url("customers/suggest"); ?>',
            // minChars: 0,
            minLength:2,
            delay: 10,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $("#select_customer_form").submit();
            }
        });

        $(".giftcard-input").autocomplete({
            source: '<?php echo site_url("giftcards/suggest"); ?>',
            // minChars: 0,
            minLength:2,
            delay: 10,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $("#add_payment_form").submit();
            }
        });

        $('#item,#batch, #customer').click(clear_fields).dblclick(function(event) {
            $(this).autocomplete("search");
        });

        $('#customer').blur(function() {
            $(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
        });

        $('#comment').keyup(function() {
            $.post('<?php echo site_url($controller_name . "/set_comment"); ?>', {
                comment: $('#comment').val()
            });
        });

        <?php
        if ($this->config->item('invoice_enable') == TRUE) {
        ?>
        $('#sales_invoice_number').keyup(function() {
            $.post('<?php echo site_url($controller_name . "/set_invoice_number"); ?>', {
                sales_invoice_number: $('#sales_invoice_number').val()
            });
        });

        var enable_invoice_number = function() {
            var enabled = $("#sales_invoice_enable").is(":checked");
            $("#sales_invoice_number").prop("disabled", !enabled).parents('tr').show();
            return enabled;
        }

        enable_invoice_number();

        $("#sales_invoice_enable").change(function() {
            var enabled = enable_invoice_number();
            $.post('<?php echo site_url($controller_name . "/set_invoice_number_enabled"); ?>', {
                sales_invoice_number_enabled: enabled
            });
        });
        <?php
        }
        ?>

        $("#sales_print_after_sale").change(function() {
            $.post('<?php echo site_url($controller_name . "/set_print_after_sale"); ?>', {
                sales_print_after_sale: $(this).is(":checked")
            });
        });

        $('#email_receipt').change(function() {
            $.post('<?php echo site_url($controller_name . "/set_email_receipt"); ?>', {
                email_receipt: $('#email_receipt').is(':checked') ? '1' : '0'
            });
        });

        $("#finish_sale_button").click(function() {
            $('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/complete_receipt"); ?>');
            $('#buttons_form').submit();
        });

        $("#finish_invoice_quote_button").click(function() {
            $('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/complete"); ?>');
            $('#buttons_form').submit();
        });

        $("#suspend_sale_button").click(function() {
            $('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/suspend"); ?>');
            $('#buttons_form').submit();
        });

        $("#cancel_sale_button").click(function() {
            if (confirm('<?php echo $this->lang->line("sales_confirm_cancel_sale"); ?>')) {
                localStorage.clear();
                $('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/cancel"); ?>');
                $('#buttons_form').submit();
            }
        });

        $("#add_payment_button").click(function() {
            $('#add_payment_form').submit();
        });

        $("#payment_types").change(check_payment_type).ready(check_payment_type);

        $("#cart_contents input").keypress(function(event) {
            if (event.which == 13) {
                $(this).parents("tr").prevAll("form:first").submit();
            }
        });

        $("#amount_tendered").keypress(function(event) {
            if (event.which == 13) {
                $('#add_payment_form').submit();
            }
        });

        $("#finish_sale_button").keypress(function(event) {
            if (event.which == 13) {
                $('#finish_sale_form').submit();
            }
        });

        dialog_support.init("a.modal-dlg, button.modal-dlg");

        table_support.handle_submit = function(resource, response, stay_open) {
            if (response.success) {
                if (resource.match(/customers$/)) {
                    $("#customer").val(response.id);
                    $("#select_customer_form").submit();
                } else {
                    var $stock_location = $("select[name='stock_location']").val();
                    $("#item_location").val($stock_location);
                    $("#item").val(response.id);
                    if (stay_open) {
                        $("#add_item_form").ajaxSubmit();
                    } else {
                        //$("#add_item_form").submit();
                    }
                }
            }
        }

        $('[name="price"],[name="quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="transfer_price"]').focusout(function() {
            // $(this).parents("tr").prevAll("form:first").submit()
        });

    });

    function check_payment_type() {
        var cash_rounding = <?php echo json_encode($cash_rounding); ?>;

        if ($("#payment_types").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>") {
            $("#sale_total").html("<?php echo to_currency($total); ?>");
            $("#sale_amount_due").html("<?php echo to_currency($amount_due); ?>");
            $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
            $("#amount_tendered:enabled").val('').focus();
            $(".giftcard-input").attr('disabled', false);
            $(".non-giftcard-input").attr('disabled', true);
            $(".giftcard-input:enabled").val('').focus();
        } else if ($("#payment_types").val() == "<?php echo $this->lang->line('sales_cash'); ?>" && cash_rounding) {
            $("#sale_total").html("<?php echo to_currency($cash_total); ?>");
            $("#sale_amount_due").html("<?php echo to_currency($cash_amount_due); ?>");
            $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
            $("#amount_tendered:enabled").val('<?php echo to_currency_no_money($cash_amount_due); ?>');
            $(".giftcard-input").attr('disabled', true);
            $(".non-giftcard-input").attr('disabled', false);
        } else {
            $("#sale_total").html("<?php echo to_currency($non_cash_total); ?>");
            $("#sale_amount_due").html("<?php echo to_currency($non_cash_amount_due); ?>");
            $("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
            $("#amount_tendered:enabled").val('<?php echo to_currency_no_money($non_cash_amount_due); ?>');
            $(".giftcard-input").attr('disabled', true);
            $(".non-giftcard-input").attr('disabled', false);
        }
    }
</script>
<?php //$this->load->view("items/scripts/push_script"); ?>
<?php $this->load->view("partial/footer"); ?>
