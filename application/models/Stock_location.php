<?php
class Stock_location extends CI_Model
{
	public function exists($location_name = '')
	{
		$this->db->from('stock_locations');
		$this->db->where('location_name', $location_name);

		return ($this->db->get()->num_rows() >= 1);
	}
	public function exists_loc($location_id)
	{
		$this->db->from('stock_locations');
		$this->db->where('location_id', $location_id);

		return ($this->db->get()->num_rows() >= 1);
	}

	public function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('stock_locations');
		$this->db->limit($limit);
		$this->db->offset($offset);

		return $this->db->get();
	}
	public function get_all_form()
	{
		$all = $this->get_all()->result_array();
		$ret = array();
		foreach ($all as $k => $value) {
			$ret[$value['location_id']] = $value['location_name'];
		}
		return $ret;
	}
	public function update_phone($phone){
        return $this->update($phone,'phone');
    }
    public function update_mail($mail){
        return $this->update($mail,'mail');
    }
    public function update_name($name){
	    return $this->update($name,'name');
    }
    public function update_address($name){
        return $this->update($name,'address');
    }
    public function get_updated_locations(){
        // $employee = $this->CI->Employee->get_logged_in_employee_info();
        // $location = $this->db->get_where('stock_locations',['location_id'=>$employee->branch_id])->row();
        $this->load->library('External_calls');
        $resp = json_decode(External_calls::makeRequest(ERD_BASE_URL.'/branches'));
        if($resp->status == '00'){
            $branches = $resp->data->data;
            if(count($branches) > 0){
                foreach ($branches as $branch){
                    $e_br = $this->db->get_where('stock_locations',['location_name'=>$branch->branch_name])->row();
                    if($e_br){
                        if($e_br->brid == null){
                            $this->db->update('stock_locations',['brid'=>$branch->brid],['location_id'=>$e_br->location_id]);
                        }
                        continue;
                    }
                    $e_br = $this->db->get_where('stock_locations',['brid'=>$branch->brid])->row();
                    if($e_br){
                        if($e_br->location_name !== $branch->branch_name){
                            $this->db->update('stock_locations',['location_name'=>$branch->branch_name],['location_id'=>$e_br->location_id]);
                        }
                        continue;
                    }
                    $this->db->insert('stock_locations',['location_name'=>$branch->branch_name,
                        'location_number'=>$branch->phone,'location_address'=>$branch->address,
                        'location_email'=>$branch->email,'brid'=>$branch->brid,'deleted'=>$branch->status=='active'?0:1]);
                }
            }
            return ['message'=>"Available branch data synced, Please refresh",'success'=>true];
        }
        return ['message'=>$resp->message?$resp->message:$resp->error];
    }
    public function register_online(){
        $employee = $this->CI->Employee->get_logged_in_employee_info();
        $location = $this->db->get_where('stock_locations',['location_id'=>$employee->branch_id])->row();
        $this->load->library('External_calls');
        $resp = External_calls::makeRequest(ERD_BASE_URL.'/branches/register',
            [
                'branch_name'=>$location->location_name,
                'address'=>$location->location_address,
                'phone'=>$location->location_number,
                'email'=>$location->location_email,
				'brid'=>$location->brid,
                'caller'=>'HaslekIsBae'
            ],'POST');
        $resp = json_decode($resp);
        if($resp->status == '00'){
            $this->db->update('stock_locations',['brid'=>$resp->branch_id],['location_id'=>$employee->branch_id]);
            return ['message'=>$resp->message,'success'=>true];
        }
        return ['message'=>$resp->message?$resp->message:$resp->error];
    }
	private function update($name,$type){
        $employee = $this->CI->Employee->get_logged_in_employee_info();
        if($type == 'name'){
            $exist = $this->db->get_where('stock_locations',['location_'.$type=>$name])->row();
            if($exist && $exist->location_id != $employee->branch_id){
                return ['message'=>'Name used already by another branch'];
            }
        }
        $exist = $this->db->get_where('stock_locations',['location_id'=>$employee->branch_id])->row();
        $this->db->update('stock_locations',['location_'.$type=>$name],['location_id'=>$employee->branch_id]);
//        $exist->
        if($exist->brid){
            $this->load->library('External_calls');
            $resp = External_calls::makeRequest(ERD_BASE_URL.'/branches/'.$exist->brid.'/update/'.$type,['caller'=>'HaslekIsBae',$type=>$name],'POST');
            $resp = json_decode($resp);
            if($resp->status == '00'){
                return ['message'=>"Branch $type updated successfully",'success'=>true];
            }
            return ['message'=>$resp->message?$resp->message:$resp->error];
        }
        return ['message'=>"Branch name updated locally, please click on the register button to make it visible to other branches!!"];
    }

	public function get_undeleted_all($module_id = 'items')
	{
		$this->db->from('stock_locations');
		$this->db->join('permissions', 'permissions.location_id = stock_locations.location_id');
		$this->db->join('grants', 'grants.permission_id = permissions.permission_id');
		$this->db->where('person_id', $this->session->userdata('person_id'));
		$this->db->like('permissions.permission_id', $module_id, 'after');
		$this->db->where('deleted', 0);


		return $this->db->get();
	}

	public function show_locations($module_id = 'items')
	{
		$stock_locations = $this->get_allowed_locations($module_id);

		return count($stock_locations) > 1;
	}

	public function multiple_locations()
	{
		return $this->get_all()->num_rows() > 1;
	}

	public function get_allowed_locations($module_id = 'items')
	{
		$stock = $this->get_undeleted_all($module_id)->result_array();
		$stock_locations = array();
		foreach ($stock as $location_data) {
			$stock_locations[$location_data['location_id']] = $location_data['location_name'];
		}

		return $stock_locations;
	}

	public function get_transfer_locations()
	{
		$stock = $this->get_undeleted_all('receivings')->result_array();
		$all = $this->get_all()->result_array();
		$stock_locations = array();
		$all_locations = array();
		foreach ($stock as $location_data) {
			$stock_locations[$location_data['location_id']] = $location_data['location_name'];
		}
		foreach ($all as $all_location_data) {
			$all_locations[$all_location_data['location_id']] = $all_location_data['location_name'];
		}
		$others = array();
		foreach ($all_locations as $key => $value) {
			if (!array_key_exists($key, $stock_locations)) {
				$others[$key] = $value;
			}
		}

		return $others;
	}

	public function is_allowed_location($location_id, $module_id = 'items')
	{
		$this->db->from('stock_locations');
		$this->db->join('permissions', 'permissions.location_id = stock_locations.location_id');
		$this->db->join('grants', 'grants.permission_id = permissions.permission_id');
		$this->db->where('person_id', $this->session->userdata('person_id'));
		$this->db->like('permissions.permission_id', $module_id, 'after');
		$this->db->where('deleted', 0);
		$this->db->where('stock_locations.location_id', $location_id);

		return ($this->db->get()->num_rows() == 1);
	}

	public function get_default_location_id()
	{
		/*$this->db->from('stock_locations');
		$this->db->join('permissions', 'permissions.location_id = stock_locations.location_id');
		$this->db->join('grants', 'grants.permission_id = permissions.permission_id');
		$this->db->where('person_id', $this->session->userdata('person_id'));
		$this->db->where('deleted', 0);
		$this->db->limit(1);
		return $this->db->get()->row()->location_id;*/
		/*
		This is supposed to return the location of the current employee
		*/
		$this->db->from('employees');
		$this->db->where('person_id', $this->session->userdata('person_id'));
		$this->db->where('deleted', 0);
		$this->db->limit(1);
		return $this->db->get()->row()->branch_id;
	}

	public function get_location_name($location_id)
	{
		$this->db->from('stock_locations');
		$this->db->where('location_id', $location_id);


		return $this->db->get()->row()->location_name;
	}

	public function save(&$location_data, $location_id)
	{
		$location_name = $location_data['location_name'];
		$location_address = $location_data['location_address'];
		$location_number = $location_data['location_number'];

		if (!$this->exists_loc($location_id)) {
			$this->db->trans_start();

			$location_data = array('location_name' => $location_name, 'deleted' => 0, 'location_address' => $location_address, 'location_number' => $location_number);
			$this->db->insert('stock_locations', $location_data);
			$location_id = $this->db->insert_id();
			$this->_insert_new_permission('items', $location_id, $location_name);
			$this->_insert_new_permission('sales', $location_id, $location_name);
			$this->_insert_new_permission('receivings', $location_id, $location_name);


			$this->db->trans_complete();

			return $this->db->trans_status();
		} else {
			$this->db->where('location_id', $location_id);

			return $this->db->update('stock_locations', $location_data);
		}
	}

	private function _insert_new_permission($module, $location_id, $location_name)
	{
		// insert new permission for stock location
		$permission_id = $module . '_' . $location_name;
		$permission_data = array('permission_id' => $permission_id, 'module_id' => $module, 'location_id' => $location_id);
		$this->db->insert('permissions', $permission_data);

		// insert grants for new permission
		$employees = $this->Employee->get_all();
		foreach ($employees->result_array() as $employee) {
			if ($employee['role'] == 10) {
				$grants_data = array('permission_id' => $permission_id, 'person_id' => $employee['person_id']);
				$this->db->insert('grants', $grants_data);
			}
		}
	}

	/*
     Deletes one item
    */
	public function delete($location_id)
	{
		$this->db->trans_start();

		$this->db->where('location_id', $location_id);
		$this->db->update('stock_locations', array('deleted' => 1));

		$this->db->where('location_id', $location_id);
		$this->db->delete('permissions');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
