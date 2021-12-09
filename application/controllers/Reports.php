<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('Secure_Controller.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reports extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('reports');
		$this->load->library('report_lib');
		$this->load->model('Expenses');

		$method_name = $this->uri->segment(2);
		$exploder = explode('_', $method_name);

		if (sizeof($exploder) > 1) {
			preg_match('/(?:inventory)|([^_.]*)(?:_graph|_row)?$/', $method_name, $matches);
			preg_match('/^(.*?)([sy])?$/', array_pop($matches), $matches);
			$submodule_id = $matches[1] . ((count($matches) > 2) ? $matches[2] : 's');

			$this->track_page('reports/' . $submodule_id, 'reports_' . $submodule_id);

			// check access to report submodule
			// if ($submodule_id != 'transfers') { //everyone who has access to report should have access to transfers too
			// 	if (!$this->Employee->has_grant('reports_' . $submodule_id, $this->Employee->get_logged_in_employee_info()->person_id)) {
			// 		redirect('no_access/reports/reports_' . $submodule_id);
			// 	}
			// }
		}

		$this->load->helper('report');
	}

	//Initial report listing screen
	public function index() //remove the s
	{
//	    echo "here";
		$data['grants'] = $this->xss_clean($this->Employee->get_employee_grants($this->session->userdata('person_id')));
        $this->load->view('reports/listing_account', $data);
//		$this->load->view('reports/listing', $data);
//        $this->account_report();
	}
	public function account_report()
	{

		$data['grants'] = $this->xss_clean($this->Employee->get_employee_grants($this->session->userdata('person_id')));

		$this->load->view('reports/listing_account', $data);
	}

	//Summary sales report
	public function summary_sales($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;


		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));


		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_sales_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary categories report
	public function summary_categories($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;

		$report_data = $model->getData($inputs);

		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'category' => $row['category'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary customers report
	public function summary_customers($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'customer_name' => $row['customer'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_customers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}


	public function date_input_recv1()
	{
		$data = array();
		$stock_locations = $data = $this->xss_clean($this->Stock_location->get_allowed_locations('receivings'));
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'receiving';

		$this->load->view('reports/date_input1', $data);
	}
	
	//Summary suppliers report
	public function summary_suppliers($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'supplier_name' => $row['supplier'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_suppliers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary items report
	public function summary_items($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'item_name' => $row['name'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax'  => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_items_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary employees report
	public function summary_employees($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'employee_name' => $row['employee'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_employees_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary taxes report
	public function summary_taxes($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'tax_percent' => $row['percent'],
				'report_count' => $row['count'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary discounts report
	public function summary_discounts($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'discount' => $row['discount_percent'],
				'count' => $row['count']
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_discounts_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Summary payments report
	public function summary_payments($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'payment_type' => $row['payment_type'],
				'report_count' => $row['count'],
				'amount_tendered' => to_currency($row['payment_amount'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_payments_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $summary
		);

		$this->load->view('reports/tabular', $data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	public function date_input()
	{
		$data = array();
		$stock_locations = $data = $this->xss_clean($this->Stock_location->get_allowed_locations('sales'));
		$stock_locations['all'] = $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';

		$this->load->view('reports/date_input', $data);
	}
    public function date_input_ir()
    {
        $data = array();
        $stock_locations = $data = $this->xss_clean($this->Stock_location->get_allowed_locations('sales'));
        $stock_locations['all'] = $this->lang->line('reports_all');
        $data['stock_locations'] = array_reverse($stock_locations, TRUE);
        $data['mode'] = 'irecharge';
        $this->load->view('reports/date_input', $data);
    }

	public function date_input_expenses()
	{

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$cate_list = $this->Expenses->categories_list();
		
		
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
		}
		$data['categories'] = $categories;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if (!($employee->role == 5 || $employee->role == 14)) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		//get customers 
		// $customers = array('all' => 'All');
		// foreach ($this->Customer->search('')->result() as $customer) {
		// 	$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
		// }
		
		$data['employees'] = $employees;
		// $data['customer'] = $customers;
		$data['type'] = array(
			'all' => 'Both',
			'OUTFLOW' => 'OUTFLOW',
			'INFLOW' => 'INFLOW'
		);

		$data['expense_type'] = array(
			'all' => 'Both',
			'ADMINISTRATIVE' => 'ADMINISTRATIVE',
			'OVERHEAD' => 'OVERHEAD'
		);

		$this->load->view('reports/date_input_expense', $data);
	}

	public function date_input_audit()
	{

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$action_list = $this->Audits->get_action_types();

		// print_r($action_list);
		// die();
		
		$actions = array('all' => 'All');
		foreach ($action_list as $row => $value) {
			// print_r($value->action_type);
			// die();
			$actions[$value->action_type] = ucfirst($value->action_type);
		}
		$data['actions'] = $actions;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		
		$data['employees'] = $employees;

		$this->load->view('reports/date_input_audit', $data);
	}

	public function date_input_credit()
	{

		$data = array();
//		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		
//		foreach ($loc as $l) {
//			$stock_locations[$l->location_id] = $l->location_name;
//		}
//		$stock_locations['all'] =  $this->lang->line('reports_all');
//		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
        $data['current_location_name'] = $this->Employee->get_logged_in_employee_info()->branch_name;

		$customers = array('all' => 'All');
		foreach ($this->Customer->get_all()->result() as $employee) {
			//just show all customerss
			$customers[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		
		$data['customers'] = $customers;

		$this->load->view('reports/date_input_credit', $data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	public function date_input_sales()
	{

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';


		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
        $data['logged_in_role'] = $this->Employee->get_logged_in_employee_info()->role;

		$cate_list = $this->Item->categories_list();
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
		}
		$data['categories'] = $categories;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if (!($employee->role == 5 || $employee->role == 14)) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		//get customers 
		$customers = array('all' => 'All');
		foreach ($this->Customer->search('')->result() as $customer) {
			$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
		}
		$data['employee'] = $employees;
		$data['customer'] = $customers;
		$data['vatable'] = array(
			'all' => 'Both',
			'NO' => 'NO',
			'YES' => 'YES'
		);
		$payment_types = array('all' => 'All');
		foreach ($this->Sale->get_payment_options() as $key => $value) {
			$payment_types[$value] = $value;
		}
		$data['payment_types'] = $payment_types;

		$this->load->view('reports/date_input', $data);
	}

	public function date_product_input_sales()
	{

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';


		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$cate_list = $this->Item->categories_list();
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
		}
		$data['categories'] = $categories;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if (!($employee->role == 5 || $employee->role == 14)) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		//get customers 
		$customers = array('all' => 'All');
		foreach ($this->Customer->search('')->result() as $customer) {
			$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
		}
		$data['employee'] = $employees;
		$data['customer'] = $customers;
		$data['vatable'] = array(
			'all' => 'Both',
			'NO' => 'NO',
			'YES' => 'YES'
		);
		$payment_types = array('all' => 'All');
		foreach ($this->Sale->get_payment_options() as $key => $value) {
			$payment_types[$value] = $value;
		}
		$data['payment_types'] = $payment_types;

		$this->load->view('reports/date_product_input', $data);
	}

	public function date_item_inventory_input()
	{

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
		$data['mode'] = 'sale';


		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$cate_list = $this->Item->categories_list();
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
		}
		$data['categories'] = $categories;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if (!($employee->role == 5 || $employee->role == 14)) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		//get customers 
		$customers = array('all' => 'All');
		foreach ($this->Customer->search('')->result() as $customer) {
			$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
		}
		$data['employee'] = $employees;
		$data['customer'] = $customers;
		$data['vatable'] = array(
			'all' => 'Both',
			'NO' => 'NO',
			'YES' => 'YES'
		);
		$payment_types = array('all' => 'All');
		foreach ($this->Sale->get_payment_options() as $key => $value) {
			$payment_types[$value] = $value;
		}
		$data['payment_types'] = $payment_types;

		$this->load->view('reports/date_item_inventory_input', $data);
	}


	public function date_expiry_items()
	{

		//clear dept
		$this->report_lib->clear_dept();
		$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
		$this->report_lib->set_dept($dept);

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$cate_list = $this->Item->categories_list_by_dept($dept);
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
			// $categories[$value['name']] = $value['name'];
		}
		$data['categories'] = $categories;

		$data['depts'] = array(
			'all' => 'Both',
			'Pharmacy' => 'Pharmacy',
			'Superstore' => 'Superstore'
		);

		$data['selected_dept'] = $dept;

		$this->load->view('reports/date_expiry_items', $data);
	}

	public function date_expired_items()
	{

		//clear dept
		$this->report_lib->clear_dept();
		$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
		$this->report_lib->set_dept($dept);

		$data = array();
		$loc = $this->Stock_location->get_all()->result();
		$stock_locations = array();
		foreach ($loc as $l) {
			$stock_locations[$l->location_id] = $l->location_name;
		}
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$cate_list = $this->Item->categories_list_by_dept($dept);
		$categories = array('all' => 'All');
		foreach ($cate_list as $row => $value) {
			$categories[$value['id']] = $value['name'];
			// $categories[$value['name']] = $value['name'];
		}
		$data['categories'] = $categories;

		$data['depts'] = array(
			'all' => 'Both',
			'Pharmacy' => 'Pharmacy',
			'Superstore' => 'Superstore'
		);

		$data['selected_dept'] = $dept;

		$this->load->view('reports/date_expired_items', $data);
	}

	public function expiry_items($start_date = null, $end_date =null, $dept = "all", $category = "all", $location_id=null){
	    if($location_id == null){
	        $location_id = $this->Employee->get_logged_in_employee_info()->branch_id;
        }
		if($end_date == null){
	        $end_date = date('Y-m-d',strtotime('+ 1 day'));
        }
		if($start_date == null){
	        $end_date = date('Y-m-d',strtotime('- 1 day'));
        }
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('start_date' => date('Y-m-d'), 'end_date' => $end_date, 'dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);

		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getExpiry($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0
		);

		foreach ($report_data as $item) {

			//update totals
			$totals['total'] = $totals['total'] + $item['unit_price'];
			$totals['cost'] = $totals['cost'] +  $item['cost_price'];

			$details_data[$item['id']][] = array(
				'id' => $item['id'],
				'name' => $item['name'],
				'category' => $item['category'],
				'item_number' => $item['item_number'],
				'quantity' => $item['quantity'],
				'cost_price' => to_currency($item['cost_price']),
				'unit_price' => to_currency($item['unit_price']),
				'expiry_date' => $item['expiry'],
				'batch_no' => $item['batch_no'],
				'shelf' => $item['shelf'],
			);
		}

		$data = array(
			'title'					=> "Detailed Expiry Items Report",
			'report_title_data'		=> $this->get_expiry_report_data($inputs),
			// 'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'category'					=> $category,
			'dept'					=> $dept,
			'location_id'			=> $location_id,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$data['is_expired'] = false;

		$this->load->view('reports/tabular_details_print_expiry', $data);
	}

	public function expired_items($dept = "all", $category = "all", $location_id = 2){
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);

		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getExpired($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0
		);

		foreach ($report_data as $item) {

			//update totals
			$totals['total'] = $totals['total'] + $item['unit_price'];
			$totals['cost'] = $totals['cost'] +  $item['cost_price'];

			$details_data[$item['id']][] = array(
				'id' => $item['id'],
				'name' => $item['name'],
				'category' => $item['category'],
				'item_number' => $item['item_number'],
				'quantity' => $item['quantity'],
				'cost_price' => to_currency($item['cost_price']),
				'unit_price' => to_currency($item['unit_price']),
				'expiry_date' => $item['expiry'],
				'batch_no' => $item['batch_no'],
				'shelf' => $item['shelf'],
			);
		}

		$data = array(
			'title'					=> "Detailed Expired Items Report",
			'report_title_data'		=> $this->get_expired_report_data($inputs),
			// 'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'category'					=> $category,
			'dept'					=> $dept,
			'location_id'			=> $location_id,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$data['is_expired'] = true;

		$this->load->view('reports/tabular_details_print_expiry', $data);
	}

	//zero expiry items selected.
	public function exit_expiry_items($start_date, $end_date, $location_id){
		//here
	}

	private function get_expiry_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Expiry Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_expired_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	public function print_filtered_expiry_report_items_export($start_date, $end_date, $dept = "all", $category = "all", $location_id)
	{
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		// $inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);
		$inputs = array('start_date' => date('Y-m-d'), 'end_date' => $end_date, 'dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;
		//date('Y-m-d')

		$report_data = $model->getExpiry($inputs);
		// return print_r(count($report_data));

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Category");
		$sheet->setCellValue("D1", "Item Number");
		$sheet->setCellValue("E1", "Quantity");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Unit Price");
		$sheet->setCellValue("H1", "Expiry Date");
		$sheet->setCellValue("I1", "Batch NO.");
		$sheet->setCellValue("J1", "Shelf NO.");

		$sn = 1;
		$total_cost = 0;
		$total_value = 0;
		$total_selling_price = 0;

		foreach ($report_data as $key => $row) {
			//fetch the details items
			$sn++;
			$total_cost = ($row['quantity'] *$row['cost_price']);
			$total_value += ($row['quantity'] * $row['unit_price']);
			$total_selling_price += ($row['quantity'] * $row['unit_price']);
			$sheet->setCellValue("A" . $sn, $row['id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['category']);
			$sheet->setCellValue("D" . $sn, $row['item_number']);
			$sheet->setCellValue("E" . $sn,  round($row['quantity']));
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['unit_price']);
			$sheet->setCellValue("H" . $sn, date("Y-m-d h:i A", strtotime($row['expiry'])));
			$sheet->setCellValue("I" . $sn, $row['batch_no']);
			$sheet->setCellValue("I" . $sn, $row['shelf']);
		}
		$n = $sn + 2;
		$sheet->setCellValue("C" . $n, "Total Cost");
		$sheet->setCellValue("F" . $n, $total_cost);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total value");
		$sheet->setCellValue("F" . $n, $total_value);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total Selling Price");
		$sheet->setCellValue("F" . $n, $total_selling_price);


		$writer = new Xlsx($spreadsheet);

		$filename =  "Detailed_Expiry_Items_Report_for_" . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
	}

	public function print_filtered_expired_report_items_export($dept = "all", $category = "all", $location_id)
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		// $inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;
		//date('Y-m-d')

		$report_data = $model->getExpired($inputs);
		// return print_r(count($report_data));

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Category");
		$sheet->setCellValue("D1", "Item Number");
		$sheet->setCellValue("E1", "Quantity");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Unit Price");
		$sheet->setCellValue("H1", "Expiry Date");
		$sheet->setCellValue("I1", "Batch NO.");
		$sheet->setCellValue("J1", "Shelf NO.");

		$sn = 1;
		$total_value = 0;
		$total_cost = 0;

		foreach ($report_data as $key => $row) {
			//fetch the details items
			$sn++;
			$total_value += ($row['quantity'] * $row['unit_price']);
			$total_cost += ($row['quantity']* $row['cost_price']);
			$sheet->setCellValue("A" . $sn, $row['id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['category']);
			$sheet->setCellValue("D" . $sn, $row['item_number']);
			$sheet->setCellValue("E" . $sn,  round($row['quantity']));
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['unit_price']);
			$sheet->setCellValue("H" . $sn, date("Y-m-d h:i A", strtotime($row['expiry'])));
			$sheet->setCellValue("I" . $sn, $row['batch_no']);
			$sheet->setCellValue("I" . $sn, $row['shelf']);
		}

		$n = $sn + 2;
		$sheet->setCellValue("C" . $n, "Total Cost");
		$sheet->setCellValue("F" . $n, $total_cost);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total value");
		$sheet->setCellValue("F" . $n, $total_value);

		$writer = new Xlsx($spreadsheet);

		$filename =  "Detailed_Expired_Items_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}


	public function expiry_report($days = 90, $dept = "all", $category = "all", $location_id = 2){
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";

		$start_date = date("Y-m-d", time() + 86400 * 0);
		$end_date = date("Y-m-d", time() + 86400 * $days);
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'dept' => $dept, 'category' => $cat_name, 'location_id' => $location_id);

		

		// return print_r($end_date);

		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getExpiry($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0
		);

		foreach ($report_data as $item) {

			//update totals
			$totals['total'] = $totals['total'] + $item['unit_price'];
			$totals['cost'] = $totals['cost'] +  $item['cost_price'];

			$details_data[$item['id']][] = array(
				'id' => $item['id'],
				'name' => $item['name'],
				'category' => $item['category'],
				'item_number' => $item['item_number'],
				'quantity' => $item['quantity'],
				'cost_price' => to_currency($item['cost_price']),
				'unit_price' => to_currency($item['unit_price']),
				'expiry_date' => $item['expiry'],
				'batch_no' => $item['batch_no'],
				'shelf' => $item['shelf'],
			);
		}

		$data = array(
			'title'					=> "Items Expiring in the Next 90 Days",
			'report_title_data'		=> $this->get_expiry_report_data($inputs),
			// 'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'category'					=> $category,
			'dept'					=> $dept,
			'location_id'			=> $location_id,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_details_print_expiry', $data);
		
		ini_set('memory_limit',$old_limit);
	}



	public function date_input_recv()
	{
		$data = array();
		$stock_locations = $this->xss_clean($this->Stock_location->get_all_form());

		$data['stock_locations'] = array_merge(array('all' => 'All'), $stock_locations);
        $data['cur_loc'] = 'all';
		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if ($employee->role != 4) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}
		$data['employee'] = $employees;
		$data['logged_in_role'] = $this->Employee->get_logged_in_employee_info()->role;
		$suppliers = array('all' => 'All');
		foreach ($this->Supplier->get_all()->result() as $supplier) {

			$suppliers[$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
		}
		$data['suppliers'] = $suppliers;

		$data['mode'] = 'receiving';

		$this->load->view('reports/date_input', $data);
	}




	public function date_product_input_recv()
	{
		$data = array();
		$stock_locations = $this->xss_clean($this->Stock_location->get_all_form());

		$data['stock_locations'] = array_merge(array('all' => 'All'), $stock_locations);
		$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			//just show all employees
			// if ($employee->role != 4) {
			// 	//collection officers
			// 	continue;
			// }
			$employees[$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}
		$data['employee'] = $employees;
		$suppliers = array('all' => 'All');
		foreach ($this->Supplier->get_all()->result() as $supplier) {

			$suppliers[$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
		}
		$data['suppliers'] = $suppliers;

		$data['mode'] = 'receiving';

		$this->load->view('reports/date_product_input', $data);
	}

		public function date_input_price_list()
		{

			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			// $cate_list = $this->Item->categories_list();
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
				// $categories[$value['name']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;
	
			$this->load->view('reports/date_input_price_list', $data);
		}

		public function date_input_out_of_stock()
		{
			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;

			$data['type'] = array(
				'out' => 'Out of Stock',
				'minimum' => 'Minimum Stock Level'
			);
	
			$this->load->view('reports/date_input_out_of_stock', $data);
		}

		public function date_input_stock_value()
		{

			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;

			$suppliers = array('all' => 'All');
			foreach ($this->Supplier->get_all()->result() as $supplier) {

				$suppliers[$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
			}
			$data['suppliers'] = $suppliers;
	
			$this->load->view('reports/date_input_stock_value', $data);
		}

		public function date_input_all_items()
		{

			//clear dept
			$this->report_lib->clear_dept();

			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['prescription'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;

			$suppliers = array('all' => 'All');
			foreach ($this->Supplier->get_all()->result() as $supplier) {

				$suppliers[$supplier->person_id] = $this->xss_clean($supplier->company_name.' '.$supplier->first_name . ' ' . $supplier->last_name);
			}
			$data['suppliers'] = $suppliers;
	
			$this->load->view('reports/date_input_all_items', $data);
		}

		public function date_input_vat_tax()
		{

			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['prescription'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;

			$suppliers = array('all' => 'All');
			foreach ($this->Supplier->get_all()->result() as $supplier) {

				$suppliers[$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
			}
			$data['suppliers'] = $suppliers;
	
			$this->load->view('reports/date_input_vat_tax', $data);
		}

		public function date_input_markup_report()
		{

			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
	
			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	
			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;

			$suppliers = array('all' => 'All');
			foreach ($this->Supplier->get_all()->result() as $supplier) {

				$suppliers[$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
			}
			$data['suppliers'] = $suppliers;
	
			$this->load->view('reports/date_input_markup_report', $data);
		}

		//jude
		public function display_data(){
			$array = array();

			return print_r($array);
		}

		public function date_input_sales_markup_report()
		{

			//clear dept
			$this->report_lib->clear_dept();
			$dept = !empty($this->input->post('dept')) ? $this->input->post('dept') : 'all';
			$this->report_lib->set_dept($dept);
	
			$data = array();
			$loc = $this->Stock_location->get_all()->result();
			$stock_locations = array();
			foreach ($loc as $l) {
				$stock_locations[$l->location_id] = $l->location_name;
			}
			$stock_locations['all'] =  $this->lang->line('reports_all');
			$data['stock_locations'] = array_reverse($stock_locations, TRUE);
			// $data['mode'] = 'sale';

			$data['current_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;

			$cate_list = $this->Item->categories_list_by_dept($dept);
			$categories = array('all' => 'All');
			foreach ($cate_list as $row => $value) {
				$categories[$value['id']] = $value['name'];
			}
			$data['categories'] = $categories;

			$employees = array('all' => 'All');
			foreach ($this->Employee->get_all()->result() as $employee) {
				//just show all employees
				// if (!($employee->role == 5 || $employee->role == 14)) {
				// 	//collection officers
				// 	continue;
				// }
				$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
			}
			//get customers 
			$customers = array('all' => 'All');
			foreach ($this->Customer->search('')->result() as $customer) {
				$customers[$customer->person_id] = $this->xss_clean($customer->last_name . ' ' . $customer->first_name);
			}
			$data['employee'] = $employees;
			$data['customer'] = $customers;
			$data['vatable'] = array(
				'all' => 'Both',
				'NO' => 'NO',
				'YES' => 'YES'
			);

			$data['depts'] = array(
				'all' => 'Both',
				'Pharmacy' => 'Pharmacy',
				'Superstore' => 'Superstore'
			);

			$data['selected_dept'] = $dept;
			
			$payment_types = array('all' => 'All');
			foreach ($this->Sale->get_payment_options() as $key => $value) {
				$payment_types[$value] = $value;
			}
			$data['payment_types'] = $payment_types;

	
			$this->load->view('reports/date_input_sales_markup_report', $data);
		}

		//NEW REPORT FEATURES
		public function display_price_list($dept = "all", $category = "all", $vated = "all", $location_id = "all"){
			$old_limit = ini_get('memory_limit');
			ini_set('memory_limit','-1');
			$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
			$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'location_id' => $location_id);
			$this->load->model('reports/Detailed_receivings');
			$model = $this->Detailed_receivings;

			$report_data = $model->getProductPriceList($inputs);

			$summary_data = array();
			$details_data = array();

			$total_price = 0;
			foreach ($report_data as $key => $row) {
					$total_price += $row['unit_price'];
					$summary_data[] = $this->xss_clean(array(
						'id' => $row['item_id'],
						'name' => $row['name'],
						'item_number' => $row['item_number'],
						'category' => $row['category'],
						'unit_price' => $row['unit_price'],
					));
			}
			$data = array(
				'title'					=> 'Product Price List',
				'report_title_data' 	=> $this->get_price_list_report_data($inputs),
				// 'headers' 				=> $headers,
				'summary_data' 			=> $summary_data,
				'overall_summary_data' 	=> array(
					'Total Price' => $total_price
				),
				'location_id' => $location_id,
				'category' => $category,
				'dept' => $dept,
				'vated' => $vated,
			);

			//logged in employee and branch info 
			$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
			$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
			$data['branch_address'] = $branch_info->location_address;
			$data['branch_number'] = $branch_info->location_number;


			$this->load->view('reports/print_items_price_list', $data);
			
			ini_set('memory_limit',$old_limit);
		}

		public function display_out_of_stock($dept = "all", $category = "all", $type = "all", $vated = "all", $location_id = "all"){
			$old_limit = ini_get('memory_limit');
			ini_set('memory_limit','-1');
			$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
			$inputs = array('dept' => $dept, 'category' => $cat_name, 'type' => $type, 'vated' => $vated, 'location_id' => $location_id);
			$this->load->model('reports/Detailed_receivings');
			$model = $this->Detailed_receivings;

			$report_data = $model->getOutOfStock($inputs);

			$summary_data = array();
			$details_data = array();

			$total_price = 0;
			$total_cost = 0;
			foreach ($report_data as $key => $row) {
					$total_price += $row['unit_price'];
					$total_cost += $row['cost_price'];
					$summary_data[] = $this->xss_clean(array(
						'id' => $row['item_id'],
						'name' => $row['name'],
						'item_number' => $row['item_number'],
						'category' => $row['category'],
						'unit_price' => $row['unit_price'],
						'cost_price' => $row['cost_price'],
						'quantity' => $row['quantity'],
						'reorder_level' => $row['reorder_level'],
					));
			}
			$data = array(
				'title'					=> $inputs['type'] == 'out' ? 'Out of Stock Report' : 'Minimum Stock Level Report',
				'report_title_data' 	=> $this->get_out_of_stock_report_data($inputs),
				// 'headers' 				=> $headers,
				'summary_data' 			=> $summary_data,
				'overall_summary_data' 	=> array(
					'Total Price' => $total_price,
					'Total Cost' => $total_cost
				),
				'location_id' => $location_id,
				'dept' => $dept,
				'category' => $category,
				'type' => $type,
				'vated' => $vated
			);

			//logged in employee and branch info 
			$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
			$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
			$data['branch_address'] = $branch_info->location_address;
			$data['branch_number'] = $branch_info->location_number;


			$this->load->view('reports/print_out_of_stock_items', $data);
			
			ini_set('memory_limit',$old_limit);
		}

		public function out_of_stock_export($dept = "all", $category = "all", $type = "all",  $vated = "all", $location_id = "all")
		{
			$old_limit = ini_get('memory_limit');
			ini_set('memory_limit','-1');
			$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
			$inputs = array('dept' => $dept, 'category' => $cat_name, 'type' => $type, 'vated' => $vated, 'location_id' => $location_id);
			$this->load->model('reports/Detailed_receivings');
			$model = $this->Detailed_receivings;
	
			$report_data = $model->getOutOfStock($inputs);
	
			$spreadsheet = new SpreadSheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue("A1", "Item ID");
			$sheet->setCellValue("B1", "Item Name");
			$sheet->setCellValue("C1", "Item Number");
			$sheet->setCellValue("D1", "Item Category");
			$sheet->setCellValue("E1", "Selling Price");
			$sheet->setCellValue("F1", "Cost Price");
			$sheet->setCellValue("G1", "Quantity");
			$sheet->setCellValue("H1", "Reorder level");
			$sn = 1;
	
			foreach ($report_data as $key => $row) {
	
				$sn++;
					$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
					$sheet->setCellValue("B" . $sn, $row['name']);
					$sheet->setCellValue("C" . $sn, $row['item_number']);
					$sheet->setCellValue("D" . $sn, $row['category']);
					$sheet->setCellValue("E" . $sn, $row['unit_price']);
					$sheet->setCellValue("F" . $sn, $row['cost_price']);
					$sheet->setCellValue("G" . $sn, $row['quantity']);
					$sheet->setCellValue("H" . $sn, $row['reorder_level']);
			}
	
			$writer = new Xlsx($spreadsheet);
	
			$filename =  "Out_of_Stock_Report";
	
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
			header('Cache-Control: max-age=0');
	
			$writer->save('php://output');
			ini_set('memory_limit',$old_limit);
		}


		public function below_reorder_level($dept = "all", $category = "all", $type = "minimum", $vated = "all", $location_id = 2){
			$old_limit = ini_get('memory_limit');
			ini_set('memory_limit','-1');
			
			$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
			$inputs = array('dept' => $dept, 'category' => $cat_name, 'type' => $type, 'vated' => $vated, 'location_id' => $location_id);
			$this->load->model('reports/Detailed_receivings');
			$model = $this->Detailed_receivings;

			$report_data = $model->getOutOfStock($inputs);

			$summary_data = array();
			$details_data = array();

			$total_price = 0;
			$total_cost = 0;
			foreach ($report_data as $key => $row) {
					$total_price += $row['unit_price'];
					$total_cost += $row['cost_price'];
					$summary_data[] = $this->xss_clean(array(
						'id' => $row['item_id'],
						'name' => $row['name'],
						'item_number' => $row['item_number'],
						'category' => $row['category'],
						'unit_price' => $row['unit_price'],
						'cost_price' => $row['cost_price'],
						'quantity' => $row['quantity'],
						'reorder_level' => $row['reorder_level'],
					));
			}
			$data = array(
				'title'					=> 'Items Below Stock Level Report',
				'report_title_data' 	=> $this->get_out_of_stock_report_data($inputs , false),
				// 'headers' 				=> $headers,
				'summary_data' 			=> $summary_data,
				'overall_summary_data' 	=> array(
					'Total Price' => $total_price,
					'Total Cost' => $total_cost
				),
				'location_id' => $location_id,
				'dept' => $dept,
				'category' => $category,
				'type' => $type,
				'vated' => $vated
			);

			//logged in employee and branch info 
			$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
			$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
			$data['branch_address'] = $branch_info->location_address;
			$data['branch_number'] = $branch_info->location_number;


			$this->load->view('reports/print_out_of_stock_items', $data);
			
			ini_set('memory_limit',$old_limit);
		}

		public function price_list_export($dept = "all", $category = "all", $vated = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getProductPriceList($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Number");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Price");
		$sn = 1;

		foreach ($report_data as $key => $row) {

			$sn++;
				$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
				$sheet->setCellValue("B" . $sn, $row['name']);
				$sheet->setCellValue("C" . $sn, $row['item_number']);
				$sheet->setCellValue("D" . $sn, $row['category']);
				$sheet->setCellValue("E" . $sn, $row['unit_price']);
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Product_Price_List_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	private function get_stock_value($inputs,$offset = 0){
		
		return $this->Detailed_receivings->getStockValue($inputs);
	}
	public function display_stock_value($dept = "all", $category = "all", $vated = "all", $supplier = "all", $location_id = "all"){
		
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;
		$c_off = 0;
		$totat_count = $model->getStockCount($inputs);
        $report_data = $model->getStockValue($inputs);
//		if($totat_count > 1000){
//			$report_data = $model->getStockValue($inputs,1000);
//		}else{
//			$report_data = $model->getStockValue($inputs);
//		}
		$summary_data = array();
		$total_price = 0;
		$total_cost = 0;
		$total_value = 0;



		// for($c_off=0;$c_off < $totat_count;$c_off += 1000){
		// 	$report_data = $model->getStockValue($inputs);
		// }

		
		// $report_data = [];

	
		foreach ($report_data as $key => $row) {
				$total_price += $row['unit_price'] * $row['quantity'];
				$total_cost += $row['cost_price'] * $row['quantity'];
				$value = $row['unit_price'] * $row['quantity'];
				$total_value += $value;
				$summary_data[] = $this->xss_clean(array(
					'id' => $row['item_id'],
					'name' => $row['name'],
					'item_number' => $row['item_number'],
					'category' => $row['category'],
					'unit_price' => $row['unit_price'],
					'cost_price' => $row['cost_price'],
					'value' => $value,
					'quantity' => round($row['quantity'])
				));
		}
		$data = array(
			'title'					=> 'Stock Value Report',
			'report_title_data' 	=> $this->get_stock_value_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
//				'Total Selling Price' => $total_price,
				 'Total Cost of Items' => $total_cost,
				'Total Value' => $total_price
			),
			'location_id' => $location_id,
			'dept' => $dept,
			'category' => $category,
			'supplier' => $supplier,
			'vated' => $vated
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;


		$this->load->view('reports/print_stock_value', $data);
		ini_set('memory_limit',$old_limit);

		// return print_r($report_data);
	}

	public function stock_value_export($dept = "all", $category = "all", $vated = "all", $supplier = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$total_value = 0;
		$total_cost = 0;
		$total_selling_price = 0;
		$report_data = $model->getStockValue($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Number");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Selling Price");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Quantity");
		$sheet->setCellValue("H1", "Value");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$value = $row['cost_price'] * $row['quantity'];
			$total_value += $value;
			$total_cost +=($row['cost_price'] * $row['quantity']);
			$total_selling_price +=($row['unit_price'] * $row['quantity']);
			$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['item_number']);
			$sheet->setCellValue("D" . $sn, $row['category']);
			$sheet->setCellValue("E" . $sn, $row['unit_price']);
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['quantity']);
			$sheet->setCellValue("H" . $sn, $value);
		}
		$n = $sn + 2;
		$sheet->setCellValue("C" . $n, "Total value");
		$sheet->setCellValue("H" . $n, $total_value);

		$n += 2;
		$sheet->setCellValue("C" . $n, "Total cost");
		$sheet->setCellValue("H" . $n, $total_cost);

		$n += 2;
		$sheet->setCellValue("C" . $n, "Total Selling Price");
		$sheet->setCellValue("H" . $n, $total_selling_price);

		$writer = new Xlsx($spreadsheet);

		$filename =  "Stock_Value_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function display_all_items($dept = "all", $category = "all", $vated = "all", $prescription = "all", $supplier = "all", $location_id = "all"){
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'prescription' => $prescription, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getAllItems($inputs);

		$summary_data = array();

		$total_price = 0;
		$total_cost = 0;
		foreach ($report_data as $key => $row) {
				$total_price += $row['unit_price'];
				$total_cost += $row['cost_price'];

				$vat_excl = $row['unit_price'] / 1.075;
				$vat_incl = 1.075 * $vat_excl;
				$summary_data[] = $this->xss_clean(array(
					'id' => $row['item_id'],
					'name' => $row['name'],
					'item_number' => $row['item_number'],
					'category' => $row['category'],
					'unit_price' => $row['unit_price'],
					'cost_price' => $row['cost_price'],
					'pack' => $row['pack'],
					'vat_excl' => $vat_excl,
					'vat_incl' => $vat_incl,
					'quantity' => round($row['quantity']),

				));
		}
		$data = array(
			'title'					=> 'Items Report',
			'report_title_data' 	=> $this->get_all_items_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Price' => $total_price,
				'Total Cost' => $total_cost
			),
			'location_id' => $location_id,
			'dept' => $dept,
			'category' => $category,
			'vated' => $vated,
			'prescription' => $prescription,
			'supplier' => $supplier
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/print_all_items', $data);
		ini_set('memory_limit',$old_limit);

		// return print_r($report_data);
	}

	public function all_items_export($dept = "all", $category = "all", $vated = "all", $prescription = "all", $supplier = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'prescription' => $prescription, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getAllItems($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Number");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Selling Price");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Quantity");
		$sheet->setCellValue("H1", "Pack");
		$sheet->setCellValue("I1", "VAT Excl. Price");
		$sheet->setCellValue("J1", "VAT Incl. Price");
		$sn = 1;
		$total_value_vat_incl = 0;
		$total_value = 0;
		$total_cost = 0;
		$total_value_vat_excl = 0;

		foreach ($report_data as $key => $row) {
			$sn++;
			$total_cost += ($row['quantity']*$row['cost_price']);
			$vat_excl = $row['unit_price'] / 1.075;
			$vat_incl = 1.075 * $vat_excl;
			$total_value_vat_incl += ($row['quantity'] * $vat_incl);
			$total_value_vat_excl += ($row['quantity'] * $vat_excl);
			$value = $row['cost_price'] * $row['quantity'];
			$total_value += $value;
			$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['item_number']);
			$sheet->setCellValue("D" . $sn, $row['category']);
			$sheet->setCellValue("E" . $sn, $row['unit_price']);
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['quantity']);
			$sheet->setCellValue("H" . $sn, $row['pack']);
			$sheet->setCellValue("I" . $sn, round($vat_excl, 2));
			$sheet->setCellValue("J" . $sn, round($vat_incl, 2));
		}

		$n = $sn + 2;
		$sheet->setCellValue("C" . $n, "Total Cost");
		$sheet->setCellValue("F" . $n, $total_cost);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total value");
		$sheet->setCellValue("F" . $n, $total_value);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total value VAT EXCL");
		$sheet->setCellValue("F" . $n, $total_value_vat_excl);

		$n = $n + 2;
		$sheet->setCellValue("C" . $n, "Total value VAT INCL");
		$sheet->setCellValue("F" . $n, $total_value_vat_incl);

		$writer = new Xlsx($spreadsheet);

		$filename =  "Items_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function display_expense($start_date, $end_date, $category = "all", $employee = "all", $type = "all", $exp_type = "all", $location_id = "all"){
		// $cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'category' => $category, 'employee' => $employee, 'type' => $type, 'expense_category_type' => $exp_type, 'location_id' => $location_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;
		$report_data = $model->getExpenses($inputs);

		$summary_data = array();

		$total_amount = 0;
		$total_inflow_amount = 0;
		$total_outflow_amount = 0;
		foreach ($report_data as $key => $row) {
			$total_amount += $row['amount'];

			if($row['type'] == 'INFLOW'){
				$total_inflow_amount += $row['amount'];
			}

			if($row['type'] == 'OUTFLOW'){
				$total_outflow_amount += $row['amount'];
			}

			$summary_data[] = $this->xss_clean(array(
				'id' => $row['id'],
				'date' => $row['created_at'],
				'category' => $row['category_name'],
				'details' => $row['details'],
				'amount' => $row['amount'],
				'receipt_no' => $row['receipt_no'],
				'employee' => $row['employee_name'],
				'type' => $row['type'],
				'expense_category_type' => $row['expense_category_type'],
				'balance' => $row['balance'],
			));
		}
		$data = array(
			'title'					=> 'Expense Account Report',
			'report_title_data' 	=> $this->get_expenses_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Inflow' => $total_inflow_amount,
				'Total Outflow' => $total_outflow_amount,
				'Balance' => $total_inflow_amount - $total_outflow_amount
			),
			'location_id' => $location_id,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'employee' => $employee,
			'type' => $type,
			'category' => $category,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		// die();

		$this->load->view('reports/print_expense', $data);
		ini_set('memory_limit',$old_limit);

		// return print_r($report_data);
	}

	public function display_audit($start_date, $end_date, $action = "all", $employee = "all", $location_id = "all"){
		// $cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'action_type' => $action, 'employee' => $employee, 'location_id' => $location_id);
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$report_data = $model->getAudit($inputs);

		$summary_data = array();

		foreach ($report_data as $key => $row) {

			$summary_data[] = $this->xss_clean(array(
				'id' => $row['audit_id'],
				'date' => $row['audit_time'],
				'action' => $row['action_type'],
				'description' => $row['description'],
				'employee' => $row['employee_name'],
			));
		}
		$data = array(
			'title'					=> 'Audit Trail Report',
			'report_title_data' 	=> $this->get_audit_report_data($inputs),
			// 'headers' 				=> $headers,s
			'summary_data' 			=> $summary_data,
			// 'overall_summary_data' 	=> array(
			// 	'Total Inflow' => $total_inflow_amount,
			// 	'Total Outflow' => $total_outflow_amount,
			// 	'Balance' => $total_inflow_amount - $total_outflow_amount
			// ),
			'location_id' => $location_id,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'employee' => $employee,
			'action' => $action,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		// die();

		$this->load->view('reports/print_audit', $data);
		
		ini_set('memory_limit',$old_limit);
	}

	public function display_credit($customer = "all", $location_id = "all"){
		// $inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'customer' => $customer, 'location_id' => $location_id);
        $old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$params = $this->input->post();
        $date = explode('-',$params['daterangepicker']);
		// $start_date = $this->input->post()
        if($params['customer'] == 'all'){
            $this->load->model('reports/Specific_customer');
            $model = $this->Specific_customer;
            $report = $model->getSpecializedCreditReport($params);
            $data = array(
                'title'					=> 'Credit Customers Report',
                'report_title_data' 	=> $this->get_credit_report_data($inputs),
//                'headers' 				=> $headers,
                'summary_data' 			=> $report,
                'start_date'=>date('Y-m-d',strtotime(str_replace(' ','',$params['start_date']))),//date('Y-m-d',$date[0]),
                'end_date'=>date('Y-m-d',strtotime(str_replace(' ','',$params['end_date']))),//,
//                'overall_summary_data' 	=> array(
//                    'Total Credit Amount' => $total_credit_amount,
//                ),
                'location_id' => $location_id,
                'customer' => $customer,
            );

            //logged in employee and branch info
            $employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
            $branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
            $data['branch_address'] = $branch_info->location_address;
            $data['branch_number'] = $branch_info->location_number;

            $this->load->view('reports/print_credit', $data);
        }else{
            redirect(base_url('customers/ledger/'.$params['customer'].'/'.date('Y-m-d',strtotime(str_replace(' ','',$date[0]))).'/'.date('Y-m-d',strtotime(str_replace(' ','',$date[1])))));
        }
		$inputs = array('customer' => $customer, 'location_id' => $location_id);
		$report_data = $model->getCreditReport($inputs);


		$summary_data = array();

//		foreach ($report_data as $key => $row) {
//			$summary_data[] = $this->xss_clean(array(
//				'id' => $row['customer_id'],
//				'customer' => $row['customer_name'],
//				'phone' => $row['phone_number'],
//				'email' => $row['email'],
//				'credit_limit' => $row['credit_limit'],
//				'credit_amount' => $row['wallet'],
//			));
//		}
//		$data = array(
//			'title'					=> 'Credit Customers Report',
//			'report_title_data' 	=> $this->get_credit_report_data($inputs),
//			 'headers' 				=> $headers,
//			'summary_data' 			=> $summary_data,
//			 'overall_summary_data' 	=> array(
//			 	'Total Credit Amount' => $total_credit_amount,
//			 ),
//			'location_id' => $location_id,
//			'customer' => $customer,
//		);

		//logged in employee and branch info 
//		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
//		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
//		$data['branch_address'] = $branch_info->location_address;
//		$data['branch_number'] = $branch_info->location_number;
//
//		$this->load->view('reports/print_credit', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function expenses_export($start_date, $end_date, $category = "all", $employee = "all", $type = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'category' => $category, 'employee' => $employee, 'type' => $type, 'location_id' => $location_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getExpenses($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Expense ID");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Expense Category");
		$sheet->setCellValue("D1", "Expense Description");
		$sheet->setCellValue("E1", "Employee");
		$sheet->setCellValue("F1", "Type");
		$sheet->setCellValue("G1", "Amount");
		$sheet->setCellValue("H1", "Invoice Number");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$sheet->setCellValue("A" . $sn, 'EXP ' . $row['id']);
			$sheet->setCellValue("B" . $sn, $row['created_at']);
			$sheet->setCellValue("C" . $sn, $row['category']);
			$sheet->setCellValue("D" . $sn, $row['details']);
			$sheet->setCellValue("E" . $sn, $row['employee_name']);
			$sheet->setCellValue("F" . $sn, $row['type']);
			$sheet->setCellValue("G" . $sn, $row['amount']);
			$sheet->setCellValue("G" . $sn, $row['receipt_no']);
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Expense_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function audit_export($start_date, $end_date, $action = "all", $employee = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'action_type' => $action, 'employee' => $employee, 'location_id' => $location_id);
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$report_data = $model->getAudit($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Audit ID");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Action");
		$sheet->setCellValue("D1", "Description");
		$sheet->setCellValue("E1", "Employee");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$sheet->setCellValue("A" . $sn, 'AUD ' . $row['audit_id']);
			$sheet->setCellValue("B" . $sn, $row['audit_time']);
			$sheet->setCellValue("C" . $sn, $row['action_type']);
			$sheet->setCellValue("D" . $sn, $row['description']);
			$sheet->setCellValue("E" . $sn, $row['employee_name']);
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Audit_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function credit_export($customer = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('customer' => $customer, 'location_id' => $location_id);
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$report_data = $model->getCreditReport($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "ID");
		$sheet->setCellValue("B1", "Customer");
		$sheet->setCellValue("C1", "Phone");
		$sheet->setCellValue("D1", "Email");
		$sheet->setCellValue("E1", "Credit Limit");
		$sheet->setCellValue("F1", "Credit Amount");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$sheet->setCellValue("A" . $sn, $row['customer_id']);
			$sheet->setCellValue("B" . $sn, $row['customer_name']);
			$sheet->setCellValue("C" . $sn, $row['phone_number']);
			$sheet->setCellValue("D" . $sn, $row['email']);
			$sheet->setCellValue("E" . $sn, $row['credit_limit']);
			$sheet->setCellValue("F" . $sn, $row['wallet']);
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Credit_Customer_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function display_vat_tax($dept = "all", $category = "all", $prescription = "all", $supplier = "all", $location_id = "all"){
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'prescription' => $prescription, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getVatItems($inputs);

		$summary_data = array();

		$total_price = 0;
		$total_cost = 0;
		$overall_total_vat = 0;
		foreach ($report_data as $key => $row) {
				$total_price += $row['unit_price'];
				$total_cost += $row['cost_price'];

				$vat_excl = $row['unit_price'] / 1.075;
				$vat_incl = 1.075 * $vat_excl;

				$total_vat_amount = 0;
				$total_amount_sold = 0;
				$total_qty_sold = 0;

				//fetch the details sales items
				$items = $model->getSalesItemsForAnItem($row['item_id']);

				foreach ($items as $item) {
					$total_vat_amount += $item['vat'];
					$total_amount_sold += $item['item_unit_price'];
					$total_qty_sold += $item['quantity_purchased'];
				}

				$overall_total_vat += $total_vat_amount;
				$summary_data[] = $this->xss_clean(array(
					'id' => $row['item_id'],
					'name' => $row['name'],
					'item_number' => $row['item_number'],
					'category' => $row['category'],
					'unit_price' => $row['unit_price'],
					'cost_price' => $row['cost_price'],
					'pack' => $row['pack'],
					'vat_excl' => $vat_excl,
					'vat_incl' => $vat_incl,
					'quantity' => round($row['quantity']),
					'quantity_sold' => round($total_qty_sold),
					'total_vat' => round($total_vat_amount),
					'total_amount_sold' => $total_amount_sold
				));

				
		}
		$data = array(
			'title'					=> 'VAT / TAX Report',
			'report_title_data' 	=> $this->get_vat_items_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Price' => $total_price,
				'Total Cost' => $total_cost,
				'Total VAT Amount' => $overall_total_vat
			),
			'location_id' => $location_id,
			'dept' => $dept,
			'category' => $category,
			'prescription' => $prescription,
			'supplier' => $supplier
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/print_vat_tax', $data);
		
		ini_set('memory_limit',$old_limit);

		// return print_r($report_data);
	}

	public function vat_tax_export($dept = "all", $category = "all", $prescription = "all", $supplier = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('dept' => $dept, 'category' => $cat_name, 'prescription' => $prescription, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getVatItems($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Number");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Selling Price");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Quantity");
		$sheet->setCellValue("H1", "VAT Excl. Price");
		$sheet->setCellValue("I1", "VAT Incl. Price");
		$sheet->setCellValue("J1", "Quantity Sold");
		$sheet->setCellValue("K1", "Total VAT");
		$sheet->setCellValue("L1", "Total Amount Sold");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$total_vat_amount = 0;
			$total_amount_sold = 0;
			$total_qty_sold = 0;

			$vat_excl = $row['unit_price'] / 1.075;
			$vat_incl = 1.075 * $vat_excl;

			$items = $model->getSalesItemsForAnItem($row['item_id']);
			foreach ($items as $item) {
				$total_vat_amount += $item['vat'];
				$total_amount_sold += $item['item_unit_price'];
				$total_qty_sold += $item['quantity_purchased'];
			}

			$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['item_number']);
			$sheet->setCellValue("D" . $sn, $row['category']);
			$sheet->setCellValue("E" . $sn, $row['unit_price']);
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['quantity']);
			$sheet->setCellValue("H" . $sn, $vat_excl);
			$sheet->setCellValue("I" . $sn, $vat_incl);
			$sheet->setCellValue("J" . $sn, $total_qty_sold);
			$sheet->setCellValue("K" . $sn, $total_vat_amount);
			$sheet->setCellValue("L" . $sn, $total_amount_sold);
		}

		$writer = new Xlsx($spreadsheet);
		$filename =  "Items_VAT_TAX_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}


	public function display_markup_report($start_markup, $end_markup, $dept = "all", $category = "all", $vated = "all", $supplier = "all", $location_id = "all"){
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('start_markup' => $start_markup, 'end_markup' => $end_markup, 'dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getMarkupItems($inputs);

		$summary_data = array();

		$total_price = 0;
		$total_cost = 0;
		foreach ($report_data as $key => $row) {
				$total_price += $row['unit_price'];
				$total_cost += $row['cost_price'];

				$markup_percent = (($row['unit_price_markup'] - 1) * 100);
				$markup_value = ($row['unit_price'] - $row['cost_price']);
				$summary_data[] = $this->xss_clean(array(
					'id' => $row['item_id'],
					'name' => $row['name'],
					'item_number' => $row['item_number'],
					'category' => $row['category'],
					'unit_price' => $row['unit_price'],
					'cost_price' => $row['cost_price'],
					'markup_percent' => $markup_percent,
					'markup' => $row['unit_price_markup'],
					'markup_value' => $markup_value,
					'quantity' => round($row['quantity']),
				));
		}
		$data = array(
			'title'					=> 'Items Mark UP Report',
			'report_title_data' 	=> $this->get_markup_items_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Price' => $total_price,
				'Total Cost' => $total_cost
			),
			'location_id' => $location_id,
			'start_markup' => $start_markup,
			'end_markup' => $end_markup,
			'dept' => $dept,
			'category' => $category,
			'vated' => $vated,
			'supplier' => $supplier
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/print_markup_report', $data);
		
		ini_set('memory_limit',$old_limit);

		// return print_r($report_data);
	}

	public function markup_report_export($start_markup, $end_markup, $dept = "all", $category = "all", $vated = "all", $supplier = "all", $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$cat_name = $category != "all" ? $this->Item->get_item_category_by_id($category) : "all";
		$inputs = array('start_markup' => $start_markup, 'end_markup' => $end_markup, 'dept' => $dept, 'category' => $cat_name, 'vated' => $vated, 'supplier' => $supplier, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getMarkupItems($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Item ID");
		$sheet->setCellValue("B1", "Item Name");
		$sheet->setCellValue("C1", "Item Number");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Selling Price");
		$sheet->setCellValue("F1", "Cost Price");
		$sheet->setCellValue("G1", "Quantity");
		$sheet->setCellValue("H1", "Markup Percentage");
		$sn = 1;

		foreach ($report_data as $key => $row) {
			$sn++;
			$markup_percent = (($row['unit_price_markup'] - 1) * 100);
			$sheet->setCellValue("A" . $sn, 'ITEM ' . $row['item_id']);
			$sheet->setCellValue("B" . $sn, $row['name']);
			$sheet->setCellValue("C" . $sn, $row['item_number']);
			$sheet->setCellValue("D" . $sn, $row['category']);
			$sheet->setCellValue("E" . $sn, $row['unit_price']);
			$sheet->setCellValue("F" . $sn, $row['cost_price']);
			$sheet->setCellValue("G" . $sn, $row['quantity']);
			$sheet->setCellValue("H" . $sn, $markup_percent);
		}

		$writer = new Xlsx($spreadsheet);
		$filename =  "Items_Markup_Report";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function display_sales_markup_report($start_date, $end_date, $start_markup, $end_markup, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all", $item_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'start_markup' => $start_markup, 'end_markup' => $end_markup, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type, 'item_id' => $item_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		// $report_data = $model->getSalesDataProductSpecific($inputs);
		$report_data = $model->getSalesMarkupItems($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0
		);

		// print_r($report_data);
		// return;

		foreach ($report_data as $row) {

			$cost = $model->getCost($row['sale_id']);

			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];

			// $markup_percent = (($row['unit_price_markup'] - 1) * 100);

			$total_cost = $row['quantity_purchased'] * $row['item_cost_price'];
				if ($row['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $row['pack'];
				}

				$sale_markup = round($row['item_unit_price'] / $row['item_cost_price'], 2);
				$markup_percent = ($sale_markup - 1) * 100;
				// $markup_percent = ($sale_markup) * 100;
				$details_data[$row['sale_id']][] = array(
					'id' => $row['sale_id'],
					'name' => $row['name'],
					'category' => $row['category'],
					'item_number' => $row['item_number'],
					'sale_time' => $row['sale_time'],
					'employee_name' => $row['employee_name'],
					'customer_name' => $row['customer_name'],
					'quantity' => $row['quantity_purchased'],
					'cost_price' => to_currency($row['item_cost_price']),
					'unit_price' => to_currency($row['item_unit_price']),
					'sales_type' => $row['qty_selected'],
					'cost' => to_currency($total_cost),
					'total' => to_currency(($row['quantity_purchased'] * $row['item_unit_price']) - $row['discount']),
					'discount_percent' => $row['discount_percent'],
					'discount' => to_currency($row['discount']),
					'vat' => to_currency($row['vat']),
					'markup_percent' => $markup_percent,
					'markup' => $sale_markup
				);
		}

		$data = array(
			'title'					=> "Sales Mark Up Report",
			'report_title_data'		=> $this->get_sales_markup_report_data($inputs),
			'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type,
			'item_id'			=> $inputs['item_id']
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		// $this->load->view('reports/tabular_details_print_content', $data);


		$this->load->view('reports/print_sales_markup_report', $data);
		
		ini_set('memory_limit',$old_limit);
	}

	


		public function print_filtered_all_items_export($category, $vated, $location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('category' => $category, 'vated' => $vated, 'location_id' => $location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getProductSpecificReceivings($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Item Number");
		$sheet->setCellValue("F1", "Employee Name");
		$sheet->setCellValue("G1", "Supplier");
		$sheet->setCellValue("H1", "Quantity Ordered");
		$sheet->setCellValue("I1", "Quantity Received");
		$sheet->setCellValue("J1", "Cost");
		$sheet->setCellValue("K1", "Retail Price");

		$sheet->setCellValue("L1", "Total Cost");
		$sheet->setCellValue("M1", "Total Price");
		$sheet->setCellValue("N1", "Reference");
		$sheet->setCellValue("O1", "Comment");

		$sn = 1;

		foreach ($report_data as $key => $row) {

			$sn++;
				$sheet->setCellValue("A" . $sn, 'RECV ' . $row['receiving_id']);
				$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['receiving_time'])));
				$sheet->setCellValue("C" . $sn, $row['name']);
				$sheet->setCellValue("D" . $sn, $row['category']);
				$sheet->setCellValue("E" . $sn, $row['item_number']);
				$sheet->setCellValue("F" . $sn, $row['employee_name']);
				$sheet->setCellValue("G" . $sn, $row['supplier'] ? $row['supplier'] : 'N/A');
				$sheet->setCellValue("H" . $sn,  round($row['quantity_ordered']));
				$sheet->setCellValue("I" . $sn, round($row['quantity_purchased']));
				$sheet->setCellValue("J" . $sn, $row['item_cost_price']);
				$sheet->setCellValue("K" . $sn, $row['item_unit_price']);
				$sheet->setCellValue("L" . $sn, $row['cost']);
				$sheet->setCellValue("M" . $sn, $row['price']);
				$sheet->setCellValue("N" . $sn, $row['reference']);
				$sheet->setCellValue("O" . $sn, $row['comment']);
		}

		$writer = new Xlsx($spreadsheet);


		$filename =  "Product_Specific_Receiving_Report_" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	// public function date_input_trans()
	// {
	// 	$data = array();

	// 	$stock_locations = $this->xss_clean($this->Stock_location->get_all_form());

	// 	$data['stock_locations'] = $stock_locations;
	// 	$data['mode'] = 'transfer';

	// 	$data['default_from_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
	// 	$employees = array('all' => 'All');
	// 	foreach ($this->Employee->get_all()->result() as $employee) {
	// 		if ($employee->role != 4) {
	// 			//inventory officers
	// 			continue;
	// 		}
	// 		$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
	// 	}
	// 	$data['employee'] = $employees;

	// 	$this->load->view('reports/date_input', $data);
	// }


	public function date_input_trans()
	{
		$data = array();

		// $stock_locations = $this->xss_clean($this->Stock_location->get_all_form());
		// $stock_locations = $this->xss_clean($this->Stock_location->get_all_form());
		$stock_locations = array('all' => 'All');

		$s = $this->Stock_location->get_all_form();
		
		foreach ($this->Stock_location->get_all_form() as $i => $stock_location) {
			// return print_r($i);
			// if ($employee->role != 4) {
			// 	//inventory officers
			// 	continue;
			// }
			// $stock_locations[$stock_location->location_id] = $this->xss_clean($stock_location->location_name);
			$stock_locations[$i] = $this->xss_clean($stock_location);
		}
		$data['stock_locations'] = $stock_locations;
		$data['mode'] = 'transfer';

		$data['default_from_location'] = $this->Employee->get_logged_in_employee_info()->branch_id;
		$data['logged_in_role'] = $this->Employee->get_logged_in_employee_info()->role;
		$employees = array('all' => 'All');
		foreach ($this->Employee->get_all()->result() as $employee) {
			if ($employee->role != 4) {
				//inventory officers
				continue;
			}
			$employees[$employee->person_id] = $this->xss_clean($employee->last_name . ' ' . $employee->first_name);
		}
		$data['employee'] = $employees;

		$this->load->view('reports/date_input', $data);
	}

	//Graphical summary sales report
	public function graphical_summary_sales($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$date = date($this->config->item('dateformat'), strtotime($row['sale_date']));
			$labels[] = $date;
			$series[] = array('meta' => $date, 'value' => $row['total']);
		}

		$data = array(
			'title' => $this->lang->line('reports_sales_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/line',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => $this->lang->line('reports_revenue'),
			'xaxis_title' => $this->lang->line('reports_date'),
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary items report
	public function graphical_summary_items($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['name'];
			$series[] = $row['total'];
		}

		$data = array(
			'title' => $this->lang->line('reports_items_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/hbar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => $this->lang->line('reports_items'),
			'xaxis_title' => $this->lang->line('reports_revenue'),
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary customers report
	public function graphical_summary_categories($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['category'];
			$series[] = array('meta' => $row['category'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']);
		}

		$data = array(
			'title' => $this->lang->line('reports_categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
	}

	//Graphical summary suppliers report
	public function graphical_summary_suppliers($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['supplier'];
			$series[] = array('meta' => $row['supplier'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']);
		}

		$data = array(
			'title' => $this->lang->line('reports_suppliers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary employees report
	public function graphical_summary_employees($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['employee'];
			$series[] = array('meta' => $row['employee'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']);
		}

		$data = array(
			'title' => $this->lang->line('reports_employees_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary taxes report
	public function graphical_summary_taxes($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['percent'];
			$series[] = array('meta' => $row['percent'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']);
		}

		$data = array(
			'title' => $this->lang->line('reports_taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary customers report
	public function graphical_summary_customers($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['customer'];
			$series[] = $row['total'];
		}

		$data = array(
			'title' => $this->lang->line('reports_customers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/hbar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => $this->lang->line('reports_customers'),
			'xaxis_title' => $this->lang->line('reports_revenue'),
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary discounts report
	public function graphical_summary_discounts($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['discount_percent'];
			$series[] = $row['count'];
		}

		$data = array(
			'title' => $this->lang->line('reports_discounts_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/bar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => $this->lang->line('reports_count'),
			'xaxis_title' => $this->lang->line('reports_discount_percent'),
			'show_currency' => FALSE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	//Graphical summary payments report
	public function graphical_summary_payments($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));

		$labels = array();
		$series = array();
		foreach ($report_data as $row) {
			$row = $this->xss_clean($row);

			$labels[] = $row['payment_type'];
			$series[] = array('meta' => $row['payment_type'] . ' ' . round($row['payment_amount'] / $summary['total'] * 100, 2) . '%', 'value' => $row['payment_amount']);
		}

		$data = array(
			'title' => $this->lang->line('reports_payments_summary_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => TRUE
		);

		$this->load->view('reports/graphical', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function specific_customer_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_customer');

		$customers = array();
		foreach ($this->Customer->get_all()->result() as $customer) {
			$customers[$customer->person_id] = $this->xss_clean($customer->first_name . ' ' . $customer->last_name);
		}
		$data['specific_input_data'] = $customers;

		$this->load->view('reports/specific_input', $data);
	}
	public function specific_expiry_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_expiry');

		$employees = array();
		foreach ($this->Employee->get_all()->result() as $employee) {
			$employees[$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}
		$data['specific_input_data'] = $employees;

		$this->load->view('reports/specific_expiry_input', $data);
	}

	public function specific_expiry($start_date, $end_date, $receiving_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id);

		$this->load->model('reports/Specific_expiry');
		$model = $this->Specific_expiry;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());
		$report_data = $model->getData($inputs);

		$summary_data = array();
		$details_data = array();

		$show_locations = $this->xss_clean($this->Stock_location->multiple_locations());

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(
				array(
					'id' => $row['item_id'],
					'name' => $row['name'],
					'expiry' => $row['expiry']
				)
			);

			foreach ($report_data['details'][$key] as $drow) {
				$quantity_purchased = $drow['receiving_quantity'] > 1 ? to_quantity_decimals($drow['quantity_purchased']) . ' x ' . to_quantity_decimals($drow['receiving_quantity']) : to_quantity_decimals($drow['quantity_purchased']);
				if ($show_locations) {
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$row['receiving_id']][] = $this->xss_clean(array($drow['item_number'], $drow['pack'], $drow['category'], $quantity_purchased, to_currency($drow['total']), $drow['discount_percent'] . '%'));
			}
		}

		$data = array(
			'title' => $this->lang->line('reports_detailed_expiry_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $headers,
			'editable' => 'receivings',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);

		$this->load->view('reports/tabular_details', $data);
	}

	public function specific_customer($start_date, $end_date, $customer_id, $sale_type)
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'customer_id' => $customer_id, 'sale_type' => $sale_type);

		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());
		$report_data = $model->getData($inputs);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(array(
				'id' => anchor('sales/receipt/' . $row['sale_id'], 'POS ' . $row['sale_id'], array('target' => '_blank')),
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment']
			));

			foreach ($report_data['details'][$key] as $drow) {
				$details_data[$row['sale_id']][] = $this->xss_clean(array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], to_quantity_decimals($drow['quantity_purchased']), to_currency($drow['subtotal']), to_currency($drow['tax']), to_currency($drow['total']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'] . '%'));
			}

			if (isset($report_data['rewards'][$key])) {
				foreach ($report_data['rewards'][$key] as $drow) {
					$details_data_rewards[$row['sale_id']][] = $this->xss_clean(array($drow['used'], $drow['earned']));
				}
			}
		}

		$customer_info = $this->Customer->get_info($customer_id);
		$data = array(
			'title' => $this->xss_clean($customer_info->first_name . ' ' . $customer_info->last_name . ' ' . $this->lang->line('reports_report')),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $headers,
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);

		$this->load->view('reports/tabular_details', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function specific_employee_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_employee');

		$employees = array();
		foreach ($this->Employee->get_all()->result() as $employee) {
			if ($employee->role != 5) {
				//sale officers
				continue;
			}
			$employees[$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}
		$data['specific_input_data'] = $employees;

		$this->load->view('reports/specific_input', $data);
	}
	public function fetch_irecharge_trans(){
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
	    $data = $this->input->post();
        $this->load->model('IRecharge');
        $trans = $this->IRecharge->getReport($$data['start'],$data['end'],$data['type'],$data['staff']);
        $bal = $this->IRecharge->getWalletBalance();
        $this->load->view('reports/irecharge_trans_disp',['trans'=>$trans,'bal'=>$bal]);
		ini_set('memory_limit',$old_limit);

    }

	public function specific_employee($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = "all", $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		//$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());
		//$report_data = $model->getData($inputs);
		$report_data = $model->getSalesData($inputs);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();
		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0,
			'profit' => 0
		);

		foreach ($report_data as $row) {
			$payment = $model->getPayment($row['sale_id']);
			$cost = $model->getCost($row['sale_id']);
			$prefx = ($row['sales_type'] == 0)&&($row['quantity_purchased'] > 0) ? 'POS' :'ROS';
			$summary_data[] = array(
				'id' => anchor('sales/receipt/' . $row['sale_id'], "$prefx " . $row['sale_id'], array('target' => '_blank')),
				'sale_date' => date("Y-m-d h:i A", strtotime($row['sale_time'])),
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'customer_name' => $row['customer_name'],
				'total' => to_currency($row['amount']), //discounted total
				'total_vat' => to_currency($row['total_vat']),
				'cost' => to_currency($cost),
				'credit' => to_currency($row['credit']),
				'discount' => to_currency($row['discount']) . ((strlen($row['discount']) > 0 && $row['auth_code']) ? '(Authorized: ' . $this->Employee->get_info($row['auth_code'])->username . ')' : ""),
				'total_payment' => to_currency($payment->payment_amount),
				'change_due' => to_currency($payment->payment_amount - $row['total_vat'] - ($row['amount'] - $row['discount'])),
				'payment_type' => $payment->payment_type,
				'comment' => $row['comment']
			);
			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];
			
			//fetch the details items
			$items = $model->getSalesItemsData($row['sale_id']);
			foreach ($items as $item) {

				$total_cost = $item['quantity_purchased'] * $item['item_cost_price'];
				if ($item['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $item['pack'];
				}
				$details_data[$row['sale_id']][] = array(
					$item['name'],
					$item['category'],
					$item['item_number'],
					$item['quantity_purchased'],
					to_currency($item['item_cost_price']),
					to_currency($item['item_unit_price']),
					$item['qty_selected'],
					to_currency($total_cost),
					to_currency($item['quantity_purchased'] * $item['item_unit_price']),
					$item['discount_percent'],
					to_currency($item['discount']),
					to_currency($item['vat'])

				);
			}
		}
		$totals['profit'] = $totals['total'] - $totals['cost'] - $totals['discount'] - $totals['vat'];

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

		$this->load->view('reports/tabular_details', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function specific_discount_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_discount');

		$discounts = array();
		for ($i = 0; $i <= 100; $i += 10) {
			$discounts[$i] = $i . '%';
		}
		$data['specific_input_data'] = $discounts;

		$data = $this->xss_clean($data);

		$this->load->view('reports/specific_input', $data);
	}

	public function specific_discount($start_date, $end_date, $discount, $sale_type)
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'discount' => $discount, 'sale_type' => $sale_type);

		$this->load->model('reports/Specific_discount');
		$model = $this->Specific_discount;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());
		$report_data = $model->getData($inputs);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(array(
				'id' => anchor('sales/receipt/' . $row['sale_id'], 'POS ' . $row['sale_id'], array('target' => '_blank')),
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment']
			));

			foreach ($report_data['details'][$key] as $drow) {
				$details_data[$row['sale_id']][] = $this->xss_clean(array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], to_quantity_decimals($drow['quantity_purchased']), to_currency($drow['subtotal']), to_currency($drow['tax']), to_currency($drow['total']), to_currency($drow['profit']), $drow['discount_percent'] . '%'));
			}

			if (isset($report_data['rewards'][$key])) {
				foreach ($report_data['rewards'][$key] as $drow) {
					$details_data_rewards[$row['sale_id']][] = $this->xss_clean(array($drow['used'], $drow['earned']));
				}
			}
		}

		$data = array(
			'title' => $discount . '% ' . $this->lang->line('reports_discount') . ' ' . $this->lang->line('reports_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $headers,
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);

		$this->load->view('reports/tabular_details', $data);
	}

	public function get_detailed_sales_row($sale_id)
	{
		$inputs = array('sale_id' => $sale_id);

		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$model->create($inputs);

		$report_data = $model->getDataBySaleId($sale_id);

		$summary_data = $this->xss_clean(array(
			'sale_id' => $report_data['sale_id'],
			'sale_date' => $report_data['sale_date'],
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'employee_name' => $report_data['employee_name'],
			'customer_name' => $report_data['customer_name'],
			'subtotal' => to_currency($report_data['subtotal']),
			'tax' => to_currency($report_data['tax']),
			'total' => to_currency($report_data['total']),
			'cost' => to_currency($report_data['cost']),
			'profit' => to_currency($report_data['profit']),
			'payment_type' => $report_data['payment_type'],
			'comment' => $report_data['comment'],
			'edit' => anchor(
				'sales/edit/' . $report_data['sale_id'],
				'<span class="glyphicon glyphicon-edit"></span>',
				array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $this->lang->line('common_delete'), 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => $this->lang->line('sales_update'))
			)
		));

		echo json_encode(array($sale_id => $summary_data));
	}

	public function detailed_sales($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		//$location_id = 22;//using location to query sales will be discussed later
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());

		$report_data = $model->getData($inputs);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();

		$show_locations = $this->xss_clean($this->Stock_location->multiple_locations());

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(array(
				'id' => $row['sale_id'],
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/' . $row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $this->lang->line('common_delete'), 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => $this->lang->line('sales_update'))
				)
			));

			foreach ($report_data['details'][$key] as $drow) {
				$quantity_purchased = to_quantity_decimals($drow['quantity_purchased']);
				if ($show_locations) {
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$row['sale_id']][] = $this->xss_clean(array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $quantity_purchased, to_currency($drow['subtotal']), to_currency($drow['tax']), to_currency($drow['total']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'] . '%'));
			}

			if (isset($report_data['rewards'][$key])) {
				foreach ($report_data['rewards'][$key] as $drow) {
					$details_data_rewards[$row['sale_id']][] = $this->xss_clean(array($drow['used'], $drow['earned']));
				}
			}
		}

		$data = array(
			'title' => $this->lang->line('reports_detailed_sales_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);
		//echo json_encode($data);
		$this->load->view('reports/tabular_details', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function detailed_product_sales($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		//$location_id = 22;//using location to query sales will be discussed later
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());

		$report_data = $model->getData($inputs);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();

		$show_locations = $this->xss_clean($this->Stock_location->multiple_locations());

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(array(
				'id' => $row['sale_id'],
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/' . $row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $this->lang->line('common_delete'), 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => $this->lang->line('sales_update'))
				)
			));

			foreach ($report_data['details'][$key] as $drow) {
				$quantity_purchased = to_quantity_decimals($drow['quantity_purchased']);
				if ($show_locations) {
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$row['sale_id']][] = $this->xss_clean(array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $quantity_purchased, to_currency($drow['subtotal']), to_currency($drow['tax']), to_currency($drow['total']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'] . '%'));
			}

			if (isset($report_data['rewards'][$key])) {
				foreach ($report_data['rewards'][$key] as $drow) {
					$details_data_rewards[$row['sale_id']][] = $this->xss_clean(array($drow['used'], $drow['earned']));
				}
			}
		}

		$data = array(
			'title' => $this->lang->line('reports_detailed_sales_report'),
			'subtitle' => $this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);
		//echo json_encode($data);
		$this->load->view('reports/tabular_details', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function get_detailed_receivings_row($receiving_id)
	{
		$inputs = array('receiving_id' => $receiving_id);

		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$model->create($inputs);

		$report_data = $model->getDataByReceivingId($receiving_id);

		$summary_data = $this->xss_clean(array(
			'receiving_id' => $report_data['receiving_id'],
			'receiving_date' => $report_data['receiving_date'],
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'employee_name' => $report_data['employee_name'],
			'supplier_name' => $report_data['supplier_name'],
			'total' => to_currency($report_data['total']),
			'payment_type' => $report_data['payment_type'],
			'reference' => $report_data['reference'],
			'comment' => $report_data['comment'],
			'edit' => anchor(
				'receivings/edit/' . $report_data['receiving_id'],
				'<span class="glyphicon glyphicon-edit"></span>',
				array('class' => 'modal-dlg print_hide', 'data-btn-submit' => $this->lang->line('common_submit'), 'data-btn-delete' => $this->lang->line('common_delete'), 'title' => $this->lang->line('receivings_update'))
			)
		));

		echo json_encode(array($receiving_id => $summary_data));
	}

	public function detailed_receivings($start_date, $end_date, $receiving_type, $location_id = 'all', $employee_id = 'all', $supplier = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'employee_id' => $employee_id, 'supplier' => $supplier);

		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		//$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumns());

		$report_data = $model->getAllReceivings($inputs);


		$summary_data = array();
		$details_data = array();

		$total = 0;
		foreach ($report_data as $key => $row) {
			//add current to the overral total
			$total += $row['cost'];
			//hide the update button if account
			$employee = $this->Employee->get_logged_in_employee_info();
			if ($employee->role == 12) {
				$editing = '';
			} else {
				$editing = anchor(
					'receivings/edit/' . $row['receiving_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					array('class' => 'modal-dlg print_hide ', 'data-btn-delete' => $this->lang->line('common_delete'), 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => $this->lang->line('receivings_update'))
				);
			}

			$summary_data[] = $this->xss_clean(array(
				'id' =>  anchor('receivings/receipt/' . $row['receiving_id'], 'RECV ' . $row['receiving_id'], array('target' => '_blank')),
				'receiving_date' => $row['receiving_time'],
				'quantity' => round($row['quantity_purchased']),
				'employee_name' => $row['employee_name'],
				'supplier_name' => $row['supplier'],
				'total' => to_currency($row['cost']),
				'price' => to_currency($row['price']),
				//'payment_type' => $row['payment_type'],
				'reference' => $row['reference'],
				'comment' => $row['comment'],
				'edit' => $editing
			));
			$details = $model->getReceivingItemsData($row['receiving_id']);
			foreach ($details as $drow) {


				$details_data[$row['receiving_id']][] = $this->xss_clean(array(
					$drow['item_number'],
					$drow['name'],
					$drow['category'],
					round($drow['quantity_purchased']),
					to_currency($drow['item_cost_price']),
					to_currency($drow['item_unit_price']),
					to_currency($drow['item_cost_price'] * $drow['quantity_purchased']),
					to_currency($drow['item_unit_price'] * $drow['quantity_purchased']),
				));
			}
		}

		$data = array(
			'title' => $this->lang->line('reports_detailed_receivings_report'),
			'report_title_data' => $this->get_receivings_report_data($inputs),
			'headers' => $headers,
			'editable' => 'receivings',
			'location_id' => $location_id,
			'receiving_type' => $receiving_type,
			'employee_id' => $employee_id,
			'start_date' => $start_date,
			'supplier' => $supplier,
			'end_date' => $end_date,
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'overall_summary_data' => array('Total Cost' => $total) //$this->xss_clean($model->getSummaryData($inputs))
		);

		$this->load->view('reports/tabular_details_receivings', $data);
		ini_set('memory_limit',$old_limit);
	}

	private function get_price_list_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		// if ($inputs['supplier'] != 'all') {
		// 	$supplier_info = $this->Supplier->get_info($inputs['supplier']);
		// 	$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		// }

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_out_of_stock_report_data($inputs, $showType = true)
	{
		$report_title_data = array();
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		if($showType){
			$report_title_data['Type'] = $inputs['type'] == 'out' ? 'Out of Stock Report' : 'Minimum Stock Level Report';
		}
		
		// if ($inputs['supplier'] != 'all') {
		// 	$supplier_info = $this->Supplier->get_info($inputs['supplier']);
		// 	$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		// }

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}
		return $report_title_data;
	}

	//

	private function get_stock_value_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['supplier'] != 'all') {
			$supplier_info = $this->Supplier->get_info($inputs['supplier']);
			$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_all_items_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Prescription'] = ucfirst($inputs['prescription']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['supplier'] != 'all') {
			$supplier_info = $this->Supplier->get_info($inputs['supplier']);
			$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_vat_items_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Prescription'] = ucfirst($inputs['prescription']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['supplier'] != 'all') {
			$supplier_info = $this->Supplier->get_info($inputs['supplier']);
			$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_markup_items_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Markup Range'] = $inputs['start_markup'] . ' TO ' . $inputs['end_markup'];
		$report_title_data['Vated'] = ucfirst($inputs['vated']);
		$report_title_data['Prescription'] = ucfirst($inputs['prescription']);
		$report_title_data['Department'] = ucfirst($inputs['dept']);
		$report_title_data['Category'] = ucfirst($inputs['category']);
		
		if ($inputs['supplier'] != 'all') {
			$supplier_info = $this->Supplier->get_info($inputs['supplier']);
			$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_receivings_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		$report_title_data['Receiving Type'] = ucfirst($inputs['receiving_type']);
		if ($inputs['employee_id'] != 'all') {

			$employee_info = $this->Employee->get_info($inputs['employee_id']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}
		if ($inputs['supplier'] != 'all') {

			$supplier_info = $this->Supplier->get_info($inputs['supplier']);
			$report_title_data['Supplier'] = $supplier_info->first_name . ' ' . $supplier_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		if ($inputs['item_id'] != 'all') {
			//logged in employee and branch info 
			$info = $this->Item->get_info($inputs['item_id']);
			$report_title_data['Item'] = $info->name;
		}

		return $report_title_data;
	}

	private function get_expenses_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		$report_title_data['Type'] = ucfirst($inputs['type']);
		$report_title_data['Expense Type'] = ucfirst($inputs['expense_category_type']);

		if ($inputs['employee_id'] != 'all') {
			$employee_info = $this->Employee->get_info($inputs['employee_id']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}

		if ($inputs['category'] != 'all') {
			$category_info = $this->Expenses->get_category_info($inputs['category']);
			$report_title_data['Category'] = $category_info->name;
		}else{
			$report_title_data['Category'] = 'All';
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_audit_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		$report_title_data['Action'] = ucfirst($inputs['action_type']);

		if ($inputs['employee'] != 'all') {
			$employee_info = $this->Employee->get_info($inputs['employee']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_credit_report_data($inputs)
	{
		$report_title_data = array();
		// $report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		// $report_title_data['Action'] = ucfirst($inputs['action_type']);

		if ($inputs['customer'] != 'all') {
			$customer_info = $this->Customer->get_info($inputs['customer']);
			$report_title_data['Customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
		}

		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_transfers_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];

		if ($inputs['employee_id'] != 'all') {

			$employee_info = $this->Employee->get_info($inputs['employee_id']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}


		if ($inputs['from_location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['from_location_id']);
			$report_title_data['From Branch'] = $branch_info->location_name;
		}
		if ($inputs['to_location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info2 = $this->Employee->get_branchinfo($inputs['to_location_id']);
			$report_title_data['To Branch'] = $branch_info2->location_name;
		}

		return $report_title_data;
	}
	public function detailed_transfers($start_date, $end_date, $employee_id = "all", $from_location_id = "all", $to_location_id = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'from_location_id' => $from_location_id, 'to_location_id' => $to_location_id);


		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		//$model->createTransfer($inputs); //this will create temp tables for receivings

		$headers = $this->xss_clean($model->getTransferDataColumns());
		$transfer_data = $model->getTransferData($inputs);


		$total = 0;
		$summary_data = array();
		foreach ($transfer_data as $row) {
			$total += $row['total'];
			$summary_data[] = array(
				'id' => anchor('receivings/transfer_reprint/' . $row['transfer_id'], 'PUSH ' . $row['transfer_id'], array('target' => '_blank')),
				'transfer_date' => $row['transfer_time'],
				'quantity' => round($row['pushed_quantity']),
				'employee_name' => $row['employee_name'],
				'transfering_branch' => $row['from_location_name'],
				'receiving_branch' => $row['to_location_name'],
				'total' => to_currency($row['total'])
			);
		}
		$data = array(
			'title' => 'Transfer Report',
			'report_title_data' => $this->get_transfers_report_data($inputs),
			'headers' => $headers,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'employee_id' => $employee_id,
			'to_branch' => $to_location_id,
			'from_branch' => $from_location_id,
			'summary_data' => $summary_data,

			'overall_summary_data' => array('Total' => $total)
		);

		$this->load->view('reports/tabular_details_transfer', $data);
		ini_set('memory_limit',$old_limit);
	}


	public function inventory_low()
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array();

		$this->load->model('reports/Inventory_low');
		$model = $this->Inventory_low;

		$report_data = $model->getData($inputs);

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'item_name' => $row['name'],
				'item_number' => $row['item_number'],
				'quantity' => to_quantity_decimals($row['quantity']),
				'reorder_level' => to_quantity_decimals($row['reorder_level']),
				'location_name' => $row['location_name']
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_inventory_low_report'),
			'subtitle' => '',
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);

		$this->load->view('reports/tabular', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function inventory_summary_input()
	{
		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;

		$data = array();
		$data['item_count'] = $model->getItemCountDropdownArray();

		$stock_locations = $this->xss_clean($this->Stock_location->get_allowed_locations());
		$stock_locations['all'] = $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$this->load->view('reports/inventory_summary_input', $data);
	}

	public function inventory_summary($location_id = 'all', $item_count = 'all')
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('location_id' => $location_id, 'item_count' => $item_count);

		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;

		$report_data = $model->getData($inputs);

		$tabular_data = array();
		foreach ($report_data as $row) {
			$tabular_data[] = $this->xss_clean(array(
				'item_name' => $row['name'],
				'item_number' => $row['item_number'],
				'quantity' => to_quantity_decimals($row['quantity']),
				'reorder_level' => to_quantity_decimals($row['reorder_level']),
				'location_name' => $row['location_name'],
				'cost_price' => to_currency($row['cost_price']),
				'unit_price' => to_currency($row['unit_price']),
				'subtotal' => to_currency($row['sub_total_value'])
			));
		}

		$data = array(
			'title' => $this->lang->line('reports_inventory_summary_report'),
			'subtitle' => '',
			'headers' => $this->xss_clean($model->getDataColumns()),
			'data' => $tabular_data,
			'summary_data' => $this->xss_clean($model->getSummaryData($report_data))
		);

		$this->load->view('reports/tabular', $data);
		ini_set('memory_limit',$old_limit);
	}
	//	Returns subtitle for the reports
	private function _get_subtitle_report($inputs)
	{
		$subtitle = '';

		if (empty($this->config->item('date_or_time_format'))) {
			$subtitle .= date($this->config->item('dateformat'), strtotime($inputs['start_date'])) . ' - ' . date($this->config->item('dateformat'), strtotime($inputs['end_date']));
		} else {
			$subtitle .= date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime(rawurldecode($inputs['start_date']))) . ' - ' . date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime(rawurldecode($inputs['end_date'])));
		}

		return $subtitle;
	}

	public function summary_vat($start_date, $end_date, $sale_type, $location_id = 'all')
	{
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);

		$this->load->model('reports/Summary_vat');
		$model = $this->Summary_discounts;

		$report_data = $model->getData($inputs);
		$summary = $this->xss_clean($model->getSummaryData($inputs));
	}

	public function revenue_by_item()
	{
		$data['table_headers'] = $this->xss_clean(get_vat_headers());

		$this->load->view('reports/vat', $data);
	}

	public function vat_data()
	{
		$search			= $this->input->get('search');
		$limit 			= $this->input->get('limit');
		$offset 		= $this->input->get('offset');
		$sort 			= $this->input->get('sort') ? $this->input->get('sort') : 'name';
		$order 			= $this->input->get('order');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');
		$location_id	= $this->input->get('location_id') ? $this->input->get('location_id') : 'all';

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'location_id' => $location_id, 'search' => $search, 'limit' => $limit, 'offset' => $offset, 'sort' => $sort, 'order' => $order);

		$this->load->model('reports/Summary_vat');
		$model = $this->Summary_vat;

		$report_data = $model->getData($inputs);
		$total_rows = $model->getDataCount($inputs);

		$data_rows	= array();
		foreach ($report_data->result() as $key => $value) {
			$data_rows[] = $this->xss_clean(get_vat_item_row($value->id, $value->quantity, $value->name, $value->price, $this));
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}


	public function print_filtered_report($start_date, $end_date, $employee_id, $location_id, $sale_type,  $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;



		$report_data = $model->getSalesData($inputs);



		$summary_data = array();



		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0,
			'profit' => 0
		);


		foreach ($report_data as $row) {
			$payment = $model->getPayment($row['sale_id']);
			$cost = $model->getCost($row['sale_id']);
			$summary_data[] = array(
				'id' => 'POS ' . $row['sale_id'],
				'sale_date' => date("Y-m-d h:i A", strtotime($row['sale_time'])),
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'customer_name' => $row['customer_name'],
				'employee_name' => $row['employee_name'],
				'total' => to_currency($row['amount']), //discounted total
				'total_vat' => to_currency($row['total_vat']),
				'cost' => to_currency($cost),
				'credit' => to_currency($row['credit']),
				'discount' => to_currency($row['discount']) . ((strlen($row['discount']) > 0 && $row['auth_code']) ? '(Authorized: ' . $this->Employee->get_info_by_authcode($row['auth_code'])->username . ')' : ""),
				'total_payment' => to_currency($payment->payment_amount),
				'change_due' => to_currency($payment->payment_amount - $row['total_vat'] - ($row['amount'] - $row['discount'])),
				'payment_type' => $payment->payment_type,

			);
			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];
		}
		$totals['profit'] = $totals['total'] - $totals['cost'] - $totals['discount'] - $totals['vat'];





		$data = array(
			'title'					=> "Sales Report",
			'report_title_data'            => $this->get_sales_report_data($inputs),
			'subtitle' 				=> '', //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),

			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,

			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type

		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_details_print', $data);
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_export($start_date, $end_date, $employee_id, $location_id, $sale_type,  $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesData($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Quantity");
		$sheet->setCellValue("D1", "Customer");
		$sheet->setCellValue("E1", "Employee");
		$sheet->setCellValue("F1", "Total Cost");
		$sheet->setCellValue("G1", "Discounted Total");
		$sheet->setCellValue("H1", "Total Discount");
		$sheet->setCellValue("I1", "Total VAT");
		$sheet->setCellValue("J1", "Amount Tendered");
		$sheet->setCellValue("K1", "Change Due");
		$sheet->setCellValue("L1", "Payment Types");

		$sn = 1;

		foreach ($report_data as $row) {
			$sn++;
			$payment = $model->getPayment($row['sale_id']);
			$cost = $model->getCost($row['sale_id']);

			$sheet->setCellValue("A" . $sn, 'POS ' . $row['sale_id']);
			$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['sale_time'])));
			$sheet->setCellValue("C" . $sn,  to_quantity_decimals($row['quantity_purchased']));
			$sheet->setCellValue("D" . $sn, $row['customer_name']);
			$sheet->setCellValue("E" . $sn, $row['employee_name']);
			$sheet->setCellValue("F" . $sn, $cost);
			$sheet->setCellValue("G" . $sn, $row['amount'] - $row['discount']);
			$sheet->setCellValue("H" . $sn, $row['discount']);
			$sheet->setCellValue("I" . $sn, $row['total_vat']);
			$sheet->setCellValue("J" . $sn, $payment->payment_amount);
			$sheet->setCellValue("K" . $sn, $payment->payment_amount - $row['total_vat'] - ($row['amount'] - $row['discount']));
			$sheet->setCellValue("L" . $sn, $payment->payment_type);
		}



		$writer = new Xlsx($spreadsheet);


		$filename =  "Sales_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_summary_report_items($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$model->create($inputs);
		$headers = $this->xss_clean($model->GetPrintData());
		$report_data = $model->getSalesData($inputs);

		//create array with employees as keys
		$summary_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			// 'discount' => 0,
		);

		foreach ($report_data as $key => $row) {
			$cost = $model->getCost($row['sale_id']);
			$summary_data[$row['employee_name']]['total'] += $row['amount'] - $row['discount'];
			$summary_data[$row['employee_name']]['cost'] += $cost;

			$summary_data[$row['employee_name']]['discount'] += $row['discount'];
			$summary_data[$row['employee_name']]['vat'] += $row['total_vat'];

			$payments  = $model->getPaymentTypes($row['sale_id']);
			foreach ($payments as $k => $payment) {
				if (strtolower(trim($payment['payment_type'])) == 'cash') {
					$summary_data[$row['employee_name']]['cash'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'pos') {
					$summary_data[$row['employee_name']]['pos'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'check') {
					$summary_data[$row['employee_name']]['check'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'transfer') {
					$summary_data[$row['employee_name']]['transfer'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'wallet') {
					$summary_data[$row['employee_name']]['wallet'] += $payment['payment_amount'];
				}

				$summary_data[$row['employee_name']]['payment_amount'] += $payment['payment_amount'];
			}

			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			// $totals['discount'] = $totals['discount'] + $row['discount'];

		}
		//calculate change due
		foreach ($summary_data as $name => $employee_pay) {
			$summary_data[$name]['change_due'] = $summary_data[$name]['payment_amount'] - ($summary_data[$name]['total'] + $summary_data[$name]['vat']);
		}

		$data = array(
			'title'					=> "Summary Sales Report",
			'report_title_data'            => $this->get_sales_report_data($inputs),
			'subtitle' 				=> '', //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(), //$this->xss_clean($model->getSummaryData($report_data['summary'])),
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type,
			'overall_summary_data' 	=> $totals,
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_summary_print', $data);
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_summary_report_items_export($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$model->create($inputs);
		$headers = $this->xss_clean($model->GetPrintData());
		$report_data = $model->getSalesData($inputs);

		$summary_data = array();

		//create array with employees as keys
		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Employee");
		$sheet->setCellValue("B1", "Total Cost");
		$sheet->setCellValue("C1", "Total VAT");
		$sheet->setCellValue("D1", "Total Discount");
		$sheet->setCellValue("E1", "Discounted Total");
		$sheet->setCellValue("F1", "Total Paymenet");
		$sheet->setCellValue("G1", "Total Change Due");
		$sheet->setCellValue("H1", "Cash");
		$sheet->setCellValue("I1", "POS");
		$sheet->setCellValue("J1", "Transfer");
		$sheet->setCellValue("K1", "Check");
		$sheet->setCellValue("L1", "Wallet");



		foreach ($report_data as $key => $row) {
			$cost = $model->getCost($row['sale_id']);
			$summary_data[$row['employee_name']]['total'] += $row['amount'] - $row['discount'];
			$summary_data[$row['employee_name']]['cost'] += $cost;

			$summary_data[$row['employee_name']]['discount'] += $row['discount'];
			$summary_data[$row['employee_name']]['vat'] += $row['total_vat'];

			$payments  = $model->getPaymentTypes($row['sale_id']);
			foreach ($payments as $k => $payment) {
				if (strtolower(trim($payment['payment_type'])) == 'cash') {
					$summary_data[$row['employee_name']]['cash'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'pos') {
					$summary_data[$row['employee_name']]['pos'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'check') {
					$summary_data[$row['employee_name']]['check'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'transfer') {
					$summary_data[$row['employee_name']]['transfer'] += $payment['payment_amount'];
				}
				if (strtolower(trim($payment['payment_type'])) == 'wallet') {
					$summary_data[$row['employee_name']]['wallet'] += $payment['payment_amount'];
				}

				$summary_data[$row['employee_name']]['payment_amount'] += $payment['payment_amount'];
			}
		}
		//calculate change due
		foreach ($summary_data as $name => $employee_pay) {
			$summary_data[$name]['change_due'] = $summary_data[$name]['payment_amount'] - ($summary_data[$name]['total'] + $summary_data[$name]['vat']);
		}

		//populate the spreadsheet object
		$sn = 1;
		foreach ($summary_data as $name => $d) {
			$sn++;


			$sheet->setCellValue("A" . $sn, ucwords($name));
			$sheet->setCellValue("B" . $sn, $d['cost']);
			$sheet->setCellValue("C" . $sn,  $d['vat']);
			$sheet->setCellValue("D" . $sn, $d['discount']);
			$sheet->setCellValue("E" . $sn, $d['total']);
			$sheet->setCellValue("F" . $sn, $d['payment_amount']);
			$sheet->setCellValue("G" . $sn, $d['change_due']);
			$sheet->setCellValue("H" . $sn, $d['cash']);
			$sheet->setCellValue("I" . $sn, $d['pos']);
			$sheet->setCellValue("J" . $sn, $d['transfer']);
			$sheet->setCellValue("K" . $sn, $d['check']);
			$sheet->setCellValue("L" . $sn, $d['wallet']);
		}


		$writer = new Xlsx($spreadsheet);


		$filename =  "Sales_Summary_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	private function get_sales_markup_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		$report_title_data['Markup Range'] = $inputs['start_markup'] . ' TO ' . $inputs['end_markup'];
		$report_title_data['Sales Type'] = ucfirst($inputs['sale_type']);
		if ($inputs['employee_id'] != 'all') {

			$employee_info = $this->Employee->get_info($inputs['employee_id']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}
		if ($inputs['customer_id'] != 'all') {

			$customer_info = $this->Customer->get_info($inputs['customer_id']);
			$report_title_data['Customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
		}
		if ($inputs['vatable'] != 'all') {
			$report_title_data['Vatable'] = $inputs['vatable'];
		}
		if ($inputs['credit'] != 'all') {
			$report_title_data['Credit'] = $inputs['credit'];
		}
		if ($inputs['payment_type'] != 'all') {
			$report_title_data['Payment Type'] = $inputs['payment_type'];
		}
		if ($inputs['discount'] != 'all') {
			$report_title_data['Discount'] = $inputs['discount'];
		}
		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		return $report_title_data;
	}

	private function get_sales_report_data($inputs)
	{
		$report_title_data = array();
		$report_title_data['Date'] = $inputs['start_date'] . ' TO ' . $inputs['end_date'];
		if (!empty($inputs['item_id']) && $inputs['item_id'] != 'all') {
			$item_info = $this->Item->get_info($inputs['item_id']);
			$report_title_data['Item'] = $item_info->name;
		}
		$report_title_data['Sales Type'] = ucfirst($inputs['sale_type']);
		if ($inputs['employee_id'] != 'all') {

			$employee_info = $this->Employee->get_info($inputs['employee_id']);
			$report_title_data['Employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		}
		if ($inputs['customer_id'] != 'all') {

			$customer_info = $this->Customer->get_info($inputs['customer_id']);
			$report_title_data['Customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
		}
		if ($inputs['vatable'] != 'all') {
			$report_title_data['Vatable'] = $inputs['vatable'];
		}
		if ($inputs['credit'] != 'all') {
			$report_title_data['Credit'] = $inputs['credit'];
		}
		if ($inputs['payment_type'] != 'all') {
			$report_title_data['Payment Type'] = $inputs['payment_type'];
		}
		if ($inputs['discount'] != 'all') {
			$report_title_data['Discount'] = $inputs['discount'];
		}
		if ($inputs['location_id'] != 'all') {
			//logged in employee and branch info 
			$branch_info = $this->Employee->get_branchinfo($inputs['location_id']);
			$report_title_data['Location'] = $branch_info->location_name;
		}

		

		return $report_title_data;
	}
	public function print_filtered_report_receivings($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'employee_id' => $employee_id, 'supplier' => $supplier);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;


		//Get all receivings
		$report_data = $model->getAllReceivings($inputs);

		$summary_data = array();


		$total_cost = 0;
		$total_price = 0;
		foreach ($report_data as $key => $row) {
			//add current to the overral total
			$total_cost += $row['cost'];
			$total_price += $row['price'];
			//hide the update button if account


			$summary_data[] = $this->xss_clean(array(
				'id' =>  anchor('receivings/receipt/' . $row['receiving_id'], 'RECV ' . $row['receiving_id'], array('target' => '_blank')),
				'receiving_time' => $row['receiving_time'],
				'quantity_purchased' => round($row['quantity_purchased']),
				'quantity_ordered' => round($row['quantity_ordered']),
				'employee_name' => $row['employee_name'],
				'supplier_name' => $row['supplier'],
				'cost' => $row['cost'], ///total cost
				'price' => $row['price'],
				//'payment_type' => $row['payment_type'],
				'reference' => $row['reference'],
				'comment' => $row['comment'],

				'receiving_id' => $row['receiving_id']
			));
		}



		$data = array(
			'title'					=> 'Receivings Report',
			'report_title_data' 				=> $this->get_receivings_report_data($inputs),
			//'headers' 				=> $headers,
			'summary_data' 			=> $summary_data, //$receivings,
			'overall_summary_data' 	=> array(
				'Total Cost' => $total_cost,
				'Total Price' => $total_price
			),
			'supplier'				=> $this->Supplier->get_info($supplier),
			'start_date'					=> $start_date,
			'end_date'					=> $end_date,
			'receiving_type'				=> $receiving_type,
			'location_id'				=> $location_id,
			'employee_id'				=> $employee_id,
			'supplier'				=> $supplier
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_details_print_receivings', $data);
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_receivings_export($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'employee_id' => $employee_id, 'supplier' => $supplier);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;


		//Get all receivings
		$report_data = $model->getAllReceivings($inputs);

		$summary_data = array();


		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Employee Name");
		$sheet->setCellValue("D1", "Supplier Name");
		$sheet->setCellValue("E1", "Quantity Received");
		$sheet->setCellValue("F1", "Quantity Ordered");
		$sheet->setCellValue("G1", "Total Cost");
		$sheet->setCellValue("H1", "Total Price");

		$sheet->setCellValue("I1", "Comment");
		$sheet->setCellValue("J1", "Reference");

		$sn = 1;
		foreach ($report_data as $key => $row) {
			$sn++;
			$sheet->setCellValue("A" . $sn,  'RECV ' . $row['receiving_id']);
			$sheet->setCellValue("B" . $sn,  $row['receiving_time']);
			$sheet->setCellValue("C" . $sn,   $row['employee_name']);
			$sheet->setCellValue("D" . $sn,   $row['supplier']);
			$sheet->setCellValue("E" . $sn,  round($row['quantity_purchased']));
			$sheet->setCellValue("F" . $sn,   round($row['quantity_ordered']));
			$sheet->setCellValue("G" . $sn,   $row['cost']);
			$sheet->setCellValue("H" . $sn,   $row['price']);
			$sheet->setCellValue("I" . $sn,  $row['comment']);
			$sheet->setCellValue("J" . $sn,  $row['reference']);
		}

		$writer = new Xlsx($spreadsheet);


		$filename =  "Receiving_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_transfer($start_date, $end_date, $employee_id = "all", $from_location_id = "all", $to_location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => urldecode($start_date), 'end_date' => urldecode($end_date), 'employee_id' => $employee_id, 'from_location_id' => $from_location_id, 'to_location_id' => $to_location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;


		$transfers = $model->getTransferData($inputs);

		$total = 0;
		$summary_data = array();
		foreach ($transfers as $row) {
			$total += $row['total'];
			$summary_data[] = array(
				'id' => 'PUSH ' . $row['transfer_id'],
				'transfer_date' => $row['transfer_time'],
				'quantity' => round($row['pushed_quantity']),
				'employee_name' => $row['employee_name'],
				'transfering_branch' => $row['from_location_name'],
				'receiving_branch' => $row['to_location_name'],
				'total' => $row['total']
			);
		}
		$data = array(
			'title'					=>  'Transfers Report',
			'report_title_data' 				=> $this->get_transfers_report_data($inputs),
			//'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array('Total' => $total),
			'employee_id' => $employee_id,

			'start_date'					=> $start_date,
			'end_date'					=> $end_date,
			'from_branch' => $from_location_id,
			'to_branch' => $to_location_id

		);
		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;


		$this->load->view('reports/tabular_details_print_transfers', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function print_filtered_report_transfer_export($start_date, $end_date, $employee_id = "all", $from_location_id = "all", $to_location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'from_location_id' => $from_location_id, 'to_location_id' => $to_location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		//Get all transfer items
		$transfers = $model->getTransferData($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Transfer ID.");
		$sheet->setCellValue("B1", "Date");


		$sheet->setCellValue("C1", "Quantity Transfered");
		$sheet->setCellValue("D1", "Total");

		$sheet->setCellValue("E1", "Performed By");
		$sheet->setCellValue("F1", "Transfering Branch");
		$sheet->setCellValue("G1", "Receiving Branch");


		$sn = 1;

		foreach ($transfers as $row) {


			$details = $model->getTransferDataItems($row['transfer_id']);
			foreach ($details as $item) {
				$sn++;
				$sheet->setCellValue("A" . $sn, 'PUSH ' . $row['transfer_id']);
				$sheet->setCellValue("B" . $sn, $row['transfer_time']);

				$sheet->setCellValue("C" . $sn,  $item['pushed_quantity']);
				$sheet->setCellValue("D" . $sn,  $row['total']);
				$sheet->setCellValue("E" . $sn,  $row['employee_name']);
				$sheet->setCellValue("F" . $sn,  $row['from_location_name']);
				$sheet->setCellValue("G" . $sn,  $row['to_location_name']);
			}
		}

		//download here
		$writer = new Xlsx($spreadsheet);


		$filename =  "Transfer_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	////PRODUCT SPECIFIC ITEMS RECEIVING

	public function print_filtered_report_product_specific_items_receivings($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all", $item = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'supplier' => $supplier, 'employee_id' => $employee_id, 'item_id' => $item);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getProductSpecificReceivings($inputs);

		// echo "<pre>". print_r($report_data) . "</pre>";
		// return;

		$summary_data = array();
		$details_data = array();

		$total_cost = 0;
		$total_price = 0;
		foreach ($report_data as $key => $row) {
			//add current to the overral total

			$cost = $row['item_cost_price'] * $row['quantity_purchased'];
				$price = $row['item_unit_price'] * $row['quantity_purchased'];

				$total_cost +=$cost;
				$total_price +=$price;
				$summary_data[] = $this->xss_clean(array(
					'receiving_id' => $row['receiving_id'],
					'receiving_date' => $row['receiving_date'],
					'name' => $row['name'],
					'item_number' => $row['item_number'],
					'category' => $row['category'],
					'unit_cost' => $row['item_cost_price'],
					'unit_price' => $row['item_unit_price'],
					'quantity_received' => round($row['quantity_purchased']),
					'quantity_ordered' => round($row['quantity_ordered']),
					'employee_name' => $row['employee_name'],
					'supplier_name' => $row['supplier'],
					'cost' => $cost, ///total cost
					'price' => $price, ///total 
					'reference' => $row['reference'],
					'comment' => $row['comment'],
				));

		}
		$data = array(
			'title'					=> 'Product Specific Receiving Items Report',
			'report_title_data' 				=> $this->get_receivings_report_data($inputs),
			// 'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Cost' => $total_cost,
				'Total Price' => $total_price
			),

			'start_date'					=> $start_date,
			'end_date'					=> $end_date,
			'receiving_type'				=> $receiving_type,
			'employee_id' => $employee_id,
			'location_id' => $location_id,
			'supplier' => $supplier,
			'item_id' => $inputs['item_id']
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;


		$this->load->view('reports/tabular_details_print_content_product_receivings', $data);
		ini_set('memory_limit',$old_limit);

	}
	public function print_filtered_report_product_specific_items_receivings_export($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all", $item = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'supplier' => $supplier, 'employee_id' => $employee_id, 'item_id' => $item);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getProductSpecificReceivings($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Item Number");
		$sheet->setCellValue("F1", "Employee Name");
		$sheet->setCellValue("G1", "Supplier");
		$sheet->setCellValue("H1", "Quantity Ordered");
		$sheet->setCellValue("I1", "Quantity Received");
		$sheet->setCellValue("J1", "Cost");
		$sheet->setCellValue("K1", "Retail Price");

		$sheet->setCellValue("L1", "Total Cost");
		$sheet->setCellValue("M1", "Total Price");
		$sheet->setCellValue("N1", "Reference");
		$sheet->setCellValue("O1", "Comment");

		$sn = 1;

		foreach ($report_data as $key => $row) {

			$sn++;
				$sheet->setCellValue("A" . $sn, 'RECV ' . $row['receiving_id']);
				$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['receiving_time'])));
				$sheet->setCellValue("C" . $sn, $row['name']);
				$sheet->setCellValue("D" . $sn, $row['category']);
				$sheet->setCellValue("E" . $sn, $row['item_number']);
				$sheet->setCellValue("F" . $sn, $row['employee_name']);
				$sheet->setCellValue("G" . $sn, $row['supplier'] ? $row['supplier'] : 'N/A');
				$sheet->setCellValue("H" . $sn,  round($row['quantity_ordered']));
				$sheet->setCellValue("I" . $sn, round($row['quantity_purchased']));
				$sheet->setCellValue("J" . $sn, $row['item_cost_price']);
				$sheet->setCellValue("K" . $sn, $row['item_unit_price']);
				$sheet->setCellValue("L" . $sn, $row['cost']);
				$sheet->setCellValue("M" . $sn, $row['price']);
				$sheet->setCellValue("N" . $sn, $row['reference']);
				$sheet->setCellValue("O" . $sn, $row['comment']);
		}

		$writer = new Xlsx($spreadsheet);


		$filename =  "Product_Specific_Receiving_Report_" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}


	///END PRODUCT SPECIFIC RECEIVING HERE


	public function print_filtered_report_items_receivings($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'supplier' => $supplier, 'employee_id' => $employee_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;




		$report_data = $model->getAllReceivings($inputs);

		$summary_data = array();
		$details_data = array();

		$total_cost = 0;
		$total_price = 0;
		foreach ($report_data as $key => $row) {
			//add current to the overral total
			



			$details = $model->getReceivingItemsData($row['receiving_id']);
			foreach ($details as $drow) {

				$cost = $drow['item_cost_price'] * $drow['quantity_purchased'];
				$price = $drow['item_unit_price'] * $drow['quantity_purchased'];

				$total_cost +=$cost;
				$total_price +=$price;
				$summary_data[] = $this->xss_clean(array(
					'receiving_id' => $row['receiving_id'],
					'receiving_date' => $row['receiving_time'],
					'name' => $drow['name'],
					'item_number' => $drow['item_number'],
					'category' => $drow['category'],
					'unit_cost' => $drow['item_cost_price'],
					'unit_price' => $drow['item_unit_price'],
					'quantity_received' => round($drow['quantity_purchased']),
					'quantity_ordered' => round($drow['quantity_ordered']),
					'employee_name' => $row['employee_name'],
					'supplier_name' => $row['supplier'],
					'cost' => $cost, ///total cost
					'price' => $price, ///total 
					'reference' => $row['reference'],
					'comment' => $row['comment'],
				));
			}
		}
		$data = array(
			'title'					=> 'Detailed Receiving Items Report',
			'subtitle' 				=> $this->get_receivings_report_data($inputs),
			//'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array(
				'Total Cost' => $total_cost,
				'Total Selling Price' => $total_price
			),

			'start_date'					=> $start_date,
			'end_date'					=> $end_date,
			'receiving_type'				=> $receiving_type,
			'employee_id' => $employee_id,
			'location_id' => $location_id,
			'supplier' => $supplier
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;


		$this->load->view('reports/tabular_details_print_content_receivings', $data);
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_items_receivings_export($start_date, $end_date, $receiving_type = "all", $location_id = "all", $employee_id = "all", $supplier = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'supplier' => $supplier, 'employee_id' => $employee_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;




		$report_data = $model->getAllReceivings($inputs);


		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Item Number");
		$sheet->setCellValue("F1", "Employee Name");
		$sheet->setCellValue("G1", "Supplier");
		$sheet->setCellValue("H1", "Quantity Ordered");
		$sheet->setCellValue("I1", "Quantity Received");
		$sheet->setCellValue("J1", "Cost");
		$sheet->setCellValue("K1", "Retail Price");

		$sheet->setCellValue("L1", "Total Cost");
		$sheet->setCellValue("M1", "Total Price");
		$sheet->setCellValue("N1", "Reference");
		$sheet->setCellValue("O1", "Comment");


		$sn = 1;

		foreach ($report_data as $key => $row) {



			$details = $model->getReceivingItemsData($row['receiving_id']);
			foreach ($details as $drow) {
				$sn++;
				$sheet->setCellValue("A" . $sn, 'RECV ' . $row['receiving_id']);
				$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['receiving_time'])));
				$sheet->setCellValue("C" . $sn, $drow['name']);
				$sheet->setCellValue("D" . $sn, $drow['category']);
				$sheet->setCellValue("E" . $sn, $drow['item_number']);
				$sheet->setCellValue("F" . $sn, $row['employee_name']);
				$sheet->setCellValue("G" . $sn, $row['supplier'] ? $row['supplier'] : 'N/A');
				$sheet->setCellValue("H" . $sn,  round($drow['quantity_ordered']));
				$sheet->setCellValue("I" . $sn, round($drow['quantity_purchased']));
				$sheet->setCellValue("J" . $sn, $drow['item_cost_price']);
				$sheet->setCellValue("K" . $sn, $drow['item_unit_price']);
				$sheet->setCellValue("L" . $sn, $row['cost']);
				$sheet->setCellValue("M" . $sn, $row['price']);
				$sheet->setCellValue("N" . $sn, $row['reference']);
				$sheet->setCellValue("O" . $sn, $row['comment']);
			}
		}

		$writer = new Xlsx($spreadsheet);


		$filename =  "Detailed_Receiving_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_items_transfers($start_date, $end_date, $employee_id = "all", $from_location_id = "all", $to_location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'from_location_id' => $from_location_id, 'to_location_id' => $to_location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		//Get all transfer items
		$transfers = $model->getTransferData($inputs);

		$total = 0;
		$summary_data = array();
		foreach ($transfers as $row) {
			$total += $row['total'];

			$details = $model->getTransferDataItems($row['transfer_id']);
			foreach ($details as $item) {
				$summary_data[] = array(
					'id' => 'PUSH ' . $row['transfer_id'],
					'transfer_date' => $row['transfer_time'],
					'quantity' => round($item['pushed_quantity']),
					'employee_name' => $row['employee_name'],
					'transfering_branch' => $row['from_location_name'],
					'receiving_branch' => $row['to_location_name'],
					'total' => $row['total'],
					'transfer_price' => $item['transfer_price'],
					'item_unit_price' => $item['item_unit_price'],
					'item_cost_price' => $item['item_cost_price'],
					'item_number' => $item['item_number'],
					'name' => $item['name'],
					'category' => $item['category']
				);
			}
		}
		$data = array(
			'title'					=>  'Detailed Transfers Report',
			'report_title_data' 				=> $this->get_transfers_report_data($inputs),
			//'headers' 				=> $headers,
			'summary_data' 			=> $summary_data,
			'overall_summary_data' 	=> array('Total' => $total),
			'employee_id' => $employee_id,

			'start_date'					=> $start_date,
			'end_date'					=> $end_date,
			'from_branch' => $from_location_id,
			'to_branch' => $to_location_id

		);
		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;



		$this->load->view('reports/tabular_details_print_content_transfer', $data);
		ini_set('memory_limit',$old_limit);
	}
	public function print_filtered_report_items_transfers_export($start_date, $end_date, $employee_id = "all", $from_location_id = "all", $to_location_id = "all")
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'from_location_id' => $from_location_id, 'to_location_id' => $to_location_id);
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		//Get all transfer items
		$transfers = $model->getTransferData($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Transfer ID.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Number");
		$sheet->setCellValue("E1", "Item Category");


		$sheet->setCellValue("F1", "Cost");
		$sheet->setCellValue("G1", "Price");
		$sheet->setCellValue("H1", "Transfer Price");
		$sheet->setCellValue("I1", "Quantity Transfered");
		$sheet->setCellValue("J1", "Total");

		$sheet->setCellValue("K1", "Performed By");
		$sheet->setCellValue("L1", "Transfering Branch");
		$sheet->setCellValue("M1", "Receiving Branch");


		$sn = 1;

		foreach ($transfers as $row) {

			$details = $model->getTransferDataItems($row['transfer_id']);
			foreach ($details as $item) {
				$sn++;
				$sheet->setCellValue("A" . $sn, 'PUSH ' . $row['transfer_id']);
				$sheet->setCellValue("B" . $sn, $row['transfer_time']);
				$sheet->setCellValue("C" . $sn,  $item['name']);
				$sheet->setCellValue("D" . $sn,  $item['item_number']);
				$sheet->setCellValue("E" . $sn,  $item['category']);
				$sheet->setCellValue("F" . $sn,  $item['item_cost_price']);
				$sheet->setCellValue("G" . $sn,  $item['item_unit_price']);
				$sheet->setCellValue("H" . $sn,  $item['transfer_price']);
				$sheet->setCellValue("I" . $sn,  $item['pushed_quantity']);
				$sheet->setCellValue("J" . $sn,  $row['total']);
				$sheet->setCellValue("K" . $sn,  $row['employee_name']);
				$sheet->setCellValue("L" . $sn,  $row['from_location_name']);
				$sheet->setCellValue("M" . $sn,  $row['to_location_name']);
			}
		}

		//download here
		$writer = new Xlsx($spreadsheet);


		$filename =  "Detailed_Transfer_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function print_filtered_report_items_product_specific($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all", $item_id = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type, 'item_id' => $item_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesDataProductSpecific($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0,
			'quantities' => 0
		);

		// print_r($report_data);
		// return;

		
		foreach ($report_data as $row) {

			$cost = $model->getCost($row['sale_id']);

			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];

			$total_cost = $row['quantity_purchased'] * $row['item_cost_price'];
				if ($row['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $row['pack'];
					$totals['quantities'] = $totals['quantities'] + $row['pack'];
				}else{
					$totals['quantities'] = $totals['quantities'] + $row['quantity_purchased'];
				}

				$details_data[$row['sale_id']][] = array(
					'id' => $row['sale_id'],
					'name' => $row['name'],
					'category' => $row['category'],
					'item_number' => $row['item_number'],
					'sale_time' => $row['sale_time'],
					'employee_name' => $row['employee_name'],
					'customer_name' => $row['customer_name'],
					'quantity' => $row['quantity_purchased'],
					'cost_price' => to_currency($row['item_cost_price']),
					'unit_price' => to_currency($row['item_unit_price']),
					'sales_type' => $row['qty_selected'],
					'cost' => to_currency($total_cost),
					'total' => to_currency(($row['quantity_purchased'] * $row['item_unit_price']) - $row['discount']),
					'discount_percent' => $row['discount_percent'],
					'discount' => to_currency($row['discount']),
					'vat' => to_currency($row['vat'])
				);
		}

		$data = array(
			'title'					=> "Product Specific Sales Report",
			'report_title_data'		=> $this->get_sales_report_data($inputs),
			'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type,
			'item_id'			=> $inputs['item_id']
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		// $this->load->view('reports/tabular_details_print_content', $data);
		$this->load->view('reports/tabular_details_print_product_content', $data);
		ini_set('memory_limit',$old_limit);
	}


	public function print_filtered_report_items($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesData($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0,
			'profit' => 0,
			'quantities' => 0,
		);

		foreach ($report_data as $row) {

			$cost = $model->getCost($row['sale_id']);

			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];

			//fetch the details items
			$items = $model->getSalesItemsData($row['sale_id']);
			$total_qty = 0;
			foreach ($items as $item) {

				$total_cost = $item['quantity_purchased'] * $item['item_cost_price'];
				if ($item['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $item['pack'];
					$total_qty += $item['pack'];
				}else{
					$total_qty += $item['quantity_purchased'];
				}
				$details_data[$row['sale_id']][] = array(
					'id' => $row['sale_id'],
					'name' => $item['name'],
					'category' => $item['category'],
					'item_number' => $item['item_number'],
					'employee_name' => $row['employee_name'],
					'customer_name' => $row['customer_name'],
					'quantity' => $item['quantity_purchased'],
					'cost_price' => to_currency($item['item_cost_price']),
					'unit_price' => to_currency($item['item_unit_price']),
					'sales_type' => $item['qty_selected'],
					'cost' => to_currency($total_cost),
					'total' => to_currency(($item['quantity_purchased'] * $item['item_unit_price']) - $item['discount']),
					'discount_percent' => $item['discount_percent'],
					'discount' => to_currency($item['discount']),
					'vat' => to_currency($item['vat'])

				);
			}
		}
		$totals['profit'] = $totals['total'] - $totals['cost'] - $totals['discount'] - $totals['vat'];
		$tatals['quantities'] = $total_qty;

		$data = array(
			'title'					=> "Detailed Sales Items Report",
			'report_title_data'		=> $this->get_sales_report_data($inputs),
			'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_details_print_content', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function print_filtered_product_report_items($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesData($inputs);

		$details_data = array();

		$totals = array(
			'total' => 0,
			'cost' => 0,
			'vat' => 0,
			'discount' => 0
		);

		foreach ($report_data as $row) {

			$cost = $model->getCost($row['sale_id']);

			//update totals
			$totals['total'] = $totals['total'] + $row['amount'] - $row['discount'];
			$totals['cost'] = $totals['cost'] + $cost;
			$totals['vat'] = $totals['vat'] + $row['total_vat'];
			$totals['discount'] = $totals['discount'] + $row['discount'];

			//fetch the details items
			$items = $model->getSalesItemsData($row['sale_id']);
			foreach ($items as $item) {

				$total_cost = $item['quantity_purchased'] * $item['item_cost_price'];
				if ($item['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $item['pack'];
				}
				$details_data[$row['sale_id']][] = array(
					'id' => $row['sale_id'],
					'name' => $item['name'],
					'category' => $item['category'],
					'item_number' => $item['item_number'],
					'employee_name' => $row['employee_name'],
					'customer_name' => $row['customer_name'],
					'quantity' => $item['quantity_purchased'],
					'cost_price' => to_currency($item['item_cost_price']),
					'unit_price' => to_currency($item['item_unit_price']),
					'sales_type' => $item['qty_selected'],
					'cost' => to_currency($total_cost),
					'total' => to_currency(($item['quantity_purchased'] * $item['item_unit_price']) - $item['discount']),
					'discount_percent' => $item['discount_percent'],
					'discount' => to_currency($item['discount']),
					'vat' => to_currency($item['vat'])

				);
			}
		}

		$data = array(
			'title'					=> "Detailed Sales Items Report",
			'report_title_data'		=> $this->get_sales_report_data($inputs),
			'subtitle' 				=> "", //$this->_get_subtitle_report(array('start_date' => $start_date, 'end_date' => $end_date)),
			'details_data' 			=> $details_data,
			'overall_summary_data' 	=> $totals,
			'employee_id'			=> $employee_id,
			'start'					=> $start_date,
			'end'					=> $end_date,
			'sale_type'				=> $sale_type,
			'location_id'			=> $location_id,
			'credit'				=> $credit,
			'vatable'				=> $vatable,
			'customer_id'			=> $customer_id,
			'discount'				=> $discount,
			'payment_type'			=> $payment_type
		);

		//logged in employee and branch info 
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

		$this->load->view('reports/tabular_details_print_content', $data);
		ini_set('memory_limit',$old_limit);
	}

	public function print_filtered_report_items_export($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesData($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Item Number");
		$sheet->setCellValue("F1", "Employee Name");
		$sheet->setCellValue("G1", "Customer Name");
		$sheet->setCellValue("H1", "Quantity");
		$sheet->setCellValue("I1", "Cost Price");
		$sheet->setCellValue("J1", "Unit Price");
		$sheet->setCellValue("K1", "Sales Type");
		$sheet->setCellValue("L1", "Total Cost");
		$sheet->setCellValue("M1", "Total Amount");
		$sheet->setCellValue("N1", "Discount Percent");
		$sheet->setCellValue("O1", "Total Discount");
		$sheet->setCellValue("P1", "Total VAT");

		$sn = 1;

		foreach ($report_data as $row) {



			//fetch the details items
			$items = $model->getSalesItemsData($row['sale_id']);
			foreach ($items as $item) {
				$sn++;
				$total_cost = $item['quantity_purchased'] * $item['item_cost_price'];
				if ($item['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $item['pack'];
				}

				$sheet->setCellValue("A" . $sn, 'POS ' . $row['sale_id']);
				$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['sale_time'])));
				$sheet->setCellValue("C" . $sn, $item['name']);
				$sheet->setCellValue("D" . $sn, $item['category']);
				$sheet->setCellValue("E" . $sn, $item['item_number']);
				$sheet->setCellValue("F" . $sn, $row['employee_name']);
				$sheet->setCellValue("G" . $sn, $row['customer_name']);
				$sheet->setCellValue("H" . $sn,  round($item['quantity_purchased']));
				$sheet->setCellValue("I" . $sn, $item['item_cost_price']);
				$sheet->setCellValue("J" . $sn, $item['item_unit_price']);
				$sheet->setCellValue("K" . $sn, ucfirst($item['qty_selected']));
				$sheet->setCellValue("L" . $sn, $total_cost);
				$sheet->setCellValue("M" . $sn, ($item['quantity_purchased'] * $item['item_unit_price']) - $item['discount']);
				$sheet->setCellValue("N" . $sn, $item['discount_percent']);
				$sheet->setCellValue("O" . $sn, $item['discount']);
				$sheet->setCellValue("P" . $sn, $item['vat']);
			}
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Detailed_Sales_Report" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	public function print_filtered_report_items_product_specific_export($start_date, $end_date, $employee_id, $location_id, $sale_type, $credit = 'all', $vatable = 'all', $customer_id = "all", $discount = 'all', $payment_type = "all", $item_id = "all")
	{

		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'location_id' => $location_id, 'sale_type' => $sale_type,  'credit' => $credit, 'vatable' => $vatable, 'customer_id' => $customer_id, 'discount' => $discount, 'payment_type' => $payment_type, 'item_id' => $item_id);
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$report_data = $model->getSalesDataProductSpecific($inputs);

		// print_r($inputs);

		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Receipt No.");
		$sheet->setCellValue("B1", "Date");
		$sheet->setCellValue("C1", "Item Name");
		$sheet->setCellValue("D1", "Item Category");
		$sheet->setCellValue("E1", "Item Number");
		$sheet->setCellValue("F1", "Date Sold");
		$sheet->setCellValue("G1", "Employee Name");
		$sheet->setCellValue("H1", "Customer Name");
		$sheet->setCellValue("I1", "Quantity");
		$sheet->setCellValue("J1", "Cost Price");
		$sheet->setCellValue("K1", "Unit Price");
		$sheet->setCellValue("L1", "Sales Type");
		$sheet->setCellValue("M1", "Total Cost");
		$sheet->setCellValue("N1", "Total Amount");
		$sheet->setCellValue("O1", "Discount Percent");
		$sheet->setCellValue("P1", "Total Discount");
		$sheet->setCellValue("Q1", "Total VAT");

		$sn = 1;

		foreach ($report_data as $row) {

			$sn++;
				$total_cost = $row['quantity_purchased'] * $row['item_cost_price'];
				if ($row['qty_selected'] == 'wholesale') {
					$total_cost = $total_cost * $row['pack'];
				}

				$sheet->setCellValue("A" . $sn, 'POS ' . $row['sale_id']);
				$sheet->setCellValue("B" . $sn, date("Y-m-d h:i A", strtotime($row['sale_time']))); 
				$sheet->setCellValue("C" . $sn, $row['name']);
				$sheet->setCellValue("D" . $sn, $row['category']);
				$sheet->setCellValue("E" . $sn, $row['item_number']);
				$sheet->setCellValue("F" . $sn, $row['sale_time']);
				$sheet->setCellValue("G" . $sn, $row['employee_name']);
				$sheet->setCellValue("H" . $sn, $row['customer_name']);
				$sheet->setCellValue("I" . $sn,  round($row['quantity_purchased']));
				$sheet->setCellValue("J" . $sn, $row['item_cost_price']);
				$sheet->setCellValue("K" . $sn, $row['item_unit_price']);
				$sheet->setCellValue("L" . $sn, ucfirst($row['qty_selected']));
				$sheet->setCellValue("M" . $sn, $total_cost);
				$sheet->setCellValue("N" . $sn, ($row['quantity_purchased'] * $row['item_unit_price']) - $row['discount']);
				$sheet->setCellValue("O" . $sn, $row['discount_percent']);
				$sheet->setCellValue("P" . $sn, $row['discount']);
				$sheet->setCellValue("Q" . $sn, $row['vat']);
		}

		$writer = new Xlsx($spreadsheet);

		$filename =  "Product_Specific_Sales_Report_" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		ini_set('memory_limit',$old_limit);
	}

	/**
	 * Create the excel file and save it in a file
	 */
	public function create_excel()
	{
		$old_limit = ini_get('memory_limit');
		ini_set('memory_limit','-1');
		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Name");
		$sheet->setCellValue("B1", "Age");

		$sheet->setCellValue("A2", "Emmanuel Wilson Chukwu");
		$sheet->setCellValue("B2", 27);

		$writer = new Xlsx($spreadsheet);


		$path  = "download/emp" . $this->Employee->get_logged_in_employee_info()->person_id;
		mkdir($path);
		$filename = $path . "/example.xlsx";

		$writer->save($filename);
		ini_set('memory_limit',$old_limit);
	}
	/**
	 * Create the excel file and download it
	 */
	public function download_excel()
	{
		$spreadsheet = new SpreadSheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "Name");
		$sheet->setCellValue("B1", "Age");

		$sheet->setCellValue("A2", "Column");
		$sheet->setCellValue("B2", 27);

		$writer = new Xlsx($spreadsheet);


		$filename =  "filename";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
	}
}
