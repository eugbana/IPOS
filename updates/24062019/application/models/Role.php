<?php

    class Role extends CI_Model {

        public function exists($roleId) {
            $this->db->from('grants');
            $this->db->where('grants.permission_id', $roleId);

            return $this->db->get()->num_rows() == 1;
        }
        
    }

?>