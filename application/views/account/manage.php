<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>


<h3 class="text-center"></h3>
<div id="home_module_list">

		<div class="module_item" title="New Test">
			<a href="<?php echo site_url("account/unprocessed_payment");?>"><img src="<?php echo base_url().'images/menubar/items.png';?>" border="0" alt="Menubar Image" /></a>
			<a href="<?php echo site_url("account/unprocessed_payment");?>">Unprocessed Payment</a>
		</div>
		<div class="module_item" title="Check Result" id="search">
			<a href="<?php echo site_url("account/processed_payment");?>"><img src="<?php echo base_url().'images/menubar/items.png';?>" border="0" alt="Menubar Image" /></a>
			<a href="<?php echo site_url("account/processed_payment");?>">Processed Payment</a>
		</div>
	
</div>





<?php $this->load->view("partial/footer"); ?>