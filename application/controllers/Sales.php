<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Sales extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('sales');

//		exit();
		$this->load->library('sale_lib');
		$this->load->library('tax_lib');
		$this->load->library('barcode_lib');
		$this->load->library('email_lib');
		$this->load->library('token_lib');
		$this->load->library('audit_lib');
	}

	public function check_stock()
	{
		$today = date("Y-m-d");
		$lastDay = date("t", strtotime($today));
		$firstDay = 03;

		$dayOfToday = explode("-", $today);

//		if ($lastDay == $dayOfToday[2]) {
//			//record closing stock
//			$this->Item->record_stock(date("m"), date("Y"), false);
//		} else
		    if ($firstDay == $dayOfToday[2]) {
			//record opening stock
			$this->Item->record_stock(date("m"), date("Y"), true);
		}
	}

	public function index()
	{

		$this->check_stock();

		// $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		// if (!$this->Sale->register_exists($employee_id)) {
		// 	$this->open_register();
		// } else {

		$this->_reload();
		// }
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
			print('jude');
			die();
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
		$data['filters'] = $this->Sale->get_payment_options();
		// if ($this->config->item('invoice_enable') == TRUE) {
		// 	$data['filters'] = array(
		// 		'only_cash' => $this->lang->line('sales_cash_filter'),
		// 		'only_check' => $this->lang->line('sales_check_filter'),
		// 		//'only_invoices' => $this->lang->line('sales_invoice_filter')
		// 	);
		// } else {
		// 	$data['filters'] = array(
		// 		'only_cash' => $this->lang->line('sales_cash_filter'),
		// 		'only_check' => $this->lang->line('sales_check_filter'),
		// 		'only_pos' => 'POS',
		// 		'only_transfer' => 'Transfer',

		// 	);
		// }

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

		$employee = $this->Employee->get_logged_in_employee_info();
		$employee_id = "all";
		if ($employee->role == 5) {
			$employee_id = $employee->person_id;
		}

		$filters = array(
			'sale_type' => 'all',
			'location_id' => 'all',
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'is_valid_receipt' => $this->Sale->is_valid_receipt($search),
			'employee_id' => $employee_id
		);
		$filters = array_merge($filters, $this->Sale->get_payment_options());

		// check if any filter is set in the multiselect dropdown

		$filledup = array_fill_keys($this->input->get('filters'), TRUE); //will get the array of the selected filters only and assign true to them taking them as keys
		$filters = array_merge($filters, $filledup); //if filledup has same keys at some point as filters,the filledup value will be used to replace the other one


		$found_items = $this->Sale->find($search, $filters, 0, $offset, $sort, $order)->result();

		$sales = array_slice($found_items, $offset, $limit);
		$total_rows = count($found_items);
		$payments = array(
			"CASH" => 0,
			"POS" => 0,
			"WALLET" => 0,
			"TRANSFER" => 0,
			"CHECK" => 0,
		); //$this->Sale->get_payments_summary($search, $filters);

		//$payment_summary = $this->xss_clean(get_sales_manage_payments_summary($payments, $sales, $this));


		$data_rows = array();
		$last_row = array(
			'discount' => 0,
			'vat' => 0,
			'amount_due' => 0,
			'amount_tendered' => 0,
			'change_due' => 0
		);

		foreach ($sales as $sale) {
			//fetch payment using sale_id
			$ps = $this->Sale->find_payments($sale->sale_id);
			$pays = $ps->result();
			//check whether the payment qualify what is in the filters(pos,cash, etc if they're set)
			//if the it doesn't qualify, the sale is not allowed, then break out the main sales foreach

			if (!$this->Sale->check_payment_type($pays, $filters)) {

				$total_rows--;
				continue;
			}



			$payment_types = "";
			$total_pay = 0;
			foreach ($pays as $pay) {

				$total_pay += $pay->payment_amount;
				$payment_type = strtoupper($pay->payment_type);
				if ($payment_types) $payment_types .= ','; //separate the types with comma

				$payment_types .=   $pay->payment_type;
				if (isset($payments[$payment_type])) {
					//already inserted.
					$payments[$payment_type] += $pay->payment_amount;
				} else {
					$payments[$payment_type] = $pay->payment_amount;
				}
			}
			//update the total_amount paid and change_due bcose the one from db is wrong
			$sale->total_amount_paid = $total_pay;
			$sale->change_due = $total_pay - $sale->total_amount_due;

			//check if there is change due here, if yes, remove it from cash
			if ($sale->change_due > 0) {
				if (isset($payments['CASH'])) {
					$payments['CASH'] -= $sale->change_due;
				}
			}

			$data_rows[] = $this->xss_clean(get_sale_data_row($sale, $this, $payment_types));

			//store for last row
			$last_row['discount'] += $sale->total_discount;
			$last_row['vat'] += $sale->total_vat;
			$last_row['amount_due'] += $sale->total_amount_due;
			$last_row['amount_tendered'] += $sale->total_amount_paid;
			$last_row['change_due'] += $sale->change_due;
		}

		//for last row
		$data_rows[] = array(
			'sale_id' => '-',
			'sale_time' => '<b>Total</b>',
			'customer_name' => '-',
			'discount' => '<b>' . to_currency($last_row['discount']) . '<b>',
			'vat' => '<b>' . to_currency($last_row['vat']) . '<b>',
			'amount_due' => '<b>' . to_currency($last_row['amount_due']) . '</b>',
			'amount_tendered' => '<b>' . to_currency($last_row['amount_tendered']) . '</b>',
			'change_due' => '<b>' . to_currency($last_row['change_due']) . '</b>',
			'payment_type' => '-'
		);

		$payment_summary = get_sales_payments_summary($payments, $this);



		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
	}

    public function receipt_search(){

    }
	public function item_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		if ($this->sale_lib->get_mode() == 'return' && $this->Sale->is_valid_receipt($receipt)) {
			// if a valid receipt or invoice was found the search term will be replaced with a receipt number (POS #)
			$suggestions[] = $receipt;
		}
//		$suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => 0), TRUE));
        $suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => 0), TRUE,1000,true));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		// $suggestions = $this->xss_clean($suggestions);
        if(empty($suggestions)){
            $suggestions[] = ['value'=>null,'label'=>'No items found'];
        }

		echo json_encode($suggestions);
	}
	public function customer_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		$suggestions = $this->xss_clean($this->Customer->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function supplier_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$search = $this->input->post('term') != '' ? $this->input->post('term') : 'a';

		$suggestions = $this->xss_clean($this->Sale->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	public function select_customer($aj_req = null)
	{
		$customer_id = $this->input->post('customer');
		if($aj_req != null){
            $this->sale_lib->set_customer($customer_id);
            $customer_info = $this->Customer->get_info($customer_id);
            echo json_encode(['customer_details'=>$customer_info]);
            exit();
        }else{
            if ($this->Customer->exists($customer_id)) {
                $this->sale_lib->set_customer($customer_id);
                // $this->sale_lib->recheck_items();
            }

		    $this->_reload();
        }


	}

	public function change_mode()
	{
		$data = array();
		$stock_location = $this->input->post('stock_location');
		if (!$stock_location || $stock_location == $this->sale_lib->get_sale_location()) {
			$mode = $this->input->post('mode');
			$this->sale_lib->set_mode($mode);
			$dinner_table = $this->input->post('dinner_table');
			$this->sale_lib->set_dinner_table($dinner_table);
		} elseif ($this->Stock_location->is_allowed_location($stock_location, 'sales')) {
			$this->sale_lib->set_sale_location($stock_location);
		}
		$items = $this->sale_lib->get_cart();
		$mode = $this->sale_lib->get_mode();
		foreach ($items as $index => &$item) {
			$stockno = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
			$item_info = $this->CI->Item->get_info_by_id_or_number($item['item_id']);

			if ($mode == 'sale') {
				if ($stockno == 0) {
					unset($items[$index]); //remove the item from the card
					$data['warning'] = "Out of stock items are removed from the cart.";
				} elseif ($item['quantity'] > $stockno && $item['qty_selected'] == 'retail') {
					$item['quantity'] = (int) $stockno; //assign all quantity to the sale
				} elseif (($item['quantity'] * $item_info->pack) > $stockno && $item['qty_selected'] == 'wholesale') {
					//get the integer division of the qty by pack
					$qty = floor($stockno / $item_info->pack);
					if ($qty > 0) {
						$item['quantity'] = $qty;
					} else {
						unset($items[$index]); //remove the item from the card
						$data['warning'] = "Items that has insufficient quantity are removed from the cart";
					}
				}
			}
		}
		$this->sale_lib->set_cart($items);

		$this->_reload($data);
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
        $e_info = $this->Employee->get_logged_in_employee_info();
        $branch_extra_config = $this->Appconfig->get_extra_config(['company_id'=>$e_info->branch_id,'company_branch_id'=>$e_info->branch_id]);

        $finalize_sale = true;
		$payment_type = $this->input->post('payment_type');

		$lower_sales_auth_code = $this->input->post('l_auth_code');

		$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required|callback_numeric');

		if ($this->form_validation->run() == FALSE) {
		    $finalize_sale = false;
			$data['error'] = $this->lang->line('sales_must_enter_numeric');
		} else {

			//cash,pos,transfer,check,wallet
			//check for eligibility if wallet payment
			//1. credit limit not exceeded. if part of credit limit is remaining but cant cover payment, use the one remaining and show sales board so he(the customer) can use another payment method to complete the payment for the sales
			//2. whether customer is staff . and mind the logic here since it's different from that of normal customers
			//3. 
			$continue_wallet_sales = false;


			$amount_tendered = $this->input->post('amount_tendered');
			//get the customer if exists
			$customer_id = $this->sale_lib->get_customer(); //-1 will be returned if not existing


			//check if this sale already has some wallet sales added previously
			$current_sales_credit = 0;
			$pays = $this->sale_lib->get_payments();
			if (isset($pays['Wallet'])) {
				$current_sales_credit = $pays['Wallet']['payment_amount'];
			}
			//Wallet sales needs extra business logic
			if ($customer_id > 0  && $payment_type == "Wallet") {
				$customer_info = $this->Customer->get_info($customer_id);

				//for normal staff
				if ($customer_info->staff) {
					//staff logic is periodic
					//so get the total amount of goods that this staff has bought on credit this months
					$credit = $this->Sale->get_thismonth_credit($customer_id);

					//check if the credit is exhausted
					//Notice the following about staff.
					//their credit limit decreases as they purchase more good on credit
					//when make an eligible credit purchase, their wallet wont be made to reflect the credit, since all eligible credit purchases are not repayable. hence credit purchases are forgotten once the month is over unlike normal customers
					//they can preload their wallet and use it for next purchases when they don't have credit benefit left but wish to use wallet type


					$new_credit_limit = $customer_info->credit_limit - $credit - $current_sales_credit;

					//
					$qualified_amount = bcadd($new_credit_limit, $customer_info->wallet);
				} else {
					//for normal customers
					//first, add the current wallet balance to the credit limit of the customer
					//this is the amount that this customer can use to buy from at the moment.
					$qualified_amount = bcadd($customer_info->wallet, $customer_info->credit_limit);
					$qualified_amount = $qualified_amount - $current_sales_credit; //remove wallet payment that may have been selected previous in the current sale
				}

				if ($qualified_amount > 0) {
					//check if this $qualified_amount can offset all the current tendered_amount
					if ($qualified_amount < $amount_tendered) {
						//change the amount_tendered to the remaining $qualified_amount
						$amount_tendered = $qualified_amount;
						$data['warning'] = "Note: Only part of the payment was offset using wallet since credit limit is already reached."; //but should still showing a warning 
						//message to inform the officer(casheir ) that selected tendered amount was not fully paid for using the current payment method
					}
					$continue_wallet_sales = true; //payment should be added, though not complete 
				} else {

					if ($payment_type == "Wallet") {
						$data['warning'] = "Credit limit already exhausted.";
					}
				}
			} else {

				if ($payment_type == "Wallet") {
					$data['warning'] = "You must select customer before using Wallet payment method";
				}

//				if(!empty($branch_extra_config)){
//                    if($customer_id <= 0 && $branch_extra_config[0]->customer_details_mandated==1){
//                        $finalize_sale = false;
//                        $data['error'] = "You must select customer responsible for this sale";
//                    }
//                }
			}

			if ($continue_wallet_sales || $payment_type != "Wallet") {
				//if you want to eliminate change due from the sales system, check that the 
				//amount tendered is equal to the amount supposed to be paid
				$this->sale_lib->add_payment($payment_type, $amount_tendered);
			}
		}
		//$this->_reload($data);
		// first get total payments made so far.
		// If the total payment made so far is equal to total amount for current purchase then just redirect to the print page.
		// else go back and continue loop until payment is complete.
		$totalPaidSoFar = 0;
		$total_due = $this->sale_lib->get_total(); //total_bought - discount + vat
		$pMade = $this->sale_lib->get_payments();
		if ($pMade['Cash']) {
			$totalPaidSoFar += $pMade['Cash']['payment_amount'];
		}
		if ($pMade['POS']) {
			$totalPaidSoFar += $pMade['POS']['payment_amount'];
		}
		if ($pMade['Transfer']) {
			$totalPaidSoFar += $pMade['Transfer']['payment_amount'];
		}
		if ($pMade['Check']) {
			$totalPaidSoFar += $pMade['Check']['payment_amount'];
		}
		if ($pMade['Wallet']) {
			$totalPaidSoFar += $pMade['Wallet']['payment_amount'];
		}
//        echo "Total paid: ".round($totalPaidSoFar)." Total due: ".round($total_due);
//        exit();
		if ((round($totalPaidSoFar,-1) >= round($total_due,-1)) && $finalize_sale) {
			// $this->sale_lib->clear_all();

			$this->complete_receipt();
			// $this->sale_lib->clear_all();
		} else {
			$this->_reload($data);
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


		$sale_id = $this->Sale->save_transfer($items, $request_branch, $transfer_branch, $status, $transfer_id = false);
		if ($sale_id > 0) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}
	}

	public function check_receipt()
	{
		$this->load->view('sales/check_receipt', array());
	}

	public function view_receipt()
	{
		$receipt =  !empty($this->input->post('receipt')) ? $this->input->post('receipt') : -1;
		$data = array();

		$exists = $this->Sale->exists($receipt);

		if ($receipt != -1 && $exists) {
			redirect('/sales/receipt/' . $receipt);
		} else {
			$data['message'] = 'Invalid Sale Receipt NO';
			$this->load->view('sales/check_receipt', $data);
		}
	}
	public function register_for_returns(){
	    $item_line = $this->input->get('line');
	    $type = $this->input->get('type');
	    if($item_line != null){
	        if($type == 1){
                $this->sale_lib->register_return($item_line);
            }else{
                $this->sale_lib->remove_registered($item_line);
            }
        }
	    echo json_encode(['registered'=>$this->sale_lib->get_registered_returns()]);
    }
    public function fetch_sale_items(){
	    $p_sale_id = $this->input->post('item');
	    $p_sale_id = str_replace(' ','',$p_sale_id);
	    $sale_id = str_replace('pos','',$p_sale_id);

        $data = $this->_load_sale_data($sale_id,true);

        $data['s_id'] = $sale_id;
        $data['return_approved'] = $this->sale_lib->get_auth_code(true) != null;
        $this->_reload($data);
    }
	public function add($a_req = 0)
	{
		$data = array();
		//$item_inf=array();

        $aj_req = $a_req == 1;
		$data['pill_period'] = 0;
		$discount = 0;


		if($aj_req){
		    $mode = $this->input->post('sale_mode');
		    $this->session->set_userdata('aj_req',true);
        }else{
            $mode = $this->sale_lib->get_mode();
        }
		$quantity = ($mode == 'return') ? -1 : 1;
		//$quantity = 1;
		$item_location = $this->sale_lib->get_sale_location();

		//this could be item_id (if searched manually) or barcode(if searched using barcode scanner)
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		//use $item_id_or_number_or_item_kit_or_receipt to get the item_id
        $item_identifier_type_arr = explode(' ',$this->input->post('item'));
		$is_id = true;
		if(count($item_identifier_type_arr) > 1){
			$is_id = false;
			$item_id_or_number_or_item_kit_or_receipt = end($item_identifier_type_arr);
			// echo "it sent";
			// var_dump($item_identifier_type_arr);
			// exit();
		}

		//use $item_id_or_number_or_item_kit_or_receipt to get the item_id
		// echo $item_id_or_number_or_item_kit_or_receipt."<br>";
		// var_dump($this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt));
		// exit();
        if($aj_req){
            $item = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt,$aj_req,$is_id);
        }else{
            $item = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt,false,$is_id);
        }
        $item_id = $item->item_id;

		$item_id_or_number_or_item_kit_or_receipt = $item_id;

//		$retail_or_whole_sale = $this->input->post('sale_type');
		$batches = array();
		$batch_expiry = $this->Sale->get_expired_item($item_id);
		$expired = 0;
		if(count($batch_expiry) > 0){
		    $data['expired'] = $batch_expiry;
		    foreach ($batch_expiry as $expiry){
		        $expired += $expiry->quantity;
            }
        }
		$in_stock = $this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
//		if($in_stock)

		if(!$aj_req){
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

        }
		$batch = array_unique($batches);

		if (!empty($batch)) {
			$data['warning'] = "BATCHES NO: " . implode(", ", $batch) . " for $item->name will expire or already expired";

		}

		$reference = 0;

		// if(!$this->sale_lib ->add_item($item_id_or_number_or_item_kit_or_receipt,  $retail_or_whole_sale, $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0',$qty_selected='retail',$reference))
		if ($mode == 'sale') { //allow for returns even if quantity is 0
			if ($in_stock > 0) {
				if (!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt,  $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0', $qty_selected = 'retail', $reference)) {
					$data['error'] = $this->lang->line('sales_unable_to_add_item');
				}
//				else {
//					$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location);
//				}
			} else {
				$data['warning'] = "Product item is out of stock";
			}
//            var_dump($data['warning']);
//            exit();
		} else {
			//for returns
			$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt,  $quantity, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0', $qty_selected = 'retail', $reference);
		}

		$this->_reload($data,$aj_req);
	}

	public function check_expiring_batches(){

    }
	public function add_price_check()
	{

		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		//use $item_id_or_number_or_item_kit_or_receipt to get the item_id
		$item_name = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt)->name;
		$price = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt)->unit_price;

		$data = array();
		$data['name'] = $item_name;
		$data['price'] = to_currency($price);
		// return print_r($data);


		// $this->Sale_lib->set_price_check($item_name);
		// return print_r($item_name);
		// 5017007067412

		$this->_reload_price_check($data);
	}

	private function _reload_price_check($data)
	{
		// $data = array();
		// $name = $this->Sale_lib->get_price_check();
		// $data['name'] = $name;

		// return print_r($item_name);

		// return print_r($data);

		$this->load->view("items/check_price", $data);
	}



	public function edit_item($item_id,$aj_req = 0)
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
        $batch_no = $this->input->post('batch_no');


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
        $num_expired = $this->Sale->get_total_expired_item($item_info->item_id);

		$quantit = parse_decimals($this->input->post('quantity'));

		if ($qty_type == 'wholesale') {
			$quantit = (int) parse_decimals($this->input->post('quantity')) * $item_info->pack;
		}
		$num_avail = $stockno - $num_expired;
		//if ($quantit > $stockno && $mode != 'return') {
		if ($quantit > $num_avail && $mode != 'return') { //wholesale_issue jude
			$quantity = 1;
			$qty_type = 'retail';
			$price = parse_decimals($item_info->unit_price);
			$data['warning'] = "Warning, inputed product quantity available for sale is Insufficient, Reduce quantity to process sale or contact admin to update inventory. $num_avail in total is available";
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
			$this->sale_lib->edit_item($line, $description, $serialnumber, $quantity, $discount, $price, $qty_type,$batch_no);
//            if($aj_req == 1){
//                echo json_encode(['message'=>"Updated"]);
//                exit();
//            }
		} else {
			$data['error'] = $this->lang->line('sales_error_editing_item');
			if($aj_req == 1){
			    echo json_encode(['error'=>$data['error']]);
			    exit();
            }
			$this->_reload($data);
		}

		//$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);

		$this->_reload($data,$aj_req);
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
		$time_ended = $this->input->post('time_ended');
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
			$this->sale_lib->edit_salepill_item($line, $time_started, $time_ended, $reminder_value, $no_of_days, $discount, $price);
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
			if ($discount_percent > 0) {
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
		$this->sale_lib->delete_payment("Wallet");
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
        $totals = $this->sale_lib->get_totals();
		$s_mode =$this->sale_lib->get_mode();
        $e_info = $this->Employee->get_logged_in_employee_info();
        $branch_extra_config = $this->Appconfig->get_extra_config(['company_id'=>$e_info->branch_id,'company_branch_id'=>$e_info->branch_id]);
        if($totals['total'] <= 0 && $s_mode !== 'return'){
            $data['error'] = "Sales amount must be greater than 0";
        }
        if(!empty($branch_extra_config)){
            $customer_id = $this->sale_lib->get_customer();
            if($branch_extra_config[0]->minimum_sale_value > 0){
                $lower_sales_auth_code = $this->input->post('l_auth_code');
                $min_val = $branch_extra_config[0]->minimum_sale_value;
                if($lower_sales_auth_code == null){
                    $data['error'] = "You must provide authorization to make sales lower than the minimum value of $min_val";
                    $data['lower_sale_auth_required'] = true;
//                    $this->_reload($data);
                }elseif ($lower_sales_auth_code != 'p@kis$'){
                    $data['error'] = "Invalid authorization code for sale lower than the minimum value of $min_val";
                    $data['lower_sale_auth_required'] = true;
//                    $this->_reload($data);
                }
            }
            if($branch_extra_config[0]->customer_details_mandated > 0 && $customer_id <= 0){
                $data['error'] = "You must provide customer's details for this sale.";
//                $this->_reload($data);
            }

        }

        if(isset($data['error']) && $s_mode != 'return'){
//            echo "I got here";
            $this->_reload($data);
        }else{

            $data['cart'] = $this->sale_lib->get_cart();
            if($s_mode == 'return'){
                $returned_sale = $this->session->userdata('returned_sales');
                $eligible_returns = $this->Sale->eligible_returns($returned_sale);

                $registered_returns = $this->sale_lib->get_registered_returns();
                if(isset($eligible_returns) && count($eligible_returns) > 0){
                    $returnables = [];
//                    var_dump($eligible_returns);
//                    exit();
                    foreach ($eligible_returns as $returnable){
                        $returnables[$returnable->item_id] = $returnable->quantity_purchased;
                    }
                    foreach ($data['cart'] as $cart_item) {
//                        var_dump($data['cart']);
//                        exit();
                        $datum = (object) $cart_item;
//                        $cc = 1;
                        if(!array_key_exists($datum->item_id,$returnables)){
//                            $coutn = count($returnables);
//                            $coutn1 = count($data['cart']);
                            $data['error'] = "Invalid item $datum->name on the return list!";
                            break;
                        }
                        if(abs($returnables[$datum->item_id]) < abs($datum->quantity)){
                            $qty = $returnables[$datum->item_id];
                            $data['error'] = "Invalid item quantity for $datum->name on the return list! maximum should be $qty";
                            break;
                        }
                        if($datum->batch_no !== null){
                            if($this->Sale->check_return_batch($datum->item_id,$datum->batch_no,$e_info->branch_id)){
                                $data['error'] = "Batch returned for $datum->name does not exist in this store $datum->item_id loc: $e_info->branch_id b: $datum->batch_no";
                                break;
                            }
                        }
                    }

                    if(isset($data['error'])){
//                        echo "I got here mode return inside error";
                        $this->_reload($data);
//                        exit();
                    }
                //    echo "I got here mode return after error";
                }
            }


            if(!isset($data['error'])){
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


                $data['discount'] = $this->sale_lib->get_discount();
                $data['payments'] = $this->sale_lib->get_payments();

                // Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
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
                if ($s_mode != 'return') {
                    $data['amount_change'] = $data['amount_due'] * -1;
                }
                $item_location = $this->sale_lib->get_sale_location();

                // Save the data to the sales table
                $data['sale_status'] = '0'; // Complete. not suspended sales
                $sale_type = $s_mode == 'return' ? 1 : 0;
                $data['sale_id_num'] = $this->Sale->save($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes'], $data['total'] + $data['total_vat'] - $data['discount'], $item_location,$sale_type);

				/* make array of sky and qty and post data to wp */
				$up_qty = [];
				if(!empty($data['cart'])){
					foreach ($data['cart'] as $al){
						$qty = 0;
						$get_qty = $this->Item->get_item_qty($al['item_id']);
						if( isset($get_qty['qty']) && $get_qty['qty'] > 0 ){ $qty = $get_qty['qty']; }
						$up_qty[] = ['sku'=>$al['item_number'], 'quantity'=>(int)$qty];
					}
				}
				
				if( !empty($up_qty) ){
					$this->load->library('External_calls');
					$url = WOO_BASE_URL.'/quantity';
					$response =  External_calls::makeRequest($url,$up_qty,'POST');
				}
				/* end */
                if($s_mode == 'return'){
                    $data['sale_id'] = 'ROS ' . $data['sale_id_num'];
                }else{
                    $data['sale_id'] = 'POS ' . $data['sale_id_num'];
                }

                $data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
//            $data = $this->xss_clean($data);

                if ($data['sale_id_num'] == -1) {
                    $data['error_message'] = $this->lang->line('sales_transaction_failed');
                    $this->_reload($data);
                } else {
                    $data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

                    // Reload (sorted) and filter the cart line items for printing purposes


                    //$data['cart'] = $this->get_filtered($this->sale_lib->get_cart_reordered($data['sale_id_num']));
                    if ($s_mode != 'return') {
                        $this->audit_lib->add_log('sale', 'Performed Sale with Receipt No. ' . $data["sale_id"]);
                    } else {
                        $this->audit_lib->add_log('sale', 'Performed a Sale Return with Receipt No. ' . $data["sale_id"]);
                    }
                    // $this->audit_lib->add_log('sale', 'Performed Sale with Receipt No. ' . $data["sale_id"]);
//                $this->sale_lib->clear_all();
                    $data['cart'] = $this->get_filtered($this->sale_lib->get_cart());

                    $data['mode']  = $s_mode;
                    if($s_mode =='return'){
                        $this->sale_lib->set_auth_code(null,true);
                    }else{
                        $this->sale_lib->set_auth_code(null);
                    }

                    $this->sale_lib->clear_all();
                    $this->load->view('sales/receipt', $data);

                }
            }
        }


		// $this->sale_lib->clear_all();
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

			if (isset($customer_info->company_id) && $customer_info->company_id > 0) {
				$company_info = $this->Customer->get_company_info($customer_info->company_id);
				$data['company_name'] = $company_info->company_name;
				$data['company_discount'] = $company_info->discount;
				$data['company_wallet'] = $company_info->wallet;
				$data['company_credit'] = $company_info->credit_limit;
				$data['company_markup'] = $company_info->markup;
			}

			$data['first_name'] = $customer_info->first_name;
			$data['last_name'] = $customer_info->last_name;
			$data['customer_email'] = $customer_info->email;
			$data['customer_address'] = $customer_info->address_1;
			$data['customer_wallet'] = $customer_info->wallet;
			$data['customer_credit_limit'] = $customer_info->credit_limit;
			$data['customer_sale_markup'] = $customer_info->sale_markup; //for sale markup
			$data['customer_is_staff'] = $customer_info->staff;
			$data['already_used_credit'] = $this->Sale->get_thismonth_credit($customer_id); //for staff customers
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

	private function _load_sale_data($sale_id,$for_returns = false,$for_receipt= false)
	{
        $data = array();
		$this->sale_lib->clear_all();
		$this->sale_lib->reset_cash_flags();
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		if($sale_info['sales_type'] == 1 && $for_returns){
		    $data['error'] = "You can not perform returns on a return ticket! Contact manager.";
		    return $data;
        }
		$this->sale_lib->copy_entire_sale($sale_id,$for_returns,$for_receipt);


		$data['cart'] = $this->sale_lib->get_cart();
		if(empty($data['cart']) && $for_returns){
		    $data['error'] = "Invalid receipt or receipt not eligible for returns";
		    return $data;
        }
		$data['payments'] = $this->sale_lib->get_payments();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
//		var_dump($data['payments']);
//		exit();

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
//        var_dump($totals);
//        exit();
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
//		    echo "Na this one";
//		    exit();
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

		return $data;

//		return $this->xss_clean($data);
	}
	public function set_role()
	{
		$role = $this->input->post('role');
		//$role_modules=$this->Module->get_all_modules_role($role);
		$this->sale_lib->set_modulecart('ewaiwo');
		//$this->load->view('employees/form', $data);
	}

	public function keys()
	{
		$d = $this->Sale->get_latest_sale_id();
		return print_r($d);
	}
	public function viewIRecharge(){
        $this->load->view('sales/i_recharge_page');
    }

	private function _get_sale_lib_info($data){
        $data['mode'] = $this->sale_lib->get_mode();
        $data['empty_tables'] = $this->sale_lib->get_empty_tables();
        $data['selected_table'] = $this->sale_lib->get_dinner_table();
        $data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
        $data['stock_location'] = $this->sale_lib->get_sale_location();
        $data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);

        $data['taxes'] = $this->sale_lib->get_taxes();
        $data['discount'] = $this->sale_lib->get_discount();

        $data['payments'] = $this->sale_lib->get_payments();
        return $data;
    }
	private function _reload($data = array(),$aj_request = false)
	{
		$this->sale_lib->recheck_items();

		$in_prog_stock = $this->Receiving->get_inprogress_stock_taking();
		$stock = $in_prog_stock == null ? [] : $in_prog_stock;
		$stock_count = count($stock);
		if ($stock_count > 0) {
			$data = array('message' => 'Stock Taking in Progress. You can not make a sale at this time');
			$this->load->view("stock_intake/progress", $data);
			return;
		}
		$e_info = $this->Employee->get_logged_in_employee_info();
		$employee_id = $e_info->person_id;
        $data['extraConfig'] = $this->Appconfig->get_extra_config(['company_id'=>$e_info->branch_id,'company_branch_id'=>$e_info->branch_id]);
        $data['can_vend'] = $this->Employee->can_vend($employee_id);
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
        $c_id = $this->sale_lib->get_customer();
        if($c_id != null && $c_id > 0){
            $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
//            $data['customer'] = $this->Customer->get_info($c_id);
        }

        $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);

		if ($this->config->item('invoice_enable') == '0') {
			$data['modes'] = array('sale' => $this->lang->line('sales_sale'), 'return' => $this->lang->line('sales_return'));
		} else {
			$data['modes'] = array(
				'sale' => $this->lang->line('sales_sale'),
				//'sale_invoice' => $this->lang->line('sales_sale_by_invoice'),
				//'sale_quote' => $this->lang->line('sales_quote'),
				'return' => $this->lang->line('sales_return')
			);
		}

        $data = $this->_get_sale_lib_info($data);
		$totals = $this->sale_lib->get_totals();
		$totals['discount_total'] = $totals['subtotal'] - $totals['discounted_subtotal'];

		$data['discount_approved'] = $this->sale_lib->get_auth_code() != null;
        $data['return_approved'] = $this->sale_lib->get_auth_code(true) != null;

		$data['total_discount'] = $aj_request?to_currency($totals['discount_total']):$totals['discount_total'];
		$data['total_vat'] = $aj_request?to_currency($totals['total_vat']):$totals['total_vat'];
		$data['initial_cost'] =$aj_request?to_currency( $totals['subtotal']):$totals['subtotal'];
		$data['subtotal'] = $aj_request?to_currency($totals['discounted_subtotal']):$totals['discounted_subtotal'];

		$data['cash_total'] = $aj_request?to_currency($totals['cash_total']):$totals['cash_total'];
		$data['cash_amount_due'] = $aj_request ? to_currency($totals['cash_amount_due']):$totals['cash_amount_due'];
		$data['non_cash_total'] = $aj_request?to_currency($totals['total']):$totals['total'];
		$data['non_cash_amount_due'] = $aj_request?to_currency($totals['amount_due']):$totals['amount_due'];
		$data['payments_total'] = $aj_request?to_currency($totals['payment_total']):$totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		if ($data['cash_rounding']) {
			$data['total'] = $aj_request ? to_currency($totals['cash_total']):$totals['cash_total'];
			$data['amount_due'] = $aj_request ? to_currency($totals['cash_amount_due']):$totals['cash_amount_due'];
		} else {

			//jude change to whole number her
			$data['total'] = $aj_request ? to_currency(intval($totals['total'])):intval($totals['total']);
			$data['amount_due'] = $aj_request ? to_currency(intval($totals['amount_due'])):intval($totals['amount_due']);
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$data['selected_payment_type'] = null;

		$data['payment_options'] = $this->Sale->get_payment_options();
		// return  print("<pre>".print_r($data['cart'],true)."</pre>");
        $data_w = $this->getQuote($data);
//		$this->load->view("sales/register", $data);
        if($aj_request){
//            $last_inserted = $this->CI->session->userdata('last_inserted');
            $data_w['sale_total'] = to_currency($data_w['total']+$data_w['total_vat']);
            echo json_encode(['cart_items'=>$data_w]);
            exit();
        }
        $this->load->view("sales/register", $data_w);
	}
	private function getQuote($data){
        $quote_number = $this->sale_lib->get_quote_number();
        if ($quote_number != NULL) {
            $data['quote_number'] = $quote_number;
        }

        $data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);

        $invoice_format = $this->config->item('sales_invoice_format');
        $data['invoice_format'] = $invoice_format;

        $this->set_invoice_number();
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
//        $data = $this->xss_clean($data);
        return $data;
    }
	public function me()
	{
		$this->sale_lib->recheck_items();
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
		$data = $this->_get_sale_lib_info($data);
//		$data['mode'] = $this->sale_lib->get_mode();
//		$data['empty_tables'] = $this->sale_lib->get_empty_tables();
//		$data['selected_table'] = $this->sale_lib->get_dinner_table();
//		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
//		$data['stock_location'] = $this->sale_lib->get_sale_location();
//		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
//
//		$data['taxes'] = $this->sale_lib->get_taxes();
//		$data['discount'] = $this->sale_lib->get_discount();
//		$data['payments'] = $this->sale_lib->get_payments();

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
		$data_w = $this->getQuote($data);
		$this->load->view("sales/pill", $data_w);
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
			if ($discount_percent > 0) {
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
			$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location = 0);
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

	public function get_latest()
	{
		$days = 0;


		$d = $this->CI->Customer->get_company_info(1)->markup;
		return print_r($d);
	}

	public function receipt($sale_id)
	{
		$data = $this->_load_sale_data($sale_id,0,1);
		$this->sale_lib->clear_all();
		$this->load->view('sales/receipt', $data);
	}

	public function invoice($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);
		$this->sale_lib->clear_all();
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

			//ite was not entered in the stock
			$this->sale_lib->copy_entire_sale($sale_id);
			$this->Sale->delete_suspended_sale($sale_id);
		} else {
			// This will unsuspended older suspended sales
			$sale_id = $sale_id * -1;
			$this->sale_lib->copy_entire_suspended_tables_sale($sale_id);
			$this->Sale_suspended->delete($sale_id);
		}
		//remove sales that are out of stock. this is sale mode
		$items = $this->sale_lib->get_cart();
		$data = array();
		foreach ($items as $index => &$item) {
			$stockno = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
			$item_info = $this->CI->Item->get_info_by_id_or_number($item['item_id']);


			if ($stockno == 0) {
				unset($items[$index]); //remove the item from the card
				$data['warning'] = "Out of stock items are removed from the cart.";
			} elseif ($item['quantity'] > $stockno && $item['qty_selected'] == 'retail') {
				$item['quantity'] = (int) $stockno; //assign all quantity to the sale
			} elseif (($item['quantity'] * $item_info->pack) > $stockno && $item['qty_selected'] == 'wholesale') {
				//get the integer division of the qty by pack
				$qty = floor($stockno / $item_info->pack);
				if ($qty > 0) {
					$item['quantity'] = $qty;
				} else {
					unset($items[$index]); //remove the item from the card. or change the qty selected to retail
					$data['warning'] = "Items that has insufficient quantity are removed from the cart";
				}
			}
		}
		$this->sale_lib->set_cart($items);


		$this->_reload($data);
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
//	public function approve_returns(){
//	    $code = $this->input->post('auth_code');
//	    $resp = [];
//	    if(!$code){
//	        $resp['error']= "Provide valid authorization code for this sales return";
//        }else{
//
//        }
//    }

	public function approve_discount($for_returns = 0)
	{
		$resp = array(
			'success'	=> FALSE,
			'message'	=> 'Pending'
		);
		$code = $this->input->post("code");
		if (!$code) {
			$resp['message'] = 'Please enter authorization code. Only Inventory Officers in your branch can authorize. ';
			echo json_encode($resp, true);
		} else {
			$location = $this->Employee->get_logged_in_employee_info()->branch_id;
			$employee = $this->Employee->get_employee_by_code_password($location, $code);
			if ($employee) {

				//check if auth_emp is null

				if($for_returns == 1){
//                    $this->sale_lib->set_auth_code($employee->person_id,true);
                    $this->sale_lib->set_auth_code($code,true);
                }else{
//                    $this->sale_lib->set_auth_code($employee->person_id);
                    $this->sale_lib->set_auth_code($code);
                }
				$resp['success'] = TRUE;
				$resp['message'] = "Authorization successful. Authorized by " . $employee->last_name . ' ' . $employee->first_name;
				echo json_encode($resp, true);
			} else {
				$resp['message'] = 'Wrong authorization code';
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
