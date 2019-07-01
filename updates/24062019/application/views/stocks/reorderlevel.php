<?php $this->load->view("partial/header"); ?>
    <div class="row">
        <div style="height:100px;"></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
                if (isset($error))
                {
                    echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
                }

                if (!empty($warning))
                {
                    echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
                }

                if (isset($success))
                {
                    echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
                }
            ?>
        </div>
    </div>
    <div id="page-wrap" style="margin:0 auto;margin-left:25%;">
        <div id="header">
            <h4 class="text-center no-print">Items at reorder level</h4>
        </div>
        <div id="block1">
            <div class="justify-content-center" id="logo" style="margin:0 auto;width:100%;text-align:center">
                <?php if($this->Appconfig->get('company_logo') != '') { ?>
                    <img id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo">
                <?php } ?>
                <div>&nbsp</div>
                <?php if ($this->Appconfig->get('receipt_show_company_name')) { ?>
                    <div id="company_name"><?php #echo $this->config->item('company'); ?></div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-success btn-sm no-print" onclick="window.print();">Print</button>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover table-stripped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Grammage</th>
                                <th>Formulation</th>
                                <th>Manufacturer</th>
                                <th>Available quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if (count($items) > 0) {
                                    foreach($items as $i) {
                                        echo "<tr>";
                                        echo "<td>" . $i->name . "</td>";
                                        echo "<td>" . $i->grammage . "</td>";
                                        echo "<td>Formulation</td>";
                                        echo "<td>" . $i->company_name . "</td>";
                                        echo "<td>" . $i->quantity . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td colspan='5'> No Out of stock items found</td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
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
