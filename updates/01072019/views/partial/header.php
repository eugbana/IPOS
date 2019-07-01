<!DOCTYPE html>
<html>
    
<!-- Mirrored from moltran.coderthemes.com/dark/index.html by HTTrack Website Copier/3.x [XR&CO'2013], Thu, 14 Jul 2016 12:16:29 GMT -->
	<head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<base href="<?php echo base_url();?>" />
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <link rel="shortcut icon" href="dist/assets/images/favicon_1.ico">

       <title><?php echo $this->config->item('company') . ' | ' . $this->lang->line('common_powered_by') . ' IPOS ' ?></title>
		
		


        <link rel="stylesheet" type="text/css" href="<?php echo 'dist/bootswatch/' . (empty($this->config->item('theme')) ? 'flatly' : $this->config->item('theme')) . '/bootstrap.min.css' ?>"/>
        
        <link href="dist/assets/css/icons.css" rel="stylesheet" type="text/css">
     
        <link href="dist/assets/css/menu.css" rel="stylesheet" type="text/css">
        
		
		
		
		<?php if ($this->input->cookie('debug') == 'true' || $this->input->get('debug') == 'true') : ?>
		<!-- bower:css -->
		<link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.css" />
		<link rel="stylesheet" href="bower_components/bootstrap3-dialog/dist/css/bootstrap-dialog.min.css" />
		<link rel="stylesheet" href="bower_components/jasny-bootstrap/dist/css/jasny-bootstrap.css" />
		<link rel="stylesheet" href="bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-select/dist/css/bootstrap-select.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-table/src/bootstrap-table.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css" />
		<link rel="stylesheet" href="bower_components/chartist/dist/chartist.min.css" />
		<link rel="stylesheet" href="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.css" />
		<!-- endbower -->
		<!-- start css template tags -->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.autocomplete.css"/>
		<link rel="stylesheet" type="text/css" href="css/invoice.css"/>
		<link rel="stylesheet" type="text/css" href="css/ospos.css"/>
		<link rel="stylesheet" type="text/css" href="css/ospos_print.css"/>
		<link rel="stylesheet" type="text/css" href="css/popupbox.css"/>
		<link rel="stylesheet" type="text/css" href="css/receipt.css"/>
		<link rel="stylesheet" type="text/css" href="css/register.css"/>
		<link rel="stylesheet" type="text/css" href="css/reports.css"/>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		
		
		
		<!-- end css template tags -->
		<!-- bower:js -->
		
		<script src="bower_components/jquery/dist/jquery.js"></script>
		<script src="bower_components/jquery-form/jquery.form.js"></script>
		<script src="bower_components/jquery-validate/dist/jquery.validate.js"></script>
		<script src="bower_components/jquery-ui/jquery-ui.js"></script>
		<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
		<script src="bower_components/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js"></script>
		<script src="bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.js"></script>
		<script src="bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
		<script src="bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
		<script src="bower_components/bootstrap-table/src/bootstrap-table.js"></script>
		<script src="bower_components/bootstrap-table/dist/extensions/export/bootstrap-table-export.js"></script>
		<script src="bower_components/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.js"></script>
		<script src="bower_components/moment/moment.js"></script>
		<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
		<script src="bower_components/file-saver.js/FileSaver.js"></script>
		<script src="bower_components/html2canvas/build/html2canvas.js"></script>
		<script src="bower_components/jspdf/dist/jspdf.min.js"></script>
		<script src="bower_components/jspdf-autotable/dist/jspdf.plugin.autotable.js"></script>
		<script src="bower_components/tableExport.jquery.plugin/tableExport.min.js"></script>
		<script src="bower_components/chartist/dist/chartist.min.js"></script>
		<script src="bower_components/chartist-plugin-axistitle/dist/chartist-plugin-axistitle.min.js"></script>
		<script src="bower_components/chartist-plugin-pointlabels/dist/chartist-plugin-pointlabels.min.js"></script>
		<script src="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.min.js"></script>
		<script src="bower_components/chartist-plugin-barlabels/dist/chartist-plugin-barlabels.min.js"></script>
		<script src="bower_components/remarkable-bootstrap-notify/bootstrap-notify.js"></script>
		<script src="bower_components/js-cookie/src/js.cookie.js"></script>
		

		
		<!-- endbower -->
		<!-- start js template tags -->
		<script type="text/javascript" src="js/imgpreview.full.jquery.js"></script>
		<script type="text/javascript" src="js/manage_tables.js"></script>
		<script type="text/javascript" src="js/nominatim.autocomplete.js"></script>
		<!-- end js template tags -->
		<?php else : ?>
		<!--[if lte IE 8]>
		<link rel="stylesheet" media="print" href="dist/print.css" type="text/css" />
		<![endif]-->
		<!-- start mincss template tags -->
		<link rel="stylesheet" type="text/css" href="dist/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="dist/opensourcepos.min.css?rel=033102c5d3"/>
		<link rel="stylesheet" type="text/css" href="dist/style.css"/>
		<!-- end mincss template tags -->
		<!-- start minjs template tags -->
		<script type="text/javascript" src="dist/opensourcepos.min.js?rel=406c44e716"></script>
		<!-- end minjs template tags -->
		<?php endif; ?>
	
		 <!-- Custom styles for this template-->
		<link href="dist/css/sb-admin.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
		<style>
			* {
				box-sizing: border-box;
			}

			#myInput {
				background-image: url('/css/searchicon.png');
				background-repeat: no-repeat;
				width: 90%;
				font-size: 16px;
				padding: 12px 20px 12px 40px;
				border: 1px solid #ddd;
				margin-bottom: 12px;
				margin-right: 12px;
				margin-left: 32px;
			}

			#myUL {
				list-style-type: none;
				padding: 0;
				margin: 0;
				columns: 3;
			}

			#myUL li a {
				border: 1px solid #ddd;
				margin-top: -1px; /* Prevent double borders */
				background-color: #f6f6f6;
				padding: 12px;
				text-decoration: none;
				font-size: 15px;
				color: black;
				display: block
			}

			#myUL li a:hover:not(.header) {
				background-color: #eee;
			}
			.buttona{
				padding: 10px;
				display: inline;
				border-radius: 2px;
				font-family: "Arial";
				border: 5px solid white;
				margin: 0 10px 1px;
				background: green;
				font-size: 15px;
				line-height: 15px;
				color: white;
				width: auto;
				height: auto;
				box-sizing: content-box;
			}
			a:link {
				text-decoration: none;
			}

			a:visited {
				text-decoration: none;
			}

			a:hover {
				text-decoration: underline;
			}

			a:active {
				text-decoration: underline;
			}
		</style>	
		<!--<link href="dist/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">-->
		<?php $this->load->view('partial/header_js'); ?>
		<?php $this->load->view('partial/lang_lines'); ?>

		<style type="text/css">
			html {
				overflow: auto;
			}
		</style>
		<script type="text/javascript">
			$(document).ready(function() {
				$('.blockUI').remove();
			});
		</script>

        <script src="dist/assets/js/modernizr.min.js"></script>
		<!-- <script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','http://www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-65046120-1', 'auto');
		ga('send', 'pageview');
		</script> -->

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        
    </head>
    <body class="fixed-left">
        
        <!-- Begin page -->
        <div id="wrapper">
        
            <!-- Top Bar Start -->
            <div class="topbar" style="height:73px;">
                <!-- LOGO -->
                <div class="topbar-left">
                    <div class="text-center">
                        <a href="<?php echo site_url();?>" class="logo"><i class="md md-terrain"></i> <span>IPOS</span></a>
                    </div>
                </div>
                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation" style="height:70px;margin-bottom:0px">
                    <div class="container">
                        <div class="">
                            <div class="pull-left">
                                <button class="button-menu-mobile open-left">
                                    <i class="fa fa-bars"></i>
                                </button>
                                <span class="clearfix"></span>
                            </div>
                            <form class="navbar-form pull-left" role="search">
                                <div class="form-group">
                                    <input type="text" class="form-control search-bar" placeholder="Type here for search...">
                                </div>
                                <button type="submit" class="btn btn-search"><i class="fa fa-search"></i></button>
                            </form>

                            <ul class="nav navbar-nav navbar-right pull-right">
								<li class="dropdown">
								   <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count" style="border-radius:10px;"></span> <span class="glyphicon glyphicon-bell" style="font-size:18px;"></span></a>
								   <ul class="dropdown-menu dropdown-menu-lg" id="notification">
										 
								   </ul>
								 </li>
                                <li class="dropdown hidden-xs">
                                    <a data-target="#" class="dropdown-toggle waves-effect" aria-expanded="true">
                                        <i class="md md-notificatedions"></i> <span class="badge badge-xs badge-dangers"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-lg">
                                        <li class="text-center notifi-title">Notification</li>
                                        <li class="list-group">
                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left">
                                                    <em class="fa fa-user-plus fa-2x text-info"></em>
                                                 </div>
                                                 <div class="media-body clearfix">
                                                    <div class="media-heading">New user registered</div>
                                                    <p class="m-0">
                                                       <small>You have 10 unread messages</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>
                                           <!-- list item-->
                                            <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left">
                                                    <em class="fa fa-diamond fa-2x text-primary"></em>
                                                 </div>
                                                 <div class="media-body clearfix">
                                                    <div class="media-heading">New settings</div>
                                                    <p class="m-0">
                                                       <small>There are new settings available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                            </a>
                                            <!-- list item-->
                                            <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left">
                                                    <em class="fa fa-bell-o fa-2x text-danger"></em>
                                                 </div>
                                                 <div class="media-body clearfix">
                                                    <div class="media-heading">Updates</div>
                                                    <p class="m-0">
                                                       <small>There are
                                                          <span class="text-primary">2</span> new updates available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                            </a>
                                           <!-- last list item -->
                                            <a href="javascript:void(0);" class="list-group-item">
                                              <small>See all notifications</small>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="hidden-xs">
                                    <a href="#" id="btn-fullscreen" class="waves-effect"><i class="md md-crop-free"></i></a>
                                </li>
                                <li class="hidden-xs">
                                    <a href="#" class="right-bar-toggle waves-effect"><i class="md md-chat"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"><img src="assets/images/users/user-default.png" alt="user-img" class="img-circle"> </a>
                                    <ul class="dropdown-menu">
										<li><?php echo anchor('employees/change_password/'.$user_info->person_id, '<i class="md md-face-unlock"></i> Profile', array('class' => 'modal-dlg', 'data-btn-submit' => 'Submit', 'title' => $this->lang->line('employees_change_password'))); ?></li>
                                        <li><a href="javascript:void(0)"><i class="md md-settings"></i> Settings</a></li>
                                        <li><a href="javascript:void(0)"><i class="md md-lock"></i> Lock screen</a></li>
                                        <li><a href="<?= site_url('home/logout'); ?>"><i class="md md-settings-power"></i> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->


            <!-- ========== Left Sidebar Start ========== -->

            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">
                    <div class="user-details">
                        <div class="pull-left">
                            <img src="assets/images/users/user-default.png" alt="" class="thumb-md img-circle">
                        </div>
						<?php echo form_open("sales/close_register", array('id'=>'register_form')); ?>
                        <div class="user-info">
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><?= $user_info->first_name . ' ' . $user_info->last_name;  ?> <span class="caret"></span></a>
								
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)"><i class="md md-face-unlock"></i> Profile<div class="ripple-wrapper"></div></a></li>
									
                                    <?php 
										if(($user_info->role)==5){
									?>
										<li id="close_register"><a href="javascript:void(0)"><i class="md md-settings"></i> Close Register</a></li>
									<?php }else{ ?>
										<li><a href="javascript:void(0)"><i class="md md-settings"></i> Settings</a></li>
									<?php }
									?>
									
                                    <li><a href="javascript:void(0)"><i class="md md-lock"></i> Lock screen</a></li>
                                    <li><a href="<?= site_url('home/logout'); ?>"><i class="md md-settings-power"></i> Logout</a></li>
                                </ul>
								<?php echo form_close(); ?>
                            </div>
                            
                            <p class="text-muted m-0">
								<?php //$user_info->role Administrator
									if(($user_info->role)==3){
										echo "Administrator";
									}elseif(($user_info->role)==7){
										echo "Cashier / ".$branch;
									}elseif(($user_info->role)==4){
										echo "Inventory / ".$branch;
									}elseif(($user_info->role)==6){
										echo "Accountant / ".$branch;
									}elseif(($user_info->role)==9){
										echo "Scientist / ".$branch;
									}elseif(($user_info->role)==5){
										echo "Cashier / ".$branch;
									}elseif(($user_info->role)==10){
										echo "Executive";
									} else {
										
									}
								?>
							</p>
                        </div>
                    </div>
                    <!--- Divider -->
                    <div id="sidebar-menu">
                        <ul>
                            <li>
                                <a href="<?php echo site_url();?>" <?php if($_SERVER['REQUEST_URI'] == "/home"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-home"></i><span> Dashboard </span></a>
                            </li>
							<?php if(($user_info->role)==1){?>
							
								<li>
									<a href="<?php echo site_url("laboratory/cashier");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory/cashier"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Account </span></a>
								</li>
							<?php } if( ($user_info->roles) == "custom" ) {
								 foreach($allowed_modules->result() as $module){ ?>
											<li class="has_sub">				
												<a href="<?php echo site_url("$module->module_id");?>" <?php if($_SERVER['REQUEST_URI'] == "/$module->module_id"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_".$module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
											</li>
										<?php }
								} elseif(($user_info->role)==7){
									
										foreach($allowed_modules->result() as $module)
										{
										 if($this->lang->line("module_".$module->module_id)=="Laboratory"){?>
													<li>
														<a href="<?php echo site_url("laboratory");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Available Test </span></a>
													</li>
													<li>
														<a href="<?php echo site_url("laboratory/test_start");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory/test_start"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> New Test </span></a>
													</li>
													<li id="search">
														<a><i class="<?php echo $module->icon; ?>"></i><span> Test Results Status </span></a>
													</li
																
																
												<?php }elseif($this->lang->line("module_".$module->module_id)=="Customers"){?>
							
												<?php }else{?>
												<li class="has_sub">
												<a href="<?php echo site_url("$module->module_id");?>" <?php if($_SERVER['REQUEST_URI'] == "/$module->module_id"): ?> class="waves-effect waves-light active" <?php else: ?> class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_".$module->module_id) ?> </span><span class="pull-right"><i class="md md-add"></i></span></a>
													
											</li>
												<?php
												}
										}
										?>

						
								<?php }elseif(($user_info->role)==6){
									
										foreach($allowed_modules->result() as $module)
										{
											
										 if($this->lang->line("module_".$module->module_id)=="Account"){?>
														
														<li>
															<a href="<?php echo site_url("account/unprocessed_payment");?>" <?php if($_SERVER['REQUEST_URI'] == "/account/unprocessed_payment"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed</span></a>
														</li>
														<li>
															<a href="<?php echo site_url("account/processed_payment");?>" <?php if($_SERVER['REQUEST_URI'] == "/account_processed_payment"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
														</li>
														
														
																
												<?php
												}
										}
										?>
										<?php 
											}elseif(($user_info->role)==12){
									
										foreach($allowed_modules->result() as $module)
										{
											
										 if($this->lang->line("module_".$module->module_id)=="Account"){?>
														<li>
															<a href="<?php echo site_url("reports/account_report");?>" <?php if($_SERVER['REQUEST_URI'] == "/reports/account_report"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo 'Reports' ?></span></a>
														</li>
														<li>
															<a href="<?php echo site_url("account/unprocessed_payment");?>" <?php if($_SERVER['REQUEST_URI'] == "/account/unprocessed_payment"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed</span></a>
														</li>
														<li>
															<a href="<?php echo site_url("account/processed_payment");?>" <?php if($_SERVER['REQUEST_URI'] == "/account/processed_payment"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Processed Payments</span></a>
														</li>
														
														
																
												<?php
												}
										}
										?>
										<?php 
											}elseif(($user_info->role)==3){
									
										foreach($allowed_modules->result() as $module)
										{
											
										 if($this->lang->line("module_".$module->module_id)=="Employees"){?>
														<li>
										 					<a href="<?php echo site_url("employees");?>" <?php if($_SERVER['REQUEST_URI'] == "/employees"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Employees</span></a>
														</li>
														<li>
															<a href="<?php echo site_url("config");?>" <?php if($_SERVER['REQUEST_URI'] == "/config"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Settings</span></a>
														</li>
														
														
																
												<?php
												}
										}
										?>
										<?php 
											}elseif(($user_info->role)==9){
									
										foreach($allowed_modules->result() as $module){
										 if($this->lang->line("module_".$module->module_id)=="Laboratory"){?>
															
																
														<li>
															<a href="<?php echo site_url("laboratory/new_results");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory/new_results"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>UnProcessed Results</span></a>
														</li>
														<li>
															<a href="<?php echo site_url("laboratory/pending_results");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory/pending_results"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Pending Results</span></a>
														</li>
														<li>
															<a href="<?php echo site_url("laboratory/completed_results");?>" <?php if($_SERVER['REQUEST_URI'] == "/laboratory/completed_results"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span>Completed Results</span></a>
														</li>
												<?php
												}
										}
										?>
								<?php }elseif(($user_info->role)==5){?>
										<li>
											<a href="<?php echo site_url("sales");?>" <?php if($_SERVER['REQUEST_URI'] == "/sales"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> New Sale </span></a>
										</li>
										<li>
											<a href="<?php echo site_url("sales/pill");?>" <?php if($_SERVER['REQUEST_URI'] == "/sales/pill"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Pill Reminder </span></a>
										</li>
										<li>
											<a href="<?php echo site_url("sales/detailed_sales");?>" <?php if($_SERVER['REQUEST_URI'] == "/sales/detailed_sales"): ?>class="waves-effect waves-light active" <?php else: ?>class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span> Online Sales </span></a>
										</li>
							<?php
								}elseif(($user_info->role)==4){?>
										
										<?php
								foreach($allowed_modules->result() as $module)
								{
								?>
								<?php if($this->lang->line("module_".$module->module_id)=="Items"){?>					
									<li>
										<a href="<?php echo site_url("$module->module_id");?>" <?php if($_SERVER['REQUEST_URI'] == "/$module->module_id"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-layers"></i><span><?php echo $this->lang->line("module_".$module->module_id) ?></span></a>
									</li>
									<li>
										<a href="<?php echo site_url("items/categories");?>" <?php if($_SERVER['REQUEST_URI'] == "/items/categories"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-extension"></i><span>Categories</span></a>
									</li>
									<li>
										<a href="<?php echo site_url("receivings");?>" <?php if($_SERVER['REQUEST_URI'] == "/receivings"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-assessment"></i><span>Update Inventory</span></a>
									</li>
									<li>
										<a href="<?php echo site_url("receivings/history");?>" <?php if($_SERVER['REQUEST_URI'] == "/receivings/history"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-subject"></i><span>Inventory History</span></a>
									</li>	
									<li>
										<a href="<?php echo site_url("receivings");?>" <?php if($_SERVER['REQUEST_URI'] == "/receivings"): ?>class="waves-effect waves-light active" <?php else:  ?> class="waves-effect waves-light" <?php endif; ?>><i class="md md-fast-rewind"></i><span>Returns</span></a>
									</li>
										<?php
									}
								}
								?>
							<?php
								}else{?>
										
										<?php
								foreach($allowed_modules->result() as $module)
								{
								?>
								
								<li>
								<a href="<?php echo site_url("$module->module_id");?>" <?php if($_SERVER['REQUEST_URI'] == "/$module->module_id"): ?>class="waves-effect waves-light active"<?php else: ?> class="waves-effect waves-light" <?php endif; ?>><i class="<?php echo $module->icon; ?>"></i><span><?php echo $this->lang->line("module_".$module->module_id) ?></span></a>
								</li>
									
								<?php
									
								}
								?>
							<?php
								}
								?>
								

                            

                            
                                </ul>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
			
            <!-- Left Sidebar End --> 
			<div id="content-wrapper"style="padding-top:0px;">
			<div class="container-fluid">
				<div class="row">
		
	 
