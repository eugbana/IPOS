
<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#buy_data_tab" title="Power unit purchase">Buy <?=isset($airtime)?"Airtime":"Data" ?></a>
    </li>
    <?php
    if(!isset($airtime)){
        ?>
        <li role="presentation">
            <a data-toggle="tab" href="#get_device_info_tab" title="Get meter info">Get Smile Device Info</a>
        </li>
        <li role="presentation">
            <a data-toggle="tab" href="#bundle_tab" title="Available data bundles based on networks">Data Bundles</a>
        </li>
    <?php
    }
    ?>
</ul>
<div class="tab-content">
    <div class="tab-pane fade in active" id="buy_data_tab">
        <?php
        echo form_open('',['data-url'=>'buyData','class'=>'form modal-form','id'=>'vend-data'])?>
        <div class="form-group">
            <label class="col-form-label">Mobile
                <input type="text" name="mobile" class="form-control" required/>
            </label>
        </div>
        <div class="form-group">
            <label class="col-form-label">Network
                <select class="form-control network-discos"  id="<?=isset($airtime)?'vend-airtime-sel':'vend-data-sel'?>" name="network" required>
                    <option value="">Select a network</option>
                    <option value="MTN">MTN</option>
                    <option value="Etisalat">Etisalat</option>
                    <option value="Airtel">Airtel</option>
                    <option value="Glo">GLO</option>
                </select>
            </label>
        </div>
        <?php
        if(!isset($airtime)){
            ?>
            <input type="hidden" name="is_data" value="1"/>
            <div class="form-group">
                <label class="col-form-label">Bundle
                    <select class="form-control network-bundles" name="code" required>
                        <option value="">Select a bundle</option>
                    </select>
                </label>
            </div>
            <div class="form-group">
                <label>
                    Customer Email
                    <input type="text" name="email" class="form-control" required/>
                </label>
            </div>
        <?php
        }else{
            ?>
            <div class="form-group">
                <label>
                    Amount
                    <input type="text" name="amount" class="form-control" required/>
                </label>
            </div>
        <?php
        }
        ?>
        <?php
        echo form_submit(array(
            'name' => 'submit_form',
            'id' => 'submit_form',
            'value'=>$this->lang->line('common_submit'),
            'class' => 'btn btn-primary btn-sm pull-right'));
        echo form_close();
        ?>
    </div>
    <?php
    if(!isset($airtime)){
        ?>
        <div class="tab-pane" id="get_device_info_tab">
            <div class="" style="display: none" id="smile-info-div">
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
            echo form_open('',['data-url'=>'getSmileDeviceInfo','id'=>'device-info']);
            ?>
            <div class="form-group">
                <label class="col-form-label">Device ID
                    <input type="text" name="device" class="form-control" required/>
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
        <div class="tab-pane" id="bundle_tab">
            <div class="form-group">
                <label class="col-form-label">Network
                    <select class="form-control" id="net-bundle" name="network" required>
                        <option value="">Select a network provider</option>
                        <option value="MTN">MTN</option>
                        <option value="Etisalat">Etisalat</option>
                        <option value="Airtel">Airtel</option>
                        <option value="Glo">GLO</option>
                    </select>
                </label>
            </div>
            <table id="bundles-table" class="table table-borderless">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Validity Period</th>
                    <th>Price</th>
                </tr>
                </thead>
            </table>
        </div>
    <?php
    }
    ?>
</div>

<script>
    $(document).ready(function(){
        // $('#distributors-table').dataTable({});
        <?php
            if(!isset($airtime)){
                ?>
        $('#vend-data-sel,#net-bundle').on('change',function () {
            const c_ele = $(this);
            if(c_ele.val() !== '' || c_ele.val() !== null){
                const n_ele = $('.network-bundles');
                n_ele.empty().prepend('<option>Loading...</option>');
                $.post("<?=base_url('irechargecontroller/getAvailableDataBundles')?>",{"network":c_ele.val()},function (data) {
                    n_ele.empty()
                    if(data.status === "00"){
                        if(data.bundles.length > 0){
                            if(c_ele.attr('id') === 'net-bundle'){
                                $.each(data.bundles,function (i,v) {
                                    $('#bundles-table').append('<tr>' +
                                        '<td>' +(i+1)+ '</td>'+
                                        '<td>' +v.title +
                                        '</td>'+
                                        '<td>' +v.validity +
                                        '</td>'+
                                        '<td>' +v.price +
                                        '</td>'+
                                        '</tr>');
                                });
                            }else{
                                $.each(data.bundles,function (i,v) {
                                    n_ele.append($("<option />").val(v.code).text(v.title));
                                });
                            }

                        }else{
                            if(c_ele.attr('id') === 'net-bundle'){
                                n_ele.append('<option value="">No bundle available</option>');
                            }

                        }
                    }
                    // console.log('discos',Object.keys(data))
                },'json');
            }
        });

        <?php
            }
        ?>
        //$('form').off('submit').on('submit',function (e) {
        //    e.preventDefault();
        //    const ele = $(this);
        //    $.post("<?//=base_url('irechargecontroller')?>//"+'/'+$(this).attr('data-url'),$(this).serializeArray(),function (data) {
        //        if(data.status ==='00'){

        //            // if(ele.attr('id')=== 'vend-power'){
        //            //     console.log(data);
        //            // }
        //        }
        //        // console.log('meter info',data,$(this).attr('id'));
        //    },'json');
        //});
    });
</script>
