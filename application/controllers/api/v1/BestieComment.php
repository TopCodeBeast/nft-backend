<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class BestieComment extends BaseController
{
    public function __construct()
    {   
        parent::__construct();
        $this->load->model('BestieCommentModel', "model");
    }

    public function add (){
        $data = $this->input->post();
        $data['commented_at'] = date("Y-m-d H:s:i");
        $result = $this->BestieComment->save($data);
        if($result){
            $post = array(
                "status" => true,
                "msg" => "Successfully Added"
            );
        }else{
            $post = array(
                "status" => false,
                "msg" => "Successfully Added"
            );
        }
        $this->response($post);

    }
    public function all(){
        $filter = $this->input->post();
        $filter["is_active"] = 0;
        $filter["replied_commentid"] = 0;
        $output = $this->BestieComment->all($filter);
        $data = array();

        foreach ($output as $result) {

            $filter["replied_commentid"] = $result->id;
            $commentarray = $this->BestieComment->count($filter);
            $likearray = $this->BestieCommentLike->count(array("commentid"=>$cid));

            $output = $this->User->select($result->userid);
            
            $pic = getProfileImage((array)$output);
            $repliedcommentarray = array();

            $outputreply = $this->BestieComment->all(array("bestie_id"=>$filter['bestie_id'], "is_active"=>0, "replied_commentid"=>$cid));
            foreach ($outputreply as $results) {
                $rcid = $results['id'];
                $ruid = $results['userid'];
                
                $replylikearray = $this->BestieCommentLike->count(array("commentid"=>$rcid));
                
                $outputreply = $this->User->select($ruid);
            
                $replypic = getProfileImage((array)$outputreply);
                
                $re_cid = $results['id'];
                $re_repliedcommentarray = array();
                $re_outputreply = $this->BestieComment->all(array("bestie_id"=>$filter['bestie_id'],"is_active"=>0, "replied_commentid"=>$re_cid));
                foreach ($re_outputreply as $re_results) {
                    $re_rcid = $re_results['id'];
                    $re_ruid = $re_results['userid'];
                    $re_replylikearray = $this->BestieCommentLike->count(array("commentid"=>$re_rcid));;
              
                    $re_outputreply = $this->User->select($re_ruid);
              
                    $re_replypic = getProfileImage((array)$re_outputreply);
              
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
                'total_repliedcomment_count'       => $commentarray,
                'total_like_count'       => $likearray,
                'replied_comments' => $repliedcommentarray,
            );
        }
        $post = array("details"=> $data,"status"=>true);
        $this->response($post);
    }
}