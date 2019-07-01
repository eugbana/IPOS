<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sale_lib
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('tax_lib');
		$this->CI->load->model('enums/Rounding_code');

	}
	public function set_modulecart($cart_data)
	{
		$this->CI->session->set_userdata('module_cart', $cart_data);
	}
	public function empty_modulecart()
	{
		$this->CI->session->unset_userdata('module_cart');
	}
	public function get_modulecart()
	{
		if(!$this->CI->session->userdata('module_cart'))
		{
			$this->set_modulecart(array());
		}

		return $this->CI->session->userdata('module_cart');
	}
	public function set_balance_id($cart_data)
	{
		$this->CI->session->set_userdata('balance_id', $cart_data);
	}
	public function empty_balance_id()
	{
		$this->CI->session->unset_userdata('balance_id');
	}
	public function get_balance_id()
	{
		if(!$this->CI->session->userdata('balance_id'))
		{
			$this->set_balance_id(array());
		}

		return $this->CI->session->userdata('balance_id');
	}
	public function set_pill($cart_data)
	{
		$this->CI->session->set_userdata('pill_cart', $cart_data);
	}
	public function empty_pill()
	{
		$this->CI->session->unset_userdata('pill_cart');
	}
	public function get_pill()
	{
		if(!$this->CI->session->userdata('pill_cart'))
		{
			$this->set_pill(array());
		}

		return $this->CI->session->userdata('pill_cart');
	}

	public function get_line_sequence_options()
	{
		return array(
		
			'0' => $this->CI->lang->line('sales_entry'),
			'1' => $this->CI->lang->line('sales_group_by_type'),
			'2' => $this->CI->lang->line('sales_group_by_category')
		);
	}

	public function get_register_mode_options()
	{
		return array(
			'sale' => $this->CI->lang->line('sales_receipt'),
			'sale_invoice' => $this->CI->lang->line('sales_invoice'),
			'sale_quote' => $this->CI->lang->line('sales_quote')
		);
	}

	public function get_cart()
	{
		if(!$this->CI->session->userdata('sales_cart'))
		{
			$this->set_cart(array());
		}

		return $this->CI->session->userdata('sales_cart');
	}
	public function get_lab_accountcart()
	{
		if(!$this->CI->session->userdata('lab_accountcart'))
		{
			$this->set_lab_accountcart(array());
		}

		return $this->CI->session->userdata('lab_accountcart');
	}
	public function get_lab_resultcart()
	{
		if(!$this->CI->session->userdata('lab_resultcart'))
		{
			$this->set_lab_resultcart(array());
		}

		return $this->CI->session->userdata('lab_resultcart');
	}
	public function get_labcart()
	{
		if(!$this->CI->session->userdata('lab_cart'))
		{
			$this->set_labcart(array());
		}

		return $this->CI->session->userdata('lab_cart');
	}
	
	

	public function sort_and_filter_cart($cart)
	{
		if(empty($cart))
		{
			return $cart;
		}

		$filtered_cart = array();

		foreach($cart as $k=>$v)
		{
			if($v['print_option'] == '0')
			{
				$filtered_cart[] = $v;
			}
		}

		// Entry sequence (this will render kits in the expected sequence)
		if($this->CI->config->item('line_sequence') == '0')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif($this->CI->config->item('line_sequence') == '1')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['stock_type'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['stock_type'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC. $filtered_cart);
		}
		// Group by Item Category
		elseif($this->CI->config->item('line_sequence') == '2')
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['category'][$k] = $v['stock_type'];
				$sort['description'][$k] = $v['description'];
				$sort['name'][$k] = $v['name'];
			}
			array_multisort($sort['category'], SORT_DESC, $sort['description'], SORT_ASC, $sort['name'], SORT_ASC, $filtered_cart);
		}
		// Group by entry sequence in descending sequence (the Standard)
		else
		{
			$sort = array();
			foreach($filtered_cart as $k=>$v)
			{
				$sort['line'][$k] = $v['line'];
			}
			array_multisort($sort['line'], SORT_ASC, $filtered_cart);
		}

		return $filtered_cart;
	}

	public function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('sales_cart', $cart_data);
	}
	public function set_lab_resultcart($cart_data)
	{
		$this->CI->session->set_userdata('lab_resultcart', $cart_data);
	}
	public function set_lab_accountcart($cart_data)
	{
		$this->CI->session->set_userdata('lab_accountcart', $cart_data);
	}
	public function set_labcart($cart_data)
	{
		$this->CI->session->set_userdata('lab_cart', $cart_data);
	}
	

	public function empty_cart()
	{
		$this->CI->session->unset_userdata('sales_cart');
	}
	public function empty_lab_accountcart()
	{
		$this->CI->session->unset_userdata('lab_accountcart');
	}
	
	public function empty_labcart()
	{
		$this->CI->session->unset_userdata('lab_cart');
	}
	public function empty_lab_resultcart()
	{
		$this->CI->session->unset_userdata('lab_resultcart');
	}

	
	public function get_comment() 
	{
		// avoid returning a NULL that results in a 0 in the comment if nothing is set/available
		$comment = $this->CI->session->userdata('sales_comment');

		return empty($comment) ? '' : $comment;
	}

	public function set_comment($comment) 
	{
		$this->CI->session->set_userdata('sales_comment', $comment);
	}

	public function clear_comment() 	
	{
		$this->CI->session->unset_userdata('sales_comment');
	}
	
	public function get_invoice_number()
	{
		return $this->CI->session->userdata('sales_invoice_number');
	}

	public function get_quote_number()
	{
		return $this->CI->session->userdata('sales_quote_number');
	}

	public function set_invoice_number($invoice_number, $keep_custom = FALSE)
	{
		$current_invoice_number = $this->CI->session->userdata('sales_invoice_number');
		if(!$keep_custom || empty($current_invoice_number))
		{
			$this->CI->session->set_userdata('sales_invoice_number', $invoice_number);
		}
	}

	public function set_quote_number($quote_number, $keep_custom = FALSE)
	{
		$current_quote_number = $this->CI->session->userdata('sales_quote_number');
		if(!$keep_custom || empty($current_quote_number))
		{
			$this->CI->session->set_userdata('sales_quote_number', $quote_number);
		}
	}

	public function clear_invoice_number()
	{
		$this->CI->session->unset_userdata('sales_invoice_number');
	}

	public function clear_quote_number()
	{
		$this->CI->session->unset_userdata('sales_quote_number');
	}

	public function set_suspended_id($suspended_id)
	{
		$this->CI->session->set_userdata('suspended_id', $suspended_id);
	}

	public function get_suspended_id()
	{
		return $this->CI->session->userdata('suspended_id');
	}

	public function is_invoice_mode()
	{
		return ($this->CI->session->userdata('sales_invoice_number_enabled') == 'true' ||
				$this->CI->session->userdata('sales_mode') == 'sale_invoice' ||
				($this->CI->session->userdata('sales_invoice_number_enabled') == '1') &&
					$this->CI->config->item('invoice_enable') == TRUE);
	}

	public function is_sale_by_receipt_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale');
	}

	public function is_quote_mode()
	{
		return ($this->CI->session->userdata('sales_mode') == 'sale_quote');
	}

	public function set_invoice_number_enabled($invoice_number_enabled)
	{
		return $this->CI->session->set_userdata('sales_invoice_number_enabled', $invoice_number_enabled);
	}
	
	public function is_print_after_sale() 
	{
		return ($this->CI->session->userdata('sales_print_after_sale') == 'true' ||
				$this->CI->session->userdata('sales_print_after_sale') == '1');
	}
	
	public function set_print_after_sale($print_after_sale)
	{
		return $this->CI->session->set_userdata('sales_print_after_sale', $print_after_sale);
	}
	
	public function get_email_receipt() 
	{
		return $this->CI->session->userdata('sales_email_receipt');
	}

	public function set_email_receipt($email_receipt) 
	{
		$this->CI->session->set_userdata('sales_email_receipt', $email_receipt);
	}

	public function clear_email_receipt() 	
	{
		$this->CI->session->unset_userdata('sales_email_receipt');
	}

	// Multiple Payments
	public function get_payments()
	{
		if(!$this->CI->session->userdata('sales_payments'))
		{
			$this->set_payments(array());
		}

		return $this->CI->session->userdata('sales_payments');
	}
	public function get_sales_id()
	{
		if(!$this->CI->session->userdata('sales_id'))
		{
			$this->set_sales_id(array());
		}

		return $this->CI->session->userdata('sales_id');
	}
	public function set_sales_id($payments_data)
	{
		$this->CI->session->set_userdata('sales_id', $payments_data);
	}
	public function empty_sales_id()
	{
		$this->CI->session->unset_userdata('sales_id');
	}
	
	public function get_transfer_id()
	{
		if(!$this->CI->session->userdata('transfer_id'))
		{
			$this->set_transfer_id(array());
		}

		return $this->CI->session->userdata('transfer_id');
	}
	public function set_transfer_id($payments_data)
	{
		$this->CI->session->set_userdata('transfer_id', $payments_data);
	}
	public function empty_transfer_id()
	{
		$this->CI->session->unset_userdata('transfer_id');
	}


	// Multiple Payments
	public function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('sales_payments', $payments_data);
	}

	// Multiple Payments
	public function add_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			//payment_method already exists, add to payment_amount
			$payments[$payment_id]['payment_amount'] = bcadd($payments[$payment_id]['payment_amount'], $payment_amount);
		}
		else
		{
			//add to existing array
			$payment = array($payment_id => array('payment_type' => $payment_id, 'payment_amount' => $payment_amount));
			
			$payments += $payment;
		}

		$this->set_payments($payments);
	}

	// Multiple Payments
	public function edit_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payments);

			return TRUE;
		}

		return FALSE;
	}

	// Multiple Payments
	public function delete_payment($payment_id)
	{
		$payments = $this->get_payments();
		unset($payments[urldecode($payment_id)]);
		$this->set_payments($payments);
	}

	// Multiple Payments
	public function empty_payments()
	{
		$this->CI->session->unset_userdata('sales_payments');
	}

	// Multiple Payments
	public function get_payments_total()
	{
		$subtotal = 0;
		$this->reset_cash_flags();
		foreach($this->get_payments() as $payments)
		{
			$subtotal = bcadd($payments['payment_amount'], $subtotal);
			if($this->CI->session->userdata('cash_rounding') && $this->CI->lang->line('sales_cash') != $payments['payment_type'])
			{
				$this->CI->session->set_userdata('cash_rounding', 0);
			}
		}

		return $subtotal;
	}

	public function get_cash_rounding()
	{

	}

	/*
	 * Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'paid_in_full'
	 * 'subtotal', 'discounted_subtotal', 'tax_exclusive_subtotal'
	 */
	public function get_totals_lab()
	{
		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		$totals = array();

		$subtotal = 0;
		$discounted_subtotal = 0;
		$tax_exclusive_subtotal = 0;
		foreach($this->get_labcart() as $item)
		{
			//$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			//$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			if($item['stock_name'] == "WholeSale"){
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], FALSE));
			} else {
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			}
			if($item['stock_name'] == "WholeSale"){
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], TRUE));
			} else {
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			}
			if($this->CI->config->config['tax_included'])
			{
				$tax_exclusive_subtotal = bcadd($tax_exclusive_subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], TRUE));
			}
		}

		$totals['subtotal'] = $subtotal;
		$totals['discounted_subtotal'] = $discounted_subtotal;
		$totals['tax_exclusive_subtotal'] = $tax_exclusive_subtotal;

		$total = $discounted_subtotal;
		if ($this->CI->config->config['tax_included'])
		{
			$totals['total'] = $total;
		}
		else
		{
			foreach($this->get_taxes() as $sales_tax)
			{
				$total = bcadd($total, $sales_tax['sale_tax_amount']);
			}
			$totals['total'] = $total;
		}

		if($cash_rounding)
		{
			$cash_total = $this->check_for_cash_rounding($total);
			$totals['cash_total'] = $cash_total;
		}
		else
		{
			$cash_total = $total;
		}

		$totals['cash_total'] = $cash_total;

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_rounding)
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		if($this->get_mode() == 'return')
		{
			$totals['payments_cover_total'] = $current_due >= 0;
		}
		else
		{
			$totals['payments_cover_total'] =  $current_due <= 0;
		}

		return $totals;
	}
	public function get_totals()
	{
		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		$totals = array();

		$subtotal = 0;
		$discounted_subtotal = 0;
		$tax_exclusive_subtotal = 0;
		foreach($this->get_cart() as $item)
		{
			//$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			//$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			if($item['stock_name'] == "WholeSale"){
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], FALSE));
			} else {
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			}
			if($item['stock_name'] == "WholeSale"){
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], TRUE));
			} else {
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			}
			if($this->CI->config->config['tax_included'])
			{
				$tax_exclusive_subtotal = bcadd($tax_exclusive_subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], TRUE));
			}
		}

		$totals['subtotal'] = $subtotal;
		$totals['discounted_subtotal'] = $discounted_subtotal;
		$totals['tax_exclusive_subtotal'] = $tax_exclusive_subtotal;

		$total = $discounted_subtotal;
		if ($this->CI->config->config['tax_included'])
		{
			$totals['total'] = $total;
		}
		else
		{
			foreach($this->get_taxes() as $sales_tax)
			{
				$total = bcadd($total, $sales_tax['sale_tax_amount']);
			}
			$totals['total'] = $total;
		}

		if($cash_rounding)
		{
			$cash_total = $this->check_for_cash_rounding($total);
			$totals['cash_total'] = $cash_total;
		}
		else
		{
			$cash_total = $total;
		}

		$totals['cash_total'] = $cash_total;

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_rounding)
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		if($this->get_mode() == 'return')
		{
			$totals['payments_cover_total'] = $current_due >= 0;
		}
		else
		{
			$totals['payments_cover_total'] =  $current_due <= 0;
		}

		return $totals;
	}
	public function get_totals_pill()
	{
		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		$totals = array();

		$subtotal = 0;
		$discounted_subtotal = 0;
		$tax_exclusive_subtotal = 0;
		foreach($this->get_pill() as $item)
		{
			//$subtotal = bcadd($subtotal, $this->get_item_total($item['no_of_days'], $item['price'], $item['reminder_value'], FALSE));
			$discounted_subtotal = bcadd($discounted_subtotal, (24/$item['reminder_value'])*$item['no_of_days']*$item['price']);
			
				$subtotal = (24/$item['reminder_value'])*$item['no_of_days']*$item['price'];
			
			
				//$discounted_subtotal =(24/$item['reminder_value'])*$item['no_of_days']*$item['price'];
			
			
		}

		$totals['subtotal'] = $subtotal;
		$totals['discounted_subtotal'] = $discounted_subtotal;
		$totals['tax_exclusive_subtotal'] = $tax_exclusive_subtotal;

		$total = $discounted_subtotal;
		if ($this->CI->config->config['tax_included'])
		{
			$totals['total'] = $total;
		}
		else
		{
			foreach($this->get_taxes() as $sales_tax)
			{
				$total = bcadd($total, $sales_tax['sale_tax_amount']);
			}
			$totals['total'] = $total;
		}

		if($cash_rounding)
		{
			$cash_total = $this->check_for_cash_rounding($total);
			$totals['cash_total'] = $cash_total;
		}
		else
		{
			$cash_total = $total;
		}

		$totals['cash_total'] = $cash_total;

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_rounding)
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		if($this->get_mode() == 'return')
		{
			$totals['payments_cover_total'] = $current_due >= 0;
		}
		else
		{
			$totals['payments_cover_total'] =  $current_due <= 0;
		}

		return $totals;
	}





	// Multiple Payments
	public function get_amount_due()
	{
		// Payment totals need to be identified first so that we know whether or not there is a non-cash payment involved
		$payment_total = $this->get_payments_total();
		$sales_total = $this->get_total();
		$amount_due = bcsub($sales_total, $payment_total);
		$precision = $this->CI->config->item('currency_decimals');
		$rounded_due = bccomp(round($amount_due, $precision, PHP_ROUND_HALF_EVEN), 0, $precision);
		// take care of rounding error introduced by round tripping payment amount to the browser
		return $rounded_due == 0 ? 0 : $amount_due;
	}

	public function is_payment_covering_total()
	{

		if($this->get_mode() == 'return')
		{
			return $this->get_amount_due() >= 0;
		}
		else
		{
			return $this->get_amount_due() <= 0;
		}
	}

	public function get_customer()
	{
		if(!$this->CI->session->userdata('sales_customer'))
		{
			$this->set_customer(-1);
		}

		return $this->CI->session->userdata('sales_customer');
	}

	public function set_customer($customer_id)
	{
		$this->CI->session->set_userdata('sales_customer', $customer_id);
	}

	public function remove_customer()
	{
		$this->CI->session->unset_userdata('sales_customer');
	}
	
	public function get_employee()
	{
		if(!$this->CI->session->userdata('sales_employee'))
		{
			$this->set_employee(-1);
		}

		return $this->CI->session->userdata('sales_employee');
	}

	public function set_employee($employee_id)
	{
		$this->CI->session->set_userdata('sales_employee', $employee_id);
	}

	public function remove_employee()
	{
		$this->CI->session->unset_userdata('sales_employee');
	}

	public function get_mode()
	{
		if(!$this->CI->session->userdata('sales_mode'))
		{
			if($this->CI->config->config['invoice_enable'] == '1')
			{
				$this->set_mode($this->CI->config->config['default_register_mode']);
			}
			else{
				$this->set_mode('sale');
			}
		}

		return $this->CI->session->userdata('sales_mode');
	}

	public function set_mode($mode)
	{
		$this->CI->session->set_userdata('sales_mode', $mode);
	}

	public function clear_mode()
	{
		$this->CI->session->unset_userdata('sales_mode');
	}

	public function get_dinner_table()
	{
		if(!$this->CI->session->userdata('dinner_table'))
		{
			if($this->CI->config->item('dinner_table_enable') == TRUE)
			{
				$this->set_dinner_table(1);
			}
		}

		return $this->CI->session->userdata('dinner_table');
	}

	public function set_dinner_table($dinner_table)
	{
		$this->CI->session->set_userdata('dinner_table', $dinner_table);
	}

	public function clear_table()
	{
		$this->CI->session->unset_userdata('dinner_table');
	}

	public function get_sale_location()
	{
		if(!$this->CI->session->userdata('sales_location'))
		{
			$this->set_sale_location($this->CI->Stock_location->get_default_location_id());
		}

		return $this->CI->session->userdata('sales_location');
	}

	public function set_sale_location($location)
	{
		$this->CI->session->set_userdata('sales_location', $location);
	}

	public function set_payment_type($payment_type)
	{
		$this->CI->session->set_userdata('payment_type', $payment_type);
	}

	public function get_payment_type()
	{
		return $this->CI->session->userdata('payment_type');
	}

	public function clear_sale_location()
	{
		$this->CI->session->unset_userdata('sales_location');
	}

	public function set_giftcard_remainder($value)
	{
		$this->CI->session->set_userdata('sales_giftcard_remainder', $value);
	}

	public function get_giftcard_remainder()
	{
		return $this->CI->session->userdata('sales_giftcard_remainder');
	}

	public function clear_giftcard_remainder()
	{
		$this->CI->session->unset_userdata('sales_giftcard_remainder');
	}

	public function set_rewards_remainder($value)
	{
		$this->CI->session->set_userdata('sales_rewards_remainder', $value);
	}

	public function get_rewards_remainder()
	{
		return $this->CI->session->userdata('sales_rewards_remainder');
	}

	public function clear_rewards_remainder()
	{
		$this->CI->session->unset_userdata('sales_rewards_remainder');
	}

public function add_item(&$item_id, $quantity = 1, $item_location, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '0',$qty_selected='retail',$reference=0)
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_cart();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $item['item_location'] == $item_location && $item['reference']==$reference)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}
			if($item['item_id'] == $item_id && $item['item_location'] == $item_location && $item['reference']==$reference)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				
			}
			
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		if(is_null($price) && $reference==0)
		{
			$price = $item_info->unit_price;
		}elseif(is_null($price) && $reference==1)
		{
			$price = $this->CI->config->config['price_pill'];
			//$price = 2;
		}
		

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option =='1')
		{
			if($price == 0)
			{
				$print_option = '2';
			}
			else
			{
				$print_option = '0';
			}
		}
		
				$total = $this->get_item_total($quantity, $price, $discount);
			
		
			$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		$no_of_days=1;
		$reminder_value=1;
		$time_started=date('Y-m-d H:i:s');
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'item_location' => $item_location,
					'stock_name' => $this->CI->Stock_location->get_location_name($item_location),
					'line' => $insertkey,
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'cost_price' => $item_info->cost_price,
					'description' => $description != NULL ? $description : $item_info->description,
					'serialnumber' => $serialnumber != NULL ? $serialnumber : '',
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'qty_selected' => $qty_selected,
					'discount' => $discount,
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity,
					'price' => $price,
					'wholeprice' => $wholeprice,
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => '1',
					'stock_type' => $stock_type,
					'tax_category_id' => $item_info->tax_category_id,
					
					
					'time_started' => $time_started,
					'reference' => $reference,
					
					
					'reminder_value' => $reminder_value,
					'reminder_amount' => $reminder_amount,
					'no_of_days' => $no_of_days,
					
					'period' => $period,
					
				)
			);
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['quantity'] = $quantity;
			$line['total'] = $total;
			$line['discounted_total'] = $discounted_total;
		}

		$this->set_cart($items);

		return TRUE;
	}
	public function add_item_pill(&$item_id, $period, $no_of_days, $time_started,$price = NULL)
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);
		$pill_info = $this->CI->Item->get_info_pill($period);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;
		$reminder_value = $pill_info->reminder_value;
		$reminder_amount = $pill_info->reminder_amount;
		
		$total =(24/$reminder_value)*$no_of_days*$reminder_amount;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_pill();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				
			}
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		
			//$price = $pill_info->reminder_amount;
		
		

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option =='1')
		{
			if($price == 0)
			{
				$print_option = '2';
			}
			else
			{
				$print_option = '0';
			}
		}
		
			//$total = $this->get_item_total($quantity, $price, $discount);
			
			
		
			$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'time_started' => $time_started,
					'stock_name' => $this->CI->Stock_location->get_location_name($item_location),
					'line' => $insertkey,
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'reminder_value' => $reminder_value,
					'reminder_amount' => $reminder_amount,
					'no_of_days' => $no_of_days,
					'price' => $reminder_amount,
					'period' => $period,
					'item_location' => 1,
					'quantity' => 1,
					'discount' => 0,
					
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => '1',
					
					
				)
			);
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['total'] = $total;
			$line['discounted_total'] = $discounted_total;
		}

		$this->set_pill($items);

		return TRUE;
	}
	public function edit_pill($line,$period, $no_of_days, $time_started)
	{
		$pill_info = $this->CI->Item->get_info_pill($period);
		$items = $this->get_pill();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['period'] = $period;
			$line['no_of_days'] = $no_of_days;
			$line['time_started'] = $time_started;
			$line['reminder_value'] = $pill_info->reminder_value;
			$line['price'] = $pill_info->reminder_amount;
			$price=$pill_info->reminder_amount;
			$reminder_value=$pill_info->reminder_value;
			$line['total'] =(24/$reminder_value)*$no_of_days*$price;
			
			$this->set_pill($items);
		}

		return FALSE;
	}
	public function item_add_lab(&$item_id, $quantity = 1, $discount = 0, $price = NULL, $description = NULL, $serialnumber = NULL, $include_deleted = FALSE, $print_option = '0', $stock_type = '1')
	{
		$item_info = $this->CI->Item->get_labinfo_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_lab_accountcart();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		if(is_null($price))
		{
			$price = $item_info->test_amount;
		}
		elseif($price == 0)
		{
			$price = 0.00;
			$discount = 0.00;
		}
		if(is_null($wholeprice))
		{
			$wholeprice = $item_info->custom5;
		}
		elseif($wholeprice == 0)
		{
			$wholeprice = 0.00;
			$discount = 0.00;
		}

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option =='1')
		{
			if($price == 0)
			{
				$print_option = '2';
			}
			else
			{
				$print_option = '0';
			}
		}
		if($this->CI->Stock_location->get_location_name($item_location)=='WholeSale'){
				$total = $this->get_item_total($quantity, $wholeprice, $discount);
			}else{
				$total = $this->get_item_total($quantity, $price, $discount);
			}
		if($this->CI->Stock_location->get_location_name($item_location)=='WholeSale'){
			$discounted_total = $this->get_item_total($quantity, $wholeprice, $discount, TRUE);
		}else{
			$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		}
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
			$item = array($insertkey => array(
					'item_id' =>$item_info->item_id,
					'item_location' => 1,
					'stock_name' => 'warehouse',
					'line' => $insertkey,
					'name' => $item_info->test_name,
					'description' => $description != NULL ? $description : $item_info->description,
					'serialnumber' => $serialnumber != NULL ? $serialnumber : '',
					'allow_alt_description' => 0,
					'is_serialized' => 0,
					'quantity' => $quantity,
					'discount' => $discount,
					'in_stock' => 3,
					'price' => $item_info->test_amount,
					'wholeprice' => 3000,
					'total' => $item_info->test_amount,
					'discounted_total' => 7000,
					'print_option' => '1',
					'stock_type' => 1,
					'tax_category_id' => $item_info->tax_category_id
				)
			);
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['quantity'] = $quantity;
			$line['total'] = $total;
			$line['discounted_total'] = $discounted_total;
		}

		$this->set_lab_accountcart($items);

		return TRUE;
	}
	public function get_labaccount_totals()
	{
		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		$totals = array();

		$subtotal = 0;
		$discounted_subtotal = 0;
		$tax_exclusive_subtotal = 0;
		foreach($this->get_lab_accountcart() as $item)
		{
			//$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			//$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			if($item['stock_name'] == "WholeSale"){
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], FALSE));
			} else {
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], FALSE));
			}
			if($item['stock_name'] == "WholeSale"){
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['wholeprice'], $item['discount'], TRUE));
			} else {
				$discounted_subtotal = bcadd($discounted_subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE));
			}
			
		}

		$totals['subtotal'] = $subtotal;
		$totals['discounted_subtotal'] = $discounted_subtotal;
		$totals['tax_exclusive_subtotal'] = $tax_exclusive_subtotal;

		$total = $discounted_subtotal;
		if ($this->CI->config->config['tax_included'])
		{
			$totals['total'] = $total;
		}
		else
		{
			foreach($this->get_taxes() as $sales_tax)
			{
				$total = bcadd($total, $sales_tax['sale_tax_amount']);
			}
			$totals['total'] = $total;
		}

		if($cash_rounding)
		{
			$cash_total = $this->check_for_cash_rounding($total);
			$totals['cash_total'] = $cash_total;
		}
		else
		{
			$cash_total = $total;
		}

		$totals['cash_total'] = $cash_total;

		$payment_total = $this->get_payments_total();
		$totals['payment_total'] = $payment_total;

		$amount_due = bcsub($total, $payment_total);
		$totals['amount_due'] = $amount_due;

		$cash_amount_due = bcsub($cash_total, $payment_total);
		$totals['cash_amount_due'] = $cash_amount_due;

		if($cash_rounding)
		{
			$current_due = $cash_amount_due;
		}
		else
		{
			$current_due = $amount_due;
		}

		if($this->get_mode() == 'return')
		{
			$totals['payments_cover_total'] = $current_due >= 0;
		}
		else
		{
			$totals['payments_cover_total'] =  $current_due <= 0;
		}

		return $totals;
	}


	
	public function edit_result($line, $test_name, $test_comment, $extra_name,$o_name,$h_name)
	{
		$items = $this->get_lab_resultcart();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['test_name'] = $test_name;
			$line['test_comment'] = $test_comment;
			$line['extra_name'] = $extra_name;
			$line['o_name'] = $o_name;
			$line['h_name'] = $h_name;
			$this->set_lab_resultcart($items);
		}

		return FALSE;
	}
	public function edit_received_items($line, $received_quantity, $batch_no, $expiry)
	{
		$items = $this->get_push();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['batch_no'] = $batch_no;
			$line['expiry'] = $expiry;
			$line['received_quantity'] = $received_quantity;
			$this->set_push($items);
		}

		return FALSE;
	}
	public function edit_pushed_items($line, $received_quantity, $batch_no, $expiry)
	{
		$items = $this->get_push();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['batch_no'] = $batch_no;
			$line['expiry'] = $expiry;
			$line['quantity'] = $quantity;
			$this->set_push($items);
		}

		return FALSE;
	}

	public function add_lab_cart($grants_data)
	{
		$items=array();
		//$items = $this->get_cart();
		$insertkey=1;
		foreach($grants_data as $row=>$value){
			$item_info = $this->CI->Item->get_labinfo_by_id_or_number($value);
			$item=array($row => array(
					'name'=>$item_info->test_name,
					'item_id' => $item_info->item_id,
					'item_location' => 1,
					'stock_name' => $this->CI->Stock_location->get_location_name($item_location),
					'line' => $row,
					'item_number' => $item_info->item_number,
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => 1,
					'quantity' =>1,
					'discount' => 0,
					'in_stock' => 15,
					'price' =>$item_info->test_amount,
					'wholeprice' => 3000,
					'total' => $total,
					'discounted_total' => $discounted_total,
					'print_option' => $print_option,
					'stock_type' => '1',
					'tax_category_id' => '0'));
			$insertkey+=1;
			$items+=$item;
		}
		//$items=+=$item;
		//$items=array('name'=>'Goueg');
		
		$this->set_labcart($items);
	}
	public function get_push()
	{
		if(!$this->CI->session->userdata('push_cart'))
		{
			$this->set_push(array());
		}

		return $this->CI->session->userdata('push_cart');
	}
	public function set_push($push_data)
	{
		$this->CI->session->set_userdata('push_cart', $push_data);
	}
	public function empty_push()
	{
		$this->CI->session->unset_userdata('push_cart');
	}
	public function get_reminder()
	{
		if(!$this->CI->session->userdata('reminder_cart'))
		{
			$this->set_reminder(array());
		}

		return $this->CI->session->userdata('reminder_cart');
	}
	public function set_reminder($push_data)
	{
		$this->CI->session->set_userdata('reminder_cart', $push_data);
	}
	public function empty_reminder()
	{
		$this->CI->session->unset_userdata('reminder_cart');
	}
	public function get_transfer_branch_id($location)
	{

		return $this->CI->Item->get_transfer_branch_id($location);
	}
	
	public function item_add_result(&$item_id,$print_option, $comment, $reference=0,$extra_name=NULL, $o_name=NULL, $h_name=NULL)
	{
		$item_info = $this->CI->Item->get_labinfo_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_lab_resultcart();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id'] == $item_id && $reference == 0)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		
		
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale)
		{
			$item = array($insertkey => array(
					'item_id' =>$item_info->item_id,
					'test_code' =>$item_info->test_code,
					'test_name' =>$item_info->test_name,
					'test_unit' =>$item_info->test_unit,
					'category' => $item_info->test_type,
					'test_subgroup' => $item_info->test_subgroup,
					'test_kind' => $item_info->test_kind,
					'test_comment' => $comment,
					'extra_name' => $extra_name,
					'o_name' => $o_name,
					'h_name' => $h_name,
					'line' => $insertkey,
					'reference' => $reference,
					'print_option' => 1,
					
				)
			);
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
		}

		$this->set_lab_resultcart($items);

		return TRUE;
	}
	
	public function add_item_push(&$item_id, $quantity , $request_from_branch_id,$request_to_branch_id=0,$transfer_id=0, $received_quantity = 0, $unaccounted = 0,$reference=0, $batch_no = 0,$expiry=NULL  )
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_push();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}
			if($item['item_id'] == $item_id && $reference == 0)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}

			
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		if(is_null($price))
		{
			$price = $item_info->unit_price;
		}
		elseif($price == 0)
		{
			$price = 0.00;
			$discount = 0.00;
		}
		if(is_null($wholeprice))
		{
			$wholeprice = $item_info->custom5;
		}
		elseif($wholeprice == 0)
		{
			$wholeprice = 0.00;
			$discount = 0.00;
		}

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option =='1')
		{
			if($price == 0)
			{
				$print_option = '2';
			}
			else
			{
				$print_option = '0';
			}
		}
		if($this->CI->Stock_location->get_location_name($request_from_branch_id)=='WholeSale'){
				$total = $this->get_item_total($quantity, $wholeprice, $discount);
			}else{
				$total = $this->get_item_total($quantity, $price, $discount);
			}
		if($this->CI->Stock_location->get_location_name($request_from_branch_id)=='WholeSale'){
			$discounted_total = $this->get_item_total($quantity, $wholeprice, $discount, TRUE);
		}else{
			$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		}
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		
		if(!$itemalreadyinsale)
		{
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'item_location' => $request_from_branch_id,
					'location' => $request_to_branch_id != NULL ? $request_to_branch_id : 'None',
					'stock_name' => $this->CI->Stock_location->get_location_name($request_from_branch_id),
					'line' => $insertkey,
					'branch_transfer' => $this->CI->Item->get_transfer_branch_id($request_to_branch_id),
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'stock_type' => '0',
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $request_from_branch_id)->quantity,
					'transfer_id'=>$transfer_id,
					'received_quantity'=>$received_quantity,
					'unaccounted'=>$unaccounted,
					'reference'=>$reference,
					'expiry'=>$expiry,
					'batch_no'=>$batch_no,
					
				));
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['quantity'] = $quantity;
		}
		$this->set_push($items);

		return TRUE;
	}
	public function add_item_pull(&$item_id, $quantity , $request_from_branch_id,$request_to_branch_id=0,$transfer_id=0, $received_quantity = 0, $unaccounted = 0,$reference=0, $batch_no = 0,$expiry=NULL  )
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_push();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}
			if($item['item_id'] == $item_id && $reference == 0)
			{
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
				if(!$item_info->is_serialized)
				{
					$quantity = bcadd($quantity, $items[$updatekey]['quantity']);
				}
			}

			
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		if(is_null($price))
		{
			$price = $item_info->unit_price;
		}
		elseif($price == 0)
		{
			$price = 0.00;
			$discount = 0.00;
		}
		if(is_null($wholeprice))
		{
			$wholeprice = $item_info->custom5;
		}
		elseif($wholeprice == 0)
		{
			$wholeprice = 0.00;
			$discount = 0.00;
		}

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		if($print_option =='1')
		{
			if($price == 0)
			{
				$print_option = '2';
			}
			else
			{
				$print_option = '0';
			}
		}
		if($this->CI->Stock_location->get_location_name($request_from_branch_id)=='WholeSale'){
				$total = $this->get_item_total($quantity, $wholeprice, $discount);
			}else{
				$total = $this->get_item_total($quantity, $price, $discount);
			}
		if($this->CI->Stock_location->get_location_name($request_from_branch_id)=='WholeSale'){
			$discounted_total = $this->get_item_total($quantity, $wholeprice, $discount, TRUE);
		}else{
			$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		}
		//$total = $this->get_item_total($quantity, $price, $discount);
		//$discounted_total = $this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		$branch_location=$this->CI->Stock_location->get_location_name($request_to_branch_id);
		if(!$itemalreadyinsale)
		{
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'item_location' => $request_from_branch_id,
					'location' => $branch_location,
					'stock_name' => $this->CI->Stock_location->get_location_name($request_from_branch_id),
					'line' => $insertkey,
					'branch_transfer' => $request_to_branch_id,
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $received_quantity,
					'stock_type' => '0',
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $request_from_branch_id)->quantity,
					'transfer_id'=>$transfer_id,
					'requested_quantity'=>$quantity,
					'unaccounted'=>$unaccounted,
					'reference'=>$reference,
					'expiry'=>$expiry,
					'batch_no'=>$batch_no,
					
				));
			//add to existing array
			$items += $item;
		}
		else
		{
			$line = &$items[$updatekey];
			$line['quantity'] = $quantity;
		}
		$this->set_push($items);

		return TRUE;
	}
	public function add_item_reminder($reminder_id, $reminder_name , $reminder_amount,$reminder_value)
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists		
		
		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_reminder();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}
		
			
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		

		// For print purposes this simpifies line selection
		// 0 will print, 2 will not print.   The decision about 1 is made here
		
		
			$item = array($insertkey => array(
					'reminder_id' => $reminder_id,
					'reminder_name' => $reminder_name,
					'reminder_amount' => $reminder_amount,
					'reminder_value' => $reminder_value,
					'line' => $insertkey,
					
				));
			//add to existing array
			$items += $item;
		
		$this->set_reminder($items);

		return TRUE;
	}
	public function add_reciever_push(&$item_id, $quantity , $request_from_branch_id,$request_to_branch_id,$transfer_id, $received_quantity = 0, $unaccounted = 0,$reference=0, $batch_no = 0,$expiry=NULL  )
	{
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);

		//make sure item exists		
		if(empty($item_info))
		{
			$item_id = -1;
			return FALSE;
		}

		$item_id = $item_info->item_id;

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_push();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach($items as $item)
		{
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			
		}

		$insertkey = $maxkey + 1;
		//array/cart records are identified by $insertkey and item_id is just another field.

		
		
			$item = array($insertkey => array(
					'item_id' => $item_id,
					'item_location' => $request_from_branch_id,
					'location' => $request_to_branch_id != NULL ? $request_to_branch_id : 'None',
					'stock_name' => $this->CI->Stock_location->get_location_name($request_from_branch_id),
					'line' => $insertkey,
					'branch_transfer' => $this->CI->Item->get_transfer_branch_id($request_to_branch_id),
					'name' => $item_info->name,
					'item_number' => $item_info->item_number,
					'allow_alt_description' => $item_info->allow_alt_description,
					'is_serialized' => $item_info->is_serialized,
					'quantity' => $quantity,
					'stock_type' => '0',
					'in_stock' => $this->CI->Item_quantity->get_item_quantity($item_id, $request_from_branch_id)->quantity,
					'transfer_id'=>$transfer_id,
					'received_quantity'=>$received_quantity,
					'unaccounted'=>$unaccounted,
					'reference'=>$reference,
					'expiry'=>$expiry,
					'batch_no'=>$batch_no,
					
				));
			//add to existing array
			$items += $item;
		
		$this->set_push($items);

		return TRUE;
	}
	
	
	public function global_search($item_id)
	{
		$sglobal=$this->CI->Item->global_search_items($item_id);
		$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);
		
		foreach($sglobal as $item=>$value)
		{
			$item_id=$value['item_id'];
			$location_id=$value['location_id'];
			$sglob[]=array(
			'item'=>$value['item_id'],
			'item_name'=>$item_info->name,
			'quantity'=>$value['qty'],
			'location_name'=>$this->CI->Stock_location->get_location_name($location_id)
			);
		}
		return $sglob;
	}
	public function global_transfer_items($item_location)
	{
		$items_transfe = $this->CI->Item->global_transfer_items($item_location);


		return count($items_transfe);
	}
	public function notice_transfer_items($item_location)
	{
		$items_transfe = $this->CI->Item->global_transfer_items($item_location);


		return $items_transfe;
	}

	
	public function out_of_stock($item_id, $item_location)
	{
		//make sure item exists		
		if($item_id != -1)
		{
			$item_info = $this->CI->Item->get_info_by_id_or_number($item_id);
			

			if($item_info->stock_type == '0')
			{
				$item_quantity = $this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
				$quantity_added = $this->get_quantity_already_added($item_id, $item_location);
				
				$datetimenow=date('Y/m/d H:i:s');
				$datetimeexpire=$item_info->expiry;
				
				$datetimenowc=strtotime($datetimenow);
				$datetimeexpirec=strtotime($datetimeexpire);
				$des=$datetimeexpirec-$datetimenowc;
				$dd=$des/86400;
				
				

				if($item_quantity - $quantity_added < 0)
				{
					return $this->CI->lang->line('sales_quantity_less_than_zero');
				}
				elseif($item_quantity - $quantity_added < $item_info->reorder_level)
				{
					return $this->CI->lang->line('sales_quantity_less_than_reorder_level');
				}
			}
		}

		return '';
	}
	
	public function get_quantity_already_added($item_id, $item_location)
	{
		$items = $this->get_cart();
		$quanity_already_added = 0;
		foreach($items as $item)
		{
			if($item['item_id'] == $item_id && $item['item_location'] == $item_location)
			{
				$quanity_already_added+=$item['quantity'];
			}
		}
		
		return $quanity_already_added;
	}
	
	public function get_item_id($line_to_get)
	{
		$items = $this->get_cart();

		foreach($items as $line=>$item)
		{
			if($line == $line_to_get)
			{
				return $item['item_id'];
			}
		}
		
		return -1;
	}

	public function edit_item($line, $description, $serialnumber, $quantity, $discount, $price,$qty_selected)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['description'] = $description;
			$line['serialnumber'] = $serialnumber;
			$line['quantity'] = $quantity;
			$line['qty_selected'] = $qty_selected;
			$line['discount'] = $discount;
			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, TRUE);
			$this->set_cart($items);
		}

		return FALSE;
	}
	public function edit_salepill_item($line, $time_started, $reminder_value, $no_of_days, $discount, $price)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['time_started'] = $time_started;
			$line['reminder_value'] = $reminder_value;
			$quantity=$reminder_value*$no_of_days;
			$line['quantity'] = $quantity;
			$line['no_of_days'] = $no_of_days;
			$line['discount'] = $discount;
			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, TRUE);
			$this->set_cart($items);
		}

		return FALSE;
	}
	
	
	public function edit_reminder($line, $reminder_amount)
	{
		$items = $this->get_reminder();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['reminder_amount'] = $reminder_amount;
			$this->set_reminder($items);
		}

		return FALSE;
	}
	public function edit_item_push($line, $item_name, $item_location, $quantity, $location, $branch_transfer)
	{
		$items = $this->get_push();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['item_name'] = $item_name;
			$line['item_location'] = $item_location;
			$line['quantity'] = $quantity;
			$line['location'] = $location;
			$line['branch_transfer'] = $branch_transfer;
		
			$this->set_push($items);
		}

		return FALSE;
	}
	public function edit_item_pull($line, $item_name, $item_location, $quantity, $location, $branch_transfer,$stock_name,$in_stock)
	{
		$items = $this->get_push();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['item_name'] = $item_name;
			$line['item_location'] = $item_location;
			$line['quantity'] = $quantity;
			$line['location'] = $location;
			$line['branch_transfer'] = $branch_transfer;
			$line['stock_name'] = $stock_name;
			$line['in_stock'] = $in_stock;
		
			$this->set_push($items);
		}

		return FALSE;
	}
	

	public function delete_item($line)
	{
		$items = $this->get_cart();
		unset($items[$line]);
		$this->set_cart($items);
	}
	public function delete_item_push($line)
	{
		$items = $this->get_push();
		unset($items[$line]);
		$this->set_push($items);
	}
	public function delete_item_test($line)
	{
		$items = $this->get_lab_resultcart();
		unset($items[$line]);
		$this->set_lab_resultcart($items);
	}
	public function delete_reference_push($reference)
	{
		$items = $this->get_push();
		unset($items[$reference]);
		$this->set_push($items);
	}

	public function return_entire_sale($receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ', $receipt_sale_id);
		$sale_id = $pieces[1];

		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, -$row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber, TRUE, $row->print_option, $row->print_option);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}
	
	public function add_item_kit($external_item_kit_id, $item_location, $discount, $price_option, $kit_print_option, &$stock_warning)
	{
		//KIT #
		$pieces = explode(' ', $external_item_kit_id);
		$item_kit_id = $pieces[1];
		$result = TRUE;

		foreach($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			if($price_option == '0') // all
			{
				$price = null;
			}
			elseif($price_option == '1') // item kit only
			{
				$price = 0;
			}
			elseif($price_option == '2') // item kit plus stock items (assuming materials)
			{
				if($item_kit_item['stock_type'] == 0) // stock item
				{
					$price = null;
				}
				else
				{
					$price = 0;
				}
			}

			if($kit_print_option == '0') // all
			{
				$print_option = '0'; // print always
			}
			elseif($kit_print_option == '1') // priced
			{
				$print_option = '1'; // print if price not zero
			}
			elseif($kit_print_option == '2') // kit only if price is not zero
			{
				$print_option = '2'; // Do not include in list
			}

			$result &= $this->add_item($item_kit_item['item_id'], $item_kit_item['quantity'], $item_location, $discount, $price, null, null, null, $print_option, $item_kit_item['stock_type']);

			if($stock_warning == null)
			{
				$stock_warning = $this->out_of_stock($item_kit_item['item_id'], $item_location);
			}
		}
		
		return $result;
	}

	public function copy_entire_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber, TRUE, $row->print_option);
		}

		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount);
		}

		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
		$this->set_employee($this->CI->Sale->get_employee($sale_id)->person_id);
		$this->set_dinner_table($this->CI->Sale->get_dinner_table($sale_id));
	}

	public function get_cart_reordered($sale_id)
	{
		$this->empty_cart();
		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type, $row->qty_selected, $row->reference);
		}
		

		return $this->CI->session->userdata('sales_cart');
	}
	public function get_onlinesale_items_ordered($transfer_id)
	{
		$this->empty_cart();
		foreach($this->CI->Sale->get_onlinesale_items_ordered($transfer_id)->result() as $row)
		{
			$this->add_item($row->item_id, 1, 1, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type);
		}
		

		return $this->CI->session->userdata('sales_cart');
	}
	public function get_labcart_reordered($sale_id)
	{
		$this->empty_lab_accountcart();
		foreach($this->CI->Sale->get_sale_items_ordered($sale_id)->result() as $row)
		{
			$this->item_add_lab($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type);
		}

		return $this->CI->session->userdata('lab_accountcart');
	}
	public function get_labresultcart_reordered($sale_id)
	{
		$this->empty_lab_resultcart();
		foreach($this->CI->Sale->get_result_items_ordered($sale_id)->result() as $row)
		{
			$this->item_add_result($row->item_id, $row->print_option, $row->test_comment, $row->reference, $row->extra_name, $row->o_name, $row->h_name);
		}

		return $this->CI->session->userdata('lab_resultcart');
	}
	public function get_printresult_items_ordered($sale_id)
	{
		$this->empty_lab_resultcart();
		foreach($this->CI->Sale->get_printresult_items_ordered($sale_id)->result() as $row)
		{
			$this->item_add_result($row->item_id, $row->print_option, $row->test_comment, $row->reference, $row->extra_name, $row->o_name, $row->h_name);
		}

		return $this->CI->session->userdata('lab_resultcart');
		
	}
	public function get_labsaveresultcart_reordered($sale_id)
	{
		$this->empty_lab_resultcart();
		foreach($this->CI->Sale->get_savedresult_items_ordered($sale_id)->result() as $row)
		{
			$this->item_add_result($row->item_id, $row->print_option, $row->test_comment, $row->reference, $row->extra_name, $row->o_name, $row->h_name);
		}

		return $this->CI->session->userdata('lab_resultcart');
		
	}
	public function get_pushedcart_reordered($transfer_id)
	{
		$this->empty_push();
		foreach($this->CI->Sale->get_pushedcart_reordered($transfer_id)->result() as $row)
		{
			$this->add_item_push($row->item_id, $row->pushed_quantity, $row->request_from_branch_id, $row->request_to_branch_id,$row->transfer_id, $row->received_quantity, $row->unaccounted,$row->reference, $row->batch_no, $row->expiry);
		}

		return $this->CI->session->userdata('push_cart');
	}
	public function get_pulledcart_reordered($transfer_id)
	{
		$this->empty_push();
		foreach($this->CI->Sale->get_pulledcart_reordered($transfer_id)->result() as $row)
		{
			$this->add_item_pull($row->item_id, $row->pulled_quantity, $row->request_to_branch_id, $row->request_from_branch_id,$row->transfer_id, $row->received_quantity, $row->unaccounted,$row->reference, $row->batch_no, $row->expiry);
		}

		return $this->CI->session->userdata('push_cart');
	}
	public function get_remindercart_reordered()
	{
		$this->empty_reminder();
		foreach($this->CI->Sale->get_remindercart_reordered()->result() as $row)
		{
			$this->add_item_reminder($row->reminder_id, $row->reminder_name, $row->reminder_amount, $row->reminder_value);
		}

		return $this->CI->session->userdata('reminder_cart');
	}
	
	public function copy_entire_suspended_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type);
		}

		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount);
		}

		$suspended_sale_info = $this->CI->Sale->get_info($sale_id)->row();
		$this->set_customer($suspended_sale_info->person_id);
		$this->set_comment($suspended_sale_info->comment);

		$this->set_invoice_number($suspended_sale_info->invoice_number);
		$this->set_quote_number($suspended_sale_info->quote_number);
		$this->set_dinner_table($suspended_sale_info->dinner_table_id);
	}

	public function copy_entire_suspended_tables_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale_suspended->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount_percent, $row->item_unit_price,
				$row->description, $row->serialnumber, TRUE, $row->print_option, $row->stock_type);
		}

		foreach($this->CI->Sale_suspended->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type, $row->payment_amount);
		}

		$suspended_sale_info = $this->CI->Sale_suspended->get_info($sale_id)->row();
		$this->set_customer($suspended_sale_info->person_id);
		$this->set_comment($suspended_sale_info->comment);

		$this->set_invoice_number($suspended_sale_info->invoice_number);
		$this->set_quote_number($suspended_sale_info->quote_number);
		$this->set_dinner_table($suspended_sale_info->dinner_table_id);
	}
	public function clear_all_push()
	{
		$this->empty_push();
	
	}

	public function clear_all()
	{
		$this->set_invoice_number_enabled(FALSE);
		$this->clear_table();
		$this->empty_push();
		$this->empty_balance_id();
		$this->empty_sales_id();
		$this->empty_transfer_id();
		$this->empty_lab_accountcart();
		$this->empty_lab_resultcart();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->clear_invoice_number();
		$this->clear_quote_number();
		$this->clear_giftcard_remainder();
		$this->empty_payments();
		$this->remove_customer();
		$this->clear_cash_flags();
	}
	public function clear_all_lab()
	{
		$this->empty_sales_id();
		$this->set_invoice_number_enabled(FALSE);
		$this->clear_table();
		$this->empty_lab_accountcart();
		$this->empty_lab_resultcart();
		$this->empty_labcart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->clear_invoice_number();
		$this->clear_quote_number();
		$this->clear_giftcard_remainder();
		$this->empty_payments();
		$this->remove_customer();
		$this->clear_cash_flags();
	}

	public function clear_cash_flags()
	{
		$this->CI->session->unset_userdata('cash_rounding');
		$this->CI->session->unset_userdata('cash_mode');
		$this->CI->session->unset_userdata('payment_type');
	}

	public function reset_cash_flags()
	{
		if($this->CI->lang->line('payment_options_order') != 'cashdebitcredit')
		{
			$cash_mode = 1;
		}
		else
		{
			$cash_mode = 0;
		}
		$this->CI->session->set_userdata('cash_mode', $cash_mode);

		if($this->CI->config->config['cash_decimals'] < $this->CI->config->config['currency_decimals'])
		{
			$cash_rounding = 1;
		}
		else
		{
			$cash_rounding = 0;
		}
		$this->CI->session->set_userdata('cash_rounding', $cash_rounding);
	}
	
	public function is_customer_taxable()
	{
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);
		
		//Do not charge sales tax if we have a customer that is not taxable
		return $customer->taxable or $customer_id == -1;
	}

	/*
	 * This returns taxes for VAT taxes and for pre 3.1.0 sales taxes.
	 */
	public function get_taxes()
	{
		$register_mode = $this->CI->config->config['default_register_mode'];
		$tax_decimals = $this->CI->config->config['tax_decimals'];
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);
		$sales_taxes = array();
		//Do not charge sales tax if we have a customer that is not taxable
		if($customer->taxable or $customer_id == -1)
		{
			foreach($this->get_cart() as $line => $item)
			{
				// Start of current VAT tax apply

				$tax_info = $this->CI->Item_taxes->get_info($item['item_id']);
				$tax_group_sequence = 0;
				foreach($tax_info as $tax)
				{
					// This computes tax for each line item and adds it to the tax type total
					$tax_group = (float)$tax['percent'] . '% ' . $tax['name'];
					$tax_type = Tax_lib::TAX_TYPE_VAT;
					$tax_basis = $this->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
					$tax_amount = 0;

					if($this->CI->config->config['tax_included'])
					{
						$tax_amount = $this->get_item_tax($item['quantity'], $item['price'], $item['discount'], $tax['percent']);
					}
					elseif($this->CI->config->config['customer_sales_tax_support'] == '0')
					{
						$tax_amount = $this->CI->tax_lib->get_sales_tax_for_amount($tax_basis, $tax['percent'], '0', $tax_decimals);
					}

					if($tax_amount <> 0)
					{
						$this->CI->tax_lib->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $tax['percent'], $tax_basis, $tax_amount, $tax_group_sequence, '0', -1);
						$tax_group_sequence += 1;
					}
				}

				$tax_category = '';
				$tax_rate = '';
				$rounding_code = Rounding_code::HALF_UP;
				$tax_group_sequence = 0;
				$tax_code = '';

				if($this->CI->config->config['customer_sales_tax_support'] == '1')
				{
					// Now calculate what the sales taxes should be (storing them in the $sales_taxes array
					$this->CI->tax_lib->apply_sales_tax($item, $customer->city, $customer->state, $customer->sales_tax_code, $register_mode, 0, $sales_taxes, $tax_category, $tax_rate, $rounding_code, $tax_group_sequence, $tax_code);
				}

			}

			$this->CI->tax_lib->round_sales_taxes($sales_taxes);

		}

		return $sales_taxes;
	}

	public function apply_customer_discount($discount_percent)
	{	
		// Get all items in the cart so far...
		$items = $this->get_cart();
		
		foreach($items as &$item)
		{
			$quantity = $item['quantity'];
			//$price = $item['price'];
			if($item['stock_name'] == $this->lang->line('wholesale')){
				$price = $item['wholeprice'];
			}else{
				$price = $item['price'];
			}

			// set a new discount only if the current one is 0
			if($item['discount'] == 0)
			{
				$item['discount'] = $discount_percent;
				$item['total'] = $this->get_item_total($quantity, $price, $discount_percent);
				$item['discounted_total'] = $this->get_item_total($quantity, $price, $discount_percent, TRUE);
			}
		}

		$this->set_cart($items);
	}
	
	public function get_discount()
	{
		$discount = 0;
		foreach($this->get_cart() as $item)
		{
			if($item['discount'] > 0)
			{
				$item_discount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount']);
				$discount = bcadd($discount, $item_discount);
			}
		}

		return $discount;
	}

	public function get_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		return $this->calculate_subtotal($include_discount, $exclude_tax);
	}
	
	public function get_item_total_tax_exclusive($item_id, $quantity, $price, $discount_percentage, $include_discount = FALSE) 
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		$item_price = $this->get_item_total($quantity, $price, $discount_percentage, $include_discount);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$item_price = bcsub($item_price, $this->get_item_tax($quantity, $price, $discount_percentage, $tax_percentage));
		}
		
		return $item_price;
	}
	
	public function get_item_total($quantity, $price, $discount_percentage, $include_discount = FALSE)  
	{
		$total = bcmul($quantity, $price);
		if($include_discount)
		{
			$discount_amount = $this->get_item_discount($quantity, $price, $discount_percentage);

			return bcsub($total, $discount_amount);
		}

		return $total;
	}
	
	public function get_item_discount($quantity, $price, $discount_percentage)
	{
		$total = bcmul($quantity, $price);
		$discount_fraction = bcdiv($discount_percentage, 100);

		return bcmul($total, $discount_fraction);
	}
	
	public function get_item_tax($quantity, $price, $discount_percentage, $tax_percentage) 
	{
		$price = $this->get_item_total($quantity, $price, $discount_percentage, TRUE);
		if($this->CI->config->config['tax_included'])
		{
			$tax_fraction = bcadd(100, $tax_percentage);
			$tax_fraction = bcdiv($tax_fraction, 100);
			$price_tax_excl = bcdiv($price, $tax_fraction);

			return bcsub($price, $price_tax_excl);
		}
		$tax_fraction = bcdiv($tax_percentage, 100);

		return bcmul($price, $tax_fraction);
	}

	public function calculate_subtotal($include_discount = FALSE, $exclude_tax = FALSE)
	{
		$subtotal = 0;
		foreach($this->get_cart() as $item)
		{
			if($exclude_tax && $this->CI->config->config['tax_included'])
			{
				$subtotal = bcadd($subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], $include_discount));
			}
			else 
			{
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], $include_discount));
			}
		}

		return $subtotal;
	}

	public function get_total()
	{
		$total = $this->calculate_subtotal(TRUE);

		$cash_rounding = $this->CI->session->userdata('cash_rounding');

		foreach($this->get_taxes() as $sales_tax)
		{
			$total = bcadd($total, $sales_tax['sale_tax_amount']);
		}

		if($cash_rounding)
		{
			$rounded_total = $this->check_for_cash_rounding($total);
			return $rounded_total;
		}
		return $total;
	}

    public function get_empty_tables()
    {
    	return $this->CI->Dinner_table->get_empty_tables();		
    }

	public function check_for_cash_rounding($total)
	{
		$cash_decimals = $this->CI->config->config['cash_decimals'];
		$cash_rounding_code = $this->CI->config->config['cash_rounding_code'];
		$rounded_total = $total;

		if($cash_rounding_code == Rounding_code::HALF_UP)
		{
			$rounded_total = round ( $total, $cash_decimals, PHP_ROUND_HALF_UP);
		}
		elseif($cash_rounding_code == Rounding_code::HALF_DOWN)
		{
			$rounded_total = round ( $total, $cash_decimals, PHP_ROUND_HALF_DOWN);
		}
		elseif($cash_rounding_code == Rounding_code::HALF_EVEN)
		{
			$rounded_total = round ( $total, $cash_decimals, PHP_ROUND_HALF_EVEN);
		}
		elseif($cash_rounding_code == Rounding_code::HALF_ODD)
		{
			$rounded_total = round ( $total, $cash_decimals, PHP_ROUND_HALF_UP);
		}
		elseif($cash_rounding_code == Rounding_code::ROUND_UP)
		{
			$fig = (int) str_pad('1', $cash_decimals, '0');
			$rounded_total = (ceil($total * $fig) / $fig);
		}
		elseif($cash_rounding_code == Rounding_code::ROUND_DOWN)
		{
			$fig = (int) str_pad('1', $cash_decimals, '0');
			$rounded_total = (floor($total * $fig) / $fig);
		}
		elseif($cash_rounding_code == Rounding_code::HALF_FIVE)
		{
			$rounded_total = round($total / 5) * 5;
		}

		return $rounded_total;
	}
}

?>
