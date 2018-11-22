<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Payment_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'payment_transactions_update';
  }
  
  function listing()
  {  
    $this->_fields="a.id as id,a.profile_id,a.profile_start_date,a.paid_amount,a.profile_status,a.subscription_type,CONCAT(b.first_name,' ',b.last_name) as customer_name,a.next_due_date";
    $this->db->from("payment_transactions_update a");
    $this->db->join("admin_users b",'a.user_id=b.id');
    $this->db->group_by('a.id');
   $this->db->order_by('a.id','desc');

    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'a.profile_id':
          $this->db->like($key, $value);
        break;
        case 'a.profile_start_date':
          $this->db->like($key, $value);
        break;
        case 'a.paid_amount':
          $this->db->like($key, $value);
        break;
        case 'a.profile_status':
          $this->db->like($key, $value);
        break;
        case 'a.subscription_type':
          $this->db->like($key, $value);
        break;
        case 'b.first_name':
          $this->db->like($key, $value);
        break;
      }
    }
    return parent::listing();
  }
  function summary()
  {
    if($this->session->userdata('summary_id'))
      $user_id = $this->session->userdata('summary_id');
    else
      $user_id = get_user_data()['id'];
    $this->_fields = "*";
    $this->db->where("user_id",$user_id);
    $this->db->get("payment_history");
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
  }
    
}
?>