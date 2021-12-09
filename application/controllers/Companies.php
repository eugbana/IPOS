<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Companies extends Secure_Controller
{
	private $_list_id;

	public function __construct()
	{
		parent::__construct('companies');

		$this->load->library('mailchimp_lib');
		$this->load->library('audit_lib');

		$CI = &get_instance();

		$this->_list_id = $CI->encryption->decrypt($CI->Appconfig->get('mailchimp_list_id'));
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_company_manage_table_headers());

		$this->load->view('company/manage', $data);
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

		$all_companies = $this->Customer->search_companies($search, 0, $offset, $sort, $order)->result();
		$total_companies = count($all_companies);
		$companies = array_slice($all_companies, $offset, $limit);


		$data_rows = array();
		foreach ($companies as $company) {

			// retrieve the total amount the customer spent so far together with min, max and average values
			// $stats = $this->Customer->get_stats($person->person_id);
			//$stats = array();
			// if (empty($stats)) {
			// 	//create object with empty properties.
			// 	$stats = new stdClass;
			// 	$stats->total = 0;
			// 	$stats->min = 0;
			// 	$stats->max = 0;
			// 	$stats->average = 0;
			// 	$stats->avg_discount = 0;
			// 	$stats->quantity = 0;
			// }

			$data_rows[] = get_company_data_row($company, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_companies, 'rows' => $data_rows));
	}

	public function companies_search_suggestions()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		$suggestions = $this->xss_clean($this->Customer->get_companies_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
		// $sg = $this->xss_clean(array(
		// 	array('value' => 'value', 'label' => 'wilson'),
		// 	array('value' => 'value1', 'label' => 'wilson2'),
		// 	array('value' => 'value2', 'label' => 'wilson3')
		// ));
		// echo json_encode(
		// 	$sg
		// );
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
	Loads the company edit form
	*/
	public function view($company_id = -1)
	{

		$info = $this->Customer->get_company_info($company_id);
		foreach (get_object_vars($info) as $property => $value) {
			$info->$property = $this->xss_clean($value);
		}
		$data['company_info'] = $info;
		$packages = array('' => $this->lang->line('items_none'));
		// foreach ($this->Customer_rewards->get_all()->result_array() as $row) {
		// 	$packages[$this->xss_clean($row['package_id'])] = $this->xss_clean($row['package_name']);
		// }
		// $data['packages'] = $packages;
		// $data['selected_package'] = $info->package_id;

		// retrieve the total amount the customer spent so far together with min, max and average values
		// $stats = $this->Customer->get_stats($customer_id);
		// if (!empty($stats)) {
		// 	foreach (get_object_vars($stats) as $property => $value) {
		// 		$info->$property = $this->xss_clean($value);
		// 	}
		// 	$data['stats'] = $stats;
		// }

		$this->load->view("company/form", $data);
	}
	/*
	Loads the company wallet update form
	*/
	public function view_wallet($company_id = -1)
	{

		$info = $this->Customer->get_company_info($company_id);

		$data['company_info'] = $info;

		$data['already_used_credit'] = $this->Sale->get_thismonth_credit($customer_id); //for staff customers

		$this->load->view("company/wallet_form", $data);
	}

	public function save_wallet($company_id = -1)
	{

		$company_id = (int) $this->xss_clean($this->input->post("company_id"));
		$deposit = $this->xss_clean($this->input->post('deposit'));
		$company = $this->Customer->get_company_info($company_id);
		if ($company_id > 0) {
			//update company wallet balance
			$update_data = array(
				'balance' => $company->wallet + $deposit, //balance is what is currently in the wallet plus the new deposit
				'sale_id' => 0, //0 sale_id means funding. bigger than 0 mean sales that uses wallet payment type
				'company_id' => $company_id,
				'customer_id' => 0, //0 customer id means no customer linked to a company used the wallet amount
				'employee_id' => $this->Employee->get_logged_in_employee_info()->person_id,
				'date' => date("Y-m-d H:i:s"),
				'amount' => $deposit
			);
			$this->Customer->update_company_wallet($company_id, $update_data);
			echo json_encode(array(
				'success' => TRUE,
				'message' => 'Company wallet has been successfull updated.',
				'id' => $company_id
			));
		} else {

			echo json_encode(array(
				'success' => FALSE,
				'message' => 'No company selected',
				'id' => -1
			));
		}
	}
	public function print_customer_wallet_history($customer_id = 0, $start_date = null, $end_date = null)
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
	/*
	Inserts/updates a company
	*/
	public function save($company_id = -1)
	{
		$company_name = $this->xss_clean($this->input->post('company_name'));
		// $last_name = $this->xss_clean($this->input->post('last_name'));
		$email = $this->xss_clean(strtolower($this->input->post('email')));

		// format first and last name properly
		// $first_name = $this->nameize($first_name);
		// $last_name = $this->nameize($last_name);

		$company_data = array(
			'company_name' => $company_name,
			'cac' => $this->input->post('cac'),
			'tin' => $this->input->post('tin'),
			'contact_email' => $email,
			'contact_phone' => $this->input->post('phone_number'),
			'address1' => $this->input->post('address1'),
			'address2' => $this->input->post('address2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
            'country' => $this->input->post('country'),
            'discount' => $this->input->post('discount_percent') == '' ? 0.00 : $this->input->post('discount_percent'),
			'credit_limit' => $this->input->post('credit_limit') == '' ? 0.00 : $this->input->post('credit_limit'),
			'markup' => $this->input->post('sale_markup') == '' ? 0.00 : $this->input->post('sale_markup'),
		);

		if ($this->Customer->save_company($company_data, $company_id)) {
			// save company to Mailchimp selected list

			// New company
			if ($company_id == -1) {
				$this->audit_lib->add_log('add', 'Added a new Company, ' . $company_name);
				echo json_encode(array(
					'success' => TRUE,
					'message' => 'Company successfully added ' . $company_name,
					'id' => $this->xss_clean($company_data['company_id'])
				));
			} else // Existing company
			{
				$this->audit_lib->add_log('edit', 'Updated a Company, ' . $company_name);
				echo json_encode(array(
					'success' => TRUE,
					'message' => ' Company successfully updated' . $company_name,
					'id' => $company_id
				));
			}
		} else // Failure
		{
			echo json_encode(array(
				'success' => FALSE,
				'message' => 'Error Adding company' . $company_name,
				'id' => -1
			));
		}
	}
	
	/*
	AJAX call to verify if an phone address already exists
	*/
	public function ajax_check_phone()
	{
		$exists = $this->Customer->check_company_phone_exists(strtolower($this->input->post('phone_number')), $this->input->post('company_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	AJAX call to verify if an email address already exists
	*/
	public function ajax_check_email()
	{
		$exists = $this->Customer->check_company_email_exists(strtolower($this->input->post('email')), $this->input->post('company_id'));
		echo !$exists ? 'true' : 'false';
    }
    
    /*
	AJAX call to verify if an cac number already exists
	*/
	public function ajax_check_cac()
	{
		$exists = $this->Customer->check_company_cac_exists(strtolower($this->input->post('cac')), $this->input->post('company_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	AJAX call to verify if an email address already exists
	*/
	public function ajax_check_tin()
	{
		$exists = $this->Customer->check_company_tin_exists(strtolower($this->input->post('tin')), $this->input->post('company_id'));
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
		$companies_to_delete = $this->input->post('ids');
		$companies_info = $this->Customer->get_multiple_company_info($companies_to_delete);

		if ($this->Customer->delete_companies_list($companies_to_delete)) {
			foreach ($companies_info->result() as $info) {
				// remove customer from Mailchimp selected list
				$this->mailchimp_lib->removeMember($this->_list_id, $info->contact_email);
			}

			$this->audit_lib->add_log('delete', 'Deleted ' . count($companies_to_delete) . ' Company(s)');

			echo json_encode(array(
				'success' => TRUE,
				'message' => 'Successfully deleted ' . count($companies_to_delete) . ' Company(s)'
			));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => 'Company(s) cannot be deleted'));
		}
	}

	/*
	Companies import from excel spreadsheet
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
