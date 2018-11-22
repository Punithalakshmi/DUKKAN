<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Manager_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'admin_users';
  }
  
  function listing()
  {  
    $user = get_user_data();
    $id = $user['id'];
    $this->_fields = "*,IF(active='Y','Active','Inactive') as status";
    $this->db->where('role',3);
    $this->db->group_by('id');
    if($user['role']=="2")
      $this->db->where("company_id",$id);
    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'first_name':
          $this->db->like($key, $value);
        break;
        case 'email1':
          $this->db->like($key, $value);
        break;
        case 'office_phone':
          $this->db->like($key, $value);
        break;
        // case 'active':
        //   $this->db->like($key, $value);
        // break;
      }
    }
    return parent::listing();
  }
  public function check_username($email='',$id='')
  {
    $this->db->where("email",$email);
    $this->db->or_where("username",$email);
    if($id)
      $this->db->where("id!=",$id);
    $q = $this->db->get("admin_users");
    return $q->row_array();
  }

}
?>