<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Audit extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('audit');

		$this->load->library('barcode_lib');
		$this->load->library('sale_lib');
		$this->load->library('item_lib');

		$this->load->library('simplexlsx');
	}


	public function input()
	{
		
        $data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if (!($employee->role == 5 || $employee->role == 14)) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		// //get customers 
		// $customers = array('all' => 'All');
		// foreach ($this->Customer->search('')->result() as $customer) {
		// 	$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
		// }
		$data['employee'] = $employees;
		$data['customer'] = $customers;
        
		$this->load->view("audit/view", $data);
	}

	public function oldindex()
	{

		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		//$model->create($inputs);

		$headers = $this->xss_clean($model->getAuditDataColumns());
		// return print_r($headers);
		//$report_data = $model->getData($inputs);
		$report_data = $model->getAuditData();

		return print_r($report_data);

		$summary_data = array();

		foreach ($report_data as $row) {
			$summary_data[] = array(
				'id' => $row['audit_id'],
				'action_type' => $row['action_type'],
				'employee_name' => $row['employee_name'],
				'comment' => $row['description'],
				'audit_date' => date("Y-m-d h:i A", strtotime($row['audit_date'])),
			);
			
		}

		$data = array(
			'title'					=> 'Audit Trail',
			// 'report_title_data' 				=> $this->get_sales_report_data($inputs),
			'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'employee_id'			=> $employee_id,
		);

		$this->load->view('reports/audit_tabular_details', $data);
	}

	public function specific_employee($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = "all", $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		//$model->create($inputs);

		$headers = $this->xss_clean($model->getAuditDataColumns());
		//$report_data = $model->getData($inputs);
		$report_data = $model->getAuditData();

		$summary_data = array();

		foreach ($report_data as $row) {
			$summary_data[] = array(
				'id' => $row['audit_id'],
				'action' => $row['action'],
				'employee_name' => $row['employee_name'],
				'comment' => $row['description'],
				'audit_date' => date("Y-m-d h:i A", strtotime($row['audit_date'])),
			);
			
		}

		$data = array(
			'title'					=> 'Sales Report',
			'report_title_data' 				=> $this->get_sales_report_data($inputs),
			'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'details_data' 			=> $details_data,
			'details_data_rewards' 	=> $details_data_rewards,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'vatable'				=> $vatable,
			'credit'				=> $credit,
			'location_id'			=> $location_id,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type
		);

		$this->load->view('reports/audit_tabular_details', $data);
	}

	public function index()
	{
		//$this->sale_lib->clear_all();
		$filters = array(
			'sale' => FALSE,
			'receive' => FALSE,
			'delete'  => FALSE
		);

		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filtes = array_merge($filters, $filledup);

		$item_location = $this->sale_lib->get_sale_location();

		$data['sale_stuff'] = $this->Item->get_sales();

		$data['notice'] = $this->sale_lib->notice_transfer_items($item_location);
		$data['table_headers'] = $this->xss_clean(get_audits_manage_table_headers());
		//$data['table_headees'] = $this->xss_clean(get_items_manage_table_heders());
		$data['transfer'] = $this->sale_lib->global_transfer_items($item_location);
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array(
			'sale' => 'Sale (action)',
			'receive' => 'Receive (action)',
			'delete' => 'Delete (action)'
		);

		$employees = array('');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			$data['filters'][$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}

		$this->load->view('audit/manage', $data);
	}

	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$this->item_lib->set_item_location($this->input->get('stock_location'));

		$filters = array(
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'stock_location_id' => $this->item_lib->get_item_location(),
			'sale' => FALSE,
			'receive' => FALSE,
			'delete' => FALSE,
		);


		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		//$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers($filters));

		$items = $this->Item->search_audit($search, $filters, 0, $offset)->result();
		// return print_r($items);

		//$total_rows = $this->Item->get_found_rows($search, $filters);
		$found_items = array_slice($items, $offset, $limit); //limit is usually more than 0

		$total_rows = count($items);

		$data_rows = array();
		$data_rows = $found_items;
		// foreach ($found_items as $item) {
		// 	if (count(get_item_data_row($item, $this)) != 0) {
		// 		$data_rows[] = $this->xss_clean(get_item_data_row($item, $this));
		// 		if ($item->pic_filename != '') {
		// 			$this->_update_pic_filename($item);
		// 		}
		// 	}
		// }
		//$this->load->view('items/manage', $data);
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
}
