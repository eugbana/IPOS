<?php $this->load->view("partial/header_print"); ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<?php
		if (isset($error_message)) {
			echo "<div class='alert alert-dismissible alert-danger'>" . $error_message . "</div>";
			exit;
		}
		?>

		<?php //$this->load->view('partial/print_receipt', array('print_after_sale', $print_after_sale, 'selected_printer' => 'receipt_printer')); ?>

		<div class="print_hide" id="control_buttons" style="text-align:right">
			<!-- <a href="javascript:printdoc();">
				<div class="btn btn-info btn-sm" , id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div>
			</a> -->
			<?php //echo anchor("items/request_item", '<span class="glyphicon glyphicon-save">&nbsp</span> New Request', array('class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button')); ?>
		</div>

		<h1 class="text-center">
            <br/>
            <br/>
            <br/>
            <br/>
             <?php echo $content; ?>
            <br/>
            <br/>
            <?php echo anchor("/home", '<span class="glyphicon glyphicon-save">&nbsp</span> Go Home', array('class' => 'btn btn-info btn-md', 'id' => 'show_sales_button')); ?>
        </h1>

        

	</div>
</div>


<?php $this->load->view("partial/footer"); ?>