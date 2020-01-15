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

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('customer_name' => $this->lang->line('reports_sold_to')),

				array('total' => 'Discounted Total', 'sorter' => 'number_sorter'),
				array('total_vat' => 'VAT'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('discount' => 'Total Discount', 'sorter' => 'number_sorter'),
				array('total_payment' => 'Total Payment'),
				array('change_due' => 'Change Due'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comments' => $this->lang->line('reports_comments'))
			),
			'details' => array(
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				'Item Number',
				$this->lang->line('reports_description'),
				$this->lang->line('reports_quantity'),
				'Qty Type',
				'Total Cost',
				'Discounted Total',
				$this->lang->line('reports_profit'),
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

	public function getData(array $inputs)
	{

		$this->db->select('sales_items_temp.sale_id AS sale_id,
			MAX(sales_items_temp.sale_date) AS sale_date,
			SUM(sales_items_temp.quantity_purchased) AS items_purchased,
			MAX(sales_items_temp.customer_name) AS customer_name,
			
			MAX(sales_items_temp.qty_selected) AS qty_selected,
			
			SUM(sales_items_temp.total) AS total,
			
			SUM(sales_items_temp.vat) AS total_vat,
			SUM(sales_items_temp.discount) AS discount,
			MAX(sales_items_temp.sale_payment_amount) - SUM(sales_items_temp.total) - SUM(sales_items_temp.vat) AS change_due,
			MAX(sales_items_temp.sale_payment_amount) AS payment_amount,
			MAX(sales_items_temp.payment_type) AS payment_type,	
			MAX(sales_items_temp.comment) AS comment');
		$this->db->from('sales AS sales');
		$this->db->join('sales_items_temp AS sales_items_temp', 'sales_items_temp.sale_id = sales.sale_id');
		//profits,costs, and real quantities are calculated down

		if ($inputs['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $inputs['employee_id']);
		}
		if ($inputs['category'] != 'all') {
			$this->db->where('sales_items_temp.category', $inputs['category']);
		}
		if ($inputs['vatable'] != 'all') {
			if (strtolower($inputs['vatable']) == 'yes') {
				$this->db->where('sales_items_temp.vat >', 0);
			} else {
				$this->db->where('sales_items_temp.vat', 0);
			}
		}

		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('sales_items_temp.quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('sales_items_temp.quantity_purchased < 0');
		}

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
			$this->db->select('item_cost_price,item_unit_price,item_id,pack, sales_items_temp.name, sales_items_temp.category, serialnumber, sales_items_temp.description, quantity_purchased,sales_items_temp.qty_selected,  total, cost_retail,cost_wholesale, profit_retail,profit_wholesale, discount_percent,discount,vat');
			$this->db->from('sales_items_temp');
			//$this->db->join('items', 'items.name = sales_items_temp.name');//Some items have duplicates on names
			//so it is not best to use name here
			//$this->db->join('items', 'items.item_id = sales_items_temp.item_id');


			$this->db->where('sale_id', $value['sale_id']);

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
			$data['summary'][$key]['items_purchased'] = $qty;
			$data['summary'][$key]['cost'] = $t_cost;
			$data['summary'][$key]['profit'] = $t_profit;


			$this->db->select('used, earned');
			$this->db->from('sales_reward_points');
			$this->db->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $this->db->get()->result_array();
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
