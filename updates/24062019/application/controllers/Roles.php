<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    require_once('Secure_Controller.php');

    class Roles extends Secure_Controller {

        public function __construct($module_id = NULL) {
		    parent::__construct('roles');
        }
        
        public function index() {
            echo "Done";
            exit;
        }
    }