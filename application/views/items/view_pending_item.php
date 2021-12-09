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
            <span class="pull-right"><a type="button" class="btn btn-success btn-md text-center" href="<?php echo site_url('items/accept_item/' . $items['id']); ?>"><i class="glyphicon glyphicon-print"></i> Accept Item </a></span>
                <br/>
                <br/>
                <!-- <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;"> ID - <?php echo $items['id']; ?></h4> -->
                <h4 class="font-weight-bold " style="font-weight:bold;text-transform:uppercase;">ITEM NAME - <?php echo $items['name']; ?></h4>
                <p class="">Item Number -<b> <?php echo $items['item_number']; ?></b></p>
                <p class="">Category -<b> <?php echo $items['category']; ?></b></p>
                <p class="">Cost Price -<b> <?php echo to_currency($items['cost_price']); ?></b></p>
                <p class="">Unit Price -<b> <?php echo to_currency($items['unit_price']); ?></b></p>
                <p class="">is VATable? -<b> <?php echo $items['apply_vat']; ?></b></p>
                <p class="">Sale Markup -<b> <?php echo  $items['unit_price_markup']; ?></b></p>
                <p class="">Created at - <b><?php echo date_formatter($items['created_at']); ?></b></p>
                <!-- <p class="">Status - <b><?php echo $items['status']; ?></b></p> -->
                <!-- <p class="">Invoice NO.: <b><?php echo $meta->reference; ?></b></p> -->
                <!-- <p class="">Receiving Branch - <b><?php echo (count($items) > 0) ?  $items[0]->location_name : ''; ?></b></p> -->
                <div class="table-responsive">
                    <p>
                            &nbsp;
                            &nbsp;
                            &nbsp;
                        <!-- <span class="pull-right"><a type="button" class="btn btn-danger btn-sm" href="<?php echo site_url('items/accept_transfer/' . $items['id']); ?>"><i class="glyphicon glyphicon-plus"></i> Accepts</a></span> -->
                    </p>

                    <!-- <table class="table table-default table-hover table-stripped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;"> S/N</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item Number</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Item Name</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Category</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Cost Price </th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Unit Price</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">VAT?</th>
                                <th style="color: #495057; background-color: #e9ecef;border-color: #dee2e6;">Sale Markup</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                <tr>
                                    <td><?php echo ($i += 1); ?></td>
                                    <td><?php echo $val['item_number']; ?></td>
                                    <td><?php echo $val['name']; ?></td>
                                    <td><?php echo $val['category']; ?></td>
                                    <td><?php echo $val['cost_price']; ?></td>
                                    <td><?php echo $val['unit_price']; ?></td>
                                    <td><?php echo $val['apply_vat']; ?></td>
                                    <td><?php echo $val['unit_price_markup']; ?></td>
                                </tr>
                        </tbody>
                    </table> -->
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