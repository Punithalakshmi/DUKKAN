<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Projects_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'project';
  }
  
  function listing()
  {  
    $this->_fields = "p.*";

    $this->db->from("project p");

    $user = get_user_data();

    $id = $user['id'];
    if($user['role']=="2")
      $this->db->where("p.company_id",$id);
    if($user['role']=="3")
      $this->db->where("p.manager",$id);

    if($user['role']=="4")
      $this->db->where("FIND_IN_SET('$id',p.superintendent) !=",0);

    if($user['role']=='5'){
      $this->db->join('project_contractors pc','p.id=pc.project_id');
      $this->db->where("FIND_IN_SET('$id',pc.contractor_id) !=",0);
    }

    $this->db->group_by('id');

    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'project_no':
          $this->db->like('p.project_no', $value);
        break;
        case 'project_name':
          $this->db->like('p.project_name', $value);
        break;
        case 'complete_date':
          $this->db->like('p.complete_date', $value);
        break;
        case 'status':
          $this->db->like('p.status', $value);
        break;
      }
    }
    return parent::listing();
  }

  function punch_listing($id='')
  {  
    $this->db->where("a.project_id",$id);
    $this->db->select("a.*,b.first_name,b.last_name,b.company_name,c.room_no as roomno,c.room_name");
    $this->db->from("punchlist a");
    $this->db->join("admin_users b","a.contractor=b.id");
    $this->db->join("rooms c","a.room_no=c.id");
    $this->db->order_by("a.room_no","asc");
    $q = $this->db->get();
    return $q->result_array();
  }

  function check_user_project($where='',$project_id='')
  {
    $this->db->where($where);
    $this->db->where("id",$project_id);
    $q = $this->db->get("project");
    return $q->row_array();
  }
  function check_contractor_project($user_id='',$project_id='')
  {
    $this->db->select("a.id");
    $this->db->from("project a");
    $this->db->join("project_contractors b","a.id=b.project_id");
    $this->db->where("b.project_id",$project_id);
    //$this->db->where_in("b.contractor_id",$user_id);
    $this->db->where("FIND_IN_SET('$user_id',b.contractor_id) !=",0);
    $q = $this->db->get();
    return $q->row_array();
  }


  function get_data($table_name,$where,$field_name)
  { 
    
    if(count($where))
    {
      if($field_name!='')
        $this->db->select($field_name); 
          
      $result = $this->db->get_where($table_name,$where);
    }
    else
    {
      if($field_name!='')
        $this->db->select($field_name);

      $result = $this->db->get($table_name);
    }
    return $result;
  }

  function get_cont_list($comp_id){

    $this->db->select("*");
    $this->db->from("admin_users");
    $this->db->where("role",5);
    $this->db->where("active","Y");
    $this->db->where('(company_id="'.$comp_id.'" OR is_single_user="Y")',NULL,FALSE);
    return $this->db->get()->result_array();
  }

  function get_projects($id)
  {
    $this->db->select("a.id as p_id,a.project_no as p_no,a.project_name as p_name,DATE_FORMAT(a.complete_date,'%m/%d/%Y') as p_e_d,a.blueprint as p_b_f,b.id as client_id1,b.first_name,b.last_name,b.email,b.phone,b.address as addr,b.city,b.state,b.country,b.zip as zip_code,a.project_address1 as p_addr1,a.project_address2 as p_addr2,a.project_city as p_city,a.project_state as p_state,a.project_zip_code as p_zip_code,d.contractor_id as a_c,a.manager,a.superintendent,a.company_id");
    $this->db->where("a.id",$id);
    $this->db->from("project a");
    $this->db->join("client_contacts b","a.client_contact1=b.id");
    $this->db->join("project_contractors d","a.id=d.project_id");
    $q = $this->db->get();
    return $q->row_array();
  }

  function get_rooms($id)
  {
    $result = array();$i=0;
    $this->db->where("project_id",$id);
    $q = $this->db->get("rooms");
    foreach ($q->result_array() as $key => $value)
    {
      $result['r_id'][$i] = $value['id'];
      $result['r_name'][$i] = $value['room_name'];
      $result['r_no'][$i] = $value['room_no'];
      $i++;
    }
    return $result;
  }
  public function get_mail_recipients($id='')
  {
    $result = array();$i=0;
    $this->db->where("a.id",$id);
    $this->db->select("a.*,b.email as manager,c.email as superintendent");
    $this->db->from("project a");
    $this->db->join("admin_users b","a.manager=b.id");
    $this->db->join("admin_users c","a.superintendent=c.id");
    $q = $this->db->get();
    foreach ($q->result_array() as $key => $value)
    {
      $result[] = $value['manager'];
      $result[] = $value['superintendent'];
    }
    return $result;
  }
}
?>