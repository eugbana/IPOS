<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<?php
$granted_perm = $u_mod_grants["reports"];
$granted_report_permissions=[];
$granted = false;
if(isset($granted_perm)&& count($granted_perm)>0){
    $granted = true;
    foreach ($granted_perm as $perm){
        $granted_report_permissions[] = $perm["permission_id"];
    }
//    var_dump($granted_report_permissions);
}
if (isset($error)) {
	echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
}
?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<div class="row">
            <?php
//            var_dump($granted_report_permissions);
            if(!$granted){
                ?>
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Unauthorized</h3>
                        </div>
                        <div class="list-group justify-content-center">

                            <p>You are not authorized to view this page</p>
                        </div>
                    </div>
                </div>
                <?php
            }
            if(in_array('reports_receivings',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Receivings</h3>
                        </div>
                        <div class="list-group">
                            <?php
                            $person_id = $this->session->userdata('person_id');
                            show_report_if_allowed('detailed', 'receivings', $person_id);
                            ?>
                            <a class="list-group-item" href="<?php echo site_url('reports/detailed_product_receivings'); ?>">Item Specifc Receivings Reports</a>
                        </div>
                    </div>
                </div>
                <?php
            }
            if(in_array('reports_sales',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Sales</h3>
                        </div>
                        <div class="list-group">

                            <a class="list-group-item" href="<?php echo site_url('reports/detailed_sales'); ?>">Sales Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/detailed_product_sales'); ?>">Item Specific Sales Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/irecharge_sales'); ?>">iRecharge</a>
                            <!-- <a class="list-group-item" href="<?php echo site_url('reports/detailed_product_sales'); ?>">Sales Profit Reports</a> -->
                        </div>
                    </div>
                </div>
                <?php
            }
            if(in_array('reports_products',$granted_report_permissions) || in_array('reports_items',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Product</h3>
                        </div>
                        <div class="list-group">
                            <a class="list-group-item" href="<?php echo site_url('reports/all_items'); ?>">Items Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/item_inventory_report'); ?>">Item Specific Inventory Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/price_list'); ?>">Price List Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/stock_value'); ?>">Stock Value Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/vat_tax'); ?>">VAT / Tax Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/out_of_stock'); ?>">Out of Stock / Minimum Stock Level Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('items/stock_report'); ?>">Opening / Closing Stock</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            if(in_array('reports_markup',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Markup</h3>
                        </div>
                        <div class="list-group">
                            <a class="list-group-item" href="<?php echo site_url('reports/markup_report'); ?>">Markup Reports</a>
                            <a class="list-group-item" href="<?php echo site_url('reports/sales_markup_report'); ?>">Sales Markup Reports</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            if(in_array('reports_transfers',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Transfers</h3>
                        </div>
                        <div class="list-group">

                            <a class="list-group-item" href="<?php echo site_url('reports/detailed_transfers'); ?>">Transfers Reports</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            $expiryItemsCount = $this->Specific_employee->getExpiryItemsCount();
            ?>

			<style>
				.dot {
					height: 10px;
					width: 10px;
					background-color: #ff0000;
					border-radius: 50%;
					display: inline-block;
				}
			</style>

            <?php
            if(in_array('reports_expiry',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Item Expiry Reports</h3>
                        </div>
                        <div class="list-group">
                            <a class="list-group-item" href="<?php echo site_url('reports/expiry_items'); ?>">Item Expiry Reports <?php echo $expiryItemsCount > 0 ? '<span class="dot"></span>' : '';  ?> </a>
                            <a class="list-group-item" href="<?php echo site_url('reports/expired_items'); ?>">Expired Item Reports</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            if(in_array('reports_expenses',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Expenses</h3>
                        </div>
                        <div class="list-group">

                            <a class="list-group-item" href="<?php echo site_url('reports/date_input_expenses'); ?>">Expense Account Reports</a>
                        </div>
                    </div>
                </div>
            <?php
            }
            if(in_array('reports_misc',$granted_report_permissions)){
                ?>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span>Misc</h3>
                        </div>
                        <div class="list-group">

                            <!-- <a class="list-group-item" href="<?php echo site_url('reports/date_input_audit'); ?>">Audit trail Reports</a> -->
                            <a class="list-group-item" href="<?php echo site_url('reports/date_input_credit'); ?>">Credit Customers Reports</a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>
