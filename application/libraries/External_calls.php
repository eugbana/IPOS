<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class External_calls
{
    public static function makeRequest($url,$data=[],$type = "GET"){
//        var_dump(json_encode($data));
//        exit();
        $curl = curl_init();
        self::ecurl_setopt($curl, CURLOPT_URL, $url);
        self::ecurl_setopt($curl, CURLOPT_ENCODING, "");
        self::ecurl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        self::ecurl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        self::ecurl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        self::ecurl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        if(strtolower($type) == "post"){
            self::ecurl_setopt($curl, CURLOPT_POST, true);
            self::ecurl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        self::ecurl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json','Content-Type:application/json'));

        self::ecurl_setopt($curl, CURLOPT_CONNECTTIMEOUT,20);
        self::ecurl_setopt($curl, CURLOPT_TIMEOUT,200);
//        var_dump($curl);
//        exit();
        $response = curl_exec($curl);
        if(!$response){
            return json_encode(["error"=>curl_error($curl)]);
        }
        curl_close($curl);
        return $response;
    }
    private static function ecurl_setopt ( $ch , int $option , $value ):bool{
        $ret=curl_setopt($ch,$option,$value);
        if($ret!==true){
            //option should be obvious by stack trace
            throw new RuntimeException ( 'curl_setopt() failed. curl_errno: ' . $ch .'. curl_error: '.curl_error($ch) );
        }
        return true;
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
}
