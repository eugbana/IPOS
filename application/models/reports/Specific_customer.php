<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Specific_customer extends Report
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

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('sold_by' => $this->lang->line('reports_sold_by')),
				array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comments' => $this->lang->line('reports_comments'))),
			'details' => array(
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_serial_number'),
				$this->lang->line('reports_description'),
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_subtotal'),
				$this->lang->line('reports_tax'),
				$this->lang->line('reports_total'),
				$this->lang->line('reports_cost'),
				$this->lang->line('reports_profit'),
				$this->lang->line('reports_discount')),
			'details_rewards' => array(
				$this->lang->line('reports_used'),
				$this->lang->line('reports_earned'))
		);
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id,
			MAX(sale_date) AS sale_date,
			SUM(quantity_purchased) AS items_purchased,
			MAX(employee_name) AS employee_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');
		$this->db->where('customer_id', $inputs['customer_id']);

		if($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}

		$this->db->group_by('sale_id');
		$this->db->order_by('MAX(sale_date)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		$data['rewards'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, serialnumber, description, quantity_purchased, subtotal, tax, total, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
			$this->db->select('used, earned');
			$this->db->from('sales_reward_points');
			$this->db->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}


	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		$this->db->where('customer_id', $inputs['customer_id']);

		if($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}

		return $this->db->get()->row_array();
	}

	public function getAudit($inputs)
	{
		$this->db->select("
		audit_logs.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
		");

		$this->db->from('audit_logs as audit_logs');
		$this->db->join('people as employee', 'employee.person_id = audit_logs.employee_id', 'left');

		$this->db->where("audit_logs.audit_time >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("audit_logs.audit_time <=", $inputs['end_date'] . ' 23:59:59');

		if ($inputs['employee'] != 'all') {
			$this->db->where('audit_logs.employee_id', $inputs['employee_id']);
		}

		if ($inputs['action_type'] != 'all') {
			$this->db->where('audit_logs.action_type', $inputs['action_type']);
		}

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('expenses.location_id', $inputs['location_id']);
		// }

		$this->db->group_by('audit_logs.audit_id');
		$this->db->order_by('audit_logs.audit_time', 'desc');

		return $this->db->get()->result_array();
	}

	public function getCredit($inputs)
	{
		$this->db->select("
		audit_logs.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
		");

		$this->db->from('audit_logs as audit_logs');
		$this->db->join('people as employee', 'employee.person_id = audit_logs.employee_id', 'left');

		$this->db->where("audit_logs.audit_time >= ", $inputs['start_date'] . ' 00:00:00');
		$this->db->where("audit_logs.audit_time <=", $inputs['end_date'] . ' 23:59:59');

		if ($inputs['employee'] != 'all') {
			$this->db->where('audit_logs.employee_id', $inputs['employee_id']);
		}

		if ($inputs['action_type'] != 'all') {
			$this->db->where('audit_logs.action_type', $inputs['action_type']);
		}

		// if ($inputs['location_id'] != 'all') {
		// 	$this->db->where('expenses.location_id', $inputs['location_id']);
		// }

		$this->db->group_by('audit_logs.audit_id');
		$this->db->order_by('audit_logs.audit_time', 'desc');

		return $this->db->get()->result_array();
	}
	public function getSpecializedCreditReport($inputs){
		if($inputs['customer'] == 'all'){
			$query3 = "SELECT balance,customer_id, first_name, last_name,credit_limit FROM ipos_customers, ipos_wallet JOIN ipos_people ON customer_id = ipos_people.person_id 
							WHERE id IN ( SELECT MAX(id) FROM ipos_wallet GROUP BY customer_id) AND ipos_wallet.customer_id= ipos_customers.person_id";
			return $this->db->query($query3)->result();
		}else{
			return $this->db->select("wallet.balance,wallet.amount,wallet.sale_id,wallet.narration")
				->from('wallet')
				->where('customer_id',$inputs['customer'])
				->get()
				->result();
		}
	}
	public function getWalletSummary($input){
//		$this->db->select_sum('sales_amount as sale_amount')
//			->db->select_sum('sales_amount as return_amount')
//			->db->from('sales')
//			->db->where('customer_id',$input['customer'])
//			->db->get()
		$cust = $input['customer'];

		return $this->db->query("select SUM(s.sales_amount) as sale_total,SUM(s2.sales_amount as return_total) from sales s,
 					sales s2 where s.sales_type=0 and s2.sales_type = 1 and s.customer_id='$cust' and s2.customer_id='$cust'")
			->get()->row();
	}
	public function getCreditReport($inputs)
	{
		$this->db->select("wallet.*,sales.*, customer.*, customer_user.*,
		CONCAT(customer.first_name,' ', customer.last_name) AS customer_name");
		$this->db->from('wallet as wallet');
		$this->db->join('sales as sales', 'sales.sale_id = wallet.sale_id', "left");
		$this->db->join('people as customer', 'customer.person_id = wallet.customer_id');
		$this->db->join('customers as customer_user', 'customer_user.person_id = customer.person_id');

		if ($inputs['customer'] != 'all') {
			$this->db->where('wallet.customer_id', $inputs['customer']);
		}

		// $this->db->where('wallet.date >=', $start);
		$this->db->where('wallet.balance <', 0);
		$this->db->order_by("wallet.date", 'desc');
		$query = $this->db->get();

		// print_r($query->result());
		// die();

		return $query->result_array();
	}
}
?>
