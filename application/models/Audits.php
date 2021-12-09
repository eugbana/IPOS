<?php
class Audits extends CI_Model
{

    public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'audits.audit_id', $order = 'asc')
	{

		$this->db->select("audit_logs.*,
			CONCAT(employee.first_name,' ', employee.last_name) AS employee_name
		");
		$this->db->from('audit_logs AS audit_logs');
		$this->db->join('people as employee', 'employee.person_id = audit_logs.employee_id', 'left'); //there is always employee id
		return $this->db->get()->result_array();

		// avoid duplicated entries with same name because of inventory reporting multiple changes on the same item in the same date range
		// $this->db->group_by('audits.audit_id');

		// order by name of item
		$this->db->order_by($sort, $order);

		if ($rows > 0) {
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	public function add($audit_data){
		$this->db->insert('audit_logs', $audit_data);
	}

	public function get_action_types(){
		$this->db->distinct();
		$this->db->select('action_type');
		$this->db->from('audit_logs');
		return $this->db->get()->result();
		$query = $this->db->get();

		return $query->num_rows();
	}

}