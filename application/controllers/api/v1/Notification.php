<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class Notification extends BaseController
{

    public function __construct()
    {   
        parent::__construct();
        $this->load->model('NotificationModel', "model");
    }

    public function getNotification($user_id){
        $filter = array(
            'receiver_id'=>$user_id,
            'read_status'=> "1",
            'notification_status' => ""
        );
        $data['user'] = $this->User->select($user_id);

        $filter['notification_status'] = "Add_fan";
        $data['fan'] = $this->Notification->count($filter);
        
        $filter['notification_status'] = "Add_squad";
        $data['squad'] = $this->Notification->count($filter);

        $filter['notification_status'] = "Add_bestie";
        $data['bestie'] = $this->Notification->count($filter);

        $filter['notification_status'] = "Match_invitation";
        $data['matches'] = $this->Notification->count($filter);

        $data['pending'] = $this->db->query("SELECT COUNT(*) as count from tb_match WHERE rival_id = '$user_id' AND invitation_status IS NULL AND match_type = 'open'")->row()->count;
        
        $data['total'] = $data['fan'] + $data['squad'] + $data["matches"] + $data['bestie'] + $data['pending'];

        $post = array(
            "status" => true,
            "details"=>$data
        );
        $this->response($post);
    }

    public function setNotificationRead(){
        $filter = $this->input->post();
        if($filter["type"] == "Add_bestie"){
            $bestie["bestie_id"] = $filter["request_id"];
            $bestie["req_status"] = $filter["req_status"];
            $bestie["read_status"] = 1;
            $this->Bestie->save($bestie);
        }
        $notification["not_id"] = $filter["not_id"];
        $notification["read_status"] = 2;
        $data = $this->Notification->select($filter["not_id"]);
        $user = $this->User->select($data->sender_id);
        $from = $this->User->select($data->receiver_id);
        if($filter["req_status"] == "1"){
            $message = "You invitation is accepted by " . $from->name;
        }else{
            $message = "You invitation is rejected by " . $from->name;
        }
        $this->push($user->device_token, $message, $user->device_type);
        $this->Notification->save($notification);
        $this->response(array("status"=>true, "message"=>"change notification status"));
    }
    public function all(){
        $filter = $this->input->post();
        $data = $this->Notification->getAll($filter);
        $post = array(
            "status" => true,
            "details" => $data
        );
        $this->response($post);
    }
}