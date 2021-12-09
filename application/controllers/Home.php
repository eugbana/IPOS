<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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
        $this->check_stock();

		$logged_in_employee_info = $this->Employee->get_logged_in_employee_info();

		// echo $logged_in_employee_info->role;
		// exit();
		$data = array();

		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;
		$data['expiryItemsCount'] = $model->getExpiryItemsCount();

		//getBelowReorderLevelCount
		$this->load->model('reports/Detailed_Receivings');
		$model = $this->Detailed_Receivings;
		$data['reorderLevelItemsCount'] = $model->getBelowReorderLevelCount();

		//added this for permission control
		if (($logged_in_employee_info->role) == 3) {
			// echo 'superadmin';
			// die;
			$this->load->view('admin_dash', $data);
		} elseif (($logged_in_employee_info->role) == 7) {
			$this->load->view('cashier_dash', $data);
		} elseif (($logged_in_employee_info->role) == 4) {
			$this->load->view('invent_home', $data);
		} elseif (($logged_in_employee_info->role) == 6) {
			$this->load->view('lab_account_home', $data);
		} elseif (($logged_in_employee_info->role) == 9) {
			$this->load->view('lab_result_home', $data);
		} elseif (($logged_in_employee_info->role) == 5) {
			$this->load->view('sales_home', $data);
		} elseif (($logged_in_employee_info->role) == 10) {
			$this->load->view('md', $data);
		} elseif (($logged_in_employee_info->role) == 13 || ($logged_in_employee_info->role) == 14) {
			$this->load->view('manager', $data);
		}
//		elseif (($logged_in_employee_info->role) == 14) {
//			$this->load->view('manager', $data);
//		}
		elseif (($logged_in_employee_info->role) == 12) {
			$this->load->view('account', $data);
		} else {
			//this is for role number 0 or 14

            //lekan's comment: this also serve custom roles
			$this->load->view('home', $data);
		}
	}
    public function check_stock()
    {
        $today = date("Y-m-d");
        $lastDay = date("t", strtotime($today));
        $firstDay = 03;

        $dayOfToday = explode("-", $today);

//		if ($lastDay == $dayOfToday[2]) {
//			//record closing stock
//			$this->Item->record_stock(date("m"), date("Y"), false);
//		} else
        if ($firstDay == $dayOfToday[2]) {
            //record opening stock
            $this->Item->record_stock(date("m"), date("Y"), true);
        }
    }

	public function logout()
	{
		$this->track_page('logout', 'logout');

		$this->Employee->logout();
	}
}
