<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Reports_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table='';
  }
  function listing()
  {  
    
    $this->_fields = "a.*,CONCAT(a.project_address1,' ',a.project_address2,' ',a.project_city,' ',a.project_state,' ',a.project_zip_code) as project_address,b.first_name as manager,GROUP_CONCAT(DISTINCT e.first_name order by e.id SEPARATOR ', ') as contractor,GROUP_CONCAT(DISTINCT c.first_name) as super";
    $this->db->from("project a");
    $this->db->join("admin_users b","a.manager=b.id AND b.role='3'");
    $this->db->join("admin_users c","FIND_IN_SET(CAST(c.id AS CHAR),a.superintendent) > 0");
    $this->db->join("project_contractors d","a.id=d.project_id");
    $this->db->join("admin_users e","FIND_IN_SET(CAST(e.id AS CHAR),d.contractor_id)>0 AND e.role='5'");
    $this->db->group_by('a.id');

    $user = get_user_data();
    if($user['role']=="2")
      $this->db->where("a.company_id",$user['id']);
    if($user['role']=="3")
      $this->db->where("a.manager",$user['id']);

    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'project_name':
          $this->db->like("a.id", $value);
        break;
        case 'manager':
          $this->db->like("a.manager", $value);
        break;
        case 'super':
          $this->db->like("a.superintendent", $value);
        break;
        case 'status':
          $this->db->like("a.status", $value);
        break;
        case 'start_date':
          $this->db->like("a.start_date", date("Y-m-d",strtotime($value)));
        break;
        case 'status':
          $this->db->like("a.complete_date", date("Y-m-d",strtotime($value)));
        break;
        case 'contractor':
          $this->db->like("d.contractor_id",$value);
        break;
        
      }
    }
    return parent::listing();
  }
  public function get_projects($where=array())
  {
    if($where)
      $this->db->where($where);
    $this->db->select("*");
    $this->db->from("project");
    $this->db->group_by('id');
    $q = $this->db->get();
    return $q->row_array();
  }
  public function get_punchlist($where=array())
  {
    if($where)
      $this->db->where($where);
    $this->db->select("a.*,b.room_no as roomno,b.room_name");
    $this->db->from("punchlist a");
    $this->db->join("rooms b","a.room_no=b.id",'left');
    $this->db->order_by("b.room_no","asc");
    $q = $this->db->get();
    return $q->result_array();
  }
}
?>