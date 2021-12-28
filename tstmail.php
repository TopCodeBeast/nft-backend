<?php 
$email ='raveena.sics@gmail.com';
$to = $email;
$subject1 = "Test Mail Function";
$subject = '=?UTF-8?B?' . base64_encode($subject1) . '?=';
$message = "<b>Dear Client,</b><br>";
$message .= "<p>Thank you for your interest in Knungle!</p>";
$message .= "welcome ";
$message .= "<p>This OTP is to be used for the signup at knungle.com</p><br>";
$message .= "<p>Thank you,</p>";
$message .= "<p>Knungle</p>";
$from = "knungle@gmail.com";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'Return-Path: ' . $from . "\r\n";
$headers .= 'From: Knungle <' . $from . '>' . "\r\n";
if (mail($to, utf8_decode($subject) , $message, $headers, "-f" . $from))
{
$datareturn = array(
'statuss' => 1
);
echo json_encode($datareturn);
}

?>