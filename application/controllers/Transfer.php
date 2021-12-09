<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Transfer extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('lpo');
		// parent::__construct('receivings');

        $this->load->library('transfer_lib');
        $this->load->library('item_lib');
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
			$this->transfer_lib->set_supplier($supplier_id);
		}

		$this->_reload();
    }
    
    public function request()
	{
		$data = array();

		// $mode = $this->transfer_lib->get_mode();

		// return print_r($mode);
		$mode = 'receive';
		//this could be item_id (if searched manually) or barcode(if searched using barcode scanner)
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');

		//use $item_id_or_number_or_item_kit_or_receipt to get the item_id
		$item_id = $this->CI->Item->get_info_by_id_or_number($item_id_or_number_or_item_kit_or_receipt)->item_id;

		$quantity = ($mode == 'receive') ? 1 : -1;
		$item_location = $this->transfer_lib->get_stock_source();
		$receiving_quantity = $this->input->post('receiving_quantity'); //this is the quantity that is ordered

		$stockno = $this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity;
		if ($this->transfer_lib->get_mode() == 'return' && $stockno <= 0) {
			$data['error'] = "Item is out of stock. Returns not allowed.";
		} else {

			if (!$this->transfer_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $item_location, $receiving_quantity)) {
				$data['error'] = $this->lang->line('receivings_unable_to_add_item');
			}
		}

		$this->_reload($data);
    }

    private function _reload($data = array())
	{
		$data['cart'] = $this->transfer_lib->get_cart();
		// echo "<pre>";
		// print_r($data['cart']);
		// echo "</pre>";
		// exit;
		$data['modes'] = array('receive' => $this->lang->line('receivings_receiving'), 'return' => $this->lang->line('receivings_return'));
		$data['mode'] = $this->transfer_lib->get_mode();
		// $data['mode'] = 'receive';
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('receivings');
		$data['show_stock_locations'] = count($data['stock_locations']) > 1;
		if ($data['show_stock_locations']) {
			$data['modes']['requisition'] = $this->lang->line('receivings_requisition');
			$data['stock_source'] = $this->transfer_lib->get_stock_source();
			$data['stock_destination'] = $this->transfer_lib->get_stock_destination();
		}

		$data['total'] = $this->transfer_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->transfer_lib->get_comment();
		$data['reference'] = $this->transfer_lib->get_reference();
		$data['payment_options'] = $this->Receiving->get_payment_options();

        $locator = array('' => "Select Branch");

        

        $item_location = $this->item_lib->get_item_location();
        
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_loc($item_location)->result_array() as $row) {
			$locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
        }
        
		$data['locator'] = $locator;

		$data['selected_locator'] = $selected_locator;

		// $stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		// foreach ($stock_locations as $location) {
		// 	$location = $this->xss_clean($location);

		// 	$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
		// 	$quantity = ($item_id == -1) ? 0 : $quantity;
		// 	$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
		// 	$data['stock_locations'] = $location_array;
        // }

		$data['print_after_sale'] = 1; //$this->transfer_lib->is_print_after_sale();

        $data = $this->xss_clean($data);
        
		$this->load->view("transfer/receiving", $data);
    }
    


    ////////// STOP HERE !!
    

	public function change_mode()
	{
		$stock_destination = $this->input->post('stock_destination');
		$stock_source = $this->input->post('stock_source');
		$mode = $this->input->post('mode');
		$data = array();
		if ((!$stock_source || $stock_source == $this->transfer_lib->get_stock_source()) && (!$stock_destination || $stock_destination == $this->transfer_lib->get_stock_destination())
		) {
			$this->transfer_lib->clear_reference();

			$this->transfer_lib->set_mode($mode);
		} elseif ($this->Stock_location->is_allowed_location($stock_source, 'receivings')) {
			$this->transfer_lib->set_stock_source($stock_source);
			$this->transfer_lib->set_stock_destination($stock_destination);
		}

		//go through the items and remove any item that has 0 quantity if the mode is returns
		if ($mode == 'return') {

			$items = $this->transfer_lib->get_cart();
			foreach ($items as $index => $item) {
				$quantity = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
				if ($quantity <= 0) {
					//remove the item from the cart
					unset($items[$index]);
					$data['error'] = "Items with zero quantities are removed from the cart!";
				}
			}
		}
		$this->transfer_lib->set_cart($items);


		$this->_reload($data);
	}

	public function set_comment()
	{
		$this->transfer_lib->set_comment($this->input->post('comment'));
	}

	public function set_print_after_sale()
	{
		$this->transfer_lib->set_print_after_sale($this->input->post('recv_print_after_sale'));
	}

	public function set_reference()
	{
		$this->transfer_lib->set_reference($this->input->post('recv_reference'));
	}

	public function edit_item($line)
	{
		$data = array();

		// $this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');
		// $this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		$this->form_validation->set_rules('receiving_quantity', 'lang:items_quantity', 'required|callback_numeric');
		//$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');

		// $description = $this->input->post('description');
		// $serialnumber = $this->input->post('serialnumber');
		// $price = parse_decimals($this->input->post('price'));
		// $quantity = parse_decimals($this->input->post('quantity'));
		$receiving_quantity = parse_decimals($this->input->post('receiving_quantity'));

		//die($price.'p '.$quantity.'p '.$receiving_quantity);
		// $discount = parse_decimals($this->input->post('discount'));
		$item_location = $this->input->post('location');
		// $batch_no = $this->input->post('batch_no');
		// $expiry = $this->input->post('expiry');
		// $unit_price = parse_decimals($this->input->post('unit_price'));

		if ($this->transfer_lib->get_mode() == 'return' && $quantity > 0) {
			$quantity = $quantity * -1;
		}
		if ($this->transfer_lib->get_mode() == 'receive' && $quantity < 0) {
			$quantity = $quantity * -1;
		}


		// if ($quantity == 0) {
		// 	$this->delete_item($line);
		// 	// $quantity = 1;
		// 	// $data['warning'] = "Quantity cannot be zero. You can remove the item from the cart from cart.";
		// }


		if ($this->form_validation->run() != FALSE) {

			$this->transfer_lib->edit_item($line, $receiving_quantity);
			// $this->transfer_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $price, $batch_no, $expiry, $unit_price);
		} else {

			$data['error'] = $this->lang->line('receivings_error_editing_item');
			$this->_reload($data);
		}

		//if on returns mode and quantity is more that what's in the stock, reset it to current stock quantity
		$items = $this->transfer_lib->get_cart();
		if (isset($items[$line])) {
			$item = &$items[$line];
			$stockno = $this->Item_quantity->get_item_quantity($item['item_id'], $item['item_location'])->quantity;
			if (($item['quantity'] * -1) > $stockno && $this->transfer_lib->get_mode() == 'return') {
				$items[$line]['quantity'] = -1 * $stockno;
			}
		}
		$this->transfer_lib->set_cart($items);


		//$this->_reload($data);
		redirect('lpo');
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
		$this->transfer_lib->delete_item($item_number);

		//$this->_reload();
		redirect('lpo');
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
		$this->transfer_lib->clear_reference();
		$this->transfer_lib->remove_supplier();

		$this->_reload();
	}

	public function complete()
	{
		$data = array();

		$data['cart'] = $this->transfer_lib->get_cart();
		$data['total'] = $this->transfer_lib->get_total();
		$data['receipt_title'] = $this->lang->line('lpo_receipt');
		$data['transaction_time'] = date("Y-m-d h:i A");
		$data['transaction_date'] = date("Y-m-d h:i A");

		$data['mode'] = $this->transfer_lib->get_mode();
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'LOCAL PURCHASE ORDER';
		}
		$data['comment'] = $this->transfer_lib->get_comment();
		$data['reference'] = $this->transfer_lib->get_reference();
		$data['payment_type'] = $this->input->post('payment_type');
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['stock_location'] = $this->transfer_lib->get_stock_source();
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

		$supplier_info = '';
		$supplier_id = $this->transfer_lib->get_supplier();
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

		//SAVE receiving to database
		// $data['lpo_id'] = 'LPO ' . $this->Lpo->save($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);
		$data['lpo_id'] = 'LPO ' . $this->Receiving->save_lpo($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['stock_location']);

		$data = $this->xss_clean($data);

		if ($data['lpo_id'] == 'LPO -1') {
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		} else {
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['lpo_id']);
		}

		$data['print_after_sale'] = $this->transfer_lib->is_print_after_sale();

		$this->load->view("lpo/receipt", $data);

		$this->transfer_lib->clear_all();
	}

	public function requisition_complete()
	{
		if ($this->transfer_lib->get_stock_source() != $this->transfer_lib->get_stock_destination()) {
			foreach ($this->transfer_lib->get_cart() as $item) {
				$this->transfer_lib->delete_item($item['line']);
				$this->transfer_lib->add_item($item['item_id'], $item['quantity'], $this->transfer_lib->get_stock_destination(), $item['unit_price']);
				$this->transfer_lib->add_item($item['item_id'], -$item['quantity'], $this->transfer_lib->get_stock_source(), $item['unit_price']);
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
		$this->transfer_lib->copy_entire_receiving($receiving_id);
		$data['cart'] = $this->transfer_lib->get_cart();
		$data['total'] = $this->transfer_lib->get_total();
		$data['mode'] = $this->transfer_lib->get_mode();
		$data['receipt_title'] = $this->lang->line('lpo_receipt');
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'STOCK RETURN REPORT';
		}
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['payment_type'] = $receiving_info['payment_type'];
		$data['reference'] = $this->transfer_lib->get_reference();
		$data['receiving_id'] = 'RECV ' . $receiving_id;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		$employee_info = $this->Employee->get_info($receiving_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->transfer_lib->get_supplier();
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

		$this->transfer_lib->clear_all();
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
		$this->transfer_lib->clear_all();

		$this->_reload();
	}

	public function history()
	{
		$data = [];
		$data['table_headers'] = $this->xss_clean(get_lpo_history_headers());
		$this->load->view("lpo/history", $data);
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
		$data_all		= $this->Receiving->get_all_lpos($search, 0, $offset, $sort, $order, $filters)->result();
		//$total		= $this->Receiving->get_all_receivings($search, 0, $offset, $sort, $order, $filters)->num_rows();
		$data =  array_slice($data_all, $offset, $limit); //limit is usually more than 0
		$data_rows	= array();
		foreach ($data as $d) {
			$data_rows[] = $this->xss_clean(get_lpo_item_data_row($d, $d->lpo_id, $this));
		}
		echo json_encode(array('total' => count($data_all), 'rows' => $data_rows));
	}
	private function get_branch_id(){
        $employee = $this->Employee->get_logged_in_employee_info();
//        echo json_encode($employee);
        return $this->Employee->get_branchinfo($employee->branch_id)->brid;
    }
	public function get_incoming(){
        $this->load->library('External_calls');
        $branch_name = $this->get_branch_id();
//        $branch_name = "Tonia Pharmacy Maitama Warehouse";
        $branch_name = str_replace(' ','_',$branch_name);
//        echo json_encode($branch_name);
        $url = ERD_BASE_URL.'/transfer/'.$branch_name.'/get_incoming';
//        $url = ERD_BASE_URL.'/transfer/TPW/get_incoming';
//        var_dump(getenv('ERD_BASE_URL'));
//        echo json_encode($url);
        $data = External_calls::makeRequest($url);
        echo $data;
    }
    public function recall(){
	    $trans_reference = $this->input->post('reference');
        if(!$trans_reference){
            echo json_encode(["error"=>"Provide a valid transaction reference!"]);
            exit();
        }
        $reason = $this->input->post('remarks');
        $response = $this->updateTrans($trans_reference,'recall',$reason);
        if($response){
            $resp = json_decode($response);
            if($resp->status == "00"){
                $this->load->model('transfer_model','loaded_model');
                $this->loaded_model->recall_transfer($trans_reference);
            }
        }
        echo $response;
    }
    public function reject(){
        $trans_reference = $this->input->post('reference');
        if(!$trans_reference){
            echo json_encode(["error"=>"Provide a valid transaction reference!"]);
            exit();
        }
        $reason = $this->input->post('remarks');
        echo $this->updateTrans($trans_reference,'reject',$reason);
    }
    public function view_incoming($trans_reference = null){
        if(!$trans_reference){
            echo json_encode(["error"=>"Provide a valid transaction reference!"]);
            exit();
        }
        $this->load->library('External_calls');
        $data = [
            "transfer_reference"=>$trans_reference,
            "caller"=>"HaslekIsBae"
        ];
        $url = ERD_BASE_URL.'/transfer/view_transfer';
        $response =  External_calls::makeRequest($url,$data,'POST');

        $data = [
            'transfer'=>json_decode($response)[0]
        ];
        $this->load->view("items/incoming_trans_item",$data );
    }
    public function uploadTransfer(){
	    $this->load->model('transfer_model');
	    $unUploadedTrans = $this->transfer_model->get_un_uploaded_transfers();
//	    echo json_encode([$unUploadedTrans[0]->reference]);
//	    exit();
	    if($unUploadedTrans && count($unUploadedTrans)> 0){
	        foreach ($unUploadedTrans as $tran){
	             $this->transfer_model->upload_trans($tran->transfer_reference,$tran->sent_by);
            }
        }
    }
    public function acceptt(){
        $trans_reference = $this->input->post('reference');
        if(!$trans_reference){
            echo json_encode(["error"=>"Provide a valid transaction reference!"]);
            exit();
        }
        $response = $this->updateTrans($trans_reference,'accept');
        if($response){
            $resp = json_decode($response);
//            var_dump($resp->details->items);
//            echo "quantity: ".count($resp->details->items);
            if($resp->details->items != null && count($resp->details->items) > 0){
//                echo 'Hello world';
                $this->load->model('transfer_model','loaded_model');
                $res = $this->loaded_model->accept($resp->details);
            }
        }
        echo $response;
    }
    private function updateTrans($trans_reference,$type,$remarks = null) {
        if(!$trans_reference){
            echo json_encode(["error"=>"Provide a valid transaction reference!"]);
            exit();
        }
        $this->load->library('External_calls');

        $url = ERD_BASE_URL.'/transfer/'.$type.'_transfer';

        $employee = $this->Employee->get_logged_in_employee_info();
        $data = [
            "transfer_reference"=>$trans_reference,
            "remarks"=>$remarks,
            "caller"=>"HaslekIsBae",
            $type."ed_by"=> $employee->first_name.' '.$employee->last_name
        ];
        return External_calls::makeRequest($url,$data,'POST');
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
		$receiving = $this->Receiving->get_lpo_by_lpo_id($id)->result();
		$items = $this->Receiving->get_lpo_items_data_by_lpo_id($id)->result();

		$data = array(
			'meta'	=> $receiving[0],
			'items'	=> $items
		);
		$this->load->view("lpo/history_view", $data);
	}

	
	public function reprint($id = -1)
	{
		//If id is not valide or does not exist, go back the inventory history table
		if ($id == -1 || !$this->Receiving->exists($id)) {
			$this->history();
		}

		$receiving = $this->Receiving->get_lpo_by_lpo_id($id)->result();
		$items = $this->Receiving->get_lpo_items_data_by_lpo_id($id)->result();

		$data = array(
			'meta'	=> $receiving[0],
			'items'	=> $items
		);

		$mode = '';
		if (count($items) > 0 && (int) $items[0]->quantity_purchased < 0) {
			$mode = "Return";
		} else {
			$mode = "Receiving";
		}
		$data['mode'] = $mode;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode('LPO ' . $id);
		$data['print_after_sale'] = $this->transfer_lib->is_print_after_sale();
		$data['receipt_title'] = $this->lang->line('lpo_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['date'] = date("Y-m-d h:i A", strtotime($data['meta']->receiving_time));

		$this->load->view("lpo/history_print", $data);
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
		$data['print_after_sale'] = $this->transfer_lib->is_print_after_sale();
		$data['receipt_title'] = $this->lang->line('transfer_receipt');
		$data['date'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($data['meta']->transfer_time));
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['from_branch'] = $this->CI->Stock_location->get_location_name($data['meta']->request_from_branch_id);
		$data['to_branch'] = $this->CI->Stock_location->get_location_name($data['meta']->request_to_branch_id);
		$data['transfer_id'] = $id;
		$data['print_after_sale'] = 0;
		$this->load->view("receivings/transfer_history_print", $data);
	}


	
}
