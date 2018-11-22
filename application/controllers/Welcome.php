<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH."controllers/Admin_controller.php");

class Welcome extends Admin_Controller 
{

	 
	public function index()
	{
		$this->load->view('welcome_message');
	}
}
