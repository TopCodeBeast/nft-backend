<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class User extends BaseController
{

    public function __construct()
    {   
        parent::__construct();
        $this->load->model('UserModel', "model");
    }
}