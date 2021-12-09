<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Secure_Controller extends CI_Controller
{
	/*
	* Controllers that are considered secure extend Secure_Controller, optionally a $module_id can
	* be set to also check if a user can access a particular module in the system.
	*/
	public function __construct($module_id = NULL, $submodule_id = NULL)
	{
//	    exit();
		parent::__construct();
//		exit();
        $this->load->model('Employee','model');
        if (!$this->model->is_logged_in()) {
            redirect('login');
        }

		//$this->load->helper('url');
        $this->CI = &get_instance();
//        exit();
		$data = $this->CI->session->userdata('initData');
//		var_dump($data);
//		exit();
        $this->track_page($module_id, $module_id);
		if(!$data){

//            $this->model = $this->Employee;



            $logged_in_employee_info = $this->model->get_logged_in_employee_info();
            // if (
            // 	!$this->model->has_module_grant($module_id, $logged_in_employee_info->person_id) || (isset($submodule_id) && !$this->model->has_module_grant($submodule_id, $logged_in_employee_info->person_id))
            // ) {
            // 	//Allow accountants to pass when they are accessing anything from receivings
            // 	if ($module_id != 'receivings' && $logged_in_employee_info->role != 12) {
            // 		redirect('no_access/' . $module_id . '/' . $submodule_id);
            // 	}
            // }

            // load up global data visible to all the loaded views
            $a_modules =  $this->Module->get_allowed_modules($logged_in_employee_info->person_id);
            $data['allowed_modules'] = $a_modules->result();
            $a_modulles = $a_modules->result();
            if(count($a_modulles) > 0 ){
                foreach ($a_modulles as $module){
                    $data['u_mod_grants'][$module->module_id] = $this->model->get_custom_grants($logged_in_employee_info->person_id,$module->module_id);
                }
            }
//		$data['u_mod_grants'] = $model->get_custom_grants($logged_in_employee_info->person_id);
            $user_info = $logged_in_employee_info;
            $data['user_info'] = $logged_in_employee_info;
            $data['user_role'] = $this->Role->get_user_role($logged_in_employee_info->role);
            $data['sale_stuff'] = $branch_sales = $this->Item->get_sales();
            $data['inventory_total'] = 0; // count($this->Item->get_inventory_total($user_info->branch_id));
            $data['stock_total'] = 0; //count($this->Item->get_stock_total($user_info->branch_id));
            $data['reorder_total'] = 0; // count($this->Item->get_reorder_total($user_info->branch_id));
            $expiry_check = 0; // $this->Item->get_expiry_total($user_info->branch_id);


            $data['expiry_total'] = 0; // count($this->check_if_expire($expiry_check));
            $data['branch'] = $this->CI->Stock_location->get_location_name($user_info->branch_id);
            $data['controller_name'] = $module_id;
            $newSales = [];
            foreach ($branch_sales as $row => $value) {
//			$stock_quan = count($this->Item->get_inventory_total($value['location_id']));
                $stock_quan = $this->Item->get_inventory_total($value['location_id'],true);
                $md_expiry_check = $this->Item->get_expiry_total($value['location_id']);
//			$stock_out = count($this->Item->get_reorder_total($value['location_id']));
                $stock_out = $this->Item->get_reorder_total($value['location_id'],true);
                $expiry_quan = count($this->check_if_expire($md_expiry_check));
                $newSales[] = array('location_id' => $value['location_id'], 'stock_out' => $stock_out, 'location_name' => $value['location_name'], 'stock_quan' => $stock_quan, 'sales_amount' => $value['sales_amount'], 'expiry_quan' => $expiry_quan);
            }
            $data['newSales'] = $newSales;
            $this->CI->session->set_userdata('initData',$data);
        }else{
		    if($module_id){
		        $data['controller_name'] = $module_id;
            }
        }
//		var_dump($data);
//		exit();
		$this->load->vars($data);
	}
	public function check_if_expire($expiry_check)
	{
	    $expiry_total = [];
		if(is_array($expiry_check) && count($expiry_check)>0){
            foreach ($expiry_check as $row => $value) {
                $datetimenow = date('Y/m/d H:i:s');
                $datetimeexpire = $value['expiry'];
                $datetimenowc = strtotime($datetimenow);
                $datetimeexpirec = strtotime($datetimeexpire);
                $des = $datetimeexpirec - $datetimenowc;
                if ($value['period'] == 'days') {
                    $d_days = 86400;
                } elseif ($value['period'] == 'weeks') {
                    $d_days = 604800;
                } else {
                    $d_days = 2419200;
                }
                $dd = $des / $d_days;

                if ($dd < $value['expiry_days']) {
                    $expiry_total[] = $value['name'];
                }
            }
        }
		return $expiry_total;
	}

	/*
	* Internal method to do XSS clean in the derived classes
	*/
	protected function xss_clean($str, $is_image = FALSE,$let_be = false)
	{
		// This setting is configurable in application/config/config.php.
		// Users can disable the XSS clean for performance reasons
		// (cases like intranet installation with no Internet access)
        if($let_be){
            return $str;
        }
		if ($this->config->item('ospos_xss_clean') == FALSE) {
			return $str;
		} else {
			return $this->security->xss_clean($str, $is_image);
		}
	}

	protected function track_page($path, $page)
	{
		if (get_instance()->Appconfig->get('statistics')) {
			$this->load->library('tracking_lib');

			if (empty($path)) {
				$path = 'home';
				$page = 'home';
			}

			$this->tracking_lib->track_page('controller/' . $path, $page);
		}
	}

	protected function track_event($category, $action, $label, $value = NULL)
	{
		if (get_instance()->Appconfig->get('statistics')) {
			$this->load->library('tracking_lib');

			$this->tracking_lib->track_event($category, $action, $label, $value);
		}
	}

	public function numeric($str)
	{
		return parse_decimals($str);
	}

	public function check_numeric()
	{
		$result = TRUE;

		foreach ($this->input->get() as $str) {
			$result = parse_decimals($str);
		}

		echo $result !== FALSE ? 'true' : 'false';
	}


	// this is the basic set of methods most OSPOS Controllers will implement
	public function index()
	{
		return FALSE;
	}
	public function search()
	{
		return FALSE;
	}
	public function suggest_search()
	{
		return FALSE;
	}
	public function view($data_item_id = -1)
	{
		return FALSE;
	}
	public function save($data_item_id = -1)
	{
		return FALSE;
	}
	public function delete()
	{
		return FALSE;
	}
}
