<?php
class Lpo extends CI_Model
{
	public function get_info($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->join('people', 'people.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->get();
	}

	public function get_receiving_by_reference($reference)
	{
		$this->db->from('receivings');
		$this->db->where('reference', $reference);

		return $this->db->get();
	}

	public function is_valid_receipt($receipt_receiving_id)
	{
		if (!empty($receipt_receiving_id)) {
			//RECV #
			$pieces = explode(' ', $receipt_receiving_id);

			if (count($pieces) == 2 && preg_match('/(RECV|KIT)/', $pieces[0])) {
				return $this->exists($pieces[1]);
			} else {
				return $this->get_receiving_by_reference($receipt_receiving_id)->num_rows() > 0;
			}
		}

		return FALSE;
	}

	public function exists($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id', $receiving_id);

		return ($this->db->get()->num_rows() >= 1);
	}
	public function transfer_exists($transfer_id)
	{
		$this->db->from('item_transfer');
		$this->db->where('transfer_id', $transfer_id);

		return ($this->db->get()->num_rows() >= 1);
	}

	public function update($receiving_data, $receiving_id)
	{
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->update('receivings', $receiving_data);
	}
	public function updatee($receiving_data, $receiving_id)
	{
		$this->db->where('item_id', $receiving_id);

		return $this->db->update('items', $receiving_data);
	}

	public function save($items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $lpo_id = FALSE)
	{

		if (count($items) == 0) {
			return -1;
		}

		$receivings_data = array(
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $this->Supplier->exists($supplier_id) ? $supplier_id : NULL,
			'employee_id' => $employee_id,
			'payment_type' => $payment_type,
			'comment' => $comment,
			'reference' => $reference
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('lpos', $receivings_data);
		$lpo_id = $this->db->insert_id();

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$receivings_items_data = array(
				'lpo_id' => $lpo_id,
				'item_id' => $item['item_id'],
				'line' => $item['line'],
				'description' => $item['description'],
				'serialnumber' => $item['serialnumber'],
				'quantity_purchased' => $item['quantity'],
				'receiving_quantity' => $item['receiving_quantity'],
				'discount_percent' => $item['discount'],
				'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
				'item_unit_price' => $cur_item_info->unit_price,
				'item_location' => $item['item_location']
			);

			$this->db->insert('lpo_items', $receivings_items_data);

			// $items_received = $item['quantity'];

			// $item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

			//prepare batch info
			// $expiry_data = array();
			// if ($item['batch_no'] != '' && $item['expiry'] != '') {
			// 	$expiry_data['item_id'] = $item['item_id'];
			// 	$expiry_data['batch_no'] = $item['batch_no'];
			// 	$expiry_data['location_id'] = $item['item_location'];
			// 	$expiry_data['expiry'] = $item['expiry'];
			// 	$expiry_data['quantity'] = $items_received;
			// }

			//Update stock quantity
            //// don't update items as this is just an LPO
			// $this->Item_quantity->save(array(
			// 	'quantity' => $item_quantity->quantity + $items_received, 'item_id' => $item['item_id'],
			// 	'location_id' => $item['item_location'],
			// ), $item['item_id'], $item['item_location'], $expiry_data);

            // $recv_remarks = 'LPO ' . $lpo_id;
            
            
            //no need to log receiving items
			// $inv_data = array(
			// 	'trans_date' => date('Y-m-d H:i:s'),
			// 	'trans_items' => $item['item_id'],
			// 	'trans_user' => $employee_id,
			// 	'trans_location' => $item['item_location'],
			// 	'trans_comment' => $recv_remarks,
			// 	'trans_inventory' => $items_received,
			// 	'trans_remaining' => $item_quantity->quantity + $items_received
			// );

			// $this->Inventory->insert($inv_data);


			//$supplier = $this->Supplier->get_info($supplier_id);

			//update the item cost price
            //update the unit price because the cost price might have changed
            
            //// don't update items as this is just an LPO

			// $unit_price_markup = floatval($this->CI->config->item('unit_price_markup'));
			// $wholesale_price_markup = floatval($this->CI->config->item('wholesale_price_markup'));
			// $unit_price = $cur_item_info->unit_price;
			// $whole_price = $cur_item_info->whole_price;
			// $pack = (int) $cur_item_info->pack;
			// if ($unit_price_markup > 0) {
			// 	$unit_price = $unit_price_markup *  $item['price'];
			// }
			// if ($wholesale_price_markup > 0 && $pack > 0) {
			// 	$whole_price = $wholesale_price_markup *  $item['price'] * $pack;
			// }
			// $items_data = array(
			// 	'unit_price' => $unit_price,
			// 	'cost_price' => $item['price'],
			// 	'whole_price' => $whole_price,
            // );
            
			// $this->Item->save($items_data, $item['item_id']);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}


		return $lpo_id;
	}

	public function delete_list($receiving_ids, $employee_id, $update_inventory = TRUE)
	{
		$success = TRUE;

		// start a transaction to assure data integrity
		$this->db->trans_start();

		foreach ($receiving_ids as $receiving_id) {
			$success &= $this->delete($receiving_id, $employee_id, $update_inventory);
		}

		// execute transaction
		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function delete($receiving_id, $employee_id, $update_inventory = TRUE)
	{
		//Delete is currently disabled until properly resolved.
		//;but how it currently works is that the items are deleted, quantity received are reduced from the stock
		//if the receiving mode was receiving and quantity are added to the stock if the receiving mode was return
		return false;
		// start a transaction to assure data integrity
		$this->db->trans_start();

		if ($update_inventory) {
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_receiving_items($receiving_id)->result_array();

			foreach ($items as $item) {
				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
				// create query to update inventory tracking
				$inv_data = array(
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_comment' => 'Deleting receiving RECV ' . $receiving_id,
					'trans_location' => $item['item_location'],
					'trans_inventory' => $item['quantity_purchased'] * -1,
					'trans_remaining' => ($item_quantity - $item['quantity_purchased'])
				);
				// update inventory
				$this->Inventory->insert($inv_data);

				// update quantities
				$this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased'] * -1);
			}
		}

		// delete all items
		$this->db->delete('receivings_items', array('receiving_id' => $receiving_id));
		// delete sale itself
		$this->db->delete('receivings', array('receiving_id' => $receiving_id));

		// execute transaction
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function get_receiving_items($receiving_id)
	{
		$this->db->from('receivings_items');
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->get();
	}

	public function get_supplier($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id', $receiving_id);

		return $this->Supplier->get_info($this->db->get()->row()->supplier_id);
	}

	public function get_payment_options()
	{
		return array(
			$this->lang->line('sales_cash') => $this->lang->line('sales_cash'),
			$this->lang->line('sales_check') => $this->lang->line('sales_check'),
			$this->lang->line('sales_debit') => $this->lang->line('sales_debit'),
			$this->lang->line('sales_credit') => $this->lang->line('sales_credit')
		);
	}

	/*
	We create a temp table that allows us to do easy report/receiving queries
	*/
	public function create_temp_table(array $inputs)
	{
		if (empty($inputs['receiving_id'])) {
			if (empty($this->config->item('date_or_time_format'))) {
				$where = 'WHERE DATE(receiving_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			} else {
				$where = 'WHERE receiving_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		} else {
			$where = 'WHERE receivings_items.receiving_id = ' . $this->db->escape($inputs['receiving_id']);
		}

		/*if (isset($inputs['employee_id'])) {
			if ($inputs['employee_id'] != 'all') {
				$where .= ' AND employee_id = ' . $inputs['employee_id'];
			}
		}*/

		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('receivings_items_temp') .
				' (INDEX(receiving_date), INDEX(receiving_time), INDEX(receiving_id))
			(
				SELECT 
					MAX(DATE(receiving_time)) AS receiving_date,
					MAX(receiving_time) AS receiving_time,
					receivings_items.receiving_id,
					MAX(comment) AS comment,
					MAX(item_location) AS item_location,
					MAX(reference) AS reference,
					MAX(payment_type) AS payment_type,
					MAX(employee_id) AS employee_id, 
					items.item_id,
					MAX(receivings.supplier_id) AS supplier_id,
					MAX(quantity_purchased) AS quantity_purchased,
					MAX(receivings_items.receiving_quantity) AS quantity_ordered,
					MAX(item_cost_price) AS item_cost_price,
					MAX(item_unit_price) AS item_unit_price,
					MAX(discount_percent) AS discount_percent,
					receivings_items.line,
					
					MAX(serialnumber) AS serialnumber,
					MAX(receivings_items.description) AS description,
					MAX(item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) AS subtotal,
					MAX(item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) AS total,
					MAX((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) - (item_cost_price * quantity_purchased)) AS profit,
					MAX(item_cost_price * quantity_purchased) AS cost
				FROM ' . $this->db->dbprefix('receivings_items') . ' AS receivings_items
				INNER JOIN ' . $this->db->dbprefix('receivings') . ' AS receivings
					ON receivings_items.receiving_id = receivings.receiving_id
				INNER JOIN ' . $this->db->dbprefix('items') . ' AS items
					ON receivings_items.item_id = items.item_id
				' . "
				$where
				" . '
				GROUP BY receivings_items.receiving_id, items.item_id, receivings_items.line
			)'
		);
	}
	public function create_transfer_temp_table(array $inputs)
	{
		if (empty($inputs['transfer_id'])) {
			if (empty($this->config->item('date_or_time_format'))) {
				$where = 'WHERE DATE(transfer_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			} else {
				$where = 'WHERE transfer_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		} else {
			$where = 'WHERE items_push.transfer_id = ' . $this->db->escape($inputs['transfer_id']);
		}

		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('items_transfers_temp') .
				' (INDEX(transfer_date), INDEX(transfer_time), INDEX(transfer_id))
			(
				SELECT 
					MAX(DATE(transfer_time)) AS transfer_date,
					MAX(transfer_time) AS transfer_time,
					items_push.transfer_id,
					
					MAX(item_location) AS item_location,
					MAX(employee_id) AS employee_id, 
					items.item_id,
					MAX(receivings.supplier_id) AS supplier_id,
					MAX(quantity_purchased) AS quantity_purchased,
					MAX(receivings_items.receiving_quantity) AS receiving_quantity,
					MAX(item_cost_price) AS item_cost_price,
					MAX(item_unit_price) AS item_unit_price,
					MAX(discount_percent) AS discount_percent,
					receivings_items.line,
					MAX(serialnumber) AS serialnumber,
					MAX(receivings_items.description) AS description,
					MAX(item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) AS subtotal,
					MAX(item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) AS total,
					MAX((item_unit_price * quantity_purchased - item_unit_price * quantity_purchased * discount_percent / 100) - (item_cost_price * quantity_purchased)) AS profit,
					MAX(item_cost_price * quantity_purchased) AS cost
				FROM ' . $this->db->dbprefix('items_push') . ' AS items_push
				INNER JOIN ' . $this->db->dbprefix('receivings') . ' AS receivings
					ON receivings_items.receiving_id = receivings.receiving_id
				INNER JOIN ' . $this->db->dbprefix('items') . ' AS items
					ON receivings_items.item_id = items.item_id
				' . "
				$where
				" . '
				GROUP BY receivings_items.receiving_id, items.item_id, receivings_items.line
			)'
		);
	}

	public function get_all_receivings($search = '', $limit = 0, $offset = 0, $sort = 'receivings.receiving_id', $order = 'desc', $filters)
	{
		$this->db->from('receivings');
		$this->db->select('receivings.receiving_id as receiving_id');

		$this->db->select('receivings.reference as reference');
		$this->db->select('receivings.payment_type as payment_type');
		$this->db->select('receivings.receiving_time as receiving_time');
		//$this->db->select('suppliers.company_name as company_name');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = receivings.employee_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('receivings.reference', $search);
			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);
			$this->db->or_like('suppliers.company_name', $search);
			$this->db->or_like('receivings.receiving_id', $search);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(receiving_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		} else {
			$this->db->where('receiving_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if ($filters['employee_id'] != "all") {

			$this->db->where("employee_id", $filters['employee_id']);
		}

		$this->db->order_by($sort, $order);
		if ($limit > 0) {
			$this->db->limit($limit, $offset);
		}
		return $this->db->get();
	}
	public function get_all_transfers($search = '', $limit = 0, $offset = 0, $sort = 'item_transfer.transfer_time', $order = 'desc', $filters)
	{
		$this->db->from('item_transfer');
		$this->db->select('item_transfer.transfer_id as transfer_id');
		$this->db->select('item_transfer.transfer_time as transfer_time');

		$this->db->select('stock_locations.location_name as stock_location');

		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');

		$this->db->join('people', 'people.person_id = item_transfer.employee_id', 'LEFT');
		$this->db->join('stock_locations', 'stock_locations.location_id = item_transfer.request_to_branch_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();

			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);

			$this->db->or_like('item_transfer.transfer_id', $search);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(transfer_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		} else {
			$this->db->where('item_transfer_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}
		if ($filters['employee_id'] != "all") {
			$this->db->where("employee_id", $filters['employee_id']);
		}

		$this->db->order_by($sort, $order);
		if ($limit > 0) {
			$this->db->limit($limit, $offset);
		}
		return $this->db->get();
	}

	public function get_all_receivings_count($search = '',  $filters)
	{
		$this->db->from('receivings');
		$this->db->select('receivings.receiving_id as receiving_id');
		$this->db->select('receivings.reference as reference');
		$this->db->select('receivings.receiving_time as receiving_time');
		$this->db->select('receivings.payment_type as payment_type');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = receivings.employee_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('receivings.reference', $search);
			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);
			$this->db->or_like('suppliers.company_name', $search);
			$this->db->or_like('receivings.receiving_id', $search);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(receiving_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		} else {
			$this->db->where('receiving_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function get_receiving_by_receiving_id($id)
	{
		$this->db->from('receivings');
		$this->db->select('receivings.*');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = receivings.employee_id', 'LEFT');
		$this->db->where('receivings.receiving_id', $id);
		return $this->db->limit(1)->get();
	}
	public function get_transfer_by_transfer_id($id)
	{
		$this->db->from('item_transfer');
		$this->db->select('item_transfer.*');

		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->select('stock_locations.location_name as stock_location');
		$this->db->join('people', 'people.person_id = item_transfer.employee_id', 'LEFT');
		$this->db->join('stock_locations', 'stock_locations.location_id = request_to_branch_id', 'LEFT');
		$this->db->where('item_transfer.transfer_id', $id);
		return $this->db->limit(1)->get();
	}

	public function get_receiving_items_data_by_receiving_id($id)
	{
		$this->db->from('receivings_items');
		$this->db->select('receivings_items.*');
		$this->db->select('items.*');
		$this->db->select('stock_locations.*');
		$this->db->where('receivings_items.receiving_id', $id);
		$this->db->join('items', 'items.item_id = receivings_items.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = receivings_items.item_location');
		return $this->db->get();
	}
	public function get_transfer_items_data_by_transfer_id($id)
	{
		$this->db->from('items_push');
		$this->db->select('items_push.*');
		$this->db->select('items.*');
		$this->db->select('stock_locations.*');
		$this->db->where('items_push.transfer_id', $id);
		$this->db->join('items', 'items.item_id = items_push.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = items_push.request_from_branch_id');
		return $this->db->get();
	}
}
