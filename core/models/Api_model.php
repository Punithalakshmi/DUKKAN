<?php

require_once(COREPATH."libraries/models/App_model.php");

class Api_model extends App_Model {
    
    
    function __construct()
    {
        parent::__construct();
       
    }
   
	function login($name = "",$password = "")
	{
		
		$this->db->select("id,IF(role='2',id,company_id) as company_id,first_name,last_name,company_name, email,role,active,phone,address1,address2,city,state,zip,trial_user,expiry_date",FALSE);

		$this->db->from('admin_users');

        $this->db->where('email', $name);

		$this->db->or_where('username',$name);

		if($password)
			$this->db->where('password',md5($password));

		return $this->db->get()->row_array();

	}

	function get_projects_by_role($id='',$role='',$company_id='')
	{
		$this->db->select("p.id,p.project_no,p.project_name,p.blueprint,p.status,CONCAT(project_address1,' ',project_address2,',',project_city,',',project_state,',',project_zip_code) as project_address,CONCAT(c.first_name,' ',c.last_name,',',c.address,',',c.city,',',c.state,'-',c.zip,', ph-',c.phone) as client_address,p.complete_date,p.project_state,p.project_zip_code",FALSE);

		$this->db->from('project p');
		$this->db->join('client_contacts c','c.id=p.client_contact1');

		if(!empty($company_id))
			$this->db->where('p.company_id',$company_id);

		if($role=='3')
			$this->db->where('p.manager',$id);
		if($role=='4')
			$this->db->where("FIND_IN_SET('$id',p.superintendent) !=",0);

		if($role=='5'){
			$this->db->join('project_contractors pc','p.id=pc.project_id');
			$this->db->where("FIND_IN_SET('$id',pc.contractor_id) !=",0);
		}

		$this->db->group_by("p.id");

		return $this->db->get()->result_array();

	}

	
	function get_project_contractors($project_id)
	{	
		$this->db->select("c.id,CONCAT(c.first_name,' ',c.last_name) as name,company_name",FALSE);
		$this->db->from('admin_users c');
		$this->db->join('project_contractors p',"FIND_IN_SET(CAST(c.id AS CHAR),p.contractor_id) >0"); 
		$this->db->where('c.role','5');
		$this->db->where('p.project_id',$project_id);

		$this->db->group_by("c.id");

		return $this->db->get()->result_array();
		
	}

	function get_managers($id="",$role='',$company_id='')
	{	
		$this->db->select("*",FALSE);
		$this->db->from('admin_users'); 
		$this->db->where("role",'3');
		$this->db->where("company_id",$company_id);
		$this->db->group_by("id");
		return $this->db->get()->result_array();
	}

	function get_superintendent($id="",$role='',$company_id='')
	{	
		$this->db->select("u.*",FALSE);
		$this->db->from('admin_users u'); 
		
		if($role == '3'){
			$this->db->join('project p',"FIND_IN_SET(CAST(u.id AS CHAR),p.superintendent) >0");
		 	$this->db->where("p.manager",$id);
		}

		$this->db->where("u.role",'4');
		$this->db->where("u.company_id",$company_id);

		$this->db->group_by("u.id");

		return $this->db->get()->result_array();
		 
	}

	function get_contractors($id=0,$role='',$company_id='')
	{	
		$this->db->select("c.*");
		$this->db->from('admin_users c'); 

		if($role == '3'){
			$this->db->join('project_contractors pc','FIND_IN_SET(CAST(c.id AS CHAR),pc.contractor_id) >0');
			$this->db->join('project p','p.id=pc.project_id');
			$this->db->where('p.manager',$id);
		}

		if($role == '4'){
			$this->db->join('project_contractors pc','FIND_IN_SET(CAST(c.id AS CHAR),pc.contractor_id) >0');
			$this->db->join('project p','p.id=pc.project_id');
			$this->db->where("FIND_IN_SET('$id',p.superintendent) !=",0);
		}

		$this->db->where("c.role",'5');
		$this->db->where('(c.company_id="'.$company_id.'" OR c.is_single_user="Y")',NULL,FALSE);
		//$this->db->where("c.company_id",$company_id);

		$this->db->group_by("c.id");

		return $this->db->get()->result_array();
		
	}

	function assigned_managers($id='')
	{
		$q = $this->db->query("select b.id as manager_id,CONCAT(b.first_name,' ',b.last_name) as name,b.phone,b.email,b.address1,b.address2,b.state,b.city,b.zip from project a,admin_users b where a.manager=b.id and a.id=".$id."");

		return $q->result_array();
	}
	function assigned_superintendent($id='')
	{
		$q = $this->db->query("select b.id as manager_id,CONCAT(b.first_name,' ',b.last_name) as name,b.phone,b.email,b.address1,b.address2,b.state,b.city,b.zip from project a,admin_users b where a.superintendent=b.id and a.id=".$id."");

		return $q->result_array();
	}

	function getpunchlist_history($project_id)
	{
		$this->db->select("r.room_no as room_num,CONCAT(r.room_name,'(',r.room_no,')') as room,c.first_name as contractor_name,p.*,IF(p.item_complete_date='0000-00-00','',p.item_complete_date) as item_complete_date",FALSE);
		$this->db->from('punchlist p');
		$this->db->join('rooms r','r.id=p.room_no');
		$this->db->join('admin_users c','c.id=p.contractor','left');
		$this->db->where('p.project_id',$project_id);
		$this->db->group_by("p.id");
		$this->db->order_by("r.room_no ASC");
		return $this->db->get()->result_array();
	}

}
?>
