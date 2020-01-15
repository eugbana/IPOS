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
                <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">Inventory Intake - <?php echo $meta->reference; ?></h4>
                <p class="">Items received by -<b> <?php echo $meta->first_name . ' ' . $meta->last_name; ?></b></p>
                <p class="">Time stamp - <b><?php echo date_formatter($meta->receiving_time); ?></b></p>
                <!-- <p class="">Payment method - <b><?php echo $meta->payment_type; ?></b></p> -->
                <p class="">Receiving Branch - <b><?php echo (count($items) > 0) ?  $items[0]->location_name : ''; ?></b></p>
                <div class="table-responsive">
                    <p>
                        <span class="pull-right"><a type="button" class="btn btn-success btn-sm" href="<?php echo site_url('receivings/reprint/' . $meta->receiving_id); ?>"><i class="glyphicon glyphicon-print"></i> Print</a></span>
                    </p>
                    <table class="table table-default table-hover table-stripped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"> S/N</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Quantity</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost </th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Price </th>
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Unit Price</th> -->
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Discount Percent</th> -->

                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Receiving Quantity</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $i => $val) : ?>
                                <tr>
                                    <td><?php echo ($i += 1); ?></td>
                                    <td><?php echo $val->name; ?></td>
                                    <td><?php echo $val->quantity_purchased; ?></td>
                                    <td><?php echo to_currency($val->item_cost_price); ?></td>
                                    <td><?php echo to_currency($val->item_unit_price); ?></td>
                                    <!-- <td><?php //echo $val->discount_percent; 
                                                    ?></td> -->

                                    <td><?php echo $val->receiving_quantity; ?></td>
                                    <td><?php echo to_currency($val->receiving_quantity * $val->item_cost_price); ?></td>
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