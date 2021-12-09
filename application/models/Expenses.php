<?php
class Expenses extends CI_Model
{

    public function categories_list()
	{
		$categories = array();

		$this->db->select('id,name,type,created_at'); 
		$this->db->from('expense_categories');
		
		$this->db->order_by('id', 'asc');

		foreach ($this->db->get()->result() as $row) {
			if($row->id != 1)
			$categories[] = array('id' => $row->id, 'name' => $row->name, 'type' => $row->type, 'created_at' => $row->created_at);
		}
		return $categories;
	}
	
	public function get_category_info($category_id)
	{
		$this->db->from('expense_categories');
		$this->db->where('id', $category_id);
		$query = $this->db->get();
		return $query->row();

		// if ($query->num_rows() == 1) {
		// 	return $query->row();
		// } else {
		// 	//Get empty base parent object, as $employee_id is NOT an employee
		// 	$person_obj = parent::get_info(-1);

		// 	//Get all the fields from employee table
		// 	//append those fields to base parent object, we we have a complete empty object
		// 	foreach ($this->db->list_fields('employees') as $field) {
		// 		$person_obj->$field = '';
		// 	}

		// 	return $person_obj;
		// }
	}
    
    public function search($search, $filters, $rows = 0, $limit_from = 0, $type = "all", $sort = 'expenses.id', $order = 'desc')
	{

		$cats = $this->categories_list();

		$this->db->select("expenses.*,
            CONCAT(employee.first_name,' ', employee.last_name) AS employee_name,
            category.name as category_name,
            category.type as category_type
		");
		$this->db->from('expenses AS expenses');
		$this->db->join('people as employee', 'employee.person_id = expenses.employee_id', 'left');
		$this->db->join('expense_categories as category', 'category.id = expenses.expense_category_id', 'left');
		
		foreach ($cats as $row => $value) {
			if ($filters[str_replace(' ', '_', $value['name'])] != FALSE) {
				$this->db->where('category.name', $value['name']);
			}
		}

		if($type != "all"){
			$this->db->where('expenses.type', $type);
		}
        
        // print_r($this->db->get()->result_array());
		// return $this->db->get()->result_array();

		// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
		// $this->db->group_by('expenses.id');

		// order by name of item
		$this->db->order_by($sort, $order);

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
        }
        
        // print_r($this->db->get());

		return $this->db->get();
    }
    
    public function save_category(&$expense_category_data, $expense_category_id = FALSE)
	{
		if (!$expense_category_id || !$this->exists($expense_category_id, TRUE)) {
			if ($this->db->insert('expense_categories', $expense_category_data)) {
				$expense_category_data['id'] = $this->db->insert_id();
				return TRUE;
			}
			return FALSE;
		}

		$this->db->where('id', $expense_category_id);

		return $this->db->update('expense_categories', $expense_category_data);
	}
	public function update_category($expense_category_data, $category_name)
	{
		$this->db->where('name', $category_name);
		return $this->db->update('expense_categories', $expense_category_data);
	}
	
	public function get_info($expense_id)
	{
		$this->db->select('expenses.*');
		$this->db->select('expense_categories.name');
		$this->db->from('expenses');
		$this->db->join('expense_categories', 'expense_categories.id = expenses.expense_category_id', 'left');
		$this->db->where('expenses.id', $expense_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$expense_obj = new stdClass();

			//Get all the fields from items table
			foreach ($this->db->list_fields('expenses') as $field) {
				$expense_obj->$field = '';
			}
			return $expense_obj;
		}
	}

	public function get_all_expense_categories($limit_from = 0, $rows = 0)
	{
		$this->db->from('expense_categories');
		$this->db->order_by('name', 'asc');
		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	public function push_expenses_batch()
	{
		$payload = [];
		$this->load->model('Employee');
		$branch = $this->Employee->get_branchinfo(2);
		$erd_url = ERD_BASE_URL.'/branches/expenses/'.$branch->brid.'/batch/register';
		$expenses = $this->db->from('expenses')
			->join('expense_categories','expenses.expense_category_id = expense_categories.id')
			->join('people','expenses.employee_id = people.person_id')
			->select('expense_categories.name as category,expenses.expense_category_id,expenses.receipt_no,expense_categories.type as category_type,expenses.type,expenses.id as expense_id,expenses.amount,expenses.details,expenses.balance,expenses.created_at as expense_date,CONCAT(ipos_people.first_name,ipos_people.last_name) as employee_name')
			->where('expenses.registered = 0')
			->limit(50)
			->get()->result_array();
		if(count($expenses)>0){
			$payload['expenses'] = $expenses;
			$payload['caller'] = "HaslekIsBae";
            $this->load->library('External_calls');
            $erd_response = External_calls::makeRequest($erd_url,$payload,"POST");
            $erd_response = json_decode($erd_response,true);
            if(isset($erd_response['status']) && $erd_response['status'] == "00"){
                $data = $erd_response['data'];
                foreach ($data as $s_id=>$stat){
                    $this->db->update('expenses',['registered'=>$stat],['id'=>$s_id]);
                }
				return ['message'=>count($expenses)." expenses registered",'data'=>$erd_response['data']];
            }
			return ['message'=>isset($erd_response['message'])?$erd_response['message']:"An errors occurred!",'response'=>$erd_response];
		}
		return ['message'=>"No expenses to register!"];
	}

	public function get_balance_from_amount($amount, $type)
	{
		$this->db->from('expenses');
		// $this->db->where('sale_status', 0);
		$this->db->order_by("id", "desc");

		$bal = $this->db->get()->row(0)->balance;

		return $type == 'INFLOW' ? $bal + $amount : $bal - $amount;
	}

	public function get_current_balance()
	{
		$this->db->from('expenses');
		$this->db->order_by("id", "desc");
		$bal = $this->db->get()->row(0)->balance;
		return $bal;
	}

	public function exists($expense_id, $ignore_deleted = FALSE, $deleted = FALSE)
	{
		if (ctype_digit($expense_id)) {
			$this->db->from('expenses');
			$this->db->where('id', (int) $expense_id);
			// if ($ignore_deleted == FALSE) {
			// 	$this->db->where('deleted', $deleted);
			// }

			return ($this->db->get()->num_rows() == 1);
		}
		return FALSE;
	}

	public function save(&$expense_data, $expense_id = FALSE)
	{
		// var_dump($expense_data);
		// 		exit();
		if (!$expense_id || !$this->exists($expense_id, TRUE)) {
			if ($this->db->insert('expenses', $expense_data)) {
				$this->push_expenses_batch();
				$expense_data['id'] = $this->db->insert_id();
				return TRUE;
			}
			return FALSE;
		}

		$this->db->where('id', $expense_id);
		return $this->db->update('expenses', $expense_data);
	}
    

}