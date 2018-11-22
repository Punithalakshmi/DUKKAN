<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Gc_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'admin_users';
  }
  
  function listing()
  {  
    
    $this->_fields = "a.*,IF(a.active='Y','Active','Inactive') as status,b.name as plan";
    $this->db->from('admin_users a');
    $this->db->join('plans b','a.plan_id=b.id');
    $this->db->where('a.role',2);
    $this->db->or_where('(a.role=5 AND a.is_single_user="Y")',NULL,FALSE);
    $this->db->group_by('a.id');
    $this->db->order_by('a.id','desc');
    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'first_name':
          $this->db->like('a'.$key, $value);
        break;
        case 'email1':
          $this->db->like('a'.$key, $value);
        break;
        case 'office_phone':
          $this->db->like('a'.$key, $value);
        break;
        // case 'active':
        //   $this->db->like($key, $value);
        // break;
      }
    }
    return parent::listing();
  }

   function plans_list()
  {
    $this->db->select("*");
    $this->db->from("plans");
    return $this->db->get()->result_array();
  }

}
?>