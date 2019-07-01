<?php
    class ItemBatches extends CI_Model {

        public function save(&$data) {
            $this->db->insert('item', $data);
            $data['id'] = $this->db->insert_id();
            return TRUE;
        }
    }