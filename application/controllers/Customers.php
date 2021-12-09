<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Persons.php");

class Customers extends Persons
{
	private $_list_id;

	public function __construct()
	{
		parent::__construct('customers');

		$this->load->library('mailchimp_lib');
		$this->load->library('audit_lib');

		$CI = &get_instance();

		$this->_list_id = $CI->encryption->decrypt($CI->Appconfig->get('mailchimp_list_id'));
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_customer_manage_table_headers());

		$this->load->view('people/manage', $data);
	}

	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		// $sort   = 'person.name';
		$order  = $this->input->get('order');

		$all_customers = $this->Customer->search($search, 0, $offset, $sort, $order)->result();
		$total_customers = count($all_customers);
		$customers = array_slice($all_customers, $offset, $limit);


		$data_rows = array();
		foreach ($customers as $person) {

			// retrieve the total amount the customer spent so far together with min, max and average values
			$stats = $this->Customer->get_stats($person->person_id);
			//$stats = array();
			if (empty($stats)) {
				//create object with empty properties.
				$stats = new stdClass;
				$stats->total = 0;
				$stats->min = 0;
				$stats->max = 0;
				$stats->average = 0;
				$stats->avg_discount = 0;
				$stats->quantity = 0;
			}

			$data_rows[] = get_customer_data_row($person, $stats, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_customers, 'rows' => $data_rows));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Customer->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Customer->get_search_suggestions($this->input->post('term'), FALSE));

		echo json_encode($suggestions);
	}

	/*
	Loads the customer edit form
	*/
	public function view($customer_id = -1, $is_lab = false)
	{

		$this->audit_lib->empty_family();

		$family = !empty($this->input->post('family_id')) ? $this->input->post('family_id') : '';
		$this->audit_lib->set_family($dept);

		$info = $this->Customer->get_info($customer_id);
		foreach (get_object_vars($info) as $property => $value) {
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;
		$packages = array('' => $this->lang->line('items_none'));
		foreach ($this->Customer_rewards->get_all()->result_array() as $row) {
			$packages[$this->xss_clean($row['package_id'])] = $this->xss_clean($row['package_name']);
		}
		$data['packages'] = $packages;
		$data['selected_package'] = $info->package_id;

		$companies = array('' => $this->lang->line('items_none'));
		foreach ($this->Customer->get_all_companies()->result_array() as $row) {
			$companies[$this->xss_clean($row['company_id'])] = $this->xss_clean($row['company_name']);
		}
		$data['companies'] = $companies;
		$data['selected_company'] = $info->company_id;

		//families
		$families = array('' => $this->lang->line('items_none'));
		foreach ($this->Customer->get_all()->result_array() as $row) {
			$families[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['first_name']) . ' ' . $this->xss_clean($row['last_name']) . ' (' . $this->xss_clean($row['phone_number']) . ')';
		}
		$data['families'] = $families;
		$data['selected_family'] = $info->family_id;

		// retrieve the total amount the customer spent so far together with min, max and average values
		$stats = $this->Customer->get_stats($customer_id);
		if (!empty($stats)) {
			foreach (get_object_vars($stats) as $property => $value) {
				$info->$property = $this->xss_clean($value);
			}
			$data['stats'] = $stats;
		}
		$data['is_lab'] = $is_lab;

		$this->load->view("customers/form", $data);
	}
	/*
	Loads the customer wallet update form
	*/
	public function view_wallet($customer_id = -1)
	{


		$info = $this->Customer->get_info($customer_id);

		$data['person_info'] = $info;


		$data['already_used_credit'] = $this->Sale->get_thismonth_credit($customer_id); //for staff customers



		$this->load->view("customers/wallet_form", $data);
	}

	public function save_wallet($customer_id = -1)
	{

		$customer_id = (int) $this->xss_clean($this->input->post("customer_id"));
		$deposit = $this->xss_clean($this->input->post('deposit'));
		$narration = $this->xss_clean($this->input->post('narration'));
		$updateType = $this->xss_clean($this->input->post('update_type'));
		$customer = $this->Customer->get_info($customer_id);
		if ($customer_id > 0) {
			//update customer wallet balance
			$update_data = array(
				'balance' => $updateType < -1 ? $customer->wallet - $deposit: $customer->wallet + $deposit, //balance is what is currently in the wallet plus the new deposit
//				'sale_id' => 0, //0 sale_id means funding. bigger than 0 mean sales that uses wallet payment type
                'sale_id' => $updateType,
				'debit'=> $updateType < -1 ?$deposit: 0.00,
                'credit'=> $updateType < -1 ? 0.00 : $deposit,
				'customer_id' => $customer_id,
				'narration'=>$narration,
				'employee_id' => $this->Employee->get_logged_in_employee_info()->person_id,
				'date' => date("Y-m-d H:i:s"),
				'amount' => $deposit
			);

			// $msg = 'Updated '. $customer->first_name . ' ' . $customer->last_name . ' Wallet with ' . $deposit . ', Balance now: '. $customer->walllet + $deposit;
			$msg = " Wallet with " . $customer->first_name;

			$this->audit_lib->add_log('edit', $msg);
			// echo json_encode(array(
			// 	'success' => TRUE,
			// 	'message' => $customer->first_name,
			// 	'id' => $customer_id
			// ));

			$this->Customer->update_customer_wallet($customer_id, $update_data);
			
			echo json_encode(array(
				'success' => TRUE,
				'message' => 'Customer wallet has been successfull updated.',
				'id' => $customer_id
			));
		} else {


			echo json_encode(array(
				'success' => FALSE,
				'message' => 'No customer selected',
				'id' => -1
			));
		}
	}
	public function print_customer_wallet_history($customer_id = 0, $start_date = null, $end_date = null,$isLedger = false)
	{

		// $item_id = $this->input->post('item_id');

		// $start_date = $this->input->post('start_date');
		// $end_date = $this->input->post('end_date');
		if ($customer_id <= 0) {

			redirect('customers');
		}
		if ($start_date != null) {
			$start_date = $start_date . ' 00:00:00';
		}
		if ($end_date != null) {
			$end_date = $end_date . ' 23:59:59';
		}

		//$current_location_id = $this->input->post('stock_location');
        if($isLedger){
            $data['brought_forward'] = $this->Customer->get_brought_forward($customer_id,$start_date);
            $data['is_ledger'] = true;
        }

		$data['customer_info'] = $this->Customer->get_info($customer_id);
		$data['credit_sales'] = 0;
		if ($data['customer_info']->staff) {
			$data['credit_sales'] = $this->Sale->get_thismonth_credit($customer_id);
		}
		$cust_stats = $this->Customer->get_stats($customer_id);
		$data['customer_total'] = empty($cust_stats) ? 0 : $cust_stats->total;

		$data['wallet_info'] = $this->Customer->get_wallet_info($customer_id, $start_date, $end_date);

		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;

		$this->load->view('customers/wallet_history', $data);
	}
	public function ledger($customer_id = 0, $start_date = null, $end_date = null){
        $this->print_customer_wallet_history($customer_id,$start_date,$end_date,true);
    }
	/*
	Inserts/updates a customer
	*/
	public function save($customer_id = -1)
	{
		$first_name = $this->xss_clean($this->input->post('first_name'));
		$last_name = $this->xss_clean($this->input->post('last_name'));
		$email = $this->xss_clean(strtolower($this->input->post('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->input->post('gender'),
			'age' => $this->input->post('age'),
			'email' => $email,
			'phone_number' => $this->input->post('phone_number'),
			'date_of_birth' => $this->input->post('date_of_birth'),
			'address_1' => $this->input->post('address_1'),
			'address_2' => $this->input->post('address_2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'comments' => $this->input->post('comments')
		);


		$customer_data = array(
			'company_name' => $this->input->post('company_name') == '' ? NULL : $this->input->post('company_name'),
			'discount_percent' => $this->input->post('discount_percent') == '' ? 0.00 : $this->input->post('discount_percent'),
			'credit_limit' => $this->input->post('credit_limit') == '' ? 0.00 : $this->input->post('credit_limit'),
			'sale_markup' => $this->input->post('sale_markup') == '' ? 0.00 : $this->input->post('sale_markup'),
			'package_id' => $this->input->post('package_id') == '' ? NULL : $this->input->post('package_id'),
			'company_id' => $this->input->post('company_id') == '' ? NULL : $this->input->post('company_id'),
			'staff' => $this->input->post('type') != 0
		);

		if ($this->Customer->save_customer($person_data, $customer_data, $customer_id)) {
			// save customer to Mailchimp selected list

			// New customer
			if ($customer_id == -1) {
				echo json_encode(array(
					'success' => TRUE,
					'message' => $this->lang->line('customers_successful_adding') . ' ' . $first_name . ' ' . $last_name,
					'id' => $this->xss_clean($customer_data['person_id'])
				));
			} else // Existing customer
			{
				echo json_encode(array(
					'success' => TRUE,
					'message' => $this->lang->line('customers_successful_updating') . ' ' . $first_name . ' ' . $last_name,
					'id' => $customer_id
				));
			}
		} else // Failure
		{
			echo json_encode(array(
				'success' => FALSE,
				'message' => $this->lang->line('customers_error_adding_updating') . ' ' . $first_name . ' ' . $last_name,
				'id' => -1
			));
		}
	}
	
	/*
	AJAX call to verify if an phone address already exists
	*/
	public function ajax_check_phone()
	{
		// $check = $this->input->post('check_phone');
		$exists = $this->Customer->check_phone_exists(strtolower($this->input->post('phone_number')), $this->input->post('person_id'));

		// echo $check == '1' ? !$exists ? 'true' : 'false' : 'true';
		echo !$exists ? 'true' : 'false';
	}

	/*
	AJAX call to verify if an email address already exists
	*/
	public function ajax_check_email()
	{
		$exists = $this->Customer->check_email_exists(strtolower($this->input->post('email')), $this->input->post('person_id'));

		echo !$exists ? 'true' : 'false';
	}
	/*
	AJAX call to verify if an new wallet update amount(deposit) already exists
	*/
	public function ajax_check_deposit()
	{
		return is_numeric($this->input->post('deposit')) ? 'true' : 'false';
	}

	/*
	AJAX call to verify if an account number already exists
	*/
	public function ajax_check_account_number()
	{
		$exists = $this->Customer->check_account_number_exists($this->input->post('account_number'), $this->input->post('person_id'));

		echo !$exists ? 'true' : 'false';
	}

	/*
	This deletes customers from the customers table
	*/
	public function delete()
	{
		$customers_to_delete = $this->input->post('ids');
		$customers_info = $this->Customer->get_multiple_info($customers_to_delete);

		if ($this->Customer->delete_list($customers_to_delete)) {
			foreach ($customers_info->result() as $info) {
				// remove customer from Mailchimp selected list
				$this->mailchimp_lib->removeMember($this->_list_id, $info->email);
			}

			echo json_encode(array(
				'success' => TRUE,
				'message' => $this->lang->line('customers_successful_deleted') . ' ' . count($customers_to_delete) . ' ' . $this->lang->line('customers_one_or_multiple')
			));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('customers_cannot_be_deleted')));
		}
	}

	/*
	Customers import from excel spreadsheet
	*/
	public function excel()
	{
		$name = 'import_customers.csv';
		$data = file_get_contents('../' . $name);
		force_download($name, $data);
	}

	public function excel_import()
	{
		$this->load->view('customers/form_excel_import', NULL);
	}

	public function do_excel_import()
	{
		if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK) {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('customers_excel_import_failed')));
		} else {
			if (($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== FALSE) {
				// Skip the first row as it's the table description
				fgetcsv($handle);
				$i = 1;

				$failCodes = array();

				while (($data = fgetcsv($handle)) !== FALSE) {
					// XSS file data sanity check
					$data = $this->xss_clean($data);

					if (sizeof($data) >= 15) {
						$email = strtolower($data[3]);
						$person_data = array(
							'first_name'	=> $data[0],
							'last_name'		=> $data[1],
							'gender'		=> $data[2],
							'email'			=> $email,
							'phone_number'	=> $data[4],
							'address_1'		=> $data[5],
							'address_2'		=> $data[6],
							'city'			=> $data[7],
							'state'			=> $data[8],
							'zip'			=> $data[9],
							'country'		=> $data[10],
							'comments'		=> $data[11]
						);

						$customer_data = array(
							'company_name'		=> $data[12],
							'discount_percent'	=> $data[13],
							'credit_limit' => $data[14],
							'taxable'			=> $data[15] == '' ? 0 : 1
						);
						$account_number = $data[13];

						// don't duplicate people with same email
						$invalidated = $this->Customer->check_email_exists($email);

						if ($account_number != '') {
							$customer_data['account_number'] = $account_number;
							$invalidated &= $this->Customer->check_account_number_exists($account_number);
						}
					} else {
						$invalidated = TRUE;
					}

					if ($invalidated) {
						$failCodes[] = $i;
					} elseif ($this->Customer->save_customer($person_data, $customer_data)) {
						// save customer to Mailchimp selected list
						$this->mailchimp_lib->addOrUpdateMember($this->_list_id, $person_data['email'], $person_data['first_name'], '', $person_data['last_name']);
					} else {
						$failCodes[] = $i;
					}

					++$i;
				}

				if (count($failCodes) > 0) {
					$message = $this->lang->line('customers_excel_import_partially_failed') . ' (' . count($failCodes) . '): ' . implode(', ', $failCodes);

					echo json_encode(array('success' => FALSE, 'message' => $message));
				} else {
					echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('customers_excel_import_success')));
				}
			} else {
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('customers_excel_import_nodata_wrongformat')));
			}
		}
	}
}
