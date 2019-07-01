<?php

require_once("Secure_Controller.php");

class Stocks extends Secure_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "<h1>Index</h1>";
        exit;
    }

    public function outofstocks() {
        $data = null;
		$this->db->select('items.name, items.category, items.availability, items.grammage');
		$this->db->select('item_quantities.quantity as quantity');
		$this->db->select('suppliers.company_name');
		$this->db->from('items');
        $this->db->join('item_quantities', 'item_quantities.item_id = items.item_id');
        $this->db->join('suppliers', 'suppliers.person_id = items.supplier_id');
		//$this->db->group_by('stock_locations.location_id');
		// $this->db->where('location_id', $location);
        $this->db->where('quantity', 0);
        $data['items'] = $this->db->get()->result();
        $this->load->view('stocks/outofstock', $data);
    }

    public function ItemsAtReorderLevel() {
        $data = null;
        $this->db->select('items.name, items.category, items.availability, items.grammage');
		$this->db->select('items.reorder_level as reorder_level');
        $this->db->select('item_quantities.quantity as quantity');
        $this->db->select('suppliers.company_name');
		$this->db->from('items');
        $this->db->join('item_quantities as item_quantities', 'item_quantities.item_id = items.item_id');
        $this->db->join('suppliers', 'suppliers.person_id = items.supplier_id');
		//$this->db->group_by('stock_locations.location_id');
		$this->db->where('location_id', $location);
		$this->db->where('quantity <=', 'reorder_level');

        $data['items'] = $this->db->get()->result();
		$this->load->view('stocks/reorderlevel', $data);
    }
}