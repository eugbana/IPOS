<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,	
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');
		$this->db->where('employee_id', $inputs['employee_id']);

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
		
		// return $this->db->last_query();
		foreach($data['summary'] as $key => $value)
		{
			$this->db->select('cost_price, sales_items_temp.name, sales_items_temp.category, serialnumber, sales_items_temp.description, quantity_purchased, subtotal, tax, total, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->join('items', 'items.name = sales_items_temp.name');
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
		$this->db->where('employee_id', $inputs['employee_id']);

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

	public function GetPrintData()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('customer_name' => $this->lang->line('reports_sold_to')),
				array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				array('oprice' => 'Original Price', 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type')))
		);
	}

	public function GetEmployeeSaleByIdAndDate($id, $start_date, $end_date)
	{
		$start_date	= $start_date . ' 00:00:00';
		$end_date	= $end_date . ' 23:59:00';
		$this->db->select('sale_id, sale_status');
		$this->db->from('sales');
		$this->db->where('employee_id', $id);
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
?>
