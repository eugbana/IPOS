<?php


class IRechargeController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('IRecharge');
        $this->load->library('sale_lib');
    }
    public function buyData(){
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $data = $this->input->post();
        $is_data = false;
        if(isset($data['is_data'])){
            $is_data = true;
            if(!isset($data['network'])|| !isset($data['mobile']) || !isset($data['code'])){
                echo json_encode(['status'=>"90",'message'=>"Incomplete vending data supplied!!"]);
                exit();
            }
            $response = $this->IRecharge->buyData($data['network'],$data['mobile'],$data['code'],"haslek2013@gmail.com");
        }else{
            if(!isset($data['network'])|| !isset($data['mobile'])){
                echo json_encode(['status'=>"90",'message'=>"Incomplete vending data supplied!!"]);
                exit();
            }
            $response = $this->IRecharge->buyAirtime($data['network'],$data['amount'],$data['mobile']);
        }

        if(is_string($response)){
            echo json_encode(['status'=>"91",'message'=>$response]);
            exit();
        }
        if($response['status']=="00"){
            $trans_detail = [
                'service_type'=>$is_data?'data':'airtime',
                'amount'=>$is_data?$response['amount_paid']:$response['amount'],
                'value'=>$response['amount'],
                'transaction_status'=>$response['status'],
                'provider'=>$data['network'],
                'phone_number'=>$response['receiver'],
                'provider_reference'=>$response['ref'],
                'response_hash'=>$response['response_hash'],
                'response_message'=>$response['message'],
                'g_ref'=>$response['generated_ref']
            ];
            $this->IRecharge->saveTransactions($trans_detail);
//            echo json_encode($response);
//            exit();
        }
        echo json_encode($response);
        exit();

    }
    public function buyTv() {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $req_data = $this->input->post();
        if(isset($req_data['tv']) && isset($req_data['card']) && isset($req_data['phone']) && isset($req_data['email'])){
            $resp = $this->IRecharge->buyTv($req_data['card'],$req_data['tv'],$req_data['code'],$req_data['phone'],$req_data['email']);
            if(is_string($resp)){
                $this->encError($resp);
            }
            $trans_detail = [
                "staff_id"=>$this->Employee->get_logged_in_employee_info()->person_id,
                "customer_id"=>$this->sale_lib->get_customer(),
                "service_type"=>"Cable Tv",
                "value"=>0,
                "transaction_status"=>$resp['status'],
                "provider"=>$req_data['tv'],
                "card_number"=>$req_data['card'],
                "response_message"=>$resp['message'],
                "order_title"=>$resp['order'],
                "wallet_balance"=>$resp['wallet_balance'],
                'g_ref'=>$resp['generated_ref']
            ];
            $this->IRecharge->saveTransactions($trans_detail);
            $this->encResult($resp);
        }
        $this->encError("Incomplete input parameters!");
    }
    private function saveTransaction($trans_detail) {
        $this->IRecharge->saveTransactions($trans_detail);
    }

    public function getAvailableTv()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $tv = $this->input->post('tv');
        $resp = $this->IRecharge->getAvailableTv($tv);
        if(is_string($resp)){
            $this->encError($resp);
        }
        $this->encResult($resp);
    }

    public function getAvailableDataBundles()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $network = $this->input->post('network');
        $resp = $this->IRecharge->getDataBundles($network);
        if(is_string($resp)){
            $this->encError($resp);
        }
        $this->encResult($resp);
        $this->encError("Incomplete input parameters!");
    }

    public function buyPower()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $req_data = $this->input->post();
//        var_dump($req_data);
        $req_data['email'] = "stephen@istrategytech.com";
        if(isset($req_data['disco'])&& isset($req_data['amount']) && isset($req_data['meter_no']) && isset($req_data['phone']) && isset($req_data['email'])){

            $resp = $this->IRecharge->buyPower($req_data['disco'],$req_data['amount'],$req_data['meter_no'],$req_data['phone'],$req_data['email']);
            if(is_string($resp)){
                $this->encError($resp);
            }
            if($resp['status'] == "00"){
                $trans_detail = [
                    "staff_id"=>$this->Employee->get_logged_in_employee_info()->person_id,
                    "customer_id"=>$this->sale_lib->get_customer(),
                    "customer_address"=>isset($resp['address'])?$resp['address']:'Unknown address',
                    "service_type"=>"Power purchase",
                    "amount"=>isset($resp['amount'])?$resp['amount']:'0',
                    "value"=>isset($resp['units'])?$resp['units']:'0',
                    "transaction_status"=>$resp['status'],
                    "provider"=>$req_data['disco'],
                    "meter_no"=>$req_data['meter_no'],
                    "meter_token"=>isset($resp['meter_token'])?$resp['meter_token']:'N/A',
                    "response_message"=>isset($resp['message'])?$resp['message']:'No message response',
                    "provider_reference"=>isset($resp['ref'])?$resp['ref']:null,
                    "response_hash"=>isset($resp['response_hash'])?$resp['response_hash']:null,
                    "wallet_balance"=>isset($resp['wallet_balance'])?$resp['wallet_balance']:0,
                    'g_ref'=>$resp['generated_ref']
                ];
                $this->IRecharge->saveTransactions($trans_detail);
            }
            $this->encResult($resp);
        }
        $this->encError("Incomplete input parameters!");
    }

    public function getMeterInfo()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $req_data = $this->input->post();
        if(isset($req_data['disco']) && isset($req_data['meter_no']) ){
            $resp = $this->IRecharge->getMeterInfo($req_data['meter_no'],$req_data['disco']);
            if(is_string($resp)){
                $this->encError($resp);
            }
            $this->encResult($resp);
        }
        $this->encError("Incomplete input parameters!");
    }
    private function encError($msg){
        echo json_encode(['status'=>"91",'message'=>$msg]);
        exit();
    }
    private function encResult($resp){
        echo json_encode($resp);
        exit();
    }

    public function getAllPowerDistributors()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $resp = $this->IRecharge->getDiscos();
        if(is_string($resp)){
            $this->encError($resp);
        }
        echo json_encode($resp);
    }

    public function getTransStatus()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
    }

    public function getSmartCardInfo()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $req_data = $this->input->post();
        if(isset($req_data['tv'])&& isset($req_data['card']) && isset($req_data['code'])){
            $resp = $this->IRecharge->getTvCardInfo($req_data['card'],$req_data['tv'],$req_data['code'],$req_data['amount']);
            if(is_string($resp)){
                $this->encError($resp);
            }
            $this->encResult($resp);
        }
        $this->encError("Incomplete input parameters!");
    }

    public function getSmileDeviceInfo()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $req_data = $this->input->post();
        if(isset($req_data['device'])){
            $resp = $this->IRecharge->getSmileDeviceInfo($req_data['device']);
            if(is_string($resp)){
                $this->encError($resp);
            }
            $this->encResult($resp);
        }
        $this->encError("Incomplete input parameters!");
    }
    public function getWalletBalance(){
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        $resp = $this->IRecharge->getWalletBalance();
        if(is_string($resp)){
            $this->encError($resp);
        }
        $this->encResult($resp);

    }



}
