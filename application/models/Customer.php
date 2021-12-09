<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Customer class
 *
 * @link    github.com/jekkos/opensourcepos
 * @since   1.0
 * @author  N/A
 */

class Customer extends Person
{
	/*
	Determines if a given person_id is a customer
	*/
	public function exists($person_id)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id', $person_id);

		return ($this->db->get()->num_rows() == 1);
	}

	public function company_exists($company_id)
	{
		$this->db->from('companies');
		$this->db->where('companies.company_id', $company_id);

		return ($this->db->get()->num_rows() == 1);
	}



	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('customers');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Returns all the customers
	*/
	public function get_all($rows = 0, $limit_from = 0)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	//get all companies
	public function get_all_companies($limit_from = 0, $rows = 0)
	{
		$this->db->from('companies');
		$this->db->where('deleted', 0);
		$this->db->order_by('company_name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Gets information about a particular customer
	*/
	public function get_info($customer_id)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id', $customer_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $customer_id is NOT a customer
			$person_obj = array(); //parent::get_info(-1);

			//Get all the fields from customer table
			//append those fields to base parent object, we we have a complete empty object
			foreach ($this->db->list_fields('customers') as $field) {
				$person_obj[$field] = '';
			}

			return (object) $person_obj;
		}
	}

	/*
	Gets information about a particular company
	*/
	public function get_company_info($company_id)
	{
		$this->db->from('companies');
		// $this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('companies.company_id', $company_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $company_id is NOT a company
			$company_obj = array(); //parent::get_info(-1);

			//Get all the fields from company table
			//append those fields to base parent object, we we have a complete empty object
			foreach ($this->db->list_fields('companies') as $field) {
				$company_obj[$field] = '';
			}

			return (object) $company_obj;
		}
	}

	public function get_brought_forward($customer_id,$start){
	    $bf = 0;
	    $res = $this->db->from('wallet')
            ->where(['customer_id'=>$customer_id,])
            ->where('date <',$start)
            ->order_by("date", 'desc')
            ->limit(1)
            ->get();
	    if($res->num_rows() > 0){
	        $wallet_info = $res->row();
	        $bf = $wallet_info->balance;
        }
	    return $bf;
    }
	public function get_wallet_info($customer_id, $start, $end)
	{
//		$this->db->select('wallet.*,sales.*, employees.first_name as firstname, employees.last_name as lastname');
        $this->db->select('wallet.*, employees.first_name as firstname, employees.last_name as lastname');
		$this->db->from('wallet as wallet');
//		$this->db->join('sales as sales', 'sales.sale_id = wallet.sale_id', "left");
		$this->db->join('people as employees', 'employees.person_id = wallet.employee_id');
		// $this->db->where('wallet.customer_id', $customer_id);
		// $this->db->where("wallet.date between {ts '$start'} AND {ts '$end'}");
		$this->db->where('wallet.date >=', $start);
		$this->db->where('wallet.date <=', $end);
		$this->db->order_by("wallet.id", 'desc');
		$query = $this->db->get();

		return $query->result();
	}

	public function get_lab_invoice_info($invoice_id)
	{
		$this->db->from('laboratory_invoice');
		$this->db->where('invoice_id', $invoice_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $customer_id is NOT a customer

			//$person_obj = parent::get_lab_invoice_info(-1);

			//Get all the fields from customer table
			//append those fields to base parent object, we we have a complete empty object
			$person_obj = array();
			foreach ($this->db->list_fields('laboratory_invoice') as $field) {
				//$person_obj->$field = '';
				$person_obj[$field] = "";
			}

			return (object) $person_obj;
		}
	}


	/*
	Gets stats about a particular customer
	*/
	public function get_stats($customer_id)
	{
		// create a temporary table to contain all the sum and average of items
		$this->db->query(
			'CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_temp') .
				' (INDEX(sale_id))
			(
				SELECT
					sales.sale_id AS sale_id,
					AVG(sales_items.discount_percent) AS avg_discount,
					SUM(sales_items.quantity_purchased) AS quantity
				FROM ' . $this->db->dbprefix('sales') . ' AS sales
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales.sale_id
				WHERE sales.customer_id = ' . $this->db->escape($customer_id) . '
				GROUP BY sale_id
			)'
		);

		$totals_decimals = totals_decimals();
		$quantity_decimals = quantity_decimals();

		$this->db->select('
						SUM(sales_payments.payment_amount) AS total,
						MIN(sales_payments.payment_amount) AS min,
						MAX(sales_payments.payment_amount) AS max,
						AVG(sales_payments.payment_amount) AS average,
						' . "
						ROUND(AVG(sales_items_temp.avg_discount), $totals_decimals) AS avg_discount,
						ROUND(SUM(sales_items_temp.quantity), $quantity_decimals) AS quantity
						");
		$this->db->from('sales');
		$this->db->join('sales_payments AS sales_payments', 'sales.sale_id = sales_payments.sale_id');
		$this->db->join('sales_items_temp AS sales_items_temp', 'sales.sale_id = sales_items_temp.sale_id');
		$this->db->where('sales.customer_id', $customer_id);
		$this->db->where('sales.sale_status', 0);
		$this->db->group_by('sales.customer_id');

		$stat = $this->db->get()->row();

		// drop the temporary table to contain memory consumption as it's no longer required
		$this->db->query('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->dbprefix('sales_items_temp'));

		return $stat;
	}

	/*
	Gets information about multiple customers
	*/
	public function get_multiple_info($customer_ids)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where_in('customers.person_id', $customer_ids);
		$this->db->order_by('last_name', 'asc');

		return $this->db->get();
	}

	/*
	Gets information about multiple companies
	*/
	public function get_multiple_company_info($company_ids)
	{
		$this->db->from('companies');
		$this->db->where_in('companies.company_id', $company_ids);
		$this->db->order_by('company_name', 'asc');

		return $this->db->get();
	}

	/*
	Checks if customer phone number exists
	*/
	public function check_phone_exists($phone, $customer_id = '')
	{
		// if the phone is empty return like it is not existing
		if (empty($phone)) {
			return FALSE;
		}

		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('people.phone_number', $phone);
		$this->db->where('customers.deleted', 0);

		if (!empty($customer_id)) {
			$this->db->where('customers.person_id !=', $customer_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Checks if customer email exists
	*/
	public function check_email_exists($email, $customer_id = '')
	{
		// if the email is empty return like it is not existing
		if (empty($email)) {
			return FALSE;
		}

		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('people.email', $email);
		$this->db->where('customers.deleted', 0);

		if (!empty($customer_id)) {
			$this->db->where('customers.person_id !=', $customer_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Checks if company phone number exists
	*/
	public function check_company_phone_exists($phone, $company_id = '')
	{
		// if the phone is empty return like it is not existing
		if (empty($phone)) {
			return FALSE;
		}

		$this->db->from('companies');
		$this->db->where('companies.contact_phone', $phone);
		$this->db->where('companies.deleted', 0);

		if (!empty($company_id)) {
			$this->db->where('companies.company_id !=', $company_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Checks if company email exists
	*/
	public function check_company_email_exists($email, $company_id = '')
	{
		// if the email is empty return like it is not existing
		if (empty($email)) {
			return FALSE;
		}

		$this->db->from('companies');
		$this->db->where('companies.contact_email', $email);
		$this->db->where('companies.deleted', 0);

		if (!empty($company_id)) {
			$this->db->where('companies.company_id !=', $company_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Checks if company cac number exists
	*/
	public function check_company_cac_exists($cac, $company_id = '')
	{
		// if the cac is empty return like it is not existing
		if (empty($cac)) {
			return FALSE;
		}

		$this->db->from('companies');
		$this->db->where('companies.cac', $cac);
		$this->db->where('companies.deleted', 0);

		if (!empty($company_id)) {
			$this->db->where('companies.company_id !=', $company_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	/*
	Checks if company tin exists
	*/
	public function check_company_tin_exists($tin, $company_id = '')
	{
		// if the tin is empty return like it is not existing
		if (empty($tin)) {
			return FALSE;
		}

		$this->db->from('companies');
		$this->db->where('companies.tin', $tin);
		$this->db->where('companies.deleted', 0);

		if (!empty($company_id)) {
			$this->db->where('companies.company_id !=', $company_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}


	/*
	Inserts or updates a customer
	*/
	public function save_customer(&$person_data, &$customer_data, $customer_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if (parent::save($person_data, $customer_id)) {
			if (!$customer_id || !$this->exists($customer_id)) {
				$customer_data['person_id'] = $person_data['person_id'];
				$success = $this->db->insert('customers', $customer_data);
			} else {
				$this->db->where('person_id', $customer_id);
				$success = $this->db->update('customers', $customer_data);
			}
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Inserts or updates a company
	*/
	public function save_company(&$company_data, $company_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if (!$company_id || !$this->company_exists($company_id)) {
			// $customer_data['person_id'] = $person_data['person_id'];
			$success = $this->db->insert('companies', $company_data);
		} else {
			$this->db->where('company_id', $company_id);
			$success = $this->db->update('companies', $company_data);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function update_customer_wallet($customer_id, $wallet_data)
	{
		//update wallet data
		$this->insert_wallet($wallet_data);


		//update customer table
		$customer_data = array('wallet' => $wallet_data['balance']);
		$this->db->where('person_id', $customer_id);
		return $this->db->update('customers', $customer_data);
	}

	public function update_company_wallet($company_id, $wallet_data)
	{
		//update wallet data
		$this->insert_company_wallet($wallet_data);

		//update company table
		$company_data = array('wallet' => $wallet_data['balance']);
		$this->db->where('company_id', $company_id);
		return $this->db->update('companies', $company_data);
	}

	public function insert_wallet($wallet_data)
	{
		$this->db->insert('wallet', $wallet_data);
	}

	public function insert_company_wallet($wallet_data)
	{
		$this->db->insert('company_wallet', $wallet_data);
	}

	/*
	Updates reward points value
	*/
	public function update_reward_points_value($customer_id, $value)
	{
		$this->db->where('person_id', $customer_id);
		$this->db->update('customers', array('points' => $value));
	}


	/*
	Deletes one customer
	*/
	public function delete($customer_id)
	{
		$this->db->where('person_id', $customer_id);

		return $this->db->update('customers', array('deleted' => 1));
	}

	/*
	Deletes a list of customers
	*/
	public function delete_list($customer_ids)
	{
		$this->db->where_in('person_id', $customer_ids);

		return $this->db->update('customers', array('deleted' => 1));
	}

	/*
	Deletes a list of companies
	*/
	public function delete_companies_list($company_ids)
	{
		$this->db->where_in('company_id', $company_ids);

		return $this->db->update('companies', array('deleted' => 1));
	}

	/*
	Get search suggestions to find customers
	*/
	public function get_search_suggestions($search, $unique = TRUE, $limt = 99999)
	{
		$suggestions = array();
		//$this->db->cache_on();

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('first_name', $search, 'after');
		$this->db->or_like('last_name', $search, 'after');
		$this->db->or_like('phone_number', $search, 'after');
		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search, 'after');
		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->first_name . ' ' . $row->last_name);
		}

		if (!$unique) {
			$this->db->from('customers');
			$this->db->join('people', 'customers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('email', $search, 'after');
			$this->db->order_by('email', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->email);
			}

			$this->db->from('customers');
			$this->db->join('people', 'customers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('phone_number', $search, 'after');
			$this->db->order_by('phone_number', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->phone_number);
			}
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/*
	Get search suggestions to find companies
	*/
	public function get_companies_search_suggestions($search, $unique = TRUE, $limt = 99999)
	{
		$suggestions = array();
		//$this->db->cache_on();

		$this->db->from('companies');
		// $this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('company_name', $search, 'after');
		$this->db->or_like('cac', $search, 'after');
		$this->db->or_like('tin', $search, 'after');
		$this->db->or_like('contact_phone', $search, 'after');
		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by('company_name', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->company_id, 'label' => $row->company_name);
		}

		if (!$unique) {
			$this->db->from('companies');
			// $this->db->join('people', 'customers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('contact_email', $search, 'after');
			$this->db->order_by('contact_email', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('value' => $row->company_id, 'label' => $row->contact_email);
			}

			$this->db->from('companies');
			// $this->db->join('people', 'customers.person_id = people.person_id');
			$this->db->where('deleted', 0);
			$this->db->like('contact_phone', $search, 'after');
			$this->db->order_by('contact_phone', 'asc');
			foreach ($this->db->get()->result() as $row) {
				$suggestions[] = array('value' => $row->company_id, 'label' => $row->contact_phone);
			}
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/*
		custom function to load patients search page
	*/

	public function search_page(array $inputs)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		if (!empty($inputs['search'])) {
			$this->db->group_start();
			$this->db->like('people.phone_number', $inputs['search']);
			$this->db->or_like('people.last_name', $inputs['search']);
			$this->db->or_like('people.first_name', $inputs['search']);
			$this->db->or_like('people.email', $inputs['search']);
			$this->db->group_end();
		}
		$this->db->where('deleted', 0);
		//$this->db->group_by($inputs['sort'], $inputs['order']);
		if ($inputs['limit'] > 0) {
			$this->db->limit($inputs['limit'], $inputs['offset']);
		}
		return $this->db->get();
	}

	public function search_page_count(array $inputs)
	{
		//$this->db->cache_on();
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		if (!empty($inputs['search'])) {
			$this->db->group_start();
			$this->db->like('people.phone_number', $inputs['search']);
			$this->db->or_like('people.last_name', $inputs['search']);
			$this->db->or_like('people.first_name', $inputs['search']);
			$this->db->or_like('people.email', $inputs['search']);
			$this->db->group_end();
		}
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function load_patient_data($id)
	{
		$this->db->from('customers');
		$this->db->select('people.*');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('customers.person_id', $id);
		return $this->db->limit(1)->get();
	}

	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('first_name', $search);
		$this->db->or_like('last_name', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('phone_number', $search);

		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);

		return $this->db->get()->num_rows();
	}

	/*
	Performs a search on customers
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'last_name', $order = 'asc')
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('first_name', $search);
		$this->db->or_like('last_name', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('phone_number', $search);

		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);
		// $this->db->order_by($sort, $order);
		$this->db->order_by('last_name', 'asc');

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Performs a search on companies
	*/
	public function search_companies($search, $rows = 0, $limit_from = 0, $sort = 'company_name', $order = 'asc')
	{
		$this->db->from('companies');
		// $this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('company_name', $search);
		$this->db->or_like('cac', $search);
		$this->db->or_like('tin', $search);
		$this->db->or_like('contact_phone', $search);
		$this->db->or_like('contact_email', $search);

		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by($sort, $order);
		// $this->db->order_by('last_name', 'asc');

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}
}
