<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/core/BaseController.php';
class Match extends BaseController
{
    public function __construct()
    {   
        parent::__construct();
        $this->load->model('MatchModel', "model");
    }
    public function getOngoingMatch($id){
        $match = (array)$this->Match->select($id);
        
        $data = array();
        $compare_data = array();
        $user_id = $match['rival_id']; //rival 
        $match_id = $match['matchid'];
        $user = (array)$this->User->select($user_id);

        $user['pic'] = getProfileImage($user);
        $user['like_count'] = $this->Like->count(array('matchid'=>$match_id, 'contestent_id'=>$user_id, 'like_status'=>'like'));
        $user['dislike_count'] = $this->Like->count(array('matchid'=>$match_id, 'contestent_id'=>$user_id, 'like_status'=>'dislike'));

        $mediafiles = $this->Upload->all(array('user_uploaded'=>$user_id,'matchid' => $match_id));
        foreach($mediafiles as $index=> $media){
            $media = (array)$media;
            $filename = $media['filename'];
            $filetype = $media['filetype'];
            if ($filetype == 'file') {
                $file = base_url() . 'uploads/Matchuploads/' . $filename;
                $mediafiles[$index]->media_type = getFileType($media['filename']);
            } else {
                $file = $filename;
                $mediafiles[$index]->media_type = $media['filetype'];
            }
            $mediafiles[$index]->file_path = $file;
        }
        $user['mediafiles'] = $mediafiles;
        array_push($compare_data, $user);

        $opponents = $this->MatchUser->all(array('match_id' => $match_id, 'accept_status'=> 'accept'));
        foreach($opponents as $oppoent){
            $oppoent = (array)$oppoent;
            $user = (array)$this->User->select($oppoent['opponent_id']);

            
            $user['pic'] = getProfileImage($user);
            $match_like_count = $this->Like->count(array('matchid'=>$match_id, 'contestent_id'=>$oppoent['opponent_id'], 'like_status'=>'like'));
            $match_dislike_count = $this->Like->count(array('matchid'=>$match_id, 'contestent_id'=>$oppoent['opponent_id'], 'like_status'=>'dislike'));
            $mediafiles = $this->Upload->all(array('user_uploaded'=>$oppoent['opponent_id'],'matchid' => $match_id));
            foreach($mediafiles as $index=> $media){
                $media = (array)$media;
                $filename = $media['filename'];
                $filetype = $media['filetype'];
                if ($filetype == 'file') {
                    $file = base_url() . 'uploads/Matchuploads/' . $filename;
                    $mediafiles[$index]->media_type = getFileType($media['filename']);
                } else {
                    $file = $filename;
                    $mediafiles[$index]->media_type = $media['filetype'];
                }
                $mediafiles[$index]->file_path = $file;
            }
            $user['mediafiles'] = $mediafiles;
            array_push($compare_data, $user);
        }
        $cmntcount = $this->Comment->count(array('match_id'=>$match_id));
        /* Send duration start */
        $sduration = getDurationTime($match['replied_at']);
        $rduration = getDurationTime($match['match_end']);
        
      
        $match['comment_count'] = $cmntcount;
        $match['compare_data'] = $compare_data;
        $match['send_duration'] = $sduration;
        $match['remaining_time'] = $rduration;
        $this->response(array('success'=>true, "data"=>$match));
    }
}