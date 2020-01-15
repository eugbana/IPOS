<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Receivings extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('receivings');

		$this->load->library('receiving_lib');
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
			$this->receiving_lib->set_supplier($supplier_id);
		}

		$this->_reload();
	}

	public function change_mode()
	{
		$stock_destination = $this->input->post('stock_destination');
		$stock_source = $this->input->post('stock_source');

		if ((!$stock_source || $stock_source == $this->receiving_lib->get_stock_source()) && (!$stock_destination || $stock_destination == $this->receiving_lib->get_stock_destination())
		) {
			$this->receiving_lib->clear_reference();
			$mode = $this->input->post('mode');

			$this->receiving_lib->set_mode($mode);
		} elseif ($this->Stock_location->is_allowed_location($stock_source, 'receivings')) {
			$this->receiving_lib->set_stock_source($stock_source);
			$this->receiving_lib->set_stock_destination($stock_destination);
		}


		$this->_reload();
	}

	public function set_comment()
	{
		$this->receiving_lib->set_comment($this->input->post('comment'));
	}

	public function set_print_after_sale()
	{
		$this->receiving_lib->set_print_after_sale($this->input->post('recv_print_after_sale'));
	}

	public function set_reference()
	{
		$this->receiving_lib->set_reference($this->input->post('recv_reference'));
	}

	public function add()
	{
		$data = array();

		$mode = $this->receiving_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$quantity = ($mode == 'receive' || $mode == 'requisition') ? 1 : -1;
		$item_location = $this->receiving_lib->get_stock_source();



		if ($mode == 'return' && $this->Receiving->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt)) {
			$this->receiving_lib->return_entire_receiving($item_id_or_number_or_item_kit_or_receipt);
		} elseif ($this->Item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt)) {
			$this->receiving_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location);
		} elseif (!$this->receiving_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location)) {
			$data['error'] = $this->lang->line('receivings_unable_to_add_item');
		}

		$this->_reload($data);
	}

	public function edit_item($item_id)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		//$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');

		$description = $this->input->post('description');
		$serialnumber = $this->input->post('serialnumber');
		$price = parse_decimals($this->input->post('price'));
		$quantity = parse_decimals($this->input->post('quantity'));
		$discount = parse_decimals($this->input->post('discount'));
		$item_location = $this->input->post('location');
		$batch_no = $this->input->post('batch_no');
		$expiry = $this->input->post('expiry');
		// $unit_price = parse_decimals($this->input->post('unit_price'));

		if ($this->receiving_lib->get_mode() == 'return' && $quantity > 0) {
			$quantity = $quantity * -1;
		}
		if ($this->receiving_lib->get_mode() == 'receive' && $quantity < 0) {
			$quantity = $quantity * -1;
		}

		if ($quantity == 0) {
			$this->delete_item($item_id);
			// $quantity = 1;
			// $data['warning'] = "Quantity cannot be zero. You can remove the item from the cart from cart.";
		}

		if ($this->form_validation->run() != FALSE) {
			$this->receiving_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $price, $batch_no, $expiry);
			// $this->receiving_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $price, $batch_no, $expiry, $unit_price);
		} else {

			$data['error'] = $this->lang->line('receivings_error_editing_item');
			$this->_reload($data);
		}

		//$this->_reload($data);
		redirect('receivings');
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
		$this->receiving_lib->delete_item($item_number);

		//$this->_reload();
		redirect('receivings');
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
		$this->receiving_lib->clear_reference();
		$this->receiving_lib->remove_supplier();

		$this->_reload();
	}

	public function complete()
	{
		$data = array();

		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['receipt_title'] = $this->lang->line('receivings_receipt');
		$data['transaction_time'] = date("Y-m-d h:i A");
		$data['transaction_date'] = date("Y-m-d h:i A");

		$data['mode'] = $this->receiving_lib->get_mode();
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'STOCK RETURN REPORT';
		}
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_type'] = $this->input->post('payment_type');
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['stock_location'] = $this->receiving_lib->get_stock_source();
		if ($this->input->post('amount_tendered') != NULL) {
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_info = '';
		$supplier_id = $this->receiving_lib->get_supplier();
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
		$data['receiving_id'] = 'RECV ' . $this->Receiving->save($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);

		$data = $this->xss_clean($data);

		if ($data['receiving_id'] == 'RECV -1') {
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		} else {
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		}

		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();

		$this->load->view("receivings/receipt", $data);

		$this->receiving_lib->clear_all();
	}

	public function requisition_complete()
	{
		if ($this->receiving_lib->get_stock_source() != $this->receiving_lib->get_stock_destination()) {
			foreach ($this->receiving_lib->get_cart() as $item) {
				$this->receiving_lib->delete_item($item['line']);
				$this->receiving_lib->add_item($item['item_id'], $item['quantity'], $this->receiving_lib->get_stock_destination(), $item['unit_price']);
				$this->receiving_lib->add_item($item['item_id'], -$item['quantity'], $this->receiving_lib->get_stock_source(), $item['unit_price']);
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
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['receipt_title'] = $this->lang->line('receivings_receipt');
		if ($data['mode'] == 'return') {
			$data['receipt_title'] = 'STOCK RETURN REPORT';
		}
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('receivings');
		$data['payment_type'] = $receiving_info['payment_type'];
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['receiving_id'] = 'RECV ' . $receiving_id;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		$employee_info = $this->Employee->get_info($receiving_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->receiving_lib->get_supplier();
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

		$this->receiving_lib->clear_all();
	}

	private function _reload($data = array())
	{
		$data['cart'] = $this->receiving_lib->get_cart();
		// echo "<pre>";
		// print_r($data['cart']);
		// echo "</pre>";
		// exit;
		$data['modes'] = array('receive' => $this->lang->line('receivings_receiving'), 'return' => $this->lang->line('receivings_return'));
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('receivings');
		$data['show_stock_locations'] = count($data['stock_locations']) > 1;
		if ($data['show_stock_locations']) {
			$data['modes']['requisition'] = $this->lang->line('receivings_requisition');
			$data['stock_source'] = $this->receiving_lib->get_stock_source();
			$data['stock_destination'] = $this->receiving_lib->get_stock_destination();
		}

		$data['total'] = $this->receiving_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_options'] = $this->Receiving->get_payment_options();

		$supplier_id = $this->receiving_lib->get_supplier();
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

		$data['print_after_sale'] = 1; //$this->receiving_lib->is_print_after_sale();

		$data = $this->xss_clean($data);

		$this->load->view("receivings/receiving", $data);
	}

	public function save($receiving_id = -1)
	{
		$newdate = $this->input->post('date');

		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);

		$receiving_data = array(
			'receiving_time' => $date_formatter->format('Y-m-d H:i:s'),
			'supplier_id' => $this->input->post('supplier_id') ? $this->input->post('supplier_id') : NULL,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'reference' => $this->input->post('reference') != '' ? $this->input->post('reference') : NULL
		);

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
		$this->receiving_lib->clear_all();

		$this->_reload();
	}

	public function history()
	{
		$data = [];
		$data['table_headers'] = $this->xss_clean(get_inventory_history_headers());
		$this->load->view("receivings/history", $data);
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

		$filters = array(
			'start_date' 	=> $this->input->get('start_date'),
			'end_date' 		=> $this->input->get('end_date'),
		);

		//$total		= $this->Receiving->get_all_receivings($search, $filters);
		$data_all		= $this->Receiving->get_all_receivings($search, 0, $offset, $sort, $order, $filters)->result();
		//$total		= $this->Receiving->get_all_receivings($search, 0, $offset, $sort, $order, $filters)->num_rows();
		$data =  array_slice($data_all, $offset, $limit); //limit is usually more than 0
		$data_rows	= array();
		foreach ($data as $d) {
			$data_rows[] = $this->xss_clean(get_receiving_item_data_row($d, $d->receiving_id, $this));
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

		$filters = array(
			'start_date' 	=> $this->input->get('start_date'),
			'end_date' 		=> $this->input->get('end_date'),
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
		$receiving = $this->Receiving->get_receiving_by_receiving_id($id)->result();
		$items = $this->Receiving->get_receiving_items_data_by_receiving_id($id)->result();

		$data = array(
			'meta'	=> $receiving[0],
			'items'	=> $items
		);
		$this->load->view("receivings/history_view", $data);
	}


	public function reprint($id = -1)
	{
		//If id is not valide or does not exist, go back the inventory history table
		if ($id == -1 || !$this->Receiving->exists($id)) {
			$this->history();
		}

		$receiving = $this->Receiving->get_receiving_by_receiving_id($id)->result();
		$items = $this->Receiving->get_receiving_items_data_by_receiving_id($id)->result();

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
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode('RECV ' . $id);
		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();
		$data['receipt_title'] = $this->lang->line('receivings_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['date'] = date("Y-m-d h:i A", strtotime($data['meta']->receiving_time));


		$this->load->view("receivings/history_print", $data);
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
		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();
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
