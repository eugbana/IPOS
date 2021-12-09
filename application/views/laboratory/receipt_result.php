<div id="receipt_wrapper">
	<div id="receipt_header">
		<?php
		if ($this->config->item('laboratory_logo') != '') {
			?>
			<div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->config->item('laboratory_logo')); ?>" alt="company_logo" /></div>
		<?php
		}
		?>
		<?php
		//print_r($cart);
		if ($this->config->item('receipt_show_company_name')) {
			?>
			<div id="company_name">
				<h3><b><?php echo 'Tonia Medical Diagnostics Services LTD..'; ?></b></h3>
			</div>
		<?php
		}
		?>

		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo !empty($this->config->item('laboratory_phone')) ? $this->config->item('laboratory_phone') : '09097486557' ?></div>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>


	</div>
	<div class="row">
		<div id="receipt_general_info" class="col-xs-5">

			<div id="sale_id"><b>Request ID:</b><?php echo " " . $sale_id; ?></div>
			<?php
			if (isset($customer)) {
				?>
				<div id="customer"><b>Name:</b><?php echo " " . $customer; ?></div>
				<div id="customer"><b>Mobile:</b><?php echo " " . $phone_number; ?></div>
			<?php
			}
			?>


			<?php
			if (!empty($invoice_number)) {
				?>
				<div id="invoice_number"><?php echo $this->lang->line('sales_invoice_number') . ": " . $invoice_number; ?></div>
			<?php
			}
			?>
			<div id="employee"><b>Medical Laboratory Scientist:</b><?php echo " " . $employee; ?></div>
		</div>
		<div id="receipt_general_info" class="col-xs-5">

			<div id="sale_id"><b>Sex:</b><?php echo " " . $gender; ?></div>

			<?php
			if (isset($customer)) {
				?>
				<div id="customer"><b>Age:</b><?php echo " " . $age; ?></div>
				<div id="customer"><b>Time Taken:</b><?php echo " " . $result_time; ?></div>
			<?php
			}
			?>


			<?php
			if (!empty($invoice_number)) {
				?>
				<div id="invoice_number"><?php echo $this->lang->line('sales_invoice_number') . ": " . $invoice_number; ?></div>
			<?php
			}
			?>
			<div id="employee"><b>Time Dispatched:</b><?php echo " " . $result_end; ?></div>
		</div>

	</div>

	<!-- <style>
		td {
			font-size: 12px;
		}
	</style> -->

	<table id="receipt_items">
		<thead>
			<tr>
				<th style="width:0%;"></th>
				<th style="width:10%;"></th>
				<th style="width:20%;"></th>
				<th style="width:20%;"></th>
				<th style="width:20%;"></th>
				<th style="width:30%;"></th>

			</tr>
		</thead>
		<tbody>
			<?php
			$forth_check = 0;
			$first_check = 0;
			$third_check = 0;
			$second_check = 0;
			$first_check = 0;
			$code_check = 0;
			$arr_ar = array();
			//print_r($arr_ar);
			foreach ($cart as $line => $item) {
				//check for the items to print $can_print. if it's empty, print all,
				//else check if the item_id is included in the print and allow it to be printed 
				//else exclude it
				if (!empty($can_print)) {
					if (!in_array($item['item_id'], $can_print)) {
						continue;
					}
				}

				?>
				<?php if ($third_check < 1) { ?>
					<b>&nbsp; &nbsp; Investigation:</b>
				<?php
						$third_check += 1;
					}
					?>
				<?php

					$arr_ar[] = $item['test_name'];

					//$cew=array_unique($arr_arg);
					//$ce=$arr_arg+array('name'=>$item['test_name']);
					//array_push($arr_arg,$item['test_name']);
					//$b=array_unique($arr_arg);
					//$b=end($arr_arg);
					//explode($b,',');
					//echo $b;
					//print_r(end(each($ce)));

					?>
				<?php if ($item['test_kind'] == 'special') { ?>
					<?php if ($first_check < 1) { ?>

						<tr>
							<td></td>
							<td>
								<h5><b></b></h5>
							</td>
							<td>
								<h5><b>TEST</b></h5>
							</td>
							<td align="center">
								<h5><b>PATIENT VALUE</b></h5>
							</td>
							<td>
								<h5><b>NORMAL VALUE</b></h5>
							</td>
							<td></td>


						</tr>
					<?php
								$first_check += 1;
							}
							?>
					<?php if ($item['reference'] != 0) { ?>
						<tr>
							<td></td>
							<td><?php echo ($item['test_comment']); ?></td>
							<td><?php echo ($item['extra_name']); ?></td>
							<td align="center"><?php echo ($item['o_name']); ?></td>
							<td><?php echo ($item['h_name']); ?></td>
							<td></td>
						</tr>
					<?php
							}
							?>

				<?php } elseif (strstr($item['test_subgroup'], 'Serology') && ($item['test_name'] != 'Widal Test')) { ?>
					<?php if ($second_check < 1) { ?>

						<tr>
							<td></td>
							<td>
								<h5><b></b></h5>
							</td>
							<td>
								<h5><b>TEST</b></h5>
							</td>
							<td>
								<h5><b>RESULT</b></h5>
							</td>
							<td></td>
							<td></td>


						</tr>
					<?php
								$second_check += 1;
							}
							?>

					<tr>
						<td></td>
						<td><?php echo ($item['test_code']); ?></td>
						<td><?php echo ($item['test_name']); ?></td>
						<td><?php echo ($item['test_comment']); ?></td>
						<td></td>
						<td></td>
					</tr>


				<?php } elseif (strstr($item['test_subgroup'], 'Microbiology') && ($item['test_name'] != 'Urinalysis')) { ?>
					<?php if ($item['reference'] == 0) { ?>

						<tr>
							<td></td>
							<td><b><?php echo ($item['test_code']); ?></b></td>
							<td><b><?php echo ($item['test_name']); ?></b></td>
							<td></td>
							<td> </td>
							<td></td>


						</tr>
						<?php foreach ($cart as $let => $unline) { ?>
							<?php
											if ($unline['reference'] == $item['line']) {
												//if ((strstr($item['test_subgroup'], 'Microbiology') && ($item['test_name'] != 'Urinalysis')) && $unline['reference'] != "0") {

												?>
								<tr>
									<td></td>
									<td></td>
									<td><?php echo ($unline['extra_name']); ?></td>
									<td><?php echo ($unline['test_comment']); ?></td>
									<td></td>
									<td></td>


								</tr>
					<?php
									}
								}
							}
						} elseif (strstr($item['test_subgroup'], 'Blood Group and Genotype')) { ?>


					<?php if ($fifth_check < 1) { ?>

						<tr>
							<td></td>
							<td>
								<h5><b>CODE</b></h5>
							</td>
							<td>
								<h5><b>TEST</b></h5>
							</td>
							<td>
								<h5><b>RESULT</b></h5>
							</td>
							<td>
								<h5><b></b></h5>
							</td>
							<td></td>


						</tr>
					<?php
								$fifth_check += 1;
							}
							?>
					<tr>
						<td></td>
						<td><?php echo ($item['test_code']); ?></td>
						<td><?php echo ($item['test_name']); ?></td>
						<td><?php echo ($item['test_comment']); ?></td>
						<td></td>
						<td></td>


					</tr>

				<?php
					} else { ?>
					<?php if (strstr($item['test_name'], 'Urinalysis')) { ?>

						<?php if ($item['reference'] == 0) { ?>

							<tr>
								<td></td>
								<td><b><?php echo ($item['test_code']); ?></b></td>
								<td><b><?php echo ($item['test_name']); ?></b></td>
								<td></td>
								<td> </td>
								<td></td>


							</tr>
							<?php foreach ($cart as $let => $unline) { ?>
								<?php
													if ($unline['reference'] == $item['line']) {
														//if (strstr($item['test_name'], 'Urinalysis') && $unline['reference'] != "0") {
														?>
									<tr>
										<td></td>
										<td></td>
										<td><?php echo ($unline['extra_name']); ?></td>
										<td><?php echo ($unline['o_name']); ?></td>
										<td></td>
										<td></td>


									</tr>
						<?php
											}
										}
									}
								} elseif (strstr($item['test_name'], 'Malaria Parasite')) { ?>


						<tr>
							<td></td>
							<td><b><?php echo ($item['test_code']); ?></b></td>
							<td><b><?php echo ($item['test_name']); ?>:</b></td>
							<td colspan="2"><?php echo ($item['test_comment']); ?></td>
							<td></td>
							<td></td>

						</tr>

					<?php } elseif (strstr($item['test_name'], 'Widal Test')) { ?>

						<?php if ($item['reference'] == 0) { ?>

							<tr>
								<td></td>
								<td>
									<h5><b><?php echo ($item['test_code']); ?></b></h5>
								</td>
								<td>
									<h5><b><?php echo ($item['test_name']); ?></b></h5>
								</td>
								<td></td>
								<td> </td>
								<td></td>


							</tr>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td align="center">
									<h5><b>O</b></h5>
								</td>
								<td>
									<h5><b>H</b></h5>
								</td>
								<td></td>


							</tr>
							<?php foreach ($cart as $let => $unline) { ?>
								<?php
													if ($unline['reference'] == $item['line']) {
														//if (trim($item['test_name']) == 'Widal Test' && $unline['reference'] != "0") {
														?>
									<tr>
										<td></td>
										<td></td>
										<td><?php echo ($unline['extra_name']); ?></td>
										<td align="center"><?php echo ($unline['o_name']); ?></td>
										<td><?php echo ($unline['h_name']); ?></td>
										<td></td>


									</tr>
							<?php
												}
											}
											?>
							<tr>
								<td></td>
								<td colspan="2"> ≥ 1/80 is Significant titre</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						<?php
									}
								} elseif (strstr($item['test_name'], 'Full Blood Count(Pediatrics)') || strstr($item['test_name'], 'Full Blood Count')) { ?>

						<?php if ($item['reference'] == 0) { ?>

							<tr>
								<td></td>
								<td><b><?php echo ($item['test_code']); ?></b></td>
								<td><b><?php echo ($item['test_name']); ?></b></td>
								<td></td>
								<td> </td>
								<td></td>


							</tr>
							<tr>
								<td></td>
								<td></td>
								<td>
									<h5><b>Patient Value</b></h5>
								</td>
								<td>
									<h5><b>Units</b></h5>
								</td>
								<td>
									<h5><b>Normal value</b></h5>
								</td>
								<td></td>


							</tr>
							<?php foreach ($cart as $let => $unline) { ?>
								<?php
													if ($unline['reference'] == $item['line']) {
														//if ((strstr($item['test_name'], 'Full Blood Count(Pediatrics)') || strstr($item['test_name'], 'Full Blood Count')) && $unline['reference'] != "0") {



														?>
									<tr>
										<td></td>
										<td><?php echo ($unline['extra_name']); ?></td>
										<td><?php echo ($unline['test_comment']); ?></td>
										<td><?php echo ($unline['o_name']); ?></td>
										<td><?php echo ($unline['h_name']); ?></td>
										<td></td>


									</tr>
						<?php
											}
										}
									}
								} else { ?>



						<?php if ($code_check < 1) { ?>

							<tr>
								<td></td>
								<td>
									<h5><b>CODE</b></h5>
								</td>
								<td>
									<h5><b>TEST</b></h5>
								</td>
								<td align="center">
									<h5><b>PATIENT VALUE</b></h5>
								</td>
								<td>
									<h5><b>NORMAL VALUE</b></h5>
								</td>
								<td></td>


							</tr>
						<?php
										$code_check += 1;
									}
									?>
						<tr>
							<td></td>
							<td><?php echo ($item['test_code']); ?></td>
							<td><?php echo ($item['test_name']); ?></td>
							<td align="center"><?php echo ($item['test_comment']); ?></td>
							<td><?php echo wordwrap($item['test_unit'], 75, '<br/>'); ?></td>
							<td></td>


						</tr>


			<?php
					}
				}
			}
			$sten_array = array_unique($arr_ar);
			for ($i = 0; $i < count($sten_array); $i++) {
				//echo $sten_array[$i].'/';
			}
			echo implode("/ ", $sten_array);
			?>

		</tbody>


	</table>




</div>