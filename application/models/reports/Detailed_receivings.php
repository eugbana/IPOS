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
				array('price' => 'Total Price', 'sorter' => 'number_sorter'),
				//array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comment' => $this->lang->line('reports_comments')),
				array('reference' => $this->lang->line('receivings_reference'))
			),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				'Cost',
				'Retail Price',
				'Total Cost',
				'Total Price'

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
				array('transfering_branch' => 'Transfered From'),
				array('receiving_branch' => 'Transfered To'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),



			),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				' Cost',
				'Price',
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_total'),
			)
		);

		return $columns;
	}

	public function getTransferData(array $inputs)
	{


		$this->db->select("item_transfer.*, 
		SUM(items_push.pushed_quantity) AS pushed_quantity,
		CONCAT(people.first_name, ' ',people.last_name) AS employee_name,
		from_locations.location_name AS from_location_name,
		to_locations.location_name AS to_location_name,
		SUM(items_push.pushed_quantity) AS pushed_quantity,
		SUM(items_push.transfer_price * items_push.pushed_quantity) AS total
		
			");
		$this->db->from('item_transfer AS item_transfer');
		$this->db->join('items_push AS items_push', 'items_push.transfer_id = item_transfer.transfer_id', 'left');
		$this->db->join('people AS people', 'item_transfer.employee_id = people.person_id', 'left');
		$this->db->join('stock_locations AS from_locations', 'from_locations.location_id = item_transfer.request_from_branch_id', 'left');
		$this->db->join('stock_locations AS to_locations', 'to_locations.location_id = item_transfer.request_to_branch_id', 'left');

		$this->db->where('transfer_type', 'PUSH');

		$this->db->where('DATE_FORMAT(transfer_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));

		if ($inputs['to_location_id'] != 'all') {
			$this->db->where('item_transfer.request_to_branch_id', $inputs['to_location_id']);
		}
		if ($inputs['from_location_id'] != 'all') {
			$this->db->where('item_transfer.request_from_branch_id', $inputs['from_location_id']);
		}
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('employee_id', $inputs['employee_id']);
		}


		$this->db->group_by('transfer_id');
		$this->db->order_by('transfer_time', 'desc');


		return $this->db->get()->result_array();
	}
	public function getTransferDataItems($transfer_id)
	{


		$this->db->select('items_push.*, 
		items.name AS name,
		items.item_number AS item_number,
		items.category AS category
		
			');
		$this->db->from('items_push AS items_push');
		$this->db->join('items AS items', 'items.item_id = items_push.item_id', 'left');
		$this->db->where("transfer_id", $transfer_id);



		return $this->db->get()->result_array();
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
			SUM(quantity_ordered) AS ordered_quantity,
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
		if ($inputs['supplier'] != "all") {
			$this->db->where('supplier_id', $inputs['supplier']);
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
			$this->db->select('name, item_number, category, quantity_purchased, item_cost_price,item_unit_price, serialnumber,total, discount_percent, item_location, quantity_ordered');
			$this->db->from('receivings_items_temp');
			$this->db->join('items', 'receivings_items_temp.item_id = items.item_id');
			$this->db->where('receiving_id = ' . $value['receiving_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}
	public function getAllReceivings($inputs)
	{
		$sql = "receivings.*, 
		CONCAT(employee.first_name, ' ', employee.last_name) AS employee_name, 
		SUM(receivings_items.quantity_purchased) AS quantity_purchased,
		SUM(receivings_items.receiving_quantity) AS quantity_ordered,
		supplier.company_name as supplier,
		SUM(receivings_items.item_cost_price * receivings_items.quantity_purchased) AS cost,
		SUM(receivings_items.item_unit_price * receivings_items.quantity_purchased) AS price
		";
		//all location for receiving items are same for a particular receiving_id
		if ($inputs['location_id'] != 'all') {
			$sql .= ',
			MAX(receivings_items.item_location) AS item_location';
		}

		$this->db->select($sql);
		$this->db->from('receivings');
		$this->db->join('people AS employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('receivings_items AS receivings_items', 'receivings_items.receiving_id = receivings.receiving_id', 'left');

		$this->db->where('receiving_time >=', $inputs['start_date'] . ' 00:00:00');
		$this->db->where('receiving_time <=', $inputs['end_date'] . ' 23:59:59');
		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['supplier'] != "all") {
			$this->db->where('supplier_id', $inputs['supplier']);
		}
		if ($inputs['employee_id'] != "all") {
			$this->db->where('employee_id', $inputs['employee_id']);
		}
		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased >', 0);
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased <', 0);
		}

		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_time', 'desc'); //today will come before tomorrow


		return $this->db->get()->result_array();
	}


	public function getProductSpecificReceivings($inputs)
	{

		$sql = "*, 
		CONCAT(employee.first_name, ' ', employee.last_name) AS employee_name, 
		SUM(receivings_items.quantity_purchased) AS quantity_purchased,
		SUM(receivings_items.receiving_quantity) AS quantity_ordered,
		supplier.company_name as supplier,
		SUM(receivings_items.item_cost_price * receivings_items.quantity_purchased) AS cost,
		SUM(receivings_items.item_unit_price * receivings_items.quantity_purchased) AS price
		";
		//all location for receiving items are same for a particular receiving_id
		if ($inputs['location_id'] != 'all') {
			$sql .= ',
			MAX(receivings_items.item_location) AS item_location';
		}

		$this->db->select($sql);
		$this->db->from('receivings');
		$this->db->join('people AS employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('receivings_items AS receivings_items', 'receivings_items.receiving_id = receivings.receiving_id', 'left');
		$this->db->join('items AS items', 'receivings_items.item_id = items.item_id', 'left');

		$this->db->where('receiving_time >=', $inputs['start_date'] . ' 00:00:00');
		$this->db->where('receiving_time <=', $inputs['end_date'] . ' 23:59:59');
		if ($inputs['location_id'] != 'all') {
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['supplier'] != "all") {
			$this->db->where('supplier_id', $inputs['supplier']);
		}
		if ($inputs['employee_id'] != "all") {
			$this->db->where('employee_id', $inputs['employee_id']);
		}
		if ($inputs['receiving_type'] == 'receiving') {
			$this->db->where('quantity_purchased >', 0);
		} elseif ($inputs['receiving_type'] == 'returns') {
			$this->db->where('quantity_purchased <', 0);
		}

		if ($inputs['item_id'] != "all") {
			$this->db->where('receivings_items.item_id', $inputs['item_id']);
		}

		$this->db->group_by('receivings_items.receiving_id');
		$this->db->order_by('receiving_time', 'desc'); //today will come before tomorrow


		return $this->db->get()->result_array();
	}

	public function getReceivingItemsData($receiving_id)
	{
		$this->db->select("receivings_items.*,
		items.name AS name,
		items.category AS category,
		items.item_number AS item_number,
		items.pack AS pack
		");
		$this->db->from('receivings_items AS receivings_items');
		$this->db->where("receiving_id", $receiving_id);
		$this->db->join('items AS items', 'items.item_id = receivings_items.item_id', 'left');
		return $this->db->get()->result_array();
	}

	public function getProductPriceList($inputs){
		//sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');
		$this->db->where('item_quantities.quantity >', 0);

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

		return $this->db->get()->result_array();
	}

	public function getOutOfStock($inputs){
		//sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');
		
		if ($inputs['type'] != 'minimum') {
			$this->db->where('item_quantities.quantity =', 0);
		}else{
			$this->db->where('item_quantities.quantity <= items.reorder_level');
		}

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

		return $this->db->get()->result_array();
	}

	public function getBelowReorderLevelCount(){
		$this->db->select("*");
		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');
		$this->db->where('item_quantities.quantity <= items.reorder_level');

		return $this->db->get()->num_rows();
	}
	public function getStockCount($inputs){
		// $this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['supplier'] != 'all') {
			$this->db->where('supplier_id', $inputs['supplier']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

		return $this->db->count_all_results();
	}

	public function getStockValue($inputs,$offset = 0){
		//sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['supplier'] != 'all') {
			$this->db->where('supplier_id', $inputs['supplier']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

		return $this->db->get()->result_array();
	}

	public function getAllItems($inputs){
		//sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['supplier'] != 'all') {
			$this->db->where('supplier_id', $inputs['supplier']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

		if ($inputs['prescription'] != 'all') {
			$this->db->where('prescriptions', $inputs['prescription']);
		}

		return $this->db->get()->result_array();
	}


	public function getVatItems($inputs){
		//sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');
		$this->db->where('apply_vat', 'YES');

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['supplier'] != 'all') {
			$this->db->where('supplier_id', $inputs['supplier']);
		}

		if ($inputs['prescription'] != 'all') {
			$this->db->where('prescriptions', $inputs['prescription']);
		}

		return $this->db->get()->result_array();
	}

	public function getSalesItemsForAnItem($item_id)
	{
		$this->db->select("sales_items.*,
		ROUND(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100),2) AS discount");
		$this->db->from('sales_items AS sales_items');
		$this->db->where("item_id", $item_id);
		// $this->db->join('items AS items', 'items.item_id = sales_items.item_id', 'left');
		return $this->db->get()->result_array();
	}

	public function getMarkupItems($inputs){
		// sql
		$this->db->select("*");

		$this->db->from('items AS items');
		$this->db->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id', 'left');
		$this->db->where('items.unit_price_markup >=', $inputs['start_markup']);
		$this->db->where('items.unit_price_markup <=', $inputs['end_markup']);

		// $this->db->where('items.unit_price_markup >=', 1.5);
		// $this->db->where('items.unit_price_markup <=', $inputs['end_markup']);

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('item_location', $inputs['location_id']);
		// }

		if ($inputs['dept'] != 'all') {
			$this->db->where('type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}

		if ($inputs['supplier'] != 'all') {
			$this->db->where('supplier_id', $inputs['supplier']);
		}

		if ($inputs['vated'] != 'all') {
			$this->db->where('apply_vat', $inputs['vated']);
		}

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
