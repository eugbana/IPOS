<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save_wallet/' . $company_info->company_id, array('id' => 'wallet_form', 'class' => 'form-horizontal')); ?>
<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#customer_basic_info"><?php echo $this->lang->line("customers_basic_information"); ?></a>
    </li>


</ul>

<div class="tab-content">
    <div class="tab-pane fade in active" id="customer_basic_info">
        <fieldset>


            <div class="form-group form-group-sm">
                <?php echo form_label('Company Name', 'fullname_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'company_name',
                                'id' => 'company_name',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => $company_info->company_name
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b></b></span>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?php echo form_label('Contact Phone', 'fullname_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'phone',
                                'id' => 'phone',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => $company_info->contact_phone
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b></b></span>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?php echo form_label('Contact Email', 'fullname_label', array('class' => 'control-label col-xs-3')); ?>
                <div class='col-xs-8'>
                    <div class="input-group input-group-sm">
                        <?php echo form_input(
                            array(
                                'name' => 'email',
                                'id' => 'email',
                                'readonly' => 'readonly',
                                'class' => 'form-control input-sm',
                                'value' => $company_info->contact_email
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
                                'value' => number_format($company_info->wallet)
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
                                'value' => number_format($company_info->credit_limit)
                            )
                        ); ?>
                        <span class="input-group-addon input-sm"><b>₦</b></span>
                    </div>
                </div>
            </div>
            
            <div class="form-group form-group-sm">
                <?php echo form_label('Deposit Amount', 'deposit', array('class' => 'control-label col-xs-3')); ?>
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







        </fieldset>
        <!-- <fieldset>
            <h5>Print Wallet Transaction History</h5>
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
                        <button class="btn btn-sm text-sm btn-primary" id="print_btn" type="button"><i class="glyphicon glyphicon-print"></i> Print</button>
                    </div>
                </div>
            </div>
        </fieldset> -->
    </div>




</div>


<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {
        $("#wallet_form").on('submit', function(e) {
            e.preventDefault();

            var deposit = $("#deposit").val().trim();
            var pattern = /^[0-9]{1,}$/;
            if (!pattern.test(deposit) || parseFloat(deposit) < 100) {
                window.alert("Deposit amount must be a valid value bigger than N100");
                return;
            }
            $.post("companies/save_wallet", {
                "deposit": $("#deposit").val().trim(),
                "company_id": <?php echo $company_info->company_id; ?>
            }, function(data, status) {
                if (data.success) {
                    $.notify({
                        title: "Company Wallet Update",
                        message: data.message,
                        type: "success"
                    });
                    dialog_support.hide();
                    table_support.refresh();
                } else {
                    $.notify({
                        title: "Company Wallet Update",
                        message: data.message,
                        type: "danger"
                    });
                }
            }, "json");
        });

        $("#print_btn").on('click', function() {
            var company_id = <?php echo $company_info->company_id; ?>;

            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();
            if (start_date == '' || end_date == '') {
                window.alert("Select start and end date");
            } else {

                window.location = ["<?php echo site_url('companies/print_customer_wallet_history');  ?>", customer_id, $("#start_date").val(), $("#end_date").val()].join("/");
            }
            //$("#item_form").submit();
        });
    });
</script>