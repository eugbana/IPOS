<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_lib
{
	private $CI;

	public function __construct()
	{
		$this->CI = &get_instance();
	}

	public function get_dept()
	{
		if (!$this->CI->session->userdata('report_dept')) {
			$this->set_dept('Superstore');
		}

		return $this->CI->session->userdata('report_dept');
	}

	public function set_dept($dept)
	{
		$this->CI->session->set_userdata('report_dept', $dept);
	}

	public function clear_dept()
	{
		$this->CI->session->unset_userdata('report_dept');
	}

}
