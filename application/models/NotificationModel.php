<?php
defined('BASEPATH') or exit('No direct script access allowed');
class NotificationModel  extends AbstractModel
{
    var $_table = 'tb_notification';
    var $_pk = "not_id";
    var $_sort = "not_id";

    public function getAll($filter){
        $this->db->select("tb_user.*");
        $this->db->join("tb_user", "tb_user.id = tb_notification.receiver_id","LEFT");
        $data = parent::all($filter);
        foreach($data as $index =>$user){
            $data[$index]->pic = getProfileImage((array)$user);
        }
        return $data;
    }
}
