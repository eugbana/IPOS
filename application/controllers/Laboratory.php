<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Laboratory extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('laboratory');

		$this->load->library('item_lib');
		$this->load->library('sale_lib');
		$this->load->library('tax_lib');
		$this->load->library('barcode_lib');
		$this->load->library('email_lib');
		$this->load->library('token_lib');
	}

	public function index()
	{

		$data['table_headers'] = $this->xss_clean(get_laboratory_manage_table_headers());

		$this->load->view('laboratory/tests', $data);
	}
    public function pending_invoices()
    {
        $this->sale_lib->clear_all();
        $data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);

        $data['selected_payment_type'] = $this->sale_lib->get_payment_type();
        $data['from_cashier'] = true;

        $this->load->view('account/unprocessed', $data);
    }
    public function edit_invoice($inv_id = null){
        if(!$inv_id){
            redirect(base_url('laboratory/test_start'));
        }
        $invoice_details = $this->Item->total_invoice_item($inv_id);
        if($invoice_details && count($invoice_details) > 0){
            foreach ($invoice_details as $detail){
                $data['itemised'][] =$detail['item_id'];
            }
        }
        $data['items'] = $invoice_details;
        $data['editing'] = true;
        $data['inv_id'] = $inv_id;
        $data['inv_detail'] = $this->Customer->get_lab_invoice_info($inv_id);
        $laboratory_test = $this->Item->get_lab_items();
        $customer_info = $this->_load_customer_data($data['inv_detail']->person_id, $data, TRUE);
        $this->sale_lib->set_customer($data['inv_detail']->person_id);
        $data['laboratory_test'] = $laboratory_test;
        $this->load->view('laboratory/manage', $data);
    }
	public function test_start()
	{
		$this->sale_lib->clear_all();
		$laboratory_test = $this->Item->get_lab_items();
		$data['laboratory_test'] = $laboratory_test;
		$gender = array(1 => 'Male', 2 => 'Female');
		$data['gender'] = $gender;

		// filters that will be loaded in the multiselect dropdown

		//$this->sale_lib->empty_cart();


		//$item_inf = $this->CI->Item->global_search;
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['cart'] = $this->sale_lib->get_labcart();
		$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);



		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['discounted_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		if ($data['cash_rounding']) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		if ($customer_info && $this->config->item('customer_reward_enable') == TRUE) {
			$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);
		} else {
			$data['payment_options'] = $this->Sale->get_payment_options();
		}
		$quote_number = $this->sale_lib->get_quote_number();
		if ($quote_number != NULL) {
			$data['quote_number'] = $quote_number;
		}

		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);

		$invoice_format = $this->config->item('sales_invoice_format');
		$data['invoice_format'] = $invoice_format;

		$this->set_invoice_number($invoice_format);
		$data['invoice_number'] = $invoice_format;

		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['quote_or_invoice_mode'] = $data['mode'] == 'sale_invoice' || $data['mode'] == 'sale_quote';
		$data['sales_or_return_mode'] = $data['mode'] == 'sale' || $data['mode'] == 'return';
		if ($this->sale_lib->get_mode() == 'sale_invoice') {
			$data['mode_label'] = $this->lang->line('sales_invoice');
		} elseif ($this->sale_lib->get_mode() == 'sale_quote') {
			$data['mode_label'] = $this->lang->line('sales_quote');
		} else {
			$data['mode_label'] = $this->lang->line('sales_receipt');
		}
		$data = $this->xss_clean($data);


		$this->load->view('laboratory/manage', $data);
	}
	public function cashier()
	{

		$data['cart'] = $this->sale_lib->get_cart();
		$data['notice'] = $this->sale_lib->notice_transfer_items($this->item_lib->get_item_location());
		$data['table_headers'] = $this->xss_clean(get_laboratory_manage_table_headers());
		$data['transfer'] = $this->sale_lib->global_transfer_items($this->item_lib->get_item_location());
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array(
			'empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'is_serialized' => $this->lang->line('items_serialized_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'search_custom' => $this->lang->line('items_search_custom_items'),
			'is_deleted' => $this->lang->line('items_is_deleted')
		);

		$this->load->view('laboratory/cashier_home', $data);
	}
	public function result_info()
	{
		$sale_id = $this->input->post('sale_id');
		// $result_info = $this->Item->get_labresult_info($sale_id);


		// $data['sale_id'] = $sale_id;
		// $data['invoice'] = $result_info->invoice_id;
		// $data['status'] = $result_info->status;
		// $customer_id = $result_info->customer_id;
		// $customer_info = $this->_load_customer_data($customer_id, $data);



		//$data['cart'] = $this->sale_lib->get_labsaveresultcart_reordered($sale_id);

		// filters that will be loaded in the multiselect dropdown
		$data = $this->fetch_result_info($sale_id);

		$this->load->view('laboratory/results_check', $data);
	}
	public function print_result_info()
	{
		$sale_id = $this->input->post('sale_id');
		$result_info = $this->Item->get_labresult_info($sale_id);
		$scientist_id = $result_info->scientist;
		$employee_info = $this->Employee->get_info($scientist_id);
		//$data['scientist'] = $sale_id;
		$data['sale_id'] = $sale_id;
		//$data['result'] = $result_info->sale_id;
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['invoice'] = $result_info->invoice_id;
		$data['status'] = $result_info->status;
		$data['result_time'] = date("Y-m-d h:i A", strtotime($result_info->result_time));
		$data['result_end'] = date("Y-m-d h:i A", strtotime($result_info->result_end));

		$customer_id = $result_info->customer_id;
		$customer_info = $this->_load_customer_data($customer_id, $data);

		$data['cart'] = $this->sale_lib->get_printresult_items_ordered($sale_id, $result_info->status);

		$data['can_print'] = $this->input->post('tests'); //this is an array of item_id to print

		// filters that will be loaded in the multiselect dropdown

		$this->load->view('laboratory/receipt', $data);
		$this->sale_lib->clear_all();
	}

	public function new_test()
	{

		$data['cart'] = $this->sale_lib->get_cart();
		$data['notice'] = $this->sale_lib->notice_transfer_items();
		$data['table_headers'] = $this->xss_clean(get_laboratory_manage_table_headers());
		$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array(
			'empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'is_serialized' => $this->lang->line('items_serialized_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'search_custom' => $this->lang->line('items_search_custom_items'),
			'is_deleted' => $this->lang->line('items_is_deleted')
		);

		$this->load->view('laboratory/tests', $data);
	}
	public function view_category($lab_category_id = -1)
	{

		$test_info = $this->Item->labcategory_info($lab_category_id);
		foreach (get_object_vars($test_info) as $property => $value) {
			$test_info->$property = $this->xss_clean($value);
		}

		if ($lab_category_id == -1) {
		}



		$data['test_info'] = $test_info;

		//$suppliers = array('' => $this->lang->line('items_none'));
		/*foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['company_name']);
		}
		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $test_info->supplier_id;*/



		$this->load->view('laboratory/category_form', $data);
	}
	public function lab_categorysave($lab_category_id = -1)
	{
		$lab_category_name = $this->input->post('lab_category_name');
		//Save item data

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		//$cur_item_info = $this->Item->get_info($item_id);

		if ($this->Item->lab_category_save($lab_category_name, $lab_category_id)) {
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if ($lab_category_id == -1) {
				$lab_category_id = $item_data['lab_category_id'];
				$new_item = TRUE;
			}

			//$items_taxes_data = array();
			//$tax_names = $this->input->post('tax_names');
			//$tax_percents = $this->input->post('tax_percents');

			//Save item quantity



			if ($success) {
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			} else {
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		} else //failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}

		//$data['laboratory_items']=$this->Item->laboratory_items();
		//$this->load->view('laboratory/tests');
		//$this->tests();
	}
	public function lab_testunitsave($lab_testunit_id = -1)
	{
		$lab_testunit_name = $this->input->post('lab_testunit_name');
		//Save item data

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		//$cur_item_info = $this->Item->get_info($item_id);

		if ($this->Item->lab_testunit_save($lab_testunit_name, $lab_testunit_id)) {
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if ($lab_testunit_id == -1) {
				$lab_testunit_id = $item_data['lab_testunit_id'];
				$new_item = TRUE;
			}

			//$items_taxes_data = array();
			//$tax_names = $this->input->post('tax_names');
			//$tax_percents = $this->input->post('tax_percents');

			//Save item quantity


			if ($success) {
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['lab_testunit_name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			} else {
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		} else //failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}

		//$data['laboratory_items']=$this->Item->laboratory_items();
		//$this->load->view('laboratory/tests');
		//$this->tests();
	}
	public function lab_testsubgroupsave($lab_subgroup_id = -1)
	{
		$lab_subgroup_name = $this->input->post('lab_subgroup_name');
		//Save item data

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		//$cur_item_info = $this->Item->get_info($item_id);

		if ($this->Item->lab_testsubgroup_save($lab_subgroup_name, $lab_subgroup_id)) {
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if ($lab_subgroup_id == -1) {
				$lab_subgroup_id = $item_data['lab_subgroup_id'];
				$new_item = TRUE;
			}

			//$items_taxes_data = array();
			//$tax_names = $this->input->post('tax_names');
			//$tax_percents = $this->input->post('tax_percents');

			//Save item quantity


			if ($success) {
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['lab_testunit_name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			} else {
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		} else //failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}

		//$data['laboratory_items']=$this->Item->laboratory_items();
		//$this->load->view('laboratory/tests');
		//$this->tests();
	}

	/*
	AJAX call to verify if an phone address already exists
	*/
	public function ajax_check_phone()
	{
		// $exists = $this->Customer->check_phone_exists(strtolower($this->input->post('phone_number')), $this->input->post('person_id'));
		// echo !$exists ? 'true' : 'false';
		echo 'true';
	}


	public function view_testunit($lab_testunit_id = -1)
	{

		$test_info = $this->Item->labtestunit_info($lab_testunit_id);
		foreach (get_object_vars($test_info) as $property => $value) {
			$test_info->$property = $this->xss_clean($value);
		}

		if ($lab_testunit_id == -1) {
		}



		$data['test_info'] = $test_info;

		$this->load->view('laboratory/testunit_form', $data);
	}
	public function view_subgroup($lab_subgroup_id = -1)
	{

		$test_info = $this->Item->labtestsubgroup_info($lab_subgroup_id);
		foreach (get_object_vars($test_info) as $property => $value) {
			$test_info->$property = $this->xss_clean($value);
		}

		if ($lab_subgroup_id == -1) {
		}



		$data['test_info'] = $test_info;

		$this->load->view('laboratory/testsubgroup_form', $data);
	}
	private function _load_lab_invoice_data($invoice_id, &$data, $stats = FALSE)
	{
		$invoice_info = '';

		if ($invoice_id != -1) {
			$invoice_info = $this->Customer->get_lab_invoice_info($invoice_id);

			$data['person_id'] = $invoice_info->person_id;
			$data['doctor_name'] = $invoice_info->doctor_name;
		}

		return $invoice_info;
	}

	public function lab_result_sales_renamed()  //jude changed this from `lab_result_sales` to `lab_result_sales_renamed`
	{
		// Save the data to the sales table

		$invoice_id = $this->input->post('invoice_id');
		$invoice_details = $this->Item->total_invoice_item($invoice_id);
		$quantity = 1;
		$item_location = 1;
		$discount = 0;
		foreach ($invoice_details as $row => $value) {
			$this->sale_lib->item_add_lab($value['item_id'], $quantity, $item_location, $discount);
		}

		//$invoice_id=$this->input->post('invoice_id');
		$invoice_info = $this->_load_lab_invoice_data($invoice_id, $data);
		$customer_id = $data['person_id'];
		//$customer_id=23;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		//$data['taxes']=array();
		$data['cart'] = $this->sale_lib->get_lab_accountcart();
		$data['dinner_table'] = 1;
		$data['comments'] = '';
		$data['payments'] = 5400;
		//$customer_id=23;
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['sale_status'] = '0'; // Complete
		$data['sale_id_num'] = $this->Sale->lab_accountsave($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes'], $invoice_id);

		$data['sale_id'] = 'POS ' . $data['sale_id_num'];

		$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
		$data = $this->xss_clean($data);

		if ($data['sale_id_num'] == -1) {
			$data['error_message'] = $this->lang->line('sales_transaction_failed');
		} else {
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

			// Reload (sorted) and filter the cart line items for printing purposes
			$data['cart'] = $this->get_filtered($this->sale_lib->get_labresultcart_reordered($data['sale_id_num']));

			$this->load->view('account/receipt', $data);
			$this->sale_lib->clear_all();
		}
		//$this->load->view('sales/receipt', $data);
	}
	public function lab_result_receipt()
	{
		// Save the data to the sales table
		$data = array();

		//$customer_id=$data['person_id'];
		$customer_id = $this->sale_lib->get_customer();
		$data['customer_id'] = $customer_id;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		//$data['taxes']=array();
		$data['cart'] = $this->sale_lib->get_lab_resultcart();
		$sale_id = $data['sale_id'] = $this->sale_lib->get_sales_id();


		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['sale_status'] = '0'; // Complete
		$this->Sale->lab_save_result($data['sale_status'], $data['cart'], $sale_id, $employee_id);



		$this->load->view('laboratory/receipt', $data);
		$this->sale_lib->clear_all();

		//$this->load->view('sales/receipt', $data);
	}

	public function lab_result_saved()
	{
		// Save the data to the sales table
		$data = array();

		//$customer_id=$data['person_id'];
		$customer_id = $this->sale_lib->get_customer();
		$data['customer_id'] = $customer_id;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		//$data['taxes']=array();
		$data['cart'] = $this->sale_lib->get_lab_resultcart();

		// print('<pre>');
		// print_r($data['cart']);
		// print('</pre>');

		// die();
		$sale_id = $data['sale_id'] = $this->sale_lib->get_sales_id();
		$data['sale_id'] = $sale_id;


		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['sale_status'] = '0'; // Complete

		$this->Sale->lab_saved_result($data['sale_status'], $data['cart'], $sale_id, $employee_id);



		//$this->load->view('account/receipt', $data);
		$this->sale_lib->clear_all();
		$this->load->view('laboratory/new_results', $data);
		//$this->load->view('laboratory/check', $data);
	}
	public function new_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		$data['anything'] = '';
		$this->sale_lib->clear_all();
		$this->load->view('laboratory/new_results', $data);
	}

	public function pending_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;

		$this->sale_lib->clear_all();
		$this->load->view('laboratory/pending_results', $data);
	}
	public function completed_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;

		$this->sale_lib->clear_all();
		$this->load->view('laboratory/result', $data);
	}
	public function lab_pending_result()
	{
		// Save the data to the sales table

		//$sale_id=$this->input->post('sale_id');
		$invoice_id = $this->input->post('invoice_id');

		//$invoice_details=$this->Item->total_invoice_item($invoice_id);
		$sale_info = $this->Item->get_saleinfo($invoice_id);
		$sale_id = $sale_info->sale_id;
		$scientist_id = $sale_info->scientist;
		$this->sale_lib->set_sales_id($sale_id);
		$quantity = NULL;
		$item_location = 1;


		//foreach ($invoice_details as $row => $value) {
		//	$this->sale_lib->item_add_result($value['item_id'], $quantity, $item_location);
		//}

		//$invoice_id=$this->input->post('invoice_id');
		$invoice_info = $this->_load_lab_invoice_data($invoice_id, $data);
		$customer_id = $data['person_id'];
		$this->sale_lib->set_customer($customer_id);
		//$customer_id=23;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		$data['sale_id'] = $sale_id;
		//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		//$data['taxes']=array();
		$data['customer_id'] = $customer_id;
		//$data['cart']=$this->sale_lib->get_lab_resultcart();
		$data['cart'] = $this->sale_lib->get_labsaveresultcart_reordered($sale_id);
		// $data['cart'] = $this->sale_lib->get_lab_resultcart();

		//$data['invoice_details'] = $invoice_details;

		//$customer_id=23;
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		// return "<pre>" . print_r($data) . "</pre>";

		// print "<pre>";
		// print_r($data);
		// print "</pre>";

		// die();

		$this->load->view('laboratory/check', $data);
	}

	public function quick(){
		$data['cart'] = $this->sale_lib->get_lab_resultcart();
		print "<pre>";
		print_r($data);
		print "</pre>";

		die();
	}
	public function lab_result()
	{
		// Save the data to the sales table

		$invoice_id = $this->input->post('invoice_id');
		$invoice_details = $this->Item->total_invoice_item($invoice_id);
		$sale_info = $this->Item->get_saleinfo($invoice_id);
		$sale_id = $sale_info->sale_id;
		$this->sale_lib->set_sales_id($sale_id);
		$quantity = NULL;
		$item_location = 1;

		foreach ($invoice_details as $row => $value) {
			//references are set to zero
			$this->sale_lib->item_add_result($value['item_id'], $value['test_type'], $item_location);
		}

		//$invoice_id=$this->input->post('invoice_id');
		$invoice_info = $this->_load_lab_invoice_data($invoice_id, $data);
		$customer_id = $data['person_id'];
		$this->sale_lib->set_customer($customer_id);
		//$customer_id=23;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		//$data['taxes']=array();
		//$data['sale_id']=$sale_id;
		$data['customer_id'] = $customer_id;
		$fbc = array(
			1 => array('extra_name' => 'WBC', 'test_comment' => '6.30', 'test_unit' => '10³/uL', 'normal_value' => '[5.00 - 10.00]'),
			2 => array('extra_name' => 'NEU', 'test_comment' => '3.30', 'test_unit' => '10³/uL', 'normal_value' => '[2.00 - 7.50]'),
			3 => array('extra_name' => 'LYM', 'test_comment' => '2.39', 'test_unit' => '10³/uL', 'normal_value' => '[1.30 - 4.00]'),
			4 => array('extra_name' => 'MON', 'test_comment' => '0.40', 'test_unit' => '10³/uL', 'normal_value' => '[0.15 - 0.70]'),
			5 => array('extra_name' => 'EO', 'test_comment' => '0.08', 'test_unit' => '10³/uL', 'normal_value' => '[0.00 - 0.50]'),
			6 => array('extra_name' => 'BAS', 'test_comment' => '3.30', 'test_unit' => '10³/uL', 'normal_value' => '[0.00 - 0.15]'),
			7 => array('extra_name' => 'NEU%', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => '[40.0 - 75.0]'),
			8 => array('extra_name' => 'LYM%', 'test_comment' => '0.40', 'test_unit' => '%', 'normal_value' => '[21.0 - 40.0]'),
			9 => array('extra_name' => 'MON%', 'test_comment' => '0.08', 'test_unit' => '%', 'normal_value' => '[3.0 - 7.0]'),
			10 => array('extra_name' => 'EO%', 'test_comment' => '3.30', 'test_unit' => '%', 'normal_value' => '[0.0 - 5.0]'),
			11 => array('extra_name' => 'BAS%', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => '[0.0 - 1.5]'),
			12 => array('extra_name' => 'RBC', 'test_comment' => '0.40', 'test_unit' => '10⁶/uL', 'normal_value' => '[4.00 - 5.50]'),
			13 => array('extra_name' => 'HGB', 'test_comment' => '0.08', 'test_unit' => 'g/dL', 'normal_value' => '[12.0 - 17.4]'),
			14 => array('extra_name' => 'HCT', 'test_comment' => '3.30', 'test_unit' => '%', 'normal_value' => '[M:37 -52, F:38-45]'),
			15 => array('extra_name' => 'MCV', 'test_comment' => '2.39', 'test_unit' => 'fL', 'normal_value' => '[76.0 - 96.0]'),
			16 => array('extra_name' => 'MCH', 'test_comment' => '0.40', 'test_unit' => 'pg', 'normal_value' => '[27.0 - 32.0]'),
			17 => array('extra_name' => 'MCHC', 'test_comment' => '0.08', 'test_unit' => 'g/dL', 'normal_value' => '[30.0 - 35.0]'),
			18 => array('extra_name' => 'RDWsd', 'test_comment' => '3.30', 'test_unit' => 'fL', 'normal_value' => '[46.0 - 59.0]'),
			19 => array('extra_name' => 'RDWcv', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => '[0.00 - 16.0]'),
			20 => array('extra_name' => 'PLT', 'test_comment' => '0.40', 'test_unit' => '10³/uL', 'normal_value' => '[150 - 400]'),
			21 => array('extra_name' => 'PCT', 'test_comment' => '0.08', 'test_unit' => '%', 'normal_value' => ''),
			22 => array('extra_name' => 'MPV', 'test_comment' => '0.08', 'test_unit' => 'fL', 'normal_value' => '[8.0 - 15.0]'),
			23 => array('extra_name' => 'PDWsd', 'test_comment' => '3.30', 'test_unit' => 'fL', 'normal_value' => ''),
			24 => array('extra_name' => 'PDWcv', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => ''),
			25 => array('extra_name' => 'PLCR', 'test_comment' => '0.40', 'test_unit' => '%', 'normal_value' => ''),
			26 => array('extra_name' => 'PLCC', 'test_comment' => '0.08', 'test_unit' => '10³/uL', 'normal_value' => '')
		);

		$fbc_pd = array(
			1 => array('extra_name' => 'WBC', 'test_comment' => '6.30', 'test_unit' => '10³/uL', 'normal_value' => '[5.00 - 19.00]'),
			2 => array('extra_name' => 'NEU', 'test_comment' => '3.30', 'test_unit' => '10³/uL', 'normal_value' => '[1.00 - 8.50]'),
			3 => array('extra_name' => 'LYM', 'test_comment' => '2.39', 'test_unit' => '10³/uL', 'normal_value' => '[2.30 - 14.40]'),
			4 => array('extra_name' => 'MON', 'test_comment' => '0.40', 'test_unit' => '10³/uL', 'normal_value' => '[0.0 - 0.95]'),
			5 => array('extra_name' => 'EO', 'test_comment' => '0.08', 'test_unit' => '10³/uL', 'normal_value' => '[0.0 - 0.50]'),
			6 => array('extra_name' => 'BAS', 'test_comment' => '3.30', 'test_unit' => '10³/uL', 'normal_value' => '[0.00 - 0.15]'),
			7 => array('extra_name' => 'NEU%', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => '[20.0 - 45.0]'),
			8 => array('extra_name' => 'LYM%', 'test_comment' => '0.40', 'test_unit' => '%', 'normal_value' => '[46.0 - 76.00]'),
			9 => array('extra_name' => 'MON%', 'test_comment' => '0.08', 'test_unit' => '%', 'normal_value' => '[0.0 - 5.00]'),
			10 => array('extra_name' => 'EO%', 'test_comment' => '3.30', 'test_unit' => '%', 'normal_value' => '[0.0 - 5.0]'),
			11 => array('extra_name' => 'BAS%', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => '[0.0 - 1.5]'),

			12 => array('extra_name' => 'RBC', 'test_comment' => '0.40', 'test_unit' => '10⁶/uL', 'normal_value' => '[3.90 - 5.30]'),
			13 => array('extra_name' => 'HGB', 'test_comment' => '0.08', 'test_unit' => 'g/dL', 'normal_value' => '[9.5 - 14.1]'),
			14 => array('extra_name' => 'HCT', 'test_comment' => '3.30', 'test_unit' => '%', 'normal_value' => '[30.0 - 40.0]'),
			15 => array('extra_name' => 'MCV', 'test_comment' => '2.39', 'test_unit' => 'fL', 'normal_value' => '[70.0 - 84.0]'),
			16 => array('extra_name' => 'MCH', 'test_comment' => '0.40', 'test_unit' => 'pg', 'normal_value' => '[23.0 - 29.0]'),
			17 => array('extra_name' => 'MCHC', 'test_comment' => '0.08', 'test_unit' => 'g/dL', 'normal_value' => '[31.0 - 35.0]'),
			18 => array('extra_name' => 'RDWsd', 'test_comment' => '3.30', 'test_unit' => 'fL', 'normal_value' => '[46.0 - 59.0]'),
			19 => array('extra_name' => 'RDWcv', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => ''),
			20 => array('extra_name' => 'PLT', 'test_comment' => '0.40', 'test_unit' => '10³/uL', 'normal_value' => '[150 - 400]'),
			21 => array('extra_name' => 'PCT', 'test_comment' => '0.08', 'test_unit' => '%', 'normal_value' => ''),
			22 => array('extra_name' => 'MPV', 'test_comment' => '0.08', 'test_unit' => 'fL', 'normal_value' => '[8.0 - 15.0]'),
			23 => array('extra_name' => 'PDWsd', 'test_comment' => '3.30', 'test_unit' => 'fL', 'normal_value' => ''),
			24 => array('extra_name' => 'PDWcv', 'test_comment' => '2.39', 'test_unit' => '%', 'normal_value' => ''),
			25 => array('extra_name' => 'PLCR', 'test_comment' => '0.40', 'test_unit' => '%', 'normal_value' => ''),
			26 => array('extra_name' => 'PLCC', 'test_comment' => '0.08', 'test_unit' => '10³/uL', 'normal_value' => '')
		);

		$liver = array(
			array('test_comment' => 313, 'extra_name' => 'Aspartate Transaminase(AST)', 'o_name' => '', 'h_name' => 'Male: <35 U/L,<br> Female: <31 U/L'),
			array('test_comment' => 314, 'extra_name' => 'Alanine Transaminase(ALT)', 'o_name' => '', 'h_name' => 'Male: <45 U/L,<br> Female: <34 U/L'),
			array('test_comment' => 315, 'extra_name' => 'Alkaline Phosphatase(ALP)', 'o_name' => '', 'h_name' => '<258 U/L'),
			array('test_comment' => 316, 'extra_name' => 'Total Bilirubin', 'o_name' => '', 'h_name' => 'Adult: 1.7 - 21 umol/L'),
			array('test_comment' => 317, 'extra_name' => 'Direct Bilirubin', 'o_name' => '', 'h_name' => 'Adult: &#8804; 3.4 umol/L'),
			array('test_comment' => 318, 'extra_name' => 'Albumin', 'o_name' => '', 'h_name' => '35 - 52 g/L'),
			array('test_comment' => 320, 'extra_name' => 'Total Protein', 'o_name' => '', 'h_name' => '6.6 - 8.8 g/L')
		);
		$lipid = array(
			array('test_comment' => 325, 'extra_name' => 'Total Cholesterol', 'o_name' => '', 'h_name' => '&le; 200 mg/dL'),
			array('test_comment' => 326, 'extra_name' => 'HDL Cholesterol', 'o_name' => '', 'h_name' => '&ge; 35 mg/dL'),
			array('test_comment' => 327, 'extra_name' => 'LDL Cholesterol', 'o_name' => '', 'h_name' => '&le; 130 mg/dL'),
			array('test_comment' => 328, 'extra_name' => 'Triglyceride', 'o_name' => '', 'h_name' => '< 200 mg/dL'),

		);
		$renal = array(
			array('test_comment' => 301, 'extra_name' => 'Urea', 'o_name' => '', 'h_name' => '17 - 43 mg/dL'),
			array('test_comment' => 302, 'extra_name' => 'Creatinine', 'o_name' => '', 'h_name' => 'Male: 62 - 115 umol/dL,<br> Female: 53 - 97 umol/dL'),
			array('test_comment' => 303, 'extra_name' => 'Sodium', 'o_name' => '', 'h_name' => '135 - 145 mmol/L'),
			array('test_comment' => 304, 'extra_name' => 'Potassium', 'o_name' => '', 'h_name' => '3.5 - 5.5 mmol/L'),
			array('test_comment' => 305, 'extra_name' => 'Bicarbonate', 'o_name' => '', 'h_name' => '21 - 28 mmol/L'),
			array('test_comment' => 306, 'extra_name' => 'Chloride', 'o_name' => '', 'h_name' => '98 - 108 mmol/L'),
			array('test_comment' => 310, 'extra_name' => 'Calcium', 'o_name' => '', 'h_name' => '2.2 - 2.9 mmol/L'),
			array('test_comment' => 311, 'extra_name' => 'Uric Acid', 'o_name' => '', 'h_name' => 'Male: 3.5 - 7.2 mg/dL,<br> Female: 2.6 - 6.0 mg/dL')

		);

		$cart_check = $this->sale_lib->get_lab_resultcart();
		foreach ($cart_check as $row => $value) {
			if ($value['test_name'] == 'Full Blood Count(Pediatrics)') {
				foreach ($fbc_pd as $fbc_row => $fbc_value) {
					$this->sale_lib->item_add_result($value['item_id'], $value['test_name'], $test_comment = '', $value['line'], $fbc_value['extra_name'], $fbc_value['test_unit'], $fbc_value['normal_value']);
				}
			} elseif ($value['test_name'] == 'Full Blood Count') {
				foreach ($fbc as $fbc_row => $fbc_value) {
					$this->sale_lib->item_add_result($value['item_id'], $value['test_name'], $test_comment = '', $value['line'], $fbc_value['extra_name'], $fbc_value['test_unit'], $fbc_value['normal_value']);
				}
			} elseif ($value['test_name'] == 'Liver Function Test') {
				foreach ($liver as $liver_row => $liver_value) {
					$this->sale_lib->item_add_result($value['item_id'], $value['test_name'], $liver_value['test_comment'], $value['line'], $liver_value['extra_name'], $liver_value['o_name'], $liver_value['h_name']);
				}
			} elseif ($value['test_name'] == 'Lipid Profile Test') {
				foreach ($lipid as $lipid_row => $lipid_value) {
					$this->sale_lib->item_add_result($value['item_id'], $value['test_name'], $lipid_value['test_comment'], $value['line'], $lipid_value['extra_name'], $lipid_value['o_name'], $lipid_value['h_name']);
				}
			} elseif ($value['test_name'] == 'Renal Function Test') {
				foreach ($renal as $renal_row => $renal_value) {
					$this->sale_lib->item_add_result($value['item_id'], $value['test_name'], $renal_value['test_comment'], $value['line'], $renal_value['extra_name'], $renal_value['o_name'], $renal_value['h_name']);
				}
			}
		}
		$data['cart'] = $this->sale_lib->get_lab_resultcart();
		$data['invoice_details'] = $invoice_details;

		//$customer_id=23;
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		$this->load->view('laboratory/check', $data);
	}
	public function delete_test_item($item_number)
	{
		$this->sale_lib->delete_item_test($item_number);

		$data['cart'] = $this->sale_lib->get_lab_resultcart();
		$this->load->view('laboratory/check', $data);
	}

	public function edit_item($line_id)
	{

		$data = array();

		$test_comment = $this->input->post('test_comment');
		$test_name = $this->input->post('test_name');
		$extra_name = $this->input->post('extra_name');
		$o_name = $this->input->post('o_name');
		$h_name = $this->input->post('h_name');
		//$this->sale_lib->edit_result($item_id, $test_comment);

		// echo '<pre>';
		// print_r($this->sale_lib->get_lab_resultcart());
		// die;
		$batch_id = $this->input->post('batch') == '' ? 0 : $this->input->post('batch');
		$reference = $this->input->post('reference');
		$line = $this->input->post('line');
		$item_id = $this->input->post('item_id');

		// $m = 'Line: ' . $line . ', Ref: '. $reference . ', ItemID: ' . $item_id . ', BatchID: ' . $batch_id . ', Test Comment: '. $test_comment . ', Test Name: '. $test_name . ', Extra NAme: '. $extra_name . ', O Name: ' . $o_name . ', Hname: ' . $h_name;

		// return print_r($m);
		// die();


		if ($batch_id == 0 && $reference == 0) {
			//$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_result($line, $test_name, $test_comment, $extra_name, $o_name, $h_name);
			//$this->sale_lib->edit_pushed_items($line, $quantity,$batch_no=0,$expiry=null);
		}
		if (($reference == 0) && ($batch_id != 0)) {

			for ($i = 0; $i < $batch_id; $i++) {
				$this->sale_lib->item_add_result($item_id, $test_name, $test_comment, $line, $extra_name, $o_name, $h_name);
			}
		}

		if ($reference != 0 && $batch_id == 0) {
			//this is done by ajax now
			///$this->sale_lib->edit_result($line, $test_name, $test_comment, $extra_name, $o_name, $h_name);
		}
		$data['batch_id'] = $batch_id;
		$data['extra_name'] = $extra_name;
		$data['reference'] = $reference;
		$data['line'] = $line;
		$data['no_of_batch'] = $no_of_batch;
		$data['received_quantity'] = $received_quantity;
		$data['cart'] = $this->sale_lib->get_lab_resultcart();
		$this->load->view('laboratory/check', $data);
	}

	public function checkcart(){
		$items = $this->sale_lib->get_lab_resultcart();
		print "<pre>";
		print_r($items);
		print "</pre>";
	}
	public function edit_item_ajax()

	{
		$items = $this->sale_lib->get_lab_resultcart();

		
		$ret = array('status' => false);

		$ret['status'] = true;
		$ret['message'] = 'Done';

		$tests = $this->input->post('tests');

		// echo json_encode($tests);

		foreach ($tests as $le => $each_line) {

			$line = $each_line['line'];

			$ref = $each_line['reference'];;

			$test_comment = $each_line['test_comment'];;

			$extra_name = $each_line['extra_name'];;
			$o_name = $each_line['o_name'];;
			$h_name = $each_line['h_name'];;
			$let = (int) $each_line['let'];;

			if (isset($items[$line])) {
				$line = $items[$let];
				$line['test_comment'] = $test_comment;
				$line['extra_name'] = $extra_name;
				$line['o_name'] = $o_name;
				$line['h_name'] = $h_name;
				$items[$let] = $line;
			} else {
				$ret['status'] = false;
				$ret['message'] = "some not found ";
			}
		}
		$this->sale_lib->set_lab_resultcart($items);

		//set_lab_resultcart

		//echo json_encode($this->sale_lib->get_lab_resultcart());
		// $check = array();
		// $check['first'] = $tests;
		// $check['second'] = $this->sale_lib->get_lab_resultcart();
		echo json_encode($ret);
		//$this->load->view('laboratory/check', $data);
	}
	public function edit_item_ajax_old()

	{
		$line = $this->input->post('line');

		$ref = $this->input->post('reference');



		$test_comment = $this->input->post('test_comment');

		$extra_name = $this->input->post('extra_name');
		$o_name = $this->input->post('o_name');
		$h_name = $this->input->post('h_name');
		$let = $this->input->post('let');





		$ret = array('status' => false);
		$found = false;

		$items = $this->sale_lib->get_lab_resultcart();



		if (isset($items[$let])) {
			$line = &$items[$let];
			$line['test_comment'] = $test_comment;
			$line['extra_name'] = $extra_name;
			$line['o_name'] = $o_name;
			$line['h_name'] = $h_name;
			$found = true;


			$this->sale_lib->set_lab_resultcart($items);
			$ret['status'] = true;
			$ret['message'] = 'Done';
		} else {
			$ret['status'] = false;
			$ret['message'] = "Test not found " . $line;
		}

		//set_lab_resultcart

		echo json_encode($this->sale_lib->get_lab_resultcart());
		//$this->load->view('laboratory/check', $data);
	}

	public function get_filtered($cart)
	{
		$filtered_cart = array();
		foreach ($cart as $id => $item) {
			if ($item['print_option'] == '0') // always include
			{
				$filtered_cart[$id] = $item;
			} elseif ($item['print_option'] == '1' && $item['price'] != 0)  // include only if the price is not zero
			{
				$filtered_cart[$id] = $item;
			}
			// print_option 2 is never included
		}

		return $filtered_cart;
	}
	public function laboratory_items()
	{
		$lab_items = array();
		$data = $this->Item->account_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' => $value['status'] == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>', 'print' => '<button class="btn btn-info btn-sm pull-left modal-dlg"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}
	public function unprocessed_laboratory_items()
	{
		$lab_items = array();
		$data = $this->Item->unprocessed_account_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' => $value['status'] == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>', 'print' => '<button class="btn btn-info btn-sm pull-left modal-dlg"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}
	public function processed_laboratory_items()
	{
		$lab_items = array();
		$data = $this->Item->processed_account_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' => $value['status'] == 0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>' : '<button class="btn btn-info btn-sm pull-left modal-dlg" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>', 'print' => '<button class="btn btn-info btn-sm pull-left modal-dlg"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}
	public function new_result_items()
	{
		$lab_items = array();
		$data = $this->Item->new_result_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['customer_id'], $data); //the $data array is filled with the customer name in this method
			$lab_items[] = array('sale_id' => $value['sale_id'], 'invoice_id' => $value['invoice_id'], 'customer_id' => $data['customer'], 'result_time' => $value['result_time'], 'doctor_name' => $value['doctor_name'], 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}
	public function pending_result_items()
	{
		$lab_items = array();
		$data = $this->Item->pending_result_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['customer_id'], $data);
			$lab_items[] = array('sale_id' => $value['sale_id'], 'invoice_id' => $value['invoice_id'], 'customer_id' => $data['customer'], 'result_time' => $value['result_time'], 'doctor_name' => $value['doctor_name'], 'edit' => '<button class="btn btn-primary btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Continue</button>', 'print' => '');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}
	public function completed_result_items()
	{
		$lab_items = array();
		$data = $this->Item->completed_result_items();
		foreach ($data as $row => $value) {
			$customer_info = $this->_load_customer_data($value['customer_id'], $data);
			$lab_items[] = array('sale_id' => $value['sale_id'], 'invoice_id' => $value['invoice_id'], 'customer_id' => $data['customer'], 'result_time' => $value['result_time'], 'doctor_name' => $value['doctor_name'], 'edit' => '<button class="btn btn-success btn-sm pull-left modal-dlg update" id="' . $value['sale_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>',  'print' => '');
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($lab_items),
			"iTotalDisplayRecords" => count($lab_items),
			"aaData" => $lab_items
		);

		echo json_encode($results);
	}



	public function add_payment()
	{
		$data = array();

		$payment_type = $this->input->post('payment_type');
		if ($payment_type != $this->lang->line('sales_giftcard')) {
			$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required|callback_numeric');
		} else {
			$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required');
		}
		if ($this->form_validation->run() == FALSE) {
			if ($payment_type == $this->lang->line('sales_giftcard')) {
				$data['error'] = $this->lang->line('sales_must_enter_numeric_giftcard');
			} else {
				$data['error'] = $this->lang->line('sales_must_enter_numeric');
			}
		} else {
			if ($payment_type == $this->lang->line('sales_giftcard')) {
				// in case of giftcard payment the register input amount_tendered becomes the giftcard number
				$giftcard_num = $this->input->post('amount_tendered');

				$payments = $this->sale_lib->get_payments();
				$payment_type = $payment_type . ':' . $giftcard_num;
				$current_payments_with_giftcard = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value($giftcard_num);
				$cur_giftcard_customer = $this->Giftcard->get_giftcard_customer($giftcard_num);
				$customer_id = $this->sale_lib->get_customer();
				if (isset($cur_giftcard_customer) && $cur_giftcard_customer != $customer_id) {
					$data['error'] = $this->lang->line('giftcards_cannot_use', $giftcard_num);
				} elseif (($cur_giftcard_value - $current_payments_with_giftcard) <= 0) {
					$data['error'] = $this->lang->line('giftcards_remaining_balance', $giftcard_num, to_currency($cur_giftcard_value));
				} else {
					$new_giftcard_value = $this->Giftcard->get_giftcard_value($giftcard_num) - $this->sale_lib->get_amount_due();
					$new_giftcard_value = $new_giftcard_value >= 0 ? $new_giftcard_value : 0;
					$this->sale_lib->set_giftcard_remainder($new_giftcard_value);
					$new_giftcard_value = str_replace('$', '\$', to_currency($new_giftcard_value));
					$data['warning'] = $this->lang->line('giftcards_remaining_balance', $giftcard_num, $new_giftcard_value);
					$amount_tendered = min($this->sale_lib->get_amount_due(), $this->Giftcard->get_giftcard_value($giftcard_num));

					$this->sale_lib->add_payment($payment_type, $amount_tendered);
				}
			} elseif ($payment_type == $this->lang->line('sales_rewards')) {
				$customer_id = $this->sale_lib->get_customer();
				$package_id = $this->Customer->get_info($customer_id)->package_id;
				if (!empty($package_id)) {
					$package_name = $this->Customer_rewards->get_name($package_id);
					$points = $this->Customer->get_info($customer_id)->points;
					$points = ($points == NULL ? 0 : $points);

					$payments = $this->sale_lib->get_payments();
					$payment_type = $payment_type;
					$current_payments_with_rewards = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
					$cur_rewards_value = $points;

					if (($cur_rewards_value - $current_payments_with_rewards) <= 0) {
						$data['error'] = $this->lang->line('rewards_remaining_balance') . to_currency($cur_rewards_value);
					} else {
						$new_reward_value = $points - $this->sale_lib->get_amount_due();
						$new_reward_value = $new_reward_value >= 0 ? $new_reward_value : 0;
						$this->sale_lib->set_rewards_remainder($new_reward_value);
						$new_reward_value = str_replace('$', '\$', to_currency($new_reward_value));
						$data['warning'] = $this->lang->line('rewards_remaining_balance') . $new_reward_value;
						$amount_tendered = min($this->sale_lib->get_amount_due(), $points);

						$this->sale_lib->add_payment($payment_type, $amount_tendered);
					}
				}
			} else {
				$amount_tendered = $this->input->post('amount_tendered');
				$this->sale_lib->add_payment($payment_type, $amount_tendered);
			}
		}

		$this->_reload($data);
	}
	public function lab_account_none()
	// public function lab_account_sales()
	{
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['cart'] = $this->sale_lib->get_cart();
		$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);

		if ($this->config->item('invoice_enable') == '0') {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'return' => $this->lang->line('sales_return')
			);
		} else {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'sale_invoice' => $this->lang->line('sales_sale_by_invoice'),
				'sale_quote' => $this->lang->line('sales_quote'),
				'return' => $this->lang->line('sales_return')
			);
		}
		$data['mode'] = $this->sale_lib->get_mode();
		$data['empty_tables'] = $this->sale_lib->get_empty_tables();
		$data['selected_table'] = $this->sale_lib->get_dinner_table();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['discounted_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		if ($data['cash_rounding']) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		if ($customer_info && $this->config->item('customer_reward_enable') == TRUE) {
			$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);
		} else {
			$data['payment_options'] = $this->Sale->get_payment_options();
		}
		$quote_number = $this->sale_lib->get_quote_number();
		if ($quote_number != NULL) {
			$data['quote_number'] = $quote_number;
		}

		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);

		$invoice_format = $this->config->item('sales_invoice_format');
		$data['invoice_format'] = $invoice_format;

		$this->set_invoice_number($invoice_format);
		$data['invoice_number'] = $invoice_format;

		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['quote_or_invoice_mode'] = $data['mode'] == 'sale_invoice' || $data['mode'] == 'sale_quote';
		$data['sales_or_return_mode'] = $data['mode'] == 'sale' || $data['mode'] == 'return';
		if ($this->sale_lib->get_mode() == 'sale_invoice') {
			$data['mode_label'] = $this->lang->line('sales_invoice');
		} elseif ($this->sale_lib->get_mode() == 'sale_quote') {
			$data['mode_label'] = $this->lang->line('sales_quote');
		} else {
			$data['mode_label'] = $this->lang->line('sales_receipt');
		}
		$data = $this->xss_clean($data);

		$this->load->view("sales/register", $data);
	}


	public function lab_sales()
	{
		$this->load->view('laboratory/manage', $data);
	}
	public function lab_account()
	{
		$data['notice'] = $this->sale_lib->notice_transfer_items();
		$data['table_headers'] = $this->xss_clean(get_laboratory_manage_table_headers());
		$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array(
			'empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'is_serialized' => $this->lang->line('items_serialized_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'search_custom' => $this->lang->line('items_search_custom_items'),
			'is_deleted' => $this->lang->line('items_is_deleted')
		);

		$this->load->view('laboratory/accounts', $data);
	}
    public function update_invoice($inv_id = null){
        if(!$inv_id){
            redirect(base_url('laboratory/test_start'));
        }
//        $grants_data = $this->input->post('grants[]') != NULL ? $this->input->post('grants[]') : array();
//        $this->sale_lib->empty_labcart();
//        $data['grants_data'] = $grants_data;
//        $this->sale_lib->add_lab_cart($grants_data);
//        $data['cart'] = $this->sale_lib->get_labcart();
////        $data['cart'] = $this->input->post('grants[]') != NULL ? $this->input->post('grants[]') : array();
//        $customer_id = $this->sale_lib->get_customer();
//        if ($customer_id == -1 || $customer_id === -1 || $customer_id == "-1" || $customer_id <= 0) {
//            //go back to the new test page with error
//            $data['error'] = "You must select a customer or create a new customer before you proceed";
//            $this->_reload($data);
//            exit();
////			return;
//        }
//        $customer_info = $this->_load_customer_data($customer_id, $data, TRUE);

        $this->Sale->delete_lab_invoice_items($inv_id);
        $this->lab_cart();
//        redirect(base_url("laboratory/edit_invoice/$inv_id"));
    }
	public function lab_cart()
	{

		$data = array();
		//$data['ano'] = "Hello World";
		$test_type = $this->input->post('btnGetValue');
		$test_type = 1;
		$grants_data = $this->input->post('grants[]') != NULL ? $this->input->post('grants[]') : array();
		//clear lab cart here
		$this->sale_lib->empty_labcart();
		$this->sale_lib->add_lab_cart($grants_data);
		$data['grants_data'] = $grants_data;
		//$this->load->view('laboratory/test_sales', $data);
		//$item_inf = $this->CI->Item->global_search;

		//$data['btnGetValue'] = $btnGetValue;
		//$data['ano'] = "Hello World";
		$data['cart'] = $this->sale_lib->get_labcart();
		$customer_id = $this->sale_lib->get_customer();
//        var_dump("I got here $customer_id");
//        exit();
		if ($customer_id == -1 || $customer_id === -1 || $customer_id == "-1" || $customer_id <= 0) {
			//go back to the new test page with error
			$data['error'] = "You must select a customer or create a new customer before you proceed";
			$this->_reload($data);
			exit();
//			return;
		}

		$customer_info = $this->_load_customer_data($customer_id, $data, TRUE);

		if ($this->config->item('invoice_enable') == '0') {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'return' => $this->lang->line('sales_return')
			);
		} else {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'sale_invoice' => $this->lang->line('sales_sale_by_invoice'),
				'sale_quote' => $this->lang->line('sales_quote'),
				'return' => $this->lang->line('sales_return')
			);
		}
		$data['mode'] = $this->sale_lib->get_mode();
		$data['empty_tables'] = $this->sale_lib->get_empty_tables();
		$data['selected_table'] = $this->sale_lib->get_dinner_table();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);

		// $data['taxes'] = $this->sale_lib->get_taxes();
		// $data['discount'] = $this->sale_lib->get_discount();
		// $data['payments'] = $this->sale_lib->get_payments();
		//JUDE changed this

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'




		$invoice_format = $this->config->item('sales_invoice_format');
		$data['invoice_format'] = $invoice_format;

		// $data['cart'] = $this->sale_lib->get_labcart(); //JUDE changed this
		//$lab_items=$data['cart'];
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		//$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['invoice_number_enabled'] = $this->sale_lib->set_invoice_number_enabled(TRUE);
		$data['cur_giftcard_value'] = $this->sale_lib->get_giftcard_remainder();
		$data['cur_rewards_value'] = $this->sale_lib->get_rewards_remainder();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();

 	///////////////////////////////////////////////////////////////////

		// $customer_id = $this->sale_lib->get_customer();
		// if ($customer_id == -1 || $customer_id === -1 || $customer_id == "-1" || $customer_id <= 0) {
		// 	//go back to the new test page with error
		// 	$data['error'] = "You must select a customer or create a new customer before you proceed";
		// 	$this->_reload($data);
		// 	exit();
		// }
		// $customer_info = $this->_load_customer_data($customer_id, $data);

	///////////////////////////////////////////////////////////////////

		$data["invoice_number"] = $this->sale_lib->get_invoice_number();
		$data["quote_number"] = $this->sale_lib->get_quote_number();

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();


		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals_lab();
		$data['subtotal'] = $totals['discounted_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		$data['discounted_subtotal'] = $totals['discounted_subtotal'];
		$data['tax_exclusive_subtotal'] = $totals['tax_exclusive_subtotal'];

		if ($data['cash_rounding']) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;


		// generate final invoice number (if using the invoice in sales by receipt mode then the invoice number can be manually entered or altered in some way
		if ($this->sale_lib->is_sale_by_receipt_mode()) {
			$this->sale_lib->set_invoice_number($this->input->post('invoice_number'), $keep_custom = TRUE);
			$invoice_format = $this->sale_lib->get_invoice_number();
			//$invoice_format = true;
			if (empty($invoice_format)) {
				$invoice_format = $this->config->item('sales_invoice_format');
			}
		} else {
			$invoice_format = $this->config->item('sales_invoice_format');
		}
		$invoice_number = $this->token_lib->render($invoice_format);

		// TODO If duplicate invoice then determine the number of employees and repeat until until success or tried the number of employees (if QSEQ was used).
		if ($this->Sale->check_invoice_number_exists($invoice_number)) {
			$data['error'] = $this->lang->line('sales_invoice_number_duplicate');
			$this->_reload($data);
			exit();
		} else {
			$data['invoice_number'] = $invoice_number;
			$data['sale_status'] = '0'; // Complete

			// Save the data to the sales table
			//$data['sale_id']=3;
			$data['sale_id'] = $data['invoice_number'];

			// $data['invoice_id'] = $this->Sale->save_laboratory_invoice($data['cart'], $test_type, $customer_id, $invoice_id);
			$data['invoice_id'] = $this->Sale->save_laboratory_invoice($data['cart'], $test_type, $customer_id, $invoice_id);
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
			$this->load->view('laboratory/invoice', $data);
			$this->sale_lib->clear_all_lab();
		}
	}
	public function complete()
	{
		$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['cart'] = $this->sale_lib->get_labcart();
		//$lab_items=$data['cart'];
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		//$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['invoice_number_enabled'] = $this->sale_lib->set_invoice_number_enabled(TRUE);
		$data['cur_giftcard_value'] = $this->sale_lib->get_giftcard_remainder();
		$data['cur_rewards_value'] = $this->sale_lib->get_rewards_remainder();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$customer_id = $this->sale_lib->get_customer();
		$data["invoice_number"] = $this->sale_lib->get_invoice_number();
		$data["quote_number"] = $this->sale_lib->get_quote_number();
		$customer_info = $this->_load_customer_data($customer_id, $data);

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();


		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals_lab();
		$data['subtotal'] = $totals['discounted_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		$data['discounted_subtotal'] = $totals['discounted_subtotal'];
		$data['tax_exclusive_subtotal'] = $totals['tax_exclusive_subtotal'];

		if ($data['cash_rounding']) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;


		// generate final invoice number (if using the invoice in sales by receipt mode then the invoice number can be manually entered or altered in some way
		if ($this->sale_lib->is_sale_by_receipt_mode()) {
			$this->sale_lib->set_invoice_number($this->input->post('invoice_number'), $keep_custom = TRUE);
			$invoice_format = $this->sale_lib->get_invoice_number();
			//$invoice_format = true;
			if (empty($invoice_format)) {
				$invoice_format = $this->config->item('sales_invoice_format');
			}
		} else {
			$invoice_format = $this->config->item('sales_invoice_format');
		}
		$invoice_number = $this->token_lib->render($invoice_format);

		// TODO If duplicate invoice then determine the number of employees and repeat until until success or tried the number of employees (if QSEQ was used).
		if ($this->Sale->check_invoice_number_exists($invoice_number)) {
			$data['error'] = $this->lang->line('sales_invoice_number_duplicate');
			$this->_reload($data);
		} else {
			$data['invoice_number'] = $invoice_number;
			$data['sale_status'] = '0'; // Complete

			// Save the data to the sales table
			$data['sale_id'] = 3;

			$data['invoice_id'] = $this->Sale->save_laboratory_invoice($data['cart'], $customer_id, $invoice_id);
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
			$this->load->view('laboratory/invoice', $data);
			$this->sale_lib->clear_all_lab();
		}
	}


	/*
	Returns Items table data rows. This will be called with AJAX.
	*/
	public function lab_save($item_id = -1)
	{
		$test_code = $this->input->post('test_code');
		$test_name = $this->input->post('test_name');
		$test_amount = $this->input->post('test_amount');
		$test_type = $this->input->post('test_type');
		$test_unit = $this->input->post('test_unit');
		$test_subgroup = $this->input->post('test_subgroup');
		$test_kind = $this->input->post('test_kind');
		//Save item data





		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		//$cur_item_info = $this->Item->get_info($item_id);

		if ($this->Item->lab_save($test_code, $test_name, $test_amount, $test_type, $test_unit, $test_subgroup, $test_kind, $item_id)) {
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if ($item_id == -1) {
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}

			//$items_taxes_data = array();
			//$tax_names = $this->input->post('tax_names');
			//$tax_percents = $this->input->post('tax_percents');

			//Save item quantity
			$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();


			if ($success) {
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			} else {
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		} else //failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}

		//$data['laboratory_items']=$this->Item->laboratory_items();
		//$this->load->view('laboratory/tests');
		//$this->tests();
	}


	public function global_item_push_transfer()
	{
		$data = array();
		$push = array();
		$unique_location = array();

		$push = $this->sale_lib->get_push();
		$transfer_location = $this->sale_lib->get_sale_location();
		foreach ($push as $row => $value) {
			array_push($data, $value['branch_transfer']);
			//$check[]=$value['location'];
		}
		$unique_location = array_unique($data);
		foreach ($unique_location as $row => $value) {

			$transfer_data = array();
			$items = 0;
			$transfer_type = 'PUSH';

			$transfer_branch = $value;
			$request_branch = $transfer_location;
			$status = 0;
			$this->Sale->save_transfer($push, $items, $transfer_type, $request_branch, $transfer_branch, $status, $transfer_id);
		}


		/*$data['sale_id_num'] = $this->Sale->save_transfer($items,$quantity,$request_branch,$transfer_branch,$status, $transfer_id);
				
				$data = $this->xss_clean($data);

				if($data['sale_id_num'] == -1)
				{
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				}*/



		/*if($this->Sale->save_transfer($items,$request_branch,$transfer_branch,$status, $transfer_id))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}*/
		$this->sale_lib->clear_all_push();
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}

	private function _reload($data = array())
	{
		$laboratory_test = $this->Item->get_lab_items();
		$data['laboratory_test'] = $laboratory_test;
		$gender = array(1 => 'Male', 2 => 'Female');
		$data['gender'] = $gender;

		// filters that will be loaded in the multiselect dropdown

		//$this->sale_lib->empty_cart();


		//$item_inf = $this->CI->Item->global_search;
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['cart'] = $this->sale_lib->get_labcart();
		$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);

		if ($this->config->item('invoice_enable') == '0') {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'return' => $this->lang->line('sales_return')
			);
		} else {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				'sale_invoice' => $this->lang->line('sales_sale_by_invoice'),
				'sale_quote' => $this->lang->line('sales_quote'),
				'return' => $this->lang->line('sales_return')
			);
		}
		$data['mode'] = $this->sale_lib->get_mode();
		$data['empty_tables'] = $this->sale_lib->get_empty_tables();
		$data['selected_table'] = $this->sale_lib->get_dinner_table();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['discounted_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		if ($data['cash_rounding']) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		if ($customer_info && $this->config->item('customer_reward_enable') == TRUE) {
			$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);
		} else {
			$data['payment_options'] = $this->Sale->get_payment_options();
		}
		$quote_number = $this->sale_lib->get_quote_number();
		if ($quote_number != NULL) {
			$data['quote_number'] = $quote_number;
		}

		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);

		$invoice_format = $this->config->item('sales_invoice_format');
		$data['invoice_format'] = $invoice_format;

		$this->set_invoice_number($invoice_format);
		$data['invoice_number'] = $invoice_format;

		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['quote_or_invoice_mode'] = $data['mode'] == 'sale_invoice' || $data['mode'] == 'sale_quote';
		$data['sales_or_return_mode'] = $data['mode'] == 'sale' || $data['mode'] == 'return';
		if ($this->sale_lib->get_mode() == 'sale_invoice') {
			$data['mode_label'] = $this->lang->line('sales_invoice');
		} elseif ($this->sale_lib->get_mode() == 'sale_quote') {
			$data['mode_label'] = $this->lang->line('sales_quote');
		} else {
			$data['mode_label'] = $this->lang->line('sales_receipt');
		}
		$data = $this->xss_clean($data);

		$this->load->view('laboratory/manage', $data);
	}


	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$suppliers = $this->Supplier->search_Lab($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Supplier->get_found_rows_lab($search);

		$data_rows = array();
		foreach ($suppliers->result() as $supplier) {
			$data_rows[] = get_laboratory_data_row($supplier, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function pic_thumb($pic_filename)
	{
		$this->load->helper('file');
		$this->load->library('image_lib');

		// in this context, $pic_filename always has .ext
		$ext = pathinfo($pic_filename, PATHINFO_EXTENSION);
		$images = glob('./uploads/item_pics/' . $pic_filename);

		// make sure we pick only the file name, without extension
		$base_path = './uploads/item_pics/' . pathinfo($pic_filename, PATHINFO_FILENAME);
		if (sizeof($images) > 0) {
			$image_path = $images[0];
			$thumb_path = $base_path . $this->image_lib->thumb_marker . '.' . $ext;
			if (sizeof($images) < 2) {
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;
				$this->image_lib->initialize($config);
				$image = $this->image_lib->resize();
				$thumb_path = $this->image_lib->full_dst_path;
			}
			$this->output->set_content_type(get_mime_by_extension($thumb_path));
			$this->output->set_output(file_get_contents($thumb_path));
		}
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions(
			$this->input->post_get('term'),
			array('search_custom' => $this->input->post('search_custom'), 'is_deleted' => $this->input->post('is_deleted') != NULL),
			FALSE
		));

		echo json_encode($suggestions);
	}

	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions(
			$this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE),
			TRUE
		));

		echo json_encode($suggestions);
	}

	public function complete_push()
	{
		/*$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['push'] = $this->sale_lib->get_push();
		
		$data['sale_id_num'] = $this->Sale->save($data['push']);
		$data['sale_id'] = 'POS ' . $data['sale_id_num'];

				// Resort and filter cart lines for printing
		//$data['push'] = $this->sale_lib->sort_and_filter_cart($data['push']);

		$data = $this->xss_clean($data);
		if($data['sale_id_num'] == -1)
		{
			$data['error_message'] = $this->lang->line('sales_transaction_failed');
		}
		
		*/
	}


	public function suggest_kits()
	{
		$suggestions = $this->xss_clean($this->Item->get_kit_search_suggestions(
			$this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE),
			TRUE
		));

		echo json_encode($suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_category()
	{
		$suggestions = $this->xss_clean($this->Item->get_category_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest_location()
	{
		$suggestions = $this->xss_clean($this->Item->get_location_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest_custom()
	{
		$suggestions = $this->xss_clean($this->Item->get_custom_suggestions($this->input->post('term'), $this->input->post('field_no')));

		echo json_encode($suggestions);
	}

	public function get_row($item_ids)
	{
		$item_infos = $this->Item->get_multiple_info(explode(":", $item_ids), $this->item_lib->get_item_location());

		$result = array();
		foreach ($item_infos->result() as $item_info) {
			$result[$item_info->item_id] = $this->xss_clean(get_item_data_row($item_info, $this));
		}

		echo json_encode($result);
	}

	public function view($item_id = -1)
	{

		$test_info = $this->Item->labget_info($item_id);
		foreach (get_object_vars($test_info) as $property => $value) {
			$test_info->$property = $this->xss_clean($value);
		}

		if ($item_id == -1) {
		}
		//$laboratory_cat=array('none'=>'Choose Category','microbiology'=>'MICROBIOLOGY','chemistry'=>'CHEMISTRY','M/C/S'=>'M/C/S');
		$data['laboratory_cat'] = $laboratory_cat;
		$test_categories = array('' => $this->lang->line('items_none'));
		foreach ($this->Item->category_get_all()->result_array() as $row) {
			$test_categories[$this->xss_clean($row['lab_category_name'])] = $this->xss_clean($row['lab_category_name']);
		}
		$data['test_categories'] = $test_categories;
		$data['selected_category'] = $test_info->lab_category_id;

		$test_units = array('' => $this->lang->line('items_none'));
		foreach ($this->Item->testunit_get_all()->result_array() as $row) {
			$test_units[$this->xss_clean($row['lab_testunit_name'])] = $this->xss_clean($row['lab_testunit_name']);
		}
		$data['test_units'] = $test_units;
		$data['selected_test_unit'] = $test_info->test_unit;
		$test_subgroups = array('' => $this->lang->line('items_none'));
		foreach ($this->Item->testsubgroup_get_all()->result_array() as $row) {
			$test_subgroups[$this->xss_clean($row['lab_subgroup_name'])] = $this->xss_clean($row['lab_subgroup_name']);
		}
		$data['test_subgroups'] = $test_subgroups;
		$data['selected_test_subgroup'] = $test_info->test_subgroup;
		foreach ($this->Item->testkind_get_all()->result_array() as $row) {
			$test_kinds[$this->xss_clean($row['lab_testkind_name'])] = $this->xss_clean($row['lab_testkind_name']);
		}
		$data['test_kinds'] = $test_kinds;
		$data['selected_test_kind'] = $test_info->test_kind;

		$data['test_info'] = $test_info;

		//$suppliers = array('' => $this->lang->line('items_none'));
		/*foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['company_name']);
		}
		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $test_info->supplier_id;*/



		$this->load->view('laboratory/form', $data);
	}

	public function add_push()
	{
		$data = array();
		//$item_inf=array();


		// check if any discount is assigned to the selected customer

		// if the customer discount is 0 or no customer is selected apply the default sales discount


		//$location = 'None';
		$quantity = 1;
		$item_location = $this->sale_lib->get_sale_location();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');



		if (!$this->sale_lib->add_item_push($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location)) {
			$data['error'] = $this->lang->line('sales_unable_to_add_item');
		}

		$data['push'] = $this->sale_lib->get_push();
		//$locator = array('' => $this->lang->line('items_none'));
		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_loc()->result_array() as $row) {
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;

		$data['selected_locator'] = $selected_locator;

		$this->load->view('items/push', $data);
	}
	public function edit_item_push($item_id)
	{
		$data = array();

		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_loc()->result_array() as $row) {
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');


		$location = $this->input->post('location');
		$branch_transfer = $this->sale_lib->get_transfer_branch_id($location);
		$item_name = $this->input->post('item_name');

		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno = parse_decimals($this->input->post('stockno'));
		$quantit = parse_decimals($this->input->post('quantity'));
		if ($quantit > $stockno and $mode != 'return') {
			$quantity = 1;
			$data['warning'] = "Warning, Inputed Product Quantity is Insufficient,Reduce quantity to process sale or contact Admin to update inventory";
		} else {
			$quantity = parse_decimals($this->input->post('quantity'));
		}

		$item_location = $this->input->post('item_location');

		if ($this->form_validation->run() != FALSE) {
			$this->sale_lib->edit_item_push($item_id, $item_name, $item_location, $quantity, $location, $branch_transfer);
		} else {
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}
	public function cancel()
	{
		$this->sale_lib->clear_all_push();
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}
	public function delete_item($item_number)
	{
		$this->sale_lib->delete_item_push($item_number);

		$data['push'] = $this->sale_lib->get_push();
		$this->load->view('items/push', $data);
	}
	public function delete()
	{
		$suppliers_to_delete = $this->xss_clean($this->input->post('ids'));

		if ($this->Supplier->delete_test_list($suppliers_to_delete)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('suppliers_successful_deleted') . ' ' .
				count($suppliers_to_delete) . ' ' . 'Items'));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('suppliers_cannot_be_deleted')));
		}
	}

	public function push($item_id = -1)
	{
		$data['cart'] = $this->sale_lib->get_cart();
		$data['push'] = $this->sale_lib->get_push();
		$data['item_tax_info'] = $this->xss_clean($this->Item_taxes->get_info($item_id));
		$data['default_tax_1_rate'] = '';
		$data['default_tax_2_rate'] = '';
		$data['item_kits_enabled'] = $this->Employee->has_grant('item_kits', $this->Employee->get_logged_in_employee_info()->person_id);

		$item_info = $this->Item->get_info($item_id);
		foreach (get_object_vars($item_info) as $property => $value) {
			$item_info->$property = $this->xss_clean($value);
		}

		if ($item_id == -1) {
			$data['default_tax_1_rate'] = $this->config->item('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->item('default_tax_2_rate');

			$item_info->receiving_quantity = 0;
			$item_info->reorder_level = 0;
			$item_info->item_type = '0'; // standard
			$item_info->stock_type = '0'; // stock
			$item_info->tax_category_id = 0;
		}

		$data['item_info'] = $item_info;

		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_loc()->result_array() as $row) {
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;

		$data['selected_locator'] = $selected_locator;

		$customer_sales_tax_support = $this->config->item('customer_sales_tax_support');
		if ($customer_sales_tax_support == '1') {
			$data['customer_sales_tax_enabled'] = TRUE;
			$tax_categories = array();
			foreach ($this->Tax->get_all_tax_categories()->result_array() as $row) {
				$tax_categories[$this->xss_clean($row['tax_category_id'])] = $this->xss_clean($row['tax_category']);
			}
			$data['tax_categories'] = $tax_categories;
			$data['selected_tax_category'] = $item_info->tax_category_id;
		} else {
			$data['customer_sales_tax_enabled'] = FALSE;
			$data['tax_categories'] = array();
			$data['selected_tax_category'] = '';
		}

		$data['logo_exists'] = $item_info->pic_filename != '';
		$ext = pathinfo($item_info->pic_filename, PATHINFO_EXTENSION);
		if ($ext == '') {
			// if file extension is not found guess it (legacy)
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename . '.*');
		} else {
			// else just pick that file
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename);
		}
		$data['image_path'] = sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach ($stock_locations as $location) {
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id == -1) ? 0 : $quantity;
			$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
			$data['stock_locations'] = $location_array;
		}

		$this->load->view('items/push', $data);
	}


	public function inventory($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach (get_object_vars($item_info) as $property => $value) {
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach ($stock_locations as $location) {
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_inventory', $data);
	}


	public function count_details($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach (get_object_vars($item_info) as $property => $value) {
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach ($stock_locations as $location) {
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_count_details', $data);
	}

	public function generate_barcodes($item_ids)
	{
		$this->load->library('barcode_lib');

		$item_ids = explode(':', $item_ids);
		$result = $this->Item->get_multiple_info($item_ids, $this->item_lib->get_item_location())->result_array();
		$config = $this->barcode_lib->get_barcode_config();

		$data['barcode_config'] = $config;

		// check the list of items to see if any item_number field is empty
		foreach ($result as &$item) {
			$item = $this->xss_clean($item);

			// update the UPC/EAN/ISBN field if empty / NULL with the newly generated barcode
			if (empty($item['item_number']) && $this->config->item('barcode_generate_if_empty')) {
				// get the newly generated barcode
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();

				$save_item = array('item_number' => $item['item_number']);

				// update the item in the database in order to save the UPC/EAN/ISBN field
				$this->Item->save($save_item, $item['item_id']);
			}
		}
		$data['items'] = $result;

		// display barcodes
		$this->load->view('barcodes/barcode_sheet', $data);
	}

	public function bulk_edit()
	{
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_all()->result_array() as $row) {
			$row = $this->xss_clean($row);

			$suppliers[$row['person_id']] = $row['company_name'];
		}
		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1  => $this->lang->line('items_change_all_to_allow_alt_desc'),
			0  => $this->lang->line('items_change_all_to_not_allow_allow_desc')
		);

		$data['serialization_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1  => $this->lang->line('items_change_all_to_serialized'),
			0  => $this->lang->line('items_change_all_to_unserialized')
		);

		$this->load->view('items/form_bulk', $data);
	}

	public function save($item_id = -1)
	{
		$upload_success = $this->_handle_image_upload();
		$upload_data = $this->upload->data();

		//Save item data
		$item_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'category' => $this->input->post('category'),
			'expiry' => $this->input->post('expiry'),
			'pack' => $this->input->post('pack'),
			'item_type' => '0',
			'stock_type' => '0',
			'supplier_id' => $this->input->post('supplier_id') == '' ? NULL : $this->input->post('supplier_id'),
			'item_number' => $this->input->post('item_number') == '' ? NULL : $this->input->post('item_number'),
			'cost_price' => parse_decimals($this->input->post('cost_price')),
			'unit_price' => parse_decimals($this->input->post('unit_price')),
			'reorder_level' => parse_decimals($this->input->post('reorder_level')),
			'receiving_quantity' => parse_decimals($this->input->post('receiving_quantity')),
			'allow_alt_description' => $this->input->post('allow_alt_description') != NULL,
			'is_serialized' => $this->input->post('is_serialized') != NULL,
			'deleted' => $this->input->post('is_deleted') != NULL,

			'custom5' => $this->input->post('whole_price'),
			'custom6' => $this->input->post('custom6') == NULL ? '' : $this->input->post('custom6'),
			'custom7' => $this->input->post('custom7') == NULL ? '' : $this->input->post('custom7'),
			'custom8' => $this->input->post('custom8') == NULL ? '' : $this->input->post('custom8'),
			'custom9' => $this->input->post('custom9') == NULL ? '' : $this->input->post('custom9'),
			'custom10' => $this->input->post('custom10') == NULL ? '' : $this->input->post('custom10')
		);

		$x = $this->input->post('tax_category_id');
		if (!isset($x)) {
			$item_data['tax_category_id'] = '';
		} else {
			$item_data['tax_category_id'] = $this->input->post('tax_category_id');
		}

		if (!empty($upload_data['orig_name'])) {
			// XSS file image sanity check
			if ($this->xss_clean($upload_data['raw_name'], TRUE) === TRUE) {
				$item_data['pic_filename'] = $upload_data['raw_name'];
			}
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);

		if ($this->Item->save($item_data, $item_id)) {
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if ($item_id == -1) {
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}

			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$count = count($tax_percents);
			for ($k = 0; $k < $count; ++$k) {
				$tax_percentage = parse_decimals($tax_percents[$k]);
				if (is_numeric($tax_percentage)) {
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percentage);
				}
			}
			$success &= $this->Item_taxes->save($items_taxes_data, $item_id);

			//Save item quantity
			$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
			foreach ($stock_locations as $location) {
				$updated_quantity = parse_decimals($this->input->post('quantity_' . $location['location_id']));
				$location_detail = array(
					'item_id' => $item_id,
					'location_id' => $location['location_id'],
					'quantity' => $updated_quantity
				);
				$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location['location_id']);
				if ($item_quantity->quantity != $updated_quantity || $new_item) {
					$success &= $this->Item_quantity->save($location_detail, $item_id, $location['location_id']);

					$inv_data = array(
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item_id,
						'trans_user' => $employee_id,
						'trans_location' => $location['location_id'],
						'trans_comment' => $this->lang->line('items_manually_editing_of_quantity'),
						'trans_inventory' => $updated_quantity - $item_quantity->quantity
					);

					$success &= $this->Inventory->insert($inv_data);
				}
			}

			if ($success && $upload_success) {
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			} else {
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		} else //failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}

	public function check_item_number()
	{
		$exists = $this->Item->item_number_exists($this->input->post('item_number'), $this->input->post('item_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	If adding a new item check to see if an item kit with the same name as the item already exists.
	*/


	public function send_invoice($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);

		$result = FALSE;
		$message = $this->lang->line('sales_invoice_no_email');

		if (!empty($sale_data['customer_email'])) {
			$to = $sale_data['customer_email'];
			$subject = $this->lang->line('sales_invoice') . ' ' . $sale_data['invoice_number'];

			$text = $this->config->item('invoice_email_message');
			$text = str_replace('$INV', $sale_data['invoice_number'], $text);
			$text = str_replace('$CO', 'POS ' . $sale_data['sale_id'], $text);
			$text = $this->_substitute_customer($text, (object) $sale_data);

			// generate email attachment: invoice in pdf format
			$html = $this->load->view('sales/invoice_email', $sale_data, TRUE);
			// load pdf helper
			$this->load->helper(array('dompdf', 'file'));
			$filename = sys_get_temp_dir() . '/' . $this->lang->line('sales_invoice') . '-' . str_replace('/', '-', $sale_data['invoice_number']) . '.pdf';
			if (file_put_contents($filename, pdf_create($html)) !== FALSE) {
				$result = $this->email_lib->sendEmail($to, $subject, $text, $filename);
			}

			$message = $this->lang->line($result ? 'sales_invoice_sent' : 'sales_invoice_unsent') . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message, 'id' => $sale_id));

		$this->sale_lib->clear_all();

		return $result;
	}



	private function _load_customer_data($customer_id, &$data, $stats = FALSE)
	{
		$customer_info = '';

		if ($customer_id != -1) {
			$customer_info = $this->Customer->get_info($customer_id);
			if (isset($customer_info->company_name)) {
				$data['customer'] = $customer_info->company_name;
			} else {
				$data['customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
			}
			$data['date_of_birth'] = $customer_info->date_of_birth;
			$data['phone_number'] = $customer_info->phone_number;
			$data['first_name'] = $customer_info->first_name;
			$data['last_name'] = $customer_info->last_name;
			$data['customer_email'] = $customer_info->email;
			$data['customer_address'] = $customer_info->address_1;
			$data['gender'] = ($customer_info->gender) == 1 ? 'Male' : 'Female';
			$data['age'] = $customer_info->age;
			if (!empty($customer_info->zip) || !empty($customer_info->city)) {
				$data['customer_location'] = $customer_info->zip . ' ' . $customer_info->city;
			} else {
				$data['customer_location'] = '';
			}

			$data['customer_discount_percent'] = $customer_info->discount_percent;
			$package_id = $this->Customer->get_info($customer_id)->package_id;
			if ($package_id != NULL) {
				$package_name = $this->Customer_rewards->get_name($package_id);
				$points = $this->Customer->get_info($customer_id)->points;
				$data['customer_rewards']['package_id'] = $package_id;
				$data['customer_rewards']['points'] = ($points == NULL ? 0 : $points);
				$data['customer_rewards']['package_name'] = $package_name;
			}

			if ($stats) {
				$cust_stats = $this->Customer->get_stats($customer_id);
				$data['customer_total'] = empty($cust_stats) ? 0 : $cust_stats->total;
			}

			$data['customer_info'] = implode("\n", array(
				$data['customer'],
				$data['customer_address'],
				$data['customer_location'],

			));
		}

		return $customer_info;
	}

	private function _load_sale_data($sale_id)
	{
		$this->sale_lib->clear_all();
		$this->sale_lib->reset_cash_flags();
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$this->sale_lib->copy_entire_sale($sale_id);
		$data = array();
		$data['cart'] = $this->sale_lib->get_cart();
		$data['payments'] = $this->sale_lib->get_payments();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();

		//		$data['subtotal'] = $this->sale_lib->get_subtotal();
		//		$data['discounted_subtotal'] = $this->sale_lib->get_subtotal(TRUE);
		//		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
		//		$data['amount_due'] = $this->sale_lib->get_amount_due();
		//		$data['amount_change'] = $this->sale_lib->get_amount_due() * -1;
		//		$data['total'] = $this->sale_lib->get_total();

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($sale_info['sale_time']));
		$data['transaction_date'] = date($this->config->item('dateformat'), strtotime($sale_info['sale_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');


		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['subtotal'];
		$data['discounted_subtotal'] = $totals['discounted_subtotal'];
		$data['tax_exclusive_subtotal'] = $totals['tax_exclusive_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_total'] = $totals['total'];
		$data['non_cash_amount_due'] = $totals['amount_due'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];

		if ($this->session->userdata('cash_rounding')) {
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		} else {
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$employee_info = $this->Employee->get_info($this->sale_lib->get_employee());
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$this->_load_customer_data($this->sale_lib->get_customer(), $data);

		$data['sale_id_num'] = $sale_id;
		$data['sale_id'] = 'POS ' . $sale_id;
		$data['comments'] = $sale_info['comment'];
		$data['invoice_number'] = $sale_info['invoice_number'];
		$data['quote_number'] = $sale_info['quote_number'];
		$data['sale_status'] = $sale_info['sale_status'];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
		$data['print_after_sale'] = FALSE;
		if ($this->sale_lib->get_mode() == 'sale_invoice') {
			$data['mode_label'] = $this->lang->line('sales_invoice');
		} elseif ($this->sale_lib->get_mode() == 'sale_quote') {
			$data['mode_label'] = $this->lang->line('sales_quote');
		}

		return $this->xss_clean($data);
	}


	public function receipt($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);
		$this->load->view('sales/receipt', $data);
		$this->sale_lib->clear_all();
	}

	public function invoice($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);
		$this->load->view('sales/invoice', $data);
		$this->sale_lib->clear_all();
	}



	public function check_invoice_number()
	{
		$sale_id = $this->input->post('sale_id');
		$invoice_number = $this->input->post('invoice_number');
		$exists = !empty($invoice_number) && $this->Sale->check_invoice_number_exists($invoice_number, $sale_id);
		echo !$exists ? 'true' : 'false';
	}


	public function select_customer()
	{
		$customer_id = $this->input->post('customer');
		if ($this->Customer->exists($customer_id)) {
			$this->sale_lib->set_customer($customer_id);
		}

		$this->_reload();
	}


	public function set_invoice_number()
	{
		$this->sale_lib->set_invoice_number($this->input->post('sales_invoice_number'));
	}

	public function set_invoice_number_enabled()
	{
		$this->sale_lib->set_invoice_number_enabled($this->input->post('sales_invoice_number_enabled'));
	}

	public function set_payment_type()
	{
		$this->sale_lib->set_payment_type($this->input->post('selected_payment_type'));
		$this->_reload();
	}

	public function set_print_after_sale()
	{
		$this->sale_lib->set_print_after_sale($this->input->post('sales_print_after_sale'));
	}

	public function set_email_receipt()
	{
		$this->sale_lib->set_email_receipt($this->input->post('email_receipt'));
	}

	// Multiple Payments

	public function search_patients()
	{
		$data = [];
		$data['table_headers'] = $this->xss_clean(get_patients_headers());
		$this->load->view("laboratory/patient_search", $data);
	}

	public function service_search_patients()
	{
		$search			= $this->input->get('search');
		$limit 			= $this->input->get('limit');
		$offset 		= $this->input->get('offset');
		$sort 			= $this->input->get('sort') ? $this->input->get('sort') : 'people.person_id';
		$order 			= $this->input->get('order') ? $this->input->get('order') : 'asc';
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'search' => $search, 'limit' => $limit, 'offset' => $offset, 'sort' => $sort, 'order' => $order);
		$suggestions		= $this->Customer->search_page($inputs);
		$suggestions_total	= $this->Customer->search_page_count($inputs);
		$data_rows	= array();
		foreach ($suggestions->result() as $d) {
			$tmp = array('person_id' => $d->person_id, 'first_name' => $d->first_name, 'last_name' => $d->last_name, 'phone_number' => $d->phone_number);
			$data_rows[] = $this->xss_clean(get_patients_row($tmp, $this));
		}
		echo json_encode(array('total' => $suggestions_total, 'rows' => $data_rows));
	}

	public function view_patient_data($id)
	{
		$patient_data = $this->Customer->load_patient_data($id)->result();
		$data['patient'] = $patient_data[0];

		//get all pos id's for this patient.
		$customerPOSIDs = $this->Item->get_customer_pos_ids($id);
		$data['pos_id'] = $customerPOSIDs;
		$this->load->view("laboratory/patient_data", $data);
	}

	public function get_unprocessed_tests_count()
	{
		$lab_items	=	array();
		$data		=	$this->Item->new_result_items();
		foreach ($data as $row => $value) {
			$lab_items[]	= array('sale_id' => $value['sale_id'], 'customer_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' => '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>', 'print' => '<button class="btn btn-danger btn-sm pull-left modal-dlg " id="' . $value['invoice_id'] . '" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
		}
		echo json_encode(count($lab_items));
	}

	public function result_info_profile($id)
	{
		$data = $this->fetch_result_info($id);
		$this->load->view('laboratory/results_check', $data);
	}
	public function fetch_result_info($id)
	{
		$result_info = $this->Item->get_labresult_info($id);

		$data['sale_id'] = $id;
		$data['invoice'] = $result_info->invoice_id;
		$data['status'] = $result_info->status;

		$test_info = $this->Item->get_labtest_info($id, $result_info->status);
		$data['tests'] = $test_info;
		$customer_id = $result_info->customer_id;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		//$data['cart'] = $this->sale_lib->get_labsaveresultcart_reordered($sale_id);
		// filters that will be loaded in the multiselect dropdown
		return $data;
	}
}
