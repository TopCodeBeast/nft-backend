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
    public function push($devtoken, $message, $devicetype)
    {
    // echo $devtoken;echo $devicetype;echo $message;die();
        if ($devicetype == "android") {
            $token = $devtoken;
            $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
            $notification = [
                'title' => 'iMatch Notification',
                'body' => $message
            ];
            $extraNotificationData = ["message" => $notification, "notificationid" => '2'];

            $fcmNotification = [
                'to' => $token, //single token
                'notification' => $notification,
                'data' => $extraNotificationData
            ];
            //server api key for iMatch app
            $headers = [
                'Authorization: key=AAAAsKYPTls:APA91bGxeIwygCIeecMI8Q-huiJJSFVNRUnHXP5QoI4qIpnOFvamcMwkh_lguVtMmcfbI1FNWG7PIAKgEpKTp86jt_V2bBpg82lrMsQK8xj8hHh8UfpYMK55EZz2Bt8SnIrs7bR0iu5a',
                'Content-Type: application/json'
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            $result = curl_exec($ch);
            curl_close($ch);
            $final = json_decode($result);
            $check = $final->success;
            if ($check == 1) {
                $notify['status'] = true;
                $notify['message'] = 'Success';
            } else {
                $notify['status'] = false;
                $notify['userid'] = 'Fail';
            }
          // print_r($notify);
          //echo json_encode($notification);

        }
    }
}
