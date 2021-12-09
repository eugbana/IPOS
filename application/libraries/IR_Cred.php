<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class IR_Cred
{
    private static $vendor_id = "1512A89279";
    private static $base_url = "https://irecharge.com.ng/pwr_api_live/v2/";
    private static $live_key = "ec946f509e8ae02706ec074dee756d5f";
    private static $live_private = "88a13b5439cc1ea491888eebacb322da072e7cab3136dd74df94769302e1f1c300da3949a2ca6c409d02ea557930b124d34f5fc37a32a215576ed822dd209eb1";

    public static function getVendorId(){
        return self::$vendor_id;
    }
    public static function getLivePrivateKey() {
        return self::$live_private;
    }
    public static function getLivePubKey(){
        return self::$live_key;
    }
    public static function getBaseUrl() {
        return self::$base_url;
    }
}
