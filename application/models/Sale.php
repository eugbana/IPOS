<?php

class Sale extends CI_Model
{
	public function get_info($sale_id)
	{
		// NOTE: temporary tables are created to speed up searches due to the fact that they are ortogonal to the main query
		// create a temporary table to contain all the payments per sale
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
				'(
				SELECT payments.sale_id AS sale_id,
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE sales.sale_id = ' . $this->db->escape($sale_id) . '
				GROUP BY sale_id
			)'
		);

		$sale_price = '(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100))';
		$sale_total = 'SUM(' . $sale_price . ' + sales_items.vat)';
		/*if ($this->config->item('tax_included')) {
			$sale_total = 'SUM(' . $sale_price . ')';
			$sale_subtotal = 'SUM(' . $sale_price . ' - sales_items_taxes.tax)';
		} else {
			$sale_total = 'SUM(' . $sale_price . ' + sales_items_taxes.tax)';
			$sale_subtotal = 'SUM(' . $sale_price . ')';
		} */

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)'; //this is not true for wholesale sales

		$decimals = totals_decimals();

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
				'(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE sales.sale_id = ' . $this->db->escape($sale_id) . '
				GROUP BY sale_id, item_id, line
			)'
		);

		$this->db->select('
				sales.sale_id AS sale_id,sales_type,
				MAX(DATE(sales.sale_time)) AS sale_date,
				MAX(sales.sale_time) AS sale_time,
				MAX(sales.comment) AS comment,
				MAX(sales.sale_status) AS sale_status,
				MAX(sales.invoice_number) AS invoice_number,
				MAX(sales.quote_number) AS quote_number,
				MAX(sales.employee_id) AS employee_id,
				MAX(sales.customer_id) AS customer_id,
				MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer_name,
				MAX(customer_p.first_name) AS first_name,
				MAX(customer_p.last_name) AS last_name,
				MAX(customer_p.email) AS email,
				MAX(customer_p.comments) AS comments,
				
				
				' . "
				ROUND($sale_total, $decimals) AS amount_due,
				ROUND(SUM(payments.sale_payment_amount),2) AS amount_tendered,
				ROUND(SUM(payments.sale_payment_amount) - $sale_total,2) AS change_due,
				" . '
				MAX(payments.payment_type) AS payment_type
		');

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'left');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'left outer');
		$this->db->join(
			'sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'left outer'
		);

		$this->db->where('sales.sale_id', $sale_id);

		$this->db->group_by('sale_id');
		$this->db->order_by('sale_time', 'asc');

		return $this->db->get();
	}

	/*
	 Get number of rows for the takings (sales/manage) view
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters)->num_rows();
	}
	/*
	 Get number of rows for the takings (sales/manage) view
	*/
	public function get_found_rows_by_find($search, $filters)
	{
		return $this->find($search, $filters)->num_rows();
	}
	public function check_payment_type($payment, $filters)
	{
		if (count($payment) == 0) {
			//not payment for this sale
			return true; //so they see why. rare though
		}
		$set_filters = $this->get_set_filters($filters); //get filters that are set to true
		if (count($set_filters) == 0) {
			//no payment type is selected. so the sale that has this payment should be added to the sales that will be returned to the viewer
			return true;
		}

		//get all the payment types used in this particular payment
		$types = array();
		foreach ($payment as $k => $v) {
			$types[]  = strtolower($v->payment_type);
		}

		//loop through the types array and check if anyone is present in the set_filters arrray and return true
		//else return false after all the looping
		foreach ($types as $t => $va) {
			if (in_array($va, $set_filters))
				return true;
		}
		return false;
	}
	public function get_set_filters($filters)
	{
		$fi = array();
		foreach ($filters as $key => $value) {
			if ($key == 'Transfer' && $value != FALSE)
				$fi[] = "transfer";

			if ($key == 'Check' && $value != FALSE)
				$fi[] = "check";

			if ($key == 'Cash' && $value != FALSE)
				$fi[] = "cash";

			if ($key == 'Wallet' && $value != FALSE)
				$fi[] = "wallet";

			if ($key == 'POS' && $value != FALSE)
				$fi[] = "pos";
		}
		return $fi;
	}

	public function find_payments($sale_id)
	{

		$this->db->select("sales_payments.*");
		$this->db->from("sales_payments");
		$this->db->where("sale_id", $sale_id);



		$pays = $this->db->get();

		if ($pays->num_rows() > 0) {
			return $pays;
		} else {
			return null;
		}
	}
	/*
	 Get the sales
	*/
	public function find($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sale_time', $order = 'desc')
	{
		$start = $filters['start_date'] . ' 00:00:00';
		$end = $filters['end_date'] . ' 23:59:59';
		$total_amount = "SUM(sales_items.quantity_purchased * sales_items.item_unit_price)";
		$total_vat = "SUM(sales_items.vat)";
		$total_discount = "ROUND(SUM(sales_items.quantity_purchased * sales_items.item_unit_price * (sales_items.discount_percent / 100)),2)";
		$total_payment = "SUM(sales_payments.payment_amount)";
		//total_amount_paid and change_due are intentionally wrong for future correction
		$this->db->select("sales.*,
		($total_amount *2) AS total_amount_paid,
		($total_amount - $total_discount + $total_vat) AS total_amount_due,
		$total_vat AS total_vat,
		$total_discount AS total_discount,
		($total_vat *1) AS change_due,
		SUM(sales_items.quantity_purchased) as item_qty,
		people.last_name AS customer_last_name,
		people.first_name AS customer_first_name,
		employee.last_name AS employee_last_name,
		employee.first_name AS employee_first_name,
		MAX(sales_items.item_location) AS item_location
		");

		// $this->db->select("sales.*,
		// $total_payment AS total_amount_paid,
		// ($total_amount - $total_discount + $total_vat) AS total_amount_due,
		// $total_vat AS total_vat,
		// $total_discount AS total_discount,
		// ($total_payment - ($total_amount - $total_discount + $total_vat)) AS change_due,
		// people.last_name AS customer_last_name,
		// people.first_name AS customer_first_name,
		// MAX(sales_items.item_location) AS item_location
		// ");
		$this->db->from("sales AS sales");
		//$this->db->join("sales_payments AS sales_payments", "sales_payments.sale_id = sales.sale_id", 'left outer');
		$this->db->join("sales_items AS sales_items", "sales_items.sale_id = sales.sale_id", 'left');

		$this->db->join("people AS people", "people.person_id = sales.customer_id", 'left');
		$this->db->join("people AS employee", "employee.person_id = sales.employee_id", 'left');




		//$this->db->where("sales.sale_time BETWEEN" . $filters['start_date'] . " AND " . $filters['end_date']);
		$this->db->where("sales.sale_time >= ", $start);
		$this->db->where("sales.sale_time <= ", $end);

		if (!empty($search)) {
			if ($filters['is_valid_receipt'] != FALSE) {
				$pieces = explode(' ', $search);
				$this->db->where('sales.sale_id', $pieces[1]);
			} else {
				$this->db->group_start();
				// customer last name
				$this->db->like('people.last_name', $search);
				// customer first name
				$this->db->or_like('people.first_name', $search);

				// customer first and last name
				$this->db->or_like('CONCAT(people.first_name, " ", people.last_name)', $search);



				$this->db->group_end();
			}
		}

		if ($filters['location_id'] != 'all') {
			$this->db->where('sales_items.item_location', $filters['location_id']);
		}
		if ($filters['employee_id'] != 'all') {
			$this->db->where('sales.employee_id', $filters['employee_id']);
		}

		if ($filters['sale_type'] == 'sales') {
			$this->db->where('sales_items.quantity_purchased > 0');
		} elseif ($filters['sale_type'] == 'returns') {
			$this->db->where('sales_items.quantity_purchased < 0');
		}

		// if ($filters['only_invoices'] != FALSE) {
		// 	$this->db->where('sales.invoice_number IS NOT NULL');
		// }

		// if ($filters['only_cash'] != FALSE) {
		// 	$this->db->group_start();
		// 	$this->db->like('sales_payments.payment_type', "Cash");
		// 	$this->db->or_where('sales_payments.payment_type IS NULL');
		// 	$this->db->group_end();
		// }

		// if ($filters['only_check'] != FALSE) {
		// 	$this->db->like('sales_payments.payment_type', "Check");
		// }
		// if ($filters['only_pos'] != FALSE) {
		// 	$this->db->like('sales_payments.payment_type', "POS");
		// }
		// if ($filters['only_transfer'] != FALSE) {
		// 	$this->db->like('sales_payments.payment_type', "Transfer");
		// }

		$this->db->group_by('sale_id');
		$this->db->order_by($sort, $order);

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'sale_time', $order = 'desc')
	{
		$where = '';

		if (empty($this->config->item('date_or_time_format'))) {
			$where .= 'DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']) . ' AND sales.sale_status = 0 ';
		} else {
			$where .= 'sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])) . ' AND sales.sale_status = 0 ';
		}

		// NOTE: temporary tables are created to speed up searches due to the fact that they are ortogonal to the main query
		// create a temporary table to contain all the payments per sale item
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
				' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE ' . $where . '
				GROUP BY sale_id
			)'
		);

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';

		if ($this->config->item('tax_included')) {
			$sale_total = 'SUM(' . $sale_price . ')';
			$sale_subtotal = 'SUM(' . $sale_price . ' - sales_items_taxes.tax)';
		} else {
			$sale_total = 'SUM(' . $sale_price . ' + sales_items_taxes.tax)';
			$sale_subtotal = 'SUM(' . $sale_price . ')';
		}

		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';

		$decimals = totals_decimals();

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
				' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) as tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		$this->db->select('
				sales.sale_id AS sale_id,
				MAX(DATE(sales.sale_time)) AS sale_date,
				MAX(sales.sale_time) AS sale_time,
				MAX(sales.invoice_number) AS invoice_number,
				MAX(sales.quote_number) AS quote_number,
				SUM(sales_items.quantity_purchased) AS items_purchased,
				MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer_name,
				MAX(customer.company_name) AS company_name,
				' . "
				IFNULL(ROUND($sale_subtotal, $decimals), ROUND($sale_total - IFNULL(SUM(sales_items_taxes.tax), 0), $decimals)) AS subtotal,
				IFNULL(ROUND(SUM(sales_items_taxes.tax), $decimals), 0) AS tax,
				IFNULL(ROUND($sale_total, $decimals), ROUND($sale_subtotal, $decimals)) AS total,
				IFNULL(ROUND($sale_cost, $decimals), 0) AS cost,
				IFNULL(ROUND($sale_total - IFNULL(SUM(sales_items_taxes.tax), 0) - $sale_cost, $decimals), ROUND($sale_subtotal - $sale_cost, $decimals)) AS profit,
				IFNULL(ROUND($sale_total, $decimals), ROUND($sale_subtotal, $decimals)) AS amount_due,
				MAX(payments.sale_payment_amount) AS amount_tendered,
				(MAX(payments.sale_payment_amount) - IFNULL(ROUND($sale_total, $decimals), ROUND($sale_subtotal, $decimals))) AS change_due,
				" . '
				MAX(payments.payment_type) AS payment_type
		');

		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'left');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->join('sales_payments_temp AS payments', 'sales.sale_id = payments.sale_id', 'left outer');
		$this->db->join(
			'sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'left outer'
		);

		$this->db->where($where);

		if (!empty($search)) {
			if ($filters['is_valid_receipt'] != FALSE) {
				$pieces = explode(' ', $search);
				$this->db->where('sales.sale_id', $pieces[1]);
			} else {
				$this->db->group_start();
				// customer last name
				$this->db->like('customer_p.last_name', $search);
				// customer first name
				$this->db->or_like('customer_p.first_name', $search);
				// customer first and last name
				$this->db->or_like('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);
				// customer company name
				$this->db->or_like('customer.company_name', $search);
				$this->db->group_end();
			}
		}

		if ($filters['location_id'] != 'all') {
			$this->db->where('sales_items.item_location', $filters['location_id']);
		}

		if ($filters['sale_type'] == 'sales') {
			$this->db->where('sales_items.quantity_purchased > 0');
		} elseif ($filters['sale_type'] == 'returns') {
			$this->db->where('sales_items.quantity_purchased < 0');
		}

		if ($filters['only_invoices'] != FALSE) {
			$this->db->where('sales.invoice_number IS NOT NULL');
		}

		if ($filters['only_cash'] != FALSE) {
			$this->db->group_start();
			$this->db->like('payments.payment_type', $this->lang->line('sales_cash'));
			$this->db->or_where('payments.payment_type IS NULL');
			$this->db->group_end();
		}

		if ($filters['only_check'] != FALSE) {
			$this->db->like('payments.payment_type', $this->lang->line('sales_check'));
		}

		$this->db->group_by('sale_id');
		$this->db->order_by($sort, $order);

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	 Get the payment summary for the takings (sales/manage) view
	*/
	public function get_payments_summary($search, $filters)
	{
		// get payment summary
		$this->db->select('payment_type, count(*) AS count, SUM(payment_amount) AS payment_amount');
		$this->db->from('sales AS sales');
		$this->db->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
		$this->db->join('people AS customer_p', 'sales.customer_id = customer_p.person_id', 'left');
		$this->db->join('customers AS customer', 'sales.customer_id = customer.person_id', 'left');

		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
		} else {
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		if (!empty($search)) {
			if ($filters['is_valid_receipt'] != FALSE) {
				$pieces = explode(' ', $search);
				$this->db->where('sales.sale_id', $pieces[1]);
			} else {
				$this->db->group_start();
				// customer last name
				$this->db->like('customer_p.last_name', $search);
				// customer first name
				$this->db->or_like('customer_p.first_name', $search);
				// customer first and last name
				$this->db->or_like('CONCAT(customer_p.first_name, " ", customer_p.last_name)', $search);
				// customer company name
				$this->db->or_like('customer.company_name', $search);
				$this->db->group_end();
			}
		}

		if ($filters['sale_type'] == 'sales') {
			$this->db->where('payment_amount > 0');
		} elseif ($filters['sale_type'] == 'returns') {
			$this->db->where('payment_amount < 0');
		}

		if ($filters['only_invoices'] != FALSE) {
			$this->db->where('invoice_number IS NOT NULL');
		}

		if ($filters['only_cash'] != FALSE) {
			$this->db->like('payment_type', $this->lang->line('sales_cash'));
		}

		if ($filters['only_check'] != FALSE) {
			$this->db->like('payment_type', $this->lang->line('sales_check'));
		}

		$this->db->group_by('payment_type');

		$payments = $this->db->get()->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach ($payments as $key => $payment) {
			if (strstr($payment['payment_type'], $this->lang->line('sales_giftcard')) != FALSE) {
				$gift_card_count  += $payment['count'];
				$gift_card_amount += $payment['payment_amount'];

				// remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if ($gift_card_count > 0) {
			$payments[] = array('payment_type' => $this->lang->line('sales_giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount);
		}

		return $payments;
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('sales');

		return $this->db->count_all_results();
	}
	public function get_expired_item($item_id = null){
	    $this->db->select('batch_no','quantity');
	    $this->db->from('item_expiry');
        $this->db->where('expiry <',date('Y-m-d ').'23:59:00');
        $this->db->where('quantity >',0);
	    if($item_id){
	        $this->db->where('item_id',$item_id);
        }
	    return $this->db->get()->result();
    }
    public function get_total_expired_item($item_id = null){
        $this->db->select_sum('quantity');
        $this->db->from('item_expiry');
        $this->db->where('expiry <=',date('Y-m-d').' 23:59:00');
        $this->db->where('quantity >',0);
        if($item_id){
            $this->db->where('item_id',$item_id);
        }
        return $this->db->get()->row()->quantity;
    }

	public function get_search_suggestions($search, $limt = 99999)
	{
		$suggestions = array();
		$this->db->cache();

		if (!$this->is_valid_receipt($search)) {
			
			$this->db->distinct();
			$this->db->select('first_name, last_name');
			$this->db->from('sales');
			$this->db->join('people', 'people.person_id = sales.customer_id');
			$this->db->like('last_name', $search);
			$this->db->or_like('first_name', $search);
			$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
			$this->db->or_like('company_name', $search);
			$this->db->order_by('last_name', 'asc');

			foreach ($this->db->get()->result_array() as $result) {
				$suggestions[] = array('label' => $result['first_name'] . ' ' . $result['last_name']);
			}
		} else {
			$suggestions[] = array('label' => $search);
		}

		return $suggestions;
	}

	/*
	Gets total of invoice rows
	*/
	public function get_invoice_count()
	{
		$this->db->from('sales');
		$this->db->where('invoice_number IS NOT NULL');

		return $this->db->count_all_results();
	}

	public function get_sale_by_invoice_number($invoice_number)
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);

		return $this->db->get();
	}

	public function get_invoice_number_for_year($year = '', $start_from = 0)
	{
		$year = $year == '' ? date('Y') : $year;
		$this->db->select('COUNT( 1 ) AS invoice_number_year');
		$this->db->from('sales');
		$this->db->where('DATE_FORMAT(sale_time, "%Y" ) = ', $year);
		$this->db->where('invoice_number IS NOT NULL');
		$result = $this->db->get()->row_array();

		return ($start_from + $result['invoice_number_year']);
	}

	public function is_valid_receipt(&$receipt_sale_id)
	{
		if (!empty($receipt_sale_id)) {
			//POS #
			$pieces = explode(' ', $receipt_sale_id);

			if (count($pieces) == 2 && preg_match('/(POS)/i', $pieces[0])) {
				return $this->exists($pieces[1]);
			} elseif ($this->config->item('invoice_enable') == TRUE) {
				$sale_info = $this->get_sale_by_invoice_number($receipt_sale_id);
				if ($sale_info->num_rows() > 0) {
					$receipt_sale_id = 'POS ' . $sale_info->row()->sale_id;

					return TRUE;
				}
			}
		}

		return FALSE;
	}



	public function update($sale_id, $sale_data, $payments)
	{
		$this->db->where('sale_id', $sale_id);
		$success = $this->db->update('sales', $sale_data);

		// touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale
		if ($success && !empty($payments)) {
			//Run these queries as a transaction, we want to make sure we do all or nothing
			$this->db->trans_start();

			// first delete all payments
			$this->db->delete('sales_payments', array('sale_id' => $sale_id));

			// add new payments
			foreach ($payments as $payment) {
				$sales_payments_data = array(
					'sale_id' => $sale_id,
					'payment_type' => $payment['payment_type'],
					'payment_amount' => $payment['payment_amount']
				);

				$success = $this->db->insert('sales_payments', $sales_payments_data);
			}

			$this->db->trans_complete();

			$success &= $this->db->trans_status();
		}

		return $success;
	}
	public function close_register($sale_data, $register_id)
	{
		$this->db->where('register_id', $register_id);
		$success = $this->db->update('open_register', $sale_data);

		// touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale

		return $success;
	}
	public function save_register($sale_data)
	{

		if (is_numeric($sale_data['balance_entered'])) {
			$success = $this->db->insert('open_register', $sale_data);

			// touch payment only if update sale is successful and there is a payments object otherwise the result would be to delete all the payments associated to the sale


			return $success;
		} else {
			return False;
		}
	}

	public function save_request($push_items, $items, $transfer_type, $push_from_branch, $pushed_to_branch, $status, $transfer_id = FALSE)
	{
		$transfer_data = array(
			'request_time'	 => date('Y-m-d H:i:s'),
			'employee_id' => $this->CI->Employee->get_logged_in_employee_info()->person_id,
			'transfer_type' => $transfer_type,
			'request_from_branch_id' => $push_from_branch,
			'request_to_branch_id' => $pushed_to_branch,
			'status' => $status
		);

		// // Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('item_request', $transfer_data);
		//Get the id of the last inserted transfer
		$trans_id = $this->db->insert_id();
	
		$all_items = array();

		foreach ($push_items as $line => $item) {
		$cur_item_info = $this->Item->get_info($item['item_id']);

		$push_item = array(
			"item_number" => $item['item_number'],
			"quantity" => $item['quantity']
		);
		array_push($all_items, $push_item);

			$push_items_data = array(
				'request_id'		=> $trans_id,
				'item_id'		 => $item['item_id'],
				'line'		 => $item['line'],
				'pushed_quantity'	=> $item['quantity'],
				'request_from_branch_id' => $push_from_branch,
				'request_to_branch_id' => $pushed_to_branch, //$item['branch_transfer'],
				'reference' => $item['reference'],
				'batch_no' => $item['batch_no'],
				'item_cost_price' => $item['cost_price'],
				'item_unit_price' => $item['unit_price'],
				// 'transfer_price' => $item['transfer_price'],
				'transfer_price' => $cur_item_info->unit_price,
				'expiry' => $item['expiry'] == '' ? NULL : $item['expiry'],
				'received_quantity'		=> 0,
				'pulled_quantity'		=> 0,
				'unaccounted'		=> 0
			);

			$this->db->insert('request_items', $push_items_data);
		}

		//JUDE: Perform CURL here to request for items
		$post = array(
			"branch_name" => $this->config->item('branch_name'),
			"request_branch" => $pushed_to_branch,
			"items" => $all_items
		);
		$req_items = json_encode($post);

		$curl = curl_init();
        
		curl_setopt_array($curl, array(
		CURLOPT_URL =>  $this->config->item('erd_baseurl'). "/api/transfer/make_request",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		// CURLOPT_POSTFIELDS =>"{\n \"branch_name\" : \"capitalhub\", \"request_branch\" ; \"garki\", \"items\" ; {$req_items} \n}",
		CURLOPT_POSTFIELDS => $req_items,
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/json"
		),
		));

		$response = curl_exec($curl);
	
		curl_close($curl);

		$response = json_decode($response, true);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE && $response['status'] == 1) {
			return -1;
		}

		return $trans_id;
	}

	public function save_transfer_of_items($push_items, $request_id, $items, $transfer_type, $to_branch, $status)
	{
		$location = 2;
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$transfer_data = array(
			'transfer_time'	 => date('Y-m-d H:i:s'),
			'employee_id' => $this->CI->Employee->get_logged_in_employee_info()->person_id,
			'transfer_type' => $transfer_type,
			'transfer_to_branch' => $to_branch,
			'external_request_id' => $request_id,
			'status' => $status
		);

		// // Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('transfers', $transfer_data);
		//Get the id of the last inserted transfer
		$trans_id = $this->db->insert_id();
	
		$all_items = array();

		foreach ($push_items as $line => $item) {
		$cur_item_info = $this->Item->get_info($item['item_id']);

		$push_item = array(
			"item_number" => $item['item_number'],
			"accepted_quantity" => $item['accepted_quantity']
		);

		array_push($all_items, $push_item);

			$push_items_data = array(
				'transfer_id'		=> $trans_id,
				'item_id'		 => $item['item_id'],
				'line'		 => $item['line'],
				'requested_quantity'	=> $item['quantity'],
				'accepted_quantity'	=> $item['accepted_quantity'],
				'transfer_to_branch' => $to_branch,
				'item_cost_price' => $item['cost_price'],
				'item_unit_price' => $item['unit_price'],
				'transfer_price' => $cur_item_info->unit_price,
				'expiry' => $item['expiry'] == '' ? NULL : $item['expiry'],
				'unaccounted'		=> 0
			);

			$this->db->insert('transfer_items', $push_items_data);

			$items_accepted = $item['accepted_quantity'];
			$item_quantity = $this->Item_quantity->get_item_quantity($cur_item_info->item_id, $location);

			//Update stock quantity
			$this->Item_quantity->save(array(
				'quantity' => $item_quantity->quantity - $items_accepted, 'item_id' => $cur_item_info->item_id,
				'location_id' => $location,
			), $cur_item_info->item_id, $location);
			

			$inv_data = array(
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $cur_item_info->item_id,
				'trans_user' => $employee_id,
				'trans_location' => $location,
				'trans_comment' => 'Transfer of Items to branch: ' .$to_branch,
				'trans_inventory' => $items_accepted,
				'selling_price' => $cur_item_info->unit_price,
				'trans_remaining' => $item_quantity->quantity - $items_accepted
			);

			$this->Inventory->insert($inv_data);
			//notify item sale_tracker here if it doesnt exists
			$this->Sale->saveitemtracker($cur_item_info->item_number);
		}

		//JUDE: Perform CURL here to request for items
		$post = array(
			"branch_name" => $this->config->item('branch_name'),
			"request_id" => $request_id,
			"items" => $all_items
		);
		$req_items = json_encode($post);

		// echo "<pre>";
		// print_r($req_items);
		// echo "</pre>";
		// die();

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/accept_request",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		// CURLOPT_POSTFIELDS =>"{\n \"branch_name\" : \"capitalhub\", \"request_branch\" ; \"garki\", \"items\" ; {$req_items} \n}",
		CURLOPT_POSTFIELDS => $req_items,
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/json"
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		// echo "<pre>";
		// // print_r($req_items);
		// print_r($response);
		// echo "</pre>";
		// die();

		$this->db->trans_complete();

//		if ($this->db->trans_status() === FALSE && $response['status'] == 1) {
        if ($this->db->trans_status() === FALSE && $response['status'] == 1) {
			return -1;
		}

		return $trans_id;
	}

	public function get_single_request($request_id, $branch){
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->item('erd_baseurl'). "/api/transfer/" .$branch . "/get_pending/".$request_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));

		$response = curl_exec($curl);

		curl_close($curl);
	
		$response = json_decode($response,true);
	}

	private function getShortForm($strin){
        $words = preg_split("/\s+/", $strin);
        $acronym = "";

        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        return $acronym;
    }
	public function save_transfer($push_items, $items, $transfer_type, $push_from_branch, $pushed_to_branch, $status, $transfer_id = FALSE)
	{
//	    var_dump($push_items);
//	    exit();
	    $employee_info = $this->CI->Employee->get_logged_in_employee_info();
	    $branch_from_info = $this->Employee->get_branchinfo($push_from_branch);
	    $branch_to_info = $this->Employee->get_branchinfo($pushed_to_branch);
        $push_from_branch_name = $branch_from_info->location_name;
        $pushed_to_branch_name = $branch_to_info->location_name;
		$transfer_data = array(
			'transfer_time'	 => date('Y-m-d H:i:s'),
			'employee_id' => $employee_info->person_id,
			'transfer_type' => $transfer_type,
			'request_from_branch_id' => $push_from_branch,
			'request_to_branch_id' => $pushed_to_branch,
			'status' => $status
		);

        $total_quantity = 0;
        $total_price = 0;
		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_begin();

		$this->db->insert('item_transfer', $transfer_data);
		//Get the id of the last inserted transfer
		$trans_id = $this->db->insert_id();
//        echo "hi before";
//        echo $push_items;
//        exit();
		foreach($push_items as $line => $item) {
//		    echo "Hi";
//		$cur_item_info = $this->Item->get_info($item['item_id']); //redundant

			$push_items_data = array(
				'transfer_id'		=> $trans_id,
				'item_id'		 => $item['item_id'],
				'line'		 => $item['line'],
				'pushed_quantity'	=> $item['quantity'],
				'request_from_branch_id' => $push_from_branch,
				'request_to_branch_id' => $pushed_to_branch, //$item['branch_transfer'],
				'reference' => $item['reference'],
				'batch_no' => $item['batch_no'],
				'item_cost_price' => $item['cost_price'],
				'item_unit_price' => $item['unit_price'],
				'transfer_price' => $item['transfer_price'],
				'expiry' => $item['expiry'] == '' ? NULL : $item['expiry'],
				'received_quantity'		=> 0,
				'pulled_quantity'		=> 0,
				'unaccounted'		=> 0
			);
			$total_quantity += $item['quantity'];
			$total_price += $item['transfer_price']*$item['quantity'];
//			var_dump($push_items_data);
//			break;

			$this->db->insert('items_push', $push_items_data);
			//notify item sale_tracker here if it doesnt exists
//			$this->Sale->saveitemtracker($cur_item_info->item_number);
            $this->saveitemtracker($item['item_number']);
			//Since this is push , item are reduced from this branch
			//See the change_quantity on Item_quantity model to know the item quantity is mutliply by negative one. i.e substraction
			$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
			$quantity = $item['quantity'];
			if ($transfer_type == "PUSH") {
				$quantity = $item['quantity'] * -1; //push transfer lead to depletion of stock for the transfering branch
			}
			$this->CI->Item_quantity->change_quantity($item['item_id'], $push_from_branch, $quantity,["item_id"=>$item['item_id'],
                'batch_no'=>$item['batch_no'],'location_id'=>$push_from_branch,'quantity'=>$item['quantity'],'expiry'=>$item['selected_batch_expiry'],
                'total_quantity'=>$item_quantity]);

			//make sure to increase quantity for the transfer_to branch when it is hosted

			//Add this to inventory
			// Inventory Count Details

			$rem_quantity = $item_quantity->quantity - $item['quantity']; //inventory is depleting

            //check if remaining quantity is still greater than zero
            if($rem_quantity < 0){
                $this->db->trans_rollback();
                $i_name = $item['name'];
                return "Quantity transferred for $i_name is greater than available quantity, please update your inventory.";
            }

			$transfer_remarks = $transfer_type . ' ' . $trans_id;
			$employee_id = $employee_info->person_id;
			$inv_data = array(
				'trans_date'		=> date('Y-m-d H:i:s'),
				'trans_items'		=> $item['item_id'],
				'trans_user'		=> $employee_id,
				'trans_location'	=> $push_from_branch, //$item['item_location'],
				'trans_comment'		=> $transfer_remarks,
                'selling_price' => $item['unit_price'],
				'trans_inventory'	=> ($transfer_type == "PUSH") ? -$item['quantity'] : $item['quantity'],
				'trans_remaining' => $rem_quantity
			);
			$this->Inventory->insert($inv_data);
		}
//		$reference = $this->getShortForm($push_from_branch_name)."PUSH".$trans_id.$this->getShortForm($pushed_to_branch_name);
        $reference = $branch_from_info->brid."PUSH".$trans_id.$branch_to_info->brid;
		$this->db->update('item_transfer',['reference'=>$reference],['transfer_id'=>$trans_id]);

//		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
        $this->db->trans_commit();

        $erd_url = ERD_BASE_URL.'/transfer/push';
//        $erd_url ='localhost:8060/api/transfer/push';
//		var_dump($erd_url);
//		exit();
        $erd_data = [
            "caller"=> "HaslekIsBae",
            "items"=>$push_items,
            "transfer_reference"=>$reference,
            "from_branch" => $push_from_branch_name,
            "to_branch" => $pushed_to_branch_name,
            "to_branch_id" =>$branch_to_info->brid,
            "from_branch_id" =>$branch_from_info->brid,
            "total_quantity"=> $total_quantity,
            "total_price" => $total_price,
            "sent_by" => $employee_info->first_name.' '.$employee_info->last_name
        ];
        $this->load->library('External_calls');
        $erd_response = External_calls::makeRequest($erd_url,$erd_data,"POST");
        $erd_response_data = [
            "transfer_id"=> $trans_id,
            "transfer_reference"=>$reference,
            "sent_by"=> $employee_info->first_name.' '.$employee_info->last_name
        ];
        $erd_response = json_decode($erd_response,true);
        $erd_response = $erd_response== null ? "":$erd_response;
        if(is_string($erd_response) || $erd_response['status'] != "00"){
            $erd_response_data["response"] = is_string($erd_response)?$erd_response:$erd_response['message'];
            $erd_response_data["status"] = is_string($erd_response)?$erd_response:$erd_response['status'];
        }else{
            $erd_response_data["response"] = $erd_response['message']?$erd_response['message']:$erd_response['error'];
            $erd_response_data["status"] = $erd_response['status'];
        }
        $this->db->insert('erd_transfer_calls',$erd_response_data);
		return $trans_id;
	}

	public function save_pull_transfer($push_items, $items, $transfer_type, $push_from_branch, $pushed_to_branch, $status, $transfer_id = FALSE)
	{
		$transfer_data = array(
			'transfer_time'	 => date('Y-m-d H:i:s'),
			'transfer_type' => $transfer_type,
			'request_from_branch_id' => $push_from_branch,
			'request_to_branch_id' => $pushed_to_branch,
			'status' => $status
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('item_transfer', $transfer_data);
		$trans_id = $this->db->insert_id();

		foreach ($push_items as $line => $item) {
			if ($pushed_to_branch == $item['branch_transfer']) {
				$push_items_data = array(
					'transfer_id'		=> $trans_id,
					'item_id'		 => $item['item_id'],
					'line'		 => $item['line'],
					'pushed_quantity'	=> 0,
					'request_from_branch_id' => $push_from_branch,
					'request_to_branch_id' => $item['branch_transfer'],
					'reference' => $item['reference'],
					'batch_no' => $item['batch_no'],
					'expiry' => $item['expiry'] == '' ? NULL : $item['expiry'],
					'received_quantity'		=> 0,
					'pulled_quantity'		=> $item['quantity'],
					'unaccounted'		=> 0

				);

				$this->db->insert('items_push', $push_items_data);
				//Since this is pull , item are added to this branch
				//We can't change quantity here
				//$this->CI->Item_quantity->change_quantity($item['item_id'], $push_from_branch, $item['quantity']);

				//make sure to increase quantity for the transfer_to branch when it is hosted

				//Add this to inventory. Inventory is not needed here since it's just a request that's being made
				// Inventory Count Details
				/* $transfer_remarks = 'PULL ' . $trans_id;
				$employee_id = $this->CI->Employee->get_logged_in_employee_info()->person_id;
				$inv_data = array(
					'trans_date'		=> date('Y-m-d H:i:s'),
					'trans_items'		=> $item['item_id'],
					'trans_user'		=> $employee_id,
					'trans_location'	=> $item['item_location'],
					'trans_comment'		=> $transfer_remarks,
					'trans_inventory'	=> $item['quantity']
				);
				$this->Inventory->insert($inv_data); */
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $trans_id;
	}
	public function update_lab_invoice_items($inv_id,$lab_items,$test_type=1){
	    $this->db->trans_start();
	    $this->db->delete('laboratory_invoice_item',['invoice_id'=>$inv_id]);
        foreach ($lab_items as $line => $item) {

            $push_items_data = array(
                'invoice_id'		=> $inv_id,
                'item_id'	=> $item,
                'test_type'	=> $test_type,
            );

            $this->db->insert('laboratory_invoice_item', $push_items_data);
        }
        //return $invoice_id;

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return -1;
        }

        return $inv_id;
    }
    public function delete_lab_invoice_items($inv_id){
        $this->db->trans_start();
        $this->db->delete('laboratory_invoice', ['invoice_id'=>$inv_id]);
        $this->db->delete('laboratory_invoice_item',['invoice_id'=>$inv_id]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return -1;
        }
    }
	public function save_laboratory_invoice($lab_items, $test_type, $customer_id, $invoice_id = false)
	{


		$transfer_data = array(
			'invoice_id' => $invoice_id,
			'invoice_time'	 => date('Y-m-d H:i:s'),
			'person_id' => $customer_id,
			'doctor_name' => 'Daniel Gbenga',
			'status' => 0
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('laboratory_invoice', $transfer_data);
		$invoice_id = $this->db->insert_id();

		foreach ($lab_items as $line => $item) {

			$push_items_data = array(
				'invoice_id'		=> $invoice_id,
				'item_id'	=> $item['item_id'],
				'test_type'	=> $test_type,


			);

			$this->db->insert('laboratory_invoice_item', $push_items_data);
		}
		//return $invoice_id;

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $invoice_id;
	}

	public function itemtrackerexists($item_number)
	{
		$this->db->from('items_sold_tracker');
		$this->db->where('items_sold_tracker.item_number', $item_number);

		return ($this->db->get()->num_rows() == 1);
	}

	public function saveitemtracker($item_number){
		$success = FALSE;
		$item_data = array();

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

			if ($item_number && !$this->itemtrackerexists($item_number)) {
				$item_data['item_number'] = $item_number;
				$success = $this->db->insert('items_sold_tracker', $item_data);
			}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}
	/*
	 * To capture return on sales item and making sure item returned does not exceed quantity purchased in the sale receipt or of batch that does not exist
	 */
    public function check_return_batch($item_id,$batch_no,$location){
        $batch_exist_query = $this->db->from('item_expiry')
            ->where(['item_id'=>$item_id,'batch_no'=>$batch_no,'location_id'=>$location])
            ->get()->row();
        return $batch_exist_query == null;
    }
    public function eligible_returns($sale_id){
        $sale_items = $this->db->select('*')
            ->from('sales_items')
            ->where('sale_id',$sale_id)
            ->get()->result();
        $already_returned_items = $this->get_returned_sales_items_summary($sale_id);

        $returned_item_keys = [];
        $registered_returns = $this->sale_lib->get_registered_returns();
        foreach ($already_returned_items as $item){
            $returned_item_keys[$item->item_id] = $item->quantity;
        }
        $eligible_returns = [];
        foreach ($sale_items as $sale_item){
            $sale_item->quantity_purchased = floor($sale_item->quantity_purchased);
            if(array_key_exists($sale_item->item_id,$returned_item_keys)){
                $sale_item->quantity_purchased -= abs($returned_item_keys[$sale_item->item_id]);
//                if($sale_item->quantity_purchased > 0 && in_array($sale_item->item_id,$registered_returns)){
                if($sale_item->quantity_purchased > 0 ){
                    $sale_item->quantity_purchased = 0 - $sale_item->quantity_purchased;
                    $eligible_returns[] = $sale_item;
                }
            }else{
                $sale_item->quantity_purchased = 0 - $sale_item->quantity_purchased;
                $eligible_returns[] = $sale_item;
//                if(in_array($sale_item->item_id,$registered_returns)){
//
//                }
            }
        }
        return $eligible_returns;
    }
    private function get_returned_sales_items_summary($sale_id){
//        echo "Hello returns";
//        return "hewello";
//        $start1 = time();
         $returns = $this->db->select('quantity_purchased,item_id,')
            ->from('sales_items')
            ->where(['is_return'=>1,'returned_from'=>$sale_id])
            ->get()->result();
        $result = [];
         if($returns!= null && count($returns)>0){
//             var_dump($returns);
//             echo "<br> returned items ids above <br>";
             $query = "Select DISTINCT item_id from ipos_sales_items where is_return = 1 AND returned_from = '$sale_id'";
             $item_ids = $this->db->query($query)->result();
//                 $this->db->select('item_id')
////                 ->distinct()
//                 ->from('sales_items')
//                 ->where(['is_return'=>1,'returned_from'=>$sale_id])
//                 ->get()->result();
//             var_dump($item_ids);
//             echo "<br> returned items ids above <br>";
             foreach ($item_ids as $item_id){
                 $it_q_id= [];
                 $q_ret = 0;
                 foreach ($returns as $return){
                     if($return->item_id ==$item_id->item_id){
                         $q_ret += $return->quantity_purchased;
                     }
                 }
                 $it_q_id['item_id']= $item_id->item_id;
                 $it_q_id['quantity'] = $q_ret;
                 $result[] = (object) $it_q_id;
             }
//             echo "<br> Finished execution in ".(time()-$start1) ;
//             var_dump($result);
//             echo "<br>";
//             $start2 = time();
//             $result2 = $this->db->select('SUM(quantity_purchased) as quantity,item_id,')
//                 ->from('sales_items')
//                 ->where(['is_return'=>1,'returned_from'=>$sale_id])
//                 ->group_by('item_id')
//                 ->get()->result();
//             echo "<br> Second Finished execution in ".(time()-$start2) ;
//             var_dump($result2);
//             echo "<br>";
         }
        return $result;
    }
    public function return_items_on_sales($sale_id){
        return $this->get_returned_sales_items_summary($sale_id);
    }

    public function register_sales_batch($num = false){
        $this->load->model('employee');
        $branch_info = $this->Employee->get_branchinfo(2);
        $erd_url = ERD_BASE_URL.'/branches/sale/'.$branch_info->brid.'/batch/register';
//        echo $erd_url;
//        echo "here";

        $limit = $num ? 50 : 20;
        $sale_batch = $this->db->where(['sales.registered'=>'0'])
            ->from('sales')
            ->limit($limit)
            ->get()->result_array();
//        var_dump($sale_batch);
        if(count($sale_batch)> 0){
            $sale = [];
            foreach ($sale_batch as $batch){
                $employee_info = $this->employee->get_info($batch['employee_id']);
                $batch['employee_name'] = $employee_info->first_name.' '.$employee_info->last_name;
                $batch['items'] = $this->db->where(['sale_id'=>$batch['sale_id']])
                    ->select('sales_items.*,items.item_number')
                    ->from('sales_items')
                    ->join('items','sales_items.item_id = items.item_id')
                    ->get()->result_array();
	
                $batch['payments'] = $this->db->where(['sale_id'=>$batch['sale_id']])
                    ->from('sales_payments')
                    ->get()->result_array();
                $sale[] = $batch;
            }
//        $erd_url ='localhost:8060/api/transfer/push';
//		var_dump($erd_url);
//		exit();
            $sales_data['caller'] = "HaslekIsBae";
            $sales_data['sales'] = $sale;
            $this->load->library('External_calls');
            $erd_response = External_calls::makeRequest($erd_url,$sales_data,"POST");
            $erd_response = json_decode($erd_response,true);
            if(isset($erd_response['status']) && $erd_response['status'] == "00"){
                $data = $erd_response['data'];
                foreach ($data as $s_id=>$stat){
                    $this->db->update('sales',['registered'=>$stat],['sale_id'=>$s_id]);
                }
                
            }
			return ['message'=>$erd_response['message']];
        }
        return false;
    }
	/*
	 * Save the sale information after the sales is complete but before the final document is printed
	 * The sales_taxes variable needs to be initialized to an empty array before calling
	 */
	public function save(&$sale_status, &$items, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, &$sales_taxes, $sales_amount, $location_id, $sale_type = 0)
	{
		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if (count($items) == 0) {
			return -1;
		}
		//
		if ($sale_status == '1')	//suspend sales
		{
			if ($dinner_table > 2)	//not delivery or take away
			{
				$table_status = 1;
			} else {
				$table_status = 0;
			}
		}
		$employee_info = $this->CI->Employee->get_logged_in_employee_info();

		$auth_code = $this->sale_lib->get_auth_code();
		$authorizer_id = $this->Employee->get_info_by_authcode($auth_code)->person_id;

		$credit = 0;
		$new_wallet_balance = 0;
		$update_wallet = false;
		$wallet_type_amount = 0;
		$wallet_cover_all = false;
		if ($customer_id > 0) {

			$customer = $this->Customer->get_info($customer_id);
            $new_wallet_balance = $customer->wallet;
			//check if there is wallet payment selected
			if (isset($payments["Wallet"])) {
				$wallet_type_amount = $payments['Wallet']['payment_amount'];
				//necessary checkings already done when the wallet payment is selected

				//if customer is normal customer, use wallet amount first and only use credit when wallet has finished
				//if customer is staff, use credit first before wallet amount, since eligible credits are not repayable
				if ($customer->staff) {
					//get the remaining of his(staff) credit purchase for this month
					$remaining_credit = $customer->credit_limit - $this->get_thismonth_credit($customer_id);
					if ($remaining_credit >= $wallet_type_amount) {
						//remainig credit is enough to offset $wallet_type_amount
						$credit = $wallet_type_amount;
						//wallet is not touched, 
						$new_wallet_balance = $customer->wallet;
						$wallet_cover_all = true;
					} else {
						//he has some money in his wallet to complete this sale. if he doen't checks done during sales wouldn't have allowed $wallet_type_amount amount to be used for wallet payment
						$credit = $remaining_credit;
						$taken_from_wallet = $wallet_type_amount - $remaining_credit;
						$new_wallet_balance = $customer->wallet - $taken_from_wallet;
					}
				} else {
					$new_wallet_balance = $customer->wallet - $wallet_type_amount;
					//check if the customer has some money in his wallet and use it first
					if ($customer->wallet > 0) {
						//check if wallet amount can pay for all that's purchased
						if ($customer->wallet >= $wallet_type_amount) {
							//wallet can be used there is no credit
							$credit = 0; //this line is not necessary since credit is initialized to 0. just to make sure credit is nothing other than zero
                            $wallet_cover_all = true;
						} else {
							//current wallet balance cannot offset all the bill. so part of it is going to be on credit
							$credit = $wallet_type_amount - $customer->wallet;
						}
					} else {
						//no money in the wallet. all are credit. no need to check for credit limit exceeded since it has already been checked during sales
						$credit = $wallet_type_amount;
					}
				}
				$update_wallet = true;
			}
		}

		$sales_data = array(
			'sale_time'		 => date('Y-m-d H:i:s'),
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'	 => $employee_id,
			'comment'		 => $comment,
			'invoice_number' => $invoice_number,
			'quote_number' 	 => $quote_number,
			'dinner_table_id' => $dinner_table,
			'sales_amount'	 => $sales_amount,
			'location_id'	 => $location_id,
			'sale_status'	 => $sale_status,
			'auth_code'		 => $authorizer_id,
			'credit' => $credit,
            'sales_type' =>$sale_type
		);

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();
        $returned_sale = $this->CI->session->userdata('returned_sales');
		if($sale_type == 1){
            $return_data = ['sale_id_returned'=>$returned_sale,'sale_id_for_this_return'=>$sale_id,'amount'=>$sales_amount,
                'approved_by'=>$this->sale_lib->get_auth_code(true),'staff_id'=>$employee_id];
            $this->db->insert('sales_returns',$return_data);
        }
		$total_amount = 0;
		$total_amount_used = 0;
		$all_payments_details = "";
		$sale_payments = [];
		foreach ($payments as $payment_id => $payment) {

			$sales_payments_data = array(
				'sale_id'		 => $sale_id,
				'payment_type'	 => $payment['payment_type'],
				'payment_amount' => $payment['payment_amount']
			);
			$sale_payments[] = $sales_payments_data;
			$this->db->insert('sales_payments', $sales_payments_data);
			$all_payments_details .= $payment['payment_type'].': '.$payment['payment_amount'].',';
			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']);
		}
		$sales_data['payments'] = $sale_payments;
		$sales_data['sale_id']= $sale_id;
		$sales_data['employee_name'] = $employee_info->first_name.' '.$employee_info->last_name;
		if ($update_wallet) {
			//update customer wallet balance
            $other_payments_total = $total_amount - $wallet_type_amount;
			if(!$wallet_cover_all){
                $update_data = [
                    'balance' => $new_wallet_balance,
                    'credit'=>$other_payments_total,
                    'debit'=>$other_payments_total,
                    'sale_id' => $sale_id,
                    'customer_id' => $customer_id,
                    'employee_id' => $employee_id,
                    'date' => date("Y-m-d H:i:s"),
                    'amount' => $total_amount,
                    'narration'=> 'Purchase with: '.$all_payments_details
                ];
            }else{
                $update_data = array(
                    'balance' => $new_wallet_balance,
                    'sale_id' => $sale_id,
                    'debit'=>$wallet_type_amount,
                    'customer_id' => $customer_id,
                    'employee_id' => $employee_id,
                    'date' => date("Y-m-d H:i:s"),
                    'amount' => $wallet_type_amount
                );
            }
			$this->Customer->update_customer_wallet($customer_id, $update_data);
		}else{
            if($customer_id > 0){
                $other_payments_total = $total_amount - $wallet_type_amount;
                $update_data = [
                    'balance' => $new_wallet_balance,
                    'credit'=>$other_payments_total,
                    'debit'=>$other_payments_total,
                    'sale_id' => $sale_id,
                    'customer_id' => $customer_id,
                    'employee_id' => $employee_id,
                    'date' => date("Y-m-d H:i:s"),
                    'amount' => $total_amount,
                    'narration'=> 'Purchase with: '.$all_payments_details
                ];
                $this->Customer->update_customer_wallet($customer_id, $update_data);
            }
        }


		$sales_taxes = array();
		$sale_items = [];
		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info_by_id_or_number($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 30),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased' => $item['quantity'],
				'discount_percent'	=> $item['discount'],
				'item_cost_price'	=> $cur_item_info->cost_price,
				'pack'	=> $cur_item_info->pack,
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $item['item_location'],
				'qty_selected'		=> $item['qty_selected'],
				'reference'		=> $item['reference'],
				'print_option'		=> $item['print_option'],
				'apply_vat' => $item['apply_vat'],
				'vat' => round($item['vat'], 2),
                'is_return' => $sale_type,
                'returned_from'=> $returned_sale == null ? 0:$returned_sale
			);
			$this->db->insert('sales_items', $sales_items_data);

			//notify item sale_tracker here if it doesnt exists
			$this->saveitemtracker($cur_item_info->item_number);
			$sales_items_data['item_full_info'] =$cur_item_info;
//			return -1;
			// if ($item['reference'] == 0) {
				if (true) {

				$rem_quantity = 0;
				if ($cur_item_info->stock_type === '0' && $sale_status != '1') {
					// Update stock quantity if item type is not non-stock(stock_type =1 is for laboratory items) and sale is not being suspended
					//note that laboratory items are non-stock
					$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
					$rem_quantity = $item_quantity->quantity - $item['quantity']; //for retail
					if ($item['qty_selected'] == 'wholesale') {
						$rem_quantity = $item_quantity->quantity - ($item['quantity'] * $cur_item_info->pack);
					}
					$this->CI->Item_quantity->save(array(
						'quantity'		=> $rem_quantity,
						'item_id'		=> $item['item_id'],
						'location_id'	=> $item['item_location']
					), $item['item_id'], $item['item_location']);
				}
				$sales_items_data['remaining_quantity'] = $rem_quantity;
				$sales_items_data['item_number'] =$item['item_number'];
				$sale_items[] = $sales_items_data;
//				return -1;
				// if an items was deleted but later returned it's restored with this rule

				// if ($item['quantity'] < 0) {
				// 	$this->Item->undelete($item['item_id']);
				// }

				// Inventory Count Details
				$sale_remarks = 'POS ' . $sale_id;
				$sold_qty = $item['quantity'];

				//for wholesale
				if ($item['qty_selected'] == 'wholesale') {
					$sold_qty = $item['quantity'] * $cur_item_info->pack;
				}

				$sold_qty = -1 * $sold_qty; //the negative sign shows item is sold
				$inv_data = array(
					'trans_date'		=> date('Y-m-d H:i:s'),
					'trans_items'		=> $item['item_id'],
					'trans_user'		=> $employee_id,
					'trans_location'	=> $item['item_location'],
					'trans_comment'		=> $sale_remarks,
					'trans_inventory'	=> $sold_qty,
					'selling_price' => $cur_item_info->unit_price,
					'trans_remaining' => $rem_quantity
				);
				//Don't insert suspended sales in the inventory
				if ($sale_status != '1') {
					$this->Inventory->insert($inv_data);
				}
			}
		}

		$sales_data['item_number'] =$item['item_number'];
		$sales_data['items']= $sale_items;

		//update wallet



		$this->db->trans_complete();
//		return -1;

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
//		$
        $branch_info = $this->Employee->get_branchinfo($location_id);
        $erd_url = ERD_BASE_URL.'/branches/sale/'.$branch_info->brid.'/register';
//        $erd_url ='localhost:8060/api/transfer/push';
//		var_dump($erd_url);
//		exit();
        $sales_data['caller'] = "HaslekIsBae";
        $this->load->library('External_calls');
        $erd_response = External_calls::makeRequest($erd_url,$sales_data,"POST");
        $erd_response = json_decode($erd_response,true);
        if(isset($erd_response['status']) && $erd_response['status'] == "00"){
            $this->db->update('sales',['registered'=>1],['sale_id'=>$sale_id]);
        }
		$this->register_sales_batch(true);
		return $sale_id;
	}
	public function saveOnlineSale(&$sale_status, &$items, $employee_id, $sales_amount, $location_id, $transfer_id, $sale_id = FALSE)
	{
		$tax_decimals = $this->config->item('tax_decimals');

		if (count($items) == 0) {
			return -1;
		}

		if ($sale_status == '1')	//suspend sales
		{
			//if ($dinner_table > 2)	//not delivery or take away
			if (false) {
				$table_status = 1;
			} else {
				$table_status = 0;
			}
		}

		$auth_code = $this->sale_lib->get_auth_code();

		$sales_data = array(
			'sale_time'		 => date('Y-m-d H:i:s'),
			'customer_id'	 => null,
			'employee_id'	 => $employee_id,
			'comment'		 => '',
			'invoice_number' => null,
			'quote_number'   => null,
			'dinner_table_id' => 0,
			'sales_amount'	 => $sales_amount,
			'location_id'	 => $location_id,
			'sale_status'	 => $sale_status,
			'auth_code'		 => $auth_code,
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();
		$total_amount = 0;
		$total_amount_used = 0;




		//$customer = $this->Customer->get_info($customer_id);

		$sales_taxes = array();

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_info_by_id_or_number($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> '',
				'serialnumber'		=> '',
				'quantity_purchased' => $item['quantity'],
				'discount_percent'	=> 0.00,
				'item_cost_price'	=> $cur_item_info->cost_price,
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $location_id,
				'qty_selected'		=> 'retail',
				'print_option'		=> 0
			);

			$this->db->insert('sales_items', $sales_items_data);



			// Calculate taxes and save the tax information for the sale.  Return the result for printing


		}
		$dinner_table_data = array(
			'status' => 1
		);
		$this->db->where('transfer_id', $transfer_id);
		$this->db->update('item_transfer', $dinner_table_data);


		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $sale_id;
	}
	public function lab_accountsave(&$sale_status, &$items, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, &$sales_taxes, $invoice_id, $sale_id = FALSE, $location = 1)
	{
		$tax_decimals = $this->config->item('tax_decimals');

		if (count($items) == 0) {
			return -1;
		}

		if ($sale_status == '1')	//suspend sales
		{
			if ($dinner_table > 2)	//not delivery or take away
			{
				$table_status = 1;
			} else {
				$table_status = 0;
			}
		}

		$auth_code = $this->sale_lib->get_auth_code();

		$sales_data = array(
			'sale_time'		 => date('Y-m-d H:i:s'),
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'	 => $employee_id,
			'comment'		 => $comment,
			'invoice_number' => $invoice_number,
			'quote_number'   => $quote_number,
			'dinner_table_id' => $dinner_table,
			'sale_status'	 => $sale_status,
			'auth_code'		 => $auth_code,
			'location_id' => $location
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();
		$total_amount = 0;
		$total_amount_used = 0;

		$result_data = array(
			'sale_id'		 => $sale_id,
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'result_time'	 => date('Y-m-d H:i:s'),
			'invoice_id'	 => $invoice_id,

		);

		$this->db->insert('laboratory_results', $result_data);
		foreach ($payments as $payment_id => $payment) {
			if (substr($payment['payment_type'], 0, strlen($this->lang->line('sales_giftcard'))) == $this->lang->line('sales_giftcard')) {
				// We have a gift card and we have to deduct the used value from the total value of the card.
				$splitpayment = explode(':', $payment['payment_type']);
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value($splitpayment[1]);
				$this->Giftcard->update_giftcard_value($splitpayment[1], $cur_giftcard_value - $payment['payment_amount']);
			}

			if (substr($payment['payment_type'], 0, strlen($this->lang->line('sales_rewards'))) == $this->lang->line('sales_rewards')) {

				$cur_rewards_value = $this->Customer->get_info($customer_id)->points;
				$this->Customer->update_reward_points_value($customer_id, $cur_rewards_value - $payment['payment_amount']);
				$total_amount_used = floatval($total_amount_used) + floatval($payment['payment_amount']);
			}

			$sales_payments_data = array(
				'sale_id'		 => $sale_id,
				'payment_type'	 => $payment['payment_type'],
				'payment_amount' => $payment['payment_amount']
			);
			$this->db->insert('sales_payments', $sales_payments_data);
			$total_amount = floatval($total_amount) + floatval($payment['payment_amount']);
		}

		if (!empty($customer_id) && $this->config->item('customer_reward_enable') == TRUE) {
			$package_id = $this->Customer->get_info($customer_id)->package_id;

			if (!empty($package_id)) {
				$points_percent = $this->Customer_rewards->get_points_percent($package_id);
				$points = $this->Customer->get_info($customer_id)->points;
				$points = ($points == NULL ? 0 : $points);
				$points_percent = ($points_percent == NULL ? 0 : $points_percent);
				$total_amount_earned = ($total_amount * $points_percent / 100);
				$points = $points + $total_amount_earned;
				$this->Customer->update_reward_points_value($customer_id, $points);
				$rewards_data = array('sale_id' => $sale_id, 'earned' => $total_amount_earned, 'used' => $total_amount_used);
				$this->Rewards->save($rewards_data);
			}
		}

		$customer = $this->Customer->get_info($customer_id);

		$sales_taxes = array();

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_labinfo_by_id_or_number($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 30),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased' => $item['quantity'],
				'discount_percent'	=> $item['discount'],
				'item_cost_price'	=> $cur_item_info->test_amount,
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $item['item_location'],
				'print_option'		=> $item['print_option']
			);

			$this->db->insert('sales_items', $sales_items_data);



			// Inventory Count Details
			$sale_remarks = 'POS ' . $sale_id;
			$inv_data = array(
				'trans_date'		=> date('Y-m-d H:i:s'),
				'trans_items'		=> $item['item_id'],
				'trans_user'		=> $employee_id,
				'trans_location'	=> $item['item_location'],
				'trans_comment'		=> $sale_remarks,
				// 'selling_price' => $cur_item_info->unit_price,
				'selling_price' => $item['price'],
				'trans_inventory'	=> -$item['quantity']
			);
			$this->Inventory->insert($inv_data);

			// Calculate taxes and save the tax information for the sale.  Return the result for printing

			if ($customer_id == -1 || $customer->taxable) {
				if ($this->config->item('tax_included')) {
					$tax_type = Tax_lib::TAX_TYPE_VAT;
				} else {
					$tax_type = Tax_lib::TAX_TYPE_SALES;
				}
				$rounding_code = Rounding_code::HALF_UP; // half adjust
				$tax_group_sequence = 0;
				$item_total = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);
				$tax_basis = $item_total;
				$item_tax_amount = 0;

				foreach ($this->Item_taxes->get_info($item['item_id']) as $row) {

					$sales_items_taxes = array(
						'sale_id'			=> $sale_id,
						'item_id'			=> $item['item_id'],
						'line'				=> $item['line'],
						'name'				=> character_limiter($row['name'], 255),
						'percent'			=> $row['percent'],
						'tax_type'			=> $tax_type,
						'rounding_code'		=> $rounding_code,
						'cascade_tax'		=> 0,
						'cascade_sequence'	=> 0,
						'item_tax_amount'	=> 0
					);

					// This computes tax for each line item and adds it to the tax type total
					$tax_group = (float) $row['percent'] . '% ' . $row['name'];
					$tax_basis = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], TRUE);

					if ($this->config->item('tax_included')) {
						$tax_type = Tax_lib::TAX_TYPE_VAT;
						$item_tax_amount = $this->sale_lib->get_item_tax($item['quantity'], $item['price'], $item['discount'], $row['percent']);
					} elseif ($this->config->item('customer_sales_tax_support') == '0') {
						$tax_type = Tax_lib::TAX_TYPE_SALES;
						$item_tax_amount = $this->tax_lib->get_sales_tax_for_amount($tax_basis, $row['percent'], '0', $tax_decimals);
					} else {
						$tax_type = Tax_lib::TAX_TYPE_SALES;
					}

					$sales_items_taxes['item_tax_amount'] = $item_tax_amount;
					if ($item_tax_amount != 0) {
						$this->db->insert('sales_items_taxes', $sales_items_taxes);
						$this->tax_lib->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $row['percent'], $tax_basis, $item_tax_amount, $tax_group_sequence, $rounding_code, $sale_id,  $row['name'], '');
						$tax_group_sequence += 1;
					}
				}

				if ($this->config->item('customer_sales_tax_support') == '1') {
					$this->save_sales_item_tax($customer, $sale_id, $item, $item_total, $sales_taxes, $sequence, $cur_item_info->tax_category_id);
				}
			}
		}

		if ($customer_id == -1 || $customer->taxable) {
			$this->tax_lib->round_sales_taxes($sales_taxes);
			$this->save_sales_tax($sales_taxes);
		}

		$dinner_table_data = array(
			'status' => 1
		);

		$this->db->where('invoice_id', $invoice_id);
		$this->db->update('laboratory_invoice', $dinner_table_data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $sale_id;
	}

	public function lab_save_result(&$sale_status, &$items, $sale_id, $employee_id)
	{


		if (count($items) == 0) {
			return -1;
		}


		$this->db->trans_start();
		foreach ($items as $line => $item) {

			//$sale_id = ['sale_id'];
			$sales_items_data = array(
				'sale_id' => $sale_id,
				'item_id' => $item['item_id'],
				'test_name' => $item['test_name'],
				'test_unit' => $item['test_unit'],
				'category' => $item['category'],
				'test_comment' => $item['test_comment'],
				'line' => $item['line'],
				'reference' => $item['reference'],
				'extra_name' => $item['extra_name'],
				'o_name' => $item['o_name'],
				'h_name' => $item['h_name'],
				'print_option' => $item['print_option'],
			);
			//$sale_id = $this->db->insert_id();,
			$this->db->insert('laboratory_results_items', $sales_items_data);
		}
		$dinner_table_data = array(
			'status' => 3,
			'scientist' => $employee_id,
			'result_end' =>  date('Y-m-d H:i:s'),
		);
		$this->db->where('sale_id', $sale_id);
		$this->db->update('laboratory_results', $dinner_table_data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
		// Run these queries as a transaction, we want to make sure we do all or nothing




		// Inventory Count Details





		return TRUE;
	}
	public function receive_transfer_save(&$items, $transfer_id, $employee_id)
	{


		if (count($items) == 0) {
			return -1;
		}


		$this->db->trans_start();
		foreach ($items as $line => $item) {

			//$sale_id = ['sale_id'];
			$sales_items_data = array(
				'received_quantity' => $item['received_quantity'],

			);
			//$sale_id = $this->db->insert_id();
			//$this->db->insert('laboratory_results', $sales_items_data);
			$this->db->where('transfer_id', $transfer_id);
			$this->db->update('items_push', $sales_items_data);
			if ($item['reference'] == 0) {
				$send_location = $this->CI->Stock_location->get_location_name($item['item_location']);
				$rec_location = $this->CI->Stock_location->get_location_name($item['location']);

				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);
				$item_quantit = $this->Item_quantity->get_item_quantity($item['item_id'], $item['location']);
				$this->Item_quantity->save(array(
					'quantity'		=> $item_quantity->quantity - $item['quantity'],
					'item_id'		=> $item['item_id'],
					'location_id'	=> $item['item_location']
				), $item['item_id'], $item['item_location']);

				$this->Item_quantity->save(array(
					'quantity'		=> $item_quantit->quantity + $item['received_quantity'],
					'item_id'		=> $item['item_id'],
					'location_id'	=> $item['location']
				), $item['item_id'], $item['location']);
				$sendinv_remarks = 'PUSH ' . $rec_location;
				$recinv_remarks = 'PULL ' . $send_location;
				$sendinv_data = array(
					'trans_date'		=> date('Y-m-d H:i:s'),
					'trans_items'		=> $item['item_id'],
					'trans_user'		=> $employee_id,
					'trans_location'	=> $item['item_location'],
					'trans_comment'		=> $sendinv_remarks,
					'trans_inventory'	=> -$item['quantity']
				);
				$recinv_data = array(
					'trans_date'		=> date('Y-m-d H:i:s'),
					'trans_items'		=> $item['item_id'],
					'trans_user'		=> $employee_id,
					'trans_location'	=> $item['location'],
					'trans_comment'		=> $recinv_remarks,
					'trans_inventory'	=> $item['quantity']
				);
				$this->Inventory->insert($sendinv_data);
				$this->Inventory->insert($recinv_data);
			}

			if ($item['reference'] != 0) {

				$expiry_items_data = array(
					'expiry' => $item['expiry'],
					'item_id'		=> $item['item_id'],
					'location_id'	=> $item['item_location'],
					'batch_no'		=> $item['batch_no'],

				);
				//$sale_id = $this->db->insert_id();
				$this->db->insert('item_expiry', $expiry_items_data);
				//$this->db->where('transfer_id',$transfer_id);
				//$this->db->update('items_push', $expiry_items_data);

			}
		}
		$dinner_table_data = array(
			'status' => 1
		);
		$this->db->where('transfer_id', $transfer_id);
		$this->db->update('item_transfer', $dinner_table_data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
		// Run these queries as a transaction, we want to make sure we do all or nothing




		// Inventory Count Details





		return TRUE;
	}
	public function lab_saved_result(&$sale_status, &$items, $sale_id, $employee_id)
	{

		if (count($items) == 0) {
			return -1;
		}

		$this->db->trans_start();
		foreach ($items as $line => $item) {

			// print('<pre>');
			// print_r($sale_id);
			// print('</pre>');

			// die();

			//$sale_id = ['sale_id'];
			$sales_items_data = array(
				'sale_id' => $sale_id,
				'item_id' => $item['item_id'],
				'test_name' => $item['test_name'],
				'test_unit' => $item['test_unit'],
				'category' => $item['category'],
				'test_comment' => $item['test_comment'],
				'extra_name' => $item['extra_name'],
				'o_name' => $item['o_name'],
				'h_name' => $item['h_name'],
				'line' => $item['line'],
				'reference' => $item['reference'],
				'print_option' => $item['print_option'],
			);
			//$sale_id = $this->db->insert_id();
			if ($this->result_exists($item['line'], $sale_id, $item['test_name'])) {
				$this->db->where('line', $item['line']);
				$this->db->where('sale_id', $sale_id);
				$this->db->update('laboratory_results_saved', $sales_items_data);
			} else {
				$this->db->insert('laboratory_results_saved', $sales_items_data);
			}
			//}
		}
		$dinner_table_data = array(
			'status' => 2
		);
		$this->db->where('sale_id', $sale_id);
		$this->db->update('laboratory_results', $dinner_table_data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}
		// Run these queries as a transaction, we want to make sure we do all or nothing

		// Inventory Count Detail

		return TRUE;
	}
	public function result_exists($line, $sale_id, $test_name = 'none')
	{
		$this->db->from('laboratory_results_saved');
		$this->db->where('line', $line);
		$this->db->where('sale_id', $sale_id);
		if ($test_name != 'none') {
			$this->db->where('test_name', $test_name);
		}

		return ($this->db->get()->num_rows() == 1);
	}
	public function exists($sale_id)
	{
		//$this->db->cache_on();
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return ($this->db->get()->num_rows() == 1);
	}
	public function register_exists($employeeID)
	{
		// //$this->db->cache_on();
		$date_entered = date('Y-m-d');
		$status = 1;
		$this->db->from('open_register');
		$this->db->where('employeeID', $employeeID);
		$this->db->where('date_entered', $date_entered);
		$this->db->where('status', $status);

		return ($this->db->get()->num_rows() == 1);
	}


	public function lab_resultsave(&$sale_status, &$items, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payments, $dinner_table, &$sales_taxes, $invoice_id, $sale_id = FALSE)
	{

		if (count($items) == 0) {
			return -1;
		}

		if ($sale_status == '1')	//suspend sales
		{
			if ($dinner_table > 2)	//not delivery or take away
			{
				$table_status = 1;
			} else {
				$table_status = 0;
			}
		}

		$auth_code = $this->sale_lib->get_auth_code();

		$sales_data = array(
			'sale_time'		 => date('Y-m-d H:i:s'),
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'	 => $employee_id,
			'comment'		 => $comment,
			'invoice_number' => $invoice_number,
			'quote_number' => $quote_number,
			'dinner_table_id' => $dinner_table,
			'sale_status'	 => $sale_status,
			'auth_code'		 => $auth_code
		);

		// Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales', $sales_data);
		$sale_id = $this->db->insert_id();
		$total_amount = 0;
		$total_amount_used = 0;

		$result_data = array(
			'sale_id'		 => $sale_id,
			'customer_id'	 => $this->Customer->exists($customer_id) ? $customer_id : null,
			'result_time'	 => date('Y-m-d H:i:s'),
			'invoice_id'		 => $invoice_id,

		);

		$this->db->insert('laboratory_results', $result_data);



		$customer = $this->Customer->get_info($customer_id);

		$sales_taxes = array();

		foreach ($items as $line => $item) {
			$cur_item_info = $this->Item->get_labinfo_by_id_or_number($item['item_id']);

			$sales_items_data = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'description'		=> character_limiter($item['description'], 30),
				'serialnumber'		=> character_limiter($item['serialnumber'], 30),
				'quantity_purchased' => $item['quantity'],
				'discount_percent'	=> $item['discount'],
				'item_cost_price'	=> $cur_item_info->test_amount,
				'item_unit_price'	=> $item['price'],
				'item_location'		=> $item['item_location'],
				'print_option'		=> $item['print_option']
			);

			$this->db->insert('sales_items', $sales_items_data);



			// Inventory Count Details
			$sale_remarks = 'POS ' . $sale_id;


			// Calculate taxes and save the tax information for the sale.  Return the result for printing


		}


		$dinner_table_data = array(
			'status' => 1
		);

		$this->db->where('invoice_id', $invoice_id);
		$this->db->update('laboratory_invoice', $dinner_table_data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return -1;
		}

		return $sale_id;
	}


	/**
	 * Apply customer sales tax if the customer sales tax is enabledl
	 * The original tax is still supported if the user configures it,
	 * but it won't make sense unless it's used exclusively for the purpose
	 * of VAT tax which becomes a price component.  VAT taxes must still be reported
	 * as a separate tax entry on the invoice.
	 */
	public function save_sales_item_tax(&$customer, &$sale_id, &$item, $tax_basis, &$sales_taxes, &$sequence, $tax_category_id)
	{
		// if customer sales tax is enabled then update  sales_items_taxes with the
		if ($this->config->item('customer_sales_tax_support') == '1') {
			$register_mode = $this->config->item('default_register_mode');
			$tax_details = $this->tax_lib->apply_sales_tax($item, $customer->city, $customer->state, $customer->sales_tax_code, $register_mode, $sale_id, $sales_taxes);

			$sales_items_taxes = array(
				'sale_id'			=> $sale_id,
				'item_id'			=> $item['item_id'],
				'line'				=> $item['line'],
				'name'				=> $tax_details['tax_name'],
				'percent'			=> $tax_details['tax_rate'],
				'tax_type'			=> Tax_lib::TAX_TYPE_SALES,
				'rounding_code'		=> $tax_details['rounding_code'],
				'cascade_tax'		=> 0,
				'cascade_sequence'	=> 0,
				'item_tax_amount'	=> $tax_details['item_tax_amount']
			);

			$this->db->insert('sales_items_taxes', $sales_items_taxes);
		}
	}

	public function save_sales_tax(&$sales_taxes)
	{
		foreach ($sales_taxes as $line => $sales_tax) {
			$this->db->insert('sales_taxes', $sales_tax);
		}
	}

	public function delete_list($sale_ids, $employee_id, $update_inventory = TRUE)
	{
		$result = TRUE;

		foreach ($sale_ids as $sale_id) {
			$result &= $this->delete($sale_id, $employee_id, $update_inventory);
		}

		return $result;
	}

	public function delete($sale_id, $employee_id, $update_inventory = TRUE)
	{
		//Deleting is disabled for now until reason is resolved
		return false;



		// start a transaction to assure data integrity
		$this->db->trans_start();



		if ($update_inventory) {
			// defect, not all item deletions will be undone??
			// get array with all the items involved in the sale to update the inventory tracking
			$items = $this->get_sale_items($sale_id)->result_array();
			foreach ($items as $item) {
				$cur_item_info = $this->Item->get_info($item['item_id']);
				$item_quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location']);

				if ($cur_item_info->stock_type === '0') { //not laboratory items
					// first delete all payments
					$this->db->delete('sales_payments', array('sale_id' => $sale_id));
					// then delete all taxes on items
					$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
					// create query to update inventory tracking
					$inv_data = array(
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item['item_id'],
						'trans_user' => $employee_id,
						'trans_comment' => 'Deleting sales POS ' . $sale_id,
						'trans_location' => $item['item_location'],
						'trans_inventory' => $item['quantity_purchased'],
						'selling_price' => $cur_item_info->unit_price,
						'trans_remaining' => $item_quantity + $item['quantity_purchased']
					);
					// update inventory
					$this->Inventory->insert($inv_data);

					// update quantities
					$this->Item_quantity->change_quantity($item['item_id'], $item['item_location'], $item['quantity_purchased']);
				}
			}
		}

		// delete all items
		$this->db->delete('sales_items', array('sale_id' => $sale_id));
		// delete sale itself
		$this->db->delete('sales', array('sale_id' => $sale_id));

		// execute transaction
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function get_sale_items($sale_id)
	{
		$this->db->from('sales_items');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}

	/*
	 * Used by the invoice and receipt programs
	 */
	public function get_result_items_ordered($sale_id)
	{
		$this->db->select('
			sale_id,
			item_id,
			category,
			comment,
			extra_name,
			invoice,
			status');
		$this->db->from('laboratory_results_items as laboratory_results_items');
		$this->db->where('sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)


	}
	public function get_savedresult_items_ordered($sale_id)
	{
		$this->db->select('
			sale_id,
			item_id,

			category,
			test_unit,
			
			test_name,
			test_comment,
			extra_name,
			o_name,
			h_name,
			line,
			reference,
			print_option');
		$this->db->from('laboratory_results_saved as laboratory_results_saved');
		$this->db->where('sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}
	public function get_printresult_items_ordered($sale_id)
	{
		$this->db->select('
			sale_id,
			item_id,
			test_name,
			test_comment,
			line,
			reference,
			extra_name,
			o_name,
			h_name,
			print_option');
		$this->db->from('laboratory_results_items');
		$this->db->where('sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}
	public function get_pushedcart_reordered($transfer_id)
	{
		$this->db->select('
			transfer_id,
			item_id,
			pushed_quantity,
			received_quantity,
			request_from_branch_id,
			request_to_branch_id,
			reference,
			batch_no,
			expiry,
			unaccounted');
		$this->db->from('items_push as items_push');
		$this->db->where('transfer_id', $transfer_id);

		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}
	public function get_pulledcart_reordered($transfer_id)
	{
		$this->db->select('
			transfer_id,
			item_id,
			pushed_quantity,
			pulled_quantity,
			received_quantity,
			request_from_branch_id,
			request_to_branch_id,
			reference,
			batch_no,
			expiry,
			unaccounted');
		$this->db->from('items_push as items_push');
		$this->db->where('transfer_id', $transfer_id);

		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}
	public function get_remindercart_reordered()
	{
		$this->db->select('
			reminder_id,
			reminder_name,
			reminder_amount,
			reminder_value');
		$this->db->from('pill_reminder as pill_reminder');


		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}
	public function get_sale_items_ordered($sale_id)
	{
		$this->db->select('
			sale_id,
			sales_items.item_id,
			sales_items.description,
			serialnumber,
			line,
			quantity_purchased,
			item_cost_price,
			item_unit_price,
			discount_percent,
			item_location,
			print_option,
			items.name as name,
			category,
			item_type,
			stock_type,
			qty_selected,
			reference,
			sales_items.apply_vat AS apply_vat,
			vat');
		$this->db->from('sales_items as sales_items');
		$this->db->join('items as items', 'sales_items.item_id = items.item_id');
		$this->db->where('sale_id', $sale_id);

		// Entry sequence (this will render kits in the expected sequence)
		if ($this->config->item('line_sequence') == '0') {
			$this->db->order_by('line', 'asc');
		}
		// Group by Stock Type (nonstock first - type 1, stock next - type 0)
		elseif ($this->config->item('line_sequence') == '1') {
			$this->db->order_by('stock_type', 'desc');
			$this->db->order_by('sales_items.description', 'asc');
			$this->db->order_by('items.name', 'asc');
		}
		// Group by Item Category
		elseif ($this->config->item('line_sequence') == '2') {
			$this->db->order_by('category', 'asc');
			$this->db->order_by('sales_items.description', 'asc');
			$this->db->order_by('items.name', 'asc');
		}
		// Group by entry sequence in descending sequence (the Standard)
		else {
			$this->db->order_by('line', 'desc');
		}

		return $this->db->get();
	}
	public function get_onlinesale_items_ordered($transfer_id)
	{
		$this->db->select('
			transfer_id,
			items_push.item_id,
			items_push.pushed_quantity,
			line');
		$this->db->from('items_push as items_push');
		$this->db->where('transfer_id', $transfer_id);

		// Entry sequence (this will render kits in the expected sequence)

		return $this->db->get();
	}

	public function get_sale_payments($sale_id)
	{
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get();
	}

	public function get_payment_options($giftcard = TRUE, $reward_points = FALSE)
	{
		$payments = array();
		$payments['Cash'] = "Cash";
		$payments['POS'] = "POS";
		$payments['Check'] = "Check";
		$payments['Transfer'] = "Transfer";
		$payments['Wallet'] = "Wallet";


		// if ($this->config->item('payment_options_order') == 'debitcreditcash') {
		// 	$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
		// 	$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
		// 	$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
		// } else if ($this->config->item('payment_options_order') == 'debitcashcredit') {
		// 	$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
		// 	$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
		// 	$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
		// } else if ($this->config->item('payment_options_order') == 'cashcardtransfer') {
		// 	$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
		// 	$payments[$this->lang->line('sales_card')] = $this->lang->line('sales_card');
		// 	$payments[$this->lang->line('sales_ussd')] = $this->lang->line('sales_ussd');
		// 	$payments[$this->lang->line('sales_transfer')] = $this->lang->line('sales_transfer');
		// 	$payments[$this->lang->line('sales_wallet')] = $this->lang->line('sales_wallet');
		// } else { // default: if($this->config->item('payment_options_order') == 'cashdebitcredit')
		// 	$payments[$this->lang->line('sales_cash')] = $this->lang->line('sales_cash');
		// 	$payments[$this->lang->line('sales_debit')] = $this->lang->line('sales_debit');
		// 	$payments[$this->lang->line('sales_credit')] = $this->lang->line('sales_credit');
		// }

		// $payments[$this->lang->line('sales_check')] = $this->lang->line('sales_check');

		// if($giftcard) {
		// 	$payments[$this->lang->line('sales_giftcard')] = $this->lang->line('sales_giftcard');
		// }

		// if($reward_points) {
		// 	$payments[$this->lang->line('sales_rewards')] = $this->lang->line('sales_rewards');
		// }

		return $payments;
	}

	public function get_customer($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}

	public function get_employee($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->Employee->get_info($this->db->get()->row()->employee_id);
	}

	public function get_latest_sale_id()
	{

		//get current employee
		$employee_id = $this->CI->Employee->get_logged_in_employee_info()->person_id;
		
		$this->db->from('sales');
		$this->db->where('sale_status', 0);
		$this->db->where('employee_id', $employee_id);
		$this->db->order_by("sale_id", "desc");

		return $this->db->get()->row(0)->sale_id;
	}

	// TODO change to use new quote_number field
	public function check_quote_number_exists($quote_number, $sale_id = '')
	{
		$this->db->from('sales');
		$this->db->where('quote_number', $quote_number);
		if (!empty($sale_id)) {
			$this->db->where('sale_id !=', $sale_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	public function check_invoice_number_exists($invoice_number, $sale_id = '')
	{
		$this->db->from('sales');
		$this->db->where('invoice_number', $invoice_number);
		if (!empty($sale_id)) {
			$this->db->where('sale_id !=', $sale_id);
		}

		return ($this->db->get()->num_rows() == 1);
	}

	public function get_giftcard_value($giftcardNumber)
	{
		if (!$this->Giftcard->exists($this->Giftcard->get_giftcard_id($giftcardNumber))) {
			return 0;
		}

		$this->db->from('giftcards');
		$this->db->where('giftcard_number', $giftcardNumber);

		return $this->db->get()->row()->value;
	}

	//We create a temp table that allows us to do easy report/sales queries
	public function create_temp_table(array $inputs)
	{
		if (empty($inputs['sale_id'])) {
			if (empty($this->config->item('date_or_time_format'))) {
				$where = 'DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			} else {
				$where = 'sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		} else {
			$where = 'sales.sale_id = ' . $this->db->escape($inputs['sale_id']);
		}

		//$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)';
		$sale_price = '(sales_items.item_unit_price * sales_items.quantity_purchased )';
		$discount_items = 'sales_items.item_unit_price * sales_items.quantity_purchased * (sales_items.discount_percent / 100)';
		//the above line is not errorneous because, it uses both item_unit_price(which is wholesale price for wholesale sales and unit sale price for retail sales) for both wholesale and retail sales
		//while for quantity purchased, 1 for wholesales means total pack no for item

		//$sale_total = 'SUM(' . $sale_price . ')';
		//$discount = 'SUM(' . $discount_items . ')';

		//sales_items_taxes
		//$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$cost_price_retail = 'sales_items.item_cost_price * sales_items.quantity_purchased'; ///only for retails
		$cost_price_wholesale = 'sales_items.item_cost_price * sales_items.quantity_purchased * items.pack';

		$decimals = totals_decimals();



		// create a temporary table to contain all the payment types and amount
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payments_temp') .
				' (PRIMARY KEY(sale_id), INDEX(sale_id))
			(
				SELECT payments.sale_id AS sale_id,
					IFNULL(SUM(payments.payment_amount), 0) AS sale_payment_amount,
					GROUP_CONCAT(CONCAT(payments.payment_type, " ", payments.payment_amount) SEPARATOR ", ") AS payment_type
				FROM ' . $this->db->dbprefix('sales_payments') . ' AS payments
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = payments.sale_id
				WHERE sales.sale_status = 0 AND ' . $where . '
				GROUP BY payments.sale_id
			)'
		);

		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_temp') .
				' (INDEX(sale_date), INDEX(sale_time), INDEX(sale_id))
			(
				SELECT
					DATE(sales.sale_time) AS sale_date,
					sales.sale_time AS sale_time,
					sales.sale_id AS sale_id,
					sales.comment AS comment,
					sales.invoice_number AS invoice_number,
					sales.quote_number AS quote_number,
					sales.customer_id AS customer_id,
					CONCAT(customer_p.first_name, " ", customer_p.last_name) AS customer_name,
					customer_p.first_name AS customer_first_name,
					customer_p.last_name AS customer_last_name,
					customer_p.email AS customer_email,
					customer_p.comments AS customer_comments,
					customer.company_name AS customer_company_name,
					sales.employee_id AS employee_id,
					CONCAT(employee.first_name, " ", employee.last_name) AS employee_name,
					CONCAT(auth_employee.first_name, " ", auth_employee.last_name) AS authorizer,
					items.item_id AS item_id,
					items.name AS name,
					items.category AS category,
					items.supplier_id AS supplier_id,
					items.pack AS pack,
					sales_items.quantity_purchased AS quantity_purchased,
					sales_items.item_cost_price AS item_cost_price,
					sales_items.item_unit_price AS item_unit_price,
					sales_items.discount_percent AS discount_percent,
					sales_items.apply_vat AS vat_applied,
					sales_items.vat AS vat,
					sales_items.line AS line,
					sales_items.qty_selected AS qty_selected,
					items.item_number AS serialnumber,
					sales_items.item_location AS item_location,
					sales_items.description AS description,
					payments.payment_type AS payment_type,
					payments.sale_payment_amount AS sale_payment_amount,


					' . "
					IFNULL((ROUND($sale_price, $decimals) - ROUND($discount_items, $decimals)),0)AS total,
					
					
					IFNULL(ROUND($cost_price_retail, $decimals), 0) AS cost_retail,
					IFNULL(ROUND($cost_price_wholesale, $decimals), 0) AS cost_wholesale,
					IFNULL((ROUND($sale_price, $decimals) - ROUND($cost_price_retail,$decimals) - ROUND($discount_items, $decimals)),0) AS profit_retail,
					IFNULL((ROUND($sale_price, $decimals) - ROUND($cost_price_wholesale,$decimals) - ROUND($discount_items, $decimals)),0) AS profit_wholesale,
					IFNULL(ROUND($discount_items, $decimals), 0) AS discount

					
					" . '
				FROM ' . $this->db->dbprefix('sales_items') . ' AS sales_items
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales_items.sale_id = sales.sale_id
				INNER JOIN ' . $this->db->dbprefix('items') . ' AS items
					ON sales_items.item_id = items.item_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_payments_temp') . ' AS payments
					ON sales_items.sale_id = payments.sale_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('suppliers') . ' AS supplier
					ON items.supplier_id = supplier.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('people') . ' AS customer_p
					ON sales.customer_id = customer_p.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('customers') . ' AS customer
					ON sales.customer_id = customer.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('people') . ' AS employee
					ON sales.employee_id = employee.person_id
				LEFT OUTER JOIN ' . $this->db->dbprefix('people') . ' AS auth_employee
					ON sales.auth_code = auth_employee.person_id
				
				WHERE sales.sale_status = 0 AND ' . $where . '
				
			)'
		);

		// drop the temporary table to contain memory consumption as it's no longer required
		$this->db->query('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->dbprefix('sales_payments_temp'));
	}

	/*
	 * Retrieves all sales that are in a suspended state
	 */
	// public function get_all_suspended($customer_id = NULL)
	// {
	// 	if ($customer_id == -1) {
	// 		$query = $this->db->query('select '
	// 			. 'sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment, sale_amount from '
	// 			. $this->db->dbprefix('sales') . ' where sale_status = 1 '
	// 			. ' union '
	// 			. ' select sale_id, sale_id*-1 as suspended_sale_id, 2 as sale_status, sale_time, dinner_table_id, customer_id, comment from '
	// 			. $this->db->dbprefix('sales_suspended'));
	// 	} else {
	// 		$query = $this->db->query('select sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment, sale_amount from '
	// 			. $this->db->dbprefix('sales') . ' where sale_status = 1 and customer_id = ' . $customer_id
	// 			. ' union select sale_id, sale_id*-1 as suspended_sale_id, 2 as sale_status, sale_time, dinner_table_id, customer_id, comment from '
	// 			. $this->db->dbprefix('sales_suspended') . ' where customer_id = ' . $customer_id);
	// 	}

	// 	return $query->result_array();
	// }

	public function get_all_suspended($customer_id = NULL)
	{
		if ($customer_id == -1) {
			// $query = $this->db->query('select '
			// 	. 'sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment, SUM(sale_amount) as price from '
			// 	. $this->db->dbprefix('sales') . ' where sale_status = 1 ');

			$query = $this->db->query('select '
			. 'sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment, SUM(item_unit_price * quantity_purchased) as price from '
			. $this->db->dbprefix('sales') . ' NATURAL JOIN ' . $this->db->dbprefix('sales_items')  . ' where sale_status = 1 GROUP BY sale_id ORDER BY sale_time DESC');
		} else {
			$query = $this->db->query('select sale_id, sale_id as suspended_sale_id, sale_status, sale_time, dinner_table_id, customer_id, comment, sales_amount from '
				. $this->db->dbprefix('sales') . ' where sale_status = 1  and customer_id = ' . $customer_id . ' sORDER BY sale_time');
		}

		// $this->db->group_by('sale_id');

		return $query->result_array();
	}

	/*
	 * get the dinner table for the selected sale
	 */
	public function get_dinner_table($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row()->dinner_table_id;
	}

	/*
	* Gets total of suspended invoices rows
	*/
	public function get_suspended_invoice_count()
	{
		$this->db->from('sales');
		$this->db->where('invoice_number IS NOT NULL');
		$this->db->where('sale_status', '1');

		return $this->db->count_all_results();
	}

	/*
	 * This will remove a selected sale from the sales table.
	 * This function should only be called for suspended sales that are being restored to the current cart
	 */
	public function delete_suspended_sale($sale_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$dinner_table = $this->get_dinner_table($sale_id);
		$dinner_table_data = array(
			'status' => 0
		);

		$this->db->where('dinner_table_id', $dinner_table);
		$this->db->update('dinner_tables', $dinner_table_data);

		$this->db->delete('sales_payments', array('sale_id' => $sale_id));
		$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
		$this->db->delete('sales_items', array('sale_id' => $sale_id));
		$this->db->delete('sales_taxes', array('sale_id' => $sale_id));
		$this->db->delete('sales', array('sale_id' => $sale_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function get_suspended_sale_info($sale_id)
	{
		$this->db->from('sales');
		$this->db->where('sale_id', $sale_id);
		$this->db->join('people', 'people.person_id = sales_suspended.customer_id', 'LEFT');

		return $this->db->get();
	}

	public function create_temp_tabl(array $inputs)
	{
		if (empty($inputs['expiry_id'])) {
			if (empty($this->config->item('date_or_time_format'))) {
				$where = 'DATE(warning_date) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			} else {
				$where = 'warning_date BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		} else {
			$where = 'expiry_table.expiry_id = ' . $this->db->escape($inputs['expiry_id']);
		}

		//sales_items_taxes
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_payment_temp') .
				' (PRIMARY KEY(expiry_id), INDEX(expiry_id))
			(
				SELECT payments.expiry_id AS expiry_id,
					MAX(payments.product_description) AS product_description,
					MAX(payments.expiry_date) AS expiry_date
				FROM ' . $this->db->dbprefix('expiry_table') . ' AS payments
				
			)'
		);
	}

	public function crate_temp_table(array $inputs)
	{
		if (empty($inputs['item_id'])) {
			if (empty($this->config->item('date_or_time_format'))) {
				$where = 'WHERE DATE(expiry) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
			} else {
				$where = 'WHERE expiry BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
			}
		} else {
			$where = 'WHERE items.item_id = ' . $this->db->escape($inputs['receiving_id']);
		}

		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('receivings_items_temp') .
				' (INDEX(expiry), INDEX(item_id))
			(
				SELECT 
					MAX(expiry) AS expiry,
					MAX(name) AS name,
					items.item_id
					
				FROM ' . $this->db->dbprefix('items') . ' AS items
				
				' . "
				$where
				" . '
				GROUP BY items.item_id
			)'
		);
	}

	public function get_sales_history(array $inputs)
	{
		$this->db->select('sales.sale_time as timestamp, sales.sale_id, sales.sale_status, sales.sales_amount, employees.username as employee, COUNT(item_id) as total_items');
		$this->db->from('sales');
		$this->db->join('employees', 'sales.employee_id = employees.person_id', 'left');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id', 'left');
		if (!empty($inputs['search'])) {
			$this->db->group_start();
			$this->db->where('sales.sale_id', $inputs['search']);
			// $this->db->or_like('sales.sale_amount', $inputs['search']);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(sale_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		} else {
			$this->db->where('sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}

		$this->db->group_by($inputs['sort'], $inputs['order']);
		if ($inputs['limit'] > 0) {
			$this->db->limit($inputs['limit'], $inputs['offset']);
		}
		return $this->db->get();
	}

	public function get_sales_history_count(array $inputs)
	{
		$this->db->select('sales.sale_time as timestamp, sales.sale_id, sales.sale_status, sales.sales_amount, employees.username as employee, COUNT(item_id) as total_items');
		$this->db->from('sales');
		$this->db->join('employees', 'sales.employee_id = employees.person_id', 'left');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id', 'left');
		if (!empty($inputs['search'])) {
			$this->db->group_start();
			$this->db->where('sales.sale_id', $inputs['search']);
			// $this->db->or_like('sales.sale_amount', $inputs['search']);
			$this->db->group_end();
		}
		if (empty($this->config->item('date_or_time_format'))) {
			$this->db->where('DATE_FORMAT(sale_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		} else {
			$this->db->where('sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
		$this->db->group_by($inputs['sort'], $inputs['order']);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function get_sales_history_by_receipt_number($id)
	{
		$this->db->select('sales.*, people.first_name, people.last_name, employees.username');
		$this->db->from('sales');
		$this->db->join('employees', 'sales.employee_id = employees.person_id', 'left');
		$this->db->join('people', 'people.person_id = employees.person_id', 'left');
		$this->db->where('sale_id', $id);
		return $this->db->limit(1)->get();
	}

	public function get_sales_history_items_by_receipt_number($id)
	{
		$this->db->select('sales_items.*, items.name as name, items.apply_vat as vatable, stock_locations.location_name as location');
		$this->db->from('sales_items');
		$this->db->join('items', 'items.item_id = sales_items.item_id');
		$this->db->join('stock_locations', 'stock_locations.location_id = sales_items.item_location');
		$this->db->where('sale_id', $id);
		return $this->db->get();
	}

	public function get_sales_history_payment_methods($id)
	{
		$this->db->select('sales_payments.*');
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $id);
		return $this->db->get();
	}



	public function get_thismonth_credit($customer_id)
	{

		$month = date("m"); //month with leading zero
		$no_of_days = date("t"); //28,29,30, or 31

		$start_date = date("Y") . "-" . $month . "-01" . " 00:00:00"; //first day of this month
		$end_date = date("Y") . "-" . $month . "-" . $no_of_days . " 00:00:00"; //first day of this month

		$this->db->select("SUM(credit) as credit");
		$this->db->from("sales");
		$this->db->where("customer_id", $customer_id);
		$this->db->where("sale_time >=", $start_date);
		$this->db->where("sale_time <=", $end_date);
		$credit = $this->db->get()->result()[0]->credit;

		if (!$credit) {
			return 0.00;
		}
		return $credit;
	}

	/** Get Latest sales filtered by employee, customer, date range */
	public function get_latest_sales($type,$start_date = null, $end_date = null, $employee_id = "all", $customer_id = "all") {

		if($start_date == null) {
			$start_date = date("Y") . "-" . date("m") . "-01" . " 00:00:00";
		}else {
			$start_date = date("Y-m-d",strtotime($start_date)) . " 00:00:00";
		}
		if($end_date == null) {
			$end_date = date("Y") . "-" . date("m") . "-" . date("t") . " 00:00:00";
		}else {
			$end_date = date("Y-m-d",strtotime($end_date)) . " 23:59:59";
		}

		$this->db->select('Round(sales_items.quantity_purchased) as quantity,
		sales.sales_amount as amount,
		sales.sale_time as date,
		CONCAT("POS",sales.sale_id) as invoice,
		item.name as item_name,
		item.item_id as item_id,
		employee.person_id as employee_id,
		CONCAT(employee.first_name," ",employee.last_name) as employee_name,
		customer.person_id as customer_id,
		CONCAT(customer.first_name," ",customer.last_name) as customer_name');

		$this->db->from("sales_items as sales_items");

		$this->db->join('sales as sales', 'sales_items.sale_id = sales.sale_id','left');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id','left');
		$this->db->join('items as item', 'sales_items.item_id = item.item_id','left');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id','left');
		


		if($employee_id != "all") $this->db->where("employee_id",$employee_id);
		if($customer_id != "all") $this->db->where("customer_id",$customer_id);
		$this->db->where("sale_time >= ",$start_date);
		$this->db->where("sale_time <= ",$end_date);

		if($type == "returns") $this->db->where("quantity_purchased < 0");
		else $this->db->where("quantity_purchased > 0");

		$data = $this->db->get();


		return $data->result();
	
		 

		
	}
}
