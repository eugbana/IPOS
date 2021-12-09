<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Specific_employee extends Report
{
	function __construct()
	{

		parent::__construct();
	}

	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table($inputs);
	}

	//audit logs table header
	public function getAuditDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => 'Audit ID'),
				array('employee_name' => 'Employee'),
				array('action' => 'Action Taken'),
				array('comment' => 'Comment'),
				array('audit_date' => 'Date'),
			)
		);
	}

	public function getAuditData()
	{
		$this->db->select("audit_logs.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name
		");
		$this->db->from('audit_logs AS audit_logs');
		$this->db->join('people as employee', 'employee.person_id = audit_logs.employee_id', 'left'); //there is always employee id
		return $this->db->get()->result_array();
	}

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('customer_name' => $this->lang->line('reports_sold_to')),

				// array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('total' => 'Total Amount', 'sorter' => 'number_sorter'),
				array('total_vat' => 'Total VAT'),
				array('credit' => 'Credit'),
				array('discount' => 'Total Discount', 'sorter' => 'number_sorter'),
				array('total_payment' => 'Total Payment'),
				array('change_due' => 'Change Due'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				// array('comments' => $this->lang->line('reports_comments'))
			),
			'details' => array(
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				'Item Number',

				$this->lang->line('reports_quantity'),
				'Unit Cost',
				'Unit Price',
				'Qty Type',
				'Total Cost',
				'Total Amount',
				'Discount(%)',
				'Discount',
				'VAT'
			),
			'details_rewards' => array(
				$this->lang->line('reports_used'),
				$this->lang->line('reports_earned')
			)
		);
	}
	public function getSalesItemsData($sale_id)
	{
		$this->db->select("sales_items.*,
		items.name AS name,
		items.category AS category,
		items.item_number AS item_number,
		items.pack AS pack,
		ROUND(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100),2) AS discount");
		$this->db->from('sales_items AS sales_items');
		$this->db->where("sale_id", $sale_id);
		$this->db->join('items AS items', 'items.item_id = sales_items.item_id', 'left');
		return $this->db->get()->result_array();
	}


	

	public function getPaymentTypes($sale_id)
	{
		$this->db->select("sales_payments.*");
		$this->db->from("sales_payments");
		$this->db->where("sale_id", $sale_id);
		return $this->db->get()->result_array();
	}
	public function getPayment($sale_id)
	{
		$this->db->select("
		SUM(payment_amount) AS payment_amount,
		GROUP_CONCAT(payment_type SEPARATOR ',') AS payment_type
				");
		$this->db->from("sales_payments");
		$this->db->where("sale_id", $sale_id);
		return $this->db->get()->row();
	}
	public function getCost($sale_id)
	{
		$this->db->select("quantity_purchased, item_cost_price,pack,qty_selected");
		$this->db->from("sales_items");
		$this->db->where("sale_id", $sale_id);
		$result = $this->db->get()->result();
		$cost = 0;
		foreach ($result as $row) {
			if (strtolower($row->qty_selected) == 'retail') {
				$cost += $row->item_cost_price * $row->quantity_purchased;
			} elseif (strtolower($row->qty_selected) == 'wholesale') {
				$cost += $row->item_cost_price * $row->pack * $row->quantity_purchased;
			}
		}
		return $cost;
	}
	public function getSalesData($inputs)
	{

		//GROUP_CONCAT(sales_payments.payment_type SEPARATOR ',') AS payment_type,
		$this->db->select("
			sales.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
			CONCAT(customer.first_name,' ', customer.last_name) AS customer_name,
			SUM(sales_items.quantity_purchased) as quantity_purchased,
			SUM(sales_items.vat) AS total_vat,
			ROUND(SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100)),2) AS discount,
			SUM(sales_items.item_unit_price * sales_items.quantity_purchased) AS amount
		");

		$this->db->from('sales as sales');
		$this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		$this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id
		$this->db->join('sales_items as sales_items', 'sales_items.sale_id = sales.sale_id');
		//$this->db->join('sales_payments as sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left');

		$this->db->where("sales.sale_status = 0"); //normal sales not suspended sales
		$this->db->where("sales.sale_time >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("sales.sale_time <=", $inputs['end_date'] . ' 23:59:59');

		if ($inputs['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $inputs['employee_id']);
		}


		if ($inputs['customer_id'] != 'all') {
			$this->db->where('sales.customer_id', $inputs['customer_id']);
		}
		if ($inputs['vatable'] != 'all') {

			$this->db->where('sales_items.apply_vat', strtoupper($inputs['vatable']));
		}

		if ($inputs['sale_type'] != "all") {
			if ($inputs['sale_type'] == 'sales') {
				$this->db->where('sales_items.quantity_purchased > 0');
			} elseif ($inputs['sale_type'] == 'returns') {
				$this->db->where('sales_items.quantity_purchased < 0');
			}
		}
		if ($inputs['discount'] != "all") {
			if (strtolower($inputs['discount']) == 'yes') {
				$this->db->where('sales_items.discount_percent > 0');
			} elseif (strtolower($inputs['discount']) == 'no') {
				$this->db->where('sales_items.discount_percent < 0');
			}
		}
		if ($inputs['credit'] != 'all') {
			if ($inputs['credit'] == 'YES') {
				$this->db->where('sales.credit > 0');
			} elseif ($inputs['credit'] == 'NO') {
				$this->db->where('sales.credit < 0');
			}
		}

		if ($inputs['payment_type'] != 'all') {
			$this->db->where('payment_types LIKE %' . $inputs['payment_type'] . '%');
		}
		if ($inputs['location_id'] != 'all') {
			$this->db->where('sales.location_id', $inputs['location_id']);
		}


		$this->db->group_by('sales.sale_id');
		$this->db->order_by('sale_time', 'desc');
//		var_dump($inputs);
//		echo '<br />';
//		var_dump($this->db->get_compiled_select());
//		exit();
		return $this->db->get()->result_array();
	}

	public function getSalesDataProductSpecific($inputs)
	{


		$this->db->select("
			*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
			CONCAT(customer.first_name,' ', customer.last_name) AS customer_name,
			SUM(sales_items.quantity_purchased) as quantity_purchased,
			SUM(sales_items.vat) AS total_vat,
			ROUND(SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100)),2) AS discount,
			SUM(sales_items.item_unit_price * sales_items.quantity_purchased) AS amount
		");

		$this->db->from('sales_items as sales_items');
		$this->db->join('sales as sales', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items as items', 'sales_items.item_id = items.item_id', 'left'); 

		// $this->db->from('sales_items');
		// $this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		// $this->db->join('items', 'sales_items.item_id = items.item_id', 'left'); 

		$this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		$this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id

		$this->db->where("sales.sale_status = 0"); //normal sales not suspended sales
		$this->db->where("sales.sale_time >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("sales.sale_time <=", $inputs['end_date'] . ' 23:59:59');


		// $this->db->select("
		// 	sales_items.*,
		// 	CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
		// 	CONCAT(customer.first_name,' ', customer.last_name) AS customer_name,
		// 	SUM(sales_items.quantity_purchased) as quantity_purchased,
		// 	SUM(sales_items.vat) AS total_vat,
		// 	ROUND(SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100)),2) AS discount,
		// 	SUM(sales_items.item_unit_price * sales_items.quantity_purchased) AS amount
		// ");

		// $this->db->from('sales_items as sales_items');
		// $this->db->join('items as items', 'sales_items.item_id = items.item_id', 'left'); 
		// $this->db->join('sales as sales', 'sales_items.sale_id = sales.sale_id');
		// $this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		// $this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id

		// $this->db->where("sales.sale_status = 0"); //normal sales not suspended sales
		// $this->db->where("sales.sale_time >= ", $inputs['start_date'] . ' 00:00:00');
		// $this->db->where("sales.sale_time <=", $inputs['end_date'] . ' 23:59:59');


		if ($inputs['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $inputs['employee_id']);
		}

		if ($inputs['customer_id'] != 'all') {
			$this->db->where('sales.customer_id', $inputs['customer_id']);
		}
		if ($inputs['vatable'] != 'all') {
			$this->db->where('sales_items.apply_vat', strtoupper($inputs['vatable']));
		}

		if($inputs['item_id'] != 'all') {
			// $where = "sales_items.item_id=" . $inputs['item_id'];
			// $this->db->where($where);
			// $this->db->where('sales_items.item_id', 5497);
			$this->db->where('sales_items.item_id', $inputs['item_id']);
		}

		if ($inputs['sale_type'] != "all") {
			if ($inputs['sale_type'] == 'sales') {
				$this->db->where('sales_items.quantity_purchased > 0');
			} elseif ($inputs['sale_type'] == 'returns') {
				$this->db->where('sales_items.quantity_purchased < 0');
			}
		}
		if ($inputs['discount'] != "all") {
			if (strtolower($inputs['discount']) == 'yes') {
				$this->db->where('sales_items.discount > 0');
			} elseif (strtolower($inputs['discount']) == 'no') {
				$this->db->where('sales_items.discount < 0');
			}
		}
		if ($inputs['credit'] != 'all') {
			if ($inputs['credit'] == 'YES') {
				$this->db->where('sales.credit > 0');
			} elseif ($inputs['credit'] == 'NO') {
				$this->db->where('sales.credit < 0');
			}
		}

		if ($inputs['payment_type'] != 'all') {
			$this->db->where('payment_types LIKE %' . $inputs['payment_type'] . '%');
		}
		if ($inputs['location_id'] != 'all') {
			$this->db->where('sales.location_id', $inputs['location_id']);
		}


		$this->db->group_by('sales.sale_id');
		// $this->db->group_by('sales_items.item_id');
		$this->db->order_by('sale_time', 'desc');

		return $this->db->get()->result_array();
	}

	public function getExpenses($inputs)
	{
		$this->db->select("
			expenses.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
			SUM(expenses.amount) AS total_amount,
			expense_categories.name as category_name,
			expense_categories.type as expense_category_type,
		");

		$this->db->from('expenses as expenses');
		$this->db->join('people as employee', 'employee.person_id = expenses.employee_id', 'left');
		$this->db->join('expense_categories as expense_categories', 'expense_categories.id = expenses.expense_category_id', 'left');

		$this->db->where("expenses.created_at >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("expenses.created_at <=", $inputs['end_date'] . ' 23:59:59');

		if ($inputs['employee'] != 'all') {
			$this->db->where('expenses.employee_id', $inputs['employee_id']);
		}

		if ($inputs['type'] != 'all') {
			$this->db->where('expenses.type', strtoupper($inputs['type']));
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('expense_categories.id', $inputs['category']);
		}

		if ($inputs['expense_category_type'] != 'all') {
			$this->db->where('expense_categories.type', $inputs['expense_category_type']);
		}

		if ($inputs['location_id'] != 'all') {
			$this->db->where('expenses.location_id', $inputs['location_id']);
		}

		$this->db->group_by('expenses.id');
		$this->db->order_by('expenses.created_at', 'desc');

		return $this->db->get()->result_array();
	}

	public function getExpiry(array $inputs){
		$this->db->select("item_expiry.batch_no,item_expiry.expiry,item_expiry.quantity,items.*");

		$this->db->from('item_expiry AS item_expiry');
		$this->db->join('items AS items', 'items.item_id = item_expiry.item_id');
		$this->db->join('item_quantities', 'item_quantities.item_id = item_expiry.item_id');

		if ($inputs['dept'] != 'all') {
			$this->db->where('items.type', $inputs['dept']);
//			var_dump($inputs['dept']);
//			exit();
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('items.category', $inputs['category']);
		}

		$this->db->where("item_expiry.expiry >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("item_quantities.quantity > ", 0);
		$this->db->where("item_expiry.expiry <=", $inputs['end_date'] . ' 23:59:59');

		return $this->db->get()->result_array();
	}

	public function getExpired(array $inputs){
		$this->db->select("
			*
		");

		$this->db->from('item_expiry AS item_expiry');
		$this->db->join('items AS items', 'items.item_id = item_expiry.item_id');

		if ($inputs['dept'] != 'all') {
			$this->db->where('items.type', $inputs['dept']);
		}

		if ($inputs['category'] != 'all') {
			$this->db->where('items.category', $inputs['category']);
		}

		$today = date('Y-m-d');

		$this->db->where("item_expiry.expiry <=", $today . ' 23:59:59');

		return $this->db->get()->result_array();
	}

	public function getExpiryItemsCount($days = 90){
		$this->db->select("*");

		$today = date("Y-m-d", time() + 86400 * 0);
		$dateToLookUp = date("Y-m-d", time() + 86400 * $days);

		$this->db->from('item_expiry AS item_expiry');
		$this->db->join('items AS items', 'items.item_id = item_expiry.item_id');

		$this->db->where("item_expiry.expiry >= ", $today . ' 00:00:00');
		$this->db->where("item_expiry.expiry <=", $dateToLookUp . ' 23:59:59');

		return $this->db->get()->num_rows();
	}

	public function getExpiryItemsList($days = 90){
		$this->db->select("
			*
		");

		$today = date("Y-m-d", time() + 86400 * 0);
		$dateToLookUp = date("Y-m-d", time() + 86400 * $days);

		$this->db->from('item_expiry AS item_expiry');
		$this->db->join('items AS items', 'items.item_id = item_expiry.item_id');

		$this->db->where("item_expiry.expiry >= ", $today . ' 00:00:00');
		$this->db->where("item_expiry.expiry <=", $dateToLookUp . ' 23:59:59');

		return $this->db->get()->result_array();
	}

	public function getSalesMarkupItems($inputs)
	{

		$this->db->select("
			*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
			CONCAT(customer.first_name,' ', customer.last_name) AS customer_name,
			SUM(sales_items.quantity_purchased) as quantity_purchased,
			SUM(sales_items.vat) AS total_vat,
			ROUND(SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100)),2) AS discount,
			SUM(sales_items.item_unit_price * sales_items.quantity_purchased) AS amount	
			
		");

		// (((sales_items.item_unit_price - items.cost_price) * 0.01) + 1) as markup_value

		$this->db->from('sales_items as sales_items');
		$this->db->join('sales as sales', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items as items', 'sales_items.item_id = items.item_id', 'left'); 

		// $this->db->from('sales_items');
		// $this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		// $this->db->join('items', 'sales_items.item_id = items.item_id', 'left'); 

		$this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		$this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id

		$this->db->where("sales.sale_status = 0"); //normal sales not suspended sales
		$this->db->where("sales.sale_time >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("sales.sale_time <=", $inputs['end_date'] . ' 23:59:59');

		//markup
		// Sales Markup Calc:  Selling Price - Cost Price = MarkPrice: To get percentage: (MarkupPrice * 0.01) + 1

		// $this->db->where("sales_items.item_unit_price - items.cost_price");
		
		// $this->db->where("(((sales_items.item_unit_price - items.cost_price) * 0.01) + 1) >= ", $inputs['start_markup']);
		// $this->db->where("(((sales_items.item_unit_price - items.cost_price) * 0.01) + 1) <=", $inputs['end_markup']);

		$this->db->where("(sales_items.item_unit_price / items.cost_price) >= ", $inputs['start_markup']);
		$this->db->where("(sales_items.item_unit_price / items.cost_price) <=", $inputs['end_markup']);


		// $this->db->select("
		// 	sales_items.*,
		// 	CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
		// 	CONCAT(customer.first_name,' ', customer.last_name) AS customer_name,
		// 	SUM(sales_items.quantity_purchased) as quantity_purchased,
		// 	SUM(sales_items.vat) AS total_vat,
		// 	ROUND(SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent /100)),2) AS discount,
		// 	SUM(sales_items.item_unit_price * sales_items.quantity_purchased) AS amount
		// ");

		// $this->db->from('sales_items as sales_items');
		// $this->db->join('items as items', 'sales_items.item_id = items.item_id', 'left'); 
		// $this->db->join('sales as sales', 'sales_items.sale_id = sales.sale_id');
		// $this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		// $this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id

		// $this->db->where("sales.sale_status = 0"); //normal sales not suspended sales
		// $this->db->where("sales.sale_time >= ", $inputs['start_date'] . ' 00:00:00');
		// $this->db->where("sales.sale_time <=", $inputs['end_date'] . ' 23:59:59');


		if ($inputs['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $inputs['employee_id']);
		}

		if ($inputs['customer_id'] != 'all') {
			$this->db->where('sales.customer_id', $inputs['customer_id']);
		}
		if ($inputs['vatable'] != 'all') {
			$this->db->where('sales_items.apply_vat', strtoupper($inputs['vatable']));
		}

		if($inputs['item_id'] != 'all') {
			// $where = "sales_items.item_id=" . $inputs['item_id'];
			// $this->db->where($where);
			// $this->db->where('sales_items.item_id', 5497);
			$this->db->where('sales_items.item_id', $inputs['item_id']);
		}

		if ($inputs['sale_type'] != "all") {
			if ($inputs['sale_type'] == 'sales') {
				$this->db->where('sales_items.quantity_purchased > 0');
			} elseif ($inputs['sale_type'] == 'returns') {
				$this->db->where('sales_items.quantity_purchased < 0');
			}
		}
		if ($inputs['discount'] != "all") {
			if (strtolower($inputs['discount']) == 'yes') {
				$this->db->where('sales_items.discount > 0');
			} elseif (strtolower($inputs['discount']) == 'no') {
				$this->db->where('sales_items.discount < 0');
			}
		}
		if ($inputs['credit'] != 'all') {
			if ($inputs['credit'] == 'YES') {
				$this->db->where('sales.credit > 0');
			} elseif ($inputs['credit'] == 'NO') {
				$this->db->where('sales.credit < 0');
			}
		}

		if ($inputs['payment_type'] != 'all') {
			$this->db->where('payment_types LIKE %' . $inputs['payment_type'] . '%');
		}
		if ($inputs['location_id'] != 'all') {
			$this->db->where('sales.location_id', $inputs['location_id']);
		}


		$this->db->group_by('sales.sale_id');
		// $this->db->group_by('sales_items.item_id');
		$this->db->order_by('sale_time', 'desc');

		return $this->db->get()->result_array();
	}

	public function getAllItemsData($inputs)
	{
		//GROUP_CONCAT(sales_payments.payment_type SEPARATOR ',') AS payment_type,
		$this->db->select("
			items.*
		");

		// $this->db->from('sales as sales');
		// $this->db->join('people as customer', 'customer.person_id = sales.customer_id', 'left'); //to join null vaues because there are not always customer id
		// $this->db->join('people as employee', 'employee.person_id = sales.employee_id', 'left'); //there is always employee id
		// $this->db->join('sales_items as sales_items', 'sales_items.sale_id = sales.sale_id');

		if ($inputs['vatable'] != 'all') {
			$this->db->where('sales_items.apply_vat', strtoupper($inputs['vatable']));
		}

		if ($inputs['location_id'] != 'all') {
			$this->db->where('sales.location_id', $inputs['location_id']);
		}

		return $this->db->get()->result_array();
	}


	public function getData(array $inputs)
	{

		$this->db->select('sales_items_temp.sale_id AS sale_id,
			MAX(sales_items_temp.sale_date) AS sale_date,
			SUM(sales_items_temp.quantity_purchased) AS items_purchased,
			MAX(sales_items_temp.customer_name) AS customer_name,
			MAX(sales_items_temp.employee_name) AS employee_name,
			
			MAX(sales_items_temp.qty_selected) AS qty_selected,
			
			SUM(sales_items_temp.total) AS total,
			
			SUM(sales_items_temp.vat) AS total_vat,
			SUM(sales_items_temp.discount) AS discount,
			MAX(sales_items_temp.sale_payment_amount) - SUM(sales_items_temp.total) - SUM(sales_items_temp.vat) AS change_due,
			MAX(sales_items_temp.sale_payment_amount) AS payment_amount,
			MAX(sales_items_temp.payment_type) AS payment_type,
			MAX(sales_items_temp.authorizer) AS authorizer,	
			MAX(sales_items_temp.comment) AS comment');
		$this->db->from('sales AS sales');
		$this->db->join('sales_items_temp AS sales_items_temp', 'sales_items_temp.sale_id = sales.sale_id');
		//profits,costs, and real quantities are calculated down
		$this->db->where('sales.sale_status', 0); //not suspended sales
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $inputs['employee_id']);
		}
		// if ($inputs['category'] != 'all') {
		// 	$this->db->where('sales_items_temp.category', $inputs['category']);
		// }
		if ($inputs['customer_id'] != 'all') {
			$this->db->where('sales.customer_id', $inputs['customer_id']);
		}
		// if ($inputs['vatable'] != 'all') {
		// 	if (strtolower($inputs['vatable']) == 'yes') {
		// 		$this->db->where('sales_items_temp.vat >', 0);
		// 	} else {
		// 		$this->db->where('sales_items_temp.vat', 0);
		// 	}
		// }

		// if ($inputs['sale_type'] == 'sales') {
		// 	$this->db->where('sales_items_temp.quantity_purchased > 0');
		// } elseif ($inputs['sale_type'] == 'returns') {
		// 	$this->db->where('sales_items_temp.quantity_purchased < 0');
		// }

		$this->db->group_by('sale_id');
		$this->db->order_by('sale_date');

		$data = array();

		$data['summary'] = $this->db->get()->result_array();



		$data['details'] = array();
		$data['rewards'] = array();

		// return $this->db->last_query();


		foreach ($data['summary'] as $key => $value) {
			//change item_quantity for wholesale and retail items


			//$this->db->distinct('items.item_id');
			$this->db->select('item_cost_price,item_unit_price,item_id,pack,vat_applied, sales_items_temp.name, sales_items_temp.category,employee_name, serialnumber, sales_items_temp.description, quantity_purchased,sales_items_temp.qty_selected,  total, cost_retail,cost_wholesale, profit_retail,profit_wholesale, discount_percent,discount,vat');
			$this->db->from('sales_items_temp');
			//$this->db->join('items', 'items.name = sales_items_temp.name');//Some items have duplicates on names
			//so it is not best to use name here
			//$this->db->join('items', 'items.item_id = sales_items_temp.item_id');


			$this->db->where('sale_id', $value['sale_id']);
			if ($inputs['category'] != 'all') {
				$this->db->where('category', $inputs['category']);
			}
			if ($inputs['vatable'] != 'all') {
				if (strtolower($inputs['vatable']) == 'yes') {
					$this->db->where('vat_applied', "YES");
				} else {
					$this->db->where('vat_applied', "NO");
				}
			}
			if ($inputs['discount'] != 'all') {
				if (strtolower($inputs['discount']) == 'yes') {
					$this->db->where('discount > 0');
				} else {
					$this->db->where('discount <= 0');
				}
			}

			if ($inputs['sale_type'] == 'sales') {
				$this->db->where('quantity_purchased > 0');
			} elseif ($inputs['sale_type'] == 'returns') {
				$this->db->where('quantity_purchased < 0');
			}


			$data['details'][$key] = $this->db->get()->result_array();
			//go through, data['details'], use pack, quantity_purchased and qty_selected to update the real total 
			//quantity on data['summary'] above. this is because, wholesales don't have real quantities but packs
			//we also need to update cost and profit here.
			//we will also tweak, data['details']'s cost and price here
			$qty = 0;
			$t_cost = 0;
			$t_profit = 0;


			foreach ($data['details'][$key] as $index => $row) {
				if (strtolower($row['qty_selected']) == 'wholesale') {
					$qty += $row['pack'] * $row['quantity_purchased'];
					$t_cost += $row['cost_wholesale'];
					$t_profit += $row['profit_wholesale'];
				} else {
					$qty += $row['quantity_purchased'];
					$t_cost += $row['cost_retail'];
					$t_profit += $row['profit_retail'];
				}
			}
			//updating the wrong data in the data[summary] with some results of the sales_items_temp queries
			$data['summary'][$key]['items_purchased'] = $qty;
			$data['summary'][$key]['cost'] = $t_cost;
			$data['summary'][$key]['profit'] = $t_profit;

			//fetching the rewards from rewards table
			$this->db->select('used, earned');
			$this->db->from('sales_reward_points');
			$this->db->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $this->db->get()->result_array();


			//get all the payment method of the sales
			$this->db->select('payment_type,payment_amount');
			$this->db->from('sales_payments');
			$this->db->where('sale_id', $value['sale_id']);
			$types = "";
			$re = $this->db->get()->result_array();
			$types_array = array();
			foreach ($re as $k => $value) {
				$types_array[] = $value['payment_type'];
				$types .= $value['payment_type'];
				if ($k < count($re) - 1)
					$types .= ",";
			}
			$data['summary'][$key]['payment_type'] = $types;
			//the the sales payment to the summary report
			$data['summary'][$key]['sales_payment'] = $re;

			//check whether this sales query has payment_type set.
			//if yes, check whether the sales has the payment type, if no, unset the sale from summary array,
			//and also unset the sale from the details array using the $key on both arrays
			if ($inputs['payment_type'] != "all") {
				if (!in_array($inputs['payment_type'], array_values($types_array))) {

					unset($data['summary'][$key]);
					unset($data['details'][$key]);
				}
			}
		}
		return $data;
	}
	/**
	 * This function replaces the getSummaryData below
	 */
	public function getSummaryData(array $summary_data)
	{
		//calculate the summary data from the summary
		$s_total = 0;
		$s_discount = 0;
		$s_vat = 0;
		$s_cost = 0;
		$s_profit = 0;
		foreach ($summary_data as $key => $row) {
			$s_total   += $row['total'];
			$s_discount   += $row['discount'];
			$s_vat  +=  $row['total_vat'];
			$s_cost  += $row['cost'];
			$s_profit += $row['profit'];
		}
		return array(
			'total' => $s_total,
			'cost' => $s_cost,
			'profit' => $s_profit,
			'vat' => $s_vat,
			'discount' => $s_discount
		);
	}

	public function getSummaryData1(array $inputs)
	{
		$this->db->select('SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit, SUM(vat) AS vat, SUM(discount) AS discount');
		$this->db->from('sales_items_temp');
		if ($inputs['employee_id'] != 'all') {
			$this->db->where('employee_id', $inputs['employee_id']);
		}
		if ($inputs['category'] != 'all') {
			$this->db->where('category', $inputs['category']);
		}
		if ($inputs['vatable'] != 'all') {
			if (strtolower($inputs['vatable']) == 'yes') {
				$this->db->where('vat >', 0);
			} else {
				$this->db->where('vat', 0);
			}
		}
		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		}

		return $this->db->get()->row_array();
	}

	public function GetPrintData()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('qty_selected' => 'Sales Type'),
				array('customer_name' => $this->lang->line('reports_sold_to')),
				array('employee_name' => 'Employee'),
				//array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				//array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				//array('oprice' => 'Original Price', 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('discount' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('total_vat' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_amount' => 'Total Payment', 'sorter' => 'number_sorter'),
				array('change_due' => 'Change Due', 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type'))
			)
		);
	}

	public function GetEmployeeSaleByIdAndDate($id, $start_date, $end_date)
	{
		$start_date	= $start_date . ' 00:00:00';
		$end_date	= $end_date . ' 23:59:00';
		$this->db->select('sale_id, sale_status');
		$this->db->from('sales');
		if ($id != 'all') {
			$this->db->where('employee_id', $id);
		}
		// $this->db->where('sale_status', 1);
		$this->db->where('sale_time >=', $start_date);
		$this->db->where('sale_time <=', $end_date);
		return $this->db->get()->result();
	}

	public function GetSaleTotalAmountBySaleId($saleId)
	{
		$this->db->select('payment_amount');
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $saleId);
		return $this->db->get()->result();
	}
}
