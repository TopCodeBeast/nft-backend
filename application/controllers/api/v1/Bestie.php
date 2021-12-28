<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class Bestie extends BaseController
{

    public function __construct()
    {   
        parent::__construct();
        $this->load->model('BestieModel', "model");
    }
    
    public function add_bestie()
    {
        $data = $this->input->post();
        $count = $this->Bestie->count($data);
        if($count > 0){
            $post = array(
                "status" => false,
                "message" => "Already Added"
            );
        }else{
            $bestie_id = $this->Bestie->insert($data);
            
            $from = $this->User->select($data['req_from']);
            $to = $this->User->select($data['req_to']);
            
            $token = $to->device_token;
            $message = $from->name . ' ' . 'wants to add you as ' . $data['category'];
            $type = $to->device_type;
            $notification = array(
                'sender_id' => $data['req_from'],
                'receiver_id' => $data['req_to'],
                'message' => $message,
                'notification_status' => 'Add_' .$data['category'],
                'request_id' => $bestie_id
            );
            $this->Notification->save($notification);
            $this->push($token, $message, $type);
            $post = array(
                "status" => true,
                "message" => "Successfully Added"
            );
        }
        $this->response($post);
    }
    public function changeBestieStatus(){
        $param = $this->input->post();
        $param['read_status'] = "1";
        $this->Bestie->update($param, array("bestie_id" => $param['bestie_id']));
        if($param['req_status'] == "1"){
            $message = "Invite Accepted";
        }else{
            $message = "Invite Rejected";
        }
        $filter['request_id'] = $param['bestie_id'];
        $filter['notification_status'] = "Add_squad";
        $data = $filter;
        $data['read_status'] = "2";
        $this->Notification->update($data, $filter);
        $post = array(
            "status" => true,
            "message" => $message
        );
        $this->response($post);
    }
    public function getBestieRequest(){
        $filter = $this->input->post();
        $result = $this->Bestie->getInvitation($filter);
        $post = array(
            "status" => true,
            "details" => $result
        );
        $this->response($post);
        
    }

    public function getSquadList(){
        $id = $this->input->post("id");
        $squadList = $this->Bestie->getSquadList($id);
        $post = array(
            "status" => true,
            "details" => $squadList
        );
        $this->response($post);
    }
    public function getBestieList(){
        $req_from = $this->input->post("id");
        $filter["req_from"] = $req_from;
        $filter["req_status"] = "1";
        $filter["category"] = "bestie";

        $bestielist = $this->Bestie->getBestieList($filter);
        $post = array(
            "status" => true,
            "details" => $bestielist
        );
        $this->response($post);
    }
    public function getActionLog (){
        $filter = $this->input->post();
        $data = $this->Bestie->actionLogs($filter);
        $post =array (
            "status" => true,
            "details" => $data
        );
        $this->response($post);
    }
}