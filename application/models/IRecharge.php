<?php


class IRecharge extends CI_Model
{
//    private $base_url = "https://irecharge.com.ng/pwr_api_live/v2/";
    private $base_url = "https://irecharge.com.ng/pwr_api_sandbox/v2/";
//    private $vendor_id;
    private $vendor_id = "1907321069";
    private $v_priv = "43c71a5dec9411efd7d96e8db8dc201af42c03be521068525e872a445d37bf1e904ed4b18e61ad75ccaf83db33d18904f2ca694222ec26da0665bf60e3052fb2";//
//    private $v_priv ="54cb0954228fd47f02246ec577317392dbe9c4a351d532953b287382c2f19ebdb789eafc1981b7cb4b9bf9d30875cbafc06589cb042057509af5f2db77782ecb";
//    private $v_pub = "650d9dd9f1124187ce7ad4b9689166dd";
    private $v_pub = "00bc75046cf57007dbcc43b3109a2feb";
//    private $v_pub;
    public function __construct()
    {
        parent::__construct();
        $this->load->library('IR_Cred');
//        $this->vendor_id = IR_Cred::getVendorId();
//        $this->base_url = IR_Cred::getBaseUrl();
//        $this->v_priv = IR_Cred::getLivePrivateKey();
//        $this->v_pub = IR_Cred::getLivePubKey();
    }

    private function generateReference(){
        $gen_ref = rand(100000000000,999999999999);
        $existing =$this->db->from("irecharge_transactions")
            ->where(['g_ref'=>$gen_ref])
            ->get()
            ->row();
        if($existing){
            $gen_ref =$this->generateReference();
        }
        return $gen_ref;
    }
    private function sendRequest($url,$params=[],$power = false){
//        var_dump($params);
//        exit();
        $ch = curl_init();
        if(!empty($params)){
            $url .= '?'.http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            $err = curl_error($ch);
        }
        curl_close($ch);
        if(isset($err)){
            return $err;
        }
        $res = json_decode($this->remove_utf8_bom($result),true);
        $res['generated_ref'] = $params['reference_id'];
        return $res;
    }
    private function generateHash($ref_id,$hash_string){
        $string = $this->vendor_id.'|'.$ref_id."|".$hash_string."|".$this->v_pub;
        return hash_hmac("sha1",$string,$this->v_priv);
    }
    public function remove_utf8_bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
    public function getDiscos(){
        $disco_data = [
            'response_format'=>'json'
        ];
        return $this->sendRequest($this->base_url.'get_electric_disco.php',$disco_data);
    }
    public function getMeterInfo($meter_no,$disco,$from_vend = false){
        $ref = $this->generateReference();
        $trans_hash_string = "$meter_no|$disco";
        $hash = $this->generateHash($ref,$trans_hash_string);
        $meter_data = [
            'vendor_code'=>$this->vendor_id,
            'disco'=>$disco,
            'meter'=>$meter_no,
            'response_format'=>'json',
            'hash'=>$hash,
            'reference_id'=>$ref
        ];
        return $this->sendRequest($this->base_url.'get_meter_info.php',$meter_data);
    }
    public function buyPower($disco,$amount,$meter_no,$phone,$email){
        $meter_info = $this->getMeterInfo($meter_no,$disco,true);
        $ref = $this->generateReference();
        $a_token = $meter_info['access_token'];
        $hash_string = "$meter_no|$disco|$amount|$a_token";
        $hash = $this->generateHash($ref,$hash_string);
//        echo 'hash: '.$hash;
//        echo 'ref: '.$ref;
        $meter_data = [
            'hash'=>$hash,
            'phone'=>$phone,
            'disco'=>$disco,
            'access_token'=>$a_token,
            'email'=>$email,
            'amount'=>$amount,
            'vendor_code'=>$this->vendor_id,
            'meter'=>$meter_no,
            'reference_id'=>$ref
        ];
//        var_dump($meter_info);
        return $this->sendRequest($this->base_url.'vend_power.php',$meter_data,true);
    }
    public function buyAirtime($network,$amount,$phone){
        $ref = $this->generateReference();
        $hash = $this->generateHash($ref,"$phone|$network|$amount");
        $airtime_data = [
            'vendor_code'=>$this->vendor_id,
            'vtu_network'=>$network,
            'vtu_number'=>$phone,
            'vtu_amount'=>$amount,
            'reference_id'=>$ref,
            'vtu_email'=>'example@email.com',
            'hash'=>$hash,
            'response_format'=>'json'
        ];
        return $this->sendRequest($this->base_url.'vend_airtime.php',$airtime_data);
    }
    public function getAvailableTv($tv){
        $tv_data = [
            'response_format'=>'json',
            'tv_network'=>$tv
        ];
        return $this->sendRequest($this->base_url.'get_tv_bouquet.php',$tv_data);
    }
    public function getTvCardInfo($card_no,$tv,$code,$amount=null){
        $ref = $this->generateReference();
        $hash = $this->generateHash($ref,"$tv|$card_no|$code");
        $tv_data = [
            'reference_id'=>$ref,
            'response_format'=>'json',
            'service_code'=>$code,
            'vendor_code'=>$this->vendor_id,
            'tv_network'=>$tv,
            'hash'=>$hash,
            'smartcard_number'=>$card_no,
        ];
        if($amount){
            $tv_data['tv_amount'] = $amount;
        }
        return $this->sendRequest($this->base_url.'get_smartcard_info.php',$tv_data);
    }
    public function getTvSCardInfo(){}
    public function buyTv($card_no,$tv,$code,$phone,$email) {
        $ref = $this->generateReference();
        $card_info = $this->getTvCardInfo($card_no,$tv,$code);
        $a_token = $card_info['access_token'];
        $hash = $this->generateHash($ref,"$card_no|$tv|$code|$a_token");
        $tv_data = [
            'reference_id'=>$ref,
            'response_format'=>'json',
            'smartcard_number'=>$card_no,
            'vendor_code'=>$this->vendor_id,
            'email'=>$email,
            'service_code'=>$code,
            'phone'=>$phone,
            'access_token'=>$a_token,
            'tv_network'=>$tv,
            'hash'=>$hash
        ];
        return $this->sendRequest($this->base_url.'vend_tv.php',$tv_data);
    }
    public function getReport($start=null,$end=null,$type=null,$staff=null) {
        $where = '';
        if($start && $end){
            $where = " date between '$start' AND '$end'";
        }
        if($type){
            if($where == ''){
                $where .= " service_type = '$type'";
            }else{
                $where .= " and service_type = '$type'";
            }
        }
        if($staff){
            if($where == ''){
                $where .= " staff_id = '$staff'";
            }else{
                $where .= " and staff_id = '$staff'";
            }
        }
        $query_res = $this->db->select('amount,value,provider,date,service_type,response_message,order_title,people.first_name,people.last_name')
            ->from('irecharge_transactions')
            ->join('people','irecharge_transactions.staff_id=people.person_id');
        if($where != ''){
            $query_res->where($where);
        }
        return $query_res->get()->result();
    }

    public function getWalletBalance(){
        $req_data = [
            'response_format'=>'json',
            'vendor_code'=>$this->vendor_id
        ];
        return $this->sendRequest($this->base_url.'get_wallet_balance.php',$req_data);
    }
    public function getStatus($trans_id ){
        $info = $this->db->from('irecharge_transactions')
            ->where('id',$trans_id)
            ->get()->row();
        if($info){
            $hash = hash_hmac("SHA1","$this->vendor_id|$info->access_token|$this->v_pub",$this->v_priv);
            $stat_data = [
                'vendor_code'=>$this->vendor_id,
                'type'=>$info->service_type,
                'access_token'=>$info->access_token,
                'hash'=>$hash,
                'response_format'=>'json'
            ];
            return $this->sendRequest($this->base_url.'vend_status.php',$stat_data);
        }
        return null;
    }
    public function saveTransactions($trans_data){
        $this->db->insert('irecharge_transactions',$trans_data);
    }
    public function getSmileDeviceInfo($device_id){
        $v_code = $this->vendor_id;
        $pub = $this->v_pub;
        $hash = hash_hmac("sha1","$v_code|$device_id|$pub",$this->v_priv);
        $req_data = [
            'hash'=>$hash,
            'response_format'=>'json',
            'vendor_code'=>$v_code,
            'receiver'=>$device_id
        ];
        return $this->sendRequest($this->base_url.'get_smile_info.php',$req_data);
    }
    public function getDataBundles($network){
        $req_data = [
            'response_format'=>'json',
            'data_network'=>$network
        ];
        return $this->sendRequest($this->base_url.'get_data_bundles.php',$req_data);
    }
    public function buyData($network,$mobile,$data_code,$email){
        $ref = $this->generateReference();
        $trans_hash_string = "$mobile|$network|$data_code";
        $hash = $this->generateHash($ref,$trans_hash_string);
        $data_data = [
            'vendor_code'=>$this->vendor_id,
            'vtu_network'=>$network,
            'vtu_number'=>$mobile,
            'response_format'=>'json',
            'vtu_data'=>$data_code,
            'reference_id'=>$ref,
            'vtu_email'=>$email,
            'hash'=>$hash
        ];
        return $this->sendRequest($this->base_url.'vend_data.php',$data_data);
    }

}
