<?php
defined('BASEPATH') or exit('No direct script access allowed');
class FanModel  extends AbstractModel
{
    var $_table = 'tb_fans';
    var $_pk = "fan_id";
    var $_sort = "fan_id";

    public function fanList($id, $type){
        $this->db->select("tb_user.*");
        if($type == "fan"){
            $this->db->join("tb_user","tb_fans.req_to = tb_user.id","LEFT");
            $this->db->where("req_from", $id);
        }else{
            $this->db->join("tb_user","tb_fans.req_from = tb_user.id","LEFT");
            $this->db->where("req_to", $id);
        }
        $data = parent::all();
        foreach($data as $index =>$user){
            $data[$index]->pic = getProfileImage((array)$user);
        }
        return $data;
    }
}
