<?php
$this->load->library('algo_challenge');
?>
<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#tv_bouquet_tab" title="Power unit purchase">Get All TVs</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#buy_tv_tab" title="Cable Tv purchase">Subscribe Tv </a>
    </li>
<!--    <li role="presentation">-->
<!--        <a data-toggle="tab" href="#get_card_info_tab" title="Get smart card info">Card Info</a>-->
<!--    </li>-->
</ul>
<div class="tab-content">
    <div class="tab-pane" id="buy_tv_tab">
        <?php
        echo form_open('',['data-url'=>'buyTv','class'=>'form modal-form','id'=>'vend-tv'])?>
        <div class="form-group">
            <label class="col-form-label">Card No
                <input type="text" name="card" class="form-control" required/>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Cable Provider
                <select class="form-control" id="buy-tv-disco" name="tv" required>
                    <option value="">Select a cable</option>
                    <option value="StarTimes">StarTimes</option>
                    <option value="GOTV">GOTV</option>
                    <option value="DSTV">DSTV</option>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Service Code
                <select class="form-control" id="buy-tv-code" name="code" required>
                    <option value="">Select a plan</option>
                </select>
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
                <input type="text" name="email" class="form-control" required/>
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
    <div class="tab-pane" id="algo_tab">
        <?php
        Algo_challenge::writeOneToHundredWithoutNumbers();
        ?>
    </div>
    <div class="tab-pane" id="get_card_info_tab">
        <?php
        echo form_open('',['data-url'=>'getSmartCardInfo','id'=>'card-info-form']);
        ?>
        <div class="form-group">
            <label class="col-form-label">Card No
                <input type="text" name="card" class="form-control" required/>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Cable Providers
                <select class="form-control" id="card-tv-disc" name="tv" required>
                    <option value="">Select a cable</option>
                    <option value="StarTimes">StarTimes</option>
                    <option value="GOTV">GOTV</option>
                    <option value="DSTV">DSTV</option>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Service Code
                <select class="form-control" id="card-tv-code" name="code" required>
                    <option value="">Select a plan</option>
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
        <div class="" style="display: none" id="card-info-div">
            <div class="form-group">
                <label>
                    Owner
                    <input id="c-owner-name" class="form-control" readonly/>
                </label>
            </div>
            <div class="form-group">
                <label for="m-owner-address">
                    Account Number
                </label>
                <input id="c-owner-number"  class="form-control" readonly/>
            </div>
        </div>
    </div>
    <div class="tab-pane fade in active" id="tv_bouquet_tab">
        <div class="form-group">
            <label class="col-form-label">Cable Providers
                <select class="form-control" id="all-tv-disc" name="tv" required>
                    <option value="">Select a cable</option>
                    <option value="StarTimes">StarTimes</option>
                    <option value="GOTV">GOTV</option>
                    <option value="DSTV">DSTV</option>
                </select>
            </label>
        </div>
        <table id="cable-table" class="table table-borderless">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Name</th>
                <th>Price</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    $(document).ready(function(){
        // $('#distributors-table').dataTable({});
        $('#all-tv-disc').on('change',function () {
            if($(this).val()!==''||$(this).val()!==null){
                $.post("<?=base_url('irechargecontroller/getAvailableTv')?>",{"tv":$(this).val()},function (data) {
                    if(data.status === "00"){
                        $('#cable-table').empty();
                        let cnt = 0;
                        $.each(data.bundles,function (i,v) {
                            // $('.power-discos').append($("<option />").val(v.code).text(v.description));
                            if(v.title){
                                $('#cable-table').append('<tr>' +
                                    '<td>' +(cnt+1)+ '</td>'+
                                    '<td>' +v.title +
                                    '</td>'+
                                    '<td>' +v.price +
                                    '</td>'+
                                    '</tr>');
                                cnt++;
                            }
                        });
                    }
                    console.log('discos',Object.keys(data))
                },'json');
            }
        });
        $('#card-tv-disc').on('change',function () {
            if($(this).val()!==''||$(this).val()!==null){
                $('#card-tv-code').empty().append('<option value="">Loading...</option>');
                $.post("<?=base_url('irechargecontroller/getAvailableTv')?>",{"tv":$(this).val()},function (data) {
                    if(data.status === "00"){
                        $('#card-tv-code').empty()
                        let cnt = 0;
                        $.each(data.bundles,function (i,v) {
                            if(v.title){
                                $('#card-tv-code').append($("<option />").val(v.code).text(v.title+'| #'+v.price));
                                cnt++;
                            }
                        });
                    }
                    console.log('discos',Object.keys(data))
                },'json');
            }
        });
        $('#buy-tv-disco').on('change',function () {
            if($(this).val()!==''||$(this).val()!==null){
                $('#buy-tv-code').empty().append('<option value="">Loading...</option>');
                $.post("<?=base_url('irechargecontroller/getAvailableTv')?>",{"tv":$(this).val()},function (data) {
                    if(data.status === "00"){
                        $('#buy-tv-code').empty()
                        let cnt = 0;
                        $.each(data.bundles,function (i,v) {
                            if(v.title){
                                $('#buy-tv-code').append($("<option />").val(v.code).text(v.title+'| #'+v.price));
                                cnt++;
                            }
                        });
                    }
                    console.log('discos',Object.keys(data))
                },'json');
            }
        });
        //$('form').off('submit').on('submit',function (e) {
        //    e.preventDefault();
        //    const ele = $(this);
        //    console.log("Hello",ele.attr('id'));
        //    $.post("<?//=base_url('irechargecontroller')?>//"+'/'+$(this).attr('data-url'),$(this).serializeArray(),function (data) {
        //        if(data.status ==='00'){
        //
        //            if(ele.attr('id') === 'card-info-form' ){
        //                // console.log('id',ele.attr('id'))
        //                console.log("Hello");
        //                $('#card-info-div').css('display','block');
        //                $('#c-owner-number').val(data.customer_number);
        //                $('#c-owner-name').val(data.customer);
        //            }
        //        }
        //        // console.log('meter info',data,$(this).attr('id'));
        //    },'json');
        //});
    });
</script>
