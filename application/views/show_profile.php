<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>
<div class="content-page">
    <!-- Start content -->
    <div class="content">

        <div id="page_title"><?php echo $this->lang->line('reports_report_input'); ?></div>

        <?php
        if (isset($error)) {
            echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
        }
        ?>

        <?php echo form_open('home/profile', array('id' => 'profile', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
        <div class="form-group form-group-sm">
            <?php echo form_label('Password', 'password_label', array('class' => 'control-label col-xs-2 required')); ?>
            <div class="col-xs-3">
                <?php echo form_input(array('name' => 'password', 'class' => 'form-control input-sm', 'id' => 'password')); ?>
            </div>
        </div>
        <div class="form-group form-group-sm">
            <?php echo form_label('Confirm Passowrd', 'confirm_password_label', array('class' => 'control-label col-xs-2 required')); ?>
            <div class="col-xs-3">
                <?php echo form_input(array('name' => 'confirm_password', 'class' => 'form-control input-sm', 'id' => 'confirm_password')); ?>
            </div>
        </div>
        <div class="form-group form-group-sm">
            <div class="col-xs-3">
                <button class="btn btn-primary btn-sm" id="profile-btn">Update</button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
    $(document).ready(function() {


        $("#profile-btn").click(function() {
            window.alert('wilson you are there');
        });
    });
</script>