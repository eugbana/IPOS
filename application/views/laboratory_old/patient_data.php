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
                    <h4 class="">Patient Info</h4>
                    <!-- <p class="text-muted">Items received by <?php echo $meta->first_name . ' ' . $meta->last_name; ?></p>
                    <p class="text-muted">Time stamp - <?php echo $meta->sale_time; ?></p> -->
                    <div class="table-responsive">
                        <!-- <p>
                            <span class="pull-right"><a type="button" class="btn btn-success btn-sm" href="/sales/sales_history_reprint/<?php echo $meta->sale_id; ?>">Print</a></span>
                        </p> -->
                        <table class="table table-default table-hover table-stripped table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Person ID</td>
                                    <td><?php echo $patient->person_id; ?></td>
                                </tr>
                                <tr>
                                    <td>Full Name</td>
                                    <td><?php echo $patient->first_name . ' ' . $patient->last_name; ?></td>
                                </tr>
                                <tr>
                                    <td>Phone Number</td>
                                    <td><?php echo $patient->phone_number ? $patient->phone_number : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td><?php echo $patient->gender == '0' ? 'Male' : 'Female'; ?></td>
                                </tr>
                                <tr>
                                    <td>E-mail Address</td>
                                    <td><?php echo $patient->email ? $patient->email : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td><?php echo $patient->address_1 ? $patient->address_1 : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><?php echo $patient->city ? $patient->city : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>State</td>
                                    <td><?php echo $patient->state ? $patient->state : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td><?php echo $patient->country ? $patient->country : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>Date of Birth</td>
                                    <td><?php echo $patient->date_of_birth ? $patient->date_of_birth : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>Age</td>
                                    <td><?php echo $patient->age ? $patient->age . 'Yrs' : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td>POS ID's</td>
                                    <td>
                                        <?php if(count($pos_id) > 0): ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <?php foreach($pos_id as $p): ?>
                                                        <div class="col-md-3">
                                                            <div class="card">
                                                                <div class="card-content">
                                                                    <a href="<?php echo site_url('laboratory/result_info_profile/' . $p->sale_id); ?>"><?php echo 'POS' . $p->sale_id; ?></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            Customer has no POS ID's.
                                        <?php endif; ?>
                                    </td>
                                </tr>
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