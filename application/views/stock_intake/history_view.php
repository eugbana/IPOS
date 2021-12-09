<?php $this->load->view("partial/header"); ?>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <?php
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
            <div class="col-md-10 col-md-offset-1">
                <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">Stock Intake: <?php echo $meta->title; ?></h4>
                <h5 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">Description: <?php echo $meta->description; ?></h5>
                <h5 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">STATUS: <?php echo ucfirst($meta->status); ?></h5>
                <p class="">Started By: <b> <?php echo $meta->first_name . ' ' . $meta->last_name; ?></b></p>
                <p class="">Time stamp: <b><?php echo date_formatter($meta->receiving_time); ?></b></p>
                <div class="table-responsive">
                    <p>
                    <?php
                        if($meta->status == 'in-progress'){ ?>
                             <span class="pull-left" style="margin-left: 10px" ><a type="button" class="btn btn-primary btn-sm" href="<?php echo site_url('stockintake'); ?>"><i class="glyphicon glyphicon-print"></i> JOIN Stock Taking</a></span>
                        <?php }
                    ?>

                    <?php
                        if($meta->status == 'in-progress'){ ?>
                             <span class="pull-right" style="margin-left: 10px" ><a type="button" class="btn btn-danger btn-sm" href="<?php echo site_url('stockintake/end_stock_intake/' . $meta->stock_id); ?>"><i class="glyphicon glyphicon-print"></i> END Stock Taking</a></span>
                        <?php }
                    ?>

                     <?php
                        if($meta->status == 'done'){ ?>

                    <?php echo form_open($controller_name . "/process_stock_intake" . $meta->stock_id, array('id' => 'buttons_form')); ?>
                        <div class="form-group" id="buttons_sale">
                            <div class='btn btn-primary btn-md text-center pull-left' id='process_stock'><span class="glyphicon glyphicon-remove">&nbsp</span> Make this Stock Intake your current Inventory </div>
                        </div>
					<?php echo form_close(); ?>


                        
                             <!-- <span class="pull-left" style="margin-left: 10px" ><a id="process" class="btn btn-primary btn-md text-center" href="<?php echo site_url('stockintake/process_stock_intak/' . $meta->stock_id); ?>"><i class="glyphicon glyphicon-print"></i> Make this Stock Intake your current Inventory</a></span> -->
                        <?php }
                    ?>
                        <span class="pull-right"><a type="button" class="btn btn-success btn-sm" href="<?php echo site_url('stockintake/reprint/' . $meta->stock_id); ?>"><i class="glyphicon glyphicon-print"></i> Print</a></span>
                    </p>
                    <table class="table table-default table-hover table-stripped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"> S/N</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Current Quantity</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Stock Intake Quantity</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Variance Quantity</th>

                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Price </th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Retail Price </th>

                                
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Current Stock Total</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Stock Intake Total</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Variance Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 

                            $current_qty= 0;
                            $stock_qty= 0;
                            $variance_qty = 0;

                            $current_amount = 0;
                            $stock_amount = 0;
                            $variance_amount = 0;
                            
                            foreach ($items as $i => $val){

                                $current_qty += $val->current_quantity;
                                $stock_qty += $val->quantity_purchased;
                                $diff_qty = ($val->current_quantity - $val->quantity_purchased);
                                $variance_qty += $diff_qty;

                                $current_amount += ($val->current_quantity * $val->item_cost_price);
                                $stock_amount += ($val->quantity_purchased * $val->item_cost_price);
                                $variance_amount += ($diff_qty * $val->item_cost_price);
                                
                                ?>
                                <tr>
                                    <td><?php echo ($i += 1); ?></td>
                                    <td><?php echo $val->name; ?></td>
                                    <td><?php echo $val->current_quantity; ?></td>
                                    <td><?php echo $val->quantity_purchased; ?></td>
                                    <td><?php echo $val->current_quantity - $val->quantity_purchased; ?></td>

                                    <td><?php echo to_currency($val->item_cost_price); ?></td>
                                    <td><?php echo to_currency($val->item_unit_price); ?></td>
                                   

                                    <td><?php echo to_currency($val->current_quantity * $val->item_cost_price); ?></td>
                                    <td><?php echo to_currency($val->quantity_purchased * $val->item_cost_price); ?></td>
                                    <td><?php echo to_currency($val->item_cost_price * ($val->current_quantity - $val->quantity_purchased)); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div id="report_summary">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr>
                                <td>Total Current Quantity</td>
                                <td><?php echo $current_qty; ?></td>
                            </tr>

                            <tr>
                                <td>Total Stock Quantity</td>
                                <td><?php echo $stock_qty; ?></td>
                            </tr>

                            <tr>
                                <td>Total Variance Quantity</td>
                                <td><?php echo $variance_qty; ?></td>
                            </tr>

                            <tr>
                                <td>Total Current Amount</td>
                                <td><?php echo to_currency($current_amount); ?></td>
                            </tr>

                            <tr>
                                <td>Total Stock Amount</td>
                                <td><?php echo to_currency($stock_amount); ?></td>
                            </tr>

                            <tr>
                                <td>Total Variance Amount</td>
                                <td><?php echo to_currency($variance_amount); ?></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>


                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css" media="print">
    html {
        margin: 0;
        padding: 0;
    }

    .no-print {
        display: none;
    }

    .table-responsive {
        width: 100%;
        margin: 0 auto;
        margin-left: -25%;
        margin-right: 10%;
    }

    .side-menu {
        position: fixed;
        z-index: 1;
    }

    #image {
        margin: 0 auto;
        margin-left: -42%;
    }
</style>

<script type="text/javascript">
$(document).ready(function() {

        $("#process_stock").click(function() {
			if (confirm('Are you sure you want to Make this Stock Intake your current Inventory?')) {
                console.log('work');
				$('#buttons_form').attr('action', '<?php echo site_url($controller_name . "/process_stock_intake/".  $meta->stock_id); ?>');
				$('#buttons_form').submit();
			}
		});
    });
</script>

<?php $this->load->view("partial/footer"); ?>