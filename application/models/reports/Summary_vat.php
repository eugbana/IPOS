<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require_once("Summary_report.php");

    class Summary_vat extends Summary_report {
        function __construct() {
            parent::__construct();
        }

        protected function _get_data_columns() {
            return array(
                array('item'    => $this->lang->line('vat_table_head_item'), 'sorter' => 'number_sorter'),
                array('total'   => $this->lang->line('vat_table_head_total')));
        }

        public function getData(array $inputs) {
            $this->db->select('items.name as name, items.item_id as id, sales_items.quantity_purchased as quantity, sales_items.item_unit_price as price, sales_items.discount_percent as discount');
            $this->db->from('items');
            $this->db->join('sales_items', 'sales_items.item_id = items.item_id', 'left');
            $this->db->join('sales', 'sales_items.sale_id = sales.sale_id', 'left');
            if ( !empty($inputs['search']) ) {
                $this->db->group_start();
                    $this->db->like('items.name', $inputs['search']);
                    $this->db->or_like('items.items_id', $inputs['search']);
                    $this->db->or_like('sales.sale_id', $inputs['search']);
                $this->db->group_end();
            }
            if ( $inputs['location_id'] != 'all' ) {
                $this->db->where('sales_items.item_location', $inputs['location_id']);
            } else {
                $this->db->where_in('sales_items.item_location', array('1', '2', '3'));
            }
            // $this->db->where('items.apply_vat', 'YES');
            $this->db->where("sales.sale_time BETWEEN '" . $inputs['start_date'] . "' AND '" . $inputs['end_date'] . "'");
            $this->db->order_by('items.name');

            // return $this->db->get()->result_array();
            return $this->db->get();
        }
    }

?>