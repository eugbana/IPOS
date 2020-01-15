<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Detailed_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function createTransfer(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Receiving->create_transfer_temp_table($inputs);
	}
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Receiving->create_temp_table($inputs);
	}

	public function getDataColumns()
	{
		$columns = array(
			'summary' => array(
				array('id' => $this->lang->line('reports_receiving_id')),
				array('receiving_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('employee_name' => $this->lang->line('reports_received_by')),
				array('supplier_name' => $this->lang->line('reports_supplied_by')),
				array('total' => 'Total Cost', 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comment' => $this->lang->line('reports_comments')),
				array('reference' => $this->lang->line('receivings_reference'))
			),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				'Cost',
				$this->lang->line('reports_total'),
				$this->lang->line('reports_discount')
			)
		);

		return $columns;
	}
	public function GetPrintData()
	{
		return array(
			'summary' => array(
				array('id' => 'Receipt No.'),
				array('receiving_time' => 'Date'),
				array('quantity' => "Quantity"),
				array('supplier_name' => "Supplier"),
				array('receiving_type' => 'Receiving Type'),
				array('employee' => 'Employee'),
				array('total' => "Total", 'sorter' => 'number_sorter'),
				array('cost' => "Cost", 'sorter' => 'number_sorter'),
				array('payment_type' => "Payment Type"),
				array('reference' => 'Reference'),
				array('comment' => 'Comment')
			)
		);
	}

	public function getTransferDataColumns()
	{
		$columns = array(
			'summary' => array(
				array('id' => 'Transfer ID'),
				array('transfer_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('employee_name' => 'Performed By'),
				array('receiving_branch' => 'Transfered To'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),



			),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_total'),
			)
		);

		return $columns;
	}

	public function getTransferData(array $inputs)
	{
		$employee_info = null;
		if ($inputs['employee_id'] != 'all') {
			$employee_info = $this->CI->Employee->get_info($inputs['employee_id']);
		}

		$this->db->select('item_transfer.*, 
		SUM(items_push.pushed_quantity) AS pushed_quantity,
		people.first_name AS first_name,
		people.last_name AS last_name,
		stock_locations.location_name AS location_name,
		
			');
		$this->db->from('item_transfer AS item_transfer');
		$this->db->join('items_push AS items_push', 'items_push.transfer_id = item_transfer.transfer_id');
		$this->db->join('people AS people', 'item_transfer.employee_id = people.person_id');
		$this->db->join('stock_locations AS stock_locations', 'stock_locations.location_id = item_transfer.request_to_branch_id');

		$this->db->where('transfer_type', 'PUSH');
		if ($employee_info && $employee_info->branch_id > 0) {
			$this->db->where('item_transfer.request_from_branch_id', $employee_info->branch_id);
		}
		$this->db->where('transfer_time >=', $inputs['start_date']);
		$this->db->where('transfer_time <=', $inputs['end_date']);

		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_transfer.request_to_branch_id', $inputs['location_id']);
		}
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('employee_id', $inputs['employee_id']);
		}


		$this->db->group_by('transfer_id');
		$this->db->order_by('transfer_id');

		$data = array();
		$summary = $this->db->get()->result_array();


		foreach ($summary as $key => $value) {
			$itemD = array(
				'id' => 'PUSH ' . $value['transfer_id'],
				'transfer_date' => $value['transfer_time'],
				'quantity' => $value['pushed_quantity'],
				'employee_name' => $value['first_name'] . ' ' . $value['last_name'],
				'receiving_branch' => $value['location_name'],

			);

			//calculate the total amount in this transfer
			$this->db->select(' items_push.*,
			SUM(items_push.pushed_quantity * items_push.item_unit_price) AS total
			
				');
			$this->db->from('items_push AS items_push');
			//$this->db->join('items AS items', 'items.item_id = items_push.item_id');
			$this->db->where('transfer_id', $value['transfer_id']);
			$this->db->group_by('id');
			$this->db->order_by('id');

			$tItem = $this->db->get()->result_array();

			$total = array_reduce($tItem, function ($initial, $cur) {
				return $initial + $cur['total'];
			}, 0);
			$itemD['total'] = $total;
			$data[] = $itemD;
		}

		return $data;
	}
	public function getTransferDataItems(array $inputs)
	{
		$employee_info = null;
		if ($inputs['employee_id'] != 'all') {
			$employee_info = $this->CI->Employee->get_info($inputs['employee_id']);
		}

		$this->db->select('items_push.*, 
		item_transfer.*,
		people.first_name AS first_name,
		people.last_name AS last_name,
		stock_locations.location_name AS location_name,
		items.cost_price AS cost_price,
		(items_push.pushed_quantity * items_push.item_unit_price) AS total,
		items.name AS name,
		items.category AS category
		
			');
		$this->db->from('items_push AS items_push');
		$this->db->join('item_transfer AS item_transfer', 'item_transfer.transfer_id = items_push.transfer_id');
		$this->db->join('items AS items', 'items.item_id = items_push.item_id');

		$this->db->join('people AS people', 'item_transfer.employee_id = people.person_id');
		$this->db->join('stock_locations AS stock_locations', 'stock_locations.location_id = item_transfer.request_to_branch_id');

		$this->db->where('transfer_type', 'PUSH');
		if ($employee_info && $employee_info->branch_id > 0) {
			$this->db->where('items_push.request_from_branch_id', $employee_info->branch_id);
		}
		$this->db->where('transfer_time >=', $inputs['start_date']);
		$this->db->where('transfer_time <=', $inputs['end_date']);

		if ($inputs['location_id'] != 'all') {
			$this->db->where('items_push.request_to_branch_id', $inputs['location_id']);
		}
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('item_transfer.employee_id', $inputs['employee_id']);
		}


		$this->db->group_by('id');
		$this->db->order_by('id');


		$summary = $this->db->get()->result_array();


		return $summary;
	}

	public function getDataByReceivingId($receiving_id)
	{
		$this->db->select('receiving_id, 
			MAX(receiving_date) as receiving_date, 
			SUM(quantity_purchased) AS items_purchased, 
			MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name, 
			MAX(supplier.company_name) AS supplier_name, 
			SUM(subtotal) AS subtotal, 
			SUM(total) AS total, 
			SUM(profit) AS profit, 
			MAX(payment_type) as payment_type, 
			MAX(comment) as comment, 
			MAX(reference) as reference');
		$this->db->from('receivings_items_temp');
		$this->db->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');
		$this->db->where('receiving_id', $receiving_id);
		$this->db->group_by('receiving_id');

		return $this->db->get()->row_array();
	}

	public function getData(array $inputs)
	{
		$this->db->select('receiving_id, 
			MAX(receiving_date) as receiving_date, 
			SUM(quantity_purchased) AS items_purchased, 
			MAX(CONCAT(employee.first_name," ",employee.last_name)) AS employee_name, 
			MAX(supplier.company_name) AS supplier_name, 
			SUM(cost) AS total, 
			SUM(profit) AS profit, 
			MAX(payment_type) AS payment_type, 
			MAX(comment) AS comment, 
			MAX(reference) AS reference');
		$this->db->from('receivings_items_temp AS receivings_items_temp');
		$this->db->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');

		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('employee_id', $inputs['employee_id']);
		}


		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		} elseif ($inputs['receiving_type'] == 'requisitions') {
			$this->db->having('items_purchased = 0');
		}
		$this->db->group_by('receiving_id', 'receiving_date');
		$this->db->order_by('receiving_id');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();

		foreach ($data['summary'] as $key => $value) {
			$this->db->select('name, item_number, category, quantity_purchased, item_cost_price,item_unit_price, serialnumber,total, discount_percent, item_location, receivings_items_temp.receiving_quantity');
			$this->db->from('receivings_items_temp');
			$this->db->join('items', 'receivings_items_temp.item_id = items.item_id');
			$this->db->where('receiving_id = ' . $value['receiving_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}
	public function getAllReceivings($inputs)
	{
		$sql = 'receivings.*, 
		employee.first_name AS firstname, 
		employee.last_name AS lastname,
		SUM(receivings_items.quantity_purchased) AS quantity_purchased,
		';
		if ($inputs['location_id'] != 'all') {
			$sql .= 'receivings_items.item_location AS item_location,';
		}
		$sql .= 'supplier.company_name as supplier,
		SUM(receivings_items.item_cost_price * receivings_items.quantity_purchased) AS total

		';
		$this->db->select($sql);
		$this->db->from('receivings');
		$this->db->join('people AS employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('receivings_items AS receivings_items', 'receivings_items.receiving_id = receivings.receiving_id', 'left');

		$this->db->where('receiving_time >=', $inputs['start_date']);
		$this->db->where('receiving_time <=', $inputs['end_date']);
		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}

		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased >', 0);
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased <', 0);
		}

		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_id');


		return $this->db->get()->result_array();
	}
	public function getAllReceivingsItems($inputs)
	{
		$this->db->select('receivings_items.*,receivings.* ,
		employee.first_name AS firstname,
		items.name AS name,
		items.category AS category, 
		employee.last_name AS lastname,
		supplier.company_name as supplier,
		SUM(item_cost_price * quantity_purchased) AS total

		');
		$this->db->from('receivings_items');
		$this->db->join('receivings as receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('people AS employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('items AS items', 'receivings_items.item_id = items.item_id');
		$this->db->join('suppliers AS supplier', 'receivings.supplier_id = supplier.person_id', 'left');

		$this->db->where('receiving_time >=', $inputs['start_date']);
		$this->db->where('receiving_time <=', $inputs['end_date']);
		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}

		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased >', 0);
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased <', 0);
		}

		$this->db->group_by('id');
		$this->db->order_by('id');


		return $this->db->get()->result_array();
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(cost) AS total');
		$this->db->from('receivings_items_temp');

		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('employee_id', $inputs['employee_id']);
		}

		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		} elseif ($inputs['receiving_type'] == 'requisitions') {
			$this->db->where('quantity_purchased = 0');
		}

		return $this->db->get()->row_array();
	}
}
