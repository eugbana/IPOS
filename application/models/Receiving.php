<?php
class Receiving extends CI_Model
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

	public function stock_exists($stock_id)
	{
		$this->db->from('stock_intakes');
		$this->db->where('stock_id', $stock_id);

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

	//StockIntake
	public function create_stock_intake($stock_data)
	{
		$this->db->insert('stock_intakes', $stock_data);
		$stock_id = $this->db->insert_id();
		return $stock_id;
	}

	public function update_stock_intake($stock_data, $stock_id)
	{
		$this->db->where('stock_id', $stock_id);

		return $this->db->update('stock_intakes', $stock_data);
	}

	public function end_stock_intake($stock_data, $stock_id)
	{
		$this->db->where('stock_id', $stock_id);

		return $this->db->update('stock_intakes', $stock_data);
	}

	public function get_inprogress_stock_taking()
	{
		$status = 'in-progress';
		//$this->db->cache_on();
		$this->db->from('stock_intakes');
		$this->db->select('stock_intakes.*');
		$this->db->where('status', $status);

		// return $this->db->limit(1)->get();
		return $this->db->get()->row();
	}

	public function check_if_stock_is_received($stock_id)
	{
		$status = 'received';
		//$this->db->cache_on();
		$this->db->from('stock_intakes');
		$this->db->select('stock_intakes.*');
		$this->db->where('stock_id', $stock_id);
		$this->db->where('status', 'received');

		return $this->db->get()->num_rows() > 0 ? TRUE : FALSE;
	}

	public function check_if_stock_is_done($stock_id)
	{
		$status = 'received';
		//$this->db->cache_on();
		$this->db->from('stock_intakes');
		$this->db->select('stock_intakes.*');
		$this->db->where('stock_id', $stock_id);
		$this->db->where('status', 'done');

		return $this->db->get()->num_rows() > 0 ? TRUE : FALSE;
	}

	public function stock_item_exists($item_id, $stock_id)
	{
		//$this->db->cache_on();
		$this->db->from('stock_intakes_items');
		$this->db->where('stock_id', $stock_id);
		$this->db->where('item_id', $item_id);

		// return ($this->db->get()->num_rows() >= 1);
		return $this->db->get()->row();
	}

	public function update_stock_intake_item($stock_item_data, $item_id, $stock_id)
	{
		//$this->db->cache_on();
		$this->db->where('stock_id', $stock_id);
		$this->db->where('item_id', $item_id);

		return $this->db->update('stock_intakes_items', $stock_item_data);
	}

	public function save_stock_taking($items, $employee_id, $stock_id = FALSE)
	{

		if (count($items) == 0) {
			return -1;
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		//get stock taking where the status is currently pending
		$stock_id = $this->get_inprogress_stock_taking()->stock_id;

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info($item['item_id']);
			$cur_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

			//check if item already exists on stock items
			$existing_item = $this->stock_item_exists($item['item_id'], $stock_id);

			// if($existing_item->item_id == $item['item_id'] && $existing_item->batch_no == $item['batch_no'] && $existing_item->expiry == $item['expiry']){
			if ($existing_item->item_id == $item['item_id']) {
				$stock_items_data = array(
					// 'stock_id' => $stock_id,
					// 'item_id' => $item['item_id'],
					// 'line' => $item['line'],
					// 'description' => $item['description'],
					// 'serialnumber' => $item['serialnumber'],
					'quantity_purchased' => $existing_item->quantity_purchased + $item['quantity'],
					// 'receiving_quantity' => 0,
					// 'current_quantity' => $cur_quantity->quantity,
					// 'discount_percent' => $item['discount'],
					// 'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
					// 'item_unit_price' => $cur_item_info->unit_price,
					// 'item_location' => $item['item_location']
				);
				$this->update_stock_intake_item($stock_items_data, $item['item_id'], $stock_id);
			} else {
				$stock_items_data = array(
					'stock_id' => $stock_id,
					'item_id' => $item['item_id'],
					'line' => $item['line'],
					'description' => $item['description'],
					'serialnumber' => $item['serialnumber'],
					'quantity_purchased' => $item['quantity'],
					// 'receiving_quantity' => $item['receiving_quantity'],
					'receiving_quantity' => 0,
					'current_quantity' => $cur_quantity->quantity,
					'discount_percent' => $item['discount'],
					'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
					'item_unit_price' => $cur_item_info->unit_price,
					'item_location' => $item['item_location'],
					'batch_no' => $item['batch_no'],
					'expiry' => $item['expiry']
				);
				$this->db->insert('stock_intakes_items', $stock_items_data);
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $stock_id;
	}

	public function save($receive_status, $items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $receiving_type = "transfer btw branches")
	{

		if (count($items) <= 0) {
			return -1;
		}

		$receivings_data = array(
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $this->Supplier->exists($supplier_id) ? $supplier_id : NULL,
			'employee_id' => $employee_id,
			'payment_type' => $payment_type,
			'comment' => $comment,
			'reference' => $reference,
			'receive_status' => $receive_status,
            'receiving_type' => $receiving_type,
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('receivings', $receivings_data);
		$receiving_id = $this->db->insert_id();
		$total_price = 0;
		$rec_item = [];

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info($item['item_id']);
			$item['cost_price'] = $item['price'];
			$item['full_info'] = $cur_item_info;
			$rec_item[] = $item;
			$receivings_items_data = array(
				'receiving_id' => $receiving_id,
				'item_id' => $item['item_id'],
				'line' => $item['line'],
				'description' => $item['description'],
				'serialnumber' => $item['serialnumber'],
				'quantity_purchased' => $item['quantity'],
				'receiving_quantity' => $item['receiving_quantity'],
				'discount_percent' => $item['discount'],
				// 'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
				'item_cost_price' => $cur_item_info->cost_price, //this is already taking cost_price of items during receivng process
				'item_unit_price' => $cur_item_info->unit_price,
				'item_location' => $item['item_location'],

				//add batch_no and expiry data
				'batch_no' => $item['batch_no']==null?$item['selected_batch']: $item['batch_no'],
				'expiry_date' => $item['expiry']
			);
			$total_price += ($cur_item_info->cost_price * $item['quantity']);
			$this->db->insert('receivings_items', $receivings_items_data);

			//if receive is suspended, don't effect changes on inventory
			if ($receive_status == 0) {

				$items_received = $item['quantity'];
				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

				//prepare batch info
				$expiry_data = array();
				if ($item['batch_no'] != '' && $item['expiry'] != '') {

					$expiry_data['item_id'] = $item['item_id'];
					$expiry_data['batch_no'] = $item['batch_no'];
					$expiry_data['location_id'] = $item['item_location'];
					$expiry_data['expiry'] = $item['expiry'];
					$expiry_data['quantity'] = $items_received;
				}

				//Update stock quantity
				$this->Item_quantity->save(array(
					'quantity' => $item_quantity->quantity + $items_received, 'item_id' => $item['item_id'],
					'location_id' => $item['item_location'],
				), $item['item_id'], $item['item_location'], $expiry_data);

				$recv_remarks = 'RECV ' . $receiving_id;
				$inv_data = array(
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_location' => $item['item_location'],
					'trans_comment' => $recv_remarks,
					'trans_inventory' => $items_received,
					'selling_price' => $cur_item_info->unit_price,
					'trans_remaining' => $item_quantity->quantity + $items_received
				);

				$this->Inventory->insert($inv_data);
				//notify item sale_tracker here if it doesnt exists
				$this->Sale->saveitemtracker($cur_item_info->item_number);

				//$supplier = $this->Supplier->get_info($supplier_id);

				//update the item cost price
				//update the unit price because the cost price might have changed
				$unit_price_markup = floatval($this->CI->config->item('unit_price_markup'));
				$wholesale_price_markup = floatval($this->CI->config->item('wholesale_price_markup'));
				$unit_price = $cur_item_info->unit_price;
				$whole_price = $cur_item_info->whole_price;
				$pack = (int) $cur_item_info->pack;
				if ($unit_price_markup > 0) {
					$unit_price = $unit_price_markup *  $item['price'];
				}
				if ($wholesale_price_markup > 0 && $pack > 0) {
					$whole_price = $wholesale_price_markup *  $item['price'] * $pack;
				}
				$items_data = array(
					'unit_price' => $unit_price,
					// 'cost_price' => $item['price'],
					'cost_price' => $cur_item_info->cost_price,
					'whole_price' => $whole_price,

				);
				$this->Item->save($items_data, $item['item_id']);
			}
		}

		$this->db->trans_complete();

        $employee_info = $this->CI->Employee->get_logged_in_employee_info();
		$branch = $this->Employee->get_branchinfo($employee_info->branch_id);
        $erd_url = ERD_BASE_URL.'/branches/'.$branch->brid.'/receive_items';

//        $receivings_data = array(
//            'receiving_time' => date('Y-m-d H:i:s'),
//            'supplier_id' => $this->Supplier->exists($supplier_id) ? $supplier_id : NULL,
//            'employee_id' => $employee_id,
//            'payment_type' => $payment_type,
//            'comment' => $comment,
//            'reference' => $reference,
//            'receive_status' => $receive_status,
//            'receiving_type' => $receiving_type,
//        );
        $erd_data = [
            "caller"=> "HaslekIsBae",
            "items"=>$rec_item,
            "supplier" => $supplier_id,
            "supplier_name"=>$this->CI->Supplier->get_supplier_name($supplier_id),
            "status" =>$receive_status,
            "brid"=>$branch->brid,
            "total_price" => $total_price,
            "employee" => $employee_info->first_name.' '.$employee_info->last_name,
            'payment_type' => $payment_type,
            'comment' => $comment,
            'reference' => $reference,
            'receive_status' => $receive_status,
            'receiving_type' => $receiving_type,
			'receiving_id'=>$receiving_id,
        ];
        $this->load->library('External_calls');
        $erd_response = External_calls::makeRequest($erd_url,$erd_data,"POST");
        $erd_response_data = [
            "receive_id"=> $receiving_id,
            "receive_reference"=>$reference.' branch: '.$employee_info->branch_id,
            "received_by"=> $employee_info->first_name.' '.$employee_info->last_name
        ];
        $erd_response = json_decode($erd_response,true);
        $erd_response = $erd_response== null ? "":$erd_response;
        if(is_string($erd_response) || $erd_response['status'] != "00"){
            $erd_response_data["response"] = is_string($erd_response)?$erd_response:$erd_response['message'];
            $erd_response_data["status"] = is_string($erd_response)?$erd_response:$erd_response['status'];
        }else{
            $erd_response_data["response"] = $erd_response['message']?$erd_response['message']:$erd_response['error'];
            $erd_response_data["status"] = $erd_response['status'];
        }
        $this->db->insert('erd_receive_calls',$erd_response_data);

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
		return $receiving_id;
	}

	public function save_receiving_from_stock($receive_status, $items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $stock_id = FALSE)
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

		$this->db->insert('receivings', $receivings_data);
		$receiving_id = $this->db->insert_id();

		$status = 'received';
		$stock_save_data = array(
			'status' => $status
		);
		$this->update_stock_intake($stock_save_data, $stock_id);

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$receivings_items_data = array(
				'receiving_id' => $receiving_id,
				'item_id' => $item['item_id'],
				'line' => $item['line'],
				'description' => $item['description'],
				'serialnumber' => $item['serialnumber'],
				'quantity_purchased' => $item['quantity_purchased'],
				'receiving_quantity' => $item['receiving_quantity'],
				// 'quantity_purchased' => $item['receiving_quantity'],
				// 'receiving_quantity' => $item['quantity_purchased'],
				'discount_percent' => $item['discount_percent'],
				'item_cost_price' => $item['item_cost_price'], //this is already taking cost_price of items during receivng process
				'item_unit_price' => $cur_item_info->unit_price,
				'item_location' => $item['item_location']
			);

			$this->db->insert('receivings_items', $receivings_items_data);

			//if receive is suspended, don't effect changes on inventory
			if ($receive_status == 0) {

				$items_received = $item['quantity_purchased'];
				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

				//prepare batch info
				$expiry_data = array();
				if ($item['batch_no'] != '' && $item['expiry'] != '') {

					$expiry_data['item_id'] = $item['item_id'];
					$expiry_data['batch_no'] = $item['batch_no'];
					$expiry_data['location_id'] = $item['item_location'];
					$expiry_data['expiry'] = $item['expiry'];
					$expiry_data['quantity'] = $items_received;
				}

				//Update stock quantity
				// $this->Item_quantity->save(array(
				// 	'quantity' => $item_quantity->quantity + $items_received, 'item_id' => $item['item_id'],
				// 	'location_id' => $item['item_location'],
				// ), $item['item_id'], $item['item_location'], $expiry_data);

				$this->Item_quantity->save(array(
					'quantity' => $items_received, 'item_id' => $item['item_id'],
					'location_id' => $item['item_location'],
				), $item['item_id'], $item['item_location'], $expiry_data);

				$recv_remarks = 'RECV ' . $receiving_id;
				$inv_data = array(
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $item['item_id'],
					'trans_user' => $employee_id,
					'trans_location' => $item['item_location'],
					'trans_comment' => $recv_remarks,
					// 'trans_inventory' => $items_received,
					// 'trans_remaining' => $item_quantity->quantity + $items_received
					'selling_price' => $cur_item_info->unit_price,
					'trans_inventory' => $items_received,
					'trans_remaining' => $items_received
				);

				$this->Inventory->insert($inv_data);


				//$supplier = $this->Supplier->get_info($supplier_id);

				//update the item cost price
				//update the unit price because the cost price might have changed
				$unit_price_markup = floatval($this->CI->config->item('unit_price_markup'));
				$wholesale_price_markup = floatval($this->CI->config->item('wholesale_price_markup'));
				$unit_price = $cur_item_info->unit_price;
				$whole_price = $cur_item_info->whole_price;
				$pack = (int) $cur_item_info->pack;
				if ($unit_price_markup > 0) {
					$unit_price = $unit_price_markup *  $item['item_cost_price'];
				}
				if ($wholesale_price_markup > 0 && $pack > 0) {
					$whole_price = $wholesale_price_markup *  $item['item_cost_price'] * $pack;
				}
				$items_data = array(
					'unit_price' => $unit_price,
					'cost_price' => $item['item_cost_price'],
					'whole_price' => $whole_price,

				);
				$this->Item->save($items_data, $item['item_id']);
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}


		return $receiving_id;
	}

	public function accept_transfer($receive_status, $items, $from_branch, $employee_id, $receiving_id = FALSE)
	{

		if (count($items) == 0) {
			return -1;
		}

		$receivings_data = array(
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => NULL,
			'employee_id' => $employee_id,
			'payment_type' => 'Cash',
			'comment' => 'Transfer',
			'reference' => 0,
			'receive_status' => 0,
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('receivings', $receivings_data);
		$receiving_id = $this->db->insert_id();

		//create an accepted transfer
		$accepted_transfer_data = array(
			'created_at' => date('Y-m-d H:i:s'),
			'receiving_id' => $receiving_id,
			'employee_id' => $employee_id,
			'from_branch' => $from_branch
		);

		$this->db->insert('accepted_transfers', $accepted_transfer_data);
		$accepted_id = $this->db->insert_id();

		// "id": 1,
		// "transfer_request_id": 1,
		// "item_number": "ZOBO",
		// "requested_quantity": 10,
		// "accepted_quantity": 1,
		// "created_at": "2020-11-20T02:11:06.000000Z",
		// "updated_at": "2020-12-17T11:22:38.000000Z"

		$line = 0;
		$location = 2;

		foreach ($items as $line => $item) {
			$line += 1;
			$cur_item_info = $this->Item->get_item_info_by_number($item['item_number']);

			$receivings_items_data = array(
				'receiving_id' => $receiving_id,
				'item_id' => $cur_item_info->item_id,
				'line' => $line,
				'description' => '--',
				'serialnumber' => '--',
				'quantity_purchased' => $item['accepted_quantity'],
				'receiving_quantity' => $item['accepted_quantity'],
				'discount_percent' => '0',
				// 'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
				'item_cost_price' => $cur_item_info->cost_price, //this is already taking cost_price of items during receivng process
				'item_unit_price' => $cur_item_info->unit_price,
				'item_location' => $location
			);

			$this->db->insert('receivings_items', $receivings_items_data);

			$accept_transfers_items_data = array(
				'accepted_transfer_id' => $accepted_id,
				'item_id' => $cur_item_info->item_id,
				'accepted_quantity' => $item['accepted_quantity']
			);
			$this->db->insert('accepted_transfer_items', $accept_transfers_items_data);

			//if receive is suspended, don't effect changes on inventory
			if ($receive_status == 0) {

				$items_received = $item['accepted_quantity'];
				$item_quantity = $this->Item_quantity->get_item_quantity($cur_item_info->item_id, $location);
				$sum = $item_quantity->quantity + $items_received;

				//prepare batch info
				$expiry_data = array();
				if ($item['batch_no'] != '' && $item['expiry'] != '') {

					$expiry_data['item_id'] = $item['item_id'];
					$expiry_data['batch_no'] = ''; //TODO: added batch number
					$expiry_data['location_id'] = $location;
					$expiry_data['expiry'] = ''; //TODO: added expiry date
					$expiry_data['quantity'] = $items_received;
				}

				//Update stock quantity
				$this->Item_quantity->save(array(
					'quantity' => $item_quantity->quantity + $items_received, 'item_id' => $cur_item_info->item_id,
					'location_id' => $location,
				), $cur_item_info->item_id, $location);

				$recv_remarks = 'RECV ' . $receiving_id;
				$inv_data = array(
					'trans_date' => date('Y-m-d H:i:s'),
					'trans_items' => $cur_item_info->item_id,
					'trans_user' => $employee_id,
					'trans_location' => $location,
					'trans_comment' => 'Transfer of Items from ' . $from_branch . ' branch',
					'trans_inventory' => $items_received,
					'selling_price' => $cur_item_info->unit_price,
					'trans_remaining' => $item_quantity->quantity + $items_received
				);

				$this->Inventory->insert($inv_data);
				//notify item sale_tracker here if it doesnt exists
				$this->Sale->saveitemtracker($cur_item_info->item_number);

				//$supplier = $this->Supplier->get_info($supplier_id);

				//update the item cost price
				//update the unit price because the cost price might have changed
				$unit_price_markup = floatval($this->CI->config->item('unit_price_markup'));
				$wholesale_price_markup = floatval($this->CI->config->item('wholesale_price_markup'));
				$unit_price = $cur_item_info->unit_price;
				$cost_price = $cur_item_info->cost_price;
				$whole_price = $cur_item_info->whole_price;
				$pack = (int) $cur_item_info->pack;
				// if ($unit_price_markup > 0) {
				// 	$unit_price = $unit_price_markup *  $item['price'];
				// }
				// if ($wholesale_price_markup > 0 && $pack > 0) {
				// 	$whole_price = $wholesale_price_markup *  $item['price'] * $pack;
				// }
				$items_data = array(
					'unit_price' => $unit_price,
					'cost_price' => $cost_price,
					'whole_price' => $whole_price,
				);

				$this->Item->save($items_data, $cur_item_info->item_id);
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $receiving_id;
	}

	public function save_lpo($items, $supplier_id, $employee_id, $comment, $lpo_id = FALSE)
	{

		if (count($items) == 0) {
			return -1;
		}

		$receivings_data = array(
			'receiving_time' => date('Y-m-d H:i:s'),
			'supplier_id' => $this->Supplier->exists($supplier_id) ? $supplier_id : NULL,
			'employee_id' => $employee_id,
			// 'payment_type' => $payment_type,
			'comment' => $comment,
			// 'reference' => $reference
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
				// 'description' => $item['description'],
				// 'serialnumber' => $item['serialnumber'],
				'quantity_purchased' => 0, //default to zero
				'receiving_quantity' => $item['receiving_quantity'],
				// 'discount_percent' => $item['discount'],
				// 'item_cost_price' => $item['price'], //this is already taking cost_price of items during receivng process
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

	public function get_all_suspended($employee_id = NULL)
	{

		$query = $this->db->query('select '
			. 'receiving_id, receiving_id as suspended_receiving_id, receive_status, receiving_time, SUM(item_cost_price * quantity_purchased) as price from '
			. $this->db->dbprefix('receivings') . ' NATURAL JOIN ' . $this->db->dbprefix('receivings_items')  . ' where receive_status = 1 GROUP BY receiving_id ORDER BY receiving_time DESC');

		// $this->db->group_by('sale_id');

		return $query->result_array();
	}

	/*
	 * This will remove a selected receiving from the receivings table.
	 * This function should only be called for suspended receivings that are being restored to the current cart
	 */
	public function delete_suspended_receiving($receiving_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->delete('receivings_items', array('receiving_id' => $receiving_id));
		$this->db->delete('receivings', array('receiving_id' => $receiving_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
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
					'selling_price' => $item['unit_price'],
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

	public function get_all_receivings($search = '', $limit = 0, $offset = 0, $sort = 'receivings.receiving_id', $order = 'asc', $filters)
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

		// $this->db->order_by($sort, $order);
		$this->db->order_by($sort, 'asc');
		if ($limit > 0) {
			$this->db->limit($limit, $offset);
		}
		return $this->db->get();
	}

	public function get_all_stock_intakes($search = '', $limit = 0, $offset = 0, $sort = 'stock_intakes.stock_id', $order = 'desc', $filters)
	{
		$this->db->from('stock_intakes');
		$this->db->select('stock_intakes.stock_id as stock_id');

		$this->db->select('stock_intakes.title as title');
		$this->db->select('stock_intakes.description as description');
		$this->db->select('stock_intakes.receiving_time as receiving_time');
		$this->db->select('stock_intakes.status as status');
		//$this->db->select('suppliers.company_name as company_name');
		// $this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		// $this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = stock_intakes.employee_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('stock_intakes.title', $search);
			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);
			$this->db->or_like('stock_intakes.description', $search);
			$this->db->or_like('stock_intakes.status', $search);
			$this->db->or_like('stock_intakes.stock_id', $search);
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

	public function get_all_lpos($search = '', $limit = 0, $offset = 0, $sort = 'lpos.lpo_id', $order = 'desc', $filters)
	{
		$this->db->from('lpos');
		$this->db->select('lpos.lpo_id as lpo_id');

		$this->db->select('lpos.reference as reference');
		$this->db->select('lpos.payment_type as payment_type');
		$this->db->select('lpos.receiving_time as receiving_time');
		//$this->db->select('suppliers.company_name as company_name');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->join('suppliers', 'suppliers.person_id = lpos.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = lpos.employee_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('lpos.reference', $search);
			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);
			$this->db->or_like('suppliers.company_name', $search);
			$this->db->or_like('lpos.lpo_id', $search);
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

	// public function get_pending_requests(){
	// 	$curl = curl_init();

	// curl_setopt_array($curl, array(
	//     CURLOPT_URL => "https://toniapharmacy.istrategytech.com/api/getlast_orders",
	//     CURLOPT_RETURNTRANSFER => true,
	//     CURLOPT_ENCODING => "",
	//     CURLOPT_MAXREDIRS => 10,
	//     CURLOPT_TIMEOUT => 0,
	//     CURLOPT_FOLLOWLOCATION => true,
	//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//     CURLOPT_CUSTOMREQUEST => "GET",
	// ));
	// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
	// 	curl_setopt($curl, CURLOPT_TIMEOUT,200);

	// $response = curl_exec($curl);

	// curl_close($curl);

	// $response = json_decode($response,true);
	// }

	public function get_all_transfers($search = '', $limit = 0, $offset = 0, $sort = 'item_transfer.transfer_time', $order = 'desc', $filters)
	{
		$this->db->from('item_transfer');
		$this->db->select('item_transfer.transfer_id as transfer_id');
		$this->db->select('item_transfer.transfer_time as transfer_time');
        $this->db->select('item_transfer.reference');
        $this->db->select('item_transfer.status');

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

	public function get_all_requests($search = '', $limit = 0, $offset = 0, $sort = 'item_request.request_time', $order = 'desc', $filters)
	{
		$this->db->from('item_request');
		$this->db->select('item_request.request_id as request_id');
		$this->db->select('item_request.request_time as request_time');

		$this->db->select('stock_locations.location_name as stock_location');

		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');

		$this->db->join('people', 'people.person_id = item_request.employee_id', 'LEFT');
		$this->db->join('stock_locations', 'stock_locations.location_id = item_request.request_to_branch_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();

			$this->db->or_like('people.first_name', $search);
			$this->db->or_like('people.last_name', $search);

			$this->db->or_like('item_request.request_id', $search);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(request_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		} else {
			$this->db->where('item_request_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
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
	private function curl_request($url,$data){
		
	}

	public function get_pending_requests($branch)
	{

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/" . $branch . "/get_pending",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
		// return [];
	}

	public function get_incoming_transfers($branch)
	{

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl')."/api/transfer/" . $branch . "/incoming_transfers",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
	}

	public function get_pending_items($branch)
	{
		

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl')."/api/items_request/" . $branch . "/get_pending_items",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
	}

	public function get_incoming_transfer_by_id($branch, $id)
	{

		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/" . $branch . "/incoming_transfers/$id",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
	}

	public function get_pending_item_by_id($branch, $id)
	{

		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/items_request/" . $id . "/" . $branch . "/get_item",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
	}

	public function accept_transfers($branch, $id)
	{

	
		$curl = curl_init();
		curl_setopt_array($curl, array(
			// CURLOPT_URL => "http://127.0.0.1:8000/api/transfer/" . $branch . "/incoming_transfers/$id",
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/" . $id . "/" . $branch ."/accept_transfers",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		// print_r($response);
		// die();

		if(empty($response['data'])){
			return false;
		}

		return $response['data'];
	}

	public function accept_items($branch, $id)
	{
		

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/items_request/" . $id . "/" . $branch ."/accept_item",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		// echo "<pre>";
		// print_r($response);
		// echo "</pre>";
		// die();

		return $response['status'];
	}

	public function get_pending_requests_by_id($branch, $id)
	{

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/" . $branch . "/get_pending/$id",
			// CURLOPT_URL => "http://127.0.0.1:8000/api/transfer/garki/get_pending/3",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'][0];
	}

	public function get_global_search($query)
	{
		
		if (empty($query)) {
			return [];
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/global_search/" . $query,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($curl, CURLOPT_TIMEOUT,200);

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		return $response['data'];
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

	public function get_stock_intake_by_stock_id($id)
	{
		$this->db->from('stock_intakes');
		$this->db->select('stock_intakes.*');
		// $this->db->select('suppliers.company_name as company_name');
		// $this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		// $this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = stock_intakes.employee_id', 'LEFT');
		$this->db->where('stock_intakes.stock_id', $id);
		return $this->db->limit(1)->get();
	}

	public function get_lpo_by_lpo_id($id)
	{
		$this->db->from('lpos');
		$this->db->select('lpos.*');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('suppliers.company_name as company_name');
		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->join('suppliers', 'suppliers.person_id = lpos.supplier_id', 'LEFT');
		$this->db->join('people', 'people.person_id = lpos.employee_id', 'LEFT');
		$this->db->where('lpos.lpo_id', $id);
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

	public function get_request_by_request_id($id)
	{
		$this->db->from('item_request');
		$this->db->select('item_request.*');

		$this->db->select('people.first_name as first_name');
		$this->db->select('people.last_name as last_name');
		$this->db->select('stock_locations.location_name as stock_location');
		$this->db->join('people', 'people.person_id = item_request.employee_id', 'LEFT');
		$this->db->join('stock_locations', 'stock_locations.location_id = request_to_branch_id', 'LEFT');
		$this->db->where('item_request.request_id', $id);
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

	public function get_stock_intake_items_data_by_stock_id($id)
	{
		$this->db->from('stock_intakes_items');
		$this->db->select('stock_intakes_items.*');
		$this->db->select('stock_intakes_items.item_cost_price as price');
		$this->db->select('stock_intakes_items.quantity_purchased as quantity');
		$this->db->select('items.*');
		$this->db->select('stock_locations.*');
		$this->db->where('stock_intakes_items.stock_id', $id);
		$this->db->join('items', 'items.item_id = stock_intakes_items.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = stock_intakes_items.item_location');
		return $this->db->get();
	}


	public function get_lpo_items_data_by_lpo_id($id)
	{
		$this->db->from('lpo_items');
		$this->db->select('lpo_items.*, lpo_items.receiving_quantity as quantity_ordered');
		$this->db->select('items.*');
		$this->db->select('stock_locations.*');
		$this->db->where('lpo_items.lpo_id', $id);
		$this->db->join('items', 'items.item_id = lpo_items.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = lpo_items.item_location');
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

	public function get_request_items_data_by_request_id($id)
	{
		$this->db->from('request_items');
		$this->db->select('request_items.*');
		$this->db->select('items.*');
		$this->db->select('stock_locations.*');
		$this->db->where('request_items.request_id', $id);
		$this->db->join('items', 'items.item_id = request_items.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = request_items.request_from_branch_id');
		return $this->db->get();
	}

	/** Get Latest sales filtered by employee, customer, date range */
	public function get_latest_receivings($type, $start_date = null, $end_date = null, $employee_id = "all", $customer_id = "all")
	{



		if ($start_date == null) {
			$start_date = date("Y") . "-" . date("m") . "-01" . " 00:00:00";
		} else {
			$start_date = date("Y-m-d", strtotime($start_date)) . " 00:00:00";
		}
		if ($end_date == null) {
			$end_date = date("Y") . "-" . date("m") . "-" . date("t") . " 00:00:00";
		} else {
			$end_date = date("Y-m-d", strtotime($end_date)) . " 23:59:59";
		}

		$this->db->select('Round(receivings_items.quantity_purchased) as quantity,
			(receivings_items.item_cost_price * receivings_items.quantity_purchased ) as amount,
			receivings.receiving_time as date,
			CONCAT(receivings.reference) as invoice,
			suppliers.company_name as supplier_name,
			employee.person_id as employee_id,
			CONCAT(employee.first_name," ",employee.last_name) as employee_name,
			item.name as item_name
		');

		$this->db->from("receivings_items as receivings_items");

		$this->db->join('receivings as receivings', 'receivings_items.receiving_id = receivings.receiving_id', 'left');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id', 'left');
		$this->db->join('items as item', 'receivings_items.item_id = item.item_id', 'left');
		$this->db->join('suppliers as suppliers', 'receivings.supplier_id = suppliers.person_id', 'left');



		if ($employee_id != "all") $this->db->where("employee_id", $employee_id);
		if ($customer_id != "all") $this->db->where("customer_id", $customer_id);
		$this->db->where("receiving_time >= ", $start_date);
		$this->db->where("receiving_time <= ", $end_date);

		if ($type == "returns") $this->db->where("quantity_purchased < 0");
		else $this->db->where("quantity_purchased > 0");

		$data = $this->db->get();


		return $data->result();
	}
}
