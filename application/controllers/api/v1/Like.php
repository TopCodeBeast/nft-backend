<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class Like extends BaseController
{
    public function __construct()
    {   
        parent::__construct();
        $this->load->model('LikeModel', "model");
    }
    public function personallike(){
        $filter = $this->input->post();
        $like = (array)$this->Like->one($filter);

        if ($like) {
            if($like['like_status'] == 'like'){
                $like['like_status'] == 'dislike';
            }else{
                $like['like_status'] == 'like';
            }
            $this->Like->save($like);
        }else{
            $filter['like_status'] = 'like';
            $this->Like->save($filter);
        }
        $this->response(array("sucess"=>true, "msg"=>"Vote success"));
    }
}