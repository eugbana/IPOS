<?php $this->load->view("partial/header"); ?>
<div class="content-page">
    <!-- Start content -->
    <div class="content">
		<?php
            if(isset($error))
            {
                echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
            }

            if(!empty($warning))
            {
                echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
            }

            if(isset($success))
            {
                echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
            }
        ?>
    </div>
</div>