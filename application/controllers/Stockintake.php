<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Stockintake extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('stockintake');

		$this->load->library('stockintake_lib');
		$this->load->library('barcode_lib');
	}

	public function index()
	{

		$this->_reload();
	}

	public function item_search()
	{
		$suggestions = $this->Item->get_search_suggestions($this->input->get('term'), array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE);
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($this->input->get('term')));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}

	public function stock_item_search()
	{
		$search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;
		$suggestions = $this->Item->get_stock_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE);
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}

	public function select_supplier()
	{
		$supplier_id = $this->input->post('supplier');
		if ($this->Supplier->exists($supplier_id)) {
			$this->stockintake_lib->set_supplier($supplier_id);
		}

		$this->_reload();
	}

	public function change_mode()
	{
		$stock_destination = $this->input->post('stock_destination');
		$stock_source = $this->input->post('stock_source');
		$mode = $this->input->post('mode');
		$data = array();
		if ((!$stock_source || $stock_source == $this->stockintake_lib->get_stock_source()) && (!$stock_destination || $stock_destination == $this->stockintake_lib->get_stock_destination())
		) {
			$this->stockintake_lib->clear_reference();

			$this->stockintake_lib->set_mode($mode);
		} elseif ($this->Stock_location->is_allowed_location($stock_source, 'receivings')) {
			$this->stockintake_lib->set_stock_source($stock_source);
			$this->stockintake_lib->set_stock_destination($stock_destination);
		}

		//go through the items and remove any item that has 0 quantity if the mode is returns
		if ($mode == 'return') {

			$items = $this->stockintake_lib->get_cart();
			foreach ($items as $index => $item) {
				$quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
				if ($quantity <= 0) {
					//remove the item from the cart
					unset($items[$index]);
					$data['error'] = "Items with zero quantities are removed from the cart!";
				}
			}
		}
		$this->stockintake_lib->set_cart($items);


		$this->_reload($data);
	}

	public function set_comment()
	{
		$this->stockintake_lib->set_comment($this->input->post('comment'));
	}

	public function set_print_after_sale()
	{
		$this->stockintake_lib->set_print_after_sale($this->input->post('recv_print_after_sale'));
	}

	public function set_reference()
	{
		$this->stockintake_lib->set_reference($this->input->post('recv_reference'));
	}

	public function add()
	{
		$data = array();

		$mode = $this->stockintake_lib->get_mode();
		//this could be item_id (if searched manually) or barcode(if searched using barcode scanner)
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');

		//use $item_id_or_number_or_item_kit_or_receipt to get the item_id
		$item_id = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt)->item_id;
		

		$quantity = 1;
		$item_location = $this->stockintake_lib->get_stock_source();
		$receiving_quantity = 0; //this is the quantity that is ordered

		$stockno = $this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
		if ($this->stockintake_lib->get_mode() == 'return' && $stockno <= 0) {
			$data['error'] = "Item is out of stock. Returns not allowed.";
		} else {
			if (!$this->stockintake_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, 0, NULL, NULL, NULL, $receiving_quantity)) {
				$data['error'] = $this->lang->line('receivings_unable_to_add_item');
			}
		}

		$this->_reload($data);
	}

	public function edit_item($line)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		// $this->form_validation->set_rules('receiving_quantity', 'lang:items_quantity', 'required|callback_numeric');
		//$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');

		$description = $this->input->post('description');
		$serialnumber = $this->input->post('serialnumber');
		$price = parse_decimals($this->input->post('price'));
		$quantity = parse_decimals($this->input->post('quantity'));
		// $receiving_quantity = parse_decimals($this->input->post('receiving_quantity'));

		//die($price.'p '.$quantity.'p '.$receiving_quantity);
		$discount = parse_decimals($this->input->post('discount'));
		$item_location = $this->input->post('location');
		$batch_no = $this->input->post('batch_no');
		$expiry = $this->input->post('expiry');
		// $unit_price = parse_decimals($this->input->post('unit_price'));

		if ($this->stockintake_lib->get_mode() == 'return' && $quantity > 0) {
			$quantity = $quantity * -1;
		}
		if ($this->stockintake_lib->get_mode() == 'receive' && $quantity < 0) {
			$quantity = $quantity * -1;
		}


		if ($quantity == 0) {
			$this->delete_item($line);
			// $quantity = 1;
			// $data['warning'] = "Quantity cannot be zero. You can remove the item from the cart from cart.";
		}


		if ($this->form_validation->run() != FALSE) {

			$this->stockintake_lib->edit_item($line, $description, $serialnumber, $quantity, $discount, $price, $batch_no, $expiry, $receiving_quantity);
			// $this->stockintake_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $price, $batch_no, $expiry, $unit_price);
		} else {

			$data['error'] = $this->lang->line('receivings_error_editing_item');
			$this->_reload($data);
		}

		//if on returns mode and quantity is more that what's in the stock, reset it to current stock quantity
		$items = $this->stockintake_lib->get_cart();
		if (isset($items[$line])) {
			$item = &$items[$line];
			$stockno = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
			if (($item['quantity'] * -1) > $stockno && $this->stockintake_lib->get_mode() == 'return') {
				$items[$line]['quantity'] = -1 * $stockno;
			}
		}
		$this->stockintake_lib->set_cart($items);


		//$this->_reload($data);
		redirect('stockintake');
	}


	public function edit($receiving_id)
	{
		$data = array();

		$data['suppliers'] = array('' => 'No Supplier');
		foreach ($this->Supplier->get_all()->result() as $supplier) {
			$data['suppliers'][$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
		}

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee) {
			$data['employees'][$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}

		$receiving_info = $this->xss_clean($this->Receiving->get_info($receiving_id)->row_array());
		$data['selected_supplier_name'] = !empty($receiving_info['supplier_id']) ? $receiving_info['company_name'] : '';
		$data['selected_supplier_id'] = $receiving_info['supplier_id'];
		$data['receiving_info'] = $receiving_info;

		$this->load->view('receivings/form', $data);
	}
	public function edite($receiving_id)
	{
		$data = array();

		$data['suppliers'] = array('' => 'No Supplier');
		foreach ($this->Supplier->get_all()->result() as $supplier) {
			$data['suppliers'][$supplier->person_id] = $this->xss_clean($supplier->first_name . ' ' . $supplier->last_name);
		}

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee) {
			$data['employees'][$employee->person_id] = $this->xss_clean($employee->first_name . ' ' . $employee->last_name);
		}

		$receiving_info = $this->xss_clean($this->Receiving->get_info($receiving_id)->row_array());
		$data['selected_supplier_name'] = !empty($receiving_info['supplier_id']) ? $receiving_info['company_name'] : '';
		$data['selected_supplier_id'] = $receiving_info['supplier_id'];
		$data['receiving_info'] = $receiving_info;

		$this->load->view('receivings/forme', $data);
	}

	public function delete_item($item_number)
	{
		$this->stockintake_lib->delete_item($item_number);

		//$this->_reload();
		redirect('stockintake');
	}

	public function delete($receiving_id = -1, $update_inventory = TRUE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$receiving_ids = $receiving_id == -1 ? $this->input->post('ids') : array($receiving_id);

		if ($this->Receiving->delete_list($receiving_ids, $employee_id, $update_inventory)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivings_successfully_deleted') . ' ' .
				count($receiving_ids) . ' ' . $this->lang->line('receivings_one_or_multiple'), 'ids' => $receiving_ids));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivings_cannot_be_deleted')));
		}
	}

	public function remove_supplier()
	{
		$this->stockintake_lib->clear_reference();
		$this->stockintake_lib->remove_supplier();

		$this->_reload();
	}
	
	public function scheck(){
		$existing_item = $this->Receiving->stock_item_exists(4681, 2);
		return print_r($existing_item->item_id);
	}
    
    public function complete()
	{
		$data = array();

		$data['cart'] = $this->stockintake_lib->get_cart();
		$data['total'] = $this->stockintake_lib->get_total();
		// $data['receipt_title'] = $this->lang->line('receivings_receipt');
		$data['receipt_title'] = 'STOCK  TAKING';
		$data['transaction_time'] = date("Y-m-d h:i A");
		$data['transaction_date'] = date("Y-m-d h:i A");

		$data['mode'] = $this->stockintake_lib->get_mode();
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'STOCK RETURN REPORT';
		}
		// $data['comment'] = $this->stockintake_lib->get_comment();
		// $data['reference'] = $this->stockintake_lib->get_reference();
		// $data['payment_type'] = $this->input->post('payment_type');
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['stock_location'] = $this->stockintake_lib->get_stock_source();
		if ($this->input->post('amount_tendered') != NULL) {
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$branch_id = $employee_info->branch_id;
		$branch_info = $this->Employee->get_branchinfo($branch_id);


		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		// $supplier_info = '';
		// $supplier_id = $this->stockintake_lib->get_supplier();
		// if ($supplier_id != -1) {
		// 	$supplier_info = $this->Supplier->get_info($supplier_id);
		// 	$data['supplier'] = $supplier_info->company_name;
		// 	$data['first_name'] = $supplier_info->first_name;
		// 	$data['last_name'] = $supplier_info->last_name;
		// 	$data['supplier_email'] = $supplier_info->email;
		// 	$data['supplier_address'] = $supplier_info->address_1;
		// 	if (!empty($supplier_info->zip) or !empty($supplier_info->city)) {
		// 		$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
		// 	} else {
		// 		$data['supplier_location'] = '';
		// 	}
		// }

		//SAVE receiving to database
		$receive_status = '0';
		$data['stock_id'] = 'STCK ' . $this->Receiving->save_stock_taking($data['cart'], $employee_id, $data['stock_location']);
		
		$data = $this->xss_clean($data);

		if ($data['stock_id'] == 'STCK -1') {
			$data['error_message'] = 'Stock Taking Failed to save';
		} else {
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['stock_id']);
		}

		$data['print_after_sale'] = $this->stockintake_lib->is_print_after_sale();

		// $this->load->view("stock_intake/receipt", $data);

		$this->stockintake_lib->clear_all();

		$data['success'] = 'Stock Taking Saved';
		$this->_reload($data);
	}

	// public function complete()
	// {
	// 	$data = array();

	// 	$data['cart'] = $this->stockintake_lib->get_cart();
	// 	$data['total'] = $this->stockintake_lib->get_total();
	// 	$data['receipt_title'] = $this->lang->line('receivings_receipt');
	// 	$data['transaction_time'] = date("Y-m-d h:i A");
	// 	$data['transaction_date'] = date("Y-m-d h:i A");

	// 	$data['mode'] = $this->stockintake_lib->get_mode();
	// 	if ($data['mode'] == 'return') {
	// 		$data['receipt_title'] = 'STOCK RETURN REPORT';
	// 	}
	// 	$data['comment'] = $this->stockintake_lib->get_comment();
	// 	$data['reference'] = $this->stockintake_lib->get_reference();
	// 	$data['payment_type'] = $this->input->post('payment_type');
	// 	$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
	// 	$data['stock_location'] = $this->stockintake_lib->get_stock_source();
	// 	if ($this->input->post('amount_tendered') != NULL) {
	// 		$data['amount_tendered'] = $this->input->post('amount_tendered');
	// 		$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
	// 	}

	// 	$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
	// 	$employee_info = $this->Employee->get_info($employee_id);
	// 	$branch_id = $employee_info->branch_id;
	// 	$branch_info = $this->Employee->get_branchinfo($branch_id);
	// 	$data['branch_address'] = $branch_info->location_address;
	// 	$data['branch_number'] = $branch_info->location_number;
	// 	$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

	// 	$supplier_info = '';
	// 	$supplier_id = $this->stockintake_lib->get_supplier();
	// 	if ($supplier_id != -1) {
	// 		$supplier_info = $this->Supplier->get_info($supplier_id);
	// 		$data['supplier'] = $supplier_info->company_name;
	// 		$data['first_name'] = $supplier_info->first_name;
	// 		$data['last_name'] = $supplier_info->last_name;
	// 		$data['supplier_email'] = $supplier_info->email;
	// 		$data['supplier_address'] = $supplier_info->address_1;
	// 		if (!empty($supplier_info->zip) or !empty($supplier_info->city)) {
	// 			$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
	// 		} else {
	// 			$data['supplier_location'] = '';
	// 		}
	// 	}

	// 	//SAVE receiving to database
	// 	$receive_status = '0';
	// 	$data['receiving_id'] = 'RECV ' . $this->Receiving->save($receive_status, $data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);

	// 	$data = $this->xss_clean($data);

	// 	if ($data['receiving_id'] == 'RECV -1') {
	// 		$data['error_message'] = $this->lang->line('receivings_transaction_failed');
	// 	} else {
	// 		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
	// 	}

	// 	$data['print_after_sale'] = $this->stockintake_lib->is_print_after_sale();

	// 	$this->load->view("receivings/receipt", $data);

	// 	$this->stockintake_lib->clear_all();
	// }

	public function requisition_complete()
	{
		if ($this->stockintake_lib->get_stock_source() != $this->stockintake_lib->get_stock_destination()) {
			foreach ($this->stockintake_lib->get_cart() as $item) {
				$this->stockintake_lib->delete_item($item['line']);
				$this->stockintake_lib->add_item($item['item_id'], $item['quantity'], $this->stockintake_lib->get_stock_destination(), $item['unit_price']);
				$this->stockintake_lib->add_item($item['item_id'], -$item['quantity'], $this->stockintake_lib->get_stock_source(), $item['unit_price']);
			}

			$this->complete();
		} else {
			$data['error'] = $this->lang->line('receivings_error_requisition');

			$this->_reload($data);
		}
	}

	public function receipt($receiving_id)
	{
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		$this->stockintake_lib->copy_entire_receiving($receiving_id);
		$data['cart'] = $this->stockintake_lib->get_cart();
		$data['total'] = $this->stockintake_lib->get_total();
		$data['mode'] = $this->stockintake_lib->get_mode();
		$data['receipt_title'] = $this->lang->line('receivings_receipt');
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'STOCK RETURN REPORT';
		}
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['payment_type'] = $receiving_info['payment_type'];
		$data['reference'] = $this->stockintake_lib->get_reference();
		$data['receiving_id'] = 'RECV ' . $receiving_id;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		$employee_info = $this->Employee->get_info($receiving_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->stockintake_lib->get_supplier();
		if ($supplier_id != -1) {
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if (!empty($supplier_info->zip) or !empty($supplier_info->city)) {
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			} else {
				$data['supplier_location'] = '';
			}
		}

		$data['print_after_sale'] = FALSE;

		$data = $this->xss_clean($data);

		$this->load->view("receivings/receipt", $data);

		$this->stockintake_lib->clear_all();
    }
    
    public function new(){
		//here
		
		$stock = $this->Receiving->get_inprogress_stock_taking();
		$stock_count = count($stock);

		if($stock_count > 0){
			$data = array('message' => 'You can not start a new stocking until the current one is done');
			$this->load->view("stock_intake/progress", $data);
			return;
		}

        $data = array();
        $this->load->view("stock_intake/new", $data);
    }

	private function _reload($data = array())
	{

		if($this->Receiving->get_inprogress_stock_taking()->stock_id <= 0){
			$data = array('message' => 'There is no Stock Taking in progress');
			$this->load->view("stock_intake/progress", $data);
			return;
		}

		$data['cart'] = $this->stockintake_lib->get_cart();
		// echo "<pre>";
		// print_r($data['cart']);
		// echo "</pre>";
		// exit;
		$data['modes'] = array('receive' => $this->lang->line('receivings_receiving'), 'return' => $this->lang->line('receivings_return'));
		$data['mode'] = $this->stockintake_lib->get_mode();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('receivings');
		$data['show_stock_locations'] = count($data['stock_locations']) > 1;
		// if ($data['show_stock_locations']) {
		// 	$data['modes']['requisition'] = $this->lang->line('receivings_requisition');
		// 	$data['stock_source'] = $this->stockintake_lib->get_stock_source();
		// 	$data['stock_destination'] = $this->stockintake_lib->get_stock_destination();
		// }

		$data['total'] = $this->stockintake_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->stockintake_lib->get_comment();
		$data['reference'] = $this->stockintake_lib->get_reference();
		$data['payment_options'] = $this->Receiving->get_payment_options();

		$supplier_id = $this->stockintake_lib->get_supplier();
		$supplier_info = '';
		if ($supplier_id != -1) {
			$supplier_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if (!empty($supplier_info->zip) or !empty($supplier_info->city)) {
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			} else {
				$data['supplier_location'] = '';
			}
		}

		$data['print_after_sale'] = 1; //$this->stockintake_lib->is_print_after_sale();
		$curr_stock = $this->Receiving->get_inprogress_stock_taking();
		$data['title'] = $curr_stock->title;
		$data['description'] = $curr_stock->description;
		$data['stock_status'] = $curr_stock->status;

		$data = $this->xss_clean($data);

		$this->load->view("stock_intake/receiving", $data);
    }
    
    public function create_stock_intake($stock_id = -1)
	{

		$stock = $this->Receiving->get_inprogress_stock_taking();
		$stock_count = count($stock);

		if(!is_null($stock)){
			echo json_encode(array('error' => TRUE, 'message' => 'Stock Intake already in progress.', 'id' => $stock_id));
			return;
		}

		// if ($new_stock_id = $this->Receiving->create_stock_intake($stock_save_data)) {
		// 	echo json_encode(array('success' => TRUE, 'message' => 'Stock Intake successfully started.', 'id' => $new_stock_id));
		// } 

        $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $stock_save_data = array(
			'title' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'receiving_time' => date('Y-m-d H:i:s'),
            'employee_id' => $employee_id
		);

		$new_stock_id = $this->Receiving->create_stock_intake($stock_save_data);

		if ($new_stock_id > 0) {
			// redirect('stockintake/history_view/'. $new_stock_id, 'refresh');
			echo json_encode(array('success' => TRUE, 'message' => 'Stock Intake successfully started.', 'id' => $new_stock_id));
		} else{
			echo json_encode(array('error' => TRUE, 'message' => 'Stock Intake not initiated', 'id' => $new_stock_id));
		}
	}

	public function end_stock_intake($stock_id = -1)
	{

		if ($stock_id <= 0) {
			echo json_encode(array('error' => TRUE, 'message' => 'Stock intake action failed', 'id' => $stock_id));
			// return;
		redirect('stockintake/history');
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$status = 'done';
		
        $stock_save_data = array(
			'status' => $status
		);

		if ($this->Receiving->update_stock_intake($stock_save_data, $stock_id)) {
			echo json_encode(array('success' => TRUE, 'message' => 'Stock Intake successfully ended.', 'id' => $stock_id));
		}

		redirect('stockintake/history_view/' . $stock_id);
	}

	public function save($receiving_id = -1)
	{
		$newdate = $this->input->post('date');

		//$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);

		if ($receiving_id > 0) {
			$receiving_data = array(
				'supplier_id' => $this->input->post('supplier_id') ? $this->input->post('supplier_id') : NULL,
				'comment' => $this->input->post('comment'),
				'reference' => $this->input->post('reference') != '' ? $this->input->post('reference') : NULL
			);
		} else {
			$receiving_data = array(
				'receiving_time' => date('Y-m-d H:i:s', $newdate),
				'supplier_id' => $this->input->post('supplier_id') ? $this->input->post('supplier_id') : NULL,
				'employee_id' => $this->input->post('employee_id'),
				'comment' => $this->input->post('comment'),
				'reference' => $this->input->post('reference') != '' ? $this->input->post('reference') : NULL
			);
		}


		if ($this->Receiving->update($receiving_data, $receiving_id)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivings_successfully_updated'), 'id' => $receiving_id));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivings_unsuccessfully_updated'), 'id' => $receiving_id));
		}
	}
	public function savee($receiving_id = -1)
	{
		$newdate = $this->input->post('date');

		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);

		$receiving_data = array(
			'expiry' => $date_formatter->format('Y-m-d H:i:s')
		);

		if ($this->Receiving->updatee($receiving_data, $receiving_id)) {
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivings_successfully_updated'), 'id' => $receiving_id));
		} else {
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivings_unsuccessfully_updated'), 'id' => $receiving_id));
		}
	}


	public function cancel_receiving()
	{
		$this->stockintake_lib->clear_all();

		$this->_reload();
	}

	public function history()
	{
		$data = [];
		$data['table_headers'] = $this->xss_clean(get_stock_intake_history_headers());
		// $this->load->view("lpo/history", $data);
		$this->load->view("stock_intake/history", $data);
	}
	public function transfer_history()
	{



		$data = [];
		$data['table_headers'] = $this->xss_clean(get_transfer_history_headers());
		$this->load->view("receivings/transfer_history", $data);
	}

	public function history_data()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$employee_id = "all";
		$employee = $this->Employee->get_logged_in_employee_info();
		if ($employee->role != 3) {
			///only admin can see all the transfers

			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		}

		$filters = array(
			'start_date' 	=> $this->input->get('start_date'),
			'end_date' 		=> $this->input->get('end_date'),
			'employee_id' => $employee_id
		);

		//$total		= $this->Receiving->get_all_receivings($search, $filters);
		$data_all		= $this->Receiving->get_all_stock_intakes($search, 0, $offset, $sort, $order, $filters)->result();
		//$total		= $this->Receiving->get_all_receivings($search, 0, $offset, $sort, $order, $filters)->num_rows();
		$data =  array_slice($data_all, $offset, $limit); //limit is usually more than 0
		$data_rows	= array();
		foreach ($data as $d) {
			$data_rows[] = $this->xss_clean(get_stock_intake_item_data_row($d, $d->stock_id, $this));
		}
		echo json_encode(array('total' => count($data_all), 'rows' => $data_rows));
	}
	public function transfer_history_data()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$employee_id = "all";
		$employee = $this->Employee->get_logged_in_employee_info();
		if ($employee->role != 3) {
			///only admin can see all the transfers
			$employee_id = $employee->person_id;
		}
		$filters = array(
			'start_date' 	=> $this->input->get('start_date'),
			'end_date' 		=> $this->input->get('end_date'),
			'employee_id' => $employee_id
		);


		//$total		= $this->Receiving->get_all_receivings($search, $filters);
		$total		= $this->Receiving->get_all_transfers($search, 0, $offset, $sort, $order, $filters)->num_rows();
		$data		= $this->Receiving->get_all_transfers($search, $limit, $offset, $sort, $order, $filters);

		$data_rows	= array();
		foreach ($data->result() as $d) {
			$data_rows[] = $this->xss_clean(get_transfer_item_data_row($d, $d->transfer_id, $this));
		}
		echo json_encode(array('total' => $total, 'rows' => $data_rows));
	}

	public function history_view($id = -1)
	{
		if ($id == -1) {
			$this->history();
		}
		$receiving = $this->Receiving->get_stock_intake_by_stock_id($id)->result();
		$items = $this->Receiving->get_stock_intake_items_data_by_stock_id($id)->result();

		$data = array(
			'meta'	=> $receiving[0],
			'items'	=> $items
		);
		$this->load->view("stock_intake/history_view", $data);
	}

	public function process_lpo($id = -1)
	{
		$this->stockintake_lib->clear_all();
		//If id is not valide or does not exist, go back the inventory history table
		if ($id == -1 || !$this->Receiving->exists($id)) {
			$this->history();
		}

		$receiving = $this->Receiving->get_lpo_by_lpo_id($id)->result();
		$items = $this->Receiving->get_lpo_items_data_by_lpo_id($id)->result();

		// $data = array(
		// 	'meta'	=> $receiving[0],
		// 	'items'	=> $items
		// );

		// $data = array();

		foreach ($items as $index => $item) {
			$quantity = $this->Item_quantity->get_item_quantity($item->item_id, $item->item_location)->quantity;
			// return print_r($quantity);
		// if ($quantity <= 0) {
				//remove the item from the cart
				// $data['error'] = "Items with zero quantities are removed from the cart!";
			// $item_location = $this->stockintake_lib->get_stock_source();
			$item_location = 2;
			$this->stockintake_lib->add_item($item->item_id, $quantity, $item_location, 0, NULL, NULL, NULL, $item->quantity_ordered);
			// $this->stockintake_lib->add_item($item->item_id, $quantity, $item_location, 0, NULL, NULL, NULL, $item->quantity_purchased);
			// }
		}

		//set the supplier
		if ($this->Supplier->exists($receiving[0]->supplier_id)) {
			$this->stockintake_lib->set_supplier($receiving[0]->supplier_id);
		}

		// $mode = $this->stockintake_lib->get_mode();
		// //this could be item_id (if searched manually) or barcode(if searched using barcode scanner)
		// $item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');

		// //use $item_id_or_number_or_item_kit_or_receipt to get the item_id
		// $item_id = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt)->item_id;
		

		// $quantity = ($mode == 'receive') ? 1 : -1;
		// $item_location = $this->stockintake_lib->get_stock_source();
		// $receiving_quantity = 0; //this is the quantity that is ordered

		// $stockno = $this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
		// if ($this->stockintake_lib->get_mode() == 'return' && $stockno <= 0) {
		// 	$data['error'] = "Item is out of stock. Returns not allowed.";
		// } else {
		// 	if (!$this->stockintake_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, 0, NULL, NULL, NULL, $receiving_quantity)) {
		// 		$data['error'] = $this->lang->line('receivings_unable_to_add_item');
		// 	}
		// }

		$this->_reload($data);

		// $mode = '';
		// if (count($items) > 0 && (int) $items[0]->quantity_purchased < 0) {
		// 	$mode = "Return";
		// } else {
		// 	$mode = "Receiving";
		// }
		// $data['mode'] = $mode;
		// $data['barcode'] = $this->barcode_lib->generate_receipt_barcode('LPO ' . $id);
		// $data['print_after_sale'] = $this->lpo_lib->is_print_after_sale();
		// $data['receipt_title'] = $this->lang->line('lpo_receipt');
		// $data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		// $data['date'] = date("Y-m-d h:i A", strtotime($data['meta']->receiving_time));

		// $this->load->view("lpo/history_print", $data);
	}

	public function process_stock_intake($stockId = -1)
	{
		if($stockId <= 0){
			$data = array('message' => 'Invalid Stock Taking');
			$this->load->view("stock_intake/progress", $data);
			return;
		}

		if($this->Receiving->check_if_stock_is_received($stockId)){
			$data = array('message' => 'This Stock taking has already been received');
			$this->load->view("stock_intake/progress", $data);
			return;
		}

		if(!$this->Receiving->check_if_stock_is_done($stockId)){
			$data = array('message' => 'This Stock taking is current in Progress');
			$this->load->view("stock_intake/progress", $data);
			return;
		}

		$data = array();

		///details to get from stock taking
		// $stockId = $this->Receiving->get_inprogress_stock_taking();
		$items_on_stock_taking = $this->Receiving->get_stock_intake_by_stock_id($stockId)->result();
		$items = $this->Receiving->get_stock_intake_items_data_by_stock_id($stockId)->result_array();

		//fetch
		$supplier_id = 1;
		$employee_id = $items_on_stock_taking[0]->employee_id;
		$comment = '';
		$reference = '';
		$payment_type = '';
		
		$stock_location = $items[0]->location_id;
		
		//receipt data

		// $data['total'] = $this->receiving_lib->get_total();
		$data['total'] = 3000;
		$data['receipt_title'] = $this->lang->line('receivings_receipt');
		$data['transaction_time'] = date("Y-m-d h:i A");
		$data['transaction_date'] = date("Y-m-d h:i A");

		$data['mode'] = 'receive';

		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['stock_location'] = $stock_location;

		
		// if ($this->input->post('amount_tendered') != NULL) {
		// 	$data['amount_tendered'] = $this->input->post('amount_tendered');
		// 	$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		// }

		// $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		
		$branch_id = $employee_info->branch_id;
		$branch_info = $this->Employee->get_branchinfo($branch_id);
		
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		//supplier
		$supplier_info = '';
		// $supplier_id = $this->receiving_lib->get_supplier();
		// if ($supplier_id != -1) {
		// 	$supplier_info = $this->Supplier->get_info($supplier_id);
		// 	$data['supplier'] = $supplier_info->company_name;
		// 	$data['first_name'] = $supplier_info->first_name;
		// 	$data['last_name'] = $supplier_info->last_name;
		// 	$data['supplier_email'] = $supplier_info->email;
		// 	$data['supplier_address'] = $supplier_info->address_1;
		// 	if (!empty($supplier_info->zip) or !empty($supplier_info->city)) {
		// 		$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
		// 	} else {
		// 		$data['supplier_location'] = '';
		// 	}
		// }

		// return print_r($supplier_info);
		// die();

		//SAVE receiving to database
		$receive_status = '0';
		// $data['receiving_id'] = 'RECV ' . $this->Receiving->save($receive_status, $data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);
		$data['receiving_id'] = 'RECV ' . $this->Receiving->save_receiving_from_stock($receive_status, $items, $supplier_id, $employee_id, $comment, $reference, $payment_type, $stockId);

		$data['meta'] = $items_on_stock_taking[0];
		$data['cart'] =  $items;
		
		// return print_r($data['cart']);
		// $data = $this->xss_clean($data);

		if ($data['receiving_id'] == 'RECV -1') {
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		} else {
			
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		}
		
		// $data['cart'] = json_decode(json_encode($items), true);

		// return var_dump($data['cart'][2]);
		// die();

		// $data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();
		$data['print_after_sale'] = 0;
		// $mode = '';
		// if (count($items) > 0 && (int) $items[0]->quantity_purchased < 0) {
		// 	$mode = "Return";
		// } else {
		// 	$mode = "Receiving";
		// }
		// $data['mode'] = $mode;
		// $data['barcode'] = $this->barcode_lib->generate_receipt_barcode('RECV ' . $id);
		// $data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();
		// $data['receipt_title'] = $this->lang->line('receivings_receipt');
		// $data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		// $data['date'] = date("Y-m-d h:i A", strtotime($data['meta']->receiving_time));


		// $this->load->view("receivings/history_print", $data);

		// echo "<pre>";
		// 	echo var_dump($data['cart'][0]);
		// echo "</pre>";

		// return;

		$this->load->view("receivings/receipt", $data);

		// $this->receiving_lib->clear_all();
	}


	public function reprint($id = -1)
	{
		//If id is not valide or does not exist, go back the inventory history table
		if ($id == -1 || !$this->Receiving->stock_exists($id)) {
			$this->history();
		}

		$receiving = $this->Receiving->get_stock_intake_by_stock_id($id)->result();
		$items = $this->Receiving->get_stock_intake_items_data_by_stock_id($id)->result();

		$data = array(
			'meta'	=> $receiving[0],
			'items'	=> $items
		);
		
		$data['mode'] = 'Receiving';
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode('STCK ' . $id);
		$data['print_after_sale'] = $this->stockintake_lib->is_print_after_sale();
		$data['receipt_title'] = 'STOCK INTAKE REPORT';
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['date'] = date("Y-m-d h:i A", strtotime($data['meta']->receiving_time));

		$this->load->view("stock_intake/history_print", $data);
	}
	public function transfer_reprint($id = -1)
	{
		//If id is not valide or does not exist, go back the inventory history table
		if ($id == -1 || !$this->Receiving->transfer_exists($id)) {
			$this->transfer_history();
		}

		$transfer = $this->Receiving->get_transfer_by_transfer_id($id)->result();
		$items = $this->Receiving->get_transfer_items_data_by_transfer_id($id)->result();

		$data = array(
			'meta'	=> $transfer[0], //Note that item exist here
			'items'	=> $items
		);

		$mode = $data['meta']->transfer_type;
		//show the receipt

		$employee_info = $this->Employee->get_info($data['meta']->employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;
		$data['mode'] = $mode;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode('PUSH ' . $id);
		$data['print_after_sale'] = $this->stockintake_lib->is_print_after_sale();
		$data['receipt_title'] = $this->lang->line('transfer_receipt');
		$data['date'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($data['meta']->transfer_time));
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['from_branch'] = $this->CI->Stock_location->get_location_name($data['meta']->request_from_branch_id);
		$data['to_branch'] = $this->CI->Stock_location->get_location_name($data['meta']->request_to_branch_id);
		$data['transfer_id'] = $id;
		$data['print_after_sale'] = 0;
		$this->load->view("receivings/transfer_history_print", $data);
	}

	public function suspend()
	{
		$cart = $this->stockintake_lib->get_cart();
		$payment_type = 'Cash';
		$employee = $this->Employee->get_logged_in_employee_info();
		$employee_id = $employee->person_id;
		$comment = $this->stockintake_lib->get_comment();
		$reference = $this->stockintake_lib->get_reference();
		$supplier_id = $this->stockintake_lib->get_supplier();

		return print_r($supplier_id);

		// $invoice_number = $this->_is_custom_invoice_number($customer_info) ? $this->sale_lib->get_invoice_number() : NULL;

		//get_total payment and save it in the sales tale
		// $total = 0;
		// foreach ($payments as $id => $value) {
		// 	$total = floatval($total) + floatval($value['payment_amount']);
		// }
		$item_location = $employee->branch_id;
		$receive_status = '1';

		$data = array();
		$sales_taxes = array();

		//don't suspend if on return mode
		$mode = $this->stockintake_lib->get_mode();
		if ($mode != 'receive') {
			$data['error'] = "Sorry, You cannot suspend receivings on this mode.";
		} else {

			// $save_resp = $this->Receiving->save($receive_status, $cart, $customer_id, $employee_id, $comment, $invoice_number, $quote_number, $payment_type, $dinner_table, $sales_taxes, $total, $item_location);
			$save_resp = $this->Receiving->save($receive_status, $cart, $supplier_id, $employee_id, $comment, $reference, $payment_type, $item_location);
			if ($save_resp == '-1') {
				$data['error'] = $this->lang->line('sales_unsuccessfully_suspended_sale');
			} else {
				$data['success'] = $this->lang->line('sales_successfully_suspended_sale') . ' - Suspended Sale ID = ' . $save_resp;
			}
			$this->stockintake_lib->clear_all();
		}


		// $data['current_suspended_sale_id'] = $save_resp; 
		//Initially I wanted to return the ID as a data param but displaying it on the notification is better.
		$this->_reload($data);
	}

	public function suspended()
	{
		$customer_id = $this->stockintake_lib->get_customer();
		$data = array();
		$data['suspended_receivings'] = $this->xss_clean($this->Receiving->get_all_suspended($customer_id));
		$data['dinner_table_enable'] = $this->config->item('dinner_table_enable');
		$this->load->view('receivings/suspended', $data);
	}
}
