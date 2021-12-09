<div class="alert alert-success alert-dismissible fade show"></div>
<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#buy_power_tab" title="Power unit purchase">Buy Power</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#get_meter_info_tab" title="Get meter info">Meter Info</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#distributors_tab" title="Available power distribution companies">All distributors</a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade in active" id="buy_power_tab">
        <div id="meter-token">

        </div>
        <?php
        echo form_open('',['data-url'=>'buyPower','class'=>'form modal-form','id'=>'vend-power'])?>
        <div class="form-group">
            <label class="col-form-label">Power Distributor
                <select class="form-control power-discos" name="disco" required>
                    <option value="">Select a distributor</option>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Meter No
                <input type="text" name="meter_no" class="form-control" required/>
            </label>
        </div>

        <div class="form-group">
            <label>
                Customer Phone
                <input type="text" name="phone" class="form-control" required/>
            </label>
        </div>
        <div class="form-group">
            <label>
                Customer Email
                <input type="text" name="email" class="form-control"/>
            </label>
        </div>
        <div class="form-group">
            <label>
                Amount
                <input type="text" name="amount" class="form-control" required/>
            </label>
        </div>
        <?php
        echo form_submit(array(
            'name' => 'submit_form',
            'id' => 'submit_form',
            'value'=>$this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm pull-right'));
        echo form_close();
        ?>
    </div>
    <div class="tab-pane" id="get_meter_info_tab">
        <div class="" style="display: none" id="meter-info-div">
            <div class="form-group">
                <label>
                    Owner
                    <input id="m-owner-name" class="form-control" readonly/>
                </label>

            </div>
            <div class="form-group">
                <label for="m-owner-address">
                    Address
                </label>
                <input id="m-owner-address"  class="form-control" readonly/>
            </div>
            <div class="form-group">
                <label>
                    Minimum Amount
                    <input id="m-owner-min-amount" class="form-control" readonly/>
                </label>
                <label>
                    Util
                    <input id="m-owner-util" class="form-control" readonly/>
                </label>
            </div>
        </div>
        <?php
            echo form_open('',['data-url'=>'getMeterInfo','id'=>'meter-info']);
            ?>
        <div class="form-group">
            <label class="col-form-label">Meter No
                <input type="text" name="meter_no" class="form-control" required/>
            </label>

        </div>
        <div class="form-group">
            <label class="col-form-label">Power Distributor
                <select class="form-control power-discos" name="disco" required>
                    <option value="">Select a distributor</option>
                </select>
            </label>
        </div>
        <?php echo form_submit(array(
            'name' => 'submit_form',
            'id' => 'submit_meter-form',
            'value'=>$this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm pull-right'));
            echo form_close();
        ?>
    </div>
    <div class="tab-pane" id="distributors_tab">
        <table id="distributors-table" class="table table-borderless">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Name</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    $(document).ready(function(){
       // $('#distributors-table').dataTable({});
       $.get("<?=base_url('irechargecontroller/getAllPowerDistributors')?>",function (data) {
           if(data.status === "00"){

               $.each(data.bundles,function (i,v) {
                    $('.power-discos').append($("<option />").val(v.code).text(v.description));
                    $('#distributors-table').append('<tr>' +
                        '<td>' +(i+1)+ '</td>'+
                        '<td>' +v.code +
                        '</td>'+
                        '</tr>');
               });
           }
           console.log('discos',Object.keys(data))
       },'json');
       $('form').off('submit').on('submit',function (e) {
            e.preventDefault();
            const ele = $(this);
            ele.find(':submit').attr('disabled','disabled');
           const info_div =$('#i-rech-message-div');
            $.post("<?=base_url('irechargecontroller')?>"+'/'+$(this).attr('data-url'),$(this).serializeArray(),function (data) {
                if(data !== null &&data.status ==='00'){
                    console.log('Form id:',ele.attr('id'));
                    switch (ele.attr('id')){
                        case 'meter-info':
                            $('#meter-info-div').css('display','block');
                            $('#m-owner-address').val(data.customer.address);
                            $('#m-owner-min-amount').val(data.customer.minimumAmount);
                            $('#m-owner-name').val(data.customer.name);
                            $('#m-owner-util').val(data.customer.util);
                            break;
                        case 'device-info':
                            $('#meter-info-div').css('display','block');
                            $('#m-owner-address').val(data.customer.address);
                            $('#m-owner-min-amount').val(data.customer.minimumAmount);
                            $('#m-owner-name').val(data.customer.name);
                            $('#m-owner-util').val(data.customer.util);
                            break;
                        case 'card-info-form':
                            $('#card-info-div').css('display','block');
                            $('#c-owner-number').val(data.customer_number);
                            $('#c-owner-name').val(data.customer);
                            break;
                        case 'vend-power':
                            info_div.empty().append(
                                '<div class="alert alert-success alert-dismissible show" role="alert">' +
                                '<strong>Request '+data.message+'</strong>' +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>'+
                                '</div>'
                            );
                            $('#meter-token').empty().append(
                                '<div class="form-group"><label>' +
                                'Token: <input type="text" class="form-control" readonly value="' +data.meter_token+
                                '" /><label></div>'
                            );
                            break;
                        default:
                            info_div.empty().append(
                                '<div class="alert alert-success alert-dismissible show" role="alert">' +
                                    '<strong>Request '+data.message+'</strong>' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                    '<span aria-hidden="true">&times;</span>' +
                                    '</button>'+
                                '</div>'
                            );
                    }
                    ele.get(0).reset();

                }else {
                    let message = "An error occurred during request";
                    if(data){
                        message = data.message;
                    }
                    $('#i-rech-message-div').empty().append(
                        '<div class="alert alert-danger alert-dismissible show" role="alert">' +
                        '<strong>Request failed: '+message+'</strong>' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>'+
                        '</div>'
                    )
                }
                ele.find(':submit').removeAttr('disabled');
                // console.log('meter info',data,$(this).attr('id'));
            },'json');
       });
    });
</script>
