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
            $this->db->select('items.name as name, items.item_id as id, SUM(quantity_purchased) as quantity, SUM(item_unit_price) as price, sales_items.discount_percent as discount');
            $this->db->from('items');
            $this->db->join('sales_items', 'sales_items.item_id = items.item_id', 'left');
            $this->db->join('sales', 'sales_items.sale_id = sales.sale_id', 'left');
            if ( !empty($inputs['search']) ) {
                $this->db->group_start();
                    $this->db->like('items.name', $inputs['search']);
                    $this->db->or_like('items.item_id', $inputs['search']);
                    // $this->db->or_like('sales.sale_id', $inputs['search']);
                $this->db->group_end();
            }
            if ( $inputs['location_id'] != 'all' ) {
                $this->db->where('sales_items.item_location', $inputs['location_id']);
            } else {
                $this->db->where_in('sales_items.item_location', array('1', '2', '3'));
            }
            // $this->db->where('items.apply_vat', 'YES');
            if ( empty($this->config->item('date_or_time_format')) ) {
                $this->db->where('DATE_FORMAT(sale_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
            } else {
                $this->db->where('sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
            }

            $this->db->group_by($inputs['sort'], $inputs['order']);
            if ($inputs['limit'] > 0) {
                $this->db->limit($inputs['limit'], $inputs['offset']);
            }

            // return $this->db->get()->result_array();
            return $this->db->get();
        }

        public function getDataCount(array $inputs) {
            $this->db->select('items.name as name, items.item_id as id, SUM(quantity_purchased) as quantity, SUM(item_unit_price) as price, sales_items.discount_percent as discount');
            $this->db->from('items');
            $this->db->join('sales_items', 'sales_items.item_id = items.item_id', 'left');
            $this->db->join('sales', 'sales_items.sale_id = sales.sale_id', 'left');
            if ( !empty($inputs['search']) ) {
                $this->db->group_start();
                    $this->db->like('items.name', $inputs['search']);
                    $this->db->or_like('items.item_id', $inputs['search']);
                $this->db->group_end();
            }
            if ( $inputs['location_id'] != 'all' ) {
                $this->db->where('sales_items.item_location', $inputs['location_id']);
            } else {
                $this->db->where_in('sales_items.item_location', array('1', '2', '3'));
            }
            // $this->db->where('items.apply_vat', 'YES');
            if ( empty($this->config->item('date_or_time_format')) ) {
                $this->db->where('DATE_FORMAT(sale_time, "%Y-%m-%d") BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
            } else {
                $this->db->where('sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
            }
            $this->db->group_by($inputs['sort'], $inputs['order']);
            $query = $this->db->get();
            return $query->num_rows();
        }
    }

?>