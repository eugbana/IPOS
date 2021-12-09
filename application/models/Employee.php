<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Employee extends Person
{
	/*
	Determines if a given person_id is an employee
	*/
	public function exists($person_id)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id', $person_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$this->db->from('employees');
		$this->db->where('deleted', 0);

		return $this->db->count_all_results();
	}

	/*
	Returns all the employees
	*/
	public function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('employees');
		$this->db->where('deleted', 0);
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->order_by('last_name', 'asc');
		$this->db->limit($limit);
		$this->db->offset($offset);

		return $this->db->get();
	}
	/*
	Gets information about a particular employee
	*/
	public function get_info($employee_id)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id', $employee_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $employee_id is NOT an employee
			//$person_obj = parent::get_info(-1);

			//Get all the fields from employee table
			//append those fields to base parent object, we we have a complete empty object
			$person_obj = array();
			foreach ($this->db->list_fields('employees') as $field) {
				//$person_obj->$field = '';
				$person_obj[$field] = "";
			}

			return (object) $person_obj;
		}
	}
	public function get_info_by_authcode($auth_code)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.auth_code', $auth_code);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $employee_id is NOT an employee
			//$person_obj = parent::get_info(-1);

			//Get all the fields from employee table
			//append those fields to base parent object, we we have a complete empty object
			$person_obj = array();
			foreach ($this->db->list_fields('employees') as $field) {
				//$person_obj->$field = '';
				$person_obj[$field] = "";
			}

			return (object) $person_obj;
		}
	}

	public function get_branchinfo($location_id)
	{
		$this->db->from('stock_locations');
		$this->db->where('location_id', $location_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $employee_id is NOT an employee
			$person_obj = parent::get_info(-1);

			//Get all the fields from employee table
			//append those fields to base parent object, we we have a complete empty object
			foreach ($this->db->list_fields('employees') as $field) {
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets information about multiple employees
	*/
	public function get_multiple_info($employee_ids)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where_in('employees.person_id', $employee_ids);
		$this->db->order_by('last_name', 'asc');

		return $this->db->get();
	}

	/*
	Inserts or updates an employee
	*/
	public function save_employee(&$person_data, &$employee_data, $employee_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if (parent::save($person_data, $employee_id)) {
			if (!$employee_id || !$this->exists($employee_id)) {
				$employee_data['person_id'] = $employee_id = $person_data['person_id'];
				$success = $this->db->insert('employees', $employee_data);
			} else {
				$this->db->where('person_id', $employee_id);
				$success = $this->db->update('employees', $employee_data);
			}

			//We have either inserted or updated a new employee, now lets set permissions. 

		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
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

		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('people.phone_number', $phone);
		$this->db->where('employees.deleted', 0);

		if (!empty($customer_id)) {
			$this->db->where('employees.person_id !=', $customer_id);
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

		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('people.email', $email);
		$this->db->where('employees.deleted', 0);

		if (!empty($customer_id)) {
			$this->db->where('employees.person_id !=', $customer_id);
		}

		return ($this->db->get()->num_rows() >= 1);
	}

	public function saving_employee(&$person_data, &$grants_data, &$employee_data, $employee_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if (parent::save($person_data, $employee_id)) {
			if (!$employee_id || !$this->exists($employee_id)) {
				$employee_data['person_id'] = $employee_id = $person_data['person_id'];
				$success = $this->db->insert('employees', $employee_data);
			} else {
				$this->db->where('person_id', $employee_id);
				$success = $this->db->update('employees', $employee_data);
			}

			//We have either inserted or updated a new employee, now lets set permissions. 
			//ignore if user is not new
			if ($success && !$employee_id) {
				//First lets clear out any grants the employee currently has.
				$success = $this->db->delete('grants', array('person_id' => $employee_id));

				//Now insert the new grants
				if ($success) {
					for ($i = 0; $i < count($grants_data); $i++) {
						$success = $this->db->insert('grants', array('permission_id' => $grants_data[$i], 'person_id' => $employee_id));
					}
				}
			}

			//We have either inserted or updated a new employee, now lets set permissions. 

		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}
	public function save_employee_role(&$employee_data, &$grants_data, $grants_module, $employee_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		$this->db->where('person_id', $employee_id);
		$success = $this->db->update('employees', $employee_data);


		//We have either inserted or updated a new employee, now lets set permissions. 
		if ($success) {
			//First lets clear out any grants the employee currently has.
//            $already_granted = $this->db->get_where('grants',['person_id'=>$employee_id])->result();
//            var_dump($already_granted);
//            exit();
			$success = $this->db->delete('grants', array('person_id' => $employee_id));

			//Now insert the new grants
			if ($success) {
			    //created an index variable to match the grants data :::Lekan
                //N.B it is expected that the grants data and grants module contain same number of elements, I try to make sure of that in the frontend
                // Can't help it, it's the codebase I met and I can't just change it overnight so I work with it..:::Lekan
                $cInd = 0;
				foreach ($grants_data as $permission_id) {
				    $grants_module_data = explode('_',$permission_id);
					$success = $this->db->insert('grants', array('permission_id' => $permission_id, 'person_id' => $employee_id,'mod_id'=>$grants_module_data[0])); // added the table field (mod_id) :::Lekan
                    $cInd++; // increment the index :::Lekan
				}
			}
		}
		$this->db->trans_complete();
		$success &= $this->db->trans_status();
		return $success;
	}

	public function save_role(&$grants_data, $roles)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();



		//We have either inserted or updated a new employee, now lets set permissions. 


		//Now insert the new grants
		$this->db->insert('role_id', $roles);
		$role_id = $this->db->insert_id();

		foreach ($grants_data as $permission_id) {
			$success = $this->db->insert('roles', array('module_id' => $permission_id, 'roles' => $role_id));
		}




		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/*
	Deletes one employee
	*/
	public function delete($employee_id)
	{
		$success = FALSE;

		//Don't let employees delete theirself
		if ($employee_id == $this->get_logged_in_employee_info()->person_id) {
			return FALSE;
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		//Delete permissions
		if ($this->db->delete('grants', array('person_id' => $employee_id))) {
			$this->db->where('person_id', $employee_id);
			$success = $this->db->update('employees', array('deleted' => 1));
		}

		$this->db->trans_complete();

		return $success;
	}

	/*
	Deletes a list of employees
	*/
	public function delete_list($employee_ids)
	{
		$success = FALSE;

		//Don't let employees delete theirself
		if (in_array($this->get_logged_in_employee_info()->person_id, $employee_ids)) {
			return FALSE;
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->where_in('person_id', $employee_ids);
		//Delete permissions
		if ($this->db->delete('grants')) {
			//delete from employee table
			$this->db->where_in('person_id', $employee_ids);
			$success = $this->db->update('employees', array('deleted' => 1));
		}

		$this->db->trans_complete();

		return $success;
	}

	/*
	Get search suggestions to find employees
	*/
	public function get_search_suggestions($search, $limit = 5)
	{
		$suggestions = array();

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('first_name', $search);
		$this->db->or_like('last_name', $search);
		$this->db->or_like('username', $search);
		$this->db->or_like('auth_code', $search);
		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		$this->db->where('deleted', 0);
		$this->db->order_by('last_name', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->first_name . ' ' . $row->last_name);
		}

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like('email', $search);
		$this->db->order_by('email', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->email);
		}

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like('username', $search);
		$this->db->order_by('username', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->username);
		}

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like('phone_number', $search);
		$this->db->order_by('phone_number', 'asc');
		foreach ($this->db->get()->result() as $row) {
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->phone_number);
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->group_start();
		$this->db->like('first_name', $search);
		$this->db->or_like('last_name', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('phone_number', $search);
		$this->db->or_like('username', $search);
		$this->db->or_like('auth_code', $search);
		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		//$this->db->where('deleted', 0);

		return $this->db->get()->num_rows();
	}

	/*
	Performs a search on employees
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'last_name', $order = 'asc')
	{

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id = people.person_id');

		$this->db->group_start();
		$this->db->like('first_name', $search);
		$this->db->or_like('last_name', $search);
		$this->db->or_like('username', $search);
		$this->db->or_like('email', $search);
		$this->db->or_like('phone_number', $search);
		$this->db->or_like('username', $search);
		$this->db->or_like('auth_code', $search);
		$this->db->or_like('CONCAT(first_name, " ", last_name)', $search);
		$this->db->group_end();
		//$this->db->where('deleted', 0);
		$this->db->order_by($sort, $order);

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	public function login($username, $password)
	{
		$query = $this->db->get_where('employees', array('username' => $username, 'deleted' => 0), 1);

		if ($query->num_rows() == 1) {
			$row = $query->row();

			// compare passwords depending on the hash version
			if ($row->hash_version == 1 && $row->password == md5($password)) {
				$this->db->where('person_id', $row->person_id);
				$this->session->set_userdata('person_id', $row->person_id);
				$password_hash = password_hash($password, PASSWORD_DEFAULT);

				return $this->db->update('employees', array('hash_version' => 2, 'password' => $password_hash));
			} elseif ($row->hash_version == 2 && password_verify($password, $row->password)) {
				$this->session->set_userdata('person_id', $row->person_id);

				return TRUE;
			}
		}

		return FALSE;
	}

	/*
	Logs out a user by destorying all session data and redirect to login
	*/
	public function logout()
	{
		$this->session->sess_destroy();

		redirect('login');
	}

	/*
	Determins if a employee is logged in
	*/
	public function is_logged_in()
	{
		return ($this->session->userdata('person_id') != FALSE);
	}

	/*
	Gets information about the currently logged in employee.
	*/
	public function get_logged_in_employee_info()
	{
		if ($this->is_logged_in()) {
			return $this->get_info($this->session->userdata('person_id'));
		}

		return FALSE;
	}

	/*
	Determines whether the employee has access to at least one submodule
	 */
	public function has_module_grant($permission_id, $person_id)
	{
		$this->db->from('grants');
		$this->db->like('permission_id', $permission_id, 'after');
		$this->db->where('person_id', $person_id);
		$result_count = $this->db->get()->num_rows();

		if ($result_count != 1) {
			return ($result_count != 0);
		}

		return $this->has_subpermissions($permission_id);
	}

	/*
	Checks permissions
	*/
	public function has_subpermissions($permission_id)
	{
		$this->db->from('permissions');
		$this->db->like('permission_id', $permission_id . '_', 'after');

		return ($this->db->get()->num_rows() == 0);
	}

	/*
	Determines whether the employee specified employee has access the specific module.
	*/
	public function has_grant($permission_id, $person_id)
	{
		//if no module_id is null, allow access
		if ($permission_id == NULL) {
			return TRUE;
		}
		$query = $this->db->get_where('grants', array('person_id' => $person_id, 'permission_id' => $permission_id), 1);
		return ($query->num_rows() == 1);
	}

    /*
     * function to get custom roles grants
     * created by lekan :::Lekan
     */
    public function get_custom_grants($employee_id,$module){
//        return [];
        return $this->db->select("permission_id")
            ->from('grants')
//            ->like('permission_id',$module,'after')
            ->where(['person_id'=>$employee_id,'mod_id'=>$module])
//            ->group_by('mod_id')
            ->get()->result_array();
    }
    public function can_vend($employee_id){
        return $this->db->select("permission_id")
            ->from('grants')
//            ->like('permission_id',$module,'after')
            ->where(['person_id'=>$employee_id,'permission_id'=>"irecharge"])
//            ->group_by('mod_id')
            ->get()->row();
    }

	/*
	Gets employee permission grants
	*/
	public function get_employee_grants($person_id)
	{
		$this->db->from('grants');
		$this->db->where('person_id', $person_id);

		return $this->db->get()->result_array();
	}


	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	public function check_password($username, $password)
	{
		$query = $this->db->get_where('employees', array('username' => $username, 'deleted' => 0), 1);

		if ($query->num_rows() == 1) {
			$row = $query->row();

			// compare passwords
			if (password_verify($password, $row->password)) {
				return TRUE;
			}
		}

		return FALSE;
	}


	/*
	Change password for the employee
	*/
	public function change_password($employee_data, $employee_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->where('person_id', $employee_id);
		$success = $this->db->update('employees', $employee_data);

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function get_employee_by_code($code)
	{
		$query = $this->db->get_where('employees', array('auth_code' => $code), 1);

		if ($query->num_rows() == 1) {
			return $query->row();
		}

		return [];
	}
	public function get_employee_by_code_password($location, $code)
	{

		$this->db->select('employees.person_id,employees.username,employees.auth_code,employees.password,people.first_name,people.last_name');
		$this->db->from('employees AS employees');
		$this->db->join('people AS people', 'employees.person_id = people.person_id');
		$this->db->where('branch_id', $location);
		$this->db->where('auth_code', $code);
		$this->db->where('deleted', 0);


		$q = $this->db->get();
		if ($q->num_rows() == 1) {
			return $q->row();
		}

		return null;
	}

	public function get_employee_and_id()
	{
		$this->db->from('employees');
		$this->db->select('employees.person_id, people.first_name as fname, people.last_name as lname, employees.username');
		$this->db->join('people', 'employees.person_id = people.person_id');
		//$this->db->where('deleted', 0);
		$this->db->order_by('people.first_name', 'ASC');

		return $this->db->get();
	}
}
