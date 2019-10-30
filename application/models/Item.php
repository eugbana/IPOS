<?php
class Item extends CI_Model
{
	/*
	Determines if a given item_id is an item
	*/
	public function exists($item_id, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		if (ctype_digit($item_id)) {
			$this->db->from('items');
			$this->db->where('item_id', (int) $item_id);
			if ($ignore_deleted == FALSE) {
				$this->db->where('deleted', $deleted);
			}

			return ($this->db->get()->num_rows() == 1);
		}

		return FALSE;
	}

	/*
	Determines if a given item_number exists
	*/
	public function get_transfer_branch_id($location_name)
	{
		$suggestions = array();

		$this->db->select('location_id');
		$this->db->from('stock_locations');
		$this->db->where('location_name', $location_name);
		foreach ($this->db->get()->result() as $row) {
			return $row->location_id;
		}


		//return $suggestions['location_id'];
		//return 1;
	}

	public function item_number_exists($item_number, $item_id = '')
	{
		$this->db->from('items');
		$this->db->where('item_number', (string) $item_number);
		if (ctype_digit($item_id)) {
			$this->db->where('item_id !=', (int) $item_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('items');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Get number of rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters)->num_rows();
	}

	/*
	Perform a search on items
	*/
	/*public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'items.name', $order = 'asc')
	{
		$this->db->select('MAX(items.name) as name');
		$this->db->select('MAX(items.category) as category');
		$this->db->select('MAX(items.supplier_id) as supplier_id');
		$this->db->select('MAX(items.item_number) as item_number');
		$this->db->select('MAX(items.description) as description');
		$this->db->select('MAX(items.cost_price) as cost_price');
		$this->db->select('MAX(items.unit_price) as unit_price');
		$this->db->select('MAX(items.reorder_level) as reorder_level');
		$this->db->select('MAX(items.receiving_quantity) as receiving_quantity');
		$this->db->select('items.item_id as item_id');
		$this->db->select('MAX(items.pic_filename) as pic_filename');
		$this->db->select('MAX(items.allow_alt_description) as allow_alt_description');
		$this->db->select('MAX(items.is_serialized) as is_serialized');
		$this->db->select('MAX(items.deleted) as deleted');
		$this->db->select('MAX(items.expiry_days) as expiry_days');
		$this->db->select('MAX(items.pack) as pack');
		//$this->db->select('MAX(items.custom3) as custom3');
		//$this->db->select('MAX(items.custom4) as custom4');
		$this->db->select('MAX(items.custom5) as custom5');
		$this->db->select('MAX(items.custom6) as custom6');
		$this->db->select('MAX(items.custom7) as custom7');
		$this->db->select('MAX(items.custom8) as custom8');
		$this->db->select('MAX(items.custom9) as custom9');
		$this->db->select('MAX(items.custom10) as custom10');

		$this->db->select('MAX(suppliers.person_id) as person_id');
		$this->db->select('MAX(suppliers.company_name) as company_name');
		$this->db->select('MAX(suppliers.agency_name) as agency_name');
		$this->db->select('MAX(suppliers.account_number) as account_number');
		$this->db->select('MAX(suppliers.deleted) as deleted');

		$this->db->select('MAX(inventory.trans_id) as trans_id');
		$this->db->select('MAX(inventory.trans_items) as trans_items');
		$this->db->select('MAX(inventory.trans_user) as trans_user');
		$this->db->select('MAX(inventory.trans_date) as trans_date');
		$this->db->select('MAX(inventory.trans_comment) as trans_comment');
		$this->db->select('MAX(inventory.trans_location) as trans_location');
		$this->db->select('MAX(inventory.trans_inventory) as trans_inventory');
		$this->db->select('MAX(item_expiry.item_id) as batch');

		if($filters['stock_location_id'] > -1)
		{
			$this->db->select('MAX(item_quantities.item_id) as qty_item_id');
			$this->db->select('MAX(item_quantities.location_id) as location_id');
			$this->db->select('MAX(item_quantities.quantity) as quantity');
		}

		$this->db->from('items as items');
		$this->db->join('suppliers as suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('inventory as inventory', 'inventory.trans_items = items.item_id');
		//$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id');
		if($filters['expiry'] != false)
		{
			$this->db->select('MAX(item_expiry.item_id) as batch');
			//$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id', 'left');
			//$this->db->where('location_id', $filters['stock_location_id']);
		}

		if($filters['stock_location_id'] > -1)
		{
			$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
			$this->db->where('location_id', $filters['stock_location_id']);
		}

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE_FORMAT(trans_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		}
		else
		{
			$this->db->where('trans_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if(!empty($search))
		{
			if($filters['search_custom'] == FALSE)
			{
				$this->db->group_start();
					$this->db->like('name', $search);
					$this->db->or_like('item_number', $search);
					$this->db->or_like('items.item_id', $search);
					$this->db->or_like('company_name', $search);
					$this->db->or_like('category', $search);
				$this->db->group_end();
			}
			else
			{
				$this->db->group_start();
					$this->db->like('expiry_days', $search);
					$this->db->or_like('custom2', $search);
					$this->db->or_like('custom3', $search);
					$this->db->or_like('custom4', $search);
					$this->db->or_like('custom5', $search);
					$this->db->or_like('custom6', $search);
					$this->db->or_like('custom7', $search);
					$this->db->or_like('custom8', $search);
					$this->db->or_like('custom9', $search);
					$this->db->or_like('custom10', $search);
				$this->db->group_end();
			}
		}

		//$this->db->where('items.deleted', $filters['is_deleted']);

		if($filters['empty_upc'] != FALSE)
		{
			$this->db->where('item_number', NULL);
		}
		if($filters['low_inventory'] != FALSE)
		{
			$this->db->where('quantity <=', 'reorder_level');
		}
		if($filters['is_serialized'] != FALSE)
		{
			$this->db->where('is_serialized', 1);
		}
		if($filters['no_description'] != FALSE)
		{
			$this->db->where('items.description', '');
		}
		$this->db->where("items.stock_type != '1'");

		// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
		$this->db->group_by('items.item_id');

		// order by name of item
		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'items.name', $order = 'asc')
	{
		if ($filters['expiry'] == FALSE) {
			$this->db->select('MAX(items.name) as name');
			$this->db->select('MAX(items.category) as category');
			$this->db->select('MAX(items.supplier_id) as supplier_id');
			$this->db->select('MAX(items.item_number) as item_number');
			$this->db->select('MAX(items.description) as description');
			$this->db->select('MAX(items.cost_price) as cost_price');
			$this->db->select('MAX(items.unit_price) as unit_price');
			$this->db->select('MAX(items.reorder_level) as reorder_level');
			$this->db->select('MAX(items.receiving_quantity) as receiving_quantity');
			$this->db->select('items.item_id as item_id');
			$this->db->select('MAX(items.pic_filename) as pic_filename');
			$this->db->select('MAX(items.allow_alt_description) as allow_alt_description');
			$this->db->select('MAX(items.is_serialized) as is_serialized');
			$this->db->select('MAX(items.deleted) as deleted');
			$this->db->select('MAX(items.expiry_days) as expiry_days');
			$this->db->select('MAX(items.pack) as pack');
			//$this->db->select('MAX(items.custom3) as custom3');
			//$this->db->select('MAX(items.custom4) as custom4');
			$this->db->select('MAX(items.custom5) as custom5');
			$this->db->select('MAX(items.custom6) as custom6');
			$this->db->select('MAX(items.custom7) as custom7');
			$this->db->select('MAX(items.custom8) as custom8');
			$this->db->select('MAX(items.custom9) as custom9');
			$this->db->select('MAX(items.custom10) as custom10');

			$this->db->select('MAX(suppliers.person_id) as person_id');
			$this->db->select('MAX(suppliers.company_name) as company_name');
			$this->db->select('MAX(suppliers.agency_name) as agency_name');
			$this->db->select('MAX(suppliers.account_number) as account_number');
			$this->db->select('MAX(suppliers.deleted) as deleted');

			$this->db->select('MAX(inventory.trans_id) as trans_id');
			$this->db->select('MAX(inventory.trans_items) as trans_items');
			$this->db->select('MAX(inventory.trans_user) as trans_user');
			$this->db->select('MAX(inventory.trans_date) as trans_date');
			$this->db->select('MAX(inventory.trans_comment) as trans_comment');
			$this->db->select('MAX(inventory.trans_location) as trans_location');
			$this->db->select('MAX(inventory.trans_inventory) as trans_inventory');


			if ($filters['stock_location_id'] > -1) {
				//$this->db->select('MAX(item_quantities.item_id) as qty_item_id');
				$this->db->select('MAX(item_quantities.location_id) as location_id');
				$this->db->select('MAX(item_quantities.quantity) as quantity');
				if ($filters['expiry'] != FALSE) {
					$this->db->select('MAX(item_expiry.batch_no) as batch');
				}
			}

			$this->db->from('items as items');
			//$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id');
			$this->db->join('suppliers as suppliers', 'suppliers.person_id = items.supplier_id', 'left');
			$this->db->join('inventory as inventory', 'inventory.trans_items = items.item_id');



			if ($filters['stock_location_id'] > -1) {
				$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');

				$this->db->where('location_id', $filters['stock_location_id']);
			}

			if (empty($this->config->item('date_or_time_format'))) {
				$this->db->where('DATE_FORMAT(trans_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
			} else {
				$this->db->where('trans_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
			}

			if (!empty($search)) {
				if ($filters['search_custom'] == FALSE) {
					$this->db->group_start();
					//$trinog=substr($search, 0, 1, 'utf-8');
					$this->db->like('name', $search);
					//$this->db->like('name', $search);
					$this->db->or_like('items.item_number', $search);
					$this->db->or_like('items.item_id', $search);
					$this->db->or_like('company_name', $search);
					$this->db->or_like('category', $search);

					$this->db->group_end();
				} else {
					$this->db->group_start();
					$this->db->like('expiry_days', $search);
					$this->db->or_like('custom2', $search);
					$this->db->or_like('custom3', $search);
					$this->db->or_like('custom4', $search);
					$this->db->or_like('custom5', $search);
					$this->db->or_like('custom6', $search);
					$this->db->or_like('custom7', $search);
					$this->db->or_like('custom8', $search);
					$this->db->or_like('custom9', $search);
					$this->db->or_like('custom10', $search);
					$this->db->group_end();
				}
			}

			$this->db->where('items.deleted', $filters['is_deleted']);

			if ($filters['empty_upc'] != FALSE) {
				$this->db->where('item_number', NULL);
			}
			if ($filters['low_inventory'] != FALSE) {
				$this->db->where('quantity <=', 'reorder_level');
			}
			if ($filters['is_serialized'] != FALSE) {
				$this->db->where('is_serialized', 1);
			}
			if ($filters['no_description'] != FALSE) {
				$this->db->where('items.description', '');
			}
			if ($filters['apply_vat'] != FALSE) {
				$this->db->where('items.apply_vat', 'YES');
			}

			// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
			$this->db->group_by('items.item_id');

			// order by name of item
			$this->db->order_by($sort, $order);
		} else {
			$this->db->select('items.name as name');
			$this->db->select('items.category as category');
			// $this->db->select('MAX(items.supplier_id) as supplier_id');
			// $this->db->select('MAX(items.item_number) as item_number');
			// $this->db->select('MAX(items.description) as description');
			$this->db->select('items.cost_price as cost_price');
			$this->db->select('items.unit_price as unit_price');

			$this->db->select('items.item_id as item_id');
			// $this->db->select('MAX(items.pic_filename) as pic_filename');
			// $this->db->select('MAX(items.allow_alt_description) as allow_alt_description');
			// $this->db->select('MAX(items.is_serialized) as is_serialized');
			// $this->db->select('MAX(items.deleted) as deleted');
			// $this->db->select('MAX(items.expiry_days) as expiry_days');
			$this->db->select('items.pack as pack');

			$this->db->select('item_expiry.location_id as location_id');
			$this->db->select('item_expiry.batch_no as batch');
			$this->db->select('item_expiry.expiry as expiry_date');
			$this->db->select('items.expiry_days as expiretime');
			$this->db->select('items.period as period');

			$this->db->from('items as items');
			//$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id');
			$this->db->join('suppliers as suppliers', 'suppliers.person_id = items.supplier_id', 'left');
			//$this->db->join('inventory as inventory', 'inventory.trans_items = items.item_id');

			$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id');
			$this->db->where('location_id', $filters['stock_location_id']);
		}


		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}


	/*
	Returns all the items
	*/
	public function get_all($stock_location_id = -1, $rows = 0, $limit_from = 0)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');

		if ($stock_location_id > -1) {
			$this->db->join('item_quantities', 'item_quantities.item_id = items.item_id');
			$this->db->where('location_id', $stock_location_id);
		}

		$this->db->where('items.deleted', 0);

		// order by name of item
		$this->db->order_by('items.name', 'asc');

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	public function total_invoice_item($invoice_id)
	{
		$suggestions = array();

		$this->db->select('laboratory_invoice_item.*');
		$this->db->from('laboratory_invoice_item');
		$this->db->where('invoice_id', $invoice_id);

		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array(
				'item_id' => $row->item_id,
				'test_type' => $row->test_type
			);
		}


		return $suggestions;
	}

	/*
	Gets information about a particular item
	*/
	public function get_info($item_id)
	{
		$this->db->select('items.*');
		$this->db->select('suppliers.company_name');
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where('item_id', $item_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('items') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function get_sales()
	{
		$this->db->select('stock_locations.*');
		$this->db->select('SUM(sales.sales_amount) as sales_amount');
		$this->db->from('stock_locations');
		$this->db->join('sales as sales', 'sales.location_id = stock_locations.location_id', 'left');
		$this->db->group_by('stock_locations.location_id');
		//$this->db->where('item_id', $item_id);


		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('location_id' => $row->location_id, 'location_name' => $row->location_name, 'sales_amount' => $row->sales_amount);
		}


		return $suggestions;
	}
	public function get_inventory_total($location)
	{
		$this->db->select('items.name');
		$this->db->select('item_quantities.location_id as location_id');
		$this->db->from('items');
		$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
		//$this->db->group_by('stock_locations.location_id');
		$this->db->where('location_id', $location);


		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('name' => $row->name);
		}


		return $suggestions;
	}
	public function get_stock_total($location)
	{
		$this->db->select('items.name');
		$this->db->select('item_quantities.quantity as quantity');
		$this->db->from('items');
		$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
		//$this->db->group_by('stock_locations.location_id');
		$this->db->where('location_id', $location);
		$this->db->where('quantity', 0);


		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('name' => $row->name);
		}


		return $suggestions;
	}
	public function get_reorder_total($location)
	{
		$this->db->select('items.name');
		$this->db->select('items.reorder_level as reorder_level');
		$this->db->select('item_quantities.quantity as quantity');
		$this->db->from('items');
		$this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
		//$this->db->group_by('stock_locations.location_id');
		$this->db->where('location_id', $location);
		$this->db->where('quantity <=', 'reorder_level');


		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('name' => $row->name);
		}


		return $suggestions;
	}


	public function get_expiry_total($location)
	{
		$this->db->select('items.name');
		$this->db->select('items.period');
		$this->db->select('items.expiry_days');
		$this->db->select('item_expiry.expiry as expiry');
		$this->db->select('item_expiry.location_id as location_id');
		$this->db->from('items');
		$this->db->join('item_expiry as item_expiry', 'item_expiry.item_id = items.item_id');
		//$this->db->group_by('stock_locations.location_id');
		$this->db->where('location_id', $location);


		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('name' => $row->name, 'period' => $row->period, 'expiry_days' => $row->expiry_days, 'expiry' => $row->expiry);
		}


		return $suggestions;
	}
	public function get_labresult_info($sale_id)
	{
		$this->db->select('laboratory_results.*');
		$this->db->from('laboratory_results');
		$this->db->where('sale_id', $sale_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('laboratory_results') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function get_labinfo_by_id_or_number($item_id)
	{
		$this->db->from('laboratory_test');

		$this->db->where('item_id', (int) $item_id);




		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		}

		return '';
	}
	public function labget_info($item_id)
	{
		$this->db->select('laboratory_test.*');
		$this->db->from('laboratory_test');
		$this->db->where('item_id', $item_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('laboratory_test') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function labcategory_info($lab_category_id)
	{
		$this->db->select('laboratory_category.*');
		$this->db->from('laboratory_category');
		$this->db->where('lab_category_id', $lab_category_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('laboratory_category') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function lab_category_save($lab_category_name, $lab_category_id = FALSE)
	{
		if (!$lab_category_id || !$this->exists($lab_category_id, TRUE)) {
			$item_data = array(
				'lab_category_name' => $lab_category_name,

			);

			$this->db->trans_start();

			$this->db->insert('laboratory_category', $item_data);
			$lab_category_id = $this->db->insert_id();

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return -1;
			}
			return $lab_category_id;
			//return TRUE;
		}

		$this->db->where('lab_category_id', $lab_category_id);

		return $this->db->update('laboratory_category', $item_data);
	}

	public function category_get_all($limit_from = 0, $rows = 0)
	{
		$this->db->from('laboratory_category');
		$this->db->where('deleted', 0);
		$this->db->order_by('lab_category_name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}


	public function labtestunit_info($lab_testunit_id)
	{
		$this->db->select('laboratory_testunit.*');
		$this->db->from('laboratory_testunit');
		$this->db->where('lab_testunit_id', $lab_testunit_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('laboratory_testunit') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function labtestsubgroup_info($lab_subgroup_id)
	{
		$this->db->select('laboratory_subgroup.*');
		$this->db->from('laboratory_subgroup');
		$this->db->where('lab_subgroup_id', $lab_subgroup_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('laboratory_subgroup') as $field) {
				$item_obj->$field = '';
			}

			return $item_obj;
		}
	}
	public function lab_testunit_save($lab_testunit_name, $lab_testunit_id = FALSE)
	{
		if (!$lab_testunit_id || !$this->exists($lab_testunit_id, TRUE)) {
			$item_data = array(
				'lab_testunit_name' => $lab_testunit_name,

			);

			$this->db->trans_start();

			$this->db->insert('laboratory_testunit', $item_data);
			$lab_testunit_id = $this->db->insert_id();

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return -1;
			}
			return $lab_testunit_id;
			//return TRUE;
		}

		$this->db->where('lab_testunit_id', $lab_testunit_id);

		return $this->db->update('laboratory_testunit', $item_data);
	}

	public function lab_testsubgroup_save($lab_subgroup_name, $lab_subgroup_id = FALSE)
	{
		if (!$lab_subgroup_id || !$this->exists($lab_subgroup_id, TRUE)) {
			$item_data = array(
				'lab_subgroup_name' => $lab_subgroup_name,

			);

			$this->db->trans_start();

			$this->db->insert('laboratory_subgroup', $item_data);
			$lab_subgroup_id = $this->db->insert_id();

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return -1;
			}
			return $lab_subgroup_id;
			//return TRUE;
		}

		$this->db->where('lab_subgroup_id', $lab_subgroup_id);

		return $this->db->update('laboratory_testunit', $item_data);
	}

	public function testunit_get_all($limit_from = 0, $rows = 0)
	{
		$this->db->from('laboratory_testunit');
		$this->db->where('deleted', 0);
		$this->db->order_by('lab_testunit_name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	public function testsubgroup_get_all($limit_from = 0, $rows = 0)
	{
		$this->db->from('laboratory_subgroup');
		$this->db->where('deleted', 0);
		$this->db->order_by('lab_subgroup_name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	public function testkind_get_all($limit_from = 0, $rows = 0)
	{
		$this->db->from('laboratory_kind');
		$this->db->where('deleted', 0);
		$this->db->order_by('lab_testkind_name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}



	/*
	Gets information about a particular item by item id or number
	*/
	public function get_info_by_id_or_number($item_id)
	{
		$this->db->from('items');

		if (ctype_digit($item_id)) {
			$this->db->group_start();
			$this->db->where('item_id', (int) $item_id);
			$this->db->or_where('items.item_number', $item_id);
			$this->db->group_end();
		} else {
			$this->db->where('item_number', $item_id);
		}

		$this->db->where('items.deleted', 0);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		}

		return '';
	}
	public function get_info_pill($reminder_id)
	{
		$this->db->from('pill_reminder');

		if (ctype_digit($reminder_id)) {
			$this->db->group_start();
			$this->db->where('reminder_id', (int) $reminder_id);
			$this->db->or_where('pill_reminder.reminder_id', $reminder_id);
			$this->db->group_end();
		} else {
			$this->db->where('reminder_id', $reminder_id);
		}

		$this->db->where('pill_reminder.deleted', 0);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		}

		return '';
	}
	public function get_info_by_id_or_numbe()
	{
		$this->db->from('items');


		$this->db->where('items.deleted', 0);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_info_batch($id)
	{
		$this->db->from('item_expiry');


		$this->db->where('id', $id);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_info_role($id)
	{
		$this->db->from('role_id');


		$this->db->where('id', $id);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_balanceinfo($employeeID)
	{
		$date_entered = date('Y-m-d');
		$status = 1;
		$this->db->from('open_register');


		$this->db->where('employeeID', $employeeID);
		$this->db->where('date_entered', $date_entered);
		$this->db->where('status', $status);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_info_branch($location_id)
	{
		$this->db->from('stock_locations');


		$this->db->where('location_id', $location_id);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_info_transfer($transfer_id)
	{
		$this->db->from('item_transfer');


		$this->db->where('transfer_id', $transfer_id);

		$query = $this->db->get();


		return $query->row();
	}
	public function get_saleinfo($invoice_id)
	{
		$this->db->from('laboratory_results');


		$this->db->where('invoice_id', $invoice_id);

		$query = $this->db->get();


		return $query->row();
	}

	/*
	Get an item id given an item number
	*/
	public function get_item_id($item_number, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->where('item_number', $item_number);
		if ($ignore_deleted == FALSE) {
			$this->db->where('items.deleted', $deleted);
		}

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row()->item_id;
		}

		return FALSE;
	}

	/*
	Gets information about multiple items
	*/
	public function get_multiple_info($item_ids, $location_id)
	{
		$this->db->from('items');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('item_quantities', 'item_quantities.item_id = items.item_id', 'left');
		$this->db->where('location_id', $location_id);
		$this->db->where_in('items.item_id', $item_ids);

		return $this->db->get();
	}
	public function save(&$item_data, $item_id = FALSE)
	{
		if (!$item_id || !$this->exists($item_id, TRUE)) {
			if ($this->db->insert('items', $item_data)) {
				$item_data['item_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('item_id', $item_id);

		return $this->db->update('items', $item_data);
	}

	/*
	Inserts or updates a item
	*/
	public function save_category(&$item_data, $item_id = FALSE)
	{
		if (!$item_id || !$this->exists($item_id, TRUE)) {
			if ($this->db->insert('categories', $item_data)) {
				$item_data['item_id'] = $this->db->insert_id();

				return TRUE;
			}

			return FALSE;
		}

		$this->db->where('id', $item_id);

		return $this->db->update('categories', $item_data);
	}

	public function lab_save($test_code, $test_name, $test_amount, $test_type, $test_unit, $test_subgroup, $test_kind, $item_id = FALSE)
	{
		if (!$item_id || !$this->exists($item_id, TRUE)) {
			$item_data = array(
				'name' => $test_name,
				'description' => '',
				'category' => 'Laboratory',
				'expiry_days' => 0,
				'pack' => '',
				'item_type' => '0',
				'stock_type' => '1',
				'supplier_id' => NULL,
				'item_number' => NULL,
				'cost_price' => 0,
				'unit_price' => $test_amount,
				'reorder_level' => 0,
				'receiving_quantity' => 0,
				'allow_alt_description' => 0,
				'is_serialized' => 0,
				'deleted' => 0,

				'custom5' => '',
				'custom6' => '',
				'custom7' => '',
				'custom8' => '',
				'custom9' => '',
				'custom10' => ''
			);

			$this->db->trans_start();

			$this->db->insert('items', $item_data);
			$item_id = $this->db->insert_id();

			$labitem_data = array(
				'item_id' => $item_id,
				'test_code' => $test_code,
				'test_name' => $test_name,
				'test_amount' => $test_amount,
				'test_type' => $test_type,
				'test_subgroup' => $test_subgroup,
				'test_kind' => $test_kind,
				'test_unit'	=> $test_unit
			);
			$this->db->insert('laboratory_test', $labitem_data);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return -1;
			}
			return $item_id;
			//return TRUE;
		}
		$labitem_data = array(
			'item_id' => $item_id,
			'test_code' => $test_code,
			'test_name' => $test_name,
			'test_amount' => $test_amount,
			'test_type' => $test_type,
			'test_subgroup' => $test_subgroup,
			'test_kind' => $test_kind,
			'test_unit'	=> $test_unit
		);
		$this->db->where('item_id', $item_id);
		$this->db->update('items', $item_data);

		return $this->db->update('laboratory_test', $labitem_data);
	}

	/*
	Updates multiple items at once
	*/
	public function update_multiple($item_data, $item_ids)
	{
		$this->db->where_in('item_id', explode(':', $item_ids));

		return $this->db->update('items', $item_data);
	}

	/*
	Deletes one item
	*/
	public function delete($item_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity($item_id);
		$this->db->where('item_id', $item_id);
		$success = $this->db->update('items', array('deleted' => 1));
		$success &= $this->Inventory->reset_quantity($item_id);

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Undeletes one item
	*/
	public function undelete($item_id)
	{
		$this->db->where('item_id', $item_id);

		return $this->db->update('items', array('deleted' => 0));
	}

	/*
	Deletes a list of items
	*/
	public function delete_list($item_ids)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		// set to 0 quantities
		$this->Item_quantity->reset_quantity_list($item_ids);
		$this->db->where_in('item_id', $item_ids);
		$success = $this->db->update('items', array('deleted' => 1));

		foreach ($item_ids as $item_id) {
			$success &= $this->Inventory->reset_quantity($item_id);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();

		$this->db->select('items.item_id, name, quantity');
		$this->db->from('items');
		$this->db->join('item_quantities', 'item_quantities.item_id=items.item_id');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type = '0'");
		$this->db->like('name', $search, 'after'); //it could be 'after'

		$this->db->order_by('name', 'asc');

		$this->db->limit($limit);
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->name . '(Qty:' . number_format($row->quantity) . ')');
		}

		/*$this->db->select('item_id, item_number');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type = '0'");
		$this->db->like('item_number', $search, 'after');
		$this->db->order_by('item_number', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->item_number);
		}

		if (!$unique) {
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type = '0'");
			$this->db->distinct();
			$this->db->like('category', $search, 'after');
			$this->db->order_by('category', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search, 'after');
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type = '0'");
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select('item_id, name, description');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type = '0'");
			$this->db->like('description', $search, 'after');
			$this->db->order_by('description', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$entry = array('value' => $row->item_id, 'label' => $row->name);
				if (!array_walk($suggestions, function ($value, $label) use ($entry) {
					return $entry['label'] != $label;
				})) {
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if ($filters['search_custom'] != FALSE) {
				$this->db->from('items');
				$this->db->group_start();
				$this->db->like('expiry_days', $search, 'after');
				$this->db->or_like('custom2', $search, 'after');
				$this->db->or_like('custom3', $search, 'after');
				$this->db->or_like('custom4', $search, 'after');
				$this->db->or_like('custom5', $search, 'after');
				$this->db->or_like('custom6', $search, 'after');
				$this->db->or_like('custom7', $search, 'after');
				$this->db->or_like('custom8', $search, 'after');
				$this->db->or_like('custom9', $search, 'after');
				$this->db->or_like('custom10', $search, 'after');
				$this->db->group_end();
				$this->db->where('deleted', $filters['is_deleted']);
				$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
				$this->db->where("stock_type = '0'");
				foreach ($this->db->get()->result() as $row) {
					$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
				}
			}
		}
		*/

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}
	public function get_batchsearch_suggestions($search,  $limit = 25)
	{
		$suggestions = array();

		$this->db->select('id, batch_no');
		$this->db->from('item_expiry');
		$this->db->like('batch_no', $search);
		$this->db->order_by('batch_no', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->id, 'label' => $row->batch_no);
		}



		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}



	public function get_stock_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();

		$this->db->select('item_id, name');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type != '1'"); // stocked items only
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
		}

		$this->db->select('item_id, item_number');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type != '1'"); // stocked items only
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->item_number);
		}

		if (!$unique) {
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type != '1'"); // stocked items only
			$this->db->distinct();
			$this->db->like('category', $search);
			$this->db->order_by('category', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search);
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select('item_id, name, description');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
			$this->db->where("stock_type != '1'"); // stocked items only
			$this->db->like('description', $search);
			$this->db->order_by('description', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$entry = array('value' => $row->item_id, 'label' => $row->name);
				if (!array_walk($suggestions, function ($value, $label) use ($entry) {
					return $entry['label'] != $label;
				})) {
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if ($filters['search_custom'] != FALSE) {
				$this->db->from('items');
				$this->db->group_start();
				$this->db->like('expiry_days', $search);
				$this->db->or_like('custom2', $search);
				$this->db->or_like('custom3', $search);
				$this->db->or_like('custom4', $search);
				$this->db->or_like('custom5', $search);
				$this->db->or_like('custom6', $search);
				$this->db->or_like('custom7', $search);
				$this->db->or_like('custom8', $search);
				$this->db->or_like('custom9', $search);
				$this->db->or_like('custom10', $search);
				$this->db->group_end();
				$this->db->where("item_type = '0'"); // standard, exclude kit items since kits will be picked up later
				$this->db->where("stock_type != '1'"); // stocked items only
				$this->db->where('deleted', $filters['is_deleted']);
				foreach ($this->db->get()->result() as $row) {
					$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
				}
			}
		}


		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}
	public function global_search_items($item_id)
	{
		$items_global = array();

		$this->db->select('item_id, location_id, quantity');
		$this->db->from('item_quantities');
		$this->db->where('item_id', $item_id);
		$this->db->where("quantity != '0'"); // standard, exclude kit items since kits will be picked up later

		foreach ($this->db->get()->result() as $row) {
			$items_global[] = array('item_id' => $row->item_id, 'location_id' => $row->location_id, 'qty' => $row->quantity);
		}


		return $items_global;
	}
	public function laboratory_items()
	{
		$lab_items = array();

		$this->db->select('item_id,test_code,test_name, test_amount,test_type');
		$this->db->from('laboratory_test');


		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('item_id' => $row->item_id, 'test_code' => $row->test_code, 'test_name' => $row->test_name, 'test_amount' => $row->test_amount, 'test_type' => $row->test_type, 'edit' => '<span class="glyphicon glyphicon-edit update" id="' . $row->item_id . '" name="edit" title = "update" data-toggle="modal" data-target="#userModal"></span>');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);


		return $results;
	}
	public function account_items()
	{
		$lab_items = array();

		$this->db->select('invoice_id,person_id,doctor_name, status');
		$this->db->from('laboratory_invoice');
		$this->db->order_by('invoice_id', 'asc');

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('invoice_id' => $row->invoice_id, 'person_id' => $row->person_id, 'doctor_name' => $row->doctor_name, 'status' => $row->status, 'edit' => $row->status == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>');
		}



		return $lab_items;
	}
	public function unprocessed_account_items()
	{
		$lab_items = array();

		$this->db->select('invoice_id,person_id,doctor_name, status');
		$this->db->from('laboratory_invoice');
		$this->db->order_by('invoice_id', 'desc');
		$this->db->where('status', 0);

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('invoice_id' => $row->invoice_id, 'person_id' => $row->person_id, 'doctor_name' => $row->doctor_name, 'status' => $row->status, 'edit' => $row->status == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>');
		}



		return $lab_items;
	}
	public function categories_list()
	{
		$lab_items = array();

		$this->db->select('id,name');
		$this->db->from('categories');
		$this->db->order_by('id', 'asc');

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('id' => $row->id, 'name' => $row->name);
		}



		return $lab_items;
	}
	public function processed_account_items()
	{
		$lab_items = array();

		$this->db->select('invoice_id,person_id,doctor_name, status');
		$this->db->from('laboratory_invoice');
		$this->db->where('status', 1);
		$this->db->order_by('invoice_id', 'asc');

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('invoice_id' => $row->invoice_id, 'person_id' => $row->person_id, 'doctor_name' => $row->doctor_name, 'status' => $row->status, 'edit' => $row->status == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>');
		}



		return $lab_items;
	}
	public function result_items()
	{
		$lab_items = array();

		$this->db->select('sale_id,invoice_id,customer_id,doctor_name');
		$this->db->from('laboratory_results');

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('sale_id' => $row->sale_id, 'invoice_id' => $row->invoice_id, 'customer_id' => $row->customer_id, 'doctor_name' => $row->doctor_name, 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}



		return $lab_items;
	}
	public function new_result_items()
	{
		$lab_items = array();

		$this->db->select('sale_id,invoice_id,customer_id,doctor_name');
		$this->db->from('laboratory_results');
		$this->db->where('status', 1);

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('sale_id' => $row->sale_id, 'invoice_id' => $row->invoice_id, 'customer_id' => $row->customer_id, 'doctor_name' => $row->doctor_name, 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}



		return $lab_items;
	}
	public function pending_result_items()
	{
		$lab_items = array();

		$this->db->select('sale_id,invoice_id,customer_id,doctor_name');
		$this->db->from('laboratory_results');
		$this->db->where('status', 2);

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('sale_id' => $row->sale_id, 'invoice_id' => $row->invoice_id, 'customer_id' => $row->customer_id, 'doctor_name' => $row->doctor_name, 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}



		return $lab_items;
	}
	public function completed_result_items()
	{
		$lab_items = array();

		$this->db->select('sale_id,invoice_id,customer_id,doctor_name');
		$this->db->from('laboratory_results');
		$this->db->where('status', 3);

		foreach ($this->db->get()->result() as $row) {
			$lab_items[] = array('sale_id' => $row->sale_id, 'invoice_id' => $row->invoice_id, 'customer_id' => $row->customer_id, 'doctor_name' => $row->doctor_name, 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $row->invoice_id . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}



		return $lab_items;
	}


	public function global_transfer_items($transfer_branch_id)
	{
		$items_transfer = array();

		$this->db->select('transfer_id,transfer_type,transfer_time, status');
		$this->db->from('item_transfer');
		$this->db->where('request_to_branch_id', $transfer_branch_id); // standard, exclude kit items since kits will be picked up later
		$this->db->where("status = '0'");
		$this->db->order_by('transfer_id', 'desc');

		foreach ($this->db->get()->result() as $row) {
			$items_transfer[] = array('transfer_id' => $row->transfer_id, 'status' => $row->status, 'transfer_type' => $row->transfer_type, 'transfer_time' => $row->transfer_time, 'request_from_branch_id' => $row->request_from_branch_id);
		}


		return $items_transfer;
	}
	public function find_permission_ce($module_id)
	{
		$items_transfer = array();

		$this->db->select('permission_id');
		$this->db->from('permissions');
		$this->db->where('module_id', $module_id); // standard, exclude kit items since kits will be picked up later

		foreach ($this->db->get()->result() as $row) {
			$items_transfer[] = array('permission_id' => $row->permission_id);
		}


		return $items_transfer;
	}
	public function find_permission($module_id, $location_id)
	{
		$items_transfer = array();

		$this->db->select('permission_id');
		$this->db->from('permissions');
		$this->db->where('module_id', $module_id); // standard, exclude kit items since kits will be picked up later
		$this->db->where('location_id', $location_id);

		foreach ($this->db->get()->result() as $row) {
			$items_transfer[] = array('permission_id' => $row->permission_id);
		}


		return $items_transfer;
	}
	public function get_lab_items()
	{
		$items_transfer = array();

		$this->db->select('item_id,test_code, test_name, test_amount');
		$this->db->from('laboratory_test');

		foreach ($this->db->get()->result() as $row) {
			$items_transfer[] = array('item_id' => $row->item_id, 'test_code' => $row->test_code, 'test_name' => $row->test_name, 'test_amount' => $row->test_amount, 'test_type' => $row->test_type);
		}


		return $items_transfer;
	}



	public function get_kit_search_suggestions($search, $filters = array('is_deleted' => FALSE, 'search_custom' => FALSE), $unique = FALSE, $limit = 25)
	{
		$suggestions = array();

		$this->db->select('item_id, name');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->where("item_type = '1'"); // standard, exclude kit items since kits will be picked up later
		$this->db->where("stock_type = '0'");
		$this->db->like('name', $search);
		$this->db->order_by('name', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
		}

		$this->db->select('item_id, item_number');
		$this->db->from('items');
		$this->db->where('deleted', $filters['is_deleted']);
		$this->db->like('item_number', $search);
		$this->db->order_by('item_number', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->item_id, 'label' => $row->item_number);
		}

		if (!$unique) {
			//Search by category
			$this->db->select('category');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->like('category', $search);
			$this->db->order_by('category', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->category);
			}

			//Search by supplier
			$this->db->select('company_name');
			$this->db->from('suppliers');
			$this->db->like('company_name', $search);
			// restrict to non deleted companies only if is_deleted is FALSE
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->distinct();
			$this->db->order_by('company_name', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('label' => $row->company_name);
			}

			//Search by description
			$this->db->select('item_id, name, description');
			$this->db->from('items');
			$this->db->where('deleted', $filters['is_deleted']);
			$this->db->like('description', $search);
			$this->db->order_by('description', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$entry = array('value' => $row->item_id, 'label' => $row->name);
				if (!array_walk($suggestions, function ($value, $label) use ($entry) {
					return $entry['label'] != $label;
				})) {
					$suggestions[] = $entry;
				}
			}

			//Search by custom fields
			if ($filters['search_custom'] != FALSE) {
				$this->db->from('items');
				$this->db->group_start();
				$this->db->like('expiry_days', $search);
				$this->db->or_like('custom2', $search);
				$this->db->or_like('custom3', $search);
				$this->db->or_like('custom4', $search);
				$this->db->or_like('custom5', $search);
				$this->db->or_like('custom6', $search);
				$this->db->or_like('custom7', $search);
				$this->db->or_like('custom8', $search);
				$this->db->or_like('custom9', $search);
				$this->db->or_like('custom10', $search);
				$this->db->group_end();
				$this->db->where('deleted', $filters['is_deleted']);
				foreach ($this->db->get()->result() as $row) {
					$suggestions[] = array('value' => $row->item_id, 'label' => $row->name);
				}
			}
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	public function get_category_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('category');
		$this->db->from('items');
		$this->db->like('category', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('category', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('label' => $row->category);
		}

		return $suggestions;
	}

	public function get_location_suggestions($search)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('location');
		$this->db->from('items');
		$this->db->like('location', $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('location', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('label' => $row->location);
		}

		return $suggestions;
	}

	public function get_custom_suggestions($search, $field_no)
	{
		$suggestions = array();
		$this->db->distinct();
		$this->db->select('custom' . $field_no);
		$this->db->from('items');
		$this->db->like('custom' . $field_no, $search);
		$this->db->where('deleted', 0);
		$this->db->order_by('custom' . $field_no, 'asc');
		foreach ($this->db->get()->result() as $row) {
			$row_array = (array) $row;
			$suggestions[] = array('label' => $row_array['custom' . $field_no]);
		}

		return $suggestions;
	}

	public function get_categories()
	{
		$this->db->select('category');
		$this->db->from('items');
		$this->db->where('deleted', 0);
		$this->db->distinct();
		$this->db->order_by('category', 'asc');

		return $this->db->get();
	}

	/*
	 * changes the cost price of a given item
	 * calculates the average price between received items and items on stock
	 * $item_id : the item which price should be changed
	 * $items_received : the amount of new items received
	 * $new_price : the cost-price for the newly received items
	 * $old_price (optional) : the current-cost-price
	 *
	 * used in receiving-process to update cost-price if changed
	 * caution: must be used before item_quantities gets updated, otherwise the average price is wrong!
	 *
	 */
	public function change_cost_price($item_id, $items_received, $new_price, $old_price = null)
	{
		if ($old_price === null) {
			$item_info = $this->get_info($item_id);
			$old_price = $item_info->cost_price;
		}

		$this->db->from('item_quantities');
		$this->db->select_sum('quantity');
		$this->db->where('item_id', $item_id);
		$this->db->join('stock_locations', 'stock_locations.location_id=item_quantities.location_id');
		$this->db->where('stock_locations.deleted', 0);
		$old_total_quantity = $this->db->get()->row()->quantity;

		$total_quantity = $old_total_quantity + $items_received;
		$average_price = bcdiv(bcadd(bcmul($items_received, $new_price), bcmul($old_total_quantity, $old_price)), $total_quantity);

		$data = array('cost_price' => $average_price);

		return $this->save($data, $item_id);
	}

	public function get_item_category_by_name($name)
	{
		$this->db->select('categories.*');
		$this->db->from('categories');
		$this->db->where('name', $name);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			return FALSE;
		}
	}

	public function get_item_by_item_number($item_number)
	{
		$this->db->from('items');
		$this->db->where('item_number', $item_number);
		$this->db->where('items.deleted', 0);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->result();
		}

		return FALSE;
	}

	public function get_customer_pos_ids($customerID)
	{
		$this->db->select('laboratory_results.*');
		$this->db->from('laboratory_results');
		$this->db->where('customer_id', $customerID);
		$query = $this->db->get();
		if ($query->num_rows() >= 1) {
			return $query->result();
		}
		return FALSE;
	}
}
