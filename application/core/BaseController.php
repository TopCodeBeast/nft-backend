<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Class : BaseController
 * Base Class to control over all the classes
 * @author : Kishor Mali
 * @version : 1.1
 * @since : 15 November 2016
 */
class BaseController extends CI_Controller {
    

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Authorization, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST");
        parent::__construct();
    }

    public function response($data = NULL) {
        $this->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->_display();
        exit();
    }
    public function all(){
        $filter = $this->input->post();
        $data = $this->model->all($filter);
        $this->response(array("status"=>true, "details"=> $data));
    }
    public function save(){
        $data = $this->input->post();
        $this->model->save($data);
        $this->response(array("status"=> true));
    }
    public function one(){
        $filter = $this->input->post();
        $data = $this->model->one($filter);
        $this->response(array("status"=>true, "details"=>$data));
    }
}
