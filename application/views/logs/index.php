<?php $this->load->view("partial/header"); ?>
    <script type="text/javascript">
        dialog_support.init("a.modal-dlg");
        $(document).ready(function(){
            $('#menuID').click(function() {
                $('#vendorsMenu').slideToggle('fast');
            });
        });
    </script>
    <div class="content-page">
        <!-- Start content -->
        <div class="content">
            <h3 class="text-center">Welcome! Click on any module to continue.</h3>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <h3 class="text-left" id="menuID" style="cursor: pointer"><i class="fa fa-navicon"></i> Menu</h3>
                    <div class="list-group" id="vendorsMenu" style="border: 1px solid #ccc;">
                        <a href="#" class="list-group-item list-group-item-action active">Employees Sales History</a>
                        <a href="#" class="list-group-item list-group-item-action">Sign-up Requests</label> </a>
                        <a href="#" class="list-group-item list-group-item-action">Active Accounts</label> </a>
                        <a href="#" class="list-group-item list-group-item-action">Send Message</a>
                        <a href="#" class="list-group-item list-group-item-action">Log</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <select name="employee" id="employee" class="form-control">
                        <?php foreach($employees as $e): ?>
                            <option value="<?php $e->person_id; ?>"><?php echo $e->username; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php $this->load->view("partial/footer"); ?>