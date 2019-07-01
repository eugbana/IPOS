<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Detailed_sales extends Report
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
				array('employee_name' => $this->lang->line('reports_sold_by')),
				array('customer_name' => $this->lang->line('reports_sold_to')),
				array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('sales_amount_tendered')),
				array('comment' => $this->lang->line('reports_comments'))),
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

	public function getDataBySaleId($sale_id)
	{
		$this->db->select('sale_id,
			sale_date,
			SUM(quantity_purchased) AS items_purchased,
			employee_name,
			customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			payment_type,
			comment');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row_array();
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id, 
			MAX(sale_date) AS sale_date, 
			SUM(quantity_purchased) AS items_purchased, 
			MAX(employee_name) AS employee_name, 
			MAX(customer_name) AS customer_name, 
			SUM(subtotal) AS subtotal, 
			SUM(tax) AS tax, 
			SUM(total) AS total, 
			SUM(cost) AS cost, 
			SUM(profit) AS profit, 
			MAX(payment_type) AS payment_type, 
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

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
			$this->db->select('name, category, quantity_purchased, item_location, serialnumber, description, subtotal, tax, total, cost, profit, discount_percent');
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

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

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
	
	public function getDataColumnsOnline()
	{
		return array(
			'summary' => array(
				array('id' => 'TransferID'),
				array('sale_date' => $this->lang->line('reports_date')),
				array('transfer_type' => 'Transaction'),
				array('customer' => 'Customer'),
				array('status' => 'Status')),
			'details' => array(
				'Item ID',
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				),
			
		);
	}

	

	public function getDataOnline($item_location)
	{
		$this->db->select('transfer_id, status,
			MAX(transfer_time) AS transfer_time, 
			transfer_type AS transfer_type');
		$this->db->from('item_transfer');
		
		//$this->db->or_where('transfer_type','PICKUP' );
		$this->db->where('status', 0);
		$this->db->where('request_to_branch_id',$item_location);
		$this->db->where('transfer_type', 'PICKUP');
		
		
			//$this->db->where('request_to_branch_id', $inputs['location_id']);

		

		$this->db->group_by('transfer_id');
		$this->db->order_by('MAX(transfer_time)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		$data['rewards'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('item_id, pulled_quantity, pushed_quantity');
			$this->db->from('items_push');
			$this->db->where('transfer_id', $value['transfer_id']);
			$data['details'][$key] = $this->db->get()->result_array();
			
		}

		return $data;
	}
}
?>
