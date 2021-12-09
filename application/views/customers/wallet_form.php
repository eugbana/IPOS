<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save_wallet/' . $person_info->person_id, array('id' => 'wallet_form', 'class' => 'form-horizontal')); ?>
<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#customer_basic_info"><?php echo $this->lang->line("customers_basic_information"); ?></a>
    </li>


</ul>

<div class="tab-content">
    <div class="tab-pane fade in active" id="customer_basic_info">
        <fieldset>


            <div class="form-group form-group-sm">
                <?php echo form_label('Full Name', 'fullname_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'fullname',
                                'id' => 'fullname',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => $person_info->first_name . ' ' . $person_info->last_name
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b></b></span>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Wallet Balance', 'wallet_balance_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'wallet_balance',
                                'id' => 'wallet_balance',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => number_format($person_info->wallet)
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b>₦</b></span>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Credit Limit', 'credit_limit_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'credit_limit',
                                'id' => 'credit_limit',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => number_format($person_info->credit_limit)
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b>₦</b></span>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Type', 'type_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <?php echo form_input(
                        array(
                            'name' => 'type',
                            'id' => 'type',
                            'readonly' => 'readonly',
                            'class' => 'form-control input-sm',
                            'value' => $person_info->staff ? 'Staff' : 'Customer'
                        )
                    ); ?>

                </div>
            </div>

            <?php
            if ($person_info->staff) {
            ?>
                <div class="form-group form-group-sm">
                    <?php echo form_label('This Month Credit Purchase', 'credit_label', array('class' => 'control-label col-xs-3')); ?>
                    <div class='col-xs-8'>
                        <?php echo form_input(
                            array(
                                'name' => 'credit',
                                'id' => 'credit',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => $already_used_credit
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b>₦</b></span>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="form-group form-group-sm">
                <?php echo form_label('Update type', 'tType', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <select name="update_type" id="update-type" required class="form-control input-sm">
                            <option value="0">Wallet Funding</option>
                            <option value="-1">Reconciliation Credit</option>
                            <option value="-2">Reconciliation Debit</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Amount', 'deposit', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'deposit',
                                'id' => 'deposit',
                                'type' => 'numeric',
                                'class' => 'form-control input-sm',
                                'placeholder' => 'Enter the amount here'
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b>₦</b></span>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <?php echo form_label('Narration', 'narration-label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <textarea name="narration" id="narration" placeholder="Enter narration..." required class="form-control input-sm"></textarea>
                </div>
            </div>

        </fieldset>
        <fieldset>
            <h5>Print Wallet Transaction History or Ledger</h5>
            <div class="clearfix">
                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="date">Start Date</label>
                        <input type="date" id="start_date" name='start_date' class="form-control input-sm">
                    </div>
                    <div class="col-sm-6">
                        <label for="date">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <div class="right">
                        <button class="btn btn-sm text-sm btn-primary" id="print_btn" type="button"><i class="glyphicon glyphicon-print"></i> View History</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="right">
                        <button class="btn btn-sm text-sm btn-primary" id="ledger_btn" type="button"><i class="glyphicon glyphicon-print"></i> View Ledger</button>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>




</div>


<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {
        var customer_id = <?php echo $person_info->person_id; ?>;
        $("#wallet_form").on('submit', function(e) {
            e.preventDefault();

            var deposit = $("#deposit").val().trim();
            var pattern = /^[0-9]{1,}$/;
            if (!pattern.test(deposit) || parseFloat(deposit) < 100) {
                window.alert("Deposit amount must be a valid value bigger than N100");
                return;
            }
            $.post("customers/save_wallet", {
                "deposit": deposit,
                "update_type": $('#update-type').val(),
                "narration": $('#narration').val(),
                "customer_id": customer_id

            }, function(data, status) {
                if (data.success) {
                    $.notify({
                        title: "Customer Wallet Update",
                        message: data.message,
                        type: "success"
                    });
                    dialog_support.hide();
                    table_support.refresh();
                } else {
                    $.notify({
                        title: "Customer Wallet Update",
                        message: data.message,
                        type: "danger"
                    });
                }
            }, "json");
        });

        $("#print_btn").on('click', function() {
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();
            if (start_date == '' || end_date == '') {
                window.alert("Select start and end date");
            } else {

                window.location = ["<?php echo site_url('customers/print_customer_wallet_history');  ?>", customer_id, $("#start_date").val(), $("#end_date").val()].join("/");
            }
            //$("#item_form").submit();
        });
        $("#ledger_btn").on('click', function() {
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();
            if (start_date == '' || end_date == '') {
                window.alert("Select start and end date");
            } else {
                window.location = ["<?php echo site_url('customers/ledger');  ?>", customer_id, $("#start_date").val(), $("#end_date").val()].join("/");
            }
            //$("#item_form").submit();
        });
    });
</script>
