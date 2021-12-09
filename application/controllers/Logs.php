<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Logs extends Secure_Controller {
    public function __construct() {
		parent::__construct('employees');
//		$this->load->library('item_lib');
//		$this->load->library('sale_lib');
    }
    
    public function index() {
        $employees = $this->Employee->get_employee_and_id();
        $data['employees'] = $employees->result();
        $this->load->view('logs/index', $data);
    }
}
