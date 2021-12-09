<?php
class Appconfig extends CI_Model
{
	public function exists($key)
	{
		$this->db->from('app_config');
		$this->db->where('app_config.key', $key);

		return ($this->db->get()->num_rows() == 1);
	}

	public function get_all()
	{
		$this->db->from('app_config');
		$this->db->order_by('key', 'asc');

		return $this->db->get();
	}
	public function update_extra_config($data,$clause){
	    return $this->db->update('app_config_extra',$data,$clause);
    }
    public function add_extra_config($data){
	    return $this->db->insert('app_config_extra',$data);
    }
    public function get_extra_config($clause=[]){
	     $this->db->from('app_config_extra');
	     if(!empty($clause)){
	         $this->db->where($clause);
         }
	     return $this->db->get()->result();
    }

	public function get($key)
	{
		$query = $this->db->get_where('app_config', array('key' => $key), 1);

		if ($query->num_rows() == 1) {
			return $query->row()->value;
		}

		return '';
	}

	public function save($key, $value)
	{
		$config_data = array(
			'key'   => $key,
			'value' => $value
		);

		if (!$this->exists($key)) {
			return $this->db->insert('app_config', $config_data);
		}

		$this->db->where('key', $key);

		return $this->db->update('app_config', $config_data);
	}

	public function batch_save($data)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		foreach ($data as $key => $value) {
			$success &= $this->save($key, $value);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	public function reorder_level_save($data)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		foreach ($data as $key => $value) {
			$success &= $this->save($key, $value);
		}

		$this->db->trans_complete();

		$success &= $this->db->trans_status();

		return $success;
	}

	/**
	 * This function is used to zero all the quantities. usually done before taking new inventory
	 */
	public function zero_all()
	{
		//fetch the quantitiies in the location where this logged in employee is
		$employee = $this->Employee->get_logged_in_employee_info();

		$this->db->select("item_id,quantity");
		$this->db->from("item_quantities");
		$this->db->where("location_id", $employee->branch_id);
		$this->db->where("quantity >0");

		$quantities = $this->db->get()->result();
		$inv_data = array();
		foreach ($quantities as $index => $quantity) {
			//Add it to the inventory for tracking
			$inv_data = array(
				'trans_date'		=> date('Y-m-d H:i:s'),
				'trans_items'		=> $quantity->item_id,
				'trans_user'		=> $employee->employee_id,
				'trans_location'	=> $employee->branch_id,
				'trans_comment'		=> "All Product Quantity Zeroed",
				'trans_inventory'	=> $quantity->quantity * -1,
				'trans_remaining' => 0
			);
		}
	}

	public function delete($key)
	{
		return $this->db->delete('app_config', array('key' => $key));
	}

	public function delete_all()
	{
		return $this->db->empty_table('app_config');
	}

	public function acquire_save_next_invoice_sequence()
	{
		$last_used = $this->get('last_used_invoice_number') + 1;
		$this->save('last_used_invoice_number', $last_used);
		return $last_used;
	}

	public function acquire_save_next_quote_sequence()
	{
		$last_used = $this->get('last_used_quote_number') + 1;
		$this->save('last_used_quote_number', $last_used);
		return $last_used;
	}
}
