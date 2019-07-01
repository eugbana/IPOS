<?php
class Customer_rewards extends CI_Model
{
    public function exists($package_id)
    {
        $this->db->from('customers_packages');  
        $this->db->where('package_id', $package_id);

        return ($this->db->get()->num_rows() >= 1);
    }

    public function save($package_data, $package_id) 
    {
        $name = $package_data['package_name'];
        $points_percent = $package_data['points_percent'];

        if(!$this->exists($package_id))
        {
            $this->db->trans_start();

            $location_data = array('package_name' => $name, 'deleted' => 0, 'points_percent'=>$points_percent);
            $this->db->insert('customers_packages', $package_data);
            $package_id = $this->db->insert_id();

            $this->db->trans_complete();

            return $this->db->trans_status();
        }
        else 
        {
            $this->db->where('package_id', $package_id);

            return $this->db->update('customers_packages', $package_data);
        }
    }
	public function exists_reminder($reminder_id)
    {
        $this->db->from('pill_reminder');  
        $this->db->where('reminder_id', $reminder_id);

        return ($this->db->get()->num_rows() >= 1);
    }
	public function save_reminder($package_data, $reminder_id) 
    {
        $reminder_name = $package_data['reminder_name'];
        $reminder_amount = $package_data['reminder_amount'];
		$reminder_value = $package_data['reminder_value'];

        if(!$this->exists_reminder($reminder_id))
        {
            $this->db->trans_start();

            $location_data = array('reminder_name' => $reminder_name, 'deleted' => 0, 'reminder_amount'=>$reminder_amount, 'reminder_value'=>$reminder_value);
            $this->db->insert('pill_reminder', $package_data);
            $reminder_id = $this->db->insert_id();

            $this->db->trans_complete();

            return $this->db->trans_status();
        }
        else 
        {
            $this->db->where('reminder_id', $reminder_id);

            return $this->db->update('pill_reminder', $package_data);
        }
    }

    public function get_name($package_id)
    {
        $this->db->from('customers_packages');
        $this->db->where('package_id', $package_id);

        return $this->db->get()->row()->package_name;
    }

    public function get_points_percent($package_id)
    {
        $this->db->from('customers_packages');
        $this->db->where('package_id', $package_id);

        return $this->db->get()->row()->points_percent;
    }

    public function get_all()
    {
        $this->db->from('customers_packages');

        return $this->db->get();
    }
	 public function get_all_reminders()
    {
        $this->db->from('pill_reminder');

        return $this->db->get();
    }

    public function get_undeleted_all()
    {
        $this->db->from('customers_packages');
        $this->db->where('deleted', 0);

        return $this->db->get();
    }

    /*
    Deletes one reward
    */
    public function delete($package_id)
    {
        $this->db->trans_start();

        $this->db->where('package_id', $package_id);
        $this->db->update('customers_packages', array('deleted' => 1));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
	 public function delete_reminder($reminder_id)
    {
        $this->db->trans_start();

        $this->db->where('reminder_id', $reminder_id);
        $this->db->update('pill_reminder', array('deleted' => 1));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
?>
