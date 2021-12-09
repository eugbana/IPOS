<?php
function get_laboratory_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('test_code' => $CI->lang->line('lab_test_code')),
		array('test_name' => $CI->lang->line('lab_test_name')),
		array('test_amount' => $CI->lang->line('lab_test_amount')),
		array('test_type' => $CI->lang->line('lab_test_type')),
		array('test_subgroup' => $CI->lang->line('lab_test_subgroup')),
		array('test_unit' => $CI->lang->line('lab_test_unit'))
	);


	return transform_headers($headers);
}

function get_laboratory_data_row($laboratory, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'test_id' => $laboratory->item_id,
		'test_name' => $laboratory->test_name,
		'test_code' => $laboratory->test_code,
		'test_type' => $laboratory->test_type,
		'test_amount' => to_currency($laboratory->test_amount),
		'test_subgroup' => $laboratory->test_subgroup,
		'test_unit' => $laboratory->test_unit,
		'messages' => empty($person->phone_number) ? '' : anchor(
			"Messages/view/$person->person_id",
			'<span class="glyphicon glyphicon-phone"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		),
		'edit' => anchor(
			$controller_name . "/view/$laboratory->item_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}
function get_account_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('invoice_id' => $CI->lang->line('account_invoice_number')),
		array('person_id' => $CI->lang->line('account_customer_name')),
		array('doctor_name' => $CI->lang->line('doctor_name')),
		array('status' => $CI->lang->line('payment_status')),

	);


	return transform_headers($headers);
}

function get_account_data_row($account, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'invoice_id' => $account->invoice_id,
		'person_id' => $account->person_id,
		'doctor_name' => $account->doctor_name,
		'status' => $laboratory->status,
		'test_amount' => to_currency($laboratory->test_amount),
		'test_unit' => $laboratory->test_unit,
		'messages' => empty($person->phone_number) ? '' : anchor(
			"Messages/view/$person->person_id",
			'<button><span class="glyphicon glyphicon-phone"></span></button>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		),
		'edit' => anchor(
			$controller_name . "/view/$laboratory->test_id",
			'<span class="glyphicon glyphicon-edit"><button>Hello</button></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}
function get_sales_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('sale_id' => $CI->lang->line('common_id')),
		array('sale_time' => $CI->lang->line('sales_sale_time')),
		array('customer_name' => $CI->lang->line('customers_customer')),
		array('employee_name' => 'Employee'),
		array('item_qty' => "Item Qty"),
		array('discount' => 'Discount'),
		array('vat' => 'VAT'),
		array('credit' => 'Credit'),
		array('amount_due' => "Discounted Amount"),
		array('amount_tendered' => $CI->lang->line('sales_amount_tendered')),
		array('change_due' => $CI->lang->line('sales_change_due')),
		array('payment_type' => $CI->lang->line('sales_payment_type')),

	);

	if ($CI->config->item('invoice_enable') == TRUE) {
	}

	return transform_headers(array_merge($headers, array(array('receipt' => '&nbsp', 'sortable' => FALSE))));
}

/*
 Gets the html data rows for the sales.
 */
function get_sale_data_last_row($sales, $controller)
{
	$CI = &get_instance();
	$sum_amount_due = 0;
	$sum_amount_tendered = 0;
	$sum_change_due = 0;

	foreach ($sales->result() as $key => $sale) {
		$sum_amount_due += $sale->amount_due;
		$sum_amount_tendered += $sale->amount_tendered;
		$sum_change_due += $sale->change_due;
	}

	return array(
		'sale_id' => '-',
		'sale_time' => '<b>' . $CI->lang->line('sales_total') . '</b>',
		'amount_due' => '<b>' . to_currency($sum_amount_due) . '</b>',
		'amount_tendered' => '<b>' . to_currency($sum_amount_tendered) . '</b>',
		'change_due' => '<b>' . to_currency($sum_change_due) . '</b>'
	);
}

function get_sale_data_row($sale, $controller, $payment_types)
{
	$CI = &get_instance();
	$controller_name = $CI->uri->segment(1);

	$row = array(
		'sale_id' => $sale->sale_id,
		'sale_time' => date($CI->config->item('dateformat') . ' ' . $CI->config->item('timeformat'), strtotime($sale->sale_time)),
		'customer_name' => $sale->customer_last_name . ' ' . $sale->customer_first_name,
		'employee_name' => $sale->employee_last_name . ' ' . $sale->employee_first_name,
		'item_qty' => round($sale->item_qty),
		'discount' => to_currency($sale->total_discount),
		'vat' => to_currency($sale->total_vat),
		'credit' => to_currency($sale->credit),
		'amount_due' => to_currency($sale->total_amount_due),
		'amount_tendered' => to_currency($sale->total_amount_paid),
		'change_due' => to_currency($sale->change_due),
		'payment_type' => $payment_types
	);

	if ($CI->config->item('invoice_enable')) {
		$row['invoice_number'] = $sale->invoice_number;
		$row['invoice'] = empty($sale->invoice_number) ? '' : anchor(
			$controller_name . "/invoice/$sale->sale_id",
			'<span class="glyphicon glyphicon-list-alt"></span>',
			array('title' => $CI->lang->line('sales_show_invoice'))
		);
	}

	$row['receipt'] = anchor(
		$controller_name . "/receipt/$sale->sale_id",
		'<span class="glyphicon glyphicon-print"></span>',
		array('target' => '_blank', 'title' => $CI->lang->line('sales_show_receipt'))
	);
	// $row['edit'] = anchor(
	// 	$controller_name . "/edit/$sale->sale_id",
	// 	'<span class="glyphicon glyphicon-edit"></span>',
	// 	array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $CI->lang->line('common_delete'), 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
	// );

	return $row;
}

/*
Get the sales payments summary
*/
function get_sales_manage_payments_summary($payments, $sales, $controller)
{
	$CI = &get_instance();
	$table = '<div id="report_summary">';

	foreach ($payments as $key => $payment) {
		$amount = $payment['payment_amount'];

		// WARNING: the strong assumption here is that if a change is due it was a cash transaction always
		// therefore we remove from the total cash amount any change due
		if ($payment['payment_type'] == $CI->lang->line('sales_cash')) {
			foreach ($sales->result_array() as $key => $sale) {
				$amount -= $sale['change_due'];
			}
		}
		$table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
	}
	$table .= '</div>';

	return $table;
}
/*
Get the sales payments summary
*/
function get_sales_payments_summary($payments, $controller)
{
	$CI = &get_instance();
	$table = '<div id="report_summary">';
	$table .= '<small align="center"></small>';

	foreach ($payments as $key => $payment) {

		$table .= '<div class="summary_row">' . $key . ': ' . to_currency($payment) . '</div>';
	}
	$table .= '</div>';

	return $table;
}

function transform_headers_readonly($array)
{
	$result = array();
	foreach ($array as $key => $value) {
		$result[] = array('field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp)', $value));
	}

	return json_encode($result);
}

function transform_headers($array, $readonly = FALSE, $editable = TRUE)
{
	$result = array();

	if (!$readonly) {
		$array = array_merge(array(array('checkbox' => 'select', 'sortable' => FALSE)), $array);
	}

	if ($editable) {
		$array[] = array('edit' => '');
	}

	foreach ($array as $element) {
		reset($element);
		$result[] = array(
			'field' => key($element),
			'title' => current($element),
			'switchable' => isset($element['switchable']) ?
				$element['switchable'] : !preg_match('(^$|&nbsp)', current($element)),
			'sortable' => isset($element['sortable']) ?
				$element['sortable'] : current($element) != '',
			'checkbox' => isset($element['checkbox']) ?
				$element['checkbox'] : FALSE,
			'class' => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ?
				'print_hide' : '',
			'sorter' => isset($element['sorter']) ?
				$element['sorter'] : ''
		);
	}
	return json_encode($result);
}



function get_people_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		//array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => 'LastName'),
		array('first_name' => 'FirstName'),
		//array('username'=>'Username'),

	);
	if ($CI->Employee->has_grant('employees', $CI->session->userdata('person_id'))) {
		$headers[] = array('branch' => 'Branch');
		$headers[] = array('role' => 'Role');
		$headers[] = array('username' => 'Username');
		$headers[] = array('auth_code' => 'Auth Code');
	}
	$headers[] = array('email' => $CI->lang->line('common_email'));
	$headers[] = array('phone_number' => $CI->lang->line('common_phone_number'));
	$headers[] = array('status' => $CI->lang->line('common_status'));
	if ($CI->Employee->has_grant('messages', $CI->session->userdata('person_id'))) {
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}
	if ($CI->Employee->has_grant('employees', $CI->session->userdata('person_id'))) {
		$headers[] = array('assign' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

function get_person_data_row($person, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$item_info = $CI->Item->get_info_role($person->role);
	$branch_info = $CI->Item->get_info_branch($person->branch_id);
	return array(
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'branch' => $branch_info->location_name,
		'role' => $item_info->role,
		'username' => $person->username,
		// 'auth_code' => $person->auth_code,
		'auth_code' => !empty($person->auth_code) ? '****' : '',
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'status' => ($person->deleted) == 0 ? 'active' : 'deactivated',
		'phone_number' => $person->phone_number,
		'messages' => empty($person->phone_number) ? '' : anchor(
			"Messages/view/$person->person_id",
			'<span class="glyphicon glyphicon-phone"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		),
		'edit' => anchor(
			$controller_name . "/view/$person->person_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		),
		'assign' => anchor(
			$controller_name . "/view_assign/$person->person_id",
			'<span class="glyphicon glyphicon-user"></span>',
			array('title' => 'Assign Role')
		)
	);
}

function get_customer_manage_table_headers()
{
	$CI = &get_instance();
	$role = $CI->Employee->get_logged_in_employee_info()->role;
	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('total' => $CI->lang->line('common_total_spent'), 'sortable' => FALSE),
		array('type' => 'Type'),
		array('wallet' => 'Wallet'),
		array('credit_limit' => 'Credit Limit'),


	);
	if ($role == 12 || $role == 3) {
		//admin and accountant
		$headers[] = array('edit_wallet' => '');
	}

	if ($CI->Employee->has_grant('messages', $CI->session->userdata('person_id'))) {
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

function get_customer_data_row($person, $stats, $controller)
{
	$CI = &get_instance();
	$role = $CI->Employee->get_logged_in_employee_info()->role;
	$controller_name = strtolower(get_class($CI));

	$row =  array(
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'phone_number' => $person->phone_number,
		'total' => to_currency($stats->total),
		'type' => $person->staff ? 'Staff' : 'Customer',
		'wallet' => to_currency($person->wallet),
		'credit_limit' => to_currency($person->credit_limit),
		'messages' => empty($person->phone_number) ? '' : anchor(
			"Messages/view/$person->person_id",
			'<span class="glyphicon glyphicon-phone"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		),
		'edit_wallet' => ($role == 12 || $role == 3) ? anchor(
			$controller_name . "/view_wallet/$person->person_id",
			'<span class="glyphicon glyphicon-credit-card"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => 'Update Customer Wallet')
		) : '',
		'edit' =>  anchor(
			$controller_name . "/view/$person->person_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);

	return $row;
}

// function get_customer_data_row($person, $stats, $controller)
// {
// 	$CI = &get_instance();
// 	$role = $CI->Employee->get_logged_in_employee_info()->role;
// 	$controller_name = strtolower(get_class($CI));

// 	return array(
// 		'people.person_id' => $person->person_id,
// 		'last_name' => $person->last_name,
// 		'first_name' => $person->first_name,
// 		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
// 		'phone_number' => $person->phone_number,
// 		'total' => to_currency($stats->total),
// 		'type' => $person->staff ? 'Staff' : 'Customer',
// 		'wallet' => to_currency($person->wallet),
// 		'credit_limit' => to_currency($person->credit_limit),
// 		'messages' => empty($person->phone_number) ? '' : anchor(
// 			"Messages/view/$person->person_id",
// 			'<span class="glyphicon glyphicon-phone"></span>',
// 			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
// 		),
// 		'edit_wallet' => ($role == 12 || $role == 3) ? anchor(
// 			$controller_name . "/view_wallet/$person->person_id",
// 			'<span class="glyphicon glyphicon-credit-card"></span>',
// 			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => 'Update Customer Wallet')
// 		) : '',
// 		'edit' =>  anchor(
// 			$controller_name . "/view/$person->person_id",
// 			'<span class="glyphicon glyphicon-edit"></span>',
// 			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
// 		)

// 	);
// }

function get_company_manage_table_headers()
{
	$CI = &get_instance();
	$role = $CI->Employee->get_logged_in_employee_info()->role;
	$headers = array(
		array('company.company_id' => $CI->lang->line('common_id')),
		array('company_name' => 'Company Name'),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		// array('total' => $CI->lang->line('common_total_spent'), 'sortable' => FALSE),
		// array('type' => 'Type'),
		array('wallet' => 'Wallet'),
		array('credit_limit' => 'Credit Limit')


	);
	if ($role == 12 || $role == 3) {
		//admin and accountant
		$headers[] = array('edit_wallet' => '');
	}

	if ($CI->Employee->has_grant('messages', $CI->session->userdata('person_id'))) {
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

function get_company_data_row($company, $controller)
{
	$CI = &get_instance();
	$role = $CI->Employee->get_logged_in_employee_info()->role;
	$controller_name = strtolower(get_class($CI));

	return array(
		'company.company_id' => $company->company_id,
		'company_name' => $company->company_name,
		// 'tin' => $company->tin,
		// 'cac' => $company->cac,
		'email' => empty($company->contact_email) ? '' : mailto($company->contact_email, $company->contact_email),
		'phone_number' => $company->contact_phone,
		// 'total' => to_currency($stats->total),
		// 'type' => $person->staff ? 'Staff' : 'Customer',
		'wallet' => to_currency($company->wallet),
		'credit_limit' => to_currency($company->credit_limit),
		// 'messages' => empty($person->phone_number) ? '' : anchor(
		// 	"Messages/view/$person->person_id",
		// 	'<span class="glyphicon glyphicon-phone"></span>',
		// 	array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		// ),
		'edit_wallet' => ($role == 12 || $role == 3) ? anchor(
			$controller_name . "/view_wallet/$company->company_id",
			'<span class="glyphicon glyphicon-credit-card"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => 'Update Company Wallet')
		) : '',
		'edit' =>  anchor(
			$controller_name . "/view/$company->company_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => 'Update Company')
		)

	);
}

function get_suppliers_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		//array('people.person_id' => $CI->lang->line('common_id')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('address_1' => 'Address'),
		// array('agency_name' => $CI->lang->line('suppliers_agency_name')),
		// array('last_name' => $CI->lang->line('common_last_name')),
		// array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);

	if ($CI->Employee->has_grant('messages', $CI->session->userdata('person_id'))) {
		$headers[] = array('messages' => '');
	}

	return transform_headers($headers);
}

function get_supplier_data_row($supplier, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'people.person_id' => $supplier->person_id,
		'company_name' => $supplier->company_name,
		'address_1' => $supplier->address_1,
		// 'agency_name' => $supplier->agency_name,
		// 'last_name' => $supplier->last_name,
		// 'first_name' => $supplier->first_name,
		'email' => empty($supplier->email) ? '' : mailto($supplier->email, $supplier->email),
		'phone_number' => $supplier->phone_number,
		'messages' => empty($supplier->phone_number) ? '' : anchor(
			"Messages/view/$supplier->person_id",
			'<span class="glyphicon glyphicon-phone"></span>',
			array('class' => "modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line('messages_sms_send'))
		),
		'edit' => anchor(
			$controller_name . "/view/$supplier->person_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => "modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}

function get_items_manage_table_heders()
{
	$CI = &get_instance();
	//if($filters['expiry'] != FALSE){
	//$headers[] =array('batch' => 'Batch');
	//}

	$headers = array(
		array('batch' => 'Batch'),
		array('items.item_number' => $CI->lang->line('common_id')),
		array('name' => $CI->lang->line('items_pname')),
		array('category' => $CI->lang->line('items_category')),
		array('pack' => $CI->lang->line('items_pack')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => $CI->lang->line('items_unit_price')),
		array('quantity' => $CI->lang->line('items_quantity')),
		//array('inventory' => ''),
		array('stock' => '')
	);



	return transform_headers($headers);
}
function get_items_manage_table_headers()
{
	$CI = &get_instance();
	//if($filters['expiry'] != FALSE){
	//$headers[] =array('batch' => 'Batch');
	//}

	$headers = array(
		array('batch' => 'Batch'),
		array('item_number' => 'Code'),
		array('name' => $CI->lang->line('items_pname')),
		array('type' => 'Department'), //categorization
		array('category' => 'Category'),
		array('pack' => $CI->lang->line('items_pack')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => 'Vat Excl. Price'),
		array('vated_unit_price' => 'VAT Incl. Price'),
		array('whole_price' => 'VAT Excl. Wholesale Price'),
		array('vated_whole_price' => 'VAT Incl. WholeSale Price'),
		array('quantity' => $CI->lang->line('items_quantity')),
		//array('inventory' => ''),
		array('stock' => '')
	);
	return transform_headers($headers);
}


function get_expense_manage_table_headers($showBalance = true)
{
	$CI = &get_instance();
	//if($filters['expiry'] != FALSE){
	//$headers[] =array('batch' => 'Batch');
	//}

	$headers = array(
		array('date' => 'Date'),
		array('details' => 'Details / Description'),
		array('receipt_no' => 'Reference NO'),
		array('category_name' => 'Expense Category'),
		array('employee_name' => 'Employee'), 
		array('type' => 'Type'),
		array('amount' => 'Amount'),
		$showBalance ? array('balance' => 'Balance') : array('' => ''),
		// array('edit' => '')
	);
	return transform_headers($headers);
}

function get_audits_manage_table_headers()
{
	$CI = &get_instance();
	//if($filters['expiry'] != FALSE){
	//$headers[] =array('batch' => 'Batch');
	//}

	$headers = array(
		array('audit_id' => 'Audit ID'),
		array('audit_time' => 'Audit Time'),
		array('employee_name' => 'Employee'),
		array('action_type' => 'Action'),
		array('description' => 'Description')
	);
	return transform_headers($headers);
}

function get_inventory_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('supplier_id' 	=> 'Supplier'),
		array('employee_id' 	=> 'Received By'),
		array('payment_type' 	=> 'Payment Type'),
		array('reference' 		=> 'Reference'),
		array('receiving_time'  => 'Timestamp'),
	);
	return transform_headers($headers);
}

function get_stock_intake_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('title' 		=> 'Title'),
		array('description' 		=> 'Description'),
		array('status' 		=> 'Status'),
		array('employee_id' 	=> 'Created By'),
		array('receiving_time'  => 'Timestamp'),
	);
	return transform_headers($headers);
}

// function get_pending_request_headers()
// {
// 	$CI = &get_instance();
// 	$headers = array(
// 		array('lpos.lpo_id' 	=> 'LPO ID'),
// 		array('supplier_id' 	=> 'Supplier'),
// 		array('employee_id' 	=> 'Created By'),
// 		// array('payment_type' 	=> 'Payment Type'),
// 		// array('reference' 		=> 'Invoice NO.'),
// 		array('receiving_time'  => 'Date'),
// 	);
// 	return transform_headers($headers);
// }

function get_lpo_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('lpos.lpo_id' 	=> 'LPO ID'),
		array('supplier_id' 	=> 'Supplier'),
		array('employee_id' 	=> 'Created By'),
		// array('payment_type' 	=> 'Payment Type'),
		// array('reference' 		=> 'Invoice NO.'),
		array('receiving_time'  => 'Date'),
	);
	return transform_headers($headers);
}

function get_transfer_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('transfer' 		=> 'Transfer ID'),
		array('request_to_branch_id' 	=> 'Receiving Branch'),
		array('employee_id' 	=> 'Transfered By'),
		//array('total' => 'Total')

		array('transfer_time'  => 'Timestamp'),
        array('reference'=>'Transfer Reference'),
        array('recall'=>'Action'),
	);
	return transform_headers($headers);
}

function get_request_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('request' 		=> 'Request ID'),
		array('request_to_branch_id' 	=> 'Requesting from Branch'),
		array('employee_id' 	=> 'Requested By'),
		//array('total' => 'Total'),

		array('transfer_time'  => 'Timestamp'),
	);
	return transform_headers($headers);
}

function get_pending_request_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('request.request_id' 		=> 'Request ID'),
		array('from_branch' 	=> 'From Branch'),
		// array('to_branch' 	=> 'To Branch'),
		array('created_at'  => 'Request Time'),
	);
	return transform_headers($headers);
}

function get_incoming_transfers_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('transfer.transfer_id' 		=> 'Transfer ID'),
		array('from_branch' 	=> 'From Branch'),
		// array('to_branch' 	=> 'To Branch'),
		array('created_at'  => 'Transfer Time'),
		array('status'  => 'Status'),
	);
	return transform_headers($headers);
}

function get_pending_item_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('item.id' 		=> 'ID'),
		array('name' 	=> 'Item Name'),
		array('item_number' 	=> 'Item Number'),
		array('category' 	=> 'Category'),
		array('unit_price'  => 'Unit Price'),
		array('created_at'  => 'Time'),
	);
	return transform_headers($headers);
}

function get_transfer_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'transfer.transfer_id'		=> $id,
		'transfer'					=> $id,
		'request_to_branch_id'			=> $item->stock_location,
		'employee_id'					=> $item->first_name . ' ' . $item->last_name,
		//'total'					=> to_currency($item->total),

		'transfer_time'				=> $item->transfer_time ? date_formatter($item->transfer_time) : 'N/A',
		'reference'                 => $item->reference ? $item->reference:'N/A',
		'recall'                    => ($item->reference && !$item->status)? '<div>
                                                        <div id="'.$item->reference.'">
                                                            <a class="btn btn-danger recall-button" onclick="switchTo(\''.$item->reference.'\', \'recall-'.$item->reference.'\')" data-transref="'.$item->reference.'">recall</a>
                                                        </div>
                                                        <div id="recall-'.$item->reference.'" class="form-group" style="display: none">
                                                            <textarea id="remark-'.$item->reference.'" class="form-control" placeholder="Reason to recall.."></textarea>
                                                            <a class="btn" onclick="switchTo(\''.$item->reference.'\', \'recall-'.$item->reference.'\')" data-transref="'.$item->reference.'">Cancel</a>
                                                            <a class="btn btn-primary recall-button" onclick="recall_transfer(this)" data-transref="'.$item->reference.'">Continue</a>
                                                        </div>
		                                            </div>':'N/A',
		'edit' 							=> anchor($controller_name . "/transfer_reprint/$item->transfer_id", 'view items', 'title="View Transfer Items"')
	);
}

function get_request_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'request.request_id'		=> $id,
		'request'					=> $id,
		'request_to_branch_id'			=> $item->stock_location,
		'employee_id'					=> $item->first_name . ' ' . $item->last_name,
		//'total'					=> to_currency($item->total),

		'request_time'				=> $item->request_time ? date_formatter($item->request_time) : 'N/A',
		'edit' 							=> anchor($controller_name . "/request_reprint/$item->request_id", 'view items', 'title="View Requested Items"')
	);
}

function get_pending_request_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'request.request_id'		=> $id,
		'request'					=> $id,
		'from_branch'			=> $item['from_branch'],
		'to_branch'			=> $item['to_branch'],
		'created_at'				=> $item['created_at'] ? date_formatter($item['created_at']) : 'N/A',
		'edit' 							=> anchor($controller_name . "/transfer/$id", 'view items', 'title="View Requested Items"')
	);
}

function get_vat_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('id' 				=> 'ID'),
		array('name' 			=> 'Item'),
		array('total_quantity'	=> 'Total Quantity'),
		array('total_vat'		=> 'Total'),
	);
	return transform_headers($headers);
}

function get_vat_item_row($id, $quantity, $name, $vat, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'id'		    	=> $id,
		'name'				=> $name,
		'total_quantity'	=> $quantity,
		'total_vat'			=> $vat,
	);
}

function get_receiving_item_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('quantity_purchased' 	=> 'Quantity'),
		array('item_location' 		=> 'Location'),
		array('discount_percent' 	=> 'Discount'),
		array('item_unit_price' 	=> 'Unit Price'),
		array('item_cost_price'  	=> 'Cost Price'),
	);
	return transform_headers($headers);
}

function get_receiving_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'receivings.receiving_id'		=> $id,
		'supplier_id'					=> $item->company_name ? $item->company_name : 'N/A',
		'employee_id'					=> $item->first_name . ' ' . $item->last_name,
		'payment_type'					=> $item->payment_type ? $item->payment_type : "N/A",
		'reference'						=> $item->reference ? $item->reference : "No Ref.",
		'receiving_time'				=> $item->receiving_time ? date_formatter($item->receiving_time) : 'N/A',
		'edit' 							=> anchor($controller_name . "/history_view/$item->receiving_id", 'view items', 'title="View Inventory Items"')
	);
}

function get_stock_intake_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'stock_intakes.stock_id'		=> $id,
		'employee_id'					=> $item->first_name . ' ' . $item->last_name,
		'title'					=> $item->title ? $item->title : "N/A",
		'description'						=> $item->description ? $item->description : "No Desc.",
		'status'					=> ucfirst($item->status),
		'receiving_time'				=> $item->receiving_time ? date_formatter($item->receiving_time) : 'N/A',
		'edit' 							=> anchor($controller_name . "/history_view/$item->stock_id", 'View Items', 'title="View Stock Intake Items"')
	);
}

function get_lpo_item_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'lpos.lpo_id'		=> $id,
		'supplier_id'					=> $item->company_name ? $item->company_name : 'N/A',
		'employee_id'					=> $item->first_name . ' ' . $item->last_name,
		'payment_type'					=> $item->payment_type ? $item->payment_type : "N/A",
		'reference'						=> $item->reference ? $item->reference : "No Invoice No",
		'receiving_time'				=> $item->receiving_time ? date_formatter($item->receiving_time) : 'N/A',
		'edit' 							=> anchor($controller_name . "/history_view/$item->lpo_id", 'view items', 'title="View Inventory Items"')
	);
}

function get_pending_requests_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'request.request_id'		=> $id,
		'from_branch'					=> $item['from_branch'],
		// 'to_branch'					=> $item['to_branch'],
		'status'					=> $item['status'],
		'created_at'				=> $item['created_at'] ? date_formatter($item['created_at']) : 'N/A',
		'edit' 							=> anchor($controller_name . "/transfer_item/$id", 'view items', 'title="View Items"')
	);
}

function get_incoming_transfer_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'transfer.transfer_id'		=> $id,
		'from_branch'					=> $item['to_branch'],
		// 'to_branch'					=> $item['to_branch'],
		'status'					=> $item['status'],
		'created_at'				=> $item['created_at'] ? date_formatter($item['created_at']) : 'N/A',
		'edit' 						=> anchor($controller_name . "/view_transfer/$id", 'view items', 'title="View Items"')
	);
}
function get_pending_items_data_row($item, $id, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	return array(
		'item.id'		=> $id,
		'name'					=> $item['name'],
		'item_number'					=> $item['item_number'],
		'category'					=> $item['category'],
		'unit_price'					=> $item['unit_price'],
		'created_at'				=> $item['created_at'] ? date_formatter($item['created_at']) : 'N/A',
		'edit' 						=> anchor($controller_name . "/view_pending_item/$id", 'view items', 'title="View Items"')
	);
}


function date_formatter($date)
{
	return date("D, jS M, Y. h:i A", strtotime($date));
}
function get_sales_history_headers()
{
	$CI = &get_instance();
	$headers = array(
		array('sale_id' 	=> 'Receipt Number'),
		array('employee'	=> 'Employee'),
		array('sales_amount' => 'Total'),
		array('timestamp' 	=> 'Timestamp'),
		array('sale_status'	=> 'Status'),
		array('total_items' => 'Total Items')
	);
	return transform_headers($headers);
}

function get_sales_history_row($item, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$id = $item['sale_id'];
	return array(
		'sale_id'		=> $id,
		'employee'		=> $item['employee'],
		'sales_amount'	=> $item['sales_amount'],
		'timestamp'		=> $item['timestamp'],
		'sale_status'	=> $item['sale_status'],
		'total_items'	=> $item['total_items'],
		'edit'			=> anchor($controller_name . "/history_view/$id", 'view items', 'title="View Items"')
	);
}

function get_item_data_row($item, $controller)
{
	if ($item->batch == '') {
		$CI = &get_instance();
		$item_tax_info = $CI->Item_taxes->get_info($item->item_id);
		$tax_percents = '';
		foreach ($item_tax_info as $tax_info) {
			$tax_percents .= to_tax_decimals($tax_info['percent']) . '%, ';
		}
		// remove ', ' from last item
		$tax_percents = substr($tax_percents, 0, -2);
		$controller_name = strtolower(get_class($CI));

		$image = NULL;
		if ($item->pic_filename != '') {
			$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
			if ($ext == '') {
				// legacy
				$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
			} else {
				// preferred
				$images = glob('./uploads/item_pics/' . $item->pic_filename);
			}

			if (sizeof($images) > 0) {
				$image .= '<a class="rollover" href="' . base_url($images[0]) . '"><img src="' . site_url('items/pic_thumb/' . pathinfo($images[0], PATHINFO_BASENAME)) . '"></a>';
			}
		}

		return array(
			'batch' => ($item->batch==''||$item->batch==null)?'<a class="modal-dlg" href="'.site_url("items/view_expiry_batch/$item->item_id").'">View</a>':$item->batch,
			'items.item_id' => $item->item_id,
			'item_number' => $item->item_number,
			'name' => $item->name,
			'type' => $item->type,
			'category' => $item->category,
			'company_name' => $item->company_name,
			'pack' => to_quantity_decimals($item->pack),
			'cost_price' => to_currency($item->cost_price),

			// 'unit_price' => to_currency(get_nearest_five($item->unit_price)),
			// 'vated_unit_price' => ($item->apply_vat == "YES") ? to_currency(get_nearest_five($item->unit_price + vat_amount($item->unit_price))) : to_currency(get_nearest_five($item->unit_price)),
			// 'whole_price' => to_currency($item->whole_price),
			// 'vated_whole_price' => ($item->apply_vat == "YES") ? to_currency(get_nearest_five($item->whole_price + vat_amount($item->whole_price))) : to_currency($item->whole_price),

			// 'unit_price' => to_currency(get_nearest_five($item->unit_price)),
			// 'unit_price' => ($item->apply_vat == "YES") ? to_currency(vat_excl($item->unit_price)) : to_currency(get_nearest_five($item->unit_price)), //Vat Excl
			'unit_price' => ($item->apply_vat == "YES") ? to_currency(vat_excl($item->unit_price)) : to_currency(get_nearest_five($item->unit_price)), //Vat Excl
			'vated_unit_price' => to_currency(get_nearest_five($item->unit_price)), //Vat Incl.
			'whole_price' => to_currency($item->whole_price),
			'vated_whole_price' => ($item->apply_vat == "YES") ? to_currency(get_nearest_five($item->whole_price + vat_amount($item->whole_price))) : to_currency($item->whole_price),

			'quantity' => to_quantity_decimals($item->quantity),
			'tax_percents' => !$tax_percents ? '-' : $tax_percents,
			'inventory' => anchor(
				$controller_name . "/inventory/$item->item_id",
				'<span class="glyphicon glyphicon-pushpin"></span>',
				array('class' => 'mod al-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_count'))
			),
			'stock' => anchor(
				$controller_name . "/count_details/$item->item_id",
				'<span class="glyphicon glyphicon-list-alt"></span>',
				array('class' => 'modal-dlg', 'title' => $CI->lang->line($controller_name . '_details_count'))
			),
			'edit' => anchor(
				$controller_name . "/view_update/$item->item_id",
				'<span class="glyphicon glyphicon-edit"></span>',
				array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
			)
		);
	} else {
		$datetimenow = date('Y/m/d H:i:s');
		$datetimeexpire = $item->expiry_date;
		$datetimenowc = strtotime($datetimenow);
		$datetimeexpirec = strtotime($datetimeexpire);
		$des = $datetimeexpirec - $datetimenowc;
		if ($item->period == 'days') {
			$d_days = 86400;
		} elseif ($item->period == 'weeks') {
			$d_days = 604800;
		} else {
			$d_days = 2419200;
		}
		$dd = $des / $d_days;

		if ($dd < $item->expiretime) {
			$CI = &get_instance();
			$item_tax_info = $CI->Item_taxes->get_info($item->item_id);
			$tax_percents = '';
			foreach ($item_tax_info as $tax_info) {
				$tax_percents .= to_tax_decimals($tax_info['percent']) . '%, ';
			}
			// remove ', ' from last item
			$tax_percents = substr($tax_percents, 0, -2);
			$controller_name = strtolower(get_class($CI));

			$image = NULL;
			if ($item->pic_filename != '') {
				$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
				if ($ext == '') {
					// legacy
					$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
				} else {
					// preferred
					$images = glob('./uploads/item_pics/' . $item->pic_filename);
				}

				if (sizeof($images) > 0) {
					$image .= '<a class="rollover" href="' . base_url($images[0]) . '"><img src="' . site_url('items/pic_thumb/' . pathinfo($images[0], PATHINFO_BASENAME)) . '"></a>';
				}
			}


			return array(
				'batch' => ($item->batch==''||$item->batch==null)?'<a class="modal-dlg" href="'.site_url("items/view_expiry_batch/$item->item_id").'">View</a>':$item->batch,
				'items.item_id' => $item->item_id,
				'item_number' => $item->item_number,
				'name' => $item->name,
				'category' => $item->category,
				'company_name' => $item->company_name,
				'pack' => to_quantity_decimals($item->pack),
				'cost_price' => to_currency($item->cost_price),
				'unit_price' => to_currency($item->unit_price),
				'whole_price' => to_currency($item->whole_price),
				'quantity' => to_quantity_decimals($item->quantity),
				'tax_percents' => !$tax_percents ? '-' : $tax_percents,
				'inventory' => anchor(
					$controller_name . "/inventory/$item->item_id",
					'<span class="glyphicon glyphicon-pushpin"></span>',
					array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_count'))
				),
				'stock' => anchor(
					$controller_name . "/count_details/$item->item_id",
					'<span class="glyphicon glyphicon-list-alt"></span>',
					array('class' => 'modal-dlg', 'title' => $CI->lang->line($controller_name . '_details_count'))
				),
				/*'edit' => anchor($controller_name."/view/$item->item_id", '<span class="glyphicon glyphicon-edit"></span>',
						array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
					)*/
			);
		} else {
			return array();
		}
	}
}

function get_expense_data_row($expense, $controller)
{

	// [id] => 1
    // [expense_category_id] => 1
    // [details] => Electrical Works for us
    // [receipt_no] => 1234
    // [amount] => 5000.00
    // [type] => CREDIT
    // [balance] => 10000.00
    // [location_id] => 2
    // [employee_id] => 28
    // [employee_name] => Giwa  Ade
	// [category_name] => Eletrical Works

	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	
	return array(
		'expense.id' => $expense->id,
		'date' => $expense->created_at,
		'details' => $expense->details,
		'receipt_no' => $expense->receipt_no,
		'category_name' => $expense->category_name . " &nbsp; &nbsp; &nbsp; <small> (" . $expense->category_type . ")</small>",
		'employee_name' => $expense->employee_name,
		'type' => $expense->type,
		'amount' => to_currency($expense->amount),
		'balance' => to_currency($expense->balance),
		// 'edit' => anchor(
		// 	$controller_name . "/view_update/$expense->id",
		// 	'<span class="glyphicon glyphicon-edit"></span>',
		// 	array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		// )
	);
}


function get_giftcards_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('giftcard_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('giftcard_number' => $CI->lang->line('giftcards_giftcard_number')),
		array('value' => $CI->lang->line('giftcards_card_value'))
	);

	return transform_headers($headers);
}

function get_taxes_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('tax_code' => $CI->lang->line('taxes_tax_code')),
		array('tax_code_name' => $CI->lang->line('taxes_tax_code_name')),
		array('tax_code_type_name' => $CI->lang->line('taxes_tax_code_type')),
		array('tax_rate' => $CI->lang->line('taxes_tax_rate')),
		array('rounding_code_name' => $CI->lang->line('taxes_rounding_code')),
		array('city' => $CI->lang->line('common_city')),
		array('state' => $CI->lang->line('common_state'))
	);

	return transform_headers($headers);
}

function get_giftcard_data_row($giftcard, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'giftcard_id' => $giftcard->giftcard_id,
		'last_name' => $giftcard->last_name,
		'first_name' => $giftcard->first_name,
		'giftcard_number' => $giftcard->giftcard_number,
		'value' => to_currency($giftcard->value),
		'edit' => anchor(
			$controller_name . "/view/$giftcard->giftcard_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}

function get_tax_data_row($tax_code_row, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'tax_code' => $tax_code_row->tax_code,
		'tax_code_name' => $tax_code_row->tax_code_name,
		'tax_code_type' => $tax_code_row->tax_code_type,
		'tax_rate' => $tax_code_row->tax_rate,
		'rounding_code' => $tax_code_row->rounding_code,
		'tax_code_type_name' => $CI->Tax->get_tax_code_type_name($tax_code_row->tax_code_type),
		'rounding_code_name' => $CI->get_rounding_code_name($tax_code_row->rounding_code),
		'city' => $tax_code_row->city,
		'state' => $tax_code_row->state,
		'edit' => anchor(
			$controller_name . "/view/$tax_code_row->tax_code",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}

function get_item_kits_manage_table_headers()
{
	$CI = &get_instance();

	$headers = array(
		array('item_kit_id' => $CI->lang->line('item_kits_kit')),
		array('name' => $CI->lang->line('item_kits_name')),
		array('description' => $CI->lang->line('item_kits_description')),
		array('cost_price' => $CI->lang->line('items_cost_price'), 'sortable' => FALSE),
		array('unit_price' => $CI->lang->line('items_unit_price'), 'sortable' => FALSE)
	);

	return transform_headers($headers);
}

function get_item_kit_data_row($item_kit, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	return array(
		'item_kit_id' => $item_kit->item_kit_id,
		'name' => $item_kit->name,
		'description' => $item_kit->description,
		'cost_price' => to_currency($item_kit->total_cost_price),
		'unit_price' => to_currency($item_kit->total_unit_price),
		'edit' => anchor(
			$controller_name . "/view/$item_kit->item_kit_id",
			'<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name . '_update'))
		)
	);
}

function get_patients_headers()
{
	$CI = &get_instance();
	$headers = array(

		array('person_id' 		=> 'Person ID'),
		array('first_name'		=> 'First Name'),
		array('last_name'		=> 'Last Name'),
		array('phone_number' 	=> 'Phone Number'),
		array('view'			=> 'Action')
	);
	return transform_headers($headers);
}

function get_patients_row($item, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$id = $item['person_id'];
	return array(
		'people.person_id' => $id,
		'person_id'		=> $id,
		'first_name'	=> $item['first_name'],
		'last_name'		=> $item['last_name'],
		'phone_number'	=> $item['phone_number'],
		'view'			=> anchor($controller_name . "/view_patient_data/$id", 'view items', 'title="View Patient Data"')
	);
}
