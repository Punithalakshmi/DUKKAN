<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Profile_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'payment_history';
  }
  function listing()
  {
    $user_id = get_user_data()['id'];
    $this->_fields = "*";
    $this->db->where("user_id",$user_id);
    $this->db->group_by('id');
    $this->db->order_by('id','desc');
    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'profile_id':
          $this->db->like($key, $value);
        break;
        case 'amount':
          $this->db->like($key, $value);
        break;
        case 'trans_id':
          $this->db->like($key, $value);
        break;
        case 'payment_status':
          $this->db->like($key, $value);
        break;
        case 'paid_date':
          $this->db->like($key, $value);
        break;
      }
    }
  return parent::listing();
}
}
?>