<?php $this->load->view("partial/header"); ?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css" />




<?php /*foreach($invoice as $row=>$value){
		echo $value['item_id'];
	}*/ ?>
<div class="content-page">
	<!-- Start content -->
	<div class="content">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-4"></div>
					<div class="col-xs-4">
						<h3>Result Sheet</h3>
					</div>
					<?php //print_r($cart);
					?>
					<div class="col-xs-1">
						<a href='<?php echo site_url("laboratory/lab_result_saved"); ?>'><button class='btn btn-info btn-sm' id="saveButton">
								Save Result
							</button></a></div>

					<div class="col-xs-1"><a href='<?php echo site_url("laboratory/lab_result_receipt"); ?>'><button class='btn btn-info btn-sm' id="submitButton">
								Submit
							</button></a></div>
				</div>



			</div>

		</div>

		<?php //print_r($cart)
		?>
		<br>
		<table id="table">
			<thead>
				<tr>
					<th width="8%"></th>
					<th width="14%"></th>
					<th width="18%"></th>
					<th width="10%"></th>
					<th width="18%"></th>
					<th width="10%"></th>
					<th width="7%"></th><!-- for buttons -->


				</tr>
			</thead>
			<tbody>
				<?php $tabIndex = 1;
				foreach ($cart as $row => $value) { ?>
					<?php if (strstr($value['test_kind'], 'special')) { ?>
						<?php if ($value['reference'] == 0) { ?>
							<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal ajax-form', 'data-line' => $row, 'data-id' => 'cart_' . $row)); ?>
							<tr>
								<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
							</tr>

							<tr>
								<td></td>
								<td>
									<h4><b><?php echo $value['test_name']; ?></b></h4>
								</td>
								<td></td>




								<td></td>
								<td></td>



								<td><?php //echo form_input(array('name' => 'batch', 'data-id' => 'batch1', 'class' => 'input-sm', 'width=>5px', 'placeholder' => 'Add')); 
												?>
									<?php echo form_hidden('test_name', $value['test_name']); ?>
									<?php echo form_hidden('test_comment', $value['test_comment']); ?>
									<?php echo form_hidden('extra_name', $value['extra_name']); ?>
									<?php echo form_hidden('o_name', $value['o_name']); ?>
									<?php echo form_hidden('h_name', $value['h_name']); ?>
									<?php echo form_hidden('reference', $value['reference']); ?>
									<?php echo form_hidden('line', $value['line']); ?>
								</td>
								<td>

								</td>

							</tr>
							<tr>
								<td></td>
								<td>
									<h4>Test Code</h4>
								</td>
								<td align="center">
									<h4>Test Name</h4>
								</td>
								<td>
									<h4>Patient Value</h4>
								</td>
								<td align="center">
									<h4>Normal Value</h4>
								</td>
								<td></td>
								<td></td>
							</tr>
							<?php echo form_close(); ?>
							<?php

										//Get all the indexes in an array, which will be stringify and passed
										//to the ajax button as data
										//Also get the last index, which will be used to show the ajax button only
										//for the last item for this test
										$lines = array();
										$last_line = 0;
										foreach ($cart as $lett => $unlinenn) {
											if ($unlinenn['reference'] == $value['line']) {
												$lines[] = '' . $lett;
												$last_line = $lett;
											}
										}
										if (count($lines) == 1) {
											$lines[] = "A";
										}
										?>
							<?php foreach ($cart as $let => $unline) { ?>
								<?php if ($unline['reference'] == $value['line']) {

													?>
									<?php echo form_open($controller_name . "/edit_item/$let", array('class' => 'form-horizontal ajax-form', 'data-line' => $let, 'data-id' => 'cart_' . $let)); ?>
									<tr>
										<td><?php echo anchor($controller_name . "/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
										<td><?php echo form_input(
																	array(
																		'name' => 'test_comment' . $let,
																		'id' => 'test_comment' . $let,
																		'class' => 'form-control input-sm',
																		'value' => $unline['test_comment']
																	)
																); ?>
										</td>
										<td><?php echo form_input(
																	array(
																		'name' => 'extra_name' . $let,
																		'id' => 'extra_name' . $let,
																		'class' => 'form-control input-sm',
																		'value' => $unline['extra_name']
																	)
																); ?>
										</td>
										<td><?php echo form_input(
																	array(
																		'name' => 'o_name' . $let,
																		'data-id' => 'o_name' . $let,
																		'class' => 'form-control input-sm',
																		'value' => $unline['o_name'],
																		'tabindex' => $tabIndex++,
																		'data-datalist' => $unline['test_comment'] . 'o_name' . $unline['item_id']
																	)
																); ?>
											<?php echo form_hidden('test_name', $unline['test_name']); ?>
											<?php echo form_hidden('item_id', $unline['item_id']); ?>
											<?php echo form_hidden('reference' . $let, $unline['reference']); ?>
											<?php echo form_hidden('line' . $let, $unline['line']); ?>
										</td>





										<td><?php echo form_input(
																	array(
																		'name' => 'h_name' . $let,
																		'id' => 'h_name' . $let,
																		'class' => 'form-control input-sm',
																		'value' => $unline['h_name']
																	)
																); ?>

										</td>
										<td>
											<?php
																//the real button should be shown only on the last iteration


																if ($let == $last_line) {
																	echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';
																} else {
																	echo '';
																}
																?>
										</td>
									</tr>

						<?php
											echo form_close();
										}
									}
								}
								?>

					<?php } elseif (strstr($value['test_subgroup'], 'Serology') && $value['test_name'] != 'Widal Test') {

							//Get all the indexes in an array, which will be stringify and passed
							//to the ajax button as data
							//Also get the last index, which will be used to show the ajax button only
							//for the last item for this test
							$lines = array();
							$lines[] = $row;
							$lines[] = "A";

							?>
						<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal ajax-form', 'data-line' => $row, 'data-id' => 'cart_' . $row)); ?>
						<tr>
							<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td><?php echo $value['test_code'] ?></td>
							<td>
								<h4><b><?php echo $value['test_name'] ?></b></h4>
							</td>
							<td><?php echo form_input(
											array(
												'name' => 'test_comment' . $row,
												'id' => 'test_comment' . $row,
												'class' => 'form-control input-sm',
												'value' => $value['test_comment'],
												'tabindex' => $tabIndex++,
												'data-datalist' => $value['test_code'] . 'test_comment' . $value['item_id']
											)
										); ?></td>
							<td></td>
							<td>
								<?php echo form_hidden('test_name' . $row, $value['test_name']); ?>
								<?php //echo form_hidden('test_comment' . $row, $value['test_comment']); 
										?>
								<?php echo form_hidden('extra_name' . $row, $value['extra_name']); ?>
								<?php echo form_hidden('o_name' . $row, $value['o_name']); ?>
								<?php echo form_hidden('h_name' . $row, $value['h_name']); ?>
								<?php echo form_hidden('item_id', $value['item_id']); ?>
								<?php echo form_hidden('reference' . $row, $value['reference']); ?>
								<?php echo form_hidden('line' . $row, $value['line']); ?>
							</td>
							<td></td>
							<td>
								<?php
										//the real button should be shown only on the last iteration



										echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';

										?>
							</td>
						</tr>
						<?php
								echo form_close(); ?>
					<?php } elseif (strstr($value['test_subgroup'], 'Microbiology') && $value['test_name'] != 'Urinalysis') { ?>
						<?php if ($value['reference'] == 0) { ?>
							<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal ajax-form', 'data-line' => $row, 'data-id' => 'cart_' . $row)); ?>
							<tr>
								<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<h4><b><?php echo $value['test_name']; ?></b></h4>
								</td>
								<td></td>




								<td></td>


								<td><?php echo form_input(array('name' => 'batch', 'data-id' => 'batch1', 'class' => 'input-sm', 'width=>5px', 'placeholder' => 'Add')); ?>
									<?php echo form_hidden('test_name', $value['test_name']); ?>
									<?php echo form_hidden('item_id', $value['item_id']); ?>
									<?php echo form_hidden('reference', $value['reference']); ?>
									<?php echo form_hidden('line', $value['line']); ?>
								</td>
								<td></td>

							</tr>
							<?php
										echo form_close(); ?>
							<?php

										//Get all the indexes in an array, which will be stringify and passed
										//to the ajax button as data
										//Also get the last index, which will be used to show the ajax button only
										//for the last item for this test
										$lines = array();
										$last_line = 0;
										foreach ($cart as $lett => $unlinenn) {
											if ($unlinenn['reference'] == $value['line']) {
												$lines[] = '' . $lett;
												$last_line = $lett;
											}
										}
										if (count($lines) == 1) {
											$lines[] = "A";
										}
										?>
							<?php foreach ($cart as $let => $unline) { ?>
								<?php if ($unline['reference'] == $value['line']) { ?>
									<?php echo form_open($controller_name . "/edit_item/$let", array('class' => 'form-horizontal ajax-form', 'data-line' => $let, 'data-id' => 'cart_' . $let)); ?>
									<tr>
										<td><?php echo anchor($controller_name . "/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
										<td><?php echo form_input(
																	array(
																		'name' => 'extra_name' . $let,
																		'data-id' => 'extra_name',
																		'class' => 'form-control input-sm',
																		'value' => $unline['extra_name'],
																		'tabindex' => $tabIndex++,
																		'data-datalist' => $unline['test_code'] . 'extra_name' . $unline['item_id']
																	)
																); ?>
										</td>
										<td><?php echo form_input(
																	array(
																		'name' => 'test_comment' . $let,
																		'data-id' => 'test_comment',
																		'class' => 'form-control input-sm',
																		'value' => $unline['test_comment'],
																		'tabindex' => $tabIndex++,
																		'data-datalist' => $unline['test_code'] . 'test_comment' . $unline['item_id']
																	)
																); ?>
											<?php echo form_hidden('test_name', $unline['test_name']); ?>
											<?php echo form_hidden('item_id', $unline['item_id']); ?>
											<?php echo form_hidden('reference' . $let, $unline['reference']); ?>
											<?php echo form_hidden('o_name' . $let, $unline['o_name']); ?>
											<?php echo form_hidden('h_name' . $let, $unline['h_name']); ?>
											<?php echo form_hidden('line' . $let, $unline['line']); ?>
										</td>





										<td colspan="4">
											<?php
																//the real button should be shown only on the last iteration


																if ($let == $last_line) {
																	echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';
																} else {
																	echo '';
																}
																?>
										</td>
									</tr>

						<?php
											echo form_close();
										}
									}
								}
								?>
					<?php } else { ?>
						<tr></tr>
						<?php if (strstr($value['test_name'], 'Urinalysis')) { ?>
							<?php if ($value['reference'] == 0) { ?>
								<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal', 'data-id' => 'cart_' . $row)); ?>
								<tr>
									<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<h4><b><?php echo $value['test_name']; ?></b></h4>
									</td>
									<td></td>




									<td></td>


									<td><?php echo form_input(array('name' => 'batch', 'data-id' => 'batch', 'class' => 'input-sm', 'width=>5px', 'placeholder' => 'Add')); ?>
										<?php echo form_hidden('test_name', $value['test_name']); ?>
										<?php echo form_hidden('item_id', $value['item_id']); ?>
										<?php echo form_hidden('reference', $value['reference']); ?>
										<?php echo form_hidden('line', $value['line']); ?>
									</td>
									<td></td>
									<td></td>


								</tr>


								<?php echo form_close();
												//Get all the indexes in an array, which will be stringify and passed
												//to the ajax button as data
												//Also get the last index, which will be used to show the ajax button only
												//for the last item for this test
												$lines = array();
												$last_line = 0;
												foreach ($cart as $lett => $unlinenn) {
													if ($unlinenn['reference'] == $value['line']) {
														$lines[] = '' . $lett;
														$last_line = $lett;
													}
												}
												if (count($lines) == 1) {
													$lines[] = "A";
												}
												?>
								<?php foreach ($cart as $let => $unline) { ?>
									<?php if ($unline['reference'] == $value['line']) { ?>
										<?php echo form_open($controller_name . "/edit_item/$let", array('class' => 'form-horizontal ajax-form', 'data-line' => $let, 'data-id' => 'cart_' . $let)); ?>
										<tr>
											<td><?php echo anchor($controller_name . "/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
											<td><?php echo form_input(
																			array(
																				'name' => 'extra_name' . $let,
																				'data-id' => 'extra_name',
																				'class' => 'form-control input-sm',
																				'value' => $unline['extra_name'],
																				'tabindex' => $tabIndex++,
																				'data-datalist' => $unline['test_code'] . 'extra_name' . $unline['item_id']
																			)
																		); ?>
											</td>
											<td><?php echo form_input(
																			array(
																				'name' => 'o_name' . $let,
																				'data-id' => 'o_name',
																				'class' => 'form-control input-sm',
																				'value' => $unline['o_name'],
																				'tabindex' => $tabIndex++,
																				'data-datalist' => $unline['test_code'] . 'o_name' . $unline['item_id']
																			)
																		); ?>
												<?php echo form_hidden('test_name', $unline['test_name']); ?>
												<?php echo form_hidden('item_id', $unline['item_id']); ?>
												<?php echo form_hidden('reference' . $let, $unline['reference']); ?>
												<?php echo form_hidden('test_comment' . $let, $unline['test_comment']); ?>
												<?php echo form_hidden('h_name' . $let, $unline['h_name']); ?>
												<?php echo form_hidden('line' . $let, $unline['line']); ?>
											</td>

											<td colspan="4">
												<?php
																		//the real button should be shown only on the last iteration


																		if ($let == $last_line) {
																			echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';
																		} else {
																			echo '';
																		}
																		?>
											</td>

										</tr>

								<?php
														echo form_close();
													}
												}
												?>
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							<?php
										}
									} elseif (strstr($value['test_name'], 'Malaria Parasite')) {


										//Get all the indexes in an array, which will be stringify and passed
										//to the ajax button as data
										//Also get the last index, which will be used to show the ajax button only
										//for the last item for this test
										$lines = array();
										$lines[] = $row;
										$lines[] = 'A';

										?>
							<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal', 'data-id' => 'cart_' . $row)); ?>
							<tr>
								<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<h4><b><?php echo $value['test_name'] ?></b></h4>
								</td>
								<td><?php echo form_input(
													array(
														'name' => 'test_comment' . $row,
														'data-id' => 'test_comment',
														'class' => 'form-control input-sm',
														'value' => $value['test_comment'],
														'tabindex' => $tabIndex++,
														'data-datalist' => $value['test_code'] . 'test_comment' . $value['item_id']
													)
												); ?></td>

								<td><?php echo $value['test_unit'] ?>
									<?php echo form_hidden('test_name', $value['test_name']); ?>
									<?php echo form_hidden('item_id', $value['item_id']); ?>
									<?php echo form_hidden('reference' . $row, $value['reference']); ?>
									<?php echo form_hidden('line' . $row, $value['line']); ?>
									<?php echo form_hidden('o_name' . $row, $value['o_name']); ?>
									<?php echo form_hidden('extra_name' . $row, $value['extra_name']); ?>
									<?php echo form_hidden('h_name' . $row, $value['h_name']); ?>
								</td>

								<td colspan="4">
									<?php
												//the real button should be shown only on the last iteration



												echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';

												?>
								</td>
							</tr>

						<?php
									echo form_close();
								} elseif (strstr($value['test_name'], 'Widal Test')) { ?>
							<?php if ($value['reference'] == 0) { ?>
								<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal', 'data-id' => 'cart_' . $row)); ?>
								<tr>
									<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<h4><b><?php echo $value['test_name']; ?></h4></b>
									</td>
									<td></td>




									<td></td>


									<td><?php echo form_input(array('name' => 'batch', 'data-id' => 'batch1', 'class' => 'input-sm', 'width=>5px', 'placeholder' => 'Add')); ?>
										<?php echo form_hidden('test_name', $value['test_name']); ?>
										<?php echo form_hidden('item_id', $value['item_id']); ?>
										<?php echo form_hidden('reference', $value['reference']); ?>
										<?php echo form_hidden('line', $value['line']); ?>
									</td>
									<td></td>
									<td></td>

								</tr>
								<tr>
									<td></td>
									<td></td>
									<td colspan="2" align="center">
										<h4><b>O</b></h4>
									</td>
									<td colspan="2" align="center">
										<h4><b>H</b></h4>
									</td>
									<td></td>
								</tr>

								<?php echo form_close();

												//Get all the indexes in an array, which will be stringify and passed
												//to the ajax button as data
												//Also get the last index, which will be used to show the ajax button only
												//for the last item for this test
												$lines = array();
												$last_line = 0;
												foreach ($cart as $lett => $unlinenn) {
													if ($unlinenn['reference'] == $value['line']) {
														$lines[] = '' . $lett;
														$last_line = $lett;
													}
												}
												if (count($lines) == 1) {
													$lines[] = "A";
												}
												?>
								<?php foreach ($cart as $let => $unline) { ?>
									<?php if ($unline['reference'] == $value['line']) { ?>
										<?php echo form_open($controller_name . "/edit_item/$let", array('class' => 'form-horizontal ajax-form', 'data-line' => $let, 'data-id' => 'cart_' . $let)); ?>
										<tr>
											<td><?php echo anchor($controller_name . "/delete_test_item/$let", '<span class="glyphicon glyphicon-trash"></span>'); ?></td>
											<td><?php echo form_input(
																			array(
																				'name' => 'extra_name' . $let,
																				'data-id' => 'extra_name',
																				'class' => 'form-control input-sm',
																				'value' => $unline['extra_name'],
																				'tabindex' => $tabIndex++,
																				'data-datalist' => $unline['test_code'] . 'extra_name' . $unline['item_id']
																			)
																		); ?>
											</td>
											<td colspan="2"><?php echo form_input(
																						array(
																							'name' => 'o_name' . $let,
																							'data-id' => 'o_name',
																							'class' => 'form-control input-sm',
																							'value' => $unline['o_name'],
																							'tabindex' => $tabIndex++,
																							'data-datalist' => $unline['test_code'] . 'o_name' . $unline['item_id']
																						)
																					); ?>
												<?php echo form_hidden('test_name', $unline['test_name']); ?>
												<?php echo form_hidden('item_id', $unline['item_id']); ?>
												<?php echo form_hidden('reference' . $let, $unline['reference']); ?>
												<?php echo form_hidden('test_comment' . $let, $unline['test_comment']); ?>
												<?php echo form_hidden('line' . $let, $unline['line']); ?>
											</td>

											<td colspan="2"><?php echo form_input(
																						array(
																							'name' => 'h_name' . $let,
																							'data-id' => 'h_name',
																							'class' => 'form-control input-sm',
																							'value' => $unline['h_name'],
																							'tabindex' => $tabIndex++,
																							'data-datalist' => $unline['test_code'] . 'h_name' . $unline['item_id']
																						)
																					); ?>
											</td>
											<td>
												<?php
																		//the real button should be shown only on the last iteration


																		if ($let == $last_line) {
																			echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';
																		} else {
																			echo '';
																		}
																		?>
											</td>

										</tr>

								<?php
														echo form_close();
													}
												}
												?>
								<tr>
									<td></td>
									<td colspan="3">>= 1/180 is Significant titre</td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							<?php
										}
									} elseif (strstr($value['test_name'], 'Full Blood Count') || strstr($value['test_name'], 'Full Blood Count(Pediatrics)')) { ?>
							<?php if ($value['reference'] == 0) { ?>
								<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal', 'data-id' => 'cart_' . $row)); ?>
								<tr>
									<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<!-- <td>
										<h5><b><?php echo $value['test_code']; ?></b></h5>
									</td> -->
									<td colspan="2">
										<h4><b><?php echo $value['test_name']; ?></b></h4>
									</td>
									<td></td>




									<td></td>


									<td>
										<?php echo form_hidden('test_name', $value['test_name']); ?>
										<?php echo form_hidden('item_id', $value['item_id']); ?>
										<?php echo form_hidden('reference', $value['reference']); ?>
										<?php echo form_hidden('line', $value['line']); ?>
									</td>
									<td></td>

								</tr>
								<tr>
									<td></td>

									<td align="center">
										<h4><b>Test Name</b></h4>
									</td>
									<td align="center">
										<h4><b>Patient Value</b></h4>
									</td>
									<td align="center">
										<h4><b>Units</b></h4>
									</td>
									<td align="center">
										<h4><b>Normal value</b></h4>
									</td>
									<td colspan="2">

									</td>
								</tr>

								<?php echo form_close();

												//Get all the indexes in an array, which will be stringify and passed
												//to the ajax button as data
												//Also get the last index, which will be used to show the ajax button only
												//for the last item for this test
												$lines = array();
												$last_line = 0;
												foreach ($cart as $lett => $unlinenn) {
													if ($unlinenn['reference'] == $value['line']) {
														$lines[] = '' . $lett;
														$last_line = $lett;
													}
												}
												if (count($lines) == 1) {
													$lines[] = "A";
												}
												?>
								<?php foreach ($cart as $let => $unline) { ?>
									<?php if ($unline['reference'] == $value['line']) { ?>
										<?php echo form_open($controller_name . "/edit_item/$let", array('class' => 'form-horizontal ajax-form', 'data-line' => $let, 'data-id' => 'cart_' . $let)); ?>
										<tr>
											<td></td>
											<td align="center"><?php echo $unline['extra_name']; ?> </td>
											<td align="center"><?php echo form_input(
																							array(
																								'name' => 'test_comment' . $let,
																								'data-id' => 'test_comment',
																								'class' => 'form-control input-sm',
																								'value' => $unline['test_comment'],
																								'tabindex' => $tabIndex++,
																								'data-datalist' => str_replace('%', '', $unline['extra_name']) . 'test_comment' . $unline['item_id']
																							)
																						); ?>
												<?php echo form_hidden('test_name', $unline['test_name']); ?>
												<?php echo form_hidden('extra_name' . $let, $unline['extra_name']); ?>
												<?php echo form_hidden('h_name' . $let, $unline['h_name']); ?>
												<?php echo form_hidden('o_name' . $let, $unline['o_name']); ?>
												<?php echo form_hidden('item_id', $unline['item_id']); ?>
												<?php echo form_hidden('reference' . $let, $unline['reference']); ?>
												<?php echo form_hidden('line' . $let, $unline['line']); ?>
											</td>
											<td align="center"><?php echo $unline['o_name']; ?></td>
											<td align="center"><?php echo $unline['h_name']; ?></td>
											<td colspan="2">
												<?php
																		//the real button should be shown only on the last iteration


																		if ($let == $last_line) {
																			echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '" data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';
																		} else {
																			echo  '';
																		}
																		?>
											</td>
										</tr>

							<?php
													echo form_close();
												}
											}
										}
									} else {
										//Get all the indexes in an array, which will be stringify and passed
										//to the ajax button as data
										//Also get the last index, which will be used to show the ajax button only
										//for the last item for this test

										//to check for last index here is a little differenct since there is no condition like others
										//infact there is no way for the last element, 
										//i will display the it like the malaria test. i.e. one line has a button. we will use 'A' to make the lines an array of 
										//more than one elements so that split can work on it
										$lines = array();
										$lines[] = $row;
										$lines[] = "A";
										?>
							<?php echo form_open($controller_name . "/edit_item/$row", array('class' => 'form-horizontal', 'data-id' => 'cart_' . $row)); ?>
							<tr>
								<td colspan="7" style="border-top:1px solid black;">&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<h4><?php echo $value['test_name'] ?></h4>
								</td>
								<td><?php echo form_input(
													array(
														'name' => 'test_comment' . $row,
														'data-id' => 'test_comment',
														'class' => 'form-control input-sm',
														'value' => $value['test_comment'],
														'tabindex' => $tabIndex++,
														'data-datalist' => $value['test_code'] . 'test_comment' . $value['item_id']
													)
												); ?></td>
								<td></td>
								<td><?php //echo $value['test_unit'] 
												?>
									<?php echo form_hidden('test_name', $value['test_name']); ?>
									<?php echo form_hidden('item_id', $value['item_id']); ?>
									<?php echo form_hidden('reference' . $row, $value['reference']); ?>
									<?php echo form_hidden('line' . $row, $value['line']); ?>
									<?php echo form_hidden('extra_name' . $row, $value['extra_name']); ?>
									<?php echo form_hidden('h_name' . $row, $value['h_name']); ?>
									<?php echo form_hidden('o_name' . $row, $value['o_name']); ?>
								</td>
								<td colspan="2">
									<?php
												//the real button should be shown only on the last iteration



												echo '<button class="btn btn-primary btn-sm update-btn" tabindex="' . $tabIndex++ . '"  data-lets="' . implode(" ", $lines) . '" data-line="' . $unline['line'] . '"  data-let="' . $let . '">
																	Enter
																</button>';

												?>
								</td>
							</tr>
					<?php
								echo form_close();
							}
						}
						?>


				<?php


				}
				?>

			</tbody>
		</table>
		<br>
		<div class="container">
			<div class="row">
				<div class="col-sm-6 offset-sm-3 text-center">
					<!-- <button class="btn btn-sm btn-primary" id='filled-data'>Submit Filled Data</button> -->
				</div>
			</div>
		</div>
		<?php //echo $reference;echo $batch_id;echo $line// print_r($cart);
		?>
	</div>
</div>
</div>



<script>
	$(document).ready(function() {
		dialog_support.init("a.modal-dlg, button.modal-dlg");

		//$('[name="test_comment"],[name="batch"],[name="extra_name"],[name="o_name"],[name="h_name"]').focusout(function() {
		$('[name="batch"]').focusout(function() {
			//$('[name="batch"]').focusout(function() {
			$(this).parents("tr").prevAll("form:first").submit();
		});




		$(".update-btn").click(function() {

			var update_btn = $(this);
			var btn_text = update_btn.html();
			update_btn.html("Please Wait...");
			update_btn.attr("disabled", "disabled");


			//var lett = update_btn.data("let");
			var letts = update_btn.data("lets").split(" ");

			var test_lines = {};
			for (var i = 0; i < letts.length; i++) {

				var lett = letts[i].trim();
				var pars = parseInt(lett);
				if (!pars) { ///for letter elements
					continue;
				}

				//each input text has a name concatenated with the line number. the line number is got from the
				//clicked button
				//create an array for each line
				var each_line = {};
				each_line["let"] = lett;
				each_line["o_name"] = $("[name=o_name" + lett + "]").val();
				each_line["extra_name"] = $("[name=extra_name" + lett + "]").val();
				each_line["h_name"] = $("[name=h_name" + lett + "]").val();
				each_line["test_comment"] = $("[name=test_comment" + lett + "]").val();
				each_line["line"] = $("[name=line" + lett + "]").val();
				each_line["reference"] = $("[name=reference" + lett + "]").val();
				//add the line to the main array for the test
				test_lines[lett] = each_line;
			}

			$.post(

				"laboratory/edit_item_ajax",

				{
					'tests': test_lines,

				},
				function(data) {

					//data is already converted to json
					//console.log(data);
					if (data.status) {
						$(update_btn).removeAttr("disabled");
						$(update_btn).html(data.message);
						setTimeout(function() {
							$(update_btn).html(btn_text);
						}, 4000);
					} else {
						$(update_btn).removeAttr("disabled");
						$(update_btn).html(data.message);
						setTimeout(function() {
							$(update_btn).html(btn_text);
						}, 4000);

					}
				}, 'json');
			// error:function(msg){
			// 	console.log(msg+' yes');
			// 	$(update_btn).removeAttr("disabled");
			// 		$(update_btn).html('Failed');
			// 		setTimeout(function(){
			// 			$(update_btn).html(btn_text);
			// 		},4000);
			// }




		});

		//loop through all input and attach datalist appropriately based on the input with datalist data attribute
		$("input").each(function(index) {
			var ele = $(this); //this is actually all similar elements in  a test
			if (ele.data('datalist')) {
				var eleName = ele.data('datalist')
				//fetch the array from localstorage
				var storeItem = localStorage.getItem(eleName, null);

				if (storeItem) {
					var storeItems = JSON.parse(storeItem);

					//var parsedStoreItems = JSON.parse(storeItems);
					//create a datalist and put attach the item
					var dl = document.createElement('datalist');

					dl.id = ele.attr('name'); //we want to use the name of the element since it's unique for ids
					//create option for the datalist
					//if the storeItems is an array, use loop to create and append the options
					for (var k = 0; k < storeItems.length; k++) {
						var option = document.createElement('option');
						option.value = storeItems[k];
						dl.appendChild(option);
					}
					ele.attr('list', ele.attr('name')); //linking the input list attribute to the id of the associated datlist
					var body = document.getElementsByTagName('body')[0];
					body.appendChild(dl);

				}
			}
		});

		//list to input with datalist and put whatever they have inside the localstorage
		$("input").on('change', function() {
			var ele = $(this);
			if (ele.data('datalist')) {
				var valu = ele.val().trim();
				var eleName = ele.data('datalist');
				if (valu) {

					var storeItem = localStorage.getItem(eleName, null);
					if (!storeItem) {
						//not existing another element 
						//replace whatever that might have been the localstorage
						var insertItems = [];
						insertItems[0] = valu;
						localStorage.setItem(eleName, JSON.stringify(insertItems));
					} else {
						var storeItems = JSON.parse(storeItem);
						//already existing other element(s)
						//check if this item is alredy in the array
						var existing = false;

						for (var m = 0; m < storeItems.length; m++) {
							if (storeItems[m] === valu) {
								existing = true;
								break;
							}
						}
						if (!existing) {
							//if not existing
							var index = storeItems.length;

							if (index >= 10) {
								//put the new item to the beginning of array and remove the last one
								storeItems.unshift(valu);
								storeItems.pop();
							} else {
								storeItems[index] = valu;
							}
							localStorage.setItem(eleName, JSON.stringify(storeItems));
						}

					}



				}
			}
		});





		$('#addButton').click(function() {
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");


		});
		$(document).on('click', '.update', function() {
			var user_id = $(this).attr("id");
			$('#userModal').modal('show');
			$('#invoice_id').val(user_id);
			$('.modal-title').text("Process");
			$('#action').val("Add");
			/*$.post('<?php echo site_url("laboratory/view"); ?>', {user: user_id},function(){
				$('#userModal').modal('show');
				$('.modal-title').text("Edit");
				$('#test_code').val("Hello");
				$('#test_name').val("<?php echo $test_info->test_name; ?>");
				$('#action').val("Edit");
				$('#operation').val("Edit");
			});*/

		});
		/*$('#addButton').click(function(){
			
			$('#user_form')[0].reset();
			$('.modal-title').text("Add User");
			$('#action').val("Add");
			$('#operation').val("Add");
			
		});*/





	});

	function objectifyForm(formArray) { //serialize data function

		var returnArray = {};
		for (var i = 0; i < formArray.length; i++) {
			returnArray[formArray[i]['name']] = formArray[i]['value'];
		}
		return returnArray;
	}
</script>

<div id="userModal" class="modal fade">
	<div class="modal-dialog">
		<?php echo form_open('Account/lab_account_sales', array('id' => 'item_form ajax-form', 'data-line' => $let, 'enctype' => 'multipart/form-data')); ?>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Add Test</h4>
			</div>
			<div class="modal-body">
				<label>Enter Test Code</label>
				<input type="text" name="invoice_id" id="invoice_id" class="form-control" />
				<label>Enter Name</label>
				<input type="text" name="test_name" id="test_name" class="form-control" />
				<label>Choose Payment</label>
				<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, array('dadta-id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit')); ?>
			</div>
			<div class="modal-footer">

				<input type="hidden" name="operation" id="operation" />
				<input type="submit" name="action" id="action1" class="btn btn-success" value="Submit" />
			</div>

		</div>

		<?php echo form_close(); ?>
	</div>

</div>
<?php $this->load->view("partial/footer"); ?>