<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <h3 class="text-center"><?php echo 'Welcome! Click on any module to continue'; ?></h3>

        <div class="row">
             <!-- end col -->
            <div class="col-md-6 col-sm-6 col-lg-3">
               <div class="row">
                   <?php
                   //                var_dump($u_mod_grants['config']);
                   $granted_perm = $u_mod_grants['config'];

                   $granted_permissions=[];
                   if(isset($granted_perm)&& count($granted_perm)>0){
//                    var_dump($granted_perm);
                       foreach ($granted_perm as $perm){
                           $granted_permissions[] = $perm["permission_id"];
                       }
                   }
                   // print_r($granted_perm);
//                   echo in_array("config_backup_database",$granted_permissions);
                   // var_dump($granted_permissions);
                   if(in_array('config_database_backup',$granted_permissions)){
                       echo form_label($this->lang->line('config_backup_database'),
                           'config_backup_database', array('class' => 'control-label col-xs-6'));
                       ?>
                       <div class='col-xs-6'>
                           <button id="backup_db" class="btn btn-default btn-sm">
                               <span style="top:22%;"><?php echo $this->lang->line('config_backup_button'); ?></span>
                           </button>
                       </div>
                   <?php }
                   ?>
               </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-3">
                <div class="mini-stat clearfix bx-shadow bg-info">
                    <a href="<?php echo site_url("reports/expiry_report"); ?>"><span class="mini-stat-icon"><i class="ion-share"></i></span></a>
                    <div class="mini-stat-info text-center">
                        Expiry Items (Next 90 days)
                    </div>
                    <div class="mini-stat-info text-center">
                        <a href="<?php echo site_url("reports/expiry_report"); ?>">
                            <h3 class="h3" style="color: white" >
                                        <span>
                                            <b>
                                                <?php
                                                echo $expiryItemsCount;
                                                ?>
                                            </b>
                                        </span></h3>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {

        $("#backup_db").click(function() {
            console.log('clicked');
            var backup_db = $(this);
            backup_db.html("Backup in Progress...");
            backup_db.attr("disabled", "disabled");
            window.location='<?php echo site_url('config/backup_db/1') ?>';
        });
    });

</script>
<?php $this->load->view("partial/footer"); ?>
