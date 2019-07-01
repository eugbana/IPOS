


            </div>
			</div>
            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->


           
            <!-- /Right-bar -->
</div>
        </div>
		<!--<footer class="footer text-left">
                    2018 © InfoStrategy.
                </footer>-->
		</div>
		
		<div id="noticeModal" class="modal fade">
		<div class="modal-dialog">
			<?php echo form_open('items/receive_transfer', array('id'=>'item_transfer', 'enctype'=>'multipart/form-data')); ?>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Test</h4>
					</div>
					<div class="modal-body">
						<label>Enter Test Code</label>
						<input type="text" name="transfer_id" id="transfer_id" class="form-control" />
			
						<label>Choose Payment</label>
						<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
					</div>
					<div class="modal-footer">
						
						<input type="hidden" name="sale_id" id="sale_id" />
						<input type="submit" name="action" id="action" class="btn btn-success" value="Submit" />
					</div>
					
				</div>
			
			<?php echo form_close(); ?>
		</div>
		
	</div>
        
        
       <script>
			$("#close_register").click(function()
			{
				if(confirm('<?php echo 'Are you sure you want to close the Register? you will need to open another Register to continue'; ?>'))
				{
					$('#register_form').submit();
				}
			});

	   </script> 
       
       
        
		
		
		
		
		
		
		
		
       

        <!-- moment js  -->
       
        
        
       
        
           

        
        <!-- dashboard  -->
       

        <!-- END wrapper -->
		
		</body>

<!-- Mirrored from moltran.coderthemes.com/dark/index.html by HTTrack Website Copier/3.x [XR&CO'2013], Thu, 14 Jul 2016 12:23:45 GMT -->
</html>