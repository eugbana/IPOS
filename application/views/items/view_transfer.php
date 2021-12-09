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
                <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">TRANSFER ID - <?php echo $items['id']; ?></h4>
                <p class="">From Branch -<b> <?php echo $items['to_branch']; ?></b></p>
                <p class="">Time stamp - <b><?php echo date_formatter($items['created_at']); ?></b></p>
                <p class="">Status - <b><?php echo $items['status']; ?></b></p>
                <!-- <p class="">Invoice NO.: <b><?php echo $meta->reference; ?></b></p> -->
                <!-- <p class="">Receiving Branch - <b><?php echo (count($items) > 0) ?  $items[0]->location_name : ''; ?></b></p> -->
                <div class="table-responsive">
                    <p>
                        <span class="pull-right"><a type="button" class="btn btn-success btn-sm" href="<?php echo site_url('items/accept_transfer/' . $items['id']); ?>"><i class="glyphicon glyphicon-print"></i> Accept Transfer </a></span>
                            &nbsp;
                            &nbsp;
                            &nbsp;
                        <!-- <span class="pull-right"><a type="button" class="btn btn-danger btn-sm" href="<?php echo site_url('items/accept_transfer/' . $items['id']); ?>"><i class="glyphicon glyphicon-plus"></i> Accepts</a></span> -->
                    </p>

                    <table class="table table-default table-hover table-stripped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"> S/N</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item Number</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item Name</th>

                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Price </th> -->
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Requested Qty </th>
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Unit Price</th> -->
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Discount Percent</th> -->

                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Accepted Qty</th>
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Quantity Received</th> -->
                                <!-- <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Total</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items['items'] as $i => $val) : 
                                $cur_item_info = $this->Item->get_item_info_by_number($val['item_number']);
                                ?>
                                <tr>
                                    <td><?php echo ($i += 1); ?></td>
                                    <td><?php echo $val['item_number']; ?></td>
                                    <td><?php echo $cur_item_info->name; ?></td>
                                    <td><?php echo to_quantity_decimals($val['requested_quantity']); ?></td>
                                    <td><?php echo to_quantity_decimals($val['accepted_quantity']); ?></td>

                                    <!-- <td><?php echo to_currency($val->item_unit_price); ?></td> -->
                                    <!-- <td><?php echo $val->quantity_purchased; ?></td> -->
                                    <!-- <td><?php echo to_currency($val->quantity_purchased * $val->item_cost_price); ?></td> -->
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