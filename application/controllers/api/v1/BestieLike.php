<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class BestieLike extends BaseController
{
    public function __construct()
    {   
        parent::__construct();
        $this->load->model('BestieLikeModel', "model");
    }
    public function add(){
        $data = $this->input->post();
        print_r($data);
    }
}