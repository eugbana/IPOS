<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Home extends Secure_Controller 
{
	public function __construct()
	{
		parent::__construct();	
	}

	public function index()
	{
		//$this->load->model('Employee');
		$logged_in_employee_info = $this->Employee->get_logged_in_employee_info();
		// echo $logged_in_employee_info->role;
		// exit;
		if(($logged_in_employee_info->role)==3){
			$this->load->view('admin_dash');	
		}elseif(($logged_in_employee_info->role)==7){
			$this->load->view('cashier_dash');
		}elseif(($logged_in_employee_info->role)==4){
			$this->load->view('invent_home');
		}elseif(($logged_in_employee_info->role)==6){
			$this->load->view('lab_account_home');
		}elseif(($logged_in_employee_info->role)==9){
			$this->load->view('lab_result_home');
		}elseif(($logged_in_employee_info->role)==5){
			$this->load->view('sales_home');
		}elseif(($logged_in_employee_info->role)==10){
			$this->load->view('md');
		}elseif(($logged_in_employee_info->role)==13){
			$this->load->view('manager');
		}elseif(($logged_in_employee_info->role)==12){
			$this->load->view('account');
		}else{
			$this->load->view('home');
		}
	}

	public function logout()
	{
		$this->track_page('logout', 'logout');

		$this->Employee->logout();
	}
}
?>