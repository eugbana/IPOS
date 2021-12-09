<table id="suspended_receivings_table" class="table table-striped table-hover">
	<thead>
		<tr bgcolor="#CCC">
			<th>Receiving ID</th>
			<th>Date</th>
			<th>Total Amount</th>
			<th><?php echo $this->lang->line('sales_unsuspend_and_delete'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($suspended_receivings as $suspended_receiving)
		{
		?>
			<tr>
				<td><?php echo $suspended_receiving['receiving_id'];?></td>
				<td><?php echo date($this->config->item('dateformat'), strtotime($suspended_receiving['receiving_time']));?></td>
				<td><?php echo to_currency($suspended_receiving['price']);?></td>
				<td>
					<?php echo form_open('receivings/unsuspend');
						echo form_hidden('suspended_receiving_id', $suspended_receiving['suspended_receiving_id']);
					?>
						<input type="submit" name="submit" value="<?php echo $this->lang->line('sales_unsuspend'); ?>" id="submit" class="btn btn-primary btn-xs pull-right">
					<?php echo form_close(); ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
