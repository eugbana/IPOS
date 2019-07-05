<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require_once("Summary_report.php");

    class Summary_vat extends Summary_report {
        function __construct() {
            parent::__construct();
        }

        protected function _get_data_columns() {
            return array(
                array('discount' => $this->lang->line('reports_discount_percent'), 'sorter' => 'number_sorter'),
                array('count' => $this->lang->line('reports_count')));
        }
    }

?>