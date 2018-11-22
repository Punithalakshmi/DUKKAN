<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(COREPATH.'libraries/models/App_model.php');
class Login_Model extends App_model
{
   
  function __construct()
  {
    parent::__construct();
  }
  public function login($email='', $password='',$type='desktop')
  {
    $res = array('status'=>'success');
    $pass = md5($password);
    $this->db->select("*");
    $this->db->from("admin_users");
    $this->db->where('email', $email);
    $this->db->or_where('username', $email);
    $this->db->where('password', $pass);
    $user = $this->db->get()->row_array();       
    if(count($user)>0)
    {
      if($user['active']=="N")
      {
        $res['status'] = "error";
        $res['msg'] = "Your account is not yet activated or blocked due to payment failure.";
      }
      else if($user['active']=="Y")
      {
        $chk_session_login = get_user_session_login($user['id'],$type);
        if( !$chk_session_login )
        {
          $res['status'] = "error";
          $res['msg'] = 'User already logged in. Please signout all sessions and try again.';
        }
      }
    }
    else
    {
      $res['status'] = "error";
      $res['msg'] = "Invalid Username or Password.";
    }
    if($res['status'] === 'success')
      $this->session->set_userdata('user_data', $user);
    return $res;
  }
   public function logout()
   {
        $this->session->sess_destroy();
   }

   public function update($where=array(),$data='',$table='')
   {
      $this->db->where($where);
      $this->db->update($table,$data);
   }
    
}

?>