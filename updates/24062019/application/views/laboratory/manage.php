<?php $this->load->view("partial/header"); ?>
			<!--<button onClick=" myFunction('abc')">Try it</button>-->
<div class="content-page">
                <!-- Start content -->
                <div class="content">
		<div class="container">
			<div class="row" style="background-color:#4a48451a;">
				<div class="col-md-1"></div>
				<div class="col-md-10">
				
					<div class="row" style="background-color:white">
					<?php
							if(isset($customer))
							{
							?>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-4">
								</div>
								<div class="col-md-4">
									<h3 align="center">Patient Info</h3>
								</div>
							
								<div class="col-md-4">
									<button class='btn btn-info btn-sm pull-right' id="submitButton">
									<span class="glyphicon glyphicon-tag">&nbsp</span><?php echo $this->lang->line($controller_name. '_process'); ?>
									</button>
								</div>
							</div>
						</div>
						
							<?php echo form_open($controller_name."/edit_item", array('id'=>'carter')); ?>
						
						<div class="row">
						
							
							
							<fieldset>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
											<?php echo form_input(array(
													'name'=>'surname',
													'id'=>'surname',
													'class'=>'form-control input-sm',
													'value'=>$last_name )
													);?>
										</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
											<?php echo form_input(array(
													'name'=>'first_name',
													'id'=>'first_name',
													'class'=>'form-control input-sm',
													'value'=>$first_name)
													);?>
										</div>
									</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
									
										
											<div class="input-group  date" id="datetimepicker3">
												<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
												
												<?php echo form_input(array(
														'name'=>'age',
														'id'=>'age',
														'class'=>'form-control input-sm',
														'placeholder'=>'Date of Birth',
														'value'=>$age)
														);?>
											</div>
										
									</div>
								 </div>
							
							</fieldset>
						
						
						</div>

					
					
					<div class="row">
						
							
							
							<fieldset>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
											<?php echo form_input(array(
													'name'=>'email',
													'id'=>'email',
													'class'=>'form-control input-sm',
													'value'=>$customer_email)
													);?>
										</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone"></span></span>
											<?php echo form_input(array(
													'name'=>'phone_number',
													'id'=>'phone_number',
													'class'=>'form-control input-sm',
													'value'=>$phone_number)
													);?>
										</div>
									</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
											<?php echo form_input(array(
													'name'=>'doctor_name',
													'id'=>'doctor_name',
													'class'=>'form-control input-sm',
													'placeholder'=>'Doctor Name')
													);?>
										</div>
									</div>
							</div>
							
							</fieldset>
						
						
						</div>
						<div class="row">
						
							
							
							<fieldset>
							
							<div class="col-md-9">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
											<?php echo form_input(array(
													'name'=>'company_name',
													'id'=>'company_name',
													'class'=>'form-control input-sm',
													'placeholder'=>'NHIS/HMO/Retainer Company Name')
													);?>
										</div>
									</div>
							</div>
							<div class="col-md-2">
								<div class="form-group form-group-sm">


										<?php echo form_input(array(
														'name'=>'gender',
														'id'=>'gender',
														'class'=>'form-control input-sm',
														'placeholder'=>'Gender',
														'value'=>$gender)
														);?>
								</div>
							</div>
							
							
							
							</fieldset>
						
						
						</div>
						<div class="row">
						
							
							
							<fieldset>
							
							<div class="col-md-6">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
											<?php echo form_input(array(
													'name'=>'address_1',
													'id'=>'address_1',
													'class'=>'form-control input-sm',
													'value'=>$customer_address,
													'placeholder'=>'Customer Address',
													)
													);?>
										</div>
									</div>
							</div>
							<div class="col-md-6">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
											<?php echo form_input(array(
													'name'=>'hospital',
													'id'=>'hospital',
													'class'=>'form-control input-sm',
													'placeholder'=>'Hospital Name')
													);?>
										</div>
									</div>
							</div>
							
							
							
							</fieldset>
						
						
						</div>
						<?php echo form_close(); ?>
						<?php
						}
						else
						{
						?>
					
					<div class="row" style="margin-top:10px">
						
							
							
							<fieldset>
							<div class="col-md-2">
									<button class='btn btn-info btn-sm modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url("customers/view"); ?>'
									title='<?php echo $this->lang->line($controller_name. '_new_customer'); ?>'>
									New Customer
									</button>
								</div>
						<?php echo form_open("laboratory/select_customer", array('id'=>'select_customer_form')); ?>
							<div class="col-md-4">
								<div class="form-group form-group-sm">
								
										<div class="input-group">
											<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
											<?php echo form_input(array(
													'name'=>'customer',
													'id'=>'customer',
													'class'=>'form-control input-sm',
													'placeholder'=>'Customer Detail')
									
													);?>
										</div>
									</div>
							</div>
							
							
							
							
							
							</fieldset>
						
						
						</div>
						<?php echo form_close(); ?>
				<?php
				}
				?>
					
						</div>

					
					<div class="row" style="height:10px">
						

					</div>
					<?php echo form_open("laboratory/lab_cart", array('id'=>'process_lab_test')); ?>
					<div class="row" style="background-color:white">
					
					<div class="row"><div class="col-md-4" style="margin-left:5px"><b><h4 id="demo"></h4></b></div><div class="col-md-4"><h3 align="center">Request Form</h3></div></div>
						<div class="form-group has-feedback"><input type="text" class="form-control" id="myInput" onkeyup="searchFunction()" placeholder="Search for names.." title="Type in a name"/><i class="glyphicon glyphicon-search form-control-feedback"></i></div>
						<div class="row">
							
							<div class="col-md-12">
							
											<fieldset>
													
												<?php //print_r($laboratory_test) ?>
															
														<ul id="myUL">
													 
														<?php foreach($laboratory_test as $row=>$value){ ?>
														
															<li class="column"><a>
															<?php echo form_checkbox("grants[]",$value['item_id'], false,array('id'=>'checkbutton','class'=>'module'));?>
															<span class="medium"><?php echo $value['test_name'];?></span>
															</a></li>
														<?php
															}
														?>
														</ul>
													
													
												</fieldset>
								<?php echo form_close(); ?>
												
								</div>
								
								
							</div>
					</div>
				</div>
				<div class="col-md-1"></div>
			</div>
		</div>
	</div>
		</div>

		<?php print_r($grants_data); ?>
			
			<?php //print_r($grad);print_r($lab_values);?>
			


        
        

        

        	
			
<script>
	var check_list = [];
	var favorite=[];
	var elements = document.getElementsByClassName("column");
	$('#datetimepicker2').datetimepicker( {
	locale: "ar"
} );
$('#datetimepicker3').datetimepicker();
    $(document).ready(function() {
		
	
	$("#customer").autocomplete(
	{
		source: '<?php echo site_url("customers/suggest"); ?>',
		minChars: 0,
		delay: 10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$("#select_customer_form").submit();
		}
	});
	

		$('#submitButton').click(function(){
			$('#process_lab_test').submit();
		});
		$("#butmin").click(function(){
            $('#buttons_form').submit();
            });
		/*$("a").click(function(event){
			event.preventDefault();
		});*/
			
			/* $.each($("input[name='grants[]']:checked"), function(){            
                alert("Hioo");
              });*/
		
		/*$(".module").change(function() {
			if($(this).attr("checked", true)) {
				//Do stuff
				var checking=$(this).val();
				check_list.push(checking);
				//alert();
				alert("My favourite sports are: " + check_list.join(", "));
			}else if($(this).attr("checked", false)){
				var checking=$(this).val();
				//check_list.splice( $.inArray(checking, check_list), 1 );
				check_list.splice(check_list.indexOf(checking),1);
				//alert();
				alert(check_list);
			}
			
		});*/
		
		$('input[type="checkbox"]').click(function(event){
				//event.preventDefault();
			
            if($(this).prop("checked") == true){
                var checking=$(this).val();
				//check_list.push(checking);
				//alert();
				//valuetochecker='abc';
				var checkreturn=myFunction(checking);
				check_list.push(checkreturn);
				item_sum();
				//var valuetochecker=check_list.reduce(getSum);
				
				//alert(total);
				//document.getElementById("demo").innerHTML=check_list.reduce(getSum);
				//alert("My favourite sports are: " + check_list.join(", "));
				//favorite=check_list.join(", ")
				//anoFunction()
            }
            else if($(this).prop("checked") == false){
                var checking=$(this).val();
				var checkreturnd=myFunction(checking);
				check_list.splice( $.inArray(checkreturnd, check_list), 1 );
				item_sum();
				//alert("My favourite sports are: " + check_list.join(", "));
            }
        });
				
          
			
		
          $("input[type='checkbox']").val();  
        
        $("#butmi").click(function(){
            //var favorite = [];
			var a="a";
			var checkiy=changer(a);
            $.each($("input[name='grants[]']:checked"), function(){            
                favorite.push(checkiy);
            });
            alert("My favourite sports are: " + favorite.join(", "));
        });
		dialog_support.init("a.modal-dlg, button.modal-dlg");
		
		
		table_support.handle_submit = function(resource, response, stay_open)
	{
		if(response.success) 
		{
			if(resource.match(/customers$/))
			{
				$("#customer").val(response.id);
				$("#select_customer_form").submit();
			}
			else
			{
				var $stock_location = $("select[name='stock_location']").val();
				$("#item_location").val($stock_location);
				$("#item").val(response.id);
				if(stay_open)
				{
					$("#add_item_form").ajaxSubmit();
				}
				else
				{
					$("#add_item_form").submit();
				}
			}
		}
	}	
			
            
        });
		function myFunction(value_checker) {
				<?php
				$php_array = array(array('a'=>'abc','b'=>'def','c'=>'ghi'),
				array('a'=>'fgh','b'=>'efg','c'=>'asd')
				);
				
				$js_array = json_encode($laboratory_test);
				echo "var javascript_array = ". $js_array . ";\n";
				?>
				//var favorite = [];
				//var javascript_checker=[];
				/*var javascript_checker=javascript_array.done(
					var fixture;
					var converted_val;
					for(var i in json){
						fixture=json[i];
						if (fixture.a==value_checker){
							converted_val="Hello";
						}
					}
					return "Hello";
				);*/
				/*$.each(javascript_array, function (index, value) {
				  //console.log(value);
				  if(index=="a"){
					  favorite.push(value);
				  }
				  
				});
				alert("My favourite sports are: " + favorite.join(", "));*/
				//var myJSON = JSON.stringify(javascript_array);
				//alert("Hello World");
				//javascript_array.toString();
				//document.getElementById("demo").innerHTML=myJSON;
				//console.log(javascript_array["a"]);
				var checklick;
				$.each(javascript_array, function (i,item) {
				   //$.each(this, function (name, value) {
					  //favorite.push(name + '=' + value);
					  //favorite.push(index[);
					  //if(name=="a"){
					  //favorite.push(item.b);
					  var flick=javascript_array[i].item_id;
					  //var fglick=javascript_array[i].b;
					  
					  if(flick==value_checker){
						  //favorite.push(javascript_array[i].b);
						  checklick=javascript_array[i].test_amount;
						  //return checklick;
					  }
					  
				  //}
				  // });
				   
				});
				//alert("My favourite sports are: " + favorite.join(", "));
				return checklick;
			}
			function changer(value_checker){
				<?php $php_array = array('a'=>'abc','b'=>'def','c'=>'ghi');
				$js_array = json_encode($php_array);
				echo "var javascript_array = ". $js_array . ";\n";?>
				
				var javascript_checker=javascript_array.done(function(json){
					var fixture;
					var converted_val;
					for(var i in json){
						fixture=json[i];
						if (fixture.a==value_checker){
							converted_val="Hello";
						}
					}
					return "Hello";
				});
				
				return javascript_checker;
			}
			
			function getSum(total, num) {
				return total + num;
			}
			function anoFunction(item) {
				document.getElementById("demo").innerHTML = check_list.join(", ").reduce(getSum);
			}
			function item_sum() {
			var total = 0;
			var currency="₦";
				for (var i = 0; i < check_list.length; i++) {
					total += check_list[i] << 0;
				}
				document.getElementById("demo").innerHTML=currency+" "+total;
			}
			
			function searchFunction() {
				var input, filter, ul, li, a, i;
				input = document.getElementById("myInput");
				filter = input.value.toUpperCase();
				ul = document.getElementById("myUL");
				li = ul.getElementsByTagName("li");
				for (i = 0; i < li.length; i++) {
					a = li[i].getElementsByTagName("a")[0];
					if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
						li[i].style.display = "";
					} else {
						li[i].style.display = "none";
					}
				}
		}
		function gridView() {
		  for (i = 0; i < elements.length; i++) {
			elements[i].style.width = "50%";
		  }
}

		
    
	
	
</script>


<?php $this->load->view("partial/footer"); ?>
