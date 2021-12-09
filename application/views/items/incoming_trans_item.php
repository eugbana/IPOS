<?php $this->load->view("partial/header"); ?>
<script type="text/javascript" src="dataTables/dataTables.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="dataTables/Buttons-1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="dataTables/JSZip-2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="dataTables/pdfmake-0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="dist/swal/cdnjs/sweetalert.min.js"></script>
<script>
    function clickMe(el){
        if($(el).attr('clicked') === undefined || $(el).attr('clicked') === null){
            var val = el.getAttribute('data-transref');
            var rej_rem = '';
            var urll = '';
            var paramData = {
                'reference':el.getAttribute('data-transref')
            }
            if($(el).hasClass('accept-transfers')){
                urll += 'acceptt';
            }else {
                urll += 'reject';
                rej_rem = $('#remark-'+val).val();
                if(rej_rem === ''|| rej_rem=== null || rej_rem===undefined){
                    swal({
                        title:"Oops..",
                        icon:'error',
                        text:'Remark cannot be empty'
                    });
                    console.log('val: ',val);
                    return false;
                }
                paramData['remarks'] = rej_rem;
            }
            el.setAttribute('disabled','disabled');
            $.post("<?=site_url('transfer')?>"+'/'+urll,paramData,function (data) {
                swal({
                    title:"All good",
                    icon:'success',
                    text:data.message,
                    button: "Good"
                },function () {
                    location.reload(true);
                });
            },'json').fail(function(error){
                // alert("An error occurred");
                swal({
                    title:"Oops..",
                    icon:'error',
                    text:'An error occurred'
                },function(){
                    $(el).removeAttr('disabled');
                    $(el).removeAttr('clicked');
                });
            });
        }
        return false;
    }
    function switchTo(elFrom,elTo){
        console.log('type: ',typeof elFrom)
        $('#'+elFrom).toggle();
        $('#'+elTo).toggle();
    }
    $(document).ready(function () {
        $('#table').DataTable();
    })
</script>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <?php
        //            var_dump($transfer);
        if (isset($error)) {
            echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
        }

        if (!empty($warning)) {
            echo "<div class='alert alert-dismissible alert-warning'>" . $warning . "</div>";
        }

        if (isset($success)) {
            echo "<div class='alert alert-dismissible alert-success'>" . $success . "</div>";
        }
        ?>

        <div class="row justify-content-center">
            <div class="row clearfix">
                <div class="pull-left">
                    <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">Reference - <?=$transfer->transfer_reference ?></h4>
                    <p class="">Items transferred by -<b> <?=$transfer->sent_by; ?></b></p>
                    <p class="">Time stamp - <b><?php echo date_formatter($transfer->date_transferred); ?></b></p>
                    <p class="">Receiving Branch - <b><?=$transfer->date_transferred ?></b></p>
                </div>
                <div class="pull-right">
                    <?php
                    if($transfer->status == 'pending'){
                        ?>
                        <div>
                            <div class="clearfix" id="<?=$transfer->transfer_reference?>">
                                <button class="btn btn-primary pull-left accept-transfers" onclick="clickMe(this)" id="accept-transfer" data-transref="<?=$transfer->transfer_reference?>">Accept</button>
                                <button class="btn btn-danger pull-right" onclick="switchTo('<?=$transfer->transfer_reference?>','reject-<?=$transfer->transfer_reference?>')" id="accept-transfer" data-action="<?=$transfer->transfer_reference?>">Reject</button>
                            </div>
                            <div class="clearfix" style="display: none" id="reject-<?=$transfer->transfer_reference?>">
                                <textarea name="remark" id="remark-<?=$transfer->transfer_reference?>" class="form-control" placeholder="Reason to reject"></textarea>
                                <button class="btn btn-primary pull-left" onclick="switchTo('reject-<?=$transfer->transfer_reference?>','<?=$transfer->transfer_reference?>')" data-action="<?=$transfer->transfer_reference?>">Cancel</button>
                                <button class="btn btn-danger pull-right" onclick="clickMe(this)" data-transref="<?=$transfer->transfer_reference?>">Continue</button>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <button class="btn btn-block" onclick="window.history.back()">Back</button>
                </div>
            </div>
            <br>
            <br>
            <div class="clearfix"></div>
            <div class="row">
                <div class="table-responsive" >
                    <table id="table" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Item Name</th>
                            <th>Item Number</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Cost Price</th>
                            <th>Retail Price</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(isset($transfer) && count($transfer->items) > 0){
                            $items = $transfer->items;
                            $sn = 1;
                            foreach ($items as $item){
                                ?>
                                <tr>
                                    <td><?=$sn?></td>
                                    <td><?=$item->item_name?></td>
                                    <td><?=$item->item_number?></td>
                                    <td><?=$item->item_category?></td>
                                    <td><?=$item->transferred_quantity?></td>
                                    <td><?=$item->cost_price?></td>
                                    <td><?=$item->retail_price?></td>
                                    <td><?=$item->total_retail_price?></td>
                                </tr>
                                <?php
                                $sn++;
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>


<?php $this->load->view("partial/footer"); ?>
