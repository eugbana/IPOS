<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Expense extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('expense');

		$this->load->library('barcode_lib');
		$this->load->library('sale_lib');
		$this->load->library('item_lib');

        $this->load->library('simpleXLSX');
        $this->load->model('Expenses');
    }
    
    public function index()
	{

		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filtes = array_merge($filters, $filledup);

		$show = count($filledup) != 0 ? false : true;

		$item_location = $this->sale_lib->get_sale_location();
		$data['table_headers'] = $this->xss_clean(get_expense_manage_table_headers($show));
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array();
		//Add all categories to the filter
		$cats = $this->Expenses->categories_list();

		foreach ($cats as $row => $value) {
			$data['filters'][str_replace(' ', '_', $value['name'])] = $value['name'];
		}

		$types = array(
			'all' => 'All',
			'INFLOW' => 'INFLOW',
			'OUTFLOW' => 'OUTFLOW',
		);

		$data['types'] = $types;

		$data['balance'] = to_currency($this->Expenses->get_current_balance());
		// $data['balance'] = $this->Expenses->get_current_balance();

		$this->load->view('expenses/manage', $data);
    }
    
    public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');
		$type = $this->input->get('type');

		$this->item_lib->set_item_location($this->input->get('stock_location'));

		$filters = array(
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'stock_location_id' => $this->item_lib->get_item_location(),
		);

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		//$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers($filters));

        $expenses = $this->Expenses->search($search, $filters, 0, $offset, $type)->result();

		//$total_rows = $this->Item->get_found_rows($search, $filters);
        $found_expenses = array_slice($expenses, $offset, $limit); //limit is usually more than 0

        $total_rows = count($expenses);

		$data_rows = array();
		foreach ($found_expenses as $expense) {
            // print_r($expense);
			if (count(get_expense_data_row($expense, $this)) != 0) {
                $data_rows[] = $this->xss_clean(get_expense_data_row($expense, $this));
			}
		}
		//$this->load->view('items/manage', $data);
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
    }

	public function categories()
	{
		$cat_items = array();
		$categories = $this->Expenses->categories_list();
		$data['categories'] = $categories;
		$data['types'] = array(
			'ADMINISTRATIVE' => 'ADMINISTRATIVE',
			'OVERHEAD' => 'OVERHEAD'
		);
		$this->load->view('expenses/category', $data);
    }

    public function categories_list()
	{
		echo json_encode($this->get_categories());
    }
    
    public function get_categories()
	{
		// $cat_items = array();
		$data = $this->Expenses->categories_list();
		// foreach ($data as $row => $value) {

		// 	$cat_items[] = array('id' => $value['id'], 'name' => $value['name']);
		// }
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		return $results;
	}
    
    public function save_expense_category($expense_category_id = -1)
	{
		//Save item data
		$category = $this->input->post('category');
		if ($category == '') { 
			$expense_category_data = array(
				'name' => $this->input->post('category_name'),
				'type' => $this->input->post('category_type'),
			);

			$this->Expenses->save_category($expense_category_data);
		} else {
			$new_cate = $this->input->post('category_name');
			$new_type = $this->input->post('category_type');
			$new_item_data = array(
				'name' => $new_cate,
				'type' => $new_type,
			);

			$this->Expenses->update_category($new_item_data, $category);
		}

		redirect('expense/categories');
	}
	
    public function view($expense_id = -1)
	{
		$expense_info = $this->Expenses->get_info($expense_id);
		foreach (get_object_vars($expense_info) as $property => $value) {
			$expense_info->$property = $this->xss_clean($value);
		}

		if ($expense_id == -1) {
			$expense_info->type = 'OUTFLOW';
		}
		$data['expense_info'] = $expense_info;
		
		$categories = array();
		foreach ($this->Expenses->get_all_expense_categories()->result_array() as $row) {
			$expense_cat_id = $row['id']; 
			$name = $row['name']; 
			if($expense_cat_id != 1)
			$categories[$this->xss_clean($expense_cat_id)] = $this->xss_clean($name);
		}
		$data['categories'] = $categories;
		$data['selected_category'] = $expense_info->name;
		
		$types = array(
			'INFLOW' => 'INFLOW (DEBIT)',
			'OUTFLOW' => 'OUTFLOW (CREDIT)'
		);
		$data['selected_type'] = $expense_info->type;
		$data['types'] = $types;

		$this->load->view('expenses/form', $data);
	}

	public function save($expense_id = -1)
	{

		$employee = $this->Employee->get_logged_in_employee_info();
		$employee_id = $employee->person_id;

		$category = $this->input->post('category');
		$description = $this->input->post('description');
		$type = $this->input->post('type');
		$receipt_no = $this->input->post('receipt_no');
		$amount = $this->input->post('amount');
		$expense_data = array(
			'expense_category_id' => $type == 'INFLOW' ? 1 : $category,
			'details' => $description,
			'type' => $type,
			'amount' => $amount,
			'receipt_no' => $this->input->post('receipt_no'),
			'balance' => $this->Expenses->get_balance_from_amount($amount, $type),
			'employee_id' => $employee_id,
			'location_id' => 2
		);

		// $cur_item_info = $this->Item->get_info($item_id);
		// $this->Expenses->save($expense_data, $expense_id);
		// exit();
		if ($this->Expenses->save($expense_data, $expense_id)) {
			$success = TRUE;
			$new_item = FALSE;

			// //New item
			if ($expense_id == -1) {
				$expense_id = $expense_data['id'];
				$new_item = TRUE;
			}

			if ($success) {
				$message = $this->xss_clean('Action successful');

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $expense_id));
			} else {
				$message = $this->xss_clean('Error occured');

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $expense_id));
			}
		} else //failure
		{
			$message = $this->xss_clean("Error processing this Expense");

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}

}
