<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class ActionLog extends BaseController
{

    public function __construct()
    {   
        parent::__construct();
        $this->load->model('ActionLogModel', "model");
    }
}