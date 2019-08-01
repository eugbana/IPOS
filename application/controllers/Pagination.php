<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pagination extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->CI =& get_instance();
    }

    
}