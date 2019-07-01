<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Specific_expiry extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->crate_temp_table($inputs);
	}

	public function getDataColumns()
	{
		$columns = array(
			'summary' => array(
				array('id' => $this->lang->line('reports_expiry_id')),
				array('name' => $this->lang->line('reports_product_description')),
				array('expiry' => $this->lang->line('reports_expiry_date')),
				
				),
			
		);

		return $columns;
	}

	

	public function getData(array $inputs)
	{
		$this->db->select('item_id, 
			MAX(name) as name,
			MAX(expiry) as expiry');
		$this->db->from('receivings_items_temp AS receivings_items_temp');
		
		if($inputs['location_id'] != 'all')
		{
			//$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['receiving_type'] == 'receiving')
		{
			//$this->db->where('quantity_purchased > 0');
		}
		elseif($inputs['receiving_type'] == 'returns')
		{
			//$this->db->where('quantity_purchased < 0');
		}
		elseif($inputs['receiving_type'] == 'requisitions')
		{
			//$this->db->having('items_purchased = 0');
		}
		$this->db->group_by('item_id');
		$this->db->order_by('item_id');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('expiry,name');
			$this->db->from('receivings_items_temp');
			//$this->db->join('items', 'receivings_items_temp.item_id = items.item_id');
			$this->db->where('item_id = '.$value['item_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}

	public function getSummaryData(array $inputs)
	{
		
	}
}
?>
