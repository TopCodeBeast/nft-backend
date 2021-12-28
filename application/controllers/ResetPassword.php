<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ResetPassword extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel');
		$this->load->database();
		$this->load->helper('url', 'form');
	}
	// 	public function index($mail)
	// 	{
	// 		// echo base64_encode('raveena@gmail.com');
	// $email=base64_decode($mail);
	// echo $email;
	// 		$this->load->view('forget_password');
	// 	}
	public function welcome($mail)
	{
		echo base64_encode('raveenas@gmail.com');
		$data['email'] = base64_decode($mail);
		echo $data['email'];
		$this->load->view('forget_password', $data);
	}
	public function resetpassword()
	{
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		// echo $email,$password;die();
		$user_data = $this->UserModel->forget_password($email, $password);
		// print_r($user_data);
		// die();
		if (!empty($user_data)) {
			if ($user_data == "success") {
				// echo "good";
				$this->load->view('success');
			}
			if ($user_data =="usernotexist") {
				// echo "no such user";
				redirect_back();
			}
			if ($user_data =="fail") {
				echo "failed";
			}
		}
		
	}
	public function success()
	{
	}
}
