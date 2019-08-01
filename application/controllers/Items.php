<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Items extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('items');

		$this->load->library('item_lib');
		$this->load->library('sale_lib');
	}
	
	public function index()
	{
		
		//$this->sale_lib->clear_all();
		$filters = array('empty_upc' => FALSE,
			'low_inventory' => FALSE,
			'no_description'  => FALSE,
			'reorder_level'  => FALSE,
			'expiry' => FALSE,
			'apply_vat' => FALSE);
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filtes = array_merge($filters, $filledup);
			
		$item_location = $this->sale_lib->get_sale_location();
		$data['sale_stuff'] = $this->Item->get_sales();
		$data['notice'] = $this->sale_lib->notice_transfer_items($item_location);
		$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers());
		//$data['table_headees'] = $this->xss_clean(get_items_manage_table_heders());
		$data['transfer'] = $this->sale_lib->global_transfer_items($item_location);
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array('empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'reorder_level' => 'Reorder level items',
			'expiry' => 'Expiring',
			'apply_vat' => 'VATable Items');
		

		$this->load->view('items/manage', $data);
	}
	public function receive_transfer(){
		// Save the data to the sales table
		
			$transfer_id=$this->input->post('transfer_id');
			$transfer_info=$this->Item->get_info_transfer($transfer_id);
			$this->sale_lib->set_transfer_id($transfer_id);
			
			$transfer_type=$transfer_info->transfer_type;
			//$data['cart']=array ( 1 => array ( 'item_id' => 1330,'item_location' => 1,'stock_name' => 'warehouse','line'=> 1, 'name' => 'Booyks','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 24.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0,'tax_category_id' => 0 ), 2 => array ( 'item_id' => 1331,'item_location' => 1,'stock_name' => 'warehouse','line' => 2, 'name' => 'Panadole','allow_alt_description' => 0,'is_serialized' => 0,'quantity' => 1,'discount' => 0,'in_stock' => 10.000,'price' => 4900,'wholeprice' => 3000,'total' => 8000,'discounted_total' => 7000,'print_option' => 1,'stock_type' => 0, 'tax_category_id' => 0 ) ) ;
			
			$data['cart'] = $this->sale_lib->get_pushedcart_reordered($transfer_id);
			if($transfer_type=="PULL"){
				$data['cart'] = $this->sale_lib->get_pulledcart_reordered($transfer_id);
				$this->load->view('items/push_pull', $data);
			}
			if($transfer_type=="PUSH"){
				$this->load->view('items/receive_transfer', $data);
			}
			$data['transfer_type'] =$transfer_type;
			//$customer_id=23;
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			
			
		//$this->load->view('items/receive_transfer', $data);
	}
	public function categories(){
		// Save the data to the sales table
		
			
			
			
		$this->load->view('items/category', $data);
	}
	public function categories_list(){
		$cat_items = array();
		$data=$this->Item->categories_list();
		foreach($data as $row=>$value){
			
			$cat_items[] = array('id' => $value['id'], 'name' => $value['name'], 'edit' =>'<button type="button" id="getEdit" class="btn btn-primary btn-xs edit_cat" data-toggle="modal" data-target="#myModal" data-id="'.$value['id'].'"><i class="glyphicon glyphicon-pencil">&nbsp;</i>Edit</button><a href="index.php?delete='.$value['id'].'" onclick="return confirm(\'Are You Sure ?\')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash">&nbsp;</i>Delete</a>','delete' =>'');
			
		}
		$results = array(
			"sEcho" => 1,
        "iTotalRecords" => count($cat_items),
        "iTotalDisplayRecords" => count($cat_items),
          "aaData"=>$cat_items);
	 
		echo json_encode($results);
	}

	/*
	Returns Items table data rows. This will be called with AJAX.
	*/
	
	public function global_item_push_transfer(){
		$data=array();
		$push=array();
		$unique_location=array();
		
		$push=$this->sale_lib->get_push();
		$transfer_location=$this->sale_lib->get_sale_location();
		foreach($push as $row=>$value){
			array_push($data,$value['branch_transfer']);
			//$check[]=$value['location'];
		}
		$unique_location=array_unique($data);
		foreach($unique_location as $row=>$value){
	
			$transfer_data = array();
			$items=0;
			$transfer_type = 'PUSH';
			
			$pushed_to_branch = $value;
			$push_from_branch = $transfer_location;
			$status=0;
			$this->Sale->save_transfer($push,$items,$transfer_type, $push_from_branch,$pushed_to_branch,$status, $transfer_id);
		}
		
		
		/*$data['sale_id_num'] = $this->Sale->save_transfer($items,$quantity,$request_branch,$transfer_branch,$status, $transfer_id);
				
				$data = $this->xss_clean($data);

				if($data['sale_id_num'] == -1)
				{
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				}*/
				

		
		/*if($this->Sale->save_transfer($items,$request_branch,$transfer_branch,$status, $transfer_id))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}*/
				$this->sale_lib->clear_all_push();
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
		
	}
	public function global_item_pull_transfer(){
		$data=array();
		$pull=array();
		$pull_data=array();
		$unique_location=array();
		
		$pull=$this->sale_lib->get_push();
		$transfer_location=$this->sale_lib->get_sale_location();
		foreach($pull as $row=>$value){
			array_push($pull_data,$value['branch_transfer']);
			//$check[]=$value['location'];
		}
		$unique_location=array_unique($pull_data);
		foreach($unique_location as $row=>$value){
	
			$transfer_data = array();
			$items=0;
			$transfer_type = 'PULL';
			
			$pushed_to_branch = $value;
			$push_from_branch = $transfer_location;
			$status=0;
			$this->Sale->save_pull_transfer($pull,$items,$transfer_type, $push_from_branch,$pushed_to_branch,$status, $transfer_id);
		}
		
		
		/*$data['sale_id_num'] = $this->Sale->save_transfer($items,$quantity,$request_branch,$transfer_branch,$status, $transfer_id);
				
				$data = $this->xss_clean($data);

				if($data['sale_id_num'] == -1)
				{
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				}*/
				

		
		/*if($this->Sale->save_transfer($items,$request_branch,$transfer_branch,$status, $transfer_id))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}*/
				$this->sale_lib->clear_all_push();
		$data['pull'] = $this->sale_lib->get_push();

		$this->load->view('items/pull', $data);
		
	}

	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$this->item_lib->set_item_location($this->input->get('stock_location'));

		$filters = array('start_date' => $this->input->get('start_date'),
						'end_date' => $this->input->get('end_date'),
						'stock_location_id' => $this->item_lib->get_item_location(),
						'empty_upc' => FALSE,
						'low_inventory' => FALSE, 
						'is_serialized' => FALSE,
						'no_description' => FALSE,
						'search_custom' => FALSE,
						'is_deleted' => FALSE);
		
		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		//$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers($filters));
		
		$items = $this->Item->search($search, $filters, $limit, $offset, $sort, $order);

		$total_rows = $this->Item->get_found_rows($search, $filters);

		$data_rows = array();
		foreach($items->result() as $item)
		{
			if(count(get_item_data_row($item, $this)) !=0){
				$data_rows[] = $this->xss_clean(get_item_data_row($item, $this));
				if($item->pic_filename!='')
				{
					$this->_update_pic_filename($item);
				}
			}
		}
	//$this->load->view('items/manage', $data);
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
	public function pic_thumb($pic_filename)
	{
		$this->load->helper('file');
		$this->load->library('image_lib');

		// in this context, $pic_filename always has .ext
		$ext = pathinfo($pic_filename, PATHINFO_EXTENSION);
		$images = glob('./uploads/item_pics/' . $pic_filename);

		// make sure we pick only the file name, without extension
		$base_path = './uploads/item_pics/' . pathinfo($pic_filename, PATHINFO_FILENAME);
		if(sizeof($images) > 0)
		{
			$image_path = $images[0];
			$thumb_path = $base_path . $this->image_lib->thumb_marker . '.' . $ext;
			if(sizeof($images) < 2)
			{
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;
				$this->image_lib->initialize($config);
				$image = $this->image_lib->resize();
				$thumb_path = $this->image_lib->full_dst_path;
			}
			$this->output->set_content_type(get_mime_by_extension($thumb_path));
			$this->output->set_output(file_get_contents($thumb_path));
		}
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions($this->input->post_get('term'),
			array('search_custom' => $this->input->post('search_custom'), 'is_deleted' => $this->input->post('is_deleted') != NULL), FALSE));

		echo json_encode($suggestions);
	}

	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions($this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));

		echo json_encode($suggestions);
	}
	public function complete_push()
	{
		/*$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['push'] = $this->sale_lib->get_push();
		
		$data['sale_id_num'] = $this->Sale->save($data['push']);
		$data['sale_id'] = 'POS ' . $data['sale_id_num'];

				// Resort and filter cart lines for printing
		//$data['push'] = $this->sale_lib->sort_and_filter_cart($data['push']);

		$data = $this->xss_clean($data);
		if($data['sale_id_num'] == -1)
		{
			$data['error_message'] = $this->lang->line('sales_transaction_failed');
		}
		
		*/
		
	}


	public function suggest_kits()
	{
		$suggestions = $this->xss_clean($this->Item->get_kit_search_suggestions($this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));

		echo json_encode($suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_category()
	{
		$suggestions = $this->xss_clean($this->Item->get_category_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest_location()
	{
		$suggestions = $this->xss_clean($this->Item->get_location_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest_custom()
	{
		$suggestions = $this->xss_clean($this->Item->get_custom_suggestions($this->input->post('term'), $this->input->post('field_no')));

		echo json_encode($suggestions);
	}

	public function get_row($item_ids)
	{
		$item_infos = $this->Item->get_multiple_info(explode(":", $item_ids), $this->item_lib->get_item_location());

		$result = array();
		foreach($item_infos->result() as $item_info)
		{
			$result[$item_info->item_id] = $this->xss_clean(get_item_data_row($item_info, $this));
		}

		echo json_encode($result);
	}
	public function view_update($item_id = -1)
	{
		$data['item_tax_info'] = $this->xss_clean($this->Item_taxes->get_info($item_id));
		$data['default_tax_1_rate'] = '';
		$data['default_tax_2_rate'] = '';
		$data['item_kits_enabled'] = $this->Employee->has_grant('item_kits', $this->Employee->get_logged_in_employee_info()->person_id);

		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		

		if($item_id == -1)
		{
			$data['default_tax_1_rate'] = $this->config->item('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->item('default_tax_2_rate');

			$item_info->receiving_quantity = 0;
			$item_info->reorder_level = 0;
			$item_info->item_type = '0'; // standard
			$item_info->stock_type = '0'; // stock
			$item_info->tax_category_id = 0;
		}

		$data['item_info'] = $item_info;

		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['company_name']);
		}
		$categories = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all_categories()->result_array() as $row)
		{
			$categories[$this->xss_clean($row['name'])] = $this->xss_clean($row['name']);
		}
		$data['categories'] = $categories;
		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $item_info->supplier_id;
		$data['selected_category'] = $item_info->category;
		$data['selected_period'] = $item_info->period;

		$customer_sales_tax_support = $this->config->item('customer_sales_tax_support');
		if($customer_sales_tax_support == '1')
		{
			$data['customer_sales_tax_enabled'] = TRUE;
			$tax_categories = array();
			foreach($this->Tax->get_all_tax_categories()->result_array() as $row)
			{
				$tax_categories[$this->xss_clean($row['tax_category_id'])] = $this->xss_clean($row['tax_category']);
			}
			$data['tax_categories'] = $tax_categories;
			$data['selected_tax_category'] = $item_info->tax_category_id;
		}
		else
		{
			$data['customer_sales_tax_enabled'] = FALSE;
			$data['tax_categories'] = array();
			$data['selected_tax_category'] = '';
		}

		$data['logo_exists'] = $item_info->pic_filename != '';
		$ext = pathinfo($item_info->pic_filename, PATHINFO_EXTENSION);
		if($ext == '')
		{
			// if file extension is not found guess it (legacy)
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename . '.*');
		}
		else
		{
			// else just pick that file
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename);
		}
		$data['image_path'] = sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		
		$item_location = $this->sale_lib->get_sale_location();
		$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity);
		$quantity = ($item_id == -1) ? 0 : $quantity;
		//$location_name=$this->CI->Stock_location->get_location_name($item_location)
		$location_array[$item_location] = array('location_name' => $item_location, 'quantity' => $quantity);
		$per_pack=$item_info->pack;
		$item_info->package=$quantity/$per_pack;
		$data['stock_locations'] = $location_array;
		$period=array('days'=>'days',
						'week'=>'Weeks',
					   'month'=>'Months');
		$data['period'] = $period;
		$product_type=array(''=>'Select',
							'Syrup'		=>	'Syrup',
							'Tablet'	=>	'Tablet',
							'Eyedrops'	=>	'Eye drops',
							'Rub'		=>	'Rub',
							'Injection'	=>	'Injection',
							'Capsules'	=>	'Capsules',
							'Inhaler'	=>	'Inhaler',
							'Suspension'	=> 'Suspension',
							'Cream'		=>	'Cream',
							'Ointment'	=>	'Ointment',
							'Pant'		=> 'Pant',
							'Sweet'		=> 'Sweet',
							'Elixir'	=> 'Elixir',
							'Tonic'		=> 'Tonic',
							'Mixture'	=> 'Mixture',
							'Suppository'	=> 'Suppository',
							'Pessaries'		=> 'Pessaries',
							'Medical Device'	=> 'Medical Device',
							'Gel'				=> 'Gel',
							'Eye / Ear Drop'	=> 'Eye / Ear Drop',
							'Lotion'			=> 'Lotion',
							'Effrer'			=> 'Effrer',
							'Powder'			=> 'Powder',
					);
		$data['period'] = $period;
		$data['selected_period'] = $item_info->period;
		$data['selected_product_type'] = $item_info->formulation;
		$data['product_type'] = $product_type;

		$this->load->view('items/form_update', $data);
	}

	public function view($item_id = -1)
	{
		$data['item_tax_info'] = $this->xss_clean($this->Item_taxes->get_info($item_id));
		$data['default_tax_1_rate'] = '';
		$data['default_tax_2_rate'] = '';
		$data['item_kits_enabled'] = $this->Employee->has_grant('item_kits', $this->Employee->get_logged_in_employee_info()->person_id);

		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		

		if($item_id == -1)
		{
			$data['default_tax_1_rate'] = $this->config->item('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->item('default_tax_2_rate');

			$item_info->receiving_quantity = 0;
			$item_info->reorder_level = 0;
			$item_info->item_type = '0'; // standard
			$item_info->stock_type = '0'; // stock
			$item_info->tax_category_id = 0;
		}

		$data['item_info'] = $item_info;

		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['company_name']);
		}
		$categories = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all_categories()->result_array() as $row)
		{
			$categories[$this->xss_clean($row['name'])] = $this->xss_clean($row['name']);
		}
		$data['categories'] = $categories;
		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $item_info->supplier_id;
		$data['selected_category'] = $item_info->category;
		$data['selected_period'] = $item_info->period;

		$customer_sales_tax_support = $this->config->item('customer_sales_tax_support');
		if($customer_sales_tax_support == '1')
		{
			$data['customer_sales_tax_enabled'] = TRUE;
			$tax_categories = array();
			foreach($this->Tax->get_all_tax_categories()->result_array() as $row)
			{
				$tax_categories[$this->xss_clean($row['tax_category_id'])] = $this->xss_clean($row['tax_category']);
			}
			$data['tax_categories'] = $tax_categories;
			$data['selected_tax_category'] = $item_info->tax_category_id;
		}
		else
		{
			$data['customer_sales_tax_enabled'] = FALSE;
			$data['tax_categories'] = array();
			$data['selected_tax_category'] = '';
		}

		$data['logo_exists'] = $item_info->pic_filename != '';
		$ext = pathinfo($item_info->pic_filename, PATHINFO_EXTENSION);
		if($ext == '')
		{
			// if file extension is not found guess it (legacy)
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename . '.*');
		}
		else
		{
			// else just pick that file
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename);
		}
		$data['image_path'] = sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		
		$item_location = $this->sale_lib->get_sale_location();
		$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $item_location)->quantity);
		$quantity = ($item_id == -1) ? 0 : $quantity;
		//$location_name=$this->CI->Stock_location->get_location_name($item_location)
		$location_array[$item_location] = array('location_name' => $item_location, 'quantity' => $quantity);
		$per_pack=$item_info->pack;
		$item_info->package=$quantity/$per_pack;
		$data['stock_locations'] = $location_array;
		$period=array('days'=>'days',
						'week'=>'Weeks',
					   'month'=>'Months');
		$data['period'] = $period;
		$product_type=array(''=>'Select',
						'syrup'=>'Syrup',
					   'tablet'=>'Tablet',
					   'Eyedrops'=>'Eye drops',
					   'Rub'=>'Rub',
					   'Injection'=>'Injection',
					   'Capsules'=>'Capsules',
					   'Inhaler'=>'Inhaler');
		$data['period'] = $period;
		$data['selected_period'] = $item_info->period;
		$data['selected_product_type'] = $item_info->formulation;
		$data['product_type'] = $product_type;

		$this->load->view('items/form', $data);
	}
	public function item_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		if($this->sale_lib->get_mode() == 'return' && $this->Sale->is_valid_receipt($receipt))
		{
			// if a valid receipt or invoice was found the search term will be replaced with a receipt number (POS #)
			$suggestions[] = $receipt;
		}
		$suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}
	public function batch_search()
	{
		$suggestions = array();
		$search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		$suggestions = array_merge($suggestions, $this->Item->get_batchsearch_suggestions($search));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}
	public function edit_item_received($line_id)
	{
		$data = array();
		$no_of_batch=$this->input->post('no_of_batch') == '' ? 0 : $this->input->post('no_of_batch');
		$value_changed= $this->input->post('value_changed');
		$reference= $this->input->post('reference');
		$line= $this->input->post('line');
		$item_id= $this->input->post('item_id');
		$quantity= $this->input->post('quantity');
		$request_from_branch_id= $this->input->post('request_from_branch_id');
		$request_to_branch_id= $this->input->post('request_to_branch_id');
		$received_quantity= $this->input->post('received_quantity');;
		$transfer_id= $this->input->post('transfer_id');
		$received_batch_quantity= $this->input->post('received_batch_quantity') == '' ? 0 : $this->input->post('received_batch_quantity');
		$unaccounted= $this->input->post('unaccounted');
		$batch_no= $this->input->post('batch_no');
		$expiry= $this->input->post('expiry');
		$itemset = $this->sale_lib->get_push();
		
		if ($no_of_batch==0 && $reference==0 && $received_quantity!=0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_received_items($line, $received_quantity,$batch_no,$expiry=null);
		}
		if (($reference==0)&&($no_of_batch!=0)){
			foreach($itemset as $row=>$value){
				if($value['reference']==$line){
					$this->sale_lib->delete_item_push($row);
					//$this->delete_received_item($row);
				}
			}
			
			for ($i = 0; $i < $no_of_batch; $i++){
				$this->sale_lib->add_reciever_push($item_id, $quantity, $request_from_branch_id,$request_to_branch_id,$transfer_id,$received_batch_quantity,$unaccounted,$line,$batch_no,$expiry);
				}
		}
		
		if ($reference!=0 && $no_of_batch==0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_received_items($line, $received_batch_quantity,$batch_no,$expiry);
		}
			
		$data['line']=$line;
		$data['received_batch_quantity']=$received_batch_quantity;
		$data['reference']=$reference;
		$data['value_changed']=$value_changed;
		$data['no_of_batch']=$no_of_batch;
		$data['received_quantity']=$received_quantity;
		
		
		

		
		$data['cart']=$this->sale_lib->get_push();

		$this->load->view('items/receive_transfer', $data);
	}
	public function edit_item_batch_pushed($line_id)
	{
		$data = array();
		
		
		$item_location = $this->sale_lib->get_sale_location();
		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		

		$location = $this->input->post('location');
		$branch_transfer = $this->sale_lib->get_transfer_branch_id($location);
		$item_name = $this->input->post('item_name');
		
		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno= parse_decimals($this->input->post('stockno'));
		$quantit = parse_decimals($this->input->post('quantity'));
		
			$quantity = parse_decimals($this->input->post('quantity'));
		
		
		$item_location = $this->input->post('item_location');

		/*if($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item_push($item_id, $item_name, $item_location, $quantity, $location, $branch_transfer);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);*/
		
		
		
		
		$batch_id=$this->input->post('batch') == '' ? 0 : $this->input->post('batch');
		$batch_info = $this->Item->get_info_batch($batch_id);
		
		$batch_no=$batch_info->batch_no;
		$expiry=$batch_info->expiry;
		//$value_changed= $this->input->post('value_changed');
		$reference= $this->input->post('reference');
		$line= $this->input->post('line');
		$item_id= $this->input->post('item_id');
		//$quantity= $this->input->post('quantity');
		$request_from_branch_id= $this->input->post('request_from_branch_id');
		$request_to_branch_id= $this->input->post('request_to_branch_id');
		$received_quantity= $this->input->post('received_quantity');;
		$transfer_id= $this->input->post('transfer_id');
		$received_batch_quantity= $this->input->post('received_batch_quantity') == '' ? 0 : $this->input->post('received_batch_quantity');
		$unaccounted= $this->input->post('unaccounted');
		//$batch_no= $this->input->post('batch_no');
		//$expiry= $this->input->post('expiry');
		$itemset = $this->sale_lib->get_push();
		
		if ($batch_id==0 && $reference==0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_item_push($line, $item_name, $item_location, $quantity, $location, $branch_transfer,$batch_no=0,$expiry=null);
			//$this->sale_lib->edit_pushed_items($line, $quantity,$batch_no=0,$expiry=null);
		}
		if (($reference==0)&&($batch_id!=0)){
			
			$this->sale_lib->add_item_push($item_id, $quantity, $request_from_branch_id,$request_to_branch_id,$transfer_id,$received_batch_quantity,$unaccounted,$line,$batch_no,$expiry);
				
		}
		
		if ($reference!=0 && $batch_id==0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_pushed_items($line, $batch_no,$expiry);
		}
			
		$data['line']=$line;
		$data['received_batch_quantity']=$received_batch_quantity;
		$data['reference']=$reference;
		$data['value_changed']=$value_changed;
		$data['no_of_batch']=$no_of_batch;
		$data['received_quantity']=$received_quantity;
		$pack_type=array('single'=>'Single','pack'=>'Pack');
		$data['pack_type'] = $pack_type;
		
		
		

		
		$data['push']=$this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}
	public function edit_item_batch_push_pull($line_id)
	{
		$data = array();
		
		
		$item_location = $this->sale_lib->get_sale_location();
		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		

		$location = $this->input->post('location');
		$branch_transfer = $this->sale_lib->get_transfer_branch_id($location);
		$item_name = $this->input->post('item_name');
		
		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno= parse_decimals($this->input->post('stockno'));
		$quantit = parse_decimals($this->input->post('quantity'));
		
			$quantity = parse_decimals($this->input->post('quantity'));
		
		
		$item_location = $this->input->post('item_location');

		/*if($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item_push($item_id, $item_name, $item_location, $quantity, $location, $branch_transfer);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);*/
		
		
		
		
		$batch_id=$this->input->post('batch') == '' ? 0 : $this->input->post('batch');
		$batch_info = $this->Item->get_info_batch($batch_id);
		
		$batch_no=$batch_info->batch_no;
		$expiry=$batch_info->expiry;
		//$value_changed= $this->input->post('value_changed');
		$reference= $this->input->post('reference');
		$line= $this->input->post('line');
		$item_id= $this->input->post('item_id');
		//$quantity= $this->input->post('quantity');
		$request_from_branch_id= $this->input->post('request_from_branch_id');
		$request_to_branch_id= $this->input->post('request_to_branch_id');
		$received_quantity= $this->input->post('received_quantity');
		$transfer_id= $this->input->post('transfer_id');
		$received_batch_quantity= $this->input->post('received_batch_quantity') == '' ? 0 : $this->input->post('received_batch_quantity');
		$unaccounted= $this->input->post('unaccounted');
		//$batch_no= $this->input->post('batch_no');
		//$expiry= $this->input->post('expiry');
		$itemset = $this->sale_lib->get_push();
		
		if ($batch_id==0 && $reference==0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_item_push($line, $item_name, $item_location, $quantity, $location, $branch_transfer,$batch_no=0,$expiry=null);
			//$this->sale_lib->edit_pushed_items($line, $quantity,$batch_no=0,$expiry=null);
		}
		if (($reference==0)&&($batch_id!=0)){
			
			$this->sale_lib->add_item_pull($item_id, $quantity, $request_from_branch_id,$request_to_branch_id,$transfer_id,$received_batch_quantity,$unaccounted,$line,$batch_no,$expiry);
				
		}
		
		if ($reference!=0 && $batch_id==0){
			$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_pushed_items($line, $batch_no,$expiry);
		}
			
		
		
		
		

		
		$data['cart']=$this->sale_lib->get_push();

		$this->load->view('items/push_pull', $data);
	}
	public function edit_item_batch_pull($line_id)
	{
		$data = array();
		
		
		$item_location = $this->sale_lib->get_sale_location();
		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		

		$location = $this->input->post('location');
		$branch_transfer = $this->sale_lib->get_transfer_branch_id($location);
		$item_name = $this->input->post('item_name');
		
		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno= parse_decimals($this->input->post('stockno'));
		//$quantit = parse_decimals($this->input->post('quantity'));
		
		$quantity = parse_decimals($this->input->post('quantity'));
		
		
		$item_location = $this->input->post('item_location');

		/*if($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item_push($item_id, $item_name, $item_location, $quantity, $location, $branch_transfer);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);*/
		
		
		
		
		$batch_id=$this->input->post('batch') == '' ? 0 : $this->input->post('batch');
		$batch_info = $this->Item->get_info_batch($batch_id);
		
		$batch_no=$batch_info->batch_no;
		$expiry=$batch_info->expiry;
		//$value_changed= $this->input->post('value_changed');
		$reference= $this->input->post('reference');
		$line= $this->input->post('line');
		$item_id= $this->input->post('item_id');
		//$quantity= $this->input->post('quantity');
		$request_from_branch_id= $this->input->post('request_from_branch_id');
		$request_to_branch_id= $this->input->post('request_to_branch_id');
		$stock_name=$this->CI->Stock_location->get_location_name($branch_transfer);
		$in_stock=$this->CI->Item_quantity->get_item_quantity($item_id, $branch_transfer)->quantity;
		$received_quantity= $this->input->post('received_quantity');;
		$transfer_id= $this->input->post('transfer_id');
		$received_batch_quantity= $this->input->post('received_batch_quantity') == '' ? 0 : $this->input->post('received_batch_quantity');
		$unaccounted= $this->input->post('unaccounted');
		//$batch_no= $this->input->post('batch_no');
		//$expiry= $this->input->post('expiry');
		$itemset = $this->sale_lib->get_push();
		
		
			//$received_quantity = $this->input->post('received_quantity');
			$this->sale_lib->edit_item_pull($line, $item_name, $item_location, $quantity, $location, $branch_transfer,$stock_name,$in_stock);
			//$this->sale_lib->edit_pushed_items($line, $quantity,$batch_no=0,$expiry=null);
		
		
			
		
		
		

		
		$data['pull']=$this->sale_lib->get_push();

		$this->load->view('items/pull', $data);
	}
	public function receive_transfer_receipt(){
		// Save the data to the sales table
			$data=array();
		
			//$customer_id=$data['person_id'];

			$data['cart']=$this->sale_lib->get_push();
			$transfer_id=$data['transfer_id']=$this->sale_lib->get_transfer_id();
			
			
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee_info = $this->Employee->get_info($employee_id);
			$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
			$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
			$data['sale_status'] = '0'; // Complete
			$this->Sale->receive_transfer_save( $data['cart'],$transfer_id, $employee_id);

			

				$this->load->view('items/receive_receipt', $data);
				$this->sale_lib->clear_all();
		
		//$this->load->view('sales/receipt', $data);add_push
	}
	public function add_push()
	{
		$data = array();
		//$item_inf=array();


		// check if any discount is assigned to the selected customer

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		

		//$location = 'None';
		$quantity = 1;
		$item_location = $this->sale_lib->get_sale_location();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		/*$locating=array();
		$locate = array('location' => $item['location']);
		foreach($locator as $row=>$value)
		{
			$locate[$row] = $value;
									
		}
		$data['locate'] = $locate;*/
		

		
			if(!$this->sale_lib->add_item_push($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location))
			{
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			}
			
			$data['push'] = $this->sale_lib->get_push();
			//$locator = array('' => $this->lang->line('items_none'));
		$locator = array('' => $this->lang->line('items_none'));
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		
			$data['selected_locator'] = $selected_locator;
		$pack_type=array('single'=>'Single','pack'=>'Pack');
		$data['pack_type'] = $pack_type;
		
		$this->load->view('items/push', $data);
	}
	public function add_pull()
	{
		$data = array();
		//$item_inf=array();


		// check if any discount is assigned to the selected customer

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		

		//$location = 'None';
		$quantity = 1;
		$item_location = $this->sale_lib->get_sale_location();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		/*$locating=array();
		$locate = array('location' => $item['location']);
		foreach($locator as $row=>$value)
		{
			$locate[$row] = $value;
									
		}
		$data['locate'] = $locate;*/
		

		
			if(!$this->sale_lib->add_item_push($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location))
			{
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			}
			
			$data['pull'] = $this->sale_lib->get_push();
			//$locator = array('' => $this->lang->line('items_none'));
		$locator = array('' => $this->lang->line('items_none'));
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		
			$data['selected_locator'] = $selected_locator;
		
		$this->load->view('items/pull', $data);
	}
	public function edit_item_push($item_id)
	{
		$data = array();
		$item_location = $this->sale_lib->get_sale_location();
		$locator = array();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		

		$location = $this->input->post('location');
		$branch_transfer = $this->sale_lib->get_transfer_branch_id($location);
		$item_name = $this->input->post('item_name');
		
		//$quantity = parse_decimals($this->input->post('quantity'));
		$stockno= parse_decimals($this->input->post('stockno'));
		$quantit = parse_decimals($this->input->post('quantity'));
		if($quantit>$stockno and $mode != 'return'){
			$quantity =1;
			$data['warning'] = "Warning, Inputed Product Quantity is Insufficient,Reduce quantity to process sale or contact Admin to update inventory";
		}else{
			$quantity = parse_decimals($this->input->post('quantity'));
		}
		
		$item_location = $this->input->post('item_location');

		if($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item_push($item_id, $item_name, $item_location, $quantity, $location, $branch_transfer);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}
	public function cancel()
	{
		$this->sale_lib->clear_all_push();
		$data['push'] = $this->sale_lib->get_push();

		$this->load->view('items/push', $data);
	}
	public function delete_item($item_number)
	{
		$this->sale_lib->delete_item_push($item_number);
		
		$locator = array();
		$item_location = $this->sale_lib->get_sale_location();
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		$data['push'] = $this->sale_lib->get_push();
		$this->load->view('items/push', $data);
	}
	public function delete_pushed_item($item_number)
	{
		$this->sale_lib->delete_item_push($item_number);
		
		$data['push'] = $this->sale_lib->get_push();
		$this->load->view('items/push', $data);
	}
	public function delete_received_item($item_number)
	{
		$this->sale_lib->delete_item_push($item_number);
		
		$data['cart'] = $this->sale_lib->get_push();
		$this->load->view('items/receive_transfer', $data);
	}
	public function delete_reference_item($item_number)
	{
		$this->sale_lib->delete_reference_push($item_number);
		
		$data['cart'] = $this->sale_lib->get_push();
		$this->load->view('items/receive_transfer', $data);
	}


	public function push($item_id = -1)
	{
		//$data['cart'] = $this->sale_lib->get_cart();
		$this->sale_lib->clear_all();
		$data['push'] = $this->sale_lib->get_push();

		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		if($item_id == -1)
		{
			

			$item_info->receiving_quantity = 0;
			$item_info->reorder_level = 0;
			$item_info->item_type = '0'; // standard
			$item_info->stock_type = '0'; // stock
			$item_info->tax_category_id = 0;
		}

		$data['item_info'] = $item_info;

		$locator = array('' => $this->lang->line('items_none'));
		
		$item_location=$this->item_lib->get_item_location();
		$selected_locator = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_loc($item_location)->result_array() as $row)
		{
			$locator[$this->xss_clean($row['location_name'])] = $this->xss_clean($row['location_name']);
			$selected_locator[$this->xss_clean($row['location_id'])] = $this->xss_clean($row['location_id']);
		}
		$data['locator'] = $locator;
		
			$data['selected_locator'] = $selected_locator;

		
		
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id == -1) ? 0 : $quantity;
			$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
			$data['stock_locations'] = $location_array;
		}
		$pack_type=array('single'=>'Single','pack'=>'Pack');
		$data['pack_type'] = $pack_type;

		$this->load->view('items/push', $data);
	}
	public function pull($item_id = -1)
	{
		//$data['cart'] = $this->sale_lib->get_cart();
		$this->sale_lib->clear_all();
		$data['push'] = $this->sale_lib->get_push();
		

		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		if($item_id == -1)
		{

			$item_info->receiving_quantity = 0;
			$item_info->reorder_level = 0;
			$item_info->item_type = '0'; // standard
			$item_info->stock_type = '0'; // stock
			$item_info->tax_category_id = 0;
		}

		$data['item_info'] = $item_info;

		
		
		$data['image_path'] = sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id == -1) ? 0 : $quantity;
			$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
			$data['stock_locations'] = $location_array;
		}

		$this->load->view('items/pull', $data);
	}



	public function inventory($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
		
			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_inventory', $data);
	}
		
	
	public function count_details($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
		
			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_count_details', $data);
	}

	public function generate_barcodes($item_ids)
	{
		$this->load->library('barcode_lib');

		$item_ids = explode(':', $item_ids);
		$result = $this->Item->get_multiple_info($item_ids, $this->item_lib->get_item_location())->result_array();
		$config = $this->barcode_lib->get_barcode_config();

		$data['barcode_config'] = $config;

		// check the list of items to see if any item_number field is empty
		foreach($result as &$item)
		{
			$item = $this->xss_clean($item);
			
			// update the UPC/EAN/ISBN field if empty / NULL with the newly generated barcode
			if(empty($item['item_number']) && $this->config->item('barcode_generate_if_empty'))
			{
				// get the newly generated barcode
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();
				
				$save_item = array('item_number' => $item['item_number']);

				// update the item in the database in order to save the UPC/EAN/ISBN field
				$this->Item->save($save_item, $item['item_id']);
			}
		}
		$data['items'] = $result;

		// display barcodes
		$this->load->view('barcodes/barcode_sheet', $data);
	}

	public function bulk_edit()
	{
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$row = $this->xss_clean($row);

			$suppliers[$row['person_id']] = $row['company_name'];
		}
		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = array(
			'' => $this->lang->line('items_do_nothing'), 
			1  => $this->lang->line('items_change_all_to_allow_alt_desc'),
			0  => $this->lang->line('items_change_all_to_not_allow_allow_desc'));

		$data['serialization_choices'] = array(
			'' => $this->lang->line('items_do_nothing'), 
			1  => $this->lang->line('items_change_all_to_serialized'),
			0  => $this->lang->line('items_change_all_to_unserialized'));

		$this->load->view('items/form_bulk', $data);
	}
	public function save_category($item_id = -1)
	{

		//Save item data
		$item_data = array(
			'name' => $this->input->post('category_name'),
		);

		
		
		$this->Item->save_category($item_data);
		
		$this->load->view('items/category', $data);
	}

	public function save($item_id = -1)
	{
		$upload_success = $this->_handle_image_upload();
		$upload_data = $this->upload->data();
   
		//Save item data
		$item_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'category' => $this->input->post('category'),
			'company' => $this->input->post('company') == '' ? NULL : $this->input->post('company'), 
			'apply_vat' => $this->input->post('apply_vat') == '' ? 'NO' : $this->input->post('apply_vat'),
			'expiry_days' => $this->input->post('expiry_days') == '' ? 0 : $this->input->post('expiry_days'),  
			'grammage' => $this->input->post('grammage') == '' ? NULL : $this->input->post('grammage'), 
			'formulation' => $this->input->post('product_type') == '' ? NULL : $this->input->post('product_type'),  
			'prescriptions' => $this->input->post('prescriptions') == '' ? 'NO' : $this->input->post('prescriptions'), 
			'shelf' => $this->input->post('shelf') == '' ? NULL : $this->input->post('shelf'), 
			'period' => $this->input->post('period') == '' ? 'days' : $this->input->post('period'), 
			'pack' => $this->input->post('items_per_pack'),
			'item_type' => '0',
			'stock_type' => '0',
			'supplier_id' => $this->input->post('supplier_id') == '' ? NULL : $this->input->post('supplier_id'),
			'item_number' => $this->input->post('item_number') == '' ? NULL : $this->input->post('item_number'),
			'cost_price' => parse_decimals($this->input->post('cost_price')),
			'unit_price' => parse_decimals($this->input->post('unit_price')),
			'reorder_level' => parse_decimals($this->input->post('reorder_level')),
			'receiving_quantity' => parse_decimals($this->input->post('receiving_quantity')),
			'allow_alt_description' => 0,
			'is_serialized' => 0,
			'deleted' => 0,
			
			'whole_price' => $this->input->post('whole_price'),
			'custom6' => $this->input->post('custom6') == NULL ? '' : $this->input->post('custom6'),
			'custom7' => $this->input->post('custom7') == NULL ? '' : $this->input->post('custom7'),
			'custom8' => $this->input->post('custom8') == NULL ? '' : $this->input->post('custom8'),
			'custom9' => $this->input->post('custom9') == NULL ? '' : $this->input->post('custom9'),
			'custom10' => $this->input->post('custom10') == NULL ? '' : $this->input->post('custom10')
		);

		$x = $this->input->post('tax_category_id');
		if(!isset($x))
		{
			$item_data['tax_category_id'] = '';
		}
		else
		{
			$item_data['tax_category_id'] = $this->input->post('tax_category_id');
		}
		
		if(!empty($upload_data['orig_name']))
		{
			// XSS file image sanity check
			if($this->xss_clean($upload_data['raw_name'], TRUE) === TRUE)
			{
				$item_data['pic_filename'] = $upload_data['raw_name'];
			}
		}
		
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
		
		if($this->Item->save($item_data, $item_id))
		{
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if($item_id == -1)
			{
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}
			
			
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$count = count($tax_percents);
			for ($k = 0; $k < $count; ++$k)
			{
				$tax_percentage = parse_decimals($tax_percents[$k]);
				if(is_numeric($tax_percentage))
				{
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percentage);
				}
			}
			$success &= $this->Item_taxes->save($items_taxes_data, $item_id);

			//Save item quantity
			$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
			$item_location = $this->sale_lib->get_sale_location();
			$expir=$this->input->post('expiry');
			$bart=$this->input->post('batch_number');
			if($expir!='' && $bart!='' ){
				$expiry_data = array(
						'item_id' => $item_id,
						'expiry' => $this->input->post('expiry'),
						'batch_no' => $this->input->post('batch_number'),
						'location_id' => $item_location,
					);

				 $this->Inventory->insert_expiry($expiry_data);
			}
			
				$updated_quantity = parse_decimals($this->input->post('quantity'));
				$location_detail = array('item_id' => $item_id,
										'location_id' => $item_location,
										'quantity' => $updated_quantity);
				$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $item_location);
				if($item_quantity->quantity != $updated_quantity || $new_item)
				{
					$success &= $this->Item_quantity->save($location_detail, $item_id, $item_location);

					$inv_data = array(
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item_id,
						'trans_user' => $employee_id,
						'trans_location' => $item_location,
						'trans_comment' => $this->lang->line('items_manually_editing_of_quantity'),
						'trans_inventory' => $updated_quantity - $item_quantity->quantity
					);

					$success &= $this->Inventory->insert($inv_data);
				}
			

			if($success && $upload_success)
			{
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			}
			else
			{
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		}
		else//failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);
			
			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}
	public function save_update($item_id = -1)
	{
		$upload_success = $this->_handle_image_upload();
		$upload_data = $this->upload->data();
   
		//Save item data
		$item_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'category' => $this->input->post('category'),
			'company' => $this->input->post('company') == '' ? NULL : $this->input->post('company'), 
			'apply_vat' => $this->input->post('apply_vat') == '' ? 'NO' : $this->input->post('apply_vat'),
			'expiry_days' => $this->input->post('expiry_days') == '' ? 0 : $this->input->post('expiry_days'),  
			'grammage' => $this->input->post('grammage') == '' ? NULL : $this->input->post('grammage'), 
			'formulation' => $this->input->post('product_type') == '' ? NULL : $this->input->post('product_type'),  
			'prescriptions' => $this->input->post('prescriptions') == '' ? 'NO' : $this->input->post('prescriptions'), 
			'shelf' => $this->input->post('shelf') == '' ? NULL : $this->input->post('shelf'), 
			'period' => $this->input->post('period') == '' ? 'days' : $this->input->post('period'), 
			'pack' => $this->input->post('items_per_pack'),
			'item_type' => '0',
			'stock_type' => '0',
			'supplier_id' => $this->input->post('supplier_id') == '' ? NULL : $this->input->post('supplier_id'),
			'item_number' => $this->input->post('item_number') == '' ? NULL : $this->input->post('item_number'),
			'cost_price' => parse_decimals($this->input->post('cost_price')),
			'unit_price' => parse_decimals($this->input->post('unit_price')),
			'reorder_level' => parse_decimals($this->input->post('reorder_level')),
			'receiving_quantity' => parse_decimals($this->input->post('receiving_quantity')),
			'allow_alt_description' => 0,
			'is_serialized' => 0,
			'deleted' => 0,
			
			'whole_price' => $this->input->post('whole_price'),
			'custom6' => $this->input->post('custom6') == NULL ? '' : $this->input->post('custom6'),
			'custom7' => $this->input->post('custom7') == NULL ? '' : $this->input->post('custom7'),
			'custom8' => $this->input->post('custom8') == NULL ? '' : $this->input->post('custom8'),
			'custom9' => $this->input->post('custom9') == NULL ? '' : $this->input->post('custom9'),
			'custom10' => $this->input->post('custom10') == NULL ? '' : $this->input->post('custom10')
		);

		$x = $this->input->post('tax_category_id');
		if(!isset($x))
		{
			$item_data['tax_category_id'] = '';
		}
		else
		{
			$item_data['tax_category_id'] = $this->input->post('tax_category_id');
		}
		
		if(!empty($upload_data['orig_name']))
		{
			// XSS file image sanity check
			if($this->xss_clean($upload_data['raw_name'], TRUE) === TRUE)
			{
				$item_data['pic_filename'] = $upload_data['raw_name'];
			}
		}
		
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);

		$categoryData = $this->Item->get_item_category_by_name($item_data['category']);

		// I removed this section due to the fact that production schema has no category_id column.
		// if ($categoryData === FALSE) {
		// 	echo json_encode(array('success' => FALSE, 'message' => 'Unknown category selected! Please select a valid category.'));
		// 	exit;
		// }
		// $item_data['category_id'] = $categoryData->id;
		$item_data['slug'] = str_replace(' ', '-', strtolower($item_data['name']));

		// get the item from db; for update scenarios
		$existingItem = $this->Item->get_item_by_item_number($item_data['item_number']);
		if ( $item_id == -1 ) {
			// creating a new item.
			// check the item number to be sure its unique.
			if ( $existingItem ) {
				// there is an item with the same item_number and it has not been deleted.
				echo json_encode(array('success' => FALSE, 'message' => 'An item with this Batch number' . $item_data['item_number'] . 'already exists!'));
				exit;
			}
		} else {
			// updating not so sure of what to do here!
			// echo json_encode(array('success' => FALSE, 'message' => $existingItem));
			// exit;
			if ( $existingItem ) {
				unset($item_data['item_number']);
			}
		}
		
		if($this->Item->save($item_data, $item_id))
		{
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if($item_id == -1)
			{
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}
			
			
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$count = count($tax_percents);
			for ($k = 0; $k < $count; ++$k)
			{
				$tax_percentage = parse_decimals($tax_percents[$k]);
				if(is_numeric($tax_percentage))
				{
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percentage);
				}
			}
			$success &= $this->Item_taxes->save($items_taxes_data, $item_id);

			//Save item quantity
			$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
			$item_location = $this->sale_lib->get_sale_location();
			
			

			if($success && $upload_success)
			{
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			}
			else
			{
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		}
		else//failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);
			
			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}
	
	public function check_item_number()
	{
		$exists = $this->Item->item_number_exists($this->input->post('item_number'), $this->input->post('item_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	If adding a new item check to see if an item kit with the same name as the item already exists.
	*/
	public function check_kit_exists()
	{
		if ($this->input->post('item_number') === -1)
		{
			$exists = $this->Item_kit->item_kit_exists_for_name($this->input->post('name'));
		}
		else
		{
			$exists = FALSE;
		}
		echo !$exists ? 'true' : 'false';
	}

	private function _handle_image_upload()
	{
		/* Let files be uploaded with their original name */
		
		// load upload library
		$config = array('upload_path' => './uploads/item_pics/',
			'allowed_types' => 'gif|jpg|png',
			'max_size' => '100',
			'max_width' => '640',
			'max_height' => '480'
		);
		$this->load->library('upload', $config);
		$this->upload->do_upload('item_image');
		
		return strlen($this->upload->display_errors()) == 0 || !strcmp($this->upload->display_errors(), '<p>'.$this->lang->line('upload_no_file_selected').'</p>');
	}

	public function remove_logo($item_id)
	{
		$item_data = array('pic_filename' => NULL);
		$result = $this->Item->save($item_data, $item_id);

		echo json_encode(array('success' => $result));
	}

	public function save_inventory($item_id = -1)
	{	
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
		$location_id = $this->input->post('stock_location');
		$inv_data = array(
			'trans_date' => date('Y-m-d H:i:s'),
			'trans_items' => $item_id,
			'trans_user' => $employee_id,
			'trans_location' => $location_id,
			'trans_comment' => $this->input->post('trans_comment'),
			'trans_inventory' => parse_decimals($this->input->post('newquantity'))
		);
		
		$this->Inventory->insert($inv_data);
		
		//Update stock quantity
		$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location_id);
		$item_quantity_data = array(
			'item_id' => $item_id,
			'location_id' => $location_id,
			'quantity' => $item_quantity->quantity + parse_decimals($this->input->post('newquantity'))
		);

		if($this->Item_quantity->save($item_quantity_data, $item_id, $location_id))
		{
			$message = $this->xss_clean($this->lang->line('items_successful_updating') . ' ' . $cur_item_info->name);
			
			echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
		}
		else//failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $cur_item_info->name);
			
			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}

	public function bulk_update()
	{
		$items_to_update = $this->input->post('item_ids');
		$item_data = array();

		foreach($_POST as $key => $value)
		{		
			//This field is nullable, so treat it differently
			if($key == 'supplier_id' && $value != '')
			{	
				$item_data["$key"] = $value;
			}
			elseif($value != '' && !(in_array($key, array('item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data["$key"] = $value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data, $items_to_update))
		{
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_updated = FALSE;
			$count = count($tax_percents);
			for ($k = 0; $k < $count; ++$k)
			{		
				if(!empty($tax_names[$k]) && is_numeric($tax_percents[$k]))
				{
					$tax_updated = TRUE;
					
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percents[$k]);
				}
			}
			
			if($tax_updated)
			{
				$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);
			}

			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('items_successful_bulk_edit'), 'id' => $this->xss_clean($items_to_update)));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_error_updating_multiple')));
		}
	}

	public function delete()
	{
		$items_to_delete = $this->input->post('ids');

		if($this->Item->delete_list($items_to_delete))
		{
			$message = $this->lang->line('items_successful_deleted') . ' ' . count($items_to_delete) . ' ' . $this->lang->line('items_one_or_multiple');
			echo json_encode(array('success' => TRUE, 'message' => $message));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_cannot_be_deleted')));
		}
	}

	/*
	Items import from excel spreadsheet
	*/
	public function excel()
	{
		$name = 'import_items.csv';
		$data = file_get_contents('../' . $name);
		force_download($name, $data);
	}
	
	public function excel_import()
	{
		$this->load->view('items/form_excel_import', NULL);
	}

	public function do_excel_import()
	{
		if($_FILES['file_path']['error'] != UPLOAD_ERR_OK)
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_excel_import_failed')));
		}
		else
		{
			if(($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== FALSE)
			{
				// Skip the first row as it's the table description
				fgetcsv($handle);
				$i = 1;
				
				$failCodes = array();
		
				while(($data = fgetcsv($handle)) !== FALSE)
				{
					// XSS file data sanity check
					$data = $this->xss_clean($data);
					
					/* haven't touched this so old templates will work, or so I guess... */
					if(sizeof($data) >= 23)
					{
						$item_data = array(
							'name'					=> $data[1],
							'description'			=> $data[11],
							'category'				=> $data[2],
							'cost_price'			=> $data[4],
							'unit_price'			=> $data[5],
							'reorder_level'			=> $data[10],
							'supplier_id'			=> $this->Supplier->exists($data[3]) ? $data[3] : NULL,
							'allow_alt_description'	=> $data[12] != '' ? '1' : '0',
							'is_serialized'			=> $data[13] != '' ? '1' : '0',
							'pack'				=> $data[14],
							'custom5'				=> $data[18],
							'custom6'				=> $data[19],
							'custom7'				=> $data[20],
							'custom8'				=> $data[21],
							'custom9'				=> $data[22],
							'custom10'				=> $data[23],
							'prescriptions'				=> $data[27],
							'grammage'				=> $data[28],
							'formulation'				=> $data[29],
							'shelf'				=> $data[30]
						);

						/* we could do something like this, however, the effectiveness of
						  this is rather limited, since for now, you have to upload files manually
						  into that directory, so you really can do whatever you want, this probably
						  needs further discussion  */

						$pic_file = $data[24];
						/*if(strcmp('.htaccess', $pic_file)==0) {
							$pic_file='';
						}*/
						$item_data['pic_filename'] = $pic_file;

						$item_number = $data[0];
						$invalidated = FALSE;
						if($item_number != '')
						{
							$item_data['item_number'] = $item_number;
							$invalidated = $this->Item->item_number_exists($item_number);
						}
					}
					else 
					{
						$invalidated = TRUE;
					}

					if(!$invalidated && $this->Item->save($item_data))
					{
						$items_taxes_data = NULL;
						//tax 1
						if(is_numeric($data[7]) && $data[6] != '')
						{
							$items_taxes_data[] = array('name' => $data[6], 'percent' => $data[7] );
						}

						//tax 2
						if(is_numeric($data[9]) && $data[8] != '')
						{
							$items_taxes_data[] = array('name' => $data[8], 'percent' => $data[9] );
						}

						// save tax values
						if(count($items_taxes_data) > 0)
						{
							$this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
						}

						// quantities & inventory Info
						$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
						$emp_info = $this->Employee->get_info($employee_id);
						$comment ='Qty CSV Imported';

						$cols = count($data);

						// array to store information if location got a quantity
						$allowed_locations = $this->Stock_location->get_allowed_locations();
						for ($col = 25; $col < $cols; $col = $col + 2)
						{
							$location_id = $data[$col];
							if(array_key_exists($location_id, $allowed_locations))
							{
								$item_quantity_data = array(
									'item_id' => $item_data['item_id'],
									'location_id' => $location_id,
									'quantity' => $data[$col + 1],
								);
								$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $location_id);

								$excel_data = array(
									'trans_items' => $item_data['item_id'],
									'trans_user' => $employee_id,
									'trans_comment' => $comment,
									'trans_location' => $data[$col],
									'trans_inventory' => $data[$col + 1]
								);
								
								$this->Inventory->insert($excel_data);
								unset($allowed_locations[$location_id]);
							}
						}

						/*
						 * now iterate through the array and check for which location_id no entry into item_quantities was made yet
						 * those get an entry with quantity as 0.
						 * unfortunately a bit duplicate code from above...
						 */
						foreach($allowed_locations as $location_id => $location_name)
						{
							$item_quantity_data = array(
								'item_id' => $item_data['item_id'],
								'location_id' => $location_id,
								'quantity' => 0,
							);
							$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $data[$col]);

							$excel_data = array(
								'trans_items' => $item_data['item_id'],
								'trans_user' => $employee_id,
								'trans_comment' => $comment,
								'trans_location' => $location_id,
								'trans_inventory' => 0
							);

							$this->Inventory->insert($excel_data);
						}
					}
					else //insert or update item failure
					{
						$failCodes[] = $i;
					}

					++$i;
				}

				if(count($failCodes) > 0)
				{
					$message = $this->lang->line('items_excel_import_partially_failed') . ' (' . count($failCodes) . '): ' . implode(', ', $failCodes);
					
					echo json_encode(array('success' => FALSE, 'message' => $message));
				}
				else
				{
					echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('items_excel_import_success')));
				}
			}
			else 
			{
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_excel_import_nodata_wrongformat')));
			}
		}
	}

	/**
	 * Guess whether file extension is not in the table field,
	 * if it isn't, then it's an old-format (formerly pic_id) field,
	 * so we guess the right filename and update the table
	 * @param $item the item to update
	 */
	private function _update_pic_filename($item)
	{
		$filename = pathinfo($item->pic_filename, PATHINFO_FILENAME);
		if($filename=='')
		{
			// if the field is empty there's nothing to check
			return;
		}
		
		$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
		if ($ext == '') {
			$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
			if (sizeof($images) > 0) {
				$new_pic_filename = pathinfo($images[0], PATHINFO_BASENAME);
				$item_data = array('pic_filename' => $new_pic_filename);
				$this->Item->save($item_data, $item->item_id);
			}
		}
	}
}
?>
