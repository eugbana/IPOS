<?php
class Item_quantity extends CI_Model
{

    const FAKE_BATCH_NO = "tonia_pharmacy_and_superstore";
    public function exists($item_id, $location_id)
    {
        $this->db->from('item_quantities');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);


        /*
        //methods that change the quantities of products in the system.
        //these methods are to be synched to the produt expiry table
        save(sale save,sale receive_transfer_save, receiving save,items do_excel_import, items save,items zero_quantity, items save_inventory, laboratory save)
        [dont update batch here. save is called and the call will update batch]change_quantity(sale save_transfer, receiving delete, sale delete)
        reset_quantity(item delete, item delete_list)
        reset_quantity_list(item delete_list)
        zero_quantities_in_batch(config zero_all_quantity)
        */
        return ($this->db->get()->num_rows() == 1);
    }
    public function exists_batch($item_id, $location_id, $batch_no)
    {
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->where('batch_no', $batch_no);

        return ($this->db->get()->num_rows() == 1);
    }
    public function exists_batches($item_id, $location_id)
    {
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);

        return ($this->db->get()->num_rows() > 0);
    }


    public function save($location_detail, $item_id, $location_id, $batch_data = array(),$from_aj=false)
    {
//        return 0;
        if (!$this->exists($item_id, $location_id)) {
//            return 0;
            $status = $this->db->insert('item_quantities', $location_detail);
        } else {

            $this->db->where('item_id', $item_id);
            $this->db->where('location_id', $location_id);
//            return 0;
            $status = $this->db->update('item_quantities', $location_detail);
//            return 0;
        }
        if (empty($batch_data)) { //if $batch_data cannot be empty is the call to this save function is from the Receiving class. quantity will also be included
            $batch_data['item_id'] = $item_id;
            $batch_data['location_id'] = $location_id;
        }
        //kk
        $this->update_batch($batch_data,$from_aj);
        return $status;
    }
    public function delete_batch($item_id, $location_id, $batch_no)
    {
        $this->db->delete('item_expiry', array('item_id' => $item_id, 'location_id' => $location_id, 'batch_no' => $batch_no));
    }
    public function delete_all_item_batches($item_id, $location_id)
    {
        $this->db->delete('item_expiry', array('item_id' => $item_id, 'location_id' => $location_id));
    }
    private function update_batch($batch_data = array(),$from_aj = false)
    {


        //notice that when operation like sales,transfer,delete, or receivings (with no batch no. like receiving returns or receivings to update quantity) is carried out on the item_quantities table as contained in all the function in this class where quantities is changed, 
        //expiry(date is set in the back, with more than 100 days in the back.). this is to differentiate expired and future quantities from quantities that have no batch no in the system.

        if (!empty($batch_data)) { //although no call with empty batch_data is made to this function but this is necessary
            //get the quantity from item_quantities table to use in updating this
            //notice that this function is called in every function in this class where quantities are changed
            //$total_quantity here actually total quantities of this item now in the item_quantities table irrespective of how many batch no is has in the expiry table
            if($from_aj){
                $total_quantity = $batch_data['total_quantity']->quantity;
            }else{
                $total_quantity = $this->get_item_quantity($batch_data['item_id'], $batch_data['location_id'])->quantity;
            }

            //if quantity is zero, just delete all the batches associated with this item
            if ($total_quantity <= 0) {
                $this->delete_all_item_batches($batch_data['item_id'], $batch_data['location_id']);
                return;
            }


//            if($from_aj){
////                $batch_quantity = $batch_data['']
//            }
//            else
                if ($batch_data['batch_no'] != "" && $batch_data['expiry'] !== "") {

                //batch no is new.
                $total_item_quantity_in_this_location = $this->get_total_batch_quantity($batch_data['item_id'], $batch_data['location_id']);
                $new_quantity = $total_quantity - $total_item_quantity_in_this_location; //this is the quanity that just got added into the system

                if ($this->exists_batch($batch_data['item_id'], $batch_data['location_id'], $batch_data['batch_no'])) {

                    $total_batch_quantity_exclusive = $this->get_total_batch_quantity_exclusive($batch_data['item_id'], $batch_data['location_id'], $batch_data['batch_no']); //total of all the batches of this item exluding this batch

//

                    if($from_aj){
                        $available_qty_for_this_batch = $total_quantity - $total_batch_quantity_exclusive;
                        if($batch_data['quantity']> $available_qty_for_this_batch){
                            $qty_diff = $batch_data['quantity'] - $available_qty_for_this_batch;
                            $batch_data_batch_no = $batch_data['batch_no'];
//                            $location = $batch_data['location_id'];
//                            var_dump("$location:  got here update batch exist pass test $qty_diff, avail: $available_qty_for_this_batch, $total_quantity, $total_batch_quantity_exclusive");
//                            exit();
                            while ($qty_diff > 0){
                                $batch_data_expiry = $batch_data['expiry'];
                                $this->db->select('id,quantity')
                                    ->from('item_expiry')
                                    ->where(["item_id"=>$batch_data['item_id'],"location_id"=>$batch_data['location_id']])
                                    ->where("batch_no != '$batch_data_batch_no'")
                                    ->order_by('expiry','ASC');
                                $nb_qty = $this->db->get()->row();
                                if($nb_qty->quantity >= $qty_diff){
                                    $this->db->update('item_expiry',['quantity'=>($nb_qty->quantity - $qty_diff)],['id'=>$nb_qty->id]);
                                    $qty_diff = 0;
                                    break;
                                }else{
                                    $qty_diff -= $nb_qty->quantity;
                                    $this->db->update('item_expiry',['quantity'=>0],['id'=>$nb_qty->id]);
                                }
                            }
                        }
                        $rem_batch_quantity = $available_qty_for_this_batch - $batch_data['quantity'];
                        $this->db->update('item_expiry',['quantity'=>$rem_batch_quantity < 0? 0 : $rem_batch_quantity],['batch_no'=>$batch_data['batch_no'],
                            'item_id'=>$batch_data['item_id'],'location_id'=>$batch_data['location_id']]);
                    }
                    elseif ($new_quantity >= 0) {
                        //normal extra stock intake to batch_no already in the system. inventory person noticing he has not entered all the product in a batch even after submitting the receiving form

                        $new_quantity_only_for_this_batch = $total_quantity - $total_batch_quantity_exclusive; //the quantity of this batch is increased

                        $batch_data['quantity'] =  $new_quantity_only_for_this_batch;
                        $this->db->where("item_id", $batch_data['item_id']);
                        $this->db->where("location_id", $batch_data['location_id']);
                        $this->db->where("batch_no", $batch_data['batch_no']);
                        $this->db->update("item_expiry", $batch_data);
                    } else {
                        //normal stock return for batch already taken

                        //get quantity that was returned
                        $new_quantity = -1 * $new_quantity; ////new_quantity is negative here
                        //remove the returned quantity from the current quantity of this batch
                        $current_batch_quantity = $this->get_batch_quantity($batch_data['item_id'], $batch_data['location_id'], $batch_data['batch_no']);
                        $remaining_batch_quantity = $current_batch_quantity - $new_quantity;
                        if ($remaining_batch_quantity > 0) {
                            $batch_data['quantity'] =  $remaining_batch_quantity;
                            $this->db->where("item_id", $batch_data['item_id']);
                            $this->db->where("location_id", $batch_data['location_id']);
                            $this->db->where("batch_no", $batch_data['batch_no']);
                            $this->db->update("item_expiry", $batch_data);
                        } else {
                            //there is an error here.
                            //an item that is more than what is entered into the system during receiving is rturned

                            //To deal with this, delete this batch
                            $this->delete_batch($batch_data['item_id'], $batch_data['location_id'], $batch_data['batch_no']);
                            //go through the other batches and reduce and delete batches until the quantity of batches match with that of the item_quantities 
                            $this->update_batch_decrease($batch_data, (-1 * $remaining_batch_quantity)); //remaining quantity is negative here
                        }
                    }
                } else {


                    if (isset($batch_data['quantity'])) { //this should be saved.

                        if ($batch_data['quantity'] > 0) {

                            //new stock intake
                            // $total_quantity - $total_batch_quantity_exclusive; should be equal to this quantity

                            //normal stock intake for the product has its batch no has not_existed in the expiry table
                            //in this case
                            $batch_data['quantity'] = $new_quantity;
                            $this->db->insert('item_expiry', $batch_data);

                            //Notice that they might be some quantities of this item in the item_quantities table
                            //which may not have entered the batch table. get all this quantities and enter it in the batch table with fake batch_no and expiry date of ten years ago, so that they would be sold first.
                            //this usually happen if this is the first time this item is making it to the expiry table. this is because they(tonia pharmacy) have already started making use of this system before the product expiry is implemented.
                            //we want to make sure every item is entered into the batch table if not existing
//                            $quantities_not_yet_in_batch_tbl = $total_quantity - $batch_data['quantity'];
//                            if (!$this->exists_batches($batch_data['item_id'], $batch_data['location_id']) && $quantities_not_yet_in_batch_tbl > 0) { //make sure that no other batch is existing
//
//                                $batch_data['quantity'] = $quantities_not_yet_in_batch_tbl;
//                                $this->insert_fake_expiry($batch_data);
//                            }
                        } else {

                            //returns is being made but the batch_no supplied is not in the system.
                            //this means that this item was never taken into the system before, or it was taken without any batch_no but now being returned with a batch_no

                            //in this case, it will be considered as if no batch_no is attached
                            $this->update_batch_decrease($batch_data, (-1 * $batch_data['quantity']));
                        }
                    }
                }
            } else {
                //there is no batch_no attached
                if ($this->exists_batches($batch_data['item_id'], $batch_data['location_id'])) {

                    //get the total quantities that all the batches of this item have in the expiry table, check the difference with  what is in $total_quantity(from item_quantities table) for the same item in the same location
                    $total_item_quantity_in_this_location = $this->get_total_batch_quantity($batch_data['item_id'], $batch_data['location_id']);
                    if ($total_quantity > $total_item_quantity_in_this_location) {

                        //quantity increased (perhaps a return was made)
                        $this->update_batch_increase($batch_data, $total_quantity - $total_item_quantity_in_this_location);
                    } elseif ($total_quantity < $total_item_quantity_in_this_location) {

                        //quantity decreased (perhaps a sale was made, receivings returns without batch_no supplied)
                        $this->update_batch_decrease($batch_data, $total_item_quantity_in_this_location - $total_quantity);
                    }
                } else {

                    //batches doent exist. either new inventory(usually with no batch_no) or other operation (like sales, transfer , delete)on item that has existed but no batch was supplied for the item during the stock intake


                    //product that has existed in the system(in item_quantities)  but doesn't exist in the item_expiry table.
                    //add everything in the expired.

                    //since batch_no is emtpy here, use fake batch
//                    $batch_data['quantity'] = $total_quantity;
//                    $this->insert_fake_expiry($batch_data);
                }
            }
        }
    }
    public function insert_fake_expiry($batch_data)
    {
        $batch_data['batch_no'] = Item_quantity::FAKE_BATCH_NO;
        $batch_data['expiry'] = $this->get_ten_years_back();

        $this->db->insert('item_expiry', $batch_data);
    }
    public function is_future_date($expiry)
    {
        strtotime($expiry) > time();
    }
    public function three_years_back($expiry)
    {
        $to_time = strtotime($expiry) - time();
        if ($to_time > 0) {
            return false;
        } else {
            $time = $to_time / (60 * 60 * 24 * 355); //about a years ago
            if ($time >= 3) {
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     * Don't use this funtion if you know the item_no, batch_no and location_id before hand
     */
    private function update_batch_increase($batch_data, $quantity)
    {
        //just get the currently used batch and increase it
        $item_batches = $this->get_item_batches($batch_data['item_id'], $batch_data['location_id']);
        if (!empty($item_batches)) {

            $batch_info = $item_batches[0];
            $this->db->where("item_id", $batch_info->item_id);
            $this->db->where("location_id", $batch_info->location_id);
            $this->db->where("batch_no", $batch_info->batch_no);
            $this->db->update("item_expiry", array('quantity' => $batch_info->quantity + $quantity));
        }
    }
    /**
     * Don't use this funtion if you know the item_no, batch_no and location_id before hand
     */
    private function update_batch_decrease($batch_data, $quantity)
    {
        //get all the batches and sort by expiry date in ascending order(i.e. the yesterday come first before today date)
        //start eliminating the batches from the system by substracting the quantity by the quantity we want to decrease this item
        //if a batch is used up, delele the batch from the system

        //to make it easier, delete all batches if the total quantities of the this item is 0 in the item_quantities table
        $total_quantity = $this->get_item_quantity($batch_data['item_id'], $batch_data['location_id'])->quantity;
        //if quantity is zero, just delete all the batches associated with this item
        if ($total_quantity <= 0) {
            $this->delete_all_item_batches($batch_data['item_id'], $batch_data['location_id']);
            return;
        }

        //get all batches of this item in this location_id
        $total_batch_quantity = $this->get_total_batch_quantity($batch_data['item_id'], $batch_data['location_id']);
        if ($quantity == $total_batch_quantity) {

            $this->delete_all_item_batches($batch_data['item_id'], $batch_data['location_id']);
            return;
        } elseif ($quantity > $total_batch_quantity) {

            //it is rare for quantity to be removed from batches is more than quantities available in the batch.
            //this function can only be called when batches exists for this item.
            //whenever batches exists, batches total must tally with total quantities in the item_quantities table. 

            //for if it do occur, delete all the batches of this item.
            $remaining = $total_batch_quantity - $quantity; //this is rare and may not happen
        } else {

            //the quantity to remove from batches is smaller than the total quantity of all batches of these item
//            $item_batches = $this->get_item_batches($batch_data['item_id'], $batch_data['location_id']);

            // reduce from batches that are not expired already;
            $this->db->from('item_expiry');
            $this->db->where("expiry >", date('Y-m-d').' 23:59:59');
            $this->db->where('item_id',$batch_data['item_id']);
            $item_batches = $this->db->get()->result();
            foreach ($item_batches as $index => $batch_info) {
                if ($quantity > 0) {
                    $current_batch_quantity = $batch_info->quantity;

                    //check if the quantity of the current batch is equal or more than the quantity to remove/decrease
                    if ($quantity >= $current_batch_quantity) {
                        //delete this batch
                        $this->delete_batch($batch_info->item_id, $batch_info->location_id, $batch_info->batch_no);
                        $quantity -= $current_batch_quantity;
                    } else {
                        //batch quantity is more than the quantity to remove, so just decrease the quantity of batch instead

                        $this->db->where("item_id", $batch_info->item_id);
                        $this->db->where("location_id", $batch_info->location_id);
                        $this->db->where("batch_no", $batch_info->batch_no);
                        $this->db->update("item_expiry", array('quantity' => $batch_info->quantity - $quantity));
                        $quantity = 0;
                    }
                } else {
                    break;
                }
            }
        }
    }

    public function get_ten_years_back()
    {
        $time = time() - (60 * 60 * 24 * 355 * 10);
        return date("Y-m-d H:i:s", $time);
    }
    public function save_multiple($locations)
    {

        $this->db->insert_batch('item_quantities', $locations);
        return true;
    }

    public function save_batch($location_detail)
    {
        if (!$this->exists_batch($location_detail['item_id'], $location_detail['location_id'], $location_detail['batch_no'])) {
            return $this->db->insert('item_expiry', $location_detail);
        }

        $this->db->where('item_id', $location_detail['item_id']);
        $this->db->where('location_id', $location_detail['location_id']);
        $this->db->where('batch_no', $location_detail['batch_no']);

        return $this->db->update('item_expiry', $location_detail);
    }

    public function get_item_quantity($item_id, $location_id = 2) //use 2 as default location
    {

        //$this->db->cache_on();

        $this->db->from('item_quantities');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $result = $this->db->get()->row();
        if (empty($result)) {
            //Get empty base parent object, as $item_id is NOT an item
            $result = new stdClass();

            //Get all the fields from items table (TODO to be reviewed)

//            foreach ($this->db->list_fields('item_quantities') as $field) {
//                $result->$field = '';
//            }

            $result->quantity = 0;
        }

        return $result;
    }
    public function get_item_batch($item_id, $location_id, $batch_no)
    {

        //$this->db->cache_on();
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->where('batch_no', $batch_no);
        $result = $this->db->get()->row();


        return $result;
    }
    //get quantity for this batch
    public function get_batch_quantity($item_id, $location_id, $batch_no)
    {

        //$this->db->cache_on();
        $batch_info = $this->get_item_batch($item_id, $location_id, $batch_no);
        if (!empty($batch_info)) {
            return $batch_info->quantity;
        }
        return 0.00;
    }
    public function get_item_batches($item_id, $location_id)
    {
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->order_by('expiry', "asc"); //this is very important
        $result = $this->db->get()->result();


        return $result;
    }
    /**
     * This is the total quantity of a particular item irrespective of batch_no. this should be the same with the quantity of an item(with location_id) in the quanitity table
     */
    public function get_total_batch_quantity($item_id, $location_id)
    {
        //$this->db->cache_on();
        $this->db->select("SUM(quantity) as quantity");
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->quantity;
        }


        return 0.00;
    }
    public function get_total_batch_quantity_exclusive($item_id, $location_id, $batch_no)
    {
        //$this->db->cache_on();
        $this->db->select("SUM(quantity) as quantity");
        $this->db->from('item_expiry');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->where('batch_no !=', $batch_no);


        $result = $this->db->get()->row();
        if (!empty($result)) {
            return $result->quantity;
        }


        return 0.00;
    }


    /*
	 * changes to quantity of an item according to the given amount.
	 * if $quantity_change is negative, it will be subtracted,
	 * if it is positive, it will be added to the current quantity
	 */
    public function change_quantity($item_id, $location_id, $quantity_change,$batch_affected) //added the batch params ::Lekan
    {
        //$this->db->cache_on();
        $quantity_old = $this->get_item_quantity($item_id, $location_id);
        $quantity_new = $quantity_old->quantity + intval($quantity_change);
        $location_detail = array('item_id' => $item_id, 'location_id' => $location_id, 'quantity' => $quantity_new);

        return $this->save($location_detail, $item_id, $location_id,$batch_affected,true);
//        return $this->save($location_detail, $item_id, $location_id);
    }

    /*
	* Set to 0 all quantity in the given item
	*/
    public function reset_quantity($item_id, $location_id = 2)
    {
        $this->db->where('item_id', $item_id);

        $status = $this->db->update('item_quantities', array('quantity' => 0));

        $batch_data = array();
        $batch_data['item_id'] = $item_id;
        $batch_data['location_id'] = $location_id;

        $this->update_batch($batch_data);
        return $status;
    }

    /*
	* Set to 0 all quantity in the given list of items
	*/
    public function reset_quantity_list($item_ids, $location_id = 2)
    {

        //$this->db->cache_on();
        $this->db->where_in('item_id', $item_ids);

        $status = $this->db->update('item_quantities', array('quantity' => 0));
        foreach ($item_ids as $index => $item_id) {
            $batch_data = array();
            $batch_data['item_id'] = $item_id;
            $batch_data['location_id'] = $location_id;
            $this->update_batch($batch_data);
        }


        return $status;
    }
    public function fetch_all_non_zero_quantity($location = 2)
    {
        //$this->db->cache_on();
        $this->db->select("*");
        $this->db->from("item_quantities");

        $this->db->where("location_id", $location);

        return $this->db->get()->result();
    }
    public function zero_all_quantities_in_batch($quantities)
    {
        if (count($quantities) > 0) {
            //make sure the primarky key is set in the arrays before using the update_batch
            $this->db->update_batch($quantities);

            //since all items are
            $this->db->empty_table("item_expiry");
        }
    }
}
