<?php
defined('BASEPATH') OR ('No direct script access allowed');

    function getProfileImage($user){
        if (!empty($user['profile_pic'])) {
            $string = $user['profile_pic'];
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) {
            $pic = $user['profile_pic'];
        } else {
            $pic = base_url() . 'uploads/Avataruploads/' . $user['profile_pic'];
        }
        } else {
            $pic = base_url() . 'uploads/profile_image/user.png';
        }
        return $pic;
    }

    function getDurationTime($time){
        $current_time = date('H:i:s');
        $date = new DateTime($time);
        $since_end = $date->diff(new DateTime($current_time));
        $duration_h = $since_end->h;
        $duration_i = $since_end->i;
        $duration_s = $since_end->s;
        if ($duration_h == 0) { //hour is 0
            if ($duration_i == 0) { //minute is 0
                $duration = $duration_s . ' seconds';
            } else {
                $duration = $duration_i . ' minutes';
            }
        } else {
            $duration = $duration_h . ' hours';
        }
        return $duration;
    }

    function getFileType($filename){
        $s_exresult = explode(".", $filename);
        $s_extension = $s_exresult[1];
        if (($s_extension == 'mp3') || ($s_extension == 'amr') || ($s_extension == 'wav') || ($s_extension == 'wma') || ($s_extension == 'aac') || ($s_extension == 'ogg') || ($s_extension == 'aiff') || ($s_extension == 'aif')) {
            $filetype = "audio";
        } elseif (($s_extension == 'png') || ($s_extension == 'jpg') || ($s_extension == 'jpeg') || ($s_extension == 'gif') || ($s_extension == 'gif')) {
            $filetype = "image";
        } elseif (($s_extension == 'mp4') || ($s_extension == 'mov') || ($s_extension == 'wmv') || ($s_extension == 'flv') || ($s_extension == 'aiv') || ($s_extension == 'mkv')) {
            $filetype = "video";
        }
        return $filetype;
    }
?>