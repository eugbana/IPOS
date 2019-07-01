<?php $this->load->view("partial/header"); ?>
    <div class="content-page">
        <!-- Start content -->
        <div class="content">
            <?php
                if (isset($error)) {
                    echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
                }

                if (!empty($warning)) {
                    echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
                }

                if (isset($success)) {
                    echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
                }
            ?>  
            <div class="row justify-content-center">
                <div class="col-md-10 col-md-offset-1">
                    <h4 class="">Inventory Intake - <?php echo $meta->reference; ?></h4>
                    <p class="text-muted">Items received by <?php echo $meta->first_name . ' ' . $meta->last_name; ?></p>
                    <p class="text-muted">Time stamp - <?php echo $meta->receiving_time; ?></p>
                    <p class="text-muted">Payment method - <?php echo $meta->payment_type; ?></p>
                    <div class="table-responsive">
                        <p>
                            <span class="pull-right"><a type="button" class="btn btn-success btn-sm" href="/receivings/reprint/<?php echo $meta->receiving_id; ?>">Print</a></span>
                        </p>
                        <table class="table table-default table-hover table-stripped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Quantity Purchased</th>
                                    <th>Cost Price</th>
                                    <th>Unit Price</th>
                                    <th>Discount Percent</th>
                                    <th>Location</th>
                                    <th>Receiving Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $i => $val): ?>
                                    <tr>
                                        <td><?php echo ($i+=1); ?></td>
                                        <td><?php echo $val->name; ?></td>
                                        <td><?php echo $val->quantity_purchased; ?></td>
                                        <td><?php echo $val->item_cost_price; ?></td>
                                        <td><?php echo $val->item_unit_price; ?></td>
                                        <td><?php echo $val->discount_percent; ?></td>
                                        <td><?php echo $val->location_name; ?></td>
                                        <td><?php echo $val->receiving_quantity; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

<?php $this->load->view("partial/footer"); ?>