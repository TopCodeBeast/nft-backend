<?php
defined('BASEPATH') or exit('No direct script access allowed');
class UserModel  extends AbstractModel
{
  var $_table = 'tb_user';
  public function register($data)
  {
    $email = $this->input->post('email');
    $query =  $this->db->query("select * from tb_user where email='$email'");
    if ($this->db->affected_rows() > 0) {
      $data[] =  array();
    } else {
      $this->db->insert('tb_user', $data);
      if ($this->db->affected_rows() > 0) {
        $user_id = $this->db->insert_id();
        $this->db->select('*');
        $this->db->from('tb_user');
        $this->db->where('id', $user_id);
        $query = $this->db->get();
        $result_array =  $query->result_array();
        $data = array();
        foreach ($result_array as $result) {
          if (!empty($result['profile_pic'])) {
            $string = $result['profile_pic'];
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
              // one or more of the 'special characters' found in $string
              // echo "found";
              $pic = $result['profile_pic'];
            } else {
              // echo "not found";
              $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
            }
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/user.png';
          }
          $data[] =  array(
            'userid'        => $result['id'],
            'name'      => $result['name'],
            'email'       => $result['email'],
            'university_name' => $result['university_name'],
            'phone_number' => $result['phone'],
            'profile_pic' => $pic
          );
        }

        return $data;
      }
    }
  }
  public function login()
  {
    $device_token = $this->input->post('device_token');
    $device_type = $this->input->post('device_type');
    $email = $this->input->post('email');
    $password = base64_encode($this->input->post('password'));
    // echo "email=>". $email.";pswd=>".$this->input->post('password'),"          ";
    $checkemail = $this->db->query("select * from tb_user where email='$email' ");
    if ($checkemail->num_rows() > 0) {
      $all_pics = $this->db->query("select * from tb_user where email='$email' and password='$password'");
      $result_array = $all_pics->result_array();
      $update = $this->db->query("update tb_user set device_token='$device_token',device_type='$device_type' WHERE email='$email'");
      $data = array();
      foreach ($result_array as $result) {
        $userid = $result['id'];
        $squad = $this->db->query("select count(*) as squad_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='squad' and req_status='1'");
        $result_squad = $squad->row();
        // print_r($result_squad);
        // echo ;die();
        $bestie = $this->db->query("select count(*) as bestie_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='bestie' and req_status='1'");
        $result_bestie = $bestie->row();
        $fan = $this->db->query("select count(*) as fan_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan'");
        $result_fan = $fan->row();
        $fanof = $this->db->query("select count(*) as fanof_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan_of'");
        $result_fanof = $fanof->row();
        date_default_timezone_set('Asia/Kolkata');
        $current_date    = date('Y-m-d H:i:s');
        $matches = $this->db->query("select count(*) as match_count from tb_match where invitation_status='accept' and (rival_id='$userid' or opponent_id='$userid')");
        $result_matches = $matches->row();
        // print_r($result_matches);die();
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        if ($result['online_offline_status'] == 0) {
          $onoffstatus = "online";
        } else {
          $onoffstatus = "offline";
        }
        $data[] =  array(
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'email'       => $result['email'],
          'university_name' => $result['university_name'],
          'profile_pic'      => $pic,
          'squad_count' => $result_squad->squad_count,
          'bestie_count' => $result_bestie->bestie_count,
          'fans_count' => $result_fan->fan_count,
          'fans_of_count' => $result_fanof->fanof_count,
          'matches_count' => $result_matches->match_count,
          'online_offline_status' => $onoffstatus,
          'last_seen' => $result['last_seen']
        );
      }

      return $data;
    } else {
      return "notexist";
    }
  }
  public function forget_password($email, $password)
  {
    //   echo $email;echo $password;die();
    $password = base64_encode($password);
    $query =  $this->db->query("select * from tb_user where email='$email'");
    if ($this->db->affected_rows() > 0) {
      $this->db->set('password', $password);
      $this->db->where('email', $email);
      $this->db->update('tb_user');
      if ($this->db->affected_rows() > 0) {
        return 'success';
      } else {
        return 'fail';
      }
    } else {
      return 'usernotexist';
    }
  }
  public function list_users()
  {
    $id = $this->input->post('userid');
    $query = $this->db->query("select * from tb_user where id!='$id'");
    $result_array =  $query->result_array();
    $data = array();
    foreach ($result_array as $result) {
      $userid = $result['id'];
      $check_userblocked = $this->db->query("select * from tb_bestie where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and req_status='2'");
      if ($check_userblocked->num_rows() > 0) {
        // echo "exist";
        $block=1;
              }else{
                // echo "not exist";
                $block=0;
              } 
              // die();
              if($block==0){
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $bestie = $this->db->query("select * from tb_bestie where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and category='bestie' and req_status='1'");
        if ($bestie->num_rows() > 0) {
          $bestie_list = "yes";
        } else {
          $bestie_list = "no";
        }
        $squad = $this->db->query("select * from tb_bestie where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and category='squad' and req_status='1'");
        if ($squad->num_rows() > 0) {
          $squad_list = "yes";
        } else {
          $squad_list = "no";
        }
        $fan = $this->db->query("select * from tb_fans where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and category='fan'");
        if ($fan->num_rows() > 0) {
          $fan_list = "yes";
        } else {
          $fan_list = "no";
        }
        $fanof = $this->db->query("select * from tb_fans where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and category='fan_of'");
        if ($fan->num_rows() > 0) {
          $fanof_list = "yes";
        } else {
          $fanof_list = "no";
        }
        $data[] =  array(
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'profile_pic'      => $pic,
          'bestie_category' => $bestie_list,
          'squad_category' => $squad_list,
          'fan_category' => $fan_list,
          'fanof_category' => $fanof_list,
        );
      }
    }
    return $data;
  }
  public function getPendingMatch($user_id){
    $result = $this->db->query("SELECT * FROM tb_match WHERE rival_id = '$user_id' AND match_type = 'open' AND invitation_status IS NULL")->result_array();
    $data = array();
    foreach($result as $item){
      $status = false;
      $users = $this->db->query("SELECT tb_matchusers.accept_status, tb_user.* FROM tb_matchusers 
      LEFT JOIN tb_user ON tb_user.id = tb_matchusers.opponent_id WHERE match_id = '" .$item['matchid']."' AND user_id = '$user_id'")->result_array();
      $tem_user = array();
      foreach($users as $user){
        if (!empty($user['profile_pic'])) {
          $string = $user['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $user['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $user['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $user['pic'] = $pic;
        if($user['accept_status'] == "accept"){
          $status = true;
        }
        array_push($tem_user, $user);
      }
      $item['status'] = $status;
      $item['users'] = $tem_user;
      array_push($data, $item);
    }
    return $data;
  }
  public function listusers()
  {
    $id = $this->input->post('userid');
    $query = $this->db->query("select * from tb_user where id!='$id'");
    $result_array =  $query->result_array();
    $data = array();
    foreach ($result_array as $result) {
      if (!empty($result['profile_pic'])) {
        $string = $result['profile_pic'];
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $result['profile_pic'];
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $data[] =  array(
        'userid'        => $result['id'],
        'profile_pic'      => $pic
      );
    }

    return $data;
  }
  public function updateprofile()
  {
    // $id = $this->input->post('userid');
    $name = $this->input->post('name');
    $phone = $this->input->post('phone');
    $email = $this->input->post('email');
    $uni_name = $this->input->post('university');
    $isocode = $this->input->post('code');
    $bio = $this->input->post('bio');
    $gender = $this->input->post('gender');

    $query =  $this->db->query("select * from tb_user where email='$email'");
    if ($this->db->affected_rows() > 0) {
      $result_array =  $query->row();
      $password = $result_array->password;
      // echo $password;die();
      $data = array(
        'name'  => $name,
        'phone' => $phone,
        'email'  => $email,
        'password' => $password,
        'university_name'  => $uni_name,
        'isocode'  => $isocode,
        'bio' => $bio,
        'gender' => $gender
      );
      $this->db->where('email', $email);
      $this->db->update('tb_user', $data);
      if ($this->db->affected_rows() > 0) {
        $this->db->select('*');
        $this->db->from('tb_user');
        $this->db->where('email', $email);
        $query = $this->db->get();
        $result_array =  $query->result_array();
        $data = array();

        foreach ($result_array as $result) {
          $userid = $result['id'];
          $squad = $this->db->query("select count(*) as squad_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='squad' and req_status='1'");
          $result_squad = $squad->row();
          // print_r($result_squad);
          // echo ;die();
          $bestie = $this->db->query("select count(*) as bestie_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='bestie' and req_status='1'");
          $result_bestie = $bestie->row();
          $fan = $this->db->query("select count(*) as fan_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan'");
          $result_fan = $fan->row();
          $fanof = $this->db->query("select count(*) as fanof_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan_of'");
          $result_fanof = $fanof->row();
          date_default_timezone_set('Asia/Kolkata');
          $current_date    = date('Y-m-d H:i:s');
          $matches = $this->db->query("select count(*) as match_count from tb_match where invitation_status='accept' and (rival_id='$userid' or opponent_id='$userid')");
          $result_matches = $matches->row();

          $data[] =  array(
            'userid'        => $result['id'],
            'name'      => $result['name'],
            'email'       => $result['email'],
            'university_name' => $result['university_name'],
            'phone_number' => $result['phone'],
            'country_code' => $result['isocode'],
            'bio' => $result['bio'],
            'gender' => $result['gender'],
            'squad_count' => $result_squad->squad_count,
            'bestie_count' => $result_bestie->bestie_count,
            'fans_count' => $result_fan->fan_count,
            'fans_of_count' => $result_fanof->fanof_count,
            'matches_count' => $result_matches->match_count
          );
        }

        return $data;
      }
    }
  }
  public function profilepicupdate($image)
  {
    $uid = $this->input->post('userid');
    $check = $this->db->query("update tb_user set profile_pic='$image' WHERE id='$uid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function matchReject($data){
    $query = "UPDATE tb_matchusers SET accept_status = '".$data['status']."' WHERE match_id = '".$data['matchid']."' AND opponent_id = '".$data['userid']."'";
    $this->db->query($query);
    $query = "SELECT count(*) count FROM tb_matchusers WHERE match_id = '".$data['matchid']."' AND ( accept_status = 'accept' OR accept_status IS NULL)";
    $reject_count = $this->db->query($query)->row()->count;
    if($reject_count == 0 ){
      $current_date = date("Y-m-d H:s:i");
      $check = $this->db->query("UPDATE tb_match SET invitation_status='reject', replied_at='$current_date', match_status='0' WHERE matchid='".$data['matchid']."'");
    }
    return 'success';
  }
  public function uploadedfile($image)
  {
    $uid = $this->input->post('userid');
    $send_to = $this->input->post('send_to');
    $data = array(
      'user_id' => $uid,
      'send_to' => $send_to,
      'filename' => $image
    );
    $this->db->insert('tb_userupload', $data);
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function getprofilepic($userid)
  {
    $result =  (array)$this->User->select($userid);
    $data = array();
    $userid = $result['id'];
    
    $squad = $this->Bestie->count(array("req_to"=>$userid, "category"=>"squad", "req_status"=>1));
    $bestie = $this->Bestie->count(array("req_from"=>$userid, "category"=>"bestie", "req_status"=>1));

    $fan = $this->Fan->count(array("req_from"=>$userid));
    
    $fanof = $this->Fan->count(array("req_to"=>$userid));

    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');

    $matches = $this->db->query("select count(*) as match_count from tb_match where invitation_status='accept' and (rival_id='$userid' or opponent_id='$userid')");

    $result_matches = $matches->row();
    
    $matchesinvitation = $this->db->query("select count(*) as invitationcount from tb_matchusers WHERE opponent_id='$userid' and `accept_status` IS NULL");
    
    $result_matcheinvitation = $matchesinvitation->row();

    $openmatch_count = $this->db->query("SELECT COUNT(*) as count from tb_match WHERE rival_id = '$userid' AND invitation_status IS NULL AND match_type = 'open'")->row()->count;

    if (!empty($result['profile_pic'])) {
      $string = $result['profile_pic'];
      if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
        // one or more of the 'special characters' found in $string
        // echo "found";
        $pic = $result['profile_pic'];
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
      }
    } else {
      // echo "not found";
      $pic = base_url() . 'uploads/profile_image/user.png';
    }
    $ongoinmatch_count = $this->db->query("SELECT count(*) count FROM tb_match WHERE invitation_status = 'accept' and match_type = 'open' and match_end> CURRENT_DATE()")->row()->count;
    // $total_notification = $result_squad->squad_count + $result_fan->fan_count +$openmatch_count + $result_matcheinvitation->invitationcount;
    $data[] =  array(
      'userid'        => $result['id'],
      'name'      => $result['name'],
      'email'       => $result['email'],
      'university_name' => $result['university_name'],
      'phone_number' => $result['phone'],
      'country_code' => $result['isocode'],
      'bio' => $result['bio'],
      'gender' => $result['gender'],
      'profile_pic'      => $pic,
      'squad_count' => $squad,
      'bestie_count' => $bestie,
      'fans_count' => $fan,
      'fans_of_count' => $fanof,
      'matches_count' => $result_matches->match_count,
      'openmatch_count' => $openmatch_count,
      'ongoingmatch_count' => $ongoinmatch_count,
      'matches_invitationcount' => $result_matcheinvitation->invitationcount
    );
    return $data;
  }
  public function get_notification()
  {
    $userid = $this->input->post('userid');
    $bquery =  $this->db->query("select * from tb_notification where tb_notification.receiver_id='$userid' and notification_status='Add_bestie' and read_status='0' ");
    if ($this->db->affected_rows() > 0) {
      $bresult_array =  $bquery->result_array();
      $bestie = array();
      foreach ($bresult_array as $result) {
        $bestie[] =  array(
          'notification_id' => $result['not_id'],
          'request_id'        => $result['request_id'],
          'message'      => $result['message'],
          'category'      => $result['notification_status'],
        );
      }
      $data['Bestie'] = $bestie;
    } else {
      $data['Bestie'] =  array();
    }
    // print_r($data['Bestie']);
    // die();
    $squery =  $this->db->query("select * from tb_notification where tb_notification.receiver_id='$userid' and notification_status='Add_squad' and read_status='0' ");
    if ($this->db->affected_rows() > 0) {
      $sresult_array =  $squery->result_array();
      $squad = array();
      foreach ($sresult_array as $results) {
        $squad[] =  array(
          'notification_id' => $results['not_id'],
          'request_id'        => $results['request_id'],
          'message'      => $results['message'],
          'category'      => $results['notification_status'],
        );
      }
      $data['Squad'] = $squad;
    } else {
      $data['Squad'] =  array();
    }
    $fquery =  $this->db->query("select * from tb_notification where tb_notification.receiver_id='$userid' and notification_status='Add_fan' and read_status='0' ");
    if ($this->db->affected_rows() > 0) {
      $fresult_array =  $fquery->result_array();
      $fan = array();
      foreach ($fresult_array as $fresults) {
        $fan[] =  array(
          'notification_id' => $fresults['not_id'],
          'request_id'        => $fresults['request_id'],
          'message'      => $fresults['message'],
          'category'      => $fresults['notification_status'],
        );
      }
      $data['Fan'] = $fan;
    } else {
      $data['Fan'] =  array();
    }
    return $data;
  }
  public function startPendingMatch($matchid){
    $openmatch = $this->db->query("select * from tb_match WHERE matchid='$matchid' and `match_type` = 'open'")->row();
    $time = $openmatch->time_duration;
    $current_date = date("Y-m-d H:s:i");
    $match_end = date('Y-m-d H:i:s', strtotime($time));
    $check = $this->db->query("update tb_match set invitation_status='accept', replied_at='$current_date',  match_end='$match_end',match_status='1' WHERE matchid='$matchid'");
    return 'success';

    // $result = $this->db->query("select * from  tb_fans WHERE fan_id='$reqid'");
    // if ($this->db->affected_rows() > 0) {
    //   $check = $this->db->query("update tb_fans set req_status='$status' WHERE fan_id='$reqid'");
    //   if ($this->db->affected_rows() > 0) {
    //     $output =  $result->row();
    //     $from = $output->req_from;
    //     $to = $output->req_to;
    //     $query =  $this->db->query("select * from tb_user where id='$from'"); //sender
    //     $queryto =  $this->db->query("select * from tb_user where id='$to'"); //receiver
    //     $result_array =  $query->row();
    //     $senderdevtoken = $result_array->device_token;
    //     $sendermessage = 'Your request is ' . $stat . ' by ' . $result_array->name;
    //     $senderdevicetype = $result_array->device_type;
    //     $resultarray =  $queryto->row();
    //     $receiverdevtoken = $resultarray->device_token;
    //     $receivermessage = 'You have ' . $stat . ' request from' . $resultarray->name;
    //     $receiverdevicetype = $resultarray->device_type;
    //     $data = array(
    //       'sender_id' => $this->input->post('from'),
    //       'receiver_id' => $this->input->post('to'),
    //       'message' => 'Your request is ' . $stat . ' by ' . $result_array->name,
    //       'notification_status' => 'accept_or_reject_' . $category,
    //       'request_id' => $reqid
    //     );
    //     $this->db->insert('tb_notification', $data);
    //     $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
    //     $datas = array(
    //       'sender_id' => $this->input->post('to'),
    //       'receiver_id' => $this->input->post('from'),
    //       'message' =>  'You have ' . $status . ' request from' . $resultarray->name,
    //       'notification_status' => 'accept_or_reject_' . $category,
    //       'request_id' => $reqid
    //     );
    //     $this->db->insert('tb_notification', $datas);
    //     $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
    //     return "success";
    //   } else {
    //     return "fail";
    //   }
    // }
  }
  public function matchAccept(){
    $match_id = $this->input->post('matchid');
    $user_id = $this->input->post('userid');
    $this->db->query("UPDATE tb_matchusers SET accept_status = 'accept' WHERE match_id = '".$match_id."' AND opponent_id = '$user_id'");

    $query = "SELECT count(*) count FROM tb_matchusers WHERE match_id = '$match_id' AND ( accept_status = 'reject' OR accept_status IS NULL)";
    $accept_count = $this->db->query($query)->row()->count;
    if($accept_count == 0 ){
      $result = $this->db->query("SELECT * FROM tb_match WHERE matchid='$match_id' and `match_type` = 'open'")->row();
      $time = $result->time_duration;
      $current_date = date("Y-m-d H:s:i");
      $match_end = date('Y-m-d H:i:s', strtotime($time));
      $check = $this->db->query("update tb_match set invitation_status='accept',replied_at='$current_date',  match_end='$match_end',match_status='1' WHERE matchid='$match_id'");
    }
    return 'success';
  }
  public function accept_or_reject()
  {
    $reqid = $this->input->post('request_id');
    $status = $this->input->post('status');
    $category = $this->input->post('category');
    if ($status == '1') {
      $stat = "accepted";
    }
    if ($status == '0') {
      $stat = "rejected";
    }
    if ($status == '2') {
      $stat = "block";
    }
    if (($category == "Add_bestie") || ($category == "Add_squad")) {
      $result = $this->db->query("select * from  tb_bestie WHERE bestie_id='$reqid'");
      if ($this->db->affected_rows() > 0) {
        $check = $this->db->query("update tb_bestie set req_status='$status' WHERE bestie_id='$reqid'");
        if ($this->db->affected_rows() > 0) {
          $output =  $result->row();
          $check = $this->db->query("update tb_notification set read_status='1' WHERE request_id='$reqid'");
          $from = $output->req_from;
          $to = $output->req_to;
          $query =  $this->db->query("select * from tb_user where id='$from'"); //sender
          $queryto =  $this->db->query("select * from tb_user where id='$to'"); //receiver
          $result_array =  $query->row(); //from
          $resultarray =  $queryto->row(); //to
          $senderdevtoken = $result_array->device_token; //from
          $sendermessage = 'Your request is ' . $stat . ' by ' . $resultarray->name; //from
          $senderdevicetype = $result_array->device_type; //from
          $receiverdevtoken = $resultarray->device_token;
          $receivermessage = 'You have ' . $stat . ' request from ' . $result_array->name; //to
          $receiverdevicetype = $resultarray->device_type;
          $data = array(
            'sender_id' => $from,
            'receiver_id' => $to,
            'message' => 'Your request is ' . $stat . ' by ' . $resultarray->name,
            'notification_status' => 'accept_or_reject_' . $category,
            'request_id' => $reqid
          );
          if ($status != '2') {
            $this->db->insert('tb_notification', $data);
            $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
          }
          $datas = array(
            'sender_id' => $to,
            'receiver_id' => $from,
            'message' =>  'You have ' . $stat . ' request from ' . $result_array->name,
            'notification_status' => 'accept_or_reject_' . $category,
            'request_id' => $reqid
          );
          if ($status != '2') {
            $this->db->insert('tb_notification', $datas);
            $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
          }
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      $result = $this->db->query("select * from  tb_fans WHERE fan_id='$reqid'");
      if ($this->db->affected_rows() > 0) {
        $check = $this->db->query("update tb_fans set req_status='$status' WHERE fan_id='$reqid'");
        if ($this->db->affected_rows() > 0) {
          $output =  $result->row();
          $from = $output->req_from;
          $to = $output->req_to;
          $query =  $this->db->query("select * from tb_user where id='$from'"); //sender
          $queryto =  $this->db->query("select * from tb_user where id='$to'"); //receiver
          $result_array =  $query->row();
          $senderdevtoken = $result_array->device_token;
          $sendermessage = 'Your request is ' . $stat . ' by ' . $result_array->name;
          $senderdevicetype = $result_array->device_type;
          $resultarray =  $queryto->row();
          $receiverdevtoken = $resultarray->device_token;
          $receivermessage = 'You have ' . $stat . ' request from' . $resultarray->name;
          $receiverdevicetype = $resultarray->device_type;
          $data = array(
            'sender_id' => $this->input->post('from'),
            'receiver_id' => $this->input->post('to'),
            'message' => 'Your request is ' . $stat . ' by ' . $result_array->name,
            'notification_status' => 'accept_or_reject_' . $category,
            'request_id' => $reqid
          );
          $this->db->insert('tb_notification', $data);
          $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
          $datas = array(
            'sender_id' => $this->input->post('to'),
            'receiver_id' => $this->input->post('from'),
            'message' =>  'You have ' . $status . ' request from' . $resultarray->name,
            'notification_status' => 'accept_or_reject_' . $category,
            'request_id' => $reqid
          );
          $this->db->insert('tb_notification', $datas);
          $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
          return "success";
        } else {
          return "fail";
        }
      }
    }
  }
  public function list_besties($id)
  {
    // echo $id;
    $query = $this->db->query("SELECT * FROM `tb_bestie` WHERE (req_from='$id' or req_to='$id') and category='bestie' and req_status='1' ");
    // echo "SELECT * FROM `tb_bestie` WHERE req_from='$id' or req_to='$id' ";
    $result_array =  $query->result_array();
    // print_r( $result_array);die();
    $data = array();
    foreach ($result_array as $results) {
      // print_r($results['req_from']);die();
      if ($results['req_from'] == $id) {
        $userid = $results['req_to'];
      } else {
        $userid = $results['req_from'];
      }
      // print_r($userid);die();
      $result_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
      $output =  $result_query->result_array();
      foreach ($output as $result) {
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $bid = $result['id'];
        $result_likequery = $this->db->query("SELECT count(*) as totallike FROM `tb_bestielike` WHERE bestie_id='$bid' and like_status='like'");
        $likeoutput =  $result_likequery->row();
        $result_commentquery = $this->db->query("SELECT count(*) as totalcomment FROM `tb_bestiecomment` WHERE bestie_id='$bid'");
        $commentoutput =  $result_commentquery->row();
        $data[] =  array(
          'tableid'        => $results['bestie_id'],
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'email'       => $result['email'],
          'university_name' => $result['university_name'],
          'profile_pic'      => $pic,
          'category' => $results['category'],
          'likes' => $likeoutput->totallike,
          'comments' => $commentoutput->totalcomment,
        );
      }
    }

    return $data;
  }
  public function list_squad($id)
  {
    // echo $id;
    $query = $this->db->query("SELECT * FROM `tb_bestie` WHERE (req_from='$id' or req_to='$id') and category='squad' and req_status='1'");
    // echo "SELECT * FROM `tb_bestie` WHERE req_from='$id' or req_to='$id' ";
    $result_array =  $query->result_array();
    // print_r( $result_array);die();
    $data = array();
    foreach ($result_array as $results) {
      // print_r($results['req_from']);die();
      if ($results['req_from'] == $id) {
        $userid = $results['req_to'];
      } else {
        $userid = $results['req_from'];
      }
      // print_r($userid);die();
      $result_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
      $output =  $result_query->result_array();
      foreach ($output as $result) {
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $data[] =  array(
          'tableid'        => $results['bestie_id'],
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'email'       => $result['email'],
          'university_name' => $result['university_name'],
          'profile_pic'      => $pic,
          'category' => $results['category'],
          'likes' => ' ',
          'comments' => ' ',
        );
      }
    }

    return $data;
  }
  public function statusbar()
  {
    $userid = $this->input->post('userid');
    $category = $this->input->post('category');
    $text = $this->input->post('text');
    $send_to = $this->input->post('send_to');
    // echo $text;die();
    $data = array(
      'userid' => $this->input->post('userid'),
      'category' => $this->input->post('category'),
      'text' => $this->input->post('text'),
      'send_to' => $this->input->post('send_to'),
    );
    $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$userid' and category='$category' and send_to='$send_to' ");
    if ($this->db->affected_rows() > 0) {
      // echo "yes";die();
      $check = $this->db->query("update tb_statusbar set text='$text' WHERE userid='$userid' and category='$category' and send_to='$send_to'");
    } else {
      $this->db->insert('tb_statusbar', $data);
    }
    return $data;
  }
  public function getstatus()
  {
    $userid = $this->input->post('userid');
    $senderid = $this->input->post('senderid');
    $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$userid' and send_to='$senderid' and category='story'");
    $output = $result_query->result_array();
    $sresult_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$userid' and send_to='$senderid'");
    $status_output = $sresult_query->row();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $data['story'] =  array(
        'statusid'        => $result['statusid'],
        'category'      => $result['category'],
        'text'       => $result['text']
      );
    }
    if (!empty($status_output->disable_status)) {
      $stat = $status_output->disable_status;
    } else {
      $stat = "0";
    }
    $data['diable_status'] = $stat;
    // print_r($data);die();
    return $data;
  }
  public function getuploadfile() //get user uploaded file in besties
  {
    $userid = $this->input->post('userid');
    $senderid = $this->input->post('senderid');
    $result_query = $this->db->query("SELECT * FROM `tb_userupload` WHERE user_id='$userid' and send_to='$senderid'");
    if ($result_query->num_rows() > 0) {
      $output = $result_query->result_array();
      // print_r($output);die();
      $data = array();
      foreach ($output as $result) {
        $data[] =  array(
          'file_link'        => base_url() . 'uploads/Useruploads/' . $result['filename']
        );
      }
    } else {
      $data[] =  array(
        'file_link'        => base_url() . 'uploads/Useruploads/upload_default.jpg'
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function getstatus_letter()
  {
    $userid = $this->input->post('userid');
    $senderid = $this->input->post('senderid');
    $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$userid' and send_to='$senderid' and category='letter'");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $data[] =  array(
        'statusid'        => $result['statusid'],
        'category'      => $result['category'],
        'text'       => $result['text']
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function getstatus_slogan()
  {
    $userid = $this->input->post('userid');
    $senderid = $this->input->post('senderid');
    $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$userid' and send_to='$senderid' and category='slogan'");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $data[] =  array(
        'statusid'        => $result['statusid'],
        'category'      => $result['category'],
        'text'       => $result['text']
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function match_invitation()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $rival_id = $this->input->post('rival_id');
    $opponent_id = $this->input->post('opponent_id');
    $category = $this->input->post('category');
    $uploadid = $this->input->post('uploaded_id');
    $data = array(
      'rival_id' => $this->input->post('rival_id'),
      'opponent_id' => $this->input->post('opponent_id'),
      'description' => $this->input->post('description'),
      'time_duration' => $this->input->post('time_duration'),
      'caption' => $this->input->post('caption'),
      'category' => $this->input->post('category'),
      'created_at' => $current_date
    );
    $query =  $this->db->query("select * from tb_user where id='$rival_id'");
    $queryto =  $this->db->query("select * from tb_user where id='$opponent_id'");
    if ($this->db->affected_rows() > 0) {
      // echo "select * from tb_bestie where (req_from='$from' and req_to='$to') or (req_from='$to' and req_to='$from') and category='$category'";die();
      $checkresult_query =  $this->db->query("select * from tb_match where (rival_id='$rival_id' and opponent_id='$opponent_id') and `invitation_status` IS NULL");
      // if ($this->db->affected_rows() > 0) {
      //   return "exist";
      // } else {
      $result_query =  $this->db->query("select * from tb_match where (rival_id='$rival_id' and opponent_id='$opponent_id') and category='$category' and match_end>'$current_date'");
      // if ($this->db->affected_rows() > 0) {
      //   return "exist";
      // } else {
      $this->db->insert('tb_match', $data);
      $match_id = $this->db->insert_id();
      if ($this->db->affected_rows() > 0) {
        $result_array =  $query->row();
        $resultarray =  $queryto->row();
        // $imagedata = array(
        //   'matchid' => $matchid,
        //   'user_uploaded' =>$rival_id,
        //   'filename' => $imagename,
        //   'created_at' => $current_date
        // );
        //   $this->db->insert('tb_matchupload', $imagedata );
        $check = $this->db->query("update tb_matchupload set matchid='$match_id' WHERE mup_id= $uploadid ");

        
        $receiverdevtoken = $resultarray->device_token;
        $receivermessage = $result_array->name . ' ' . 'Invited you from ' . $category . ' list to have a Match of ' . $this->input->post('time_duration') . ' duration';
        $receiverdevicetype = $resultarray->device_type;
        $data = array(
          'sender_id' => $opponent_id,
          'receiver_id' => $rival_id,
          'message' => 'You have Invited ' . $resultarray->name . ' from ' . $category . ' list to have a Match of ' . $this->input->post('time_duration') . ' duration',
          'notification_status' => 'Match_invitation',
          'request_id' => $match_id
        );
        $this->db->insert('tb_notification', $data);
        // echo $receiverdevtoken; echo $receivermessage; echo $receiverdevicetype.'receiverdata';
        $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
        // $resultdata=array(
        //   'match_id' => $matchid,
        //   'file_link'=>base_url() . 'uploads/Matchuploads/'.$imagename
        // );
        return "success";
      } else {
        return "fail";
      }
      // }
      // }
    } else {
      return "nouser";
    }
  }
  public function get_existingmatch()
  {
    $rival_id = $this->input->post('rival_id');
    $opponent_id = $this->input->post('opponent_id');
    $category = $this->input->post('category');
    $result_query =  $this->db->query("select * from tb_match where (rival_id='$rival_id' and opponent_id='$opponent_id') and category='$category'");
    if ($this->db->affected_rows() > 0) {
      // return "exist";
      $output = $result_query->row();
      $queryto =  $this->db->query("select * from tb_user where id='$opponent_id'");
      $outputqueryto = $queryto->row();
      $data[] = array(
        'matchid' => $output->matchid,
        'reciever_name' => $outputqueryto->name,
        'description' => $output->description,
        'caption' => $output->caption,
        'duration' => $output->time_duration
      );
    }
    return $data;
  }
  public function Match_accept_or_reject()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    
    $reqid = $this->input->post('request_id');
    $status = $this->input->post('status');
    $userid = $this->input->post('user_id');
    
    $this->db->query("update tb_matchusers set accept_status='$status' WHERE match_id='$reqid' AND opponent_id = '$userid'");
    $matchuser = $this->db->query("SELECT * FROM tb_matchusers WHERE match_id='$reqid' AND opponent_id = '$userid'")->row();
    $result = $this->db->query("select * from tb_match WHERE matchid='$reqid' and `match_type` = 'open'");
    if ($this->db->affected_rows() > 0) {
      $output =  $result->row();
      if($output->invitation_status != 'accept'){
        if ($status == 'accept') {
          $match_status = 1;
          $time = $output->time_duration;
          $match_end = date('Y-m-d H:i:s', strtotime($time));
          $check = $this->db->query("update tb_match set invitation_status='$status',replied_at='$current_date',  match_end='$match_end',match_status=$match_status WHERE matchid='$reqid'");
        } 
      }
        
      $from = $matchuser->opponent_id;
      $to = $matchuser->user_id;
      $fdata = $this->db->query("SELECT * FROM `tb_user` WHERE id='$from'")->row();
      $fromname = $fdata->name;
      $tdata = $this->db->query("SELECT * FROM `tb_user` WHERE id='$to'")->row();
      $toname = $tdata->name;
      if ($status == "accept") { //only send push notification if accepted the request
        //fans of rival start
        $rivalfanquery = $this->db->query("SELECT * FROM `tb_fans` WHERE (req_from='$from' or req_to='$from') and category='fan'");
        $rivalfanresult_array =  $rivalfanquery->result_array();

        foreach ($rivalfanresult_array as $results) {
          if ($results['req_from'] == $from) {
            $rivalfanuserid = $results['req_to'];
          } else {
            $rivalfanuserid = $results['req_from'];
          }
          $rivalfanresult_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$rivalfanuserid'");
          if ($rivalfanresult_query->num_rows() > 0) {
            $rivalfanoutput =  $rivalfanresult_query->row();
            $rivalfandevicetoken = $rivalfanoutput->device_token;
            $rivalfandevicetype = $rivalfanoutput->device_type;
            $pushmessage = "$fromname added a new open match with $toname, Open app to view the contest";
            $datas = array(
              'sender_id' => $from,
              'receiver_id' => $to,
              'message' => $pushmessage,
              'notification_status' => 'fan_notification',
              'request_id' => " "
            );
            $this->db->insert('tb_notification', $datas);
            $this->push($rivalfandevicetoken, $pushmessage, $rivalfandevicetype);
          }
        }
        
        $opponentfanquery = $this->db->query("SELECT * FROM `tb_fans` WHERE (req_from='$to' or req_to='$to') and category='fan'");
        $opponentfanresult_array =  $opponentfanquery->result_array();

        foreach ($opponentfanresult_array as $results) {
          if ($results['req_from'] == $to) {
            $opponentfanuserid = $results['req_to'];
          } else {
            $opponentfanuserid = $results['req_from'];
          }
          $opponentfanresult_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$opponentfanuserid'");
          if ($opponentfanresult_query->num_rows() > 0) {
            $opponentfanoutput =  $opponentfanresult_query->row();
            $opponentfandevicetoken = $opponentfanoutput->device_token;
            $opponentfandevicetype = $opponentfanoutput->device_type;
            $pushmessage = "$fromname added a new open match with $toname, Open app to view the contest";
            $datas = array(
              'sender_id' => $from,
              'receiver_id' => $to,
              'message' => $pushmessage,
              'notification_status' => 'fan_notification',
              'request_id' => " "
            );
            $this->db->insert('tb_notification', $datas);
            $this->push($opponentfandevicetoken, $pushmessage, $opponentfandevicetype);
            
          }
        }
      }
      $result_array =  $this->db->query("select * from tb_user where id='$from'")->row();
      $resultarray =  $this->db->query("select * from tb_user where id='$to'")->row();
      if ($status == "accept") {
        $message = "accepted";
        $sendermessage = 'Your Invitation is ' . $message . ' by ' . $resultarray->name;
      } else {
        $message = "rejected";
        $sendermessage = 'Oops!  ' . $resultarray->name . ' has chickened Out.  Congrats!!  You Won Already! ';
      }
      $senderdevtoken = $result_array->device_token;
      $senderdevicetype = $result_array->device_type;
      $data = array(
        'sender_id' => $to,
        'receiver_id' => $from,
        'message' => 'Your Invitation is ' . $message . ' by ' . $resultarray->name,
        'notification_status' => 'match_reply',
        'request_id' => $reqid
      );

      $this->db->insert('tb_notification', $data);
      $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
      $receiverdevtoken = $resultarray->device_token;
      $receivermessage = 'You have ' . $message . ' Invitation from ' . $result_array->name;
      $receiverdevicetype = $resultarray->device_type;
      $datas = array(
        'sender_id' => $from,
        'receiver_id' => $to,
        'message' =>  'You have ' . $message . ' Invitation from ' . $result_array->name,
        'notification_status' => 'match_reply',
        'request_id' => $reqid
      );
      $this->db->insert('tb_notification', $datas);
      $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
      return "success";
    } else {
      return "noinvitation";
    }
  }

  public function get_myinvitation()
  {
    $userid = $this->input->post('userid');
    $result = $this->db->query("select * from tb_matchusers  LEFT JOIN tb_match on tb_matchusers.match_id = tb_match.matchid
     WHERE tb_matchusers.opponent_id='$userid' and tb_matchusers.`accept_status` IS NULL and tb_match.match_type='open'");
    if ($this->db->affected_rows() > 0) {
      $result_array = $result->result_array();
      $data = array();
      foreach ($result_array as $result) {
        $uid = $result['user_id'];
        $matchid = $result['match_id'];
        $query = $this->db->query("select * from tb_user WHERE id='$uid'");
        $output = $query->row();
        if (!empty($output->profile_pic)) {
          $string = $output->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $output->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        // $matchsenderfilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$userid' and matchid=$matchid"); //rival
        // if ($matchsenderfilequery->num_rows() > 0) {
        //   $senderfileoutput = $matchsenderfilequery->row();

        //   $sfilename = $senderfileoutput->filename;
        //   $sfiletype = $senderfileoutput->filetype;
        //   if ($sfiletype == 'file') {
        //     $sfile = base_url() . 'uploads/Matchuploads/' . $sfilename;
        //   } else {
        //     $sfile = $sfilename;
        //   }
        // } else {
        //   $sfile = base_url() . 'assets/images/splash.jpg';
        //   $sfiletype = "";
        // }
        // $matchoppofilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$uid' and matchid=$matchid"); //rival
        // if ($matchoppofilequery->num_rows() > 0) {
        //   $oppofileoutput = $matchoppofilequery->row();

        //   $ofilename = $oppofileoutput->filename;
        //   $ofiletype = $oppofileoutput->filetype;
        //   if ($ofiletype == 'file') {
        //     $ofile = base_url() . 'uploads/Matchuploads/' . $ofilename;
        //   } else {
        //     $ofile = $ofilename;
        //   }
        // } else {
        //   $ofile = base_url() . 'assets/images/splash.jpg';
        //   $ofiletype = "";
        // }
        $data[] =  array(
          'match_id'        => $result['matchid'],
          // 'senderid'        => $result['rival_id'],
          // 'sender_name' => $output->name,
          // 'receiverid'      => $result['opponent_id'],
          'description'       => $result['description'],
          'time_duration'        => $result['time_duration'],
          'caption'      => $result['caption'],
          'category'       => $result['category'],
          'user_profile' => $pic,
          // 'sender_image' => $sfile,
          // 'sender_image_type' => $sfiletype,
          // 'receiver_image' => $ofile,
          // 'receiver_image_type' => $ofiletype
        );
      }

      return $data;
    }
  }
  public function getmyinvitation_files()
  {
    $userid = $this->input->post('userid');
    $result = $this->db->query("select * from tb_match WHERE opponent_id='$userid' AND `invitation_status` IS NULL");
    if ($this->db->affected_rows() > 0) {
      $result_array = $result->result_array();
      $data = array();
      foreach ($result_array as $results) {
        $mid = $results['matchid'];
        $query = $this->db->query("select * from tb_matchupload WHERE matchid='$mid'");
        $output = $query->result_array();
        foreach ($output as $result) {
          $filetype = $result['filetype'];
          if ($filetype == 'file') {
            $file = base_url() . 'uploads/Matchuploads/' . $result['filename'];
          } else {
            $file = $result['filename'];
          }
          $data[] =  array(
            'id'        => $result['mup_id'],
            'matchid'       => $result['matchid'],
            'useruploaded'        => $result['user_uploaded'],
            'filename'      => $file,
            'filetype' => $result['filetype'],
            'created_at'        => $result['created_at']
          );
        }
      }
      return $data;
    }
  }
  public function get_personalmatch() //the matches that i have done competing my own medias.
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $user_id = $this->input->post('userid');
    $data = array();
    $result = $this->db->query(" select * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE rival_id='$user_id' and opponent_id='$user_id' and tb_match.match_type ='personal' GROUP by tb_match.matchid");
    
    if ($this->db->affected_rows() > 0) {
      $result_array = $result->result_array();
      $data = array();
      foreach ($result_array as $result) {
        $matchid = $result['matchid'];
        $uid = $result['rival_id'];
        $output = $this->db->query("select * from tb_user WHERE id='$uid'")->row();
        if (!empty($output->profile_pic)) {
          $string = $output->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            $pic = $output->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $likeresult = $this->db->query("select count(*) as total_like from tb_like WHERE matchid='$matchid' and like_status='like'")->row();

        $commentoutput = $this->db->query("select count(*) as total_comments from tb_comment WHERE match_id='$matchid' and contestent_id!='0'")->row();

        $commoncommentoutput = $this->db->query("select count(*) as total_commoncomments from tb_comment WHERE match_id='$matchid' AND `contestent_id` = 0")->row();

        $userlike = $this->db->query("select contestent_id from tb_like WHERE matchid='$matchid'");

        if ($userlike->num_rows() > 0) {
          $userlikes = $userlike->row();
          $contid = $userlikes->contestent_id;
          if ($contid == $uid) { //contested and rival are same
            $userrivallike = '1';
            $useroppolike = '0';
            $usercommonlike = '0';
            $agree = '0';
            $disagree = '0';
          }
          if ($contid == 0) {
            $userrivallike = '0';
            $useroppolike = '0';
            $usercommonlike = '1';
            
            $commonquerylikeoutput = $this->db->query("select count(*) as common_like from tb_like WHERE contestent_id='0' and matchid='$matchid' and like_status='like'")->row();
            
            $commonquerydislikeoutput = $this->db->query("select count(*) as common_dislike from tb_like WHERE contestent_id='0' and matchid='$matchid' and like_status='dislike'")->row();

            $agree = $commonquerylikeoutput->common_like;
            $disagree = $commonquerydislikeoutput->common_dislike;
          }
          if ($contid == $user_id) {
            $userrivallike = '0';
            $useroppolike = '1';
            $usercommonlike = '0';
            $agree = '0';
            $disagree = '0';
          }
        } else {
          $userrivallike = '0';
          $useroppolike = '0';
          $usercommonlike = '0';
          $agree = '0';
          $disagree = '0';
        }
        $uploadedfile = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `user_uploaded` = '$user_id' AND `matchid` = '$matchid'");
        $total_like = 0;
        if ($uploadedfile->num_rows() > 0) {
          $up_files = $uploadedfile->result_array();
          $compare_result = array();
          foreach($up_files as $item){
            $media_id = $item['mup_id'];
            $media_type = $item['filetype'];
            $sub_caption = $item['sub_caption'];
            if ($media_type == 'file') {
              $media = base_url() . 'uploads/Matchuploads/' . $item['filename'];
            } else {
              $media = $item['filename'];
            }
            //to find if this image is used for any type of closed match
            $check_query = $this->db->query("SELECT * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
            if ($check_query->num_rows() > 0) {
              $image_status = 1;
            } else {
              $image_status = 0;
            }

            //to find the cound of revealed closed match of this particular image
            $count_revealquery = $this->db->query("SELECT count(*) as sr_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='1' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
            $countrevealresult = $count_revealquery->row();
            $r_count = $countrevealresult->sr_count;

              //to find the cound of anonymous closed match of this particular image
            $count_anonyquery = $this->db->query("SELECT count(*) as sa_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='0' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid' ORDER BY `tb_match`.`matchid` DESC");
            $scountanonyresult = $count_anonyquery->row();
            $a_count = $scountanonyresult->sa_count;

            //to find how many wins with this image for a closed match
            $wincount_query = $this->db->query(" SELECT *,tb_match.matchid as id from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' ");
            $resultwin = $wincount_query->result_array();
            $win = 0;
            $loss = 0;
            $winner_name = array();
            $loser_name = array();

            foreach ($resultwin as $s) {
              $mid = $s['id'];
              $created_at = $s['created_at'];
              $matchquery = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `matchid` ='$mid'");
              $match_result = $matchquery->result_array();
              $picarray = array();
              foreach ($match_result as $sm) {
                $first_imageftype = $sm['filetype'];
                if (!empty($sm['filename'])) {
                  if ($first_imageftype == 'file') {
                    $first_image =  base_url() . 'uploads/Matchuploads/' . $sm['filename'];
                  } else {
                    $first_image = $sm['filename'];
                  }
                  $picarray[] = array(
                    'closed_image' => $first_image
                  );
                } else {
                  $picarray[] = array(
                    'closed_image' => " "
                  );
                }
              }
              $id = $s['rival_id'];
              $oppid = $s['opponent_id']; //user listing the personal match will always be opponent
              $match_seenstatus = $s['seen_status'];
              //to select the name of the winner and loser
              $rivaldetails_query = $this->db->query("SELECT * FROM `tb_user` where id='$id'");
              $rivaldetails = $rivaldetails_query->row();
              $rivalname = $rivaldetails->name;
              //
              $rivallike_query = $this->db->query("SELECT count(*) as ri_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$id'");
              $rivallike = $rivallike_query->row();
              $rlike = $rivallike->ri_like;
              $oppolike_query = $this->db->query("SELECT count(*) as op_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$oppid'");
              $oppolike = $oppolike_query->row();
              $olike = $oppolike->op_like;
              $totalmatchlike = $rlike + $olike;
              if (($rlike == 0) && ($olike == 0)) {
                $win = $win + 0;
                $loss = $loss + 0;
                $winner_name[] = array(
                  'winner_name' => "",
                  'closed_match_images' => " ",
                  'closed_match_likes' => "",
                  'created_at' => ""
                );

                $loser_name[] = array(
                  'loser_name' => "",
                  'closed_match_images' => " ",
                  'closed_match_likes' => "",
                  'created_at' => ""
                );
              } else if ($rlike > $olike) {
                //rival win, then this match is lost for the personal match listing user
                $win = $win + 0;
                $loss = $loss + 1;
                if ($match_seenstatus == 1) //getting only revealed names
                {
                  $winner_name[] = array(
                    'winner_name' => $rivalname,
                    'closed_match_images' => $picarray,
                    'closed_match_likes' => $olike . '/' . $totalmatchlike,
                    'created_at' => $created_at
                  );
                }
              } else {
                //oppo win,then this match is won for the personal match listing user
                $win = $swin + 1;
                $loss = $sloss + 0;
                if ($match_seenstatus == 1) {
                  $loser_name[] = array(
                    'loser_name' => $rivalname,
                    'closed_match_images' => $picarray,
                    'closed_match_likes' => $olike . '/' . $stotalmatchlike,
                    'created_at' => $created_at
                  );
                }
              }
            }
            $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE image_id='$media_id' and matchid='$matchid' and like_status='like'")->row(); //likes of rival

            $like = $likeoutput->sender_like;
            if ($media_type == 'file') {
              $file_extention = $item['filename'];
              $exresult = explode(".", $file_extention);
              $extension = $exresult[1];
              if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
                //sfiletype is audio
                $filetype = "audio";
              } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
                //sfiletype is image
                $filetype = "image";
              } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
                //sfiletype is video
                $filetype = "video";
              }
            }else{
              $filetype = $media_type;
            }
            $temp = array(
              'id'        => $result['rival_id'],
              'name' => $output->name,
              'profile' => $pic,
              'email' => $output->email,
              'media' => $media,
              'media_id' => $media_id,
              'media_type' => $filetype,
              'media_closedstatus' => $image_status,
              'reveal_count' => $r_count,
              'anonymous_count' => $a_count,
              'media_win' => $win,
              'media_loss' => $loss,
              'media_winname' => $winner_name,
              'media_lossname' => $loser_name,
              'likecount' => $like,
              'sub_caption'=> $sub_caption
  
            );
            $total_like = $total_like+ $like;
            array_push($compare_result, $temp);
          }
        } else {
          $senderfile = base_url() . 'assets/images/splash.jpg';
          $receiverfile = base_url() . 'assets/images/splash.jpg';
        }
        $data[] =  array(
          'match_id'        => $result['matchid'],
          'description'       => $result['description'],
          'time_duration'        => $result['time_duration'],
          'caption'      => $result['caption'],
          'category'       => $result['category'],
          'match_start' => $result['replied_at'],
          'match_end' => $result['match_end'],
          'match_invitationsend' => $result['created_at'],
          'likes' => $likeresult->total_like,
          'compare_data' => $compare_result,
          'total_likecount' => $total_like,
          'total_comment' => $commentoutput->total_comments,
          'total_commoncomments' => $commoncommentoutput->total_commoncomments,
          'match_status' => '1',
        );
      }
    }
    return $data;
  }
  public function get_openmatch() //whom have send matches to me 
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $userid = $this->input->post('userid');
    $result_array = $this->db->query("select * from tb_match  WHERE rival_id='$userid' and match_type ='open'")->result_array();
    foreach ($result_array as $result) {
      $matchid = $result['matchid'];
      $output = $this->db->query("select * from tb_user WHERE id='$userid'")->row(); //rival
      $user['user'] = $output;
      $medias = array();
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          $pic = $output->profile_pic;
        } else {
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $compare_result = array();
      $user['pic'] = $pic;
      $matchuserfilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$userid' and matchid=$matchid"); //rival
      $total_like = 0;
      if ($matchuserfilequery->num_rows() > 0) {
        $up_files = $matchuserfilequery->result_array();
        $user = array();
        $user['user'] = $output;
        
        foreach($up_files as $item){
          $media_id = $item['mup_id'];
          $sub_caption = $item['sub_caption'];
          $media_type = $item['filetype'];
          if ($media_type == 'file') {
            $media = base_url() . 'uploads/Matchuploads/' . $item['filename'];
          } else {
            $media = $item['filename'];
          }
          //to find if this image is used for any type of closed match
          $check_query = $this->db->query("SELECT * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='open' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          if ($check_query->num_rows() > 0) {
            $image_status = 1;
          } else {
            $image_status = 0;
          }

          //to find the cound of revealed closed match of this particular image
          $count_revealquery = $this->db->query("SELECT count(*) as sr_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='open' and tb_match.seen_status='1' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          $countrevealresult = $count_revealquery->row();
          $r_count = $countrevealresult->sr_count;

          //to find the cound of anonymous closed match of this particular image
          $count_anonyquery = $this->db->query("SELECT count(*) as sa_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='open' and tb_match.seen_status='0' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid' ORDER BY `tb_match`.`matchid` DESC");
          $scountanonyresult = $count_anonyquery->row();
          $a_count = $scountanonyresult->sa_count;

          //to find how many wins with this image for a closed match
          $wincount_query = $this->db->query(" SELECT *,tb_match.matchid as id from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='open' and tb_matchupload.old_mupid='$media_id' ");
          $resultwin = $wincount_query->result_array();
          $win = 0;
          $loss = 0;
          $winner_name = array();
          $loser_name = array();

          foreach ($resultwin as $s) {
            $mid = $s['id'];
            $created_at = $s['created_at'];
            $matchquery = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `matchid` ='$mid'");
            $match_result = $matchquery->result_array();
            $picarray = array();
            foreach ($match_result as $sm) {
              $first_imageftype = $sm['filetype'];
              if (!empty($sm['filename'])) {
                if ($first_imageftype == 'file') {
                  $first_image =  base_url() . 'uploads/Matchuploads/' . $sm['filename'];
                } else {
                  $first_image = $sm['filename'];
                }
                $picarray[] = array(
                  'closed_image' => $first_image
                );
              } else {
                $picarray[] = array(
                  'closed_image' => " "
                );
              }
            }
            $id = $s['rival_id'];
            $oppid = $s['opponent_id']; //user listing the personal match will always be opponent
            $match_seenstatus = $s['seen_status'];
            //to select the name of the winner and loser
            $rivaldetails_query = $this->db->query("SELECT * FROM `tb_user` where id='$id'");
            $rivaldetails = $rivaldetails_query->row();
            $rivalname = $rivaldetails->name;
            //
            $rivallike_query = $this->db->query("SELECT count(*) as ri_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$id'");
            $rivallike = $rivallike_query->row();
            $rlike = $rivallike->ri_like;
            $oppolike_query = $this->db->query("SELECT count(*) as op_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$oppid'");
            $oppolike = $oppolike_query->row();
            $olike = $oppolike->op_like;
            $totalmatchlike = $rlike + $olike;
            if (($rlike == 0) && ($olike == 0)) {
              $win = $win + 0;
              $loss = $loss + 0;
              $winner_name[] = array(
                'winner_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );

              $loser_name[] = array(
                'loser_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );
            } else if ($rlike > $olike) {
              //rival win, then this match is lost for the personal match listing user
              $win = $win + 0;
              $loss = $loss + 1;
              if ($match_seenstatus == 1) //getting only revealed names
              {
                $winner_name[] = array(
                  'winner_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $totalmatchlike,
                  'created_at' => $created_at
                );
              }
            } else {
              //oppo win,then this match is won for the personal match listing user
              $win = $swin + 1;
              $loss = $sloss + 0;
              if ($match_seenstatus == 1) {
                $loser_name[] = array(
                  'loser_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $stotalmatchlike,
                  'created_at' => $created_at
                );
              }
            }
          }
          $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE image_id='$media_id' and matchid='$matchid' and like_status='like'")->row(); //likes of rival

          $like = $likeoutput->sender_like;

          if ($media_type == 'file') {
            $file_extention = $item['filename'];
            $exresult = explode(".", $file_extention);
            $extension = $exresult[1];
            if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
              //sfiletype is audio
              $filetype = "audio";
            } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
              //sfiletype is image
              $filetype = "image";
            } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
              //sfiletype is video
              $filetype = "video";
            }
          }else{
            $filetype = $media_type;
          }
          $temp = array(
            'media' => $media,
            'media_id' => $media_id,
            'media_type' => $filetype,
            'media_closedstatus' => $image_status,
            'reveal_count' => $r_count,
            'anonymous_count' => $a_count,
            'media_win' => $win,
            'media_loss' => $loss,
            'media_winname' => $winner_name,
            'media_lossname' => $loser_name,
            'likecount' => $like,
            'sub_caption'=>$sub_caption
          );
          $total_like = $total_like+ $like;
          array_push($medias, $temp);
        }
      }
      $user['medias'] = $medias;
      $user['total_like'] = $total_like;
      $compare_result = array($user);


      // $query = "SELECT * FROM tb_matchusers WHERE match_id = '$matchid' AND user_id = '$userid'";
      // $opponents = $this->db->query($query)->result_array();

      // foreach($opponents as $opponent){
      //   $oppouid = $opponent['opponent_id']; //opponent
      //   $total_like = 0;
      //   $medias = array();
      //   $matchoppofilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$oppouid' and matchid=$matchid"); //rival
      //   $output = $this->db->query("select * from tb_user WHERE id='$oppouid'")->row(); //rival
      //   if (!empty($output->profile_pic)) {
      //     $string = $output->profile_pic;
      //       $pic = $output->profile_pic;
      //     } else {
      //       $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
      //     }
      //   } else {
      //     $pic = base_url() . 'uploads/profile_image/user.png';
      //   }
      //   $total_like = 0;
      //   $user['user'] = $output;
      //   $user['pic'] = $pic;
      //   if ($matchoppofilequery->num_rows() > 0) {
      //     $up_files = $matchoppofilequery->result_array();
      //     foreach($up_files as $item){
      //       $media_id = $item['mup_id'];
      //       $media_type = $item['filetype'];
      //       $sub_caption = $item['sub_caption'];
      //       if ($media_type == 'file') {
      //         $media = base_url() . 'uploads/Matchuploads/' . $item['filename'];
      //       } else {
      //         $media = $item['filename'];
      //       }
      //       //to find if this image is used for any type of closed match
      //       $check_query = $this->db->query("SELECT * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
      //       if ($check_query->num_rows() > 0) {
      //         $image_status = 1;
      //       } else {
      //         $image_status = 0;
      //       }

      //       //to find the cound of revealed closed match of this particular image
      //       $count_revealquery = $this->db->query("SELECT count(*) as sr_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='1' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
      //       $countrevealresult = $count_revealquery->row();
      //       $r_count = $countrevealresult->sr_count;

      //       //to find the cound of anonymous closed match of this particular image
      //       $count_anonyquery = $this->db->query("SELECT count(*) as sa_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='0' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid' ORDER BY `tb_match`.`matchid` DESC");
      //       $scountanonyresult = $count_anonyquery->row();
      //       $a_count = $scountanonyresult->sa_count;

      //       //to find how many wins with this image for a closed match
      //       $wincount_query = $this->db->query(" SELECT *,tb_match.matchid as id from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' ");
      //       $resultwin = $wincount_query->result_array();
      //       $win = 0;
      //       $loss = 0;
      //       $winner_name = array();
      //       $loser_name = array();

      //       foreach ($resultwin as $s) {
      //         $mid = $s['id'];
      //         $created_at = $s['created_at'];
      //         $matchquery = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `matchid` ='$mid'");
      //         $match_result = $matchquery->result_array();
      //         $picarray = array();
      //         foreach ($match_result as $sm) {
      //           $first_imageftype = $sm['filetype'];
      //           if (!empty($sm['filename'])) {
      //             if ($first_imageftype == 'file') {
      //               $first_image =  base_url() . 'uploads/Matchuploads/' . $sm['filename'];
      //             } else {
      //               $first_image = $sm['filename'];
      //             }
      //             $picarray[] = array(
      //               'closed_image' => $first_image
      //             );
      //           } else {
      //             $picarray[] = array(
      //               'closed_image' => " "
      //             );
      //           }
      //         }
      //         $id = $s['rival_id'];
      //         $oppid = $s['opponent_id']; //user listing the personal match will always be opponent
      //         $match_seenstatus = $s['seen_status'];
      //         //to select the name of the winner and loser
      //         $rivaldetails_query = $this->db->query("SELECT * FROM `tb_user` where id='$id'");
      //         $rivaldetails = $rivaldetails_query->row();
      //         $rivalname = $rivaldetails->name;
      //         //
      //         $rivallike_query = $this->db->query("SELECT count(*) as ri_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$id'");
      //         $rivallike = $rivallike_query->row();
      //         $rlike = $rivallike->ri_like;
      //         $oppolike_query = $this->db->query("SELECT count(*) as op_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$oppid'");
      //         $oppolike = $oppolike_query->row();
      //         $olike = $oppolike->op_like;
      //         $totalmatchlike = $rlike + $olike;
      //         if (($rlike == 0) && ($olike == 0)) {
      //           $win = $win + 0;
      //           $loss = $loss + 0;
      //           $winner_name[] = array(
      //             'winner_name' => "",
      //             'closed_match_images' => " ",
      //             'closed_match_likes' => "",
      //             'created_at' => ""
      //           );

      //           $loser_name[] = array(
      //             'loser_name' => "",
      //             'closed_match_images' => " ",
      //             'closed_match_likes' => "",
      //             'created_at' => ""
      //           );
      //         } else if ($rlike > $olike) {
      //           //rival win, then this match is lost for the personal match listing user
      //           $win = $win + 0;
      //           $loss = $loss + 1;
      //           if ($match_seenstatus == 1) //getting only revealed names
      //           {
      //             $winner_name[] = array(
      //               'winner_name' => $rivalname,
      //               'closed_match_images' => $picarray,
      //               'closed_match_likes' => $olike . '/' . $totalmatchlike,
      //               'created_at' => $created_at
      //             );
      //           }
      //         } else {
      //           //oppo win,then this match is won for the personal match listing user
      //           $win = $swin + 1;
      //           $loss = $sloss + 0;
      //           if ($match_seenstatus == 1) {
      //             $loser_name[] = array(
      //               'loser_name' => $rivalname,
      //               'closed_match_images' => $picarray,
      //               'closed_match_likes' => $olike . '/' . $stotalmatchlike,
      //               'created_at' => $created_at
      //             );
      //           }
      //         }
      //       }
      //       $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE image_id='$media_id' and matchid='$matchid' and like_status='like'")->row(); //likes of rival

      //       $like = $likeoutput->sender_like;
      //       if ($media_type == 'file') {
      //         $file_extention = $item['filename'];
      //         $exresult = explode(".", $file_extention);
      //         $extension = $exresult[1];
      //         if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
      //           //sfiletype is audio
      //           $filetype = "audio";
      //         } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
      //           //sfiletype is image
      //           $filetype = "image";
      //         } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
      //           //sfiletype is video
      //           $filetype = "video";
      //         }
      //       }else{
      //         $filetype = $media_type;
      //       }
      //       $temp = array(
      //         'media' => $media,
      //         'media_id' => $media_id,
      //         'media_type' => $filetype,
      //         'media_closedstatus' => $image_status,
      //         'reveal_count' => $r_count,
      //         'anonymous_count' => $a_count,
      //         'media_win' => $win,
      //         'media_loss' => $loss,
      //         'media_winname' => $winner_name,
      //         'media_lossname' => $loser_name,
      //         'likecount' => $like,
      //         'sub_caption'=>$sub_caption
      //       );
      //       $total_like = $total_like+ $like;
      //       array_push($medias, $temp);
      //     }
      //     $user['total_like'] = $total_like;
      //     $user['medias'] = $medias;
      //     $compare_result = array($user);
      //   }
      // }

      
      // $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$id' and matchid='$matchid'")->row();
      // $sender_like = $likeoutput->sender_like;
      
      // $likeoppoutput = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppouid' and matchid='$matchid'")->row();
      // $receiver_like = $likeoppoutput->receiver_like;

      
      // $userlike = $this->db->query("select contestent_id from tb_like WHERE user_liked='$user_id' and matchid='$matchid'");
      // if ($userlike->num_rows() > 0) {
      //   // echo 'yes';
      //   $userlikes = $userlike->row();
      //   // print_r($userlikes);
      //   $contid = $userlikes->contestent_id;
      //   if ($contid == $uid) {
      //     // echo "rival match";
      //     $userrivallike = '1';
      //     $useroppolike = '0';
      //   }
      //   if ($contid == $oppouid) {
      //     // echo "oppo match";
      //     $userrivallike = '0';
      //     $useroppolike = '1';
      //   }
      //   if ($contid == 0) {
      //     // echo "open like ";
      //     $userrivallike = '0';
      //     $useroppolike = '0';
      //   }
      // } else {
      //   $userrivallike = '0';
      //   $useroppolike = '0';
      // }
      // $cmnt_count = $this->db->query("select count(*) as total_cmnt from tb_comment WHERE match_id='$matchid'");
      // $cmntcount = $cmnt_count->row();
      // $current_time = date('H:i:s');
      // $stime = $result['replied_at'];
      // $start_date = new DateTime($stime);
      // $since_start = $start_date->diff(new DateTime($current_time));
      // $send_duration_h = $since_start->h;
      // $send_duration_i = $since_start->i;
      // $send_duration_s = $since_start->s;
      // if ($send_duration_h == 0) { //hour is 0
      //   if ($send_duration_i == 0) { //minute is 0
      //     $sduration = $send_duration_s . ' seconds';
      //   } else {
      //     $sduration = $send_duration_i . ' minutes';
      //   }
      // } else {
      //   $sduration = $send_duration_h . ' hours';
      // }
      // $etime = $result['match_end'];
      // $end_date = new DateTime($etime);
      // $since_end = $end_date->diff(new DateTime($current_time));
      // $remaining_duration_h = $since_end->h;
      // $remaining_duration_i = $since_end->i;
      // $remaining_duration_s = $since_end->s;
      // if ($remaining_duration_h == 0) { //hour is 0
      //   if ($remaining_duration_i == 0) { //minute is 0
      //     $rduration = $remaining_duration_s . ' seconds';
      //   } else {
      //     $rduration = $remaining_duration_i . ' minutes';
      //   }
      // } else {
      //   $rduration = $remaining_duration_h . ' hours';
      // }
      // if (!empty($sfileid)) {
      //   $sfileid = $sfileid;
      // } else {
      //   $sfileid = 0;
      // }
      // if (!empty($ofileid)) {
      //   $ofileid = $ofileid;
      // } else {
      //   $ofileid = 0;
      // }
      $data[] =  array(
        'match_id'        => $result['matchid'],
        'description'       => $result['description'],
        'time_duration'        => $result['time_duration'],
        'caption'      => $result['caption'],
        'category'       => $result['category'],
        'match_start' => $result['replied_at'],
        'match_end' => $result['match_end'],
        'match_invitationsend' => $result['created_at'],
        'compare_data' => $compare_result,
        'match_status' => '1',
      );
    }
  }
  public function get_ongoingmatch()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $user_id = $this->input->post('userid');
    $visitorid = $this->input->post('visitorid'); //only used to get the liked portion if the logged user is a visitor of other profile.
    $query = "SELECT * FROM tb_match WHERE invitation_status = 'accept' and match_type = 'open' and match_end> CURRENT_DATE()";
    $result_array = $this->db->query($query)->result_array();
    $data = array();
    foreach ($result_array as $result) {
      $compare_data = array();
      $user_id = $result['rival_id']; //rival 
      $match_id = $result['matchid'];
      $user = $this->db->query("SELECT * FROM tb_user WHERE id = '$user_id'")->row_array();

      if (!empty($user['profile_pic'])) {
        $string = $user['profile_pic'];
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          $pic = $user['profile_pic'];
        } else {
          $pic = base_url() . 'uploads/profile_image/' . $user['profile_pic'];
        }
      } else {
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $user['pic'] = $pic;
      $user['like_count'] = $this->db->query("SELECT COUNT(*) count FROM tb_like WHERE matchid = '$match_id' AND contestent_id = '$user_id' AND like_status = 'like'")->row()->count;
      $user['dislike_count'] = $this->db->query("SELECT COUNT(*) count FROM tb_like WHERE matchid = '$match_id' AND contestent_id = '$user_id' AND like_status = 'dislike'")->row()->count;
      $mediafiles = $this->db->query("SELECT * FROM tb_matchupload WHERE user_uploaded='$user_id' AND matchid = '$match_id'")->result_array();
      foreach($mediafiles as $index=> $media){
        $filename = $media['filename'];
        $filetype = $media['filetype'];
        if ($filetype == 'file') {
            $file = base_url() . 'uploads/Matchuploads/' . $filename;
            $mediafiles[$index]['media_type'] = getFileType($media['filename']);
        } else {
            $file = $filename;
            $mediafiles[$index]['media_type'] = $media['filetype'];
        }
        $mediafiles[$index]['file_path'] = $file;
      }
      $user['mediafiles'] = $mediafiles;
      array_push($compare_data, $user);

      $opponents = $this->db->query("SELECT * FROM tb_matchusers WHERE match_id = '$match_id' AND accept_status = 'accept'")->result_array();
      foreach($opponents as $oppoent){
        $user = $this->db->query("SELECT * FROM tb_user WHERE id = '".$oppoent['opponent_id']."'")->row_array();

        if (!empty($user['profile_pic'])) {
          $string = $user['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            $pic = $user['profile_pic'];
          } else {
            $pic = base_url() . 'uploads/profile_image/' . $user['profile_pic'];
          }
        } else {
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $user['pic'] = $pic;
        $match_like_count = $this->db->query("SELECT COUNT(*) count FROM tb_like WHERE matchid = '$match_id' AND contestent_id = '".$oppoent['opponent_id']."' AND like_status = 'like'")->row()->count;
        $match_dislike_count = $this->db->query("SELECT COUNT(*) count FROM tb_like WHERE matchid = '$match_id' AND contestent_id = '".$oppoent['opponent_id']."' AND like_status = 'dislike'")->row()->count;
        $mediafiles = $this->db->query("SELECT * FROM tb_matchupload WHERE user_uploaded='".$oppoent['opponent_id']."' AND matchid = '$match_id'")->result_array();
        foreach($mediafiles as $index=> $media){
          $filename = $media['filename'];
          $filetype = $media['filetype'];
          if ($filetype == 'file') {
            $file = base_url() . 'uploads/Matchuploads/' . $filename;
            $mediafiles[$index]['media_type'] = getFileType($media['filename']);
          } else {
              $file = $filename;
              $mediafiles[$index]['media_type'] = $media['filetype'];
          }
          $mediafiles[$index]['file_path'] = $file;
        }
        $user['mediafiles'] = $mediafiles;
        array_push($compare_data, $user);
      }

      // if (!empty($visitorid)) {
      //   $visitoruserlike = $this->db->query("select contestent_id from tb_like WHERE user_liked='$visitorid' and matchid='$matchid'");
      //   if ($visitoruserlike->num_rows() > 0) {
      //     $visitoruserlikes = $visitoruserlike->row();
      //     $cont_id = $visitoruserlikes->contestent_id;
      //     if ($cont_id == $uid) {
      //       // echo "rival match";
      //       $visitor_userrivallike = '1';
      //       $visitor_useroppolike = '0';
      //     }
      //     if ($cont_id == $oppouid) {
      //       // echo "oppo match";
      //       $visitor_userrivallike = '0';
      //       $visitor_useroppolike = '1';
      //     }
      //     if ($cont_id == 0) {
      //       // echo "open like ";
      //       $visitor_userrivallike = '0';
      //       $visitor_useroppolike = '0';
      //     }
      //   } else {
      //     $visitor_userrivallike = '0';
      //     $visitor_useroppolike = '0';
      //   }
      // } else {
      //   $visitor_userrivallike = '0';
      //   $visitor_useroppolike = '0';
      // }
      // $userlike = $this->db->query("select contestent_id from tb_like WHERE user_liked='$user_id' and matchid='$matchid'");
      // // echo "select contestent_id from tb_like WHERE user_liked='$user_id' and matchid='$matchid'";
      // if ($userlike->num_rows() > 0) {
      //   // echo 'yes';
      //   $userlikes = $userlike->row();
      //   // print_r($userlikes);
      //   $contid = $userlikes->contestent_id;
      //   if ($contid == $uid) {
      //     // echo "rival match";
      //     $userrivallike = '1';
      //     $useroppolike = '0';
      //   }
      //   if ($contid == $oppouid) {
      //     // echo "oppo match";
      //     $userrivallike = '0';
      //     $useroppolike = '1';
      //   }
      //   if ($contid == 0) {
      //     // echo "open like ";
      //     $userrivallike = '0';
      //     $useroppolike = '0';
      //   }
      // } else {
      //   $userrivallike = '0';
      //   $useroppolike = '0';
      // }
      // echo $userrivallike;
      // die();
      $cmntcount = $this->db->query("select count(*) as total_cmnt from tb_comment WHERE match_id='$match_id'")->row()->total_cmnt;
      //
      $current_time = date('H:i:s');
      /* Send duration start */
      $stime = $result['replied_at'];
      $start_date = new DateTime($stime);
      $since_start = $start_date->diff(new DateTime($current_time));
      $send_duration_h = $since_start->h;
      $send_duration_i = $since_start->i;
      $send_duration_s = $since_start->s;
      if ($send_duration_h == 0) { //hour is 0
        if ($send_duration_i == 0) { //minute is 0
          $sduration = $send_duration_s . ' seconds';
        } else {
          $sduration = $send_duration_i . ' minutes';
        }
      } else {
        $sduration = $send_duration_h . ' hours';
      }
      // echo $sduration;
      /* Send duration end */
      /* remaning duration start */
      $etime = $result['match_end'];
      $end_date = new DateTime($etime);
      $since_end = $end_date->diff(new DateTime($current_time));
      $remaining_duration_h = $since_end->h;
      $remaining_duration_i = $since_end->i;
      $remaining_duration_s = $since_end->s;
      if ($remaining_duration_h == 0) { //hour is 0
        if ($remaining_duration_i == 0) { //minute is 0
          $rduration = $remaining_duration_s . ' seconds';
        } else {
          $rduration = $remaining_duration_i . ' minutes';
        }
      } else {
        $rduration = $remaining_duration_h . ' hours';
      }
      // echo $rduration;
      /* remaning duration end */
      //
      // die();
      $result['comment_count'] = $cmntcount;
      $result['compare_data'] = $compare_data;
      $result['send_duration'] = $sduration;
      $result['remaining_time'] = $rduration;
      array_push($data, $result);
    }
    return $data;
  }
  public function like()
  {
    $uid = $this->input->post('userid');
    $mid = $this->input->post('matchid');
    $cid = $this->input->post('contestentid');
    $status = $this->input->post('status');
    $result = $this->db->query("select * from tb_match WHERE matchid='$mid'");
    if ($this->db->affected_rows() > 0) {
      $result = $this->db->query("select * from tb_like WHERE user_liked='$uid' and matchid='$mid'");
      if ($this->db->affected_rows() > 0) {
        $check = $this->db->query("update tb_like set contestent_id='$cid',like_status='$status' WHERE user_liked='$uid' and matchid='$mid'");
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      } else {
        $data = array(
          'user_liked' => $this->input->post('userid'),
          'matchid' => $this->input->post('matchid'),
          'contestent_id' => $this->input->post('contestentid'),
          'like_status' => $this->input->post('status')
        );
        $this->db->insert('tb_like', $data);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function addcomment()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $uid = $this->input->post('userid');
    $mid = $this->input->post('matchid');
    $cid = $this->input->post('comment');
    $contestentid = $this->input->post('contestentid');
    $result = $this->db->query("select * from tb_match WHERE matchid='$mid'");
    if ($this->db->affected_rows() > 0) {
      // $result = $this->db->query("select * from tb_like WHERE user_liked='$uid' and matchid='$mid'");
      // if ($this->db->affected_rows() > 0) {
      //  $check = $this->db->query("update tb_like set contestent_id='$cid',like_status='$status' WHERE user_liked='$uid' and matchid='$mid'"); 
      //  if ($this->db->affected_rows() > 0) {
      //   return "success";
      // } else {
      //   return "fail";
      // }
      // }
      // else{
      $datas = array(
        'userid' => $this->input->post('userid'),
        'match_id' => $this->input->post('matchid'),
        'comment' => $this->input->post('comment'),
        'contestent_id' => $this->input->post('contestentid'),
        'commented_at' => $current_date
      );
      $this->db->insert('tb_comment', $datas);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
      // } 
    } else {
      return "notexist";
    }
  }
  public function getComment()
  {
    $userid = $this->input->post('userid');
    $matchid = $this->input->post('matchid');
    $quwe = $this->db->query("SELECT count(*) as total_count FROM `tb_comment` WHERE contestent_id='$userid' and match_id='$matchid' ");
    $quweoutput = $quwe->row();
    $result_query = $this->db->query("SELECT * FROM `tb_comment` WHERE contestent_id='$userid' and match_id='$matchid' and replied_commentid='0'");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $uid = $result['userid'];
      $cid = $result['comment_id'];
      $cl_count = $this->db->query("SELECT count(*) as cl_count FROM `tb_commentlike` WHERE commentid='$cid'");
      $cloutput = $cl_count->row();

      $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$uid'");
      $output = $resultuser->row();
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $output->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $replyquery = $this->db->query("SELECT count(*) as totalreplycount FROM `tb_comment` WHERE contestent_id='$userid' and match_id='$matchid' and replied_commentid='$cid'");
      $replycount = $replyquery->row();
      $data[] =  array(
        'id'        => $result['comment_id'],
        'contestent_id'      => $result['contestent_id'],
        'commented_userid'      => $result['userid'],
        'commented_username'      => $output->name,
        'commented_userprofile'      => $pic,
        'comment'       => $result['comment'],
        'commented_at'       => $result['commented_at'],
        'like' => '0',
        'reply' => $replycount->totalreplycount,
        'total_comment_count'       => $quweoutput->total_count,
        'total_like_count' => $cloutput->cl_count,
        // 'total_reply_like_count' => '0',
        'replied_comment' => $result['replied_commentid']
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function getreplyComment()
  {
    $userid = $this->input->post('userid');
    $matchid = $this->input->post('matchid');
    $result_query = $this->db->query("SELECT * FROM `tb_comment` WHERE contestent_id='$userid' and match_id='$matchid' and replied_commentid!='0'");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $uid = $result['userid'];
      $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$uid'");
      $output = $resultuser->row();
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $output->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $cid = $result['comment_id'];
      $crl_count = $this->db->query("SELECT count(*) as crl_count FROM `tb_commentlike` WHERE commentid='$cid'");
      $crloutput = $crl_count->row();
      $data[] =  array(
        'id'        => $result['comment_id'],
        'contestent_id'      => $result['contestent_id'],
        'commented_userid'      => $result['userid'],
        'commented_username'      => $output->name,
        'commented_userprofile'      => $pic,
        'comment'       => $result['comment'],
        'commented_at'       => $result['commented_at'],
        'like' => '',
        'reply' => '',
        // 'total_comment_count'       => '0',
        // 'total_like_count' => '0',
        'total_reply_like_count' => $crloutput->crl_count,
        'replied_comment' => $result['replied_commentid']
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function add_fan()
  {
    $from = $this->input->post('from');
    $to = $this->input->post('to');
    $category = $this->input->post('category');
    $data = array(
      'req_from' => $this->input->post('from'),
      'req_to' => $this->input->post('to'),
      'category' => $this->input->post('category'),
    );
    $query =  $this->db->query("select * from tb_user where id='$from'");
    $queryto =  $this->db->query("select * from tb_user where id='$to'");
    if ($this->db->affected_rows() > 0) {
      // echo "select * from tb_bestie where (req_from='$from' and req_to='$to') or (req_from='$to' and req_to='$from') and category='$category'";die();
      $result_query =  $this->db->query("select * from tb_fans where ((req_from='$from' and req_to='$to') or (req_from='$to' and req_to='$from')) and category='$category'");
      if ($this->db->affected_rows() > 0) {
        return "exist";
      } else {
        $this->db->insert('tb_fans', $data);
        if ($this->db->affected_rows() > 0) {
          $fan_id = $this->db->insert_id();
          $result_array =  $query->row(); //sender
          $resultarray =  $queryto->row(); //receiver
          $senderdevtoken = $resultarray->device_token;
          if ($category == 'fan') {
            $s = $result_array->name . ' ' . 'started following you';
            $notification_status = 'Add_' . $category;
          } else {
            $s = $result_array->name . ' ' . 'added you as ' . $category;
            $notification_status = 'Add_' . $category;
          }
          $sendermessage = $s;
          $senderdevicetype = $resultarray->device_type;
          $data = array(
            'sender_id' => $this->input->post('from'),
            'receiver_id' => $this->input->post('to'),
            'message' => $s,
            'notification_status' => $notification_status,
            'request_id' => $fan_id
          );
          $this->db->insert('tb_notification', $data);
          $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
          $receiverdevtoken = $result_array->device_token;
          if ($category == 'fan') {
            $r = 'You have started following ' . $resultarray->name;
            $notification_status = 'Add_to_' . $category;
          } else {
            $r = 'You have added ' . $resultarray->name . ' to your ' . $category . ' list';
            $notification_status = 'Add_to_' . $category;
          }
          $receivermessage = $r;
          $receiverdevicetype = $result_array->device_type;
          $data = array(
            'sender_id' => $this->input->post('to'),
            'receiver_id' => $this->input->post('from'),
            'message' => $r,
            'notification_status' => $notification_status,
            'request_id' => $fan_id
          );
          $this->db->insert('tb_notification', $data);
          $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
          return "success";
        } else {
          return "fail";
        }
      }
    }
  }
  public function list_fan($id)
  {
    // echo $id;
    $query = $this->db->query("SELECT * FROM `tb_fans` WHERE (req_from='$id' or req_to='$id') and category='fan'");
    // echo "SELECT * FROM `tb_bestie` WHERE req_from='$id' or req_to='$id' ";
    $result_array =  $query->result_array();
    // print_r( $result_array);die();
    $data = array();
    foreach ($result_array as $results) {
      // print_r($results['req_from']);die();
      if ($results['req_from'] == $id) {
        $userid = $results['req_to'];
      } else {
        $userid = $results['req_from'];
      }
      // print_r($userid);die();
      $result_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
      $output =  $result_query->result_array();
      foreach ($output as $result) {
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $data[] =  array(
          'tableid'        => $results['fan_id'],
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'email'       => $result['email'],
          'university_name' => $result['university_name'],
          'profile_pic'      => $pic,
          'category' => $results['category'],
          'likes' => ' ',
          'comments' => ' ',
        );
      }
    }

    return $data;
  }
  public function list_fanof($id)
  {
    // echo $id;
    $query = $this->db->query("SELECT * FROM `tb_fans` WHERE (req_from='$id' or req_to='$id') and category='fan_of'");
    // echo "SELECT * FROM `tb_bestie` WHERE req_from='$id' or req_to='$id' ";
    $result_array =  $query->result_array();
    // print_r( $result_array);die();
    $data = array();
    foreach ($result_array as $results) {
      // print_r($results['req_from']);die();
      if ($results['req_from'] == $id) {
        $userid = $results['req_to'];
      } else {
        $userid = $results['req_from'];
      }
      // print_r($userid);die();
      $result_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
      $output =  $result_query->result_array();
      foreach ($output as $result) {
        if (!empty($result['profile_pic'])) {
          $string = $result['profile_pic'];
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $result['profile_pic'];
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $result['profile_pic'];
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $data[] =  array(
          'tableid'        => $results['fan_id'],
          'userid'        => $result['id'],
          'name'      => $result['name'],
          'email'       => $result['email'],
          'university_name' => $result['university_name'],
          'profile_pic'      => $pic,
          'category' => $results['category'],
          'likes' => ' ',
          'comments' => ' ',
        );
      }
    }

    return $data;
  }
  public function RemoveFans()
  {
    $fanid = $this->input->post('fanid');
    $check = $this->db->query("delete from tb_fans WHERE fan_id='$fanid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function RemoveBesties()
  {
    $bid = $this->input->post('besiteid');
    $check = $this->db->query("delete from tb_bestie WHERE bestie_id='$bid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function get_categorycount()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $userid = $this->input->post('userid');
    $query =  $this->db->query("select * from tb_user where id='$userid'");
    $result_query = $query->row();
    $squad = $this->db->query("select count(*) as squad_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='squad' and req_status='1'");
    $result_squad = $squad->row();
    $bestie = $this->db->query("select count(*) as bestie_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='bestie' and req_status='1'");
    $result_bestie = $bestie->row();
    $fan = $this->db->query("select count(*) as fan_count from tb_fans where (req_from='$userid')");
    $result_fan = $fan->row();
    $fanof = $this->db->query("select count(*) as fanof_count from tb_fans where (req_to='$userid')");
    $result_fanof = $fanof->row();
    $matchquery =  $this->db->query("select count(*) as endmatch_count from tb_match where (rival_id='$userid' or opponent_id='$userid') and match_end<='$current_date'");
    $result_matchquery = $matchquery->row();
    if (!empty($result_query->profile_pic)) {
      $string = $result_query->profile_pic;
      if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
        // one or more of the 'special characters' found in $string
        // echo "found";
        $pic = $result_query->profile_pic;
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/' . $result_query->profile_pic;
      }
    } else {
      // echo "not found";
      $pic = base_url() . 'uploads/profile_image/user.png';
    }
    $data[] =  array(
      'userid' => $result_query->id,
      'username' => $result_query->name,
      'user_profilepic' => $pic,
      'squad_count' => $result_squad->squad_count,
      'bestie_count' => $result_bestie->bestie_count,
      'fans_count' => $result_fan->fan_count,
      'fans_of_count' => $result_fanof->fanof_count,
      'match_count' => $result_matchquery->endmatch_count,
    );
    return $data;
  }
  public function getHeadline()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $userid = $this->input->post('userid');
    $dateStr = date('Y-m-d H:i:s', strtotime('last saturday')); //last sat date
    // $startdate = date($dateStr, strtotime('-7 days'));
    $startdate = date('Y-m-d H:i:s', strtotime('-7 days', strtotime($dateStr)));
    // echo $dateStr;
    // echo $startdate;die();
    $data = array();
    /*start of first headline */

    // echo "select * from tb_match WHERE (rival_id='$userid' or opponent_id='$userid') and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'";die();
    $resultopen = $this->db->query("select * from tb_match WHERE (rival_id='$userid' or opponent_id='$userid') and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'");
    if ($this->db->affected_rows() > 0) {
      $result_array = $resultopen->result_array();
      // print_r($result_array);die();
      foreach ($result_array as $result) {
        $matchid = $result['matchid'];
        $rivalid = $result['rival_id']; //rival
        $oppoid = $result['opponent_id']; //opponent
        $likequery = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$rivalid' and matchid='$matchid'");
        $likeoutput = $likequery->row();
        $sender_like = $likeoutput->sender_like;
        $likeoppquery = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppoid' and matchid='$matchid'");
        $likeoppoutput = $likeoppquery->row();
        $receiver_like = $likeoppoutput->receiver_like;
        if ($sender_like > $receiver_like) {
          // echo "sender won";
          $winnerid = $rivalid;
          $likes = $sender_like;
        } else {
          // echo "receiver won";
          $winnerid = $oppoid;
          $likes = $receiver_like;
        }
        if ($likes != 0) {
          $datawinner[] = array(
            'matchid' => $matchid,
            'winner' => $winnerid,
            'like' => $likes,
          );
        }
      }
      // print_r($datawinner);die();
      uasort($datawinner, function ($item, $compare) {
        return $item['like'] <= $compare['like'];
      });
      foreach ($datawinner as $result) {
        $winnerid = $result['winner'];
        $querywinner = $this->db->query("select * from tb_user WHERE id='$winnerid'");
        $outputwinner = $querywinner->row();

        if (!empty($outputwinner->profile_pic)) {
          $string = $outputwinner->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $outputwinner->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $outputwinner->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $userdata[] =  array(
          // 'matchid' => $result['matchid'],
          'userid'        => $result['winner'],
          'name'      => $outputwinner->name,
          'email'       => $outputwinner->email,
          'university_name' => $outputwinner->university_name,
          'profile_pic'      => $pic,
          'category'       => " ",
          'likes' => $result['like'],
          'comments' => ' ',
        );
      }
      $finaldata = $userdata;
      $data['first_headline'] = array_slice($finaldata, 0, 1);
    } else {
      $data['first_headline'] =  array(
        // 'matchid' =>'',
        'userid'        => '',
        'name'      => '',
        'email'       => '',
        'university_name' => '',
        'profile_pic'      => '',
        'category'       => " ",
        'likes' => '',
        'comments' => ' ',
      );
    }
    /*end of first headline */
    /*Start of Second headline */
    $resultpersonal = $this->db->query("select * from tb_match WHERE rival_id='$userid' and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'");
    // echo"select * from tb_match WHERE rival_id='$userid' and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'";die();
    if ($this->db->affected_rows() > 0) {
      $result_arraypersonal = $resultpersonal->result_array();
      // print_r($result_arraypersonal );die();
      $i = 0;
      foreach ($result_arraypersonal as $result) {
        $matchid = $result['matchid'];
        $rivalid = $result['rival_id']; //rival
        $oppoid = $result['opponent_id']; //opponent
        $likequery = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$rivalid' and matchid='$matchid'");
        $likeoutput = $likequery->row();
        $sender_like = $likeoutput->sender_like;
        $likeoppquery = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppoid' and matchid='$matchid'");
        $likeoppoutput = $likeoppquery->row();
        $receiver_like = $likeoppoutput->receiver_like;
        $receiver_like = $likeoppoutput->receiver_like;
        if ($sender_like > $receiver_like) {
          // echo "sender won";
          $winnerid = $rivalid;
          $likes = $sender_like;
        } else {
          // echo "receiver won";
          $winnerid = $oppoid;
          $likes = $receiver_like;
        }
        if ($likes != 0) {
          $personalwinner[] = array(
            'index' => $i,
            'matchid' => $matchid,
            'winner' => $winnerid,
            'like' => $likes,
          );
          $i++;
        }
      }
      // print_r($personalwinner);die();
      uasort($personalwinner, function ($item, $compare) {
        return $item['like'] <= $compare['like'];
      });
      foreach ($personalwinner as $result) {
        $winner_id = $result['winner'];
        $querysecondwinner = $this->db->query("select * from tb_user WHERE id='$winner_id'");
        $outputsecondwinner =  $querysecondwinner->row();

        if (!empty($outputsecondwinner->profile_pic)) {
          $string = $outputsecondwinner->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $outputsecondwinner->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $outputsecondwinner->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $userseconddata[] =  array(
          'matchid' => $result['matchid'],
          'userid'        => $result['winner'],
          'name'      => $outputsecondwinner->name,
          'email'       => $outputsecondwinner->email,
          'university_name' => $outputsecondwinner->university_name,
          'profile_pic'      => $pic,
          'category'       => " ",
          'likes' => $result['like'],
          'comments' => ' ',
        );
      }
      $finalseconddata = $userseconddata;
      $data['second_headline'] = array_slice($finalseconddata, 0, 1);
    } else {
      $data['second_headline'] =  array(
        'matchid' => '',
        'userid'        => '',
        'name'      => '',
        'email'       => '',
        'university_name' => '',
        'profile_pic'      => '',
        'category'       => " ",
        'likes' => '',
        'comments' => ' ',
      );
    }
    // print_r($data['second_headline']);die();
    /*End of Second headline */
    /*Start of Third headline */
    // echo "select * from tb_match WHERE rival_id='$userid' and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'";die();
    $resultthirdpersonal = $this->db->query("select * from tb_match WHERE rival_id='$userid' and invitation_status='accept' and `match_end` BETWEEN '$startdate' AND '$current_date'");
    if ($this->db->affected_rows() > 0) {
      $result_arraythird = $resultthirdpersonal->result_array();
      foreach ($result_arraythird as $result) {
        $matchid = $result['matchid'];
        $rivalid = $result['rival_id']; //rival
        $oppoid = $result['opponent_id']; //opponent
        $commentquery = $this->db->query("select count(*) as sender_count from tb_comment WHERE contestent_id='$rivalid' and match_id='$matchid'");
        $commentoutput = $commentquery->row();
        $sender_comment = $commentoutput->sender_count;
        $commentoppquery = $this->db->query("select count(*) as receiver_count from tb_comment WHERE contestent_id='$oppoid' and match_id='$matchid'");
        $commentoppoutput = $commentoppquery->row();
        $receiver_comment = $commentoppoutput->receiver_count;
        if ($sender_comment > $receiver_comment) {
          // echo "sender won";
          $winnerid = $rivalid;
          $comment = $sender_comment;
        } else {
          // echo "receiver won";
          $winnerid = $oppoid;
          $comment = $receiver_comment;
        }
        if ($comment != 0) {
          $thirdwinner[] = array(
            'matchid' => $matchid,
            'winner' => $winnerid,
            'comment' => $comment,
          );
        }
      }
      // print_r($thirdwinner);die();
      uasort($thirdwinner, function ($item, $compare) {
        return $item['comment'] <= $compare['comment'];
      });
      foreach ($thirdwinner as $result) {
        $w_id = $result['winner'];
        $querythirdwinner = $this->db->query("select * from tb_user WHERE id='$w_id'");
        $outputthirdwinner =  $querythirdwinner->row();

        if (!empty($outputthirdwinner->profile_pic)) {
          $string = $outputthirdwinner->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $outputthirdwinner->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $outputthirdwinner->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $userthirddata[] =  array(
          'matchid' => $result['matchid'],
          'userid'        => $result['winner'],
          'name'      => $outputthirdwinner->name,
          'email'       => $outputthirdwinner->email,
          'university_name' => $outputthirdwinner->university_name,
          'profile_pic'      => $pic,
          'category'       => " ",
          'likes' => ' ',
          'comments' => $result['comment'],
        );
      }
      $finalthirddata = $userthirddata;
      $data['Third_headline'] = array_slice($finalthirddata, 0, 1);
      // print_r($data['Third_headline']);die();
    } else {
      $data['Third_headline'] =  array(
        'matchid' => '',
        'userid'        => '',
        'name'      => '',
        'email'       => '',
        'university_name' => '',
        'profile_pic'      => '',
        'category'       => " ",
        'likes' => '',
        'comments' => '',
      );
    }
    /*End of Third headline */
    /*Start of Fourth headline */
    $iquery = $this->db->query("select * from tb_MyiQuery");
    if ($this->db->affected_rows() > 0) {
      $iqueryresult = $iquery->result_array();
      foreach ($iqueryresult as $result) {
        $id = $result['queryid'];
        $like1iquery = $this->db->query("select count(*) as total1like from tb_myiquerylike where like_status='like' and iquerylike_id='$id' AND `querynum` LIKE 'query1'");
        $iquery1likeresult = $like1iquery->row();
        $like2iquery = $this->db->query("select count(*) as total2like from tb_myiquerylike where like_status='like' and iquerylike_id='$id' AND `querynum` LIKE 'query2'");
        $iquery2ikeresult = $like2iquery->row();
        $total_like = $iquery1likeresult->total1like + $iquery2ikeresult->total2like;
        $ilike[] =  array(
          'userid' => $id,
          'total_like'        => $total_like
        );
      }
      $ilikecount = 0;
      foreach ($ilike as $test) {
        // echo $test['matchid'];die();
        if ($ilikecount == 0) {
          $ilikecount = $test['total_like'];
          $uid = $test['userid'];
        } else {
          if ($ilikecount > $test['total_like']) {
            $ilikecount = $ilikecount;
            $uid = $uid;
          } else {
            $ilikecount = $test['total_like'];
            $uid = $test['userid'];
          }
        }
      }
    }
    //  echo $ilikecount.'=like count';echo $userid.'=userid';
    $queryfourthwinner = $this->db->query("select * from tb_user WHERE id='$uid'");
    $outputfourthwinner = $queryfourthwinner->row();

    if (!empty($outputfourthwinner->profile_pic)) {
      $string = $outputfourthwinner->profile_pic;
      if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
        // one or more of the 'special characters' found in $string
        // echo "found";
        $pic = $outputfourthwinner->profile_pic;
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/' . $outputfourthwinner->profile_pic;
      }
    } else {
      // echo "not found";
      $pic = base_url() . 'uploads/profile_image/user.png';
    }
    $data['Fourth_headline'] =  array(
      'userid'        => $uid,
      'name'      => $outputfourthwinner->name,
      'email'       => $outputfourthwinner->email,
      'university_name' => $outputfourthwinner->university_name,
      'profile_pic'      => $pic,
      'category'       => " ",
      'likes' => $ilikecount,
      'comments' => '',
    );
    // print_r($data['Fourth_headline'] );
    /*End of Fourth headline */
    /*Start of Fifth headline */
    $squadquery = $this->db->query("SELECT * FROM `tb_bestie` WHERE (req_from='$userid' or req_to='$userid') and category='squad'");
    if ($this->db->affected_rows() > 0) {
      $result_squadquery =  $squadquery->result_array();
      //  print_r( $result_array);die();
      foreach ($result_squadquery as $results) {
        // print_r($results['req_from']);die();
        if ($results['req_from'] == $userid) {
          $u_id = $results['req_to'];
        } else {
          $u_id = $results['req_from'];
        }
        // echo $u_id.',';
        $querymostliked = $this->db->query("select count(*) as squadlike from tb_like WHERE user_liked='$u_id' and like_status='like'");
        $result_mostliked = $querymostliked->row();
        $squad[] = array(
          'userid' => $u_id,
          'likedcount' => $result_mostliked->squadlike
        );
      }
      uasort($squad, function ($item, $compare) {
        return $item['likedcount'] <= $compare['likedcount'];
      });
      //  $winnersquad=$squad[0] ;
      $squaduserid = $squad[0]['userid'];
      $squadcount = $squad[0]['likedcount'];
      //  print_r($winnersquad);
      $queryfifthwinner = $this->db->query("select * from tb_user WHERE id='$squaduserid'");
      $outputfifthhwinner = $queryfifthwinner->row();

      if (!empty($outputfifthhwinner->profile_pic)) {
        $string = $outputfifthhwinner->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $outputfifthhwinner->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $outputfifthhwinner->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $data['Fifth_headline'] =  array(
        'userid'        => $squaduserid,
        'name'      => $outputfifthhwinner->name,
        'email'       => $outputfifthhwinner->email,
        'university_name' => $outputfifthhwinner->university_name,
        'profile_pic'      => $pic,
        'category'       => " ",
        'likes' => $squadcount,
        'comments' => '',
      );
    }
    /*End of Fifth headline */
    /*Start of Sixth headline */
    $data['Sixth_headline'] =  array(
      'userid'        => '',
      'name'      => '',
      'email'       => '',
      'university_name' => '',
      'profile_pic'      => '',
      'category'       => " ",
      'likes' => '',
      'comments' => '',
    );
    return $data;
  } //Headline function end
  public function get_map()
  {
    $lat = $this->input->post('latitude');
    $long = $this->input->post('longitude');
    $geocode = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=false&key=AIzaSyCeCYMDg0kIGcWsKLygXr3NlLXMpksu45w";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geocode);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($response);
    print_r($output);
    $dataarray = get_object_vars($output);
    if ($dataarray['status'] != 'ZERO_RESULTS' && $dataarray['status'] != 'INVALID_REQUEST') {
      if (isset($dataarray['results'][0]->formatted_address)) {

        $address = $dataarray['results'][0]->formatted_address;
      } else {
        $address = 'Not Found';
      }
    } else {
      $address = 'Not Found';
    }

    return $address;
    die();
  }
  
  public function getAdminMessage()
  {
    $id = $this->input->post('userid');
    $result_query = $this->db->query("SELECT * FROM `tb_adminmessage`");
    $output = $result_query->row();
    // print_r($output);die();
    $questionid = $output->id;
    $data = array();
    $data['adminmessage'] =  array(
      'id' => $output->id,
      'message1'        => $output->message1,
      'message2'      => $output->message2
    );
    $result = $this->db->query("SELECT * FROM `tb_useranswer` where userid='$id'");
    $output_result = $result->row();
    if ($result->num_rows() > 0) {
      $data['answers'] =  array(
        'answer_id' => $output_result->ansid,
        'answer1'        => $output_result->answer1,
        'answer2'      => $output_result->answer2
      );
    } else {
      $data['answers'] =  array();
    }
    $resultiquery = $this->db->query("SELECT * FROM `tb_MyiQuery` WHERE `user_id` = '$id'");
    if ($resultiquery->num_rows() > 0) {
      $output_query = $resultiquery->result_array();
      // print_r($output);die();
      foreach ($output_query as $result) {
        $queryid = $result['queryid'];
        $q1like = $this->db->query("SELECT count(*) as q1like  FROM `tb_myiquerylike` WHERE `myqueryid` = $queryid AND `querynum` LIKE 'query1' AND `like_status` LIKE 'like'");
        $output_q1like = $q1like->row();
        $q2like = $this->db->query("SELECT count(*) as q2like  FROM `tb_myiquerylike` WHERE `myqueryid` = $queryid AND `querynum` LIKE 'query2' AND `like_status` LIKE 'like'");
        $output_q2like = $q2like->row();
        $c1like = $this->db->query("SELECT count(*) as c1like FROM `tb_myiquerycomment` WHERE `queryid` = $queryid AND `querynum` LIKE 'query1'");
        $output_c1like = $c1like->row();
        $c2like = $this->db->query("SELECT count(*) as c2like FROM `tb_myiquerycomment` WHERE `queryid` = $queryid AND `querynum` LIKE 'query2'");
        $output_c2like = $c2like->row();
        $query[] =  array(
          'queryid'        => $result['queryid'],
          'query1'      => $result['query1'],
          'query2'      => $result['query2'],
          'total_likecount_query1' => $output_q1like->q1like,
          'total_likecount_query2' => $output_q2like->q2like,
          'total_commentcount_query1' => $output_c1like->c1like,
          'total_commentcount_query2' => $output_c2like->c2like,
        );
      }
    } else {
      $query = [];
    }
    // print_r($query);die();

    $data['MyiQuery'] = $query;
    $resultlike = $this->db->query("SELECT * FROM `tb_answerlike` where user_liked='$id' and `IsActive`='0'");
    if ($resultlike->num_rows() > 0) {
      $output_resultlike = $resultlike->result_array();
      // print_r($output_resultlike);die();
      foreach ($output_resultlike as $result) {
        $like[] =  array(
          'id'        => $result['id'],
          'answer'      => $result['answerid'],
          'status'      => $result['like_status']
        );
      }
    } else {
      $like = [];
    }
    // print_r($like);die();
    $data['Like'] = $like;
    $resultcomment = $this->db->query("SELECT count(*) as total_comment  FROM `tb_useranswer` WHERE `questionid` LIKE '$questionid'");
    $output_resultcomment = $resultcomment->row();
    $resultans1like = $this->db->query("SELECT count(*) as ans1_like  FROM `tb_answerlike` WHERE `questionid` = $questionid AND `answerid` LIKE 'answer1'");
    $output_resultans1like = $resultans1like->row();
    $resultans2like = $this->db->query("SELECT count(*) as ans2_like  FROM `tb_answerlike` WHERE `questionid` = $questionid AND `answerid` LIKE 'answer2'");
    $output_resultans2like = $resultans2like->row();
    $data['Count'] = array(
      'total_comment'      => $output_resultcomment->total_comment,
      'total_ans1like'      => $output_resultans1like->ans1_like,
      'total_ans2like'      => $output_resultans2like->ans2_like,
    );
    // print_r($data);die();

    return $data;
  }
  public function getMessageAnswer() //to insert answers for the admin messages
  {
    $userid = $this->input->post('userid');
    $answer1 = $this->input->post('answer1');
    $answer2 = $this->input->post('answer2');
    $questionnum = $this->input->post('questionnum');
    $query =  $this->db->query("select * from tb_useranswer where questionid='$questionnum' and userid='$userid'");
    if ($this->db->affected_rows() > 0) {
      // $data =  array(
      //   'userid' => $userid,
      //   'questionid' => $questionnum,
      //   'answer1'        => $answer1,
      //   'answer2'      => $answer2,
      // );
      // $this->db->where('questionid', $questionnum);
      // $this->db->update('tb_useranswer', $data);
      // if ($this->db->affected_rows() > 0) {
      //   return "success";
      // } else {
      return "fail";
      // }
    } else {
      $data =  array(
        'userid' => $userid,
        'questionid' => $questionnum,
        'answer1'        => $answer1,
        'answer2'      => $answer2,
      );
      $this->db->insert('tb_useranswer', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    }
  }
  public function commentlike()
  {
    $uid = $this->input->post('userid');
    $questionid = $this->input->post('questionid');
    $aid = $this->input->post('answerid');
    $status = $this->input->post('status');
    // echo "SELECT * FROM `tb_useranswer` WHERE `$aid` IS NOT NULL and userid='$uid'";die();
    $result = $this->db->query("select * from tb_adminmessage WHERE id='$questionid'");
    if ($this->db->affected_rows() > 0) {
      $result = $this->db->query("SELECT * FROM `tb_useranswer` WHERE `$aid` IS NOT NULL and userid='$uid'");
      if ($this->db->affected_rows() > 0) {
        //answer is existing

        $result = $this->db->query("select * from tb_answerlike WHERE user_liked='$uid' and questionid='$questionid' and answerid='$aid'");
        if ($this->db->affected_rows() > 0) {
          // echo "yes exist";die();
          $result = $this->db->query("select * from tb_answerlike WHERE user_liked='$uid' and questionid='$questionid' and answerid='$aid' and like_status='dislike'");
          if ($this->db->affected_rows() > 0) {
            // echo "yes dislike";die();
            $check = $this->db->query("update tb_answerlike set like_status='like' WHERE user_liked='$uid' and questionid='$questionid' and answerid='$aid'");
            if ($this->db->affected_rows() > 0) {
              return "success";
            } else {
              return "fail";
            }
          }
          $result = $this->db->query("select * from tb_answerlike WHERE user_liked='$uid' and questionid='$questionid' and answerid='$aid' and like_status='like'");
          if ($this->db->affected_rows() > 0) {
            // echo "yes like";die();
            $check = $this->db->query("update tb_answerlike set like_status='dislike' WHERE user_liked='$uid' and questionid='$questionid' and answerid='$aid'");
            if ($this->db->affected_rows() > 0) {
              return "successful";
            } else {
              return "fail";
            }
          }
        } else {
          $data = array(
            'user_liked' => $this->input->post('userid'),
            'questionid' => $this->input->post('questionid'),
            'answerid' => $this->input->post('answerid'),
            'like_status' => $this->input->post('status')
          );
          $this->db->insert('tb_answerlike', $data);
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        }
      } else {
        //answer not existing
        return "answernotexist";
      }
    } else {
      return "notexist";
    }
  }
  public function MyiQuery()
  {
    $queryid = $this->input->post('queryid');
    if (!empty($queryid)) {
      $data = array(
        // 'user_id' => $this->input->post('userid'),
        'query1' => $this->input->post('query1'),
        'query2' => $this->input->post('query2')
      );
      $this->db->where('queryid', $queryid);
      $this->db->update('tb_MyiQuery', $data);
      if ($this->db->affected_rows() > 0) {
        return "successupdate";
      } else {
        return "fail";
      }
    } else {
      $data = array(
        'user_id' => $this->input->post('userid'),
        'query1' => $this->input->post('query1'),
        'query2' => $this->input->post('query2')
      );
      $this->db->insert('tb_MyiQuery', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    }
  }
  public function GetMyiQuery()
  {
    $userid = $this->input->post('userid');
    $result_query = $this->db->query("SELECT * FROM `tb_MyiQuery` WHERE `user_id` = '$userid'");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $data[] =  array(
        'queryid'        => $result['queryid'],
        'query1'      => $result['query1'],
        'query2'      => $result['query2']
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function commentanswer()
  {
    $data = array(
      'userid' => $this->input->post('userid'),
      'comment' => $this->input->post('comment'),
      'userstatus' => 'self'
    );
    $this->db->insert('tb_usercomments', $data);
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function RemoveiQuery()
  {
    $queryid = $this->input->post('queryid');
    $check = $this->db->query("delete from tb_MyiQuery WHERE queryid='$queryid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function Myiquerylike()
  {
    $uid = $this->input->post('userid');
    $queryid = $this->input->post('queryid');
    $qid = $this->input->post('querynum');
    $result = $this->db->query("select * from tb_MyiQuery WHERE queryid='$queryid'");
    if ($this->db->affected_rows() > 0) {
      $result = $this->db->query("SELECT * FROM `tb_MyiQuery` WHERE `$qid` IS NOT NULL and `user_id`='$uid' and queryid='$queryid'");
      if ($this->db->affected_rows() > 0) {
        $result = $this->db->query("SELECT * FROM `tb_myiquerylike` WHERE user_liked='$uid' and querynum='$qid' and myqueryid='$queryid'");
        if ($this->db->affected_rows() > 0) {
          //already existing in db
          $result = $this->db->query("select * from tb_myiquerylike WHERE user_liked='$uid' and querynum='$qid' and like_status='like' and myqueryid='$queryid'");
          if ($this->db->affected_rows() > 0) {
            $check = $this->db->query("update tb_myiquerylike set like_status='dislike' WHERE user_liked='$uid' and querynum='$qid' and myqueryid='$queryid'");
            if ($this->db->affected_rows() > 0) {
              return "successful";
            } else {
              return "fail";
            }
          }
          $result = $this->db->query("select * from tb_myiquerylike WHERE user_liked='$uid' and querynum='$qid' and like_status='dislike' and myqueryid='$queryid'");
          if ($this->db->affected_rows() > 0) {
            $check = $this->db->query("update tb_myiquerylike set like_status='like' WHERE user_liked='$uid' and querynum='$qid' and myqueryid='$queryid'");
            if ($this->db->affected_rows() > 0) {
              return "success";
            } else {
              return "fail";
            }
          }
        } else {
          //insert into db
          $data = array(
            'user_liked' => $this->input->post('userid'),
            'myqueryid' => $this->input->post('queryid'),
            'querynum' => $this->input->post('querynum'),
            'like_status' => $this->input->post('status')
          );
          $this->db->insert('tb_myiquerylike', $data);
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        }
      } else {
        return "querynotexist";
      }
    } else {
      return "querynotexist";
    }
  }
  public function myiquerycomment()
  {
    $queryid = $this->input->post('queryid');
    $result = $this->db->query("select * from tb_MyiQuery WHERE queryid='$queryid'");
    if ($this->db->affected_rows() > 0) {
      $data = array(
        'user_commented' => $this->input->post('userid'),
        'queryid' => $this->input->post('queryid'),
        'querynum' => $this->input->post('querynum'),
        'comment' => $this->input->post('comment'),
        'userstatus' => 'vistor'
      );
      $this->db->insert('tb_myiquerycomment', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    } else {
      return "notexist";
    }
  }
  public function GetMyiQueryComment()
  {
    $queryid = $this->input->post('queryid');
    $result_1query = $this->db->query("SELECT * FROM `tb_myiquerycomment` WHERE `queryid` = $queryid AND `querynum` LIKE 'query1'");
    if ($result_1query->num_rows() > 0) {
      $output1 = $result_1query->result_array();
      // print_r($output1);die();
      $data = array();
      foreach ($output1 as $result) {
        $userid = $result['user_commented'];
        $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
        $output = $resultuser->row();

        if (!empty($output->profile_pic)) {
          $string = $output->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $output->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $q1[] =  array(
          'id'        => $result['querycomment_id'],
          'comment'      => $result['comment'],
          'commented_userid'      => $result['user_commented'],
          'commented_username' => $output->name,
          'commented_userprofile' => $pic,
          'commented_at' => $result['updated_at'],
          'like' => "",
          'reply' => ""
        );
      }

      $data['query1'] = $q1;
    } else {
      $q1 = array();
      $data['query1'] = $q1;
    }
    $result_2query = $this->db->query("SELECT * FROM `tb_myiquerycomment` WHERE `queryid` = $queryid AND `querynum` LIKE 'query2'");
    if ($result_2query->num_rows() > 0) {
      $output2 = $result_2query->result_array();
      // print_r($output);die();
      foreach ($output2 as $result) {
        $userid = $result['user_commented'];
        $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
        $output = $resultuser->row();
        if (!empty($output->profile_pic)) {
          $string = $output->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $output->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $q2[] =  array(
          'id'        => $result['querycomment_id'],
          'comment'      => $result['comment'],
          'commented_userid'      => $result['user_commented'],
          'commented_username' => $output->name,
          'commented_userprofile' => $pic,
          'commented_at' => $result['updated_at'],
          'like' => "",
          'reply' => ""
        );
      }
      $data['query2'] = $q2;
    } else {
      $q2 = array();
      $data['query2'] = $q2;
    }
    // print_r($data);die();
    return $data;
  }
  public function sort_array_of_array(&$array, $subfield)
  {
    $sortarray = array();
    foreach ($array as $key => $row) {
      $sortarray[$key] = $row[$subfield];
    }

    array_multisort($sortarray, SORT_ASC, $array);
  }
  public function Top100()
  {
    $uid = $this->input->post('userid');
    $result = $this->db->query("SELECT * FROM `tb_user`");
    $output = $result->result_array();
    // print_r($output);die();
    foreach ($output as $result) {
      $userid = $result['id'];
      $userresult = $this->db->query("SELECT count(*) as total  FROM `tb_fans`where (req_from=$userid or req_to=$userid)");
      $useroutput = $userresult->row();
      if (($useroutput->total) != 0) {
        $data[] = array(
          'count' => $useroutput->total,
          'userid' => $userid
        );
      }
    }
    uasort($data, function ($item, $compare) {
      return $item['count'] <= $compare['count'];
    });

    // print_r($data);//sorted array
    foreach ($data as $result) {
      $uid = $result['userid'];
      $userquery = $this->db->query("SELECT * FROM `tb_user` where id=$uid");
      $userqueryoutput = $userquery->row();

      if (!empty($userqueryoutput->profile_pic)) {
        $string = $userqueryoutput->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $userqueryoutput->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $userqueryoutput->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $userdata[] = array(
        'userid' => $result['userid'],
        'username' => $userqueryoutput->name,
        'profile_pic' => $pic,
        'fan_count' => $result['count']
      );
    }
    $finaldata = $userdata;
    // print_r(array_slice($finaldata, 0, 99));//to get only 100 users
    $top100_array = array_slice($finaldata, 0, 99);
    // print_r($finaldata);
    // die();
    return $top100_array;
  }
  public function d()
  {
    $userid = $this->input->post('userid');
    $searchcategory = $this->input->post('category');
    $query = $this->db->query("select * from tb_match WHERE (rival_id='$userid' or opponent_id='$userid') and invitation_status='accept'");
    $result_array =  $query->result_array();
    $data = array();
    foreach ($result_array as $result) {
      $matchid = $result['matchid'];
      $rivalid = $result['rival_id']; //rival
      $oppoid = $result['opponent_id']; //opponent
      // echo"select contestent_id from tb_like WHERE user_liked='$userid' and matchid='$matchid'";die();
      $userlike = $this->db->query("select contestent_id from tb_like WHERE user_liked='$userid' and matchid='$matchid'");
      if ($userlike->num_rows() > 0) {
        $userlikes = $userlike->row();
        // print_r($userlikes);die();
        $contid = $userlikes->contestent_id;
        // echo $contid;die();
        if ($contid == $rivalid) {
          // echo "rival match";
          $userrivallike = '1';
          $useroppolike = '0';
          $usercommonlike = '0';
          $agree = '0';
          $disagree = '0';
        }
        if ($contid == 0) {
          // echo "rival match";
          $userrivallike = '0';
          $useroppolike = '0';
          $usercommonlike = '1';
          $commonquerylike = $this->db->query("select count(*) as common_like from tb_like WHERE contestent_id='0' and matchid='$matchid' and like_status='like'");
          $commonquerylikeoutput = $commonquerylike->row();
          $commonquerydislike = $this->db->query("select count(*) as common_dislike from tb_like WHERE contestent_id='0' and matchid='$matchid' and like_status='dislike'");
          $commonquerydislikeoutput = $commonquerydislike->row();
          $agree = $commonquerylikeoutput->common_like;
          $disagree = $commonquerydislikeoutput->common_dislike;
        }
        if ($contid == $oppoid) {
          // echo "oppo match";
          $userrivallike = '0';
          $useroppolike = '1';
          $usercommonlike = '0';
          $agree = '0';
          $disagree = '0';
        }
      } else {
        $userrivallike = '0';
        $useroppolike = '0';
        $usercommonlike = '0';
        $agree = '0';
        $disagree = '0';
      }
      $likequery = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$rivalid' and matchid='$matchid'");
      $likeoutput = $likequery->row();
      $sender_like = $likeoutput->sender_like;
      $queryresult = $this->db->query("select * from tb_user WHERE id='$rivalid'");
      $outputresult = $queryresult->row();

      if (!empty($outputresult->profile_pic)) {
        $string = $outputresult->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $outputresult->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $outputresult->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $likeoppquery = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppoid' and matchid='$matchid'");
      $likeoppoutput = $likeoppquery->row();
      $receiver_like = $likeoppoutput->receiver_like;
      $query = $this->db->query("select * from tb_user WHERE id='$oppoid'");
      $output = $query->row();

      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $picture = $output->profile_pic;
        } else {
          // echo "not found";
          $picture = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $commentquery = $this->db->query("select count(*) as total_comments from tb_comment WHERE match_id='$matchid' and contestent_id!='0'");
      $commentoutput = $commentquery->row();
      $commoncommentquery = $this->db->query("select count(*) as total_commoncomments from tb_comment WHERE match_id='$matchid' AND `contestent_id` = 0");
      $commoncommentoutput = $commoncommentquery->row();
      $data[] =  array(
        'match_id'        => $result['matchid'],
        'senderid'        => $result['rival_id'],
        'sender_name' => $outputresult->name,
        'sender_profile' => $pic,
        'sender_email' => $outputresult->email,
        'receiverid'      => $result['opponent_id'],
        'receiver_name' => $output->name,
        'receiver_profile' => $picture,
        'receiver_email' => $output->email,
        'description'       => $result['description'],
        'time_duration'        => $result['time_duration'],
        'caption'      => $result['caption'],
        'category'       => $result['category'],
        'match_start' => $result['replied_at'],
        'match_end' => $result['match_end'],
        'match_invitationsend' => $result['created_at'],
        'sender_image' => base_url() . 'assets/images/splash.jpg',
        'receiver_image' => base_url() . 'assets/images/splash.jpg',
        'sender_likecount' => $sender_like,
        'sender_likecount' => $sender_like,
        'receiver_likecount' => $receiver_like,
        'total_likecount' => $sender_like + $receiver_like,
        'userliked_sender' => $userrivallike,
        'userliked_receiver' => $useroppolike,
        'userliked_common' => $usercommonlike,
        'agree_count' => $agree,
        'disagree_count' => $disagree,
        'total_comment' => $commentoutput->total_comments,
        'total_commoncomments' => $commoncommentoutput->total_commoncomments
      );
    }
    $finaldata = $data;
    $data = array_slice($finaldata, 0, 10);
    return $data;
  }
  public function winners_of_the_moment()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $user_id = $this->input->post('userid');
    $result = $this->db->query("select * from tb_match WHERE (rival_id='$user_id' or opponent_id='$user_id') and match_status='1'");
    $result_array = $result->result_array();
    $data = array();
    foreach ($result_array as $result) {
      $uid = $result['rival_id']; //rival 
      // echo $uid;
      $oppouid = $result['opponent_id']; //opponent
      // echo $oppouid;
      $matchid = $result['matchid'];
      $query = $this->db->query("select * from tb_user WHERE id='$uid'");
      $output = $query->row();
      $likequery = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$uid' and matchid='$matchid'");
      $likeoutput = $likequery->row();
      $sender_like = $likeoutput->sender_like;
      $resultquery = $this->db->query("select * from tb_user WHERE id='$oppouid'");
      $resultoutput = $resultquery->row();
      $likeoppquery = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppouid' and matchid='$matchid'");
      $likeoppoutput = $likeoppquery->row();
      $receiver_like = $likeoppoutput->receiver_like;
      // echo $sender_like.'sender like';echo $receiver_like.'receiver like';
      // die();
      if ($sender_like > $receiver_like) {
        // echo "sender won";
        $winnerid = $uid;
        $likes = $sender_like;
      } else {
        // echo "receiver won";
        $winnerid = $oppouid;
        $likes = $receiver_like;
      }
      // echo $winnerid.'userid';echo $likes.'like count';die();
      $datawinner[] = array(
        'matchid' => $matchid,
        'winner' => $winnerid,
        'like' => $likes,
      );
      //  print_r($datawinner);
      foreach ($datawinner as $winner) {
        $winneruserid = $winner['winner'];
        // echo $winneruserid;
        $winnerquery = $this->db->query("select * from tb_user WHERE id='$winneruserid'");
        $winnerqueryoutput = $winnerquery->row();

        if (!empty($winnerqueryoutput->profile_pic)) {
          $string = $winnerqueryoutput->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $winnerqueryoutput->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $winnerqueryoutput->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        $datafinal[] =  array(
          'match_id'        => $winner['matchid'],
          'winner_name' => $winnerqueryoutput->name,
          'profile_pic' => $pic,
          'winner_email' => $winnerqueryoutput->email,
          'total_likecount' => $winner['like']
        );
      }
      $data = $datafinal;
    }
    // print_r($data);
    $unique = array_map("unserialize", array_unique(array_map("serialize", $data)));
    // print_r($unique);die();
    // print_r(array_unique($data))/;die();
    return $unique;
  }

  /* -------------------crone function start----------------------------*/
  public function matchend_notificationcrone()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    // echo $current_date.'current_date <br/>';
    $result = $this->db->query("select * from tb_match WHERE match_status='1'");
    $result_array = $result->result_array();
    foreach ($result_array as $result) {
      $mend = $result['match_end'];
      // echo $mend.'end <br/>';
      if ($mend < $current_date) {
        // echo "match over";
        $matchid = $result['matchid'];
        // echo $matchid;
        $check = $this->db->query("update tb_match set match_status=0 WHERE matchid='$matchid'");
        $results = $this->db->query("select * from tb_match WHERE matchid='$matchid'");
        $output =  $results->row();
        $from = $output->rival_id;
        $to = $output->opponent_id;
        $query =  $this->db->query("select * from tb_user where id='$from'");
        $queryto =  $this->db->query("select * from tb_user where id='$to'");
        $result_array =  $query->row();
        $resultarray =  $queryto->row();
        $senderdevtoken = $result_array->device_token;
        $sendermessage = 'Your Match with ' . $resultarray->name . ' is over';
        $senderdevicetype = $result_array->device_type;
        $data = array(
          'sender_id' => $from,
          'receiver_id' => $to,
          'message' => 'Your Match with ' . $resultarray->name . ' is over',
          'notification_status' => 'match_reply',
          'request_id' => $matchid
        );
        $this->db->insert('tb_notification', $data);
        $this->push($senderdevtoken, $sendermessage, $senderdevicetype);
        $receiverdevtoken = $resultarray->device_token;
        $receivermessage = 'Your Match with ' . $result_array->name . ' is over';
        $receiverdevicetype = $resultarray->device_type;
        $datas = array(
          'sender_id' => $to,
          'receiver_id' => $from,
          'message' => 'Your Match with ' . $result_array->name . ' is over',
          'notification_status' => 'match_reply',
          'request_id' => $matchid
        );
        $this->db->insert('tb_notification', $datas);
        $this->push($receiverdevtoken, $receivermessage, $receiverdevicetype);
      }
      // else{
      //   echo "match ongoing";

      // }
    }
  }
  /* -------------------crone function end----------------------------*/
  public function Recommended_for_you()
  {
    $userid = $this->input->post('userid');
    $result = $this->db->query("select * from tb_user");
    //  print_r($result->num_rows());die();
    $output = $result->result_array();
    // $i=1;
    foreach ($output as $result) {
      $id = $result['id'];
      // echo $id;
      if ($id != $userid) {
        $check_userblocked = $this->db->query("select * from tb_bestie where ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and req_status='2'");
        if ($check_userblocked->num_rows() > 0) {
          // echo "exist";
          $block=1;
                }else{
                  // echo "not exist";
                  $block=0;
                } 
                // die();
                if($block==0){
        $query = $this->db->query("SELECT * FROM `tb_bestie` WHERE ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) and req_status='1'");
        $secondquery = $this->db->query("SELECT * FROM `tb_fans` WHERE ((req_from='$userid' and req_to='$id') or (req_from='$id' and req_to='$userid')) ");
        if (($query->num_rows() == 0) && ($secondquery->num_rows() == 0)) {
          // echo $id; echo "not bestie and fan,";
          $userquery =  $this->db->query("select * from tb_user where id='$id'");
          $userqueryoutput = $userquery->row();

          if (!empty($userqueryoutput->profile_pic)) {
            $string = $userqueryoutput->profile_pic;
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
              // one or more of the 'special characters' found in $string
              // echo "found";
              $pic = $userqueryoutput->profile_pic;
            } else {
              // echo "not found";
              $pic = base_url() . 'uploads/profile_image/' . $userqueryoutput->profile_pic;
            }
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/user.png';
          }
          $userdata[] = array(
            // 'index'=>$i,
            'userid'        => $id,
            'name' => $userqueryoutput->name,
            'profile_pic' => $pic,
            'email' => $userqueryoutput->email
          );
          // $i++;
        }
        //  else 
        //  {
        //   echo "bestie or fan ,";
        // }
      }
    }
    }
    // print_r($userdata);die();
    // print_r(array_chunk($userdata, 4));
    // die();
    // $final = array_chunk($userdata, 4);
    // return $final;
    return $userdata;
  }
  public function chat()
  {
    $userid_from = $this->input->post('userid_from');
    $userid_to = $this->input->post('userid_to');
    $message = $this->input->post('message');
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $get_from_udata_query = $this->db->query("SELECT *  FROM `tb_user` WHERE id='$userid_from'");
    $get_from_udata = $get_from_udata_query->row();
    $fromname = $get_from_udata->name;
    $get_to_udata_query = $this->db->query("SELECT *  FROM `tb_user` WHERE id='$userid_to'");
    $get_to_udata = $get_to_udata_query->row();
    $toname = $get_to_udata->name;
    $todevtoken = $get_to_udata->device_token;
    $todevtype = $get_to_udata->device_type;
    $tomessage = "$fromname: $message ";
    $query =  $this->db->query("SELECT *  FROM `tb_userchat` WHERE ((`userid_from` = $userid_from) and (`userid_to` = $userid_to) or (`userid_from` = $userid_to) and (`userid_to` = $userid_from))");
    if ($this->db->affected_rows() > 0) {
      $output = $query->row();
      $chattoken = $output->chattoken;
    } else {

      $chattoken = 'chat' . $userid_from . $userid_to;
    }
    $data = array(
      'userid_from' => $userid_from,
      'userid_to' => $userid_to,
      'message' => $message,
      'chattoken' => $chattoken,
      'created_at' => $current_date
    );
    $this->db->insert('tb_userchat', $data);
    if ($this->db->affected_rows() > 0) {
      $this->push($todevtoken, $tomessage, $todevtype);
      return "success";
    } else {
      return "fail";
    }
  }
  public function getchat()
  {
    $userid = $this->input->post('userid');
    $query =  $this->db->query("SELECT *  FROM `tb_userchat` WHERE (`userid_from` = $userid) or (`userid_to` = $userid)  group by chattoken");
    // echo "SELECT *  FROM `tb_userchat` WHERE (`userid_from` = $userid) or (`userid_to` = $userid)  group by chattoken";die();
    if ($query->num_rows() > 0) {
      $output = $query->result_array();
      // print_r($output);die();
      foreach ($output as $result) {
        $userid_from = $result['userid_from'];
        $userid_to = $result['userid_to'];
        if ($userid_from == $userid) {
          $chatuser = $userid_to;
        } else {
          $chatuser = $userid_from;
        }
        $chattoken = $result['chattoken'];
        $chatquery = $this->db->query("SELECT * FROM `tb_userchat` WHERE chattoken='$chattoken' ORDER BY `tb_userchat`.`created_at` DESC");
        $chatoutput = $chatquery->row();
        $userdetails = $this->db->query("SELECT * FROM `tb_user` WHERE id='$chatuser' ");
        $userdetailsoutput = $userdetails->row();

        if (!empty($userdetailsoutput->profile_pic)) {
          $string = $userdetailsoutput->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $pic = $userdetailsoutput->profile_pic;
          } else {
            // echo "not found";
            $pic = base_url() . 'uploads/profile_image/' . $userdetailsoutput->profile_pic;
          }
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/user.png';
        }
        if ($userdetailsoutput->online_offline_status == 0) {
          $onoffstatus = "online";
        } else {
          $onoffstatus = "offline";
        }
        if (empty($chatoutput->message)) {
          $fileextention = $chatoutput->filename;
          $ex_result = explode(".", $fileextention);
          $extension = $ex_result[1];
          if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
            $ftype = "audio";
          } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
            $ftype = "photo";
          } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
            $ftype = "video";
          } else {
            $ftype = "file";
          }
        } else {
          $ftype = " ";
        }
        $data[] = array(
          'userid' => $userdetailsoutput->id,
          'name' => $userdetailsoutput->name,
          'profile_pic' => $pic,
          'last_message' => $chatoutput->message,
          'filetype' => $ftype,
          'send_at' => $chatoutput->created_at,
          'online_offline_status' => $onoffstatus,
          'last_seen' => $userdetailsoutput->last_seen,
        );
      }
    } else {
      $data = array();
    }
    return $data;
  }
  public function getdetailchat()
  {
    $loggeduserid = $this->input->post('loggeduserid');
    $chatuserid = $this->input->post('chatuserid');
    $chatquery = $this->db->query("SELECT * FROM `tb_userchat` WHERE ((`userid_from` = $loggeduserid) and (`userid_to` = $chatuserid) or (`userid_from` = $chatuserid) and (`userid_to` = $loggeduserid))");
    if ($chatquery->num_rows() > 0) {
      $userdetails = $this->db->query("SELECT * FROM `tb_user` WHERE id='$chatuserid' ");
      $userdetailsoutput = $userdetails->row();
      if ($userdetailsoutput->online_offline_status == 0) {
        $onoffstatus = "online";
      } else {
        $onoffstatus = "offline";
      }
      $chatoutput = $chatquery->result_array();

      foreach ($chatoutput as $result) {
        if (!empty($result['filename'])) {
          $file_name = base_url() . 'uploads/UserChatuploads/' . $result['filename'];
        } else {
          $file_name = " ";
        }
        if (!empty($result['message'])) {
          $text = $result['message'];
          $bHasLink = strpos($text, 'http') !== false || strpos($text, 'www.') !== false;
          // print_r($bHasLink);die();
          if ($bHasLink) {
            $file_type = "link";
          } else {
            $file_type = " ";
          }
        } else {
          $file_type = " ";
        }
        $data[] = array(
          'userid_from' => $result['userid_from'],
          'userid_to' => $result['userid_to'],
          'message' => $result['message'],
          'filename' => $file_name,
          'filetype' => $file_type,
          'send_at' => $result['created_at'],
          'online_offline_status' => $onoffstatus,
          'last_seen' => $userdetailsoutput->last_seen,
        );
      }
    } else {
      $data = array();
    }
    return $data;
  }
  public function changechatstatus()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $chatstatus = $this->input->post('status');
    $userid = $this->input->post('userid');
    $check = $this->db->query("update tb_user set online_offline_status='$chatstatus',last_seen='$current_date' WHERE id='$userid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function BestMoment()
  {
    $caption = $this->input->post('Caption');
    $userid = $this->input->post('userid');
    $data = array(
      'caption' => $caption,
      'userid' => $userid
    );
    $this->db->insert('tb_bestmoment', $data);
    if ($this->db->affected_rows() > 0) {
      $moment_id = $this->db->insert_id();
      return $moment_id;
    } else {
      return "fail";
    }
  }
  public function uploadedbestmomentfile($image)
  {
    $momentid = $this->input->post('momentid');
    $data = array(
      'momentid' => $momentid,
      'filename' => $image
    );
    $this->db->insert('tb_bestmomentupload', $data);
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function getBestMoment()
  {
    $userid = $this->input->post('userid');
    $query =  $this->db->query("select * from tb_bestmoment where userid='$userid'");
    if ($this->db->affected_rows() > 0) {
      $result_array =  $query->result_array();
      $data = array();
      foreach ($result_array as $result) {
        $momentid = $result['momentid'];
        $momentquery =  $this->db->query("select *,count(*) as total from tb_bestmomentupload where momentid='$momentid'");
        $result_query =  $momentquery->row();
        if (!empty($result_query->filename)) {
          $file = base_url() . 'uploads/Useruploads/' . $result_query->filename;
        } else {
          $file = base_url() . 'uploads/Useruploads/default.jpg';
        }
        $momentlikequery =  $this->db->query("select count(*) as totallike from tb_momentlike where momentid='$momentid' and like_status='like'");
        $result_likequery =  $momentlikequery->row();
        $momentcommentquery =  $this->db->query("select count(*) as totalcomment from tb_momentcomment where momentid='$momentid'");
        $result_commentquery =  $momentcommentquery->row();
        $data[] =  array(
          'momentid' => $result['momentid'],
          'caption' => $result['caption'],
          'Like_count' => $result_likequery->totallike,
          'comment_count' => $result_commentquery->totalcomment,
          'media_count' => $result_query->total,
          'created_at' => $result['created_at'],
          'file' => $file
        );
      }
      // $data = array_merge($datas, $link);
      return $data;
    }
  }
  public function getBestMomentFile()
  {
    $userid = $this->input->post('userid');
    $query =  $this->db->query("select * from tb_bestmoment where userid='$userid'");
    if ($this->db->affected_rows() > 0) {
      $result_array =  $query->result_array();
      $data = array();
      foreach ($result_array as $result) {
        $momentid = $result['momentid'];
        $momentquery =  $this->db->query("select * from tb_bestmomentupload where momentid='$momentid'");
        if ($this->db->affected_rows() > 0) {
          $momentresult_array =  $momentquery->result_array();
          foreach ($momentresult_array as $outputresult)
            // $link[]
            $data[]  =  array(
              'momentid' => $momentid,
              'file' => base_url() . 'uploads/Useruploads/' . $outputresult['filename']
            );
        } else {
          $data[]  =  array(
            'momentid' => $momentid,
            'file' => base_url() . 'uploads/Useruploads/default.jpg'
          );
        }
      }
      // $data = array_merge($datas, $link);
      return $data;
    }
  }
  public function uploadedchatfile($filename)
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $utid = $this->input->post('userid_to');
    $ufid = $this->input->post('userid_from');
    if ((empty($utid)) || (empty($ufid))) {
      return "empty";
    } else {
      $query =  $this->db->query("SELECT *  FROM `tb_userchat` WHERE ((`userid_from` = $ufid) and (`userid_to` = $utid) or (`userid_from` = $utid) and (`userid_to` = $ufid))");
      if ($this->db->affected_rows() > 0) {
        $output = $query->row();
        $chattoken = $output->chattoken;
      } else {

        $chattoken = 'chat' . $ufid . $utid;
      }
      $data = array(
        'userid_to' => $utid,
        'userid_from' => $ufid,
        'chattoken' => $chattoken,
        'filename' => $filename,
        'created_at' => $current_date
      );
      $this->db->insert('tb_userchat', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    }
  }
  public function MomentLike()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $uid = $this->input->post('userid');
    $mid = $this->input->post('momentid');
    $result = $this->db->query("select * from tb_bestmoment WHERE momentid='$mid'");
    if ($this->db->affected_rows() > 0) {
      $result = $this->db->query("select * from tb_momentlike WHERE userid='$uid' and momentid='$mid'");
      if ($this->db->affected_rows() > 0) {
        $checkresult = $this->db->query("select * from tb_momentlike WHERE userid='$uid' and momentid='$mid' and like_status='dislike'");
        if ($this->db->affected_rows() > 0) {
          $check = $this->db->query("update tb_momentlike set like_status='like' WHERE userid='$uid' and momentid='$mid'");
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        } else {
          $check = $this->db->query("update tb_momentlike set like_status='dislike' WHERE userid='$uid' and momentid='$mid'");
          if ($this->db->affected_rows() > 0) {
            return "successful";
          } else {
            return "fail";
          }
        }
      } else {
        $data = array(
          'userid' => $this->input->post('userid'),
          'momentid' => $this->input->post('momentid'),
          'like_status' => 'like',
          'updated_at' => $current_date
        );
        $this->db->insert('tb_momentlike', $data);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function addmomentcomment()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $uid = $this->input->post('userid');
    $mid = $this->input->post('momentid');
    $cid = $this->input->post('comment');
    $result = $this->db->query("select * from tb_bestmoment WHERE momentid='$mid'");
    if ($this->db->affected_rows() > 0) {
      $data = array(
        'userid' => $this->input->post('userid'),
        'momentid' => $this->input->post('momentid'),
        'comment' => $this->input->post('comment'),
        'updated_at' => $current_date
      );
      $this->db->insert('tb_momentcomment', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    } else {
      return "notexist";
    }
  }
  public function getmomentcomment()
  {
    $userid = $this->input->post('userid');
    $momentid = $this->input->post('momentid');
    $result_query = $this->db->query("SELECT * FROM `tb_momentcomment` WHERE momentid='$momentid' ");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();
    foreach ($output as $result) {
      $uid = $result['userid'];
      $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$uid'");
      $output = $resultuser->row();

      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $output->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $data[] =  array(
        'id'        => $result['mcomment_id'],
        'commented_userid'      => $result['userid'],
        'commented_username'      => $output->name,
        'commented_userprofile'      => $pic,
        'comment'       => $result['comment'],
        'commented_at'       => $result['updated_at'],
        // 'like' => '',
        // 'reply' => ''
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function match_fileupload($imagename)
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $mid = $this->input->post('matchid');
    $upid = $this->input->post('upload_id');
    // print_r($this->input->post('userid'));die();
    if (!empty($mid)) {
      //  echo "match not empty" ;die();
      $data = array(
        'matchid' => $this->input->post('matchid'),
        'user_uploaded' => $this->input->post('userid'),
        'filename' => $imagename,
        'filetype' => 'file',
        'created_at' => $current_date
      );
      $result_query =  $this->db->query("select * from tb_match where matchid= $mid");
      if ($this->db->affected_rows() > 0) {
        $this->db->insert('tb_matchupload', $data);
        if ($this->db->affected_rows() > 0) {

          // return "success";
          $imagedata = array(
            'filename' => base_url() . 'uploads/Matchuploads/' . $imagename
          );
          return $imagedata;
        } else {
          return "fail";
        }
      } else {
        return "notexist";
      }
    } else {
      // echo "match empty" ;die();
      //this is the code for which uploading matchfile without matchid
      $result_query =  $this->db->query("select * from tb_matchupload where mup_id= $upid");
      if ($this->db->affected_rows() > 0) {
        $check = $this->db->query("update tb_matchupload set filename='$imagename',created_at='$current_date' WHERE mup_id= $upid ");
        $imagedata = array(
          'uploaded_id' => $upid,
          'filename' => base_url() . 'uploads/Matchuploads/' . $imagename
        );
        return $imagedata;
      } else {
        $data = array(
          'matchid' => $this->input->post('matchid'),
          'user_uploaded' => $this->input->post('userid'),
          'filename' => $imagename,
          'filetype' => 'file',
          'created_at' => $current_date
        );
        $this->db->insert('tb_matchupload', $data);
        if ($this->db->affected_rows() > 0) {
          $up_id = $this->db->insert_id();
          $imagedata = array(
            'uploaded_id' => $up_id,
            'filename' => base_url() . 'uploads/Matchuploads/' . $imagename
          );
          return $imagedata;
        } else {
          return "fail";
        }
      }
    }
  }
  public function GetUserdetails()
  {
    $email = $this->input->post('email');
    $checkemail = $this->db->query("select * from tb_user where email='$email' ");
    if ($checkemail->num_rows() > 0) {
      $result_array = $checkemail->row();
      // print_r($output);die();
      $data = array();
      $userid = $result_array->id;
      $squad = $this->db->query("select count(*) as squad_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='squad' and req_status='1'");
      $result_squad = $squad->row();
      // print_r($result_squad);
      // echo ;die();
      $bestie = $this->db->query("select count(*) as bestie_count from tb_bestie where (req_from='$userid' or req_to='$userid') and category='bestie' and req_status='1'");
      $result_bestie = $bestie->row();
      $fan = $this->db->query("select count(*) as fan_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan'");
      $result_fan = $fan->row();
      $fanof = $this->db->query("select count(*) as fanof_count from tb_fans where (req_from='$userid' or req_to='$userid') and category='fan_of'");
      $result_fanof = $fanof->row();
      date_default_timezone_set('Asia/Kolkata');
      $current_date    = date('Y-m-d H:i:s');
      $matches = $this->db->query("select count(*) as match_count from tb_match where invitation_status='accept' and (rival_id='$userid' or opponent_id='$userid')");
      $result_matches = $matches->row();
      // print_r($result_matches);die();
      if (!empty($result_array->profile_pic)) {
        $string = $result_array->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $result_array->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $result_array->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      if ($result_array->online_offline_status == 0) {
        $onoffstatus = "online";
      } else {
        $onoffstatus = "offline";
      }
      $data[] =  array(
        'userid'        => $result_array->id,
        'name'      => $result_array->name,
        'email'       => $result_array->email,
        'university_name' => $result_array->university_name,
        'profile_pic'      => $pic,
        'squad_count' => $result_squad->squad_count,
        'bestie_count' => $result_bestie->bestie_count,
        'fans_count' => $result_fan->fan_count,
        'fans_of_count' => $result_fanof->fanof_count,
        'matches_count' => $result_matches->match_count,
        'online_offline_status' => $onoffstatus,
        'last_seen' => $result_array->last_seen
      );
    }
    // print_r($data);die();
    return $data;
  }
  public function addreplycomment()
  {
    $uid = $this->input->post('userid');
    $mid = $this->input->post('matchid');
    $cid = $this->input->post('comment');
    $contestentid = $this->input->post('contestentid');
    $result = $this->db->query("select * from tb_match WHERE matchid='$mid'");
    if ($this->db->affected_rows() > 0) {
      // $result = $this->db->query("select * from tb_like WHERE user_liked='$uid' and matchid='$mid'");
      // if ($this->db->affected_rows() > 0) {
      //  $check = $this->db->query("update tb_like set contestent_id='$cid',like_status='$status' WHERE user_liked='$uid' and matchid='$mid'"); 
      //  if ($this->db->affected_rows() > 0) {
      //   return "success";
      // } else {
      //   return "fail";
      // }
      // }
      // else{
      $data = array(
        'userid' => $this->input->post('userid'),
        'match_id' => $this->input->post('matchid'),
        'comment' => $this->input->post('comment'),
        'contestent_id' => $this->input->post('contestentid'),
        'replied_commentid' => $this->input->post('replied_commentid')
      );
      $this->db->insert('tb_comment', $data);
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
      // } 
    } else {
      return "notexist";
    }
  }
  public function comment_like()
  {
    $cid = $this->input->post('commentid');
    $uid = $this->input->post('userid');
    $valid = $this->db->query("select * from tb_comment WHERE comment_id='$cid' ");
    if ($this->db->affected_rows() > 0) {
      $check = $this->db->query("select * from tb_commentlike WHERE commentid='$cid' and userliked='$uid'");
      if ($this->db->affected_rows() > 0) {
        $result = $this->db->query("select * from tb_commentlike WHERE commentid='$cid' and userliked='$uid' and likestatus='like'");
        if ($this->db->affected_rows() > 0) {
          //dislike
          $update = $this->db->query("update tb_commentlike set likestatus='dislike' WHERE commentid='$cid' and userliked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "successful";
          } else {
            return "fail";
          }
        } else {
          //like
          $update = $this->db->query("update tb_commentlike set likestatus='like' WHERE commentid='$cid' and userliked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        }
      } else {
        $data = array(
          'commentid' => $this->input->post('commentid'),
          'userliked' => $this->input->post('userid'),
          'likestatus' => 'like',
        );
        $this->db->insert('tb_commentlike', $data);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function CreateOpenMatch($data){
    $match['time_duration'] = $data['time_duration'];
    $match['caption'] = $data['caption'];
    $match['description'] = $data['description'];
    $match['category'] = $data['category'];
    $match['match_type'] = "open";
    $match['rival_id'] = $data['rival_id'];
    $this->db->insert('tb_match', $match);
    $match_id = $this->db->insert_id();

    $rival_id = $data['rival_id'];
    $query = "SELECT * FROM tb_user WHERE id = '".$rival_id."'";
    $user = $this->db->query($query)->row();
    foreach(json_decode($data['opponent_id']) as $item){
      $notification = array(
        'sender_id' => $rival_id,
        'receiver_id' => $item->userid,
        'message' => $user->name . ' ' . 'Invited you from ' . $match['category'] . ' list to have a Match of ' . $match['time_duration'] . ' duration', //receiver msg
        'notification_status' => 'Match_invitation',
        'request_id' => $match_id
      );
      $this->db->insert('tb_notification', $notification);
  
      $senderdevtoken = $user->device_token;
      $sendermessage = 'You have Invited ' . $item->name . ' from ' . $match['category'] . ' list to have a Match of ' . $match['time_duration'] . ' duration';
      $senderdevicetype = $user->device_type;
      // echo $senderdevtoken; echo $sendermessage; echo $senderdevicetype.'senderdata';
      $this->push($senderdevtoken, $sendermessage, $senderdevicetype);  
      $matchuser = array(
        'user_id' => $rival_id,
        'opponent_id' => $item->userid,
        'match_id'=> $match_id 
      );
      $this->db->insert('tb_matchusers', $matchuser);
    }
    return $match_id;
    
  }
  public function MatchUpload()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $mid = $this->input->post('matchid');
    $upid = $this->input->post('upload_id');
    $text = $this->input->post('text');
    $type = $this->input->post('filetype');
    // print_r($this->input->post('userid'));die();

    // $result_query =  $this->db->query("select * from tb_matchupload where mup_id= $upid");
    // if ($this->db->affected_rows() > 0) {
    //   $check = $this->db->query("update tb_matchupload set filename='$imagename',created_at='$current_date' WHERE mup_id= $upid ");
    //   $imagedata = array(
    //     'uploaded_id' =>$upid,
    //     'filename' =>base_url() . 'uploads/Matchuploads/'.$imagename
    //   );
    //   return $imagedata;
    // }else{
    $data = array(
      'matchid' => $this->input->post('matchid'),
      'user_uploaded' => $this->input->post('userid'),
      'filename' => $text,
      'filetype' => $type,
      'created_at' => $current_date
    );
    $this->db->insert('tb_matchupload', $data);
    if ($this->db->affected_rows() > 0) {
      $up_id = $this->db->insert_id();
      $imagedata = array(
        'uploaded_id' => $up_id,
        // 'filename' =>base_url() . 'uploads/Matchuploads/'.$imagename
      );
      return  $imagedata;
    } else {
      return "fail";
    }
  }
  // }
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
  public function create_personalmatch($userid)
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $data = array(
      'rival_id' => $userid,
      'opponent_id' => $userid,
      'caption' => $this->input->post('caption'),
      'match_type' => 'personal',
      'created_at' => $current_date
    );
    $userdetail_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$userid'");
    $userdetail =  $userdetail_query->row();
    $username = $userdetail->name;
    $fanquery = $this->db->query("SELECT * FROM `tb_fans` WHERE (req_from='$userid' or req_to='$userid') and category='fan'");
    $fanresult_array =  $fanquery->result_array();
    foreach ($fanresult_array as $results) {
      if ($results['req_from'] == $userid) {
        $fanuserid = $results['req_to'];
      } else {
        $fanuserid = $results['req_from'];
      }
      $fanresult_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$fanuserid'");
      if ($fanresult_query->num_rows() > 0) {
        $fanoutput =  $fanresult_query->row();
        $fandevicetoken = $fanoutput->device_token;
        $fandevicetype = $fanoutput->device_type;
        $fanpushmessage = "$username added a new personal match , Open app to view the contest";
        $datas = array(
          'sender_id' => $userid,
          'receiver_id' => $fanuserid,
          'message' => $fanpushmessage,
          'notification_status' => 'fan_notification',
          'request_id' => " "
        );
        $this->db->insert('tb_notification', $datas);
        $this->push($fandevicetoken, $fanpushmessage, $fandevicetype);
      }
    }
    //end
    //push notification for squad and bestie
    $squad_bestie_query = $this->db->query("SELECT * FROM `tb_bestie` WHERE (req_from='$userid' or req_to='$userid')");
    $squad_bestie_result_array =  $squad_bestie_query->result_array();
    foreach ($squad_bestie_result_array as $results) {
      if ($results['req_from'] == $userid) {
        $squad_bestie_userid = $results['req_to'];
      } else {
        $squad_bestie_userid = $results['req_from'];
      }
      $squad_bestie_result_query = $this->db->query("SELECT * FROM `tb_user` WHERE id='$squad_bestie_userid'");
      if ($squad_bestie_result_query->num_rows() > 0) {
        $squad_bestie_output =  $squad_bestie_result_query->row();
        $squad_bestie_devicetoken = $squad_bestie_output->device_token;
        $squad_bestie_devicetype = $squad_bestie_output->device_type;
        $squad_bestie_pushmessage = "$username added a new personal match , Open app to view the contest";
        $datas = array(
          'sender_id' => $userid,
          'receiver_id' => $squad_bestie_userid,
          'message' => $squad_bestie_pushmessage,
          'notification_status' => 'suqad_bestie_notification',
          'request_id' => " "
        );
        $this->db->insert('tb_notification', $datas);
        $this->push($squad_bestie_devicetoken, $squad_bestie_pushmessage, $squad_bestie_devicetype);
      }
    }
    //end
    $this->db->insert('tb_match', $data);
    $match_id = $this->db->insert_id();
    return $match_id;
  }
  public function  savefile($data)
  {
    $this->db->insert('tb_matchupload', $data);
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "failed";
    }
  }
  public function create_closedmatch()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $rival_userid = $this->input->post('rival_userid');
    $opp_userid = $this->input->post('opponent_userid'); //chosed personal match users id
    $pmatch_id = $this->input->post('personal_matchid');
    
    $select_medias = json_decode($this->input->post('select_medias'));
    $caption = $this->input->post('caption');
    $seen_status = $this->input->post('seen_status');
    $data = array(
      'rival_id' => $rival_userid,
      'opponent_id' => $opp_userid,
      'caption' => $caption,
      'match_type' => 'closed',
      'seen_status' => $seen_status,
      'created_at' => $current_date
    );
    $this->db->insert('tb_match', $data);
    if ($this->db->affected_rows() > 0) {
      $match_id = $this->db->insert_id();
      foreach($select_medias as $key => $item){
        $media = $this->db->query("SELECT * FROM tb_matchupload WHERE mup_id = '".$item."'")->row_array();
        $media['old_mupid'] = $media['mup_id'];
        unset($media['mup_id']);
        $media['created_at'] = date("Y-m-d H:s:i");
        $media['matchid'] = $match_id;
        $media['personal_matchid'] = $pmatch_id;
        $this->savefile($media);
      }
      return $match_id;
    } else {
      return 'failed';
    }
  }

  public function get_closedmatch(){
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $user_id = $this->input->post('userid');
    // $visitorid = $this->input->post('visitorid'); //only used to get the liked portion if the logged user is a visitor of other profile.
    $result = $this->db->query("select * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE (rival_id='$user_id' or opponent_id='$user_id') and match_type='closed' GROUP by tb_match.matchid");
    $result_array = $result->result_array();
    $data = array();
    foreach ($result_array as $result) {
      $matchid = $result['matchid'];

      $uid = $result['rival_id']; //rival 
      
      $output = $this->db->query("select * from tb_user WHERE id='$uid'")->row(); //rival
      $sender['user'] = $output;
      $medias = array();
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          $pic = $output->profile_pic;
        } else {
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $compare_result = array();
      $sender['pic'] = $pic;
      $matchsenderfilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$uid' and matchid=$matchid"); //rival
      $total_like = 0;
      if ($matchsenderfilequery->num_rows() > 0) {
        $up_files = $matchsenderfilequery->result_array();
        $sender = array();
        $sender['user'] = $output;
        
        foreach($up_files as $item){
          $media_id = $item['mup_id'];
          $sub_caption = $item['sub_caption'];
          $media_type = $item['filetype'];
          if ($media_type == 'file') {
            $media = base_url() . 'uploads/Matchuploads/' . $item['filename'];
          } else {
            $media = $item['filename'];
          }
          //to find if this image is used for any type of closed match
          $check_query = $this->db->query("SELECT * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          if ($check_query->num_rows() > 0) {
            $image_status = 1;
          } else {
            $image_status = 0;
          }

          //to find the cound of revealed closed match of this particular image
          $count_revealquery = $this->db->query("SELECT count(*) as sr_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='1' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          $countrevealresult = $count_revealquery->row();
          $r_count = $countrevealresult->sr_count;

          //to find the cound of anonymous closed match of this particular image
          $count_anonyquery = $this->db->query("SELECT count(*) as sa_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='0' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid' ORDER BY `tb_match`.`matchid` DESC");
          $scountanonyresult = $count_anonyquery->row();
          $a_count = $scountanonyresult->sa_count;

          //to find how many wins with this image for a closed match
          $wincount_query = $this->db->query(" SELECT *,tb_match.matchid as id from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' ");
          $resultwin = $wincount_query->result_array();
          $win = 0;
          $loss = 0;
          $winner_name = array();
          $loser_name = array();

          foreach ($resultwin as $s) {
            $mid = $s['id'];
            $created_at = $s['created_at'];
            $matchquery = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `matchid` ='$mid'");
            $match_result = $matchquery->result_array();
            $picarray = array();
            foreach ($match_result as $sm) {
              $first_imageftype = $sm['filetype'];
              if (!empty($sm['filename'])) {
                if ($first_imageftype == 'file') {
                  $first_image =  base_url() . 'uploads/Matchuploads/' . $sm['filename'];
                } else {
                  $first_image = $sm['filename'];
                }
                $picarray[] = array(
                  'closed_image' => $first_image
                );
              } else {
                $picarray[] = array(
                  'closed_image' => " "
                );
              }
            }
            $id = $s['rival_id'];
            $oppid = $s['opponent_id']; //user listing the personal match will always be opponent
            $match_seenstatus = $s['seen_status'];
            //to select the name of the winner and loser
            $rivaldetails_query = $this->db->query("SELECT * FROM `tb_user` where id='$id'");
            $rivaldetails = $rivaldetails_query->row();
            $rivalname = $rivaldetails->name;
            //
            $rivallike_query = $this->db->query("SELECT count(*) as ri_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$id'");
            $rivallike = $rivallike_query->row();
            $rlike = $rivallike->ri_like;
            $oppolike_query = $this->db->query("SELECT count(*) as op_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$oppid'");
            $oppolike = $oppolike_query->row();
            $olike = $oppolike->op_like;
            $totalmatchlike = $rlike + $olike;
            if (($rlike == 0) && ($olike == 0)) {
              $win = $win + 0;
              $loss = $loss + 0;
              $winner_name[] = array(
                'winner_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );

              $loser_name[] = array(
                'loser_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );
            } else if ($rlike > $olike) {
              //rival win, then this match is lost for the personal match listing user
              $win = $win + 0;
              $loss = $loss + 1;
              if ($match_seenstatus == 1) //getting only revealed names
              {
                $winner_name[] = array(
                  'winner_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $totalmatchlike,
                  'created_at' => $created_at
                );
              }
            } else {
              //oppo win,then this match is won for the personal match listing user
              $win = $swin + 1;
              $loss = $sloss + 0;
              if ($match_seenstatus == 1) {
                $loser_name[] = array(
                  'loser_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $stotalmatchlike,
                  'created_at' => $created_at
                );
              }
            }
          }
          $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE image_id='$media_id' and matchid='$matchid' and like_status='like'")->row(); //likes of rival

          $like = $likeoutput->sender_like;

          if ($media_type == 'file') {
            $file_extention = $item['filename'];
            $exresult = explode(".", $file_extention);
            $extension = $exresult[1];
            if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
              //sfiletype is audio
              $filetype = "audio";
            } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
              //sfiletype is image
              $filetype = "image";
            } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
              //sfiletype is video
              $filetype = "video";
            }
          }else{
            $filetype = $media_type;
          }
          $temp = array(
            'media' => $media,
            'media_id' => $media_id,
            'media_type' => $filetype,
            'media_closedstatus' => $image_status,
            'reveal_count' => $r_count,
            'anonymous_count' => $a_count,
            'media_win' => $win,
            'media_loss' => $loss,
            'media_winname' => $winner_name,
            'media_lossname' => $loser_name,
            'likecount' => $like,
            'sub_caption'=>$sub_caption
          );
          $total_like = $total_like+ $like;
          array_push($medias, $temp);
        }
      }
      $sender['medias'] = $medias;
      $sender['total_like'] = $total_like;

      $oppouid = $result['opponent_id']; //opponent
      $total_like = 0;
      $medias = array();
      $matchoppofilequery = $this->db->query("select * from tb_matchupload WHERE user_uploaded='$oppouid' and matchid=$matchid"); //rival
      $output = $this->db->query("select * from tb_user WHERE id='$oppouid'")->row(); //rival
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          $pic = $output->profile_pic;
        } else {
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $total_like = 0;
      $receiver['user'] = $output;
      $receiver['pic'] = $pic;
      if ($matchoppofilequery->num_rows() > 0) {
        $up_files = $matchoppofilequery->result_array();
        foreach($up_files as $item){
          $media_id = $item['mup_id'];
          $media_type = $item['filetype'];
          $sub_caption = $item['sub_caption'];
          if ($media_type == 'file') {
            $media = base_url() . 'uploads/Matchuploads/' . $item['filename'];
          } else {
            $media = $item['filename'];
          }
          //to find if this image is used for any type of closed match
          $check_query = $this->db->query("SELECT * from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          if ($check_query->num_rows() > 0) {
            $image_status = 1;
          } else {
            $image_status = 0;
          }

          //to find the cound of revealed closed match of this particular image
          $count_revealquery = $this->db->query("SELECT count(*) as sr_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='1' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid'");
          $countrevealresult = $count_revealquery->row();
          $r_count = $countrevealresult->sr_count;

          //to find the cound of anonymous closed match of this particular image
          $count_anonyquery = $this->db->query("SELECT count(*) as sa_count from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_match.seen_status='0' and tb_matchupload.old_mupid='$media_id' and tb_matchupload.personal_matchid='$matchid' ORDER BY `tb_match`.`matchid` DESC");
          $scountanonyresult = $count_anonyquery->row();
          $a_count = $scountanonyresult->sa_count;

          //to find how many wins with this image for a closed match
          $wincount_query = $this->db->query(" SELECT *,tb_match.matchid as id from tb_match join tb_matchupload on tb_match.matchid=tb_matchupload.matchid WHERE tb_match.match_type='closed' and tb_matchupload.old_mupid='$media_id' ");
          $resultwin = $wincount_query->result_array();
          $win = 0;
          $loss = 0;
          $winner_name = array();
          $loser_name = array();

          foreach ($resultwin as $s) {
            $mid = $s['id'];
            $created_at = $s['created_at'];
            $matchquery = $this->db->query("SELECT * FROM `tb_matchupload` WHERE `matchid` ='$mid'");
            $match_result = $matchquery->result_array();
            $picarray = array();
            foreach ($match_result as $sm) {
              $first_imageftype = $sm['filetype'];
              if (!empty($sm['filename'])) {
                if ($first_imageftype == 'file') {
                  $first_image =  base_url() . 'uploads/Matchuploads/' . $sm['filename'];
                } else {
                  $first_image = $sm['filename'];
                }
                $picarray[] = array(
                  'closed_image' => $first_image
                );
              } else {
                $picarray[] = array(
                  'closed_image' => " "
                );
              }
            }
            $id = $s['rival_id'];
            $oppid = $s['opponent_id']; //user listing the personal match will always be opponent
            $match_seenstatus = $s['seen_status'];
            //to select the name of the winner and loser
            $rivaldetails_query = $this->db->query("SELECT * FROM `tb_user` where id='$id'");
            $rivaldetails = $rivaldetails_query->row();
            $rivalname = $rivaldetails->name;
            //
            $rivallike_query = $this->db->query("SELECT count(*) as ri_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$id'");
            $rivallike = $rivallike_query->row();
            $rlike = $rivallike->ri_like;
            $oppolike_query = $this->db->query("SELECT count(*) as op_like FROM `tb_like` WHERE `matchid` = '$mid' AND `contestent_id` = '$oppid'");
            $oppolike = $oppolike_query->row();
            $olike = $oppolike->op_like;
            $totalmatchlike = $rlike + $olike;
            if (($rlike == 0) && ($olike == 0)) {
              $win = $win + 0;
              $loss = $loss + 0;
              $winner_name[] = array(
                'winner_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );

              $loser_name[] = array(
                'loser_name' => "",
                'closed_match_images' => " ",
                'closed_match_likes' => "",
                'created_at' => ""
              );
            } else if ($rlike > $olike) {
              //rival win, then this match is lost for the personal match listing user
              $win = $win + 0;
              $loss = $loss + 1;
              if ($match_seenstatus == 1) //getting only revealed names
              {
                $winner_name[] = array(
                  'winner_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $totalmatchlike,
                  'created_at' => $created_at
                );
              }
            } else {
              //oppo win,then this match is won for the personal match listing user
              $win = $swin + 1;
              $loss = $sloss + 0;
              if ($match_seenstatus == 1) {
                $loser_name[] = array(
                  'loser_name' => $rivalname,
                  'closed_match_images' => $picarray,
                  'closed_match_likes' => $olike . '/' . $stotalmatchlike,
                  'created_at' => $created_at
                );
              }
            }
          }
          $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE image_id='$media_id' and matchid='$matchid' and like_status='like'")->row(); //likes of rival

          $like = $likeoutput->sender_like;
          if ($media_type == 'file') {
            $file_extention = $item['filename'];
            $exresult = explode(".", $file_extention);
            $extension = $exresult[1];
            if (($extension == 'mp3') || ($extension == 'amr') || ($extension == 'wav') || ($extension == 'wma') || ($extension == 'aac') || ($extension == 'ogg') || ($extension == 'aiff') || ($extension == 'aif')) {
              //sfiletype is audio
              $filetype = "audio";
            } elseif (($extension == 'png') || ($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'gif')) {
              //sfiletype is image
              $filetype = "image";
            } elseif (($extension == 'mp4') || ($extension == 'mov') || ($extension == 'wmv') || ($extension == 'flv') || ($extension == 'aiv') || ($extension == 'mkv')) {
              //sfiletype is video
              $filetype = "video";
            }
          }else{
            $filetype = $media_type;
          }
          $temp = array(
            'media' => $media,
            'media_id' => $media_id,
            'media_type' => $filetype,
            'media_closedstatus' => $image_status,
            'reveal_count' => $r_count,
            'anonymous_count' => $a_count,
            'media_win' => $win,
            'media_loss' => $loss,
            'media_winname' => $winner_name,
            'media_lossname' => $loser_name,
            'likecount' => $like,
            'sub_caption'=>$sub_caption
          );
          $total_like = $total_like+ $like;
          array_push($medias, $temp);
        }
        $receiver['total_like'] = $total_like;
        $receiver['medias'] = $medias;
        $compare_result = array($sender,$receiver);
      }
      $likeoutput = $this->db->query("select count(*) as sender_like from tb_like WHERE contestent_id='$uid' and matchid='$matchid'")->row();
      $sender_like = $likeoutput->sender_like;
      
      $likeoppoutput = $this->db->query("select count(*) as receiver_like from tb_like WHERE contestent_id='$oppouid' and matchid='$matchid'")->row();
      $receiver_like = $likeoppoutput->receiver_like;

      
      $userlike = $this->db->query("select contestent_id from tb_like WHERE user_liked='$user_id' and matchid='$matchid'");
      if ($userlike->num_rows() > 0) {
        // echo 'yes';
        $userlikes = $userlike->row();
        // print_r($userlikes);
        $contid = $userlikes->contestent_id;
        if ($contid == $uid) {
          // echo "rival match";
          $userrivallike = '1';
          $useroppolike = '0';
        }
        if ($contid == $oppouid) {
          // echo "oppo match";
          $userrivallike = '0';
          $useroppolike = '1';
        }
        if ($contid == 0) {
          // echo "open like ";
          $userrivallike = '0';
          $useroppolike = '0';
        }
      } else {
        $userrivallike = '0';
        $useroppolike = '0';
      }
      $cmnt_count = $this->db->query("select count(*) as total_cmnt from tb_comment WHERE match_id='$matchid'");
      $cmntcount = $cmnt_count->row();
      $current_time = date('H:i:s');
      $stime = $result['replied_at'];
      $start_date = new DateTime($stime);
      $since_start = $start_date->diff(new DateTime($current_time));
      $send_duration_h = $since_start->h;
      $send_duration_i = $since_start->i;
      $send_duration_s = $since_start->s;
      if ($send_duration_h == 0) { //hour is 0
        if ($send_duration_i == 0) { //minute is 0
          $sduration = $send_duration_s . ' seconds';
        } else {
          $sduration = $send_duration_i . ' minutes';
        }
      } else {
        $sduration = $send_duration_h . ' hours';
      }
      $etime = $result['match_end'];
      $end_date = new DateTime($etime);
      $since_end = $end_date->diff(new DateTime($current_time));
      $remaining_duration_h = $since_end->h;
      $remaining_duration_i = $since_end->i;
      $remaining_duration_s = $since_end->s;
      if ($remaining_duration_h == 0) { //hour is 0
        if ($remaining_duration_i == 0) { //minute is 0
          $rduration = $remaining_duration_s . ' seconds';
        } else {
          $rduration = $remaining_duration_i . ' minutes';
        }
      } else {
        $rduration = $remaining_duration_h . ' hours';
      }
      if (!empty($sfileid)) {
        $sfileid = $sfileid;
      } else {
        $sfileid = 0;
      }
      if (!empty($ofileid)) {
        $ofileid = $ofileid;
      } else {
        $ofileid = 0;
      }
      $data[] =  array(
        'match_id'        => $result['matchid'],
        'description'       => $result['description'],
        'time_duration'        => $result['time_duration'],
        'caption'      => $result['caption'],
        'category'       => $result['category'],
        'match_start' => $result['replied_at'],
        'match_end' => $result['match_end'],
        'match_invitationsend' => $result['created_at'],
        'compare_data' => $compare_result,
        'match_status' => '1',
      );
    }
    return $data;
  }
 
  public function personallike()
  {
    $uid = $this->input->post('userid');
    $img_id = $this->input->post('image_liked');
    $mid = $this->input->post('matchid');
    $select = $this->db->query("SELECT * FROM `tb_like` WHERE `user_liked` = '$uid' AND `matchid` = '$mid' and `image_id`='$img_id'");
    if ($this->db->affected_rows() > 0) {
      $checklike = $this->db->query("SELECT * FROM `tb_like` WHERE `user_liked` = '$uid' AND `matchid` = '$mid' and `image_id`='$img_id' AND `like_status` LIKE 'like'");
      if ($this->db->affected_rows() > 0) {
        $check = $this->db->query("update tb_like set like_status='dislike' WHERE user_liked='$uid' AND `matchid` = '$mid' and `image_id`='$img_id'");
        if ($this->db->affected_rows() > 0) {
          return "dislike";
        } else {
          return "fail";
        }
      } else {
        $check = $this->db->query("update tb_like set like_status='like' WHERE user_liked='$uid' AND `matchid` = '$mid' and `image_id`='$img_id'");
        if ($this->db->affected_rows() > 0) {
          return "like";
        } else {
          return "fail";
        }
      }
    } else {
      // insert
      $data = array(
        'user_liked' => $uid,
        'matchid' => $mid,
        'image_id' => $img_id,
        'like_status' => 'like'
      );
      $this->db->insert('tb_like', $data);
      if ($this->db->affected_rows() > 0) {
        return "like";
      } else {
        return "fail";
      }
    }
  }
  public function Bestielike()
  {
    $bid = $this->input->post('bestie_id');
    $uid = $this->input->post('userid');
    $valid = $this->db->query("select * from tb_bestie WHERE bestie_id='$bid' ");
    if ($this->db->affected_rows() > 0) {
      $check = $this->db->query("select * from tb_bestielike WHERE bestie_id='$bid' and user_liked='$uid'");
      if ($this->db->affected_rows() > 0) {
        $result = $this->db->query("select * from tb_bestielike WHERE bestie_id='$bid' and user_liked='$uid' and like_status='like'");
        if ($this->db->affected_rows() > 0) {
          //dislike
          $update = $this->db->query("update tb_bestielike set like_status='dislike' WHERE bestie_id='$bid' and user_liked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "successful";
          } else {
            return "fail";
          }
        } else {
          //like
          $update = $this->db->query("update tb_bestielike set like_status='like' WHERE bestie_id='$bid' and user_liked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        }
      } else {
        $data = array(
          'bestie_id' => $this->input->post('bestie_id'),
          'user_liked' => $this->input->post('userid'),
          'like_status' => 'like',
        );
        $this->db->insert('tb_bestielike', $data);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function BestieComment()
  {
    date_default_timezone_set('Asia/Kolkata');
    $current_date    = date('Y-m-d H:i:s');
    $bid = $this->input->post('bestie_id');
    $cid = $this->input->post('commentid');
    $result = $this->db->query("select * from tb_bestie WHERE bestie_id='$bid'");
    if ($this->db->affected_rows() > 0) {
      if (empty($cid)) {
        //new comment
        $datas = array(
          'userid' => $this->input->post('userid'),
          'bestie_id' => $this->input->post('bestie_id'),
          'comment' => $this->input->post('comment'),
          'commented_at' => $current_date
        );
        $this->db->insert('tb_bestiecomment', $datas);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      } else {
        // replied comment
        $result = $this->db->query("select * from tb_bestiecomment WHERE id='$cid'");
        if ($this->db->affected_rows() > 0) {
          $datas = array(
            'userid' => $this->input->post('userid'),
            'bestie_id' => $this->input->post('bestie_id'),
            'comment' => $this->input->post('comment'),
            'replied_commentid' => $cid,
            'commented_at' => $current_date
          );
          $this->db->insert('tb_bestiecomment', $datas);
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        } else {
          // this comment not existing
          return "commentnotexist";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function getbestieComment()
  {
    $bestie_id = $this->input->post('bestie_id');
    $result_query = $this->db->query("SELECT * FROM `tb_bestiecomment` WHERE bestie_id='$bestie_id' and is_active='0' and  replied_commentid='0' ");
    $output = $result_query->result_array();
    // print_r($output);die();
    $data = array();

    foreach ($output as $result) {
      $cid = $result['id'];
      $uid = $result['userid'];
      $resultcommentquery = $this->db->query("SELECT count(*) as total_repliedcomment FROM `tb_bestiecomment` WHERE bestie_id='$bestie_id' and is_active='0' and replied_commentid='$cid'");
      $commentarray = $resultcommentquery->row();
      $resultlikequery = $this->db->query("SELECT count(*) as totallike FROM `tb_bestiecommentlike` WHERE commentid='$cid'");
      $likearray = $resultlikequery->row();
      $resultuser = $this->db->query("SELECT * FROM `tb_user` WHERE id='$uid'");
      $output = $resultuser->row();
      if (!empty($output->profile_pic)) {
        $string = $output->profile_pic;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
          // one or more of the 'special characters' found in $string
          // echo "found";
          $pic = $output->profile_pic;
        } else {
          // echo "not found";
          $pic = base_url() . 'uploads/profile_image/' . $output->profile_pic;
        }
      } else {
        // echo "not found";
        $pic = base_url() . 'uploads/profile_image/user.png';
      }
      $repliedcommentarray = array();
      $resultreply_query = $this->db->query("SELECT * FROM `tb_bestiecomment` WHERE bestie_id='$bestie_id' and is_active='0' and  replied_commentid='$cid'");
      $outputreply = $resultreply_query->result_array();
      foreach ($outputreply as $results) {
        $rcid = $results['id'];
        $ruid = $results['userid'];
        $resultreplylikequery = $this->db->query("SELECT count(*) as totallike FROM `tb_bestiecommentlike` WHERE commentid='$rcid'");
        $replylikearray = $resultreplylikequery->row();
        $result_user = $this->db->query("SELECT * FROM `tb_user` WHERE id='$ruid'");
        $outputreply = $result_user->row();
        if (!empty($outputreply->profile_pic)) {
          $string = $outputreply->profile_pic;
          if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            // one or more of the 'special characters' found in $string
            // echo "found";
            $replypic = $outputreply->profile_pic;
          } else {
            // echo "not found";
            $replypic = base_url() . 'uploads/profile_image/' . $outputreply->profile_pic;
          }
        } else {
          // echo "not found";
          $replypic = base_url() . 'uploads/profile_image/user.png';
        }
        $re_cid = $results['id'];
        $re_repliedcommentarray = array();
        $re_resultreply_query = $this->db->query("SELECT * FROM `tb_bestiecomment` WHERE bestie_id='$bestie_id' and is_active='0' and  replied_commentid='$re_cid'");
        $re_outputreply = $re_resultreply_query->result_array();
        foreach ($re_outputreply as $re_results) {
          $re_rcid = $re_results['id'];
          $re_ruid = $re_results['userid'];
          $re_resultreplylikequery = $this->db->query("SELECT count(*) as totallike FROM `tb_bestiecommentlike` WHERE commentid='$re_rcid'");
          $re_replylikearray = $re_resultreplylikequery->row();
          $re_result_user = $this->db->query("SELECT * FROM `tb_user` WHERE id='$re_ruid'");
          $re_outputreply = $re_result_user->row();
          if (!empty($re_outputreply->profile_pic)) {
            $restring = $re_outputreply->profile_pic;
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $restring)) {
              // one or more of the 'special characters' found in $string
              // echo "found";
              $re_replypic = $re_outputreply->profile_pic;
            } else {
              // echo "not found";
              $re_replypic = base_url() . 'uploads/profile_image/' . $re_outputreply->profile_pic;
            }
          } else {
            // echo "not found";
            $re_replypic = base_url() . 'uploads/profile_image/user.png';
          }
          $re_repliedcommentarray[] =  array(
            'id'        => $re_results['id'],
            'commented_userid'      => $re_results['userid'],
            'commented_username'      => $re_outputreply->name,
            'commented_userprofile'      => $re_replypic,
            'comment'       => $re_results['comment'],
            'commented_at'       => $re_results['commented_at'],
            'total_like_count'       => $re_replylikearray->totallike,
          );
        }
        $repliedcommentarray[] =  array(
          'id'        => $results['id'],
          'commented_userid'      => $results['userid'],
          'commented_username'      => $outputreply->name,
          'commented_userprofile'      => $replypic,
          'comment'       => $results['comment'],
          'commented_at'       => $results['commented_at'],
          'total_like_count'       => $replylikearray->totallike,
          're_replied_comments' => $re_repliedcommentarray,
        );
      }
      $data[] =  array(
        'id'        => $result['id'],
        'commented_userid'      => $result['userid'],
        'commented_username'      => $output->name,
        'commented_userprofile'      => $pic,
        'comment'       => $result['comment'],
        'commented_at'       => $result['commented_at'],
        'total_repliedcomment_count'       => $commentarray->total_repliedcomment,
        'total_like_count'       => $likearray->totallike,
        'replied_comments' => $repliedcommentarray,

      );
    }
    // print_r($data);die();
    return $data;
  }
  public function DeleteBestieComment()
  {
    $commentid = $this->input->post('commentid');
    $check = $this->db->query("delete from tb_bestiecomment WHERE id='$commentid'");
    if ($this->db->affected_rows() > 0) {
      return "success";
    } else {
      return "fail";
    }
  }
  public function BestieCommentLike()
  {
    $cid = $this->input->post('commentid');
    $uid = $this->input->post('userid');
    $valid = $this->db->query("select * from tb_bestiecomment WHERE id='$cid' ");
    if ($this->db->affected_rows() > 0) {
      $check = $this->db->query("select * from tb_bestiecommentlike WHERE commentid='$cid' and userliked='$uid'");
      if ($this->db->affected_rows() > 0) {
        $result = $this->db->query("select * from tb_bestiecommentlike WHERE commentid='$cid' and userliked='$uid' and likestatus='like'");
        if ($this->db->affected_rows() > 0) {
          //dislike
          $update = $this->db->query("update tb_bestiecommentlike set likestatus='dislike' WHERE commentid='$cid' and userliked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "successful";
          } else {
            return "fail";
          }
        } else {
          //like
          $update = $this->db->query("update tb_bestiecommentlike set likestatus='like' WHERE commentid='$cid' and userliked='$uid'");
          if ($this->db->affected_rows() > 0) {
            return "success";
          } else {
            return "fail";
          }
        }
      } else {
        $data = array(
          'commentid' => $this->input->post('commentid'),
          'userliked' => $this->input->post('userid'),
          'likestatus' => 'like',
        );
        $this->db->insert('tb_bestiecommentlike', $data);
        if ($this->db->affected_rows() > 0) {
          return "success";
        } else {
          return "fail";
        }
      }
    } else {
      return "notexist";
    }
  }
  public function UpdateBestieComment()
  {
    $cid = $this->input->post('commentid');
    $comment = $this->input->post('comment');
    $valid = $this->db->query("select * from tb_bestiecomment WHERE id='$cid' ");
    if ($this->db->affected_rows() > 0) {
      $update = $this->db->query("update tb_bestiecomment set comment='$comment' WHERE id='$cid'");
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    } else {
      return "notexist";
    }
  }
  public function gettotalcommentcount()
  {
    $bestie_id = $this->input->post('senderid');
    $resultcommentquery = $this->db->query("SELECT count(*) as total_comment FROM `tb_bestiecomment` WHERE bestie_id='$bestie_id' and is_active='0'");
    $commentarray = $resultcommentquery->row();
    return $commentarray->total_comment;
  }
  public function gettotallikecount()
  {
    $bestie_id = $this->input->post('senderid');
    $resultlikequery = $this->db->query("SELECT count(*) as total_like FROM `tb_bestielike` WHERE bestie_id='$bestie_id' and like_status='like'");
    $likearray = $resultlikequery->row();
    return $likearray->total_like;
  }
  public function disable_comment()
  {
    $uid = $this->input->post('userid');
    // $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$uid'");
    // if ($this->db->affected_rows() > 0) {
    $result_query = $this->db->query("SELECT * FROM `tb_statusbar` WHERE userid='$uid' and disable_status='0'");
    if ($this->db->affected_rows() > 0) { //it is disabled
      $update = $this->db->query("update tb_statusbar set disable_status='1' WHERE userid='$uid'");
      if ($this->db->affected_rows() > 0) {
        return "successful";
      } else {
        return "fail";
      }
    } else { //it is enabled
      $update = $this->db->query("update tb_statusbar set disable_status='0' WHERE userid='$uid'");
      if ($this->db->affected_rows() > 0) {
        return "success";
      } else {
        return "fail";
      }
    }
    // } else {
    //   return "notexist";
    // }
  }
}
