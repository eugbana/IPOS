<?php $this->load->view("partial/header"); ?>


<div class="content-page">
    <!-- Start content -->
    <div class="content">

        <div style="padding-top:1rem;">


            <div class="row">
                <div class="text-center">
                    <div>
                        <?php echo anchor('employees/change_password1/' . $user_info->person_id, '<i class="md md-face-unlock"></i> Change Password', array('class' => 'btn btn-md btn-primary modal-dlg', 'data-btn-submit' => 'Submit', 'title' => $this->lang->line('employees_change_password'))); ?>


                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"><img src="assets/images/users/user-default.png" alt="user-img" class="img-circle"> </a>
                            <ul class="dropdown-menu">
                                <li><?php echo anchor('employees/change_password1/' . $user_info->person_id, '<i class="md md-face-unlock"></i> Profile', array('class' => 'modal-dlg', 'data-btn-submit' => 'Submit', 'title' => $this->lang->line('employees_change_password'))); ?></li>
                                <!-- <li><a href="javascript:void(0)"><i class="md md-settings"></i> Settings</a></li>
                                    <li><a href="javascript:void(0)"><i class="md md-lock"></i> Lock screen</a></li> -->
                                <li><a href="<?= site_url('home/logout'); ?>"><i class="md md-settings-power"></i> Logout</a></li>


                            </ul>
                        </li>

                    </div>
                </div>
            </div>
            <div class="row">
                <div id="table_holder">
                    <table id="tablef"></table>
                </div>
            </div>






        </div> <!-- container -->

    </div> <!-- content -->

    <script>
        $(document).ready(function() {
            $(document).on('click', '.push_check', function() {
                var user_id = $(this).attr("id");
                //$('#noticeModal').modal('show');
                $('#transfer_id').val(user_id);
                $('.modal-title').text("Process");
                $('#action').val("Add");
                $('#action').submit();
                $('#item_transfer').submit();
                /*$.post('<?php echo site_url("laboratory/view"); ?>', {user: user_id},function(){
                	$('#userModal').modal('show');
                	$('.modal-title').text("Edit");
                	$('#test_code').val("Hello");
                	$('#test_name').val("<?php echo $test_info->test_name; ?>");
                	$('#action').val("Edit");
                	$('#operation').val("Edit");
                });*/

            });
            $('.count').html('<?php echo $transfer; ?>');
            var added = "<li class='text-center notifi-title'>Notification</li>";
            var others = "<?php foreach ($notice as $lin => $item) {
                                echo "<a href='javascript:void(0);' class='list-group-item push_check' id='" . $item["transfer_id"] . "'><div class='media'><div class='pull-left'><em class='fa fa-user-plus fa-2x text-info'></em> </div><div class='media-body clearfix'><div class='media-heading'>" . $item["transfer_type"] . " Request</div><p class='m-0'><small>You have 10 unread messages</small></p></div></div></a>";
                            }
                            ?>";
            $('#notification').html(added + others);
        });
    </script>

    <?php $this->load->view("partial/footer"); ?>