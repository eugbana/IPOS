<?php

class Transfer_model extends CI_Model
{
    /*
    Gets information about a particular item transferred from another branch
    */
    public function check_transfer_item_info($item_name)
    {
        $this->db->select('items.*');
        $this->db->select('suppliers.company_name');
        $this->db->from('items');
        $this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
        $this->db->where('name', $item_name);

        $item = $this->db->get()->row();
        if(!$item){
            return false;
        }
//        var_dump(['item fetched',$item]);
        return $item;
    }
    public function get_un_uploaded_transfers(){
        return $this->db->from('erd_transfer_calls')
            ->where('status >',0)
            ->where('transfer_reference !=',null)
            ->get()->result();
    }
    public function upload_trans($reference,$sent_by){
        $transDetail = $this->get_transfer_details($reference);
        if($transDetail){
            $branch_from_info = $this->CI->Employee->get_branchinfo($transDetail->request_from_branch_id);
            $branch_to_info = $this->CI->Employee->get_branchinfo($transDetail->request_to_branch_id);
            $erd_data = [
                'caller'=>'HaslekIsBae',
                "items"=>$transDetail->items,
                "transfer_reference"=>$reference,
                "from_branch" => $branch_from_info->location_name,
                "to_branch" => $branch_to_info->location_name,
                "to_branch_id" =>$branch_to_info->brid,
                "from_branch_id" =>$branch_from_info->brid,
                "total_quantity"=> $transDetail->total_quantity,
                "total_price" => $transDetail->total_price,
                "sent_by" => $sent_by
            ];
            $erd_url = ERD_BASE_URL.'/transfer/push';
            $this->load->library('External_calls');
            $erd_response = External_calls::makeRequest($erd_url,$erd_data,"POST");
            $erd_response = json_decode($erd_response,true);
            $erd_response = $erd_response== null ? "":$erd_response;
            $erd_response_data = [];
            if(is_string($erd_response) || $erd_response['status'] != "00" || $erd_response == null){
                $message = isset($erd_response['error'])?$erd_response['error']:$erd_response['message'];
                $erd_response_data["response"] = isset($message)?$message:"Unknown error occurred";
                $erd_response_data["status"] = isset($erd_response['status'])? $erd_response['status'] : 1;
            }else{
                $erd_response_data["response"] = $erd_response['message']?$erd_response['message']:$erd_response['error'];
                $erd_response_data["status"] = $erd_response['status'];
            }
            $this->db->update('erd_transfer_calls',$erd_response_data,['transfer_reference'=>$reference]);
        }
    }
    public function get_transfer_details($reference){
        $transfer_detail = $this->db->get_where('item_transfer',['reference'=>$reference])->row();
        if($transfer_detail){
            $transfer_detail->items = $this->db->select(
                'items.name,items.type,items.category,items.item_number,
                items_push.pushed_quantity as quantity,items_push.batch_no,items_push.transfer_price,items_push.item_unit_price as unit_price,
                items_push.expiry,items_push.item_cost_price as cost_price'
            )
                ->from('items')
                ->join('items_push','items.item_id = items_push.item_id')
                ->where('items_push.transfer_id',$transfer_detail->transfer_id)
                ->get()->result();
            $transfer_detail->total_quantity = $this->db->select_sum('pushed_quantity')
                ->where('transfer_id',$transfer_detail->transfer_id)
                ->get('items_push')->row()->pushed_quantity;
            $transfer_detail->total_price = $this->db->select_sum('transfer_price')
                ->where('transfer_id',$transfer_detail->transfer_id)
                ->get('items_push')->row()->transfer_price;
            $employee = $this->CI->Employee->get_info($transfer_detail->employee_id);
            $transfer_detail->sent_by = $employee->first_name.' '.$employee->last_name;
        }
        return $transfer_detail;
    }
    public function recall_transfer($transfer_reference){
        $employee = $this->Employee->get_logged_in_employee_info();
        $this->db->select('item_transfer.transfer_id')
            ->select('items_push.pushed_quantity,items_push.item_id')
            ->from('item_transfer')
            ->join('items_push','item_transfer.transfer_id = items_push.transfer_id','left')
            ->where('item_transfer.reference',$transfer_reference);
        $transfer_items = $this->db->get()->result();
        if(count($transfer_items) > 0){
            $this->db->trans_start();
            foreach ($transfer_items as $item){
                $item_quantity = $this->db->select('quantity')
                    ->from('item_quantities')
                    ->where(['location_id'=>$employee->branch_id,'item_id'=>$item->item_id])
                    ->get()->row();
                $this->db->update('item_quantities',['quantity'=>$item_quantity->quantity+$item->pushed_quantity],
                    ['item_id'=>$item->item_id,'location_id'=>$employee->branch_id]);
            }
            $this->db->update('item_transfer',['status'=>3],
                ['reference'=>$transfer_reference]);
            $this->db->trans_complete();
        }
    }
    public function create_item($item){
        $item_data = [
            'name'=>$item->item_name,
            'category'=>$item->item_category ? $item->item_category: 'others',
            'item_number'=>$item->item_number,
            'cost_price'=>$item->cost_price,
            'unit_price'=>$item->retail_price
        ];
        $this->db->insert('items',$item_data);
        $item->item_id = $this->db->insert_id();
        return $item;
    }
    private function update_item_price($item_id,$cost_price,$retail_price){
        $this->db->update('items',['cost_price'=>$cost_price,'unit_price'=>$retail_price],['item_id'=>$item_id]);
    }
    public function accept($transfer_data){
//        var_dump(["I got here",$transfer_data]);
//        exit();
        $employee = $this->CI->Employee->get_logged_in_employee_info();
        $this->db->trans_start();
        $receiving_data = array(
            'employee_id' => $employee->person_id,
            'receiving_reference'=>$transfer_data->transfer_reference,
            'reference'=>$transfer_data->transfer_reference,
            'receiving_from' => $transfer_data->from_branch,
        );
        $this->db->insert('receivings', $receiving_data);
        $receiving_id = $this->db->insert_id();
        foreach ($transfer_data->items as $item) {
            $item_info = $this->check_transfer_item_info($item->item_name);

            if(!$item_info){
                $item_info = $this->create_item($item);
            }
            $this->update_item_price($item_info->item_id,$item->cost_price,$item->retail_price);
//            var_dump($item_info);
            $receiving_items_data = array(
                'receiving_id' => $receiving_id,
                'item_id' => $item_info->item_id,
                'quantity_purchased' => $item->transferred_quantity,
                'item_cost_price' => $item->cost_price,
                'item_unit_price' => $item->retail_price,
                'item_location' => $employee->branch_id,
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date,
            );

            $this->db->insert('receivings_items', $receiving_items_data);

            $item_quantity = $this->CI->Item_quantity->get_item_quantity($item_info->item_id, $employee->branch_id);

            //prepare batch info
            $expiry_data = array();
            if (($item->batch_no != ''|| $item->batch_no != null) && ($item->expiry != ''|| $item->batch_no != null)) {

                $expiry_data['item_id'] = $item_info->item_id;
                $expiry_data['batch_no'] = $item->batch_no;
                $expiry_data['location_id'] = $employee->branch_id;
                $expiry_data['expiry'] = $item->expiry;
                $expiry_data['quantity'] = $item->transferred_quantity;
            }

            //Update stock quantity
            $this->CI->Item_quantity->save(array(
                'quantity' => $item_quantity->quantity + $item->transferred_quantity, 'item_id' => $item_info->item_id,
                'location_id' => $employee->branch_id,
            ), $item_info->item_id, $employee->branch_id, $expiry_data);

            $recv_remarks = 'RECV ' . $receiving_id;
            $inv_data = array(
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $item_info->item_id,
                'trans_user' => $employee->person_id,
                'trans_location' => $employee->branch_id,
                'trans_comment' => $recv_remarks,
                'trans_inventory' => $item->transferred_quantity,
                'selling_price' => $item->retail_price,
                'trans_remaining' => $item_quantity->quantity + $item->transferred_quantity
            );

            $this->CI->Inventory->insert($inv_data);
            //notify item sale_tracker here if it doesnt exists
            $this->CI->Sale->saveitemtracker($item_info->item_number);

        }
        $this->db->trans_complete();
        return true;
    }
}
