<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Account extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('account');
		$this->load->library('item_lib');
		$this->load->library('sale_lib');
		$this->load->library('tax_lib');
		$this->load->library('barcode_lib');
		$this->load->library('email_lib');
		$this->load->library('token_lib');
	}

	public function index()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		$data['invoice']=$this->Item->total_invoice_item(7);
		$invoice_details=$this->Item->total_invoice_item(7);
			$quantity=1;
			$item_location=1;
			$discount=0;
			foreach($invoice_details as $row=>$value){
				//$this->sale_lib->add_labitem($value['item_id'], $quantity, $item_location, $discount);
			}
			
		//$data['cart']=$this->sale_lib->get_lab_accountcart();
		$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);

		
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		
		

		$this->load->view('account/manage', $data);
	}
	public function processed_payment()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		$data['invoice']=$this->Item->total_invoice_item(7);
		$invoice_details=$this->Item->total_invoice_item(7);
			$quantity=1;
			$item_location=1;
			$discount=0;
			foreach($invoice_details as $row=>$value){
				//$this->sale_lib->add_labitem($value['item_id'], $quantity, $item_location, $discount);
			}
			
		//$data['cart']=$this->sale_lib->get_lab_accountcart();
		$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);

		
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		
		

		$this->load->view('account/processed', $data);
	}
	public function unprocessed_payment()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		/*$data['invoice']=$this->Item->total_invoice_item(7);
		$invoice_details=$this->Item->total_invoice_item(7);
			$quantity=1;
			$item_location=1;
			$discount=0;
			foreach($invoice_details as $row=>$value){
				$this->sale_lib->add_labitem($value['item_id'], $quantity, $item_location, $discount);
			}*/
			
		//$data['cart']=$this->sale_lib->get_lab_accountcart();
		$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);

		
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		
		

		$this->load->view('account/unprocessed', $data);
	}
	public function scientist()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		$data['invoice']=$this->Item->total_invoice_item(7);
		$invoice_details=$this->Item->total_invoice_item(7);
			$quantity=1;
			$item_location=1;
			$discount=0;
			foreach($invoice_details as $row=>$value){
				$this->sale_lib->add_labitem($value['item_id'], $quantity, $item_location, $discount);
			}
			
		//$data['cart']=$this->sale_lib->get_lab_accountcart();
		$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);

		
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		
		

		$this->load->view('account/scientist_page', $data);
	}
	public function new_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		
		$this->sale_lib->clear_all();
		$this->load->view('account/new_results', $data);
	}
	public function pending_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		
		$this->sale_lib->clear_all();
		$this->load->view('account/pending_results', $data);
	}
	public function completed_results()
	{
		//$data['table_headers'] = $this->xss_clean(get_account_manage_table_headers());
		//$data['notice'] = $this->sale_lib->notice_transfer_items();
		//$data['transfer'] = $this->sale_lib->global_transfer_items();
		//$data['cart'] = $this->sale_lib->get_cart();
		//$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);
		//$data['cart']=Array ( 1 => Array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => Array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
		
		$this->sale_lib->clear_all();
		$this->load->view('account/result', $data);
	}
	private function _load_lab_invoice_data($invoice_id, &$data, $stats = FALSE)
	{
		$invoice_info = '';

		if($invoice_id != -1)
		{
			$invoice_info = $this->Customer->get_lab_invoice_info($invoice_id);
			
			$data['person_id'] = $invoice_info->person_id;
			$data['doctor_name'] = $invoice_info->doctor_name;
			
			
			
		}

		return $invoice_info;
	}
	public function print_labaccount_info(){
				$invoice_id=$this->input->post('invoice_id');
				$sale_info=$this->Item->get_saleinfo($invoice_id);
			    $sale_id=$data['sale_id']=$sale_info->sale_id;
				$data['cart'] = $this->sale_lib->get_labcart_reordered($sale_id);
				$this->load->view('account/receipt', $data);
				//$this->load->view('account/receipt', $data);
				$this->sale_lib->clear_all();
		
	}

	public function lab_account_sales(){
		// Save the data to the sales table
		//$this->load->view('sales/receipt', $data);
		
			$invoice_id=$this->input->post('invoice_id');
			$invoice_details=$this->Item->total_invoice_item($invoice_id);
			$quantity=1;
			$item_location=1;
			$discount=0;
			//$iteg=1330;
			foreach($invoice_details as $row=>$value){
				$this->sale_lib->item_add_lab($value['item_id'], $quantity, $item_location, $discount);
			}
			
			//$invoice_id=$this->input->post('invoice_id');
			$invoice_info=$this->_load_lab_invoice_data($invoice_id, $data);
			$customer_id=$data['person_id'];
			//$customer_id=23;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['cart']=$this->sale_lib->get_lab_accountcart();
			$data['dinner_table']=1;
			$data['invoice_details']=$invoice_details;
			$data['comments']='';
			$data['payments']=5400;
			//$customer_id=23;
			
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee_info = $this->Employee->get_info($employee_id);
			$totals = $this->sale_lib->get_labaccount_totals();
		
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');

		$data['discounted_subtotal'] = $totals['discounted_subtotal'];
		$data['tax_exclusive_subtotal'] = $totals['tax_exclusive_subtotal'];

		if($data['cash_rounding'])
		{
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		}
		else
		{
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
			$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
			$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')));
			$data['sale_status'] = '0'; // Complete
			$data['sale_id_num'] = $this->Sale->lab_accountsave($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes'],$invoice_id);

			$data['sale_id'] = 'POS ' . $data['sale_id_num'];

			$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
			$data = $this->xss_clean($data);

			if($data['sale_id_num'] == -1)
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			else
			{
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

				// Reload (sorted) and filter the cart line items for printing purposes
				$data['cart'] = $this->get_filtered($this->sale_lib->get_labcart_reordered($data['sale_id_num']));
				//$this->load->view('account/receipt', $data);
				//$this->load->view('account/receipt', $data);
				$this->sale_lib->clear_all();
			}
			$this->load->view('account/receipt', $data);
		//$this->load->view('sales/receipt', $data);
		
	}
	public function lab_result_sales(){
		// Save the data to the sales table
		
			$invoice_id=$this->input->post('invoice_id');
			$invoice_details=$this->Item->total_invoice_item($invoice_id);
			$quantity=1;
			$item_location=1;
			$discount=0;
			foreach($invoice_details as $row=>$value){
				$this->sale_lib->item_add_lab($value['item_id'], $quantity, $item_location, $discount);
			}
			
			//$invoice_id=$this->input->post('invoice_id');
			$invoice_info=$this->_load_lab_invoice_data($invoice_id, $data);
			$customer_id=$data['person_id'];
			//$customer_id=23;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['cart']=$this->sale_lib->get_lab_accountcart();
			$data['dinner_table']=1;
			$data['comments']='';
			$data['payments']=5400;
			//$customer_id=23;
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee_info = $this->Employee->get_info($employee_id);
			$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
			$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
			$data['sale_status'] = '0'; // Complete
			$data['sale_id_num'] = $this->Sale->lab_accountsave($data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], NULL, NULL, $data['payments'], $data['dinner_table'], $data['taxes'],$invoice_id);

			$data['sale_id'] = 'POS ' . $data['sale_id_num'];

			$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
			$data = $this->xss_clean($data);

			if($data['sale_id_num'] == -1)
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			else
			{
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

				// Reload (sorted) and filter the cart line items for printing purposes
				$data['cart'] = $this->get_filtered($this->sale_lib->get_labresultcart_reordered($data['sale_id_num']));

				$this->load->view('account/receipt', $data);
				$this->sale_lib->clear_all();
			}
		//$this->load->view('sales/receipt', $data);
	}
	public function lab_result_receipt(){
		// Save the data to the sales table
			$data=array();
		
			//$customer_id=$data['person_id'];
			$customer_id=$this->sale_lib->get_customer();
			$data['customer_id']=$customer_id;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['cart']=$this->sale_lib->get_lab_resultcart();
			$sale_id=$data['sale_id']=$this->sale_lib->get_sales_id();
			
			
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee_info = $this->Employee->get_info($employee_id);
			$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
			$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
			$data['sale_status'] = '0'; // Complete
			$this->Sale->lab_save_result($data['sale_status'], $data['cart'],$sale_id, $employee_id);

			

				$this->load->view('account/receipt', $data);
				$this->sale_lib->clear_all();
		
		//$this->load->view('sales/receipt', $data);
	}
	
	public function lab_result_saved(){
		// Save the data to the sales table
			$data=array();
		
			//$customer_id=$data['person_id'];
			$customer_id=$this->sale_lib->get_customer();
			$data['customer_id']=$customer_id;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['cart']=$this->sale_lib->get_lab_resultcart();
			$sale_id=$data['sale_id']=$this->sale_lib->get_sales_id();
			
			
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee_info = $this->Employee->get_info($employee_id);
			$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
			$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
			$data['sale_status'] = '0'; // Complete
			$this->Sale->lab_saved_result($data['sale_status'], $data['cart'],$sale_id, $employee_id);

			

				//$this->load->view('account/receipt', $data);
				$this->sale_lib->clear_all();
		
		//$this->load->view('sales/receipt', $data);
	}
	public function lab_pending_result(){
		// Save the data to the sales table
		
			//$sale_id=$this->input->post('sale_id');
			$invoice_id=$this->input->post('invoice_id');
			//$invoice_details=$this->Item->total_invoice_item($invoice_id);
			$sale_info=$this->Item->get_saleinfo($invoice_id);
			$sale_id=$sale_info->sale_id;
			$this->sale_lib->set_sales_id($sale_id);
			$quantity=NULL;
			$item_location=1;
			
			foreach($invoice_details as $row=>$value){
			//	$this->sale_lib->item_add_result($value['item_id'], $quantity, $item_location);
			}
			
			//$invoice_id=$this->input->post('invoice_id');
			$invoice_info=$this->_load_lab_invoice_data($invoice_id, $data);
			$customer_id=$data['person_id'];
			$this->sale_lib->set_customer($customer_id);
			//$customer_id=23;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['customer_id']=$customer_id;
			//$data['cart']=$this->sale_lib->get_lab_resultcart();
			$data['cart'] = $this->sale_lib->get_labsaveresultcart_reordered($sale_id);
			$data['invoice_details']=$invoice_details;
			
			//$customer_id=23;
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			
			
		$this->load->view('account/check', $data);
	}
	public function lab_result(){
		// Save the data to the sales table
		
			$invoice_id=$this->input->post('invoice_id');
			$invoice_details=$this->Item->total_invoice_item($invoice_id);
			$sale_info=$this->Item->get_saleinfo($invoice_id);
			$sale_id=$sale_info->sale_id;
			$this->sale_lib->set_sales_id($sale_id);
			$quantity=NULL;
			$item_location=1;
			
			foreach($invoice_details as $row=>$value){
				$this->sale_lib->item_add_result($value['item_id'], $quantity, $item_location);
			}
			
			//$invoice_id=$this->input->post('invoice_id');
			$invoice_info=$this->_load_lab_invoice_data($invoice_id, $data);
			$customer_id=$data['person_id'];
			$this->sale_lib->set_customer($customer_id);
			//$customer_id=23;
			$customer_info = $this->_load_customer_data($customer_id, $data);
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			//$data['taxes']=array();
			$data['customer_id']=$customer_id;
			$data['cart']=$this->sale_lib->get_lab_resultcart();
			$data['invoice_details']=$invoice_details;
			
			//$customer_id=23;
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			
			
		$this->load->view('account/check', $data);
	}
	public function edit_item($item_id)
	{
		$data = array();

		$test_comment = $this->input->post('test_comment');

		$this->sale_lib->edit_result($item_id, $test_comment);
		$data['cart']=$this->sale_lib->get_lab_resultcart();

		$this->load->view('account/check', $data);
	}

	public function get_filtered($cart)
	{
		$filtered_cart = array();
		foreach($cart as $id => $item)
		{
			if($item['print_option'] == '0') // always include
			{
				$filtered_cart[$id] = $item;
			}
			elseif($item['print_option'] == '1' && $item['price'] != 0)  // include only if the price is not zero
			{
				$filtered_cart[$id] = $item;
			}
			// print_option 2 is never included
		}

		return $filtered_cart;
	}
	public function laboratory_items(){
		$lab_items = array();
		$data=$this->Item->account_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>$value['status']==0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>': '<button class="btn btn-info btn-sm pull-left modal-dlg" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>','print' =>'<button class="btn btn-info btn-sm pull-left modal-dlg"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	public function unprocessed_laboratory_items(){
		$lab_items = array();
		$data=$this->Item->unprocessed_account_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>$value['status']==0 ? '<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Not Processed</button>': '<button class="btn btn-info btn-sm pull-left modal-dlg" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Processed</button>','print' =>'');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	public function processed_laboratory_items(){
		$lab_items = array();
		$data=$this->Item->processed_account_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['person_id'], $data);
			$lab_items[] = array('invoice_id' => $value['invoice_id'], 'person_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>'<button class="btn btn-success btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>','print' =>'');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	public function new_result_items(){
		$lab_items = array();
		$data=$this->Item->new_result_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['customer_id'], $data);
			$lab_items[] = array('sale_id' => $value['sale_id'], 'customer_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>','print' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg " id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	public function pending_result_items(){
		$lab_items = array();
		$data=$this->Item->pending_result_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['customer_id'], $data);
			$lab_items[] = array('sale_id' => $value['sale_id'], 'customer_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>','print' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg " id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	public function completed_result_items(){
		$lab_items = array();
		$data=$this->Item->completed_result_items();
		foreach($data as $row=>$value){
			$customer_info = $this->_load_customer_data($value['customer_id'], $data);
			$lab_items[] = array('sale_id' => $value['sale_id'], 'customer_id' => $data['customer'], 'doctor_name' => $value['doctor_name'], 'edit' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg update" id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Edit</button>','print' =>'<button class="btn btn-danger btn-sm pull-left modal-dlg " id="'.$value['invoice_id'].'" data-btn-new="New" data-btn-submit="Submit"><span class="glyphicon glyphicon-tag">&nbsp</span>Print</button>');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($lab_items),
        "iTotalDisplayRecords" => count($lab_items),
          "aaData"=>$lab_items);
	 
		echo json_encode($results);
	}
	private function _load_customer_data($customer_id, &$data, $stats = FALSE)
	{
		$customer_info = '';

		if($customer_id != -1)
		{
			$customer_info = $this->Customer->get_info($customer_id);
			if(isset($customer_info->company_name))
			{
				$data['customer'] = $customer_info->company_name;
			}
			else
			{
				$data['customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
			}
			$data['date_of_birth'] = $customer_info->date_of_birth;
			$data['first_name'] = $customer_info->first_name;
			$data['last_name'] = $customer_info->last_name;
			$data['customer_email'] = $customer_info->email;
			$data['customer_address'] = $customer_info->address_1;
			if(!empty($customer_info->zip) || !empty($customer_info->city))
			{
				$data['customer_location'] = $customer_info->zip . ' ' . $customer_info->city;
			}
			else
			{
				$data['customer_location'] = '';
			}
			$data['customer_account_number'] = $customer_info->account_number;
			$data['customer_discount_percent'] = $customer_info->discount_percent;
			$package_id = $this->Customer->get_info($customer_id)->package_id;
			if($package_id != NULL)
			{
				$package_name = $this->Customer_rewards->get_name($package_id);
				$points = $this->Customer->get_info($customer_id)->points;
				$data['customer_rewards']['package_id'] = $package_id;
				$data['customer_rewards']['points'] = ($points==NULL ? 0 : $points);
				$data['customer_rewards']['package_name'] = $package_name;
			}

			if($stats)
			{
				$cust_stats = $this->Customer->get_stats($customer_id);
				$data['customer_total'] = empty($cust_stats) ? 0 : $cust_stats->total;
			}

			$data['customer_info'] = implode("\n", array(
				$data['customer'],
				$data['customer_address'],
				$data['customer_location'],
				$data['customer_account_number']
			));
		}

		return $customer_info;
	}
	
	public function set_invoice_number()
	{
		$this->sale_lib->set_invoice_number($this->input->post('sales_invoice_number'));
	}

	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_supplier_data_row($this->Supplier->get_info($row_id), $this));

		echo json_encode($data_row);
	}
	
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$suppliers = $this->Supplier->search_Account($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Supplier->get_found_rows_account($search);

		$data_rows = array();
		foreach($suppliers->result() as $supplier)
		{
			$data_rows[] = get_account_data_row($supplier, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->post('term'), FALSE));

		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	public function view($supplier_id = -1)
	{
		$info = $this->Supplier->get_info($supplier_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;

		$this->load->view("suppliers/form", $data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	public function save($supplier_id = -1)
	{
		$first_name = $this->xss_clean($this->input->post('first_name'));
		$last_name = $this->xss_clean($this->input->post('last_name'));
		$email = $this->xss_clean(strtolower($this->input->post('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->input->post('gender'),
			'email' => $email,
			'phone_number' => $this->input->post('phone_number'),
			'address_1' => $this->input->post('address_1'),
			'address_2' => $this->input->post('address_2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'comments' => $this->input->post('comments')
		);

		$supplier_data = array(
			'company_name' => $this->input->post('company_name'),
			'agency_name' => $this->input->post('agency_name'),
			'account_number' => $this->input->post('account_number') == '' ? NULL : $this->input->post('account_number')
		);

		if($this->Supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
			$supplier_data = $this->xss_clean($supplier_data);

			//New supplier
			if($supplier_id == -1)
			{
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_adding') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_data['person_id']));
			}
			else //Existing supplier
			{
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_updating') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_id));
			}
		}
		else//failure
		{
			$supplier_data = $this->xss_clean($supplier_data);

			echo json_encode(array('success' => FALSE,
							'message' => $this->lang->line('suppliers_error_adding_updating') . ' ' . 	$supplier_data['company_name'],
							'id' => -1));
		}
	}
	
	/*
	This deletes suppliers from the suppliers table
	*/
	public function delete()
	{
		$suppliers_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode(array('success' => TRUE,'message' => $this->lang->line('suppliers_successful_deleted').' '.
							count($suppliers_to_delete).' '.$this->lang->line('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE,'message' => $this->lang->line('suppliers_cannot_be_deleted')));
		}
	}
	
}
?>
