<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Audit_lib
{
	private $CI;

	public function __construct()
	{
		$this->CI = &get_instance();
    }
    
    // public function add_log($action, $employee_id, $desc){
    public function add_log($action, $desc){
        $employee_id = $this->CI->Employee->get_logged_in_employee_info()->person_id;
        $audit_data = array(
            'action_type' => $action,
            'employee_id' => $employee_id,
            'description' => $desc
        );

        $this->CI->Audits->add($audit_data);
    }

	public function get_cart()
	{
		if (!$this->CI->session->userdata('lpo_cart')) {
			$this->set_cart(array());
		}

		return $this->CI->session->userdata('lpo_cart');
	}

	public function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('lpo_cart', $cart_data);
	}

	public function empty_cart()
	{
		$this->CI->session->unset_userdata('lpo_cart');
	}

	public function get_family()
	{
		if (!$this->CI->session->userdata('lab_family')) {
			$this->set_family('-1');
		}

		return $this->CI->session->userdata('lab_family');
	}

	public function set_family($family_id)
	{
		$this->CI->session->set_userdata('lab_family', $family_id);
	}

	public function empty_family()
	{
		$this->CI->session->unset_userdata('lab_family');
	}
}