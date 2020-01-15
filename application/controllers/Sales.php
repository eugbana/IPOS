<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Sales extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('sales');

		$this->load->library('sale_lib');
		$this->load->library('tax_lib');
		$this->load->library('barcode_lib');
		$this->load->library('email_lib');
		$this->load->library('token_lib');
	}

	public function index()
	{

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		if (!$this->Sale->register_exists($employee_id)) {
			$this->open_register();
		} else {

			$this->_reload();
		}
	}
	public function open_register()
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		if (!$this->Sale->register_exists($employee_id)) {
			$this->load->view('sales/open_register', array());
		} else {

			$this->_reload();
		}
	}
	public function save_register()
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		if ($this->Sale->register_exists($employee_id)) {
			//$this->_reload();
			redirect('sales');
		} else {
			//$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$newdate = $this->input->post('date');
			$amount = trim($this->input->post('balance_entered'));
			$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);
			$sale_data = array(
				'employeeID ' => $employee_id,
				'balance_entered' => (!is_numeric($amount) || empty($amount)) ? 0 : $amount,
				'date_entered' => date('Y-m-d')
			);

			// go through all the payment type input from the form, make sure the form matches the name and iterator number



			if ($this->Sale->save_register($sale_data)) {
				//$this->_reload();
				redirect('sales');
			} else {
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $employee_id));
			}
		}
	}
	public function close_register()
	{
		$register_id = $this->sale_lib->get_balance_id();

		$sale_data = array(
			'status ' => 2,
		);

		// go through all the payment type input from the form, make sure the form matches the name and iterator number



		if ($this->Sale->close_register($sale_data, $register_id)) {
			$this->load->view('sales_home');
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $register_id));
		}
	}

	public function manage()
	{
		$person_id = $this->session->userdata('person_id');


		$data['table_headers'] = get_sales_manage_table_headers();

		// filters that will be loaded in the multiselect dropdown
		if ($this->config->item('invoice_enable') == TRUE) {
			$data['filters'] = array(
				'only_cash' => $this->lang->line('sales_cash_filter'),
				'only_check' => $this->lang->line('sales_check_filter'),
				'only_invoices' => $this->lang->line('sales_invoice_filter')
			);
		} else {
			$data['filters'] = array(
				'only_cash' => $this->lang->line('sales_cash_filter'),
				'only_check' => $this->lang->line('sales_check_filter')
			);
		}

		$this->load->view('sales/manage', $data);
	}
	public function detailed_sales()
	{
		//$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type, 'location_id' => $location_id);
		$inputs = array();
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$model->create($inputs);

		$headers = $this->xss_clean($model->getDataColumnsOnline());

		$item_location = $this->sale_lib->get_sale_location();
		$report_data = $model->getDataOnline($item_location);

		$summary_data = array();
		$details_data = array();
		$details_data_rewards = array();


		$show_locations = $this->xss_clean($this->Stock_location->multiple_locations());

		foreach ($report_data['summary'] as $key => $row) {
			$summary_data[] = $this->xss_clean(array(
				'id' => $row['transfer_id'],
				'sale_date' => $row['transfer_time'],
				'customer' => $row['transfer_type'],
				'transfer_type' => $row['transfer_type'],
				'status' => 'PAID',
				'edit' => anchor(
					'sales/onlineProcess/' . $row['transfer_id'],
					'<span class="buttona">Process</span>',
					array('data-btn-delete' => $this->lang->line('common_delete'), 'data-btn-submit' => $this->lang->line('common_submit'), 'title' => 'process')
				)
			));

			foreach ($report_data['details'][$key] as $drow) {
				$item_info = $this->CI->Item->get_info_by_id_or_number($drow['item_id']);
				$name = $item_info->name;
				$category = $item_info->category;
				//$quantity_purchased = to_quantity_decimals($drow['quantity_purchased']);

				$details_data[$row['transfer_id']][] = $this->xss_clean(array($drow['item_id'], $name, $category, to_quantity_decimals($drow['pushed_quantity'])));
			}
		}

		$data = array(
			'title' => 'Online Sales Transaction',
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->xss_clean($model->getSummaryData($inputs))
		);
		$this->load->view('sales/tabular_details', $data);
	}
	public function onlineProcess($transfer_id)
	{
		$data = array();


		$data['car'] = $this->sale_lib->get_onlinesale_items_ordered($transfer_id);
		$data['transaction_type'] = 'ONLINE';
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$totals = $this->sale_lib->get_totals();
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
		$data['sale_status'] = 0;
		$item_location = $this->sale_lib->get_sale_location();
		$data['sale_id_num'] = $this->Sale->saveOnlineSale($data['sale_status'], $data['car'], $employee_id, $data['total'], $item_location, $transfer_id);

		// filters that will be loaded in the multiselect dropdown
		$data['cart'] = $this->get_filtered($this->sale_lib->get_cart_reordered($data['sale_id_num']));

		$this->load->view('sales/receipt', $data);
		$this->sale_lib->clear_all();
	}


	public function delete_item($item_number)
	{
		$this->sale_lib->delete_item($item_number);

		redirect('sales');
		//$this->_reload(array());
	}

	public function delete_ite($item_number)
	{
		session_start();
		$data['error'] = $_SESSION['wari'];
		unset($_SESSION['wari']);
		$this->sale_lib->delete_item($item_number);


		$this->_reload($data);
	}

	public function get_row($row_id)
	{
		$sale_info = $this->Sale->get_info($row_id)->row();
		$data_row = $this->xss_clean(get_sale_data_row($sale_info, $this));

		echo json_encode($data_row);
	}

	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$filters = array(
			'sale_type' => 'all',
			'location_id' => 'all',
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'only_cash' => FALSE,
			'only_check' => FALSE,
			'only_invoices' => $this->config->item('invoice_enable') && $this->input->get('only_invoices'),
			'is_valid_receipt' => $this->Sale->is_valid_receipt($search)
		);

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);

		$sales = $this->Sale->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Sale->get_found_rows($search, $filters);
		$payments = $this->Sale->get_payments_summary($search, $filters);
		$payment_summary = $this->xss_clean(get_sales_manage_payments_summary($payments, $sales, $this));

		$data_rows = array();
		foreach ($sales->result() as $sale) {
			$data_rows[] = $this->xss_clean(get_sale_data_row($sale, $this));
		}

		if ($total_rows > 0) {
			$data_rows[] = $this->xss_clean(get_sale_data_last_row($sales, $this));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
	}

	public function item_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		if ($this->sale_lib->get_mode() == 'return' && $this->Sale->is_valid_receipt($receipt)) {
			// if a valid receipt or invoice was found the search term will be replaced with a receipt number (POS #)
			$suggestions[] = $receipt;
		}
		$suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => 0), TRUE));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}
	public function customer_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		$suggestions = $this->xss_clean($this->Customer->get_search_suggestions($this->input->get('term'), TRUE));

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

	public function suggest_search()
	{
		$search = $this->input->post('term') != '' ? $this->input->post('term') : 'a';

		$suggestions = $this->xss_clean($this->Sale->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	public function select_customer()
	{


		$customer_id = $this->input->post('customer');

		if ($this->Customer->exists($customer_id)) {
			$this->sale_lib->set_customer($customer_id);

			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;

			// apply customer default discount to items that have 0 discount
			if ($discount_percent == 0) {

				$this->sale_lib->apply_customer_discount($discount_percent);
			}
		}

		$this->_reload();
	}

	public function change_mode()
	{
		$stock_location = $this->input->post('stock_location');
		if (!$stock_location || $stock_location == $this->sale_lib->get_sale_location()) {
			$mode = $this->input->post('mode');
			$this->sale_lib->set_mode($mode);
			$dinner_table = $this->input->post('dinner_table');
			$this->sale_lib->set_dinner_table($dinner_table);
		} elseif ($this->Stock_location->is_allowed_location($stock_location, 'sales')) {
			$this->sale_lib->set_sale_location($stock_location);
		}

		$this->_reload();
	}

	public function set_comment()
	{
		$this->sale_lib->set_comment($this->input->post('comment'));
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
		//$this->_reload($data);
		// first get total payments made so far.
		// If the total payment made so far is equal to total amount for current purchase then just redirect to the print page.
		// else go back and continue loop until payment is complete.
		$totalPaidSoFar = 0;
		$total_due = $this->sale_lib->get_total();
		$pMade = $this->sale_lib->get_payments();
		if ($pMade['Cash']) {
			$totalPaidSoFar += $pMade['Cash']['payment_amount'];
		}
		if ($pMade['USSD']) {
			$totalPaidSoFar += $pMade['USSD']['payment_amount'];
		}
		if ($pMade['Bank Transfer']) {
			$totalPaidSoFar += $pMade['Bank Transfer']['payment_amount'];
		}
		if ($pMade['Wallet']) {
			$totalPaidSoFar += $pMade['Wallet']['payment_amount'];
		}
		if ($totalPaidSoFar >= $total_due) {
			$this->complete_receipt();
		} else {
			$this->_reload();
		}
	}
	public function add_payment_pill()
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

		//$this->_reload($data);
		$this->pill();
	}

	// Multiple Payments
	public function delete_payment($payment_id)
	{
		$this->sale_lib->delete_payment($payment_id);

		$this->_reload();
	}
	public function global_add()
	{
		$data = array();

		$item_id = $this->input->post('global_item');
		$data['item_inf'] = $this->sale_lib->global_search($item_id);

		$this->_reload($data);
	}
	public function global_transfer($item_id)
	{
		$data = array();
		$transfer_data = array();
		$items = $this->input->post('item_name');
		$quantity = parse_decimals($this->input->post('quantity'));

		$transfer_branch = $this->input->post('transfer_location');
		$request_branch = $this->sale_lib->get_sale_location();
		$status = 0;

		/*$data['sale_id_num'] = $this->Sale->save_transfer($items,$quantity,$request_branch,$transfer_branch,$status, $transfer_id);
				
				$data = $this->xss_clean($data);

				if($data['sale_id_num'] == -1)
				{
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				}*/
		$this->_reload($data);



		if ($this->Sale->save_transfer($items, $request_branch, $transfer_branch, $status, $transfer_id)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}
	}

	public function add()
	{
		$data = array();
		//$item_inf=array();

		foreach ($this->Supplier->get_all_pills()->result_array() as $row) {
			$pill_period[$this->xss_clean($row['reminder_value'])] = $this->xss_clean($row['reminder_name']);
		}
		$data['pill_period'] = $pill_period;
		$discount = 0;

		// check if any discount is assigned to the selected customer
		$customer_id = $this->sale_lib->get_customer();
		if ($customer_id != -1) {
			// load the customer discount if any
			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;
			if ($discount_percent != '') {
				$discount = $discount_percent;
			}
		}

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		if ($discount == 0) {
			$discount = $this->config->item('default_sales_discount');
		}

		$mode = $this->sale_lib->get_mode();
		$quantity = ($mode == 'return') ? -1 : 1;
		//$quantity = 1;
		$item_location = $this->sale_lib->get_sale_location();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$retail_or_whole_sale = $this->input->post('sale_type');
		$batches = array();
		$batch = array();

		foreach ($this->Module->get_all_batches($item_id_or_number_or_item_kit_or_receipt, $item_location)->result() as $module) {
			//$module->module_id = $this->xss_clean($module->module_id);
			//$module->grant = $this->xss_clean($this->Employee->has_grant($module->module_id, $person_info->person_id));
			$datetimenow = date('Y/m/d H:i:s');
			$datetimeexpire = $module->expiry;
			$datetimenowc = strtotime($datetimenow);
			$datetimeexpirec = strtotime($datetimeexpire);
			$des = $datetimeexpirec - $datetimenowc;
			$dd = $des / 86400;
			$expire_days = 90;

			if ($dd < $expire_days) {
				$check_card = 1;
				$check_expiry = $module->batch_no;
			}

			$batches[] = $check_expiry;
		}
		$batch = array_unique($batches);



		if ($mode == 'return' && $this->Sale->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt)) {
			$this->sale_lib->return_entire_sale($item_id_or_number_or_item_kit_or_receipt);
		} elseif ($this->Item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt)) {
			// Add kit item to order if one is assigned
			$pieces = explode(' ', $item_id_or_number_or_item_kit_or_receipt);
			$item_kit_id = $pieces[1];
			$item_kit_info = $this->Item_kit->get_info($item_kit_id);
			$kit_item_id = $item_kit_info->kit_item_id;
			$price_option = $item_kit_info->price_option;
			$stock_type = $item_kit_info->stock_type;
			$kit_print_option = $item_kit_info->print_option; // 0-all, 1-priced, 2-kit-only

			if ($item_kit_info->kit_discount_percent != 0 && $item_kit_info->kit_discount_percent > $discount) {
				$discount = $item_kit_info->kit_discount_percent;
			}

			$price = NULL;
			$print_option = 0; // Always include in list of items on invoice

			if (!empty($kit_item_id)) {
				// if(!$this->sale_lib->add_item($kit_item_id, $retail_or_whole_sale, $quantity, $item_location, $discount, $price, NULL, NULL, NULL, $print_option, $stock_type))
				if (!$this->sale_lib->add_item($kit_item_id, $quantity, $item_location, $discount, $price, NULL, NULL, NULL, $print_option, $stock_type)) {
					$data['error'] = $this->lang->line('sales_unable_to_add_item');
				} else {
					$data['warning'] = $this->sale_lib->out_of_stock($item_kit_id, $item_location);
				}
			}

			// Add item kit items to order
			$stock_warning = NULL;
			if (!$this->sale_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location, $discount, $price_option, $kit_print_option, $stock_warning)) {
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			} elseif ($stock_warning != NULL) {
				$data['warning'] = $stock_warning;
			}
		} else {
			$reference = 0;

			// if(!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt,  $retail_or_whole_sale, $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0',$qty_selected='retail',$reference))
			if (!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt,  $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0', $qty_selected = 'retail', $reference)) {
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			} else {
				$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location);
			}
		}
		if ($batch) {
			$data['warning'] = "BATCHES NO: " . implode(", ", $batch) . " will expire";
		}

		$this->_reload($data);
	}

	public function edit_item($item_id)
	{
		$data = array();
		foreach ($this->Supplier->get_all_pills()->result_array() as $row) {
			$pill_period[$this->xss_clean($row['reminder_value'])] = $this->xss_clean($row['reminder_name']);
		}
		$data['pill_period'] = $pill_period;

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');
		$mode = $this->sale_lib->get_mode();

		$description = $this->input->post('description');
		$itemid = $this->input->post('itemid');
		$item_info = $this->CI->Item->get_info_by_id_or_number($itemid);
		$qty_type = $this->input->post('qty_type');
		$serialnumber = $this->input->post('serialnumber');
		if ($qty_type == 'retail') {
			$price = parse_decimals($item_info->unit_price);
		} else {
			$price = parse_decimals($item_info->whole_price);
		}
		$line = $this->input->post('line');
		$item_location = $this->input->post('location');

		//$quantity = parse_decimals($this->input->post('quantity'));
		//$stockno = parse_decimals($this->input->post('stockno'));
		$stockno = $this->Item_quantity->get_item_quantity($item_info->item_id, $item_location)->quantity;

		$quantit = parse_decimals($this->input->post('quantity'));

		if ($qty_type == 'wholesale') {
			$quantit = (int) parse_decimals($this->input->post('quantity')) * $item_info->pack;
		}
		//if ($quantit > $stockno && $mode != 'return') {
		if ($quantit > $stockno) {
			$quantity = 1;
			$qty_type = 'retail';
			$data['warning'] = "Warning, Inputed Product Quantity is Insufficient,Reduce quantity to process sale or contact Admin to update inventory";
		} else {
			$quantity = parse_decimals($this->input->post('quantity'));
		}
		$discount = parse_decimals($this->input->post('discount'));


		//if we are in return mode, all quantity should be in negative
		if ($mode == 'return' && $quantity > 0) {
			$quantity = $quantity * -1;
		}
		if ($mode != 'return' && $quantity < 0) {
			$quantity = $quantity * -1;
		}
		if ($quantity == 0) {
			// $quantity = 1;
			// $data['warning'] = 'Quantity cannot be zero';
			$this->delete_item($item_id);
		}
		if ($this->form_validation->run() != FALSE) {
			$this->sale_lib->edit_item($line, $description, $serialnumber, $quantity, $discount, $price, $qty_type);
		} else {
			$data['error'] = $this->lang->line('sales_error_editing_item');
			$this->_reload($data);
		}

		//$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);

		$this->_reload($data);
		//redirect('sales');
	}
	public function edit_salepill_item($item_id)
	{
		$data = array();
		foreach ($this->Supplier->get_all_pills()->result_array() as $row) {
			$pill_period[$this->xss_clean($row['reminder_id'])] = $this->xss_clean($row['reminder_name']);
		}
		$data['pill_period'] = $pill_period;

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');



		$time_started = $this->input->post('time_started');
		$reminder_value = $this->input->post('reminder_value');
		$no_of_days = $this->input->post('no_of_days');
		$itemid = $this->input->post('itemid');
		$item_info = $this->CI->Item->get_info_by_id_or_number($itemid);
		$price = $this->input->post('price');
		$line = $this->input->post('line');
		$discount = 0;
		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno = parse_decimals($this->input->post('stockno'));
		$quantit = parse_decimals($this->input->post('quantity'));

		$item_location = $this->input->post('location');

		if ($this->form_validation->run() != FALSE) {
			$this->sale_lib->edit_salepill_item($line, $time_started, $reminder_value, $no_of_days, $discount, $price);
		} else {
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);

		$this->_reload($data);
	}


	public function add_sale_pill($item_number)
	{
		$data = array();
		//$item_inf=array();

		foreach ($this->Supplier->get_all_pills()->result_array() as $row) {
			$pill_period[$this->xss_clean($row['reminder_id'])] = $this->xss_clean($row['reminder_name']);
		}
		$data['pill_period'] = $pill_period;

		$discount = 0;

		// check if any discount is assigned to the selected customer
		$customer_id = $this->sale_lib->get_customer();
		if ($customer_id != -1) {
			// load the customer discount if any
			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;
			if ($discount_percent != '') {
				$discount = $discount_percent;
			}
		}

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		if ($discount == 0) {
			$discount = $this->config->item('default_sales_discount');
		}

		$mode = $this->sale_lib->get_mode();
		$quantity = ($mode == 'return') ? -1 : 1;
		$item_location = $this->sale_lib->get_sale_location();
		$item_id_or_number_or_item_kit_or_receipt = $item_number;
		$batches = array();
		$batch = array();





		if ($mode == 'return' && $this->Sale->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt)) {
			$this->sale_lib->return_entire_sale($item_id_or_number_or_item_kit_or_receipt);
		} elseif ($this->Item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt)) {
			// Add kit item to order if one is assigned
			$pieces = explode(' ', $item_id_or_number_or_item_kit_or_receipt);
			$item_kit_id = $pieces[1];
			$item_kit_info = $this->Item_kit->get_info($item_kit_id);
			$kit_item_id = $item_kit_info->kit_item_id;
			$price_option = $item_kit_info->price_option;
			$stock_type = $item_kit_info->stock_type;
			$kit_print_option = $item_kit_info->print_option; // 0-all, 1-priced, 2-kit-only

			if ($item_kit_info->kit_discount_percent != 0 && $item_kit_info->kit_discount_percent > $discount) {
				$discount = $item_kit_info->kit_discount_percent;
			}

			$price = NULL;
			$print_option = 0; // Always include in list of items on invoice

			if (!empty($kit_item_id)) {
				if (!$this->sale_lib->add_item($kit_item_id, $quantity, $item_location, $discount, $price, NULL, NULL, NULL, $print_option, $stock_type)) {
					$data['error'] = $this->lang->line('sales_unable_to_add_item');
				} else {
					$data['warning'] = $this->sale_lib->out_of_stock($item_kit_id, $item_location);
				}
			}

			// Add item kit items to order
			$stock_warning = NULL;
			if (!$this->sale_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location, $discount, $price_option, $kit_print_option, $stock_warning)) {
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			} elseif ($stock_warning != NULL) {
				$data['warning'] = $stock_warning;
			}
		} else {
			$reference = 1;
			if (!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0', $qty_selected = 'retail', $reference)) {
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			}
		}

		$this->_reload($data);
	}





	public function remove_customer()
	{
		$this->sale_lib->clear_giftcard_remainder();
		$this->sale_lib->clear_rewards_remainder();
		$this->sale_lib->delete_payment($this->lang->line('sales_rewards'));
		$this->sale_lib->clear_invoice_number();
		$this->sale_lib->clear_quote_number();
		$this->sale_lib->remove_customer();

		$this->_reload();
	}

	public function complete_receipt()
	{


		$this->complete();
	}

	public function complete()
	{


		$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['cart'] = $this->sale_lib->get_cart();
		$data['vat'] = $this->sale_lib->getTotalVat();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$branch_id = $employee_info->branch_id;
		$branch_info = $this->Employee->get_branchinfo($branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
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
		$totals = $this->sale_lib->get_totals();
		$data['total_vat'] = $totals['total_vat'];
		$data['initial_cost'] = $totals['subtotal'];
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
		if ($this->sale_lib->get_mode() != 'return') {
			$data['amount_change'] = $data['amount_due'] * -1;
		}
		$item_location = $this->sale_lib->get_sale_location();

		if ($this->sale_lib->is_invoice_mode() || $data['invoice_number_enabled'] == TRUE) {
			// generate final invoice number (if using the invoice in sales by receipt mode then the invoice number can be manually entered or altered in some way
			if ($this->sale_lib->is_sale_by_receipt_mode()) {
				$this->sale_lib->set_invoice_number($this->input->post('invoice_number'), $keep_custom = TRUE);
				$invoice_format = $this->sale_lib->get_invoice_number();
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
				$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $data["quote_number"], $data['payments'], $data['dinner_table'], $data['taxes'], $data['total'], $item_location);
				$data['sale_id'] = 'POS ' . $data['sale_id_num'];

				// Resort and filter cart lines for printing
				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);

				if ($data['sale_id_num'] == -1) {
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				} else {
					$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
					$this->sale_lib->set_auth_code(null);

					$this->load->view('sales/invoice', $data);
					$this->sale_lib->clear_all();
				}
			}
		} elseif ($this->sale_lib->is_quote_mode()) {
			$invoice_number = NULL;
			$quote_number = $this->sale_lib->get_quote_number();

			if ($quote_number == NULL) {
				// generate quote number
				$quote_format = $this->config->item('sales_quote_format');
				$quote_number = $this->token_lib->render($quote_format);
			}

			// TODO If duplicate quote then determine the number of employees and repeat until until success or tried the number of employees (if QSEQ was used).
			if ($this->Sale->check_quote_number_exists($quote_number)) {
				$data['error'] = $this->lang->line('sales_quote_number_duplicate');
				$this->_reload($data);
			} else {
				$data['invoice_number'] = $invoice_number;
				$data['quote_number'] = $quote_number;
				$data['sale_status'] = '1'; // Suspended

				$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $quote_number, $data['payments'], $data['dinner_table'], $data['taxes'], $data['total'] + $data['total_vat'] - $data['discount'], $item_location);

				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);



				$data['barcode'] = NULL;

				//				$this->suspend_quote($quote_number);
				$this->sale_lib->set_auth_code(null);
				$this->load->view('sales/quote', $data);
				$this->sale_lib->clear_mode();
				$this->sale_lib->clear_all();
			}
		} else {


			// Save the data to the sales table
			$data['sale_status'] = '0'; // Complete
			$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes'], $data['total'] + $data['total_vat'] - $data['discount'], $item_location);

			$data['sale_id'] = 'POS ' . $data['sale_id_num'];


			$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
			$data = $this->xss_clean($data);

			if ($data['sale_id_num'] == -1) {
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			} else {
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

				// Reload (sorted) and filter the cart line items for printing purposes


				//$data['cart'] = $this->get_filtered($this->sale_lib->get_cart_reordered($data['sale_id_num']));
				$data['cart'] = $this->get_filtered($this->sale_lib->get_cart());
				//echo '<pre>';
				//print_r($this->sale_lib->get_cart());
				//die;
				//added this line so item quantities can be dedudcted from the database once they have been sold.
				// $this->Sale->deduct_item_quantity_after_selling($data['sale_id_num']);
				$data['mode']  = $this->sale_lib->get_mode();
				$this->sale_lib->set_auth_code(null);
				$this->load->view('sales/receipt', $data);
				$this->sale_lib->clear_all();
			}
		}
	}
	public function complete_receipt_pill()
	{
		$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['cart'] = $this->sale_lib->get_pill();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		$employee_info = $this->Employee->get_info($employee_id);
		$branch_id = $employee_info->branch_id;
		$branch_info = $this->Employee->get_branchinfo($branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_address;
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
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
		$totals = $this->sale_lib->get_totals();
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

		if ($this->sale_lib->is_invoice_mode() || $data['invoice_number_enabled'] == TRUE) {
			// generate final invoice number (if using the invoice in sales by receipt mode then the invoice number can be manually entered or altered in some way
			if ($this->sale_lib->is_sale_by_receipt_mode()) {
				$this->sale_lib->set_invoice_number($this->input->post('invoice_number'), $keep_custom = TRUE);
				$invoice_format = $this->sale_lib->get_invoice_number();
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
				$this->pill();
			} else {
				$data['invoice_number'] = $invoice_number;
				$data['sale_status'] = '0'; // Complete

				// Save the data to the sales table
				$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $data["quote_number"], $data['payments'], $data['dinner_table'], $data['taxes']);
				$data['sale_id'] = 'POS ' . $data['sale_id_num'];

				// Resort and filter cart lines for printing
				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);

				if ($data['sale_id_num'] == -1) {
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				} else {
					$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
					$this->load->view('sales/invoice', $data);
					$this->sale_lib->clear_all();
				}
			}
		} elseif ($this->sale_lib->is_quote_mode()) {
			$invoice_number = NULL;
			$quote_number = $this->sale_lib->get_quote_number();

			if ($quote_number == NULL) {
				// generate quote number
				$quote_format = $this->config->item('sales_quote_format');
				$quote_number = $this->token_lib->render($quote_format);
			}

			// TODO If duplicate quote then determine the number of employees and repeat until until success or tried the number of employees (if QSEQ was used).
			if ($this->Sale->check_quote_number_exists($quote_number)) {
				$data['error'] = $this->lang->line('sales_quote_number_duplicate');
				$this->pill();
			} else {
				$data['invoice_number'] = $invoice_number;
				$data['quote_number'] = $quote_number;
				$data['sale_status'] = '1'; // Suspended

				$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $quote_number, $data['payments'], $data['dinner_table'], $data['taxes']);

				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);


				$data['barcode'] = NULL;

				//				$this->suspend_quote($quote_number);

				$this->load->view('sales/quote', $data);
				$this->sale_lib->clear_mode();
				$this->sale_lib->clear_all();
			}
		} else {
			// Save the data to the sales table
			$data['sale_status'] = '0'; // Complete
			$data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes']);

			$data['sale_id'] = 'POS ' . $data['sale_id_num'];

			$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
			$data = $this->xss_clean($data);

			if ($data['sale_id_num'] == -1) {
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			} else {
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

				// Reload (sorted) and filter the cart line items for printing purposes
				$data['cart'] = $this->get_filtered($this->sale_lib->get_cart_reordered($data['sale_id_num']));

				$this->load->view('sales/receipt', $data);
				$this->sale_lib->clear_all();
			}
		}
	}

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

	public function send_quote($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);

		$result = FALSE;
		$message = $this->lang->line('sales_invoice_no_email');

		if (!empty($sale_data['customer_email'])) {
			$to = $sale_data['customer_email'];
			$subject = $this->lang->line('sales_quote') . ' ' . $sale_data['quote_number'];

			$text = $this->config->item('invoice_email_message');
			$text = str_replace('$INV', $sale_data['invoice_number'], $text);
			$text = str_replace('$CO', 'POS ' . $sale_data['sale_id'], $text);
			$text = $this->_substitute_customer($text, (object) $sale_data);

			// generate email attachment: invoice in pdf format
			$html = $this->load->view('sales/quote_email', $sale_data, TRUE);
			// load pdf helper
			$this->load->helper(array('dompdf', 'file'));
			$filename = sys_get_temp_dir() . '/' . $this->lang->line('sales_quote') . '-' . str_replace('/', '-', $sale_data['quote_number']) . '.pdf';
			if (file_put_contents($filename, pdf_create($html)) !== FALSE) {
				$result = $this->email_lib->sendEmail($to, $subject, $text, $filename);
			}

			$message = $this->lang->line($result ? 'sales_quote_sent' : 'sales_quote_unsent') . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message, 'id' => $sale_id));

		$this->sale_lib->clear_all();

		return $result;
	}

	public function send_receipt($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);

		$result = FALSE;
		$message = $this->lang->line('sales_receipt_no_email');

		if (!empty($sale_data['customer_email'])) {
			$sale_data['barcode'] = $this->barcode_lib->generate_receipt_barcode($sale_data['sale_id']);

			$to = $sale_data['customer_email'];
			$subject = $this->lang->line('sales_receipt');

			$text = $this->load->view('sales/receipt_email', $sale_data, TRUE);

			$result = $this->email_lib->sendEmail($to, $subject, $text);

			$message = $this->lang->line($result ? 'sales_receipt_sent' : 'sales_receipt_unsent') . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message, 'id' => $sale_id));

		$this->sale_lib->clear_all();

		return $result;
	}

	private function _substitute_variable($text, $variable, $object, $function)
	{
		// don't query if this variable isn't used
		if (strstr($text, $variable)) {
			$value = call_user_func(array($object, $function));
			$text = str_replace($variable, $value, $text);
		}

		return $text;
	}

	private function _substitute_customer($text, $customer_info)
	{
		// substitute customer info
		$customer_id = $this->sale_lib->get_customer();
		if ($customer_id != -1 && $customer_info != '') {
			$text = str_replace('$CU', $customer_info->first_name . ' ' . $customer_info->last_name, $text);
			$words = preg_split("/\s+/", trim($customer_info->first_name . ' ' . $customer_info->last_name));
			$acronym = '';

			foreach ($words as $w) {
				$acronym .= $w[0];
			}
			$text = str_replace('$CI', $acronym, $text);
		}

		return $text;
	}

	private function _is_custom_invoice_number($customer_info)
	{
		$invoice_number = $this->config->config['sales_invoice_format'];
		$invoice_number = $this->_substitute_variables($invoice_number, $customer_info);

		return $this->sale_lib->get_invoice_number() != $invoice_number;
	}

	private function _is_custom_quote_number($customer_info)
	{
		$quote_number = $this->config->config['sales_quote_format'];
		$quote_number = $this->_substitute_variables($quote_number, $customer_info);

		return $this->sale_lib->get_quote_number() != $quote_number;
	}

	private function _substitute_variables($text, $customer_info)
	{
		$text = $this->_substitute_variable($text, '$YCO', $this->Sale, 'get_invoice_number_for_year');
		$text = $this->_substitute_variable($text, '$CO', $this->Sale, 'get_invoice_count');
		$text = $this->_substitute_variable($text, '$SCO', $this->Sale, 'get_suspended_invoice_count');
		$text = strftime($text);
		$text = $this->_substitute_customer($text, $customer_info);

		return $text;
	}

	private function _substitute_invoice_number($customer_info)
	{
		$invoice_number = $this->config->config['sales_invoice_format'];
		$invoice_number = $this->_substitute_variables($invoice_number, $customer_info);
		$this->sale_lib->set_invoice_number($invoice_number, TRUE);

		return $this->sale_lib->get_invoice_number();
	}

	private function _substitute_quote_number($customer_info)
	{
		$quote_number = $this->config->config['sales_quote_format'];
		$quote_number = $this->_substitute_variables($quote_number, $customer_info);
		$this->sale_lib->set_quote_number($quote_number, TRUE);

		return $this->sale_lib->get_quote_number();
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
			$data['first_name'] = $customer_info->first_name;
			$data['last_name'] = $customer_info->last_name;
			$data['customer_email'] = $customer_info->email;
			$data['customer_address'] = $customer_info->address_1;
			if (!empty($customer_info->zip) || !empty($customer_info->city)) {
				$data['customer_location'] = $customer_info->zip . ' ' . $customer_info->city;
			} else {
				$data['customer_location'] = '';
			}
			$data['customer_account_number'] = $customer_info->account_number;
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
				$data['customer_account_number']
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
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($sale_info['sale_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');


		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['total_vat'] = $totals['total_vat'];
		$data['subtotal'] = $totals['subtotal'];
		$data['initial_cost'] = $totals['subtotal'];
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
	public function set_role()
	{
		$role = $this->input->post('role');
		//$role_modules=$this->Module->get_all_modules_role($role);
		$this->sale_lib->set_modulecart('ewaiwo');
		//$this->load->view('employees/form', $data);
	}

	private function _reload($data = array())
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$balance_info = $this->CI->Item->get_balanceinfo($employee_id);
		$register = $balance_info->register_id;
		$this->sale_lib->set_balance_id($register);
		//$data['register_id'] = $this->sale_lib->get_balance_id();
		//$data['register_id'] = $register;
		//$this->sale_lib->set_modulecart('llio');
		$roles = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_all_roles()->result_array() as $row) {
			$roles[$this->xss_clean($row['id'])] = $this->xss_clean($row['role']);
		}
		$qtytypes = array('retail' => 'Retail', 'wholesale' => 'Wholesale',);
		//$this->sale_lib->set_modulecart(1);
		$special_module = $this->sale_lib->get_modulecart();
		$data['roles'] = $roles;
		$data['qtytypes'] = $qtytypes;
		$data['special_module'] = $special_module;
		//$item_inf = $this->CI->Item->global_search;
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['cart'] = $this->sale_lib->get_cart();
		$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);

		if ($this->config->item('invoice_enable') == '0') {
			$data['modes'] = array('sale' => $this->lang->line('sales_sale'), 'return' => $this->lang->line('sales_return'));
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
		$totals['discount_total'] = $totals['subtotal'] - $totals['discounted_subtotal'];
		$data['discount_approved'] = $this->sale_lib->get_auth_code() != null ? TRUE : FALSE;


		$data['total_discount'] = $totals['discount_total'];
		$data['total_vat'] = $totals['total_vat'];
		$data['initial_cost'] = $totals['subtotal'];
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
		$data['selected_payment_type'] = null;
		// $data['selected_payment_type'] = $this->sale_lib->get_payment_type();
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
	public function pill()
	{
		//$this->sale_lib->set_modulecart('llio');
		$pill_period = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_all_pills()->result_array() as $row) {
			$pill_period[$this->xss_clean($row['reminder_id'])] = $this->xss_clean($row['reminder_name']);
		}
		$data['pill_period'] = $pill_period;
		//$this->sale_lib->set_modulecart(1);
		$special_module = $this->sale_lib->get_modulecart();

		$data['special_module'] = $special_module;
		//$item_inf = $this->CI->Item->global_search;
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		$data['cart'] = $this->sale_lib->get_pill();
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
		$totals = $this->sale_lib->get_totals_pill();
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

		$this->load->view("sales/pill", $data);
	}
	public function add_pill()
	{
		$data = array();
		//$item_inf=array();

		$discount = 0;

		// check if any discount is assigned to the selected customer
		$customer_id = $this->sale_lib->get_customer();
		if ($customer_id != -1) {
			// load the customer discount if any
			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;
			if ($discount_percent != '') {
				$discount = $discount_percent;
			}
		}

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		if ($discount == 0) {
			$discount = $this->config->item('default_sales_discount');
		}

		//$mode = $this->sale_lib->get_mode();
		$period = 1;
		$no_of_days = 1;
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$time_started = date('Y-m-d H-m-s');
		$batches = array();
		$batch = array();





		if (!$this->sale_lib->add_item_pill($item_id_or_number_or_item_kit_or_receipt, $period, $no_of_days, $time_started)) {
			$data['error'] = $this->lang->line('sales_unable_to_add_item');
		} else {
			$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location);
		}


		$this->pill();
	}
	public function edit_pill($item_id)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');

		$period = $this->input->post('period');
		$no_of_days = $this->input->post('no_of_days');
		//$price = $this->input->post('price');
		//$price = $this->input->post('price');
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$time_started = $this->input->post('time_started');
		$price = parse_decimals($this->input->post('price'));
		$reminder_value = $this->input->post('reminder_value');


		if ($this->form_validation->run() != FALSE) {
			$this->sale_lib->edit_pill($item_id, $period, $no_of_days, $time_started);
		} else {
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}
		$this->pill();
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

	public function edit($sale_id)
	{
		$data = array();

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee) {
			foreach (get_object_vars($employee) as $property => $value) {
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$sale_info = $this->xss_clean($this->Sale->get_info($sale_id)->row_array());
		$data['selected_customer_name'] = $sale_info['customer_name'];
		$data['selected_customer_id'] = $sale_info['customer_id'];
		$data['sale_info'] = $sale_info;

		$data['payments'] = array();
		foreach ($this->Sale->get_sale_payments($sale_id)->result() as $payment) {
			foreach (get_object_vars($payment) as $property => $value) {
				$payment->$property = $this->xss_clean($value);
			}
			$data['payments'][] = $payment;
		}

		// don't allow gift card to be a payment option in a sale transaction edit because it's a complex change
		$data['payment_options'] = $this->xss_clean($this->Sale->get_payment_options(FALSE));
		$this->load->view('sales/form', $data);
	}

	public function delete($sale_id = -1, $update_inventory = TRUE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$sale_ids = $sale_id == -1 ? $this->input->post('ids') : array($sale_id);

		if ($this->Sale->delete_list($sale_ids, $employee_id, $update_inventory)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_deleted') . ' ' .
				count($sale_ids) . ' ' . $this->lang->line('sales_one_or_multiple'), 'ids' => $sale_ids));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_deleted')));
		}
	}

	public function save($sale_id = -1)
	{
		$newdate = $this->input->post('date');
		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);
		$sale_data = array(
			'sale_time' => $date_formatter->format('Y-m-d H:i:s'),
			'customer_id' => $this->input->post('customer_id') != '' ? $this->input->post('customer_id') : NULL,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'invoice_number' => $this->input->post('invoice_number') != '' ? $this->input->post('invoice_number') : NULL
		);

		// go through all the payment type input from the form, make sure the form matches the name and iterator number
		$payments = array();
		$number_of_payments = $this->input->post('number_of_payments');
		for ($i = 0; $i < $number_of_payments; ++$i) {
			$payment_amount = $this->input->post('payment_amount_' . $i);
			$payment_type = $this->input->post('payment_type_' . $i);
			// remove any 0 payment if by mistake any was introduced at sale time
			if ($payment_amount != 0) {
				// search for any payment of the same type that was already added, if that's the case add up the new payment amount
				$key = FALSE;
				if (!empty($payments)) {
					// search in the multi array the key of the entry containing the current payment_type
					// NOTE: in PHP5.5 the array_map could be replaced by an array_column
					$key = array_search($payment_type, array_map(function ($v) {
						return $v['payment_type'];
					}, $payments));
				}

				// if no previous payment is found add a new one
				if ($key === FALSE) {
					$payments[] = array('payment_type' => $payment_type, 'payment_amount' => $payment_amount);
				} else {
					// add up the new payment amount to an existing payment type
					$payments[$key]['payment_amount'] += $payment_amount;
				}
			}
		}

		if ($this->Sale->update($sale_id, $sale_data, $payments)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}
	}

	public function cancel()
	{
		$this->sale_lib->clear_all();
		$this->_reload();
	}

	public function discard_quote()
	{
		$suspended_id = $this->sale_lib->get_suspended_id();
		$this->sale_lib->clear_all();
		$this->Sale->delete_suspended_sale($suspended_id);
		$this->_reload();
	}

	public function suspend()
	{
		$dinner_table = $this->sale_lib->get_dinner_table();
		$cart = $this->sale_lib->get_cart();
		$payments = $this->sale_lib->get_payments();
		$employee = $this->Employee->get_logged_in_employee_info();
		$employee_id = $employee->person_id;
		$customer_id = $this->sale_lib->get_customer();
		$customer_info = $this->Customer->get_info($customer_id);
		$invoice_number = $this->_is_custom_invoice_number($customer_info) ? $this->sale_lib->get_invoice_number() : NULL;
		$quote_number = $this->sale_lib->get_quote_number();
		$comment = $this->sale_lib->get_comment();

		//get_total payment and save it in the sales tale
		$total = 0;
		foreach ($payments as $id => $value) {
			$total = floatval($total) + floatval($value['payment_amount']);
		}
		$item_location = $employee->branch_id;
		$sale_status = '1';

		$data = array();
		$sales_taxes = array();

		$data['mode'] = $this->sale_lib->get_mode();
		//don't suspend if on return mode
		$mode = $this->sale_lib->get_mode();
		if ($mode != 'sale') {
			$data['error'] = "Sorry, You cannot suspend sales in this mode.";
		} else {

			$save_resp = $this->Sale->save($sale_status, $cart, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, $sales_taxes, $total, $item_location);
			if ($save_resp == '-1') {
				$data['error'] = $this->lang->line('sales_unsuccessfully_suspended_sale');
			} else {
				$data['success'] = $this->lang->line('sales_successfully_suspended_sale') . ' - Suspended Sale ID = ' . $save_resp;
			}
			$this->sale_lib->clear_all();
		}


		// $data['current_suspended_sale_id'] = $save_resp; 
		//Initially I wanted to return the ID as a data param but displaying it on the notification is better.
		$this->_reload($data);
	}

	public function suspend_quote($quote_number)
	{
		$cart = $this->sale_lib->get_cart();
		$payments = $this->sale_lib->get_payments();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$customer_id = $this->sale_lib->get_customer();
		$customer_info = $this->Customer->get_info($customer_id);
		$dinner_table = $this->sale_lib->get_dinner_table();
		$invoice_number = $this->_is_custom_invoice_number($customer_info) ? $this->sale_lib->get_invoice_number() : NULL;
		$comment = $this->sale_lib->get_comment();
		$sale_status = '2'; // Suspend

		$data = array();
		$sales_taxes = array();
		if ($this->Sale->save($sale_status, $cart, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, $sales_taxes) == '-1') {
			$data['error'] = $this->lang->line('sales_unsuccessfully_suspended_sale');
		} else {
			$data['success'] = $this->lang->line('sales_successfully_suspended_sale');
		}
	}

	public function suspended()
	{
		$customer_id = $this->sale_lib->get_customer();
		$data = array();
		$data['suspended_sales'] = $this->xss_clean($this->Sale->get_all_suspended($customer_id));
		$data['dinner_table_enable'] = $this->config->item('dinner_table_enable');
		$this->load->view('sales/suspended', $data);
	}

	/*
	 * We will eventually drop the current set of "suspended" tables since suspended sales
	 * are now stored in the sales tables with a sale_status value of suspended.
	 */
	public function unsuspend()
	{
		$sale_id = $this->input->post('suspended_sale_id');
		$this->sale_lib->clear_all();

		if ($sale_id > 0) {
			//$items_test = $this->Sale->reupdate_stock_quantity($sale_id);
			//ite was not entered in the stock
			$this->sale_lib->copy_entire_sale($sale_id);
			$this->Sale->delete_suspended_sale($sale_id);
		} else {
			// This will unsuspended older suspended sales
			$sale_id = $sale_id * -1;
			$this->sale_lib->copy_entire_suspended_tables_sale($sale_id);
			$this->Sale_suspended->delete($sale_id);
		}

		$this->_reload();
	}

	public function check_invoice_number()
	{
		$sale_id = $this->input->post('sale_id');
		$invoice_number = $this->input->post('invoice_number');
		$exists = !empty($invoice_number) && $this->Sale->check_invoice_number_exists($invoice_number, $sale_id);
		echo !$exists ? 'true' : 'false';
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

	public function approve_discount()
	{
		$resp = array(
			'success'	=> FALSE,
			'message'	=> 'Pending'
		);
		$code = $this->input->post("code");
		if (!$code) {
			$resp['message'] = 'Please enter authorization code';
			echo json_encode($resp, true);
		} else {
			$employee = $this->Employee->get_employee_by_code($code);
			if (count($employee) > 0) {
				$this->sale_lib->set_auth_code($code);
				$resp['success'] = TRUE;
				$resp['message'] = "Authorization successful.";
				echo json_encode($resp, true);
			} else {
				$resp['message'] = 'Invalid Authorization Code!';
				echo json_encode($resp, true);
			}
		}
	}

	public function history()
	{
		$data = [];
		$data['table_headers'] = $this->xss_clean(get_sales_history_headers());
		$this->load->view("sales/history", $data);
	}

	public function history_data()
	{
		$search			= $this->input->get('search');
		$limit 			= $this->input->get('limit');
		$offset 		= $this->input->get('offset');
		$sort 			= $this->input->get('sort') ? $this->input->get('sort') : 'sale_id';
		$order 			= $this->input->get('order');
		$start_date		= $this->input->get('start_date');
		$end_date		= $this->input->get('end_date');

		$inputs = array('start_date' => $start_date, 'end_date' => $end_date, 'search' => $search, 'limit' => $limit, 'offset' => $offset, 'sort' => $sort, 'order' => $order);

		$data		= $this->Sale->get_sales_history($inputs);
		$total		= $this->Sale->get_sales_history_count($inputs);
		$data_rows	= array();
		foreach ($data->result() as $d) {
			$status = "Pending";
			if ($d->sale_status == 0) {
				$status = "Complete";
			}
			$tmp = array('sale_id' => $d->sale_id, 'employee' => $d->employee, 'sales_amount' => $d->sales_amount, 'timestamp' => $d->timestamp, 'sale_status' => $status, 'total_items' => $d->total_items);
			$data_rows[] = $this->xss_clean(get_sales_history_row($tmp, $this));
		}
		echo json_encode(array('total' => $total, 'rows' => $data_rows));
	}

	public function history_view($receiptNumber = -1)
	{
		if ($receiptNumber == -1) {
			$this->history();
		}
		$saleData	= $this->Sale->get_sales_history_by_receipt_number($receiptNumber)->result();
		$items		= $this->Sale->get_sales_history_items_by_receipt_number($receiptNumber)->result();
		$payments	= $this->Sale->get_sales_history_payment_methods($receiptNumber)->result();

		$payments_types = [];
		$n = count($payments);
		foreach ($payments as $i => $val) {
			if (!in_array($val->payment_type, $payments_types)) {
				$payments_types["$val->payment_type"] = $val->payment_amount;
			} else {
				$payments_types["$val->payment_type"] += $val->payment_amount;
			}
		}

		$data = array(
			'meta'		=> $saleData[0],
			'items'		=> $items,
			'payments'	=> $payments_types
		);
		$this->load->view("sales/history_view", $data);
	}

	public function sales_history_reprint($receiptNumber = -1)
	{
		if ($receiptNumber == -1) {
			$this->history();
		}
		$saleData	= $this->Sale->get_sales_history_by_receipt_number($receiptNumber)->result();
		$items		= $this->Sale->get_sales_history_items_by_receipt_number($receiptNumber)->result();
		$payments	= $this->Sale->get_sales_history_payment_methods($receiptNumber)->result();

		$payments_types = [];
		$n = count($payments);
		foreach ($payments as $i => $val) {
			if (!in_array($val->payment_type, $payments_types)) {
				$payments_types["$val->payment_type"] = $val->payment_amount;
			} else {
				$payments_types["$val->payment_type"] += $val->payment_amount;
			}
		}

		$data = array(
			'meta'		=> $saleData[0],
			'items'		=> $items,
			'payments'	=> $payments_types
		);

		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$this->load->view("sales/history_print", $data);
	}
}
