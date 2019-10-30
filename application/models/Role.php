<?php

class Role extends CI_Model
{

    public function exists($roleId)
    {
        $this->db->from('grants');
        $this->db->where('grants.permission_id', $roleId);

        return $this->db->get()->num_rows() == 1;
    }

    public function get_user_role($role_id)
    {

        $this->db->from('role_id');
        $this->db->where('role_id.id', $role_id);
        $row = $this->db->get()->row();
        if (!empty($row)) {
            return $row->role;
        } else {
            return '';
        }
    }
}
