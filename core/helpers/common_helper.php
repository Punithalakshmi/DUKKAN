<?php

function is_logged_in()
{
    $CI = get_instance();
    
    $user_data = get_user_data();
    
    if( is_array($user_data) && $user_data )
        return TRUE;

    return FALSE;

}

function get_current_user_id()
{
  $CI = & get_instance();    
  $current_user = get_user_data();    
  if(!empty($current_user))
  {
    return $current_user['id'];
  }
  return FALSE;
}

function get_user_data()
{
  $CI = get_instance();
  if($CI->session->userdata('user_data'))
  {
    return $CI->session->userdata('user_data');
  }
  else
  {
    return FALSE;
  }
}

function get_user_role( $user_id = 0 )
{
  $CI= & get_instance();
  if(!$user_id) 
  {
    $user_data = get_user_data();
    return $user_data['role'];
  }   
  $CI->load->model('user_model');
  $row = $CI->user_model->get_where(array('id' => $user_id))->row_array;

  if( !$row )
    return FALSE;
  return $row['role'];
}

function get_roles()
{
  $CI = & get_instance();
  $CI->load->model('role_model');
  $records = $CI->role_model->get_roles();
  $roles = array();
  foreach ($records as $id => $val) 
  {
    $roles[$id] = $val;
  }
  return $roles;
}

function get_plans()
{
  $CI = & get_instance();
  $CI->load->model('plans_model');
  $records = $CI->plans_model->plans_list();
  $plans = array();
  foreach ($records as $id => $val) 
  {
    $plans[$val['id']] = $val['name'];
  }
  return $plans;
}

function display_flashmsg($flash)
{
  if(!$flash)
    return FALSE;
  $status = $msg = '';
  if(isset($flash['success_msg']))
  {
    $status = 'success';
    $msg = $flash['success_msg'];
  }
  if(isset($flash['error_msg']))
  {
    $status = 'danger';
    $msg = $flash['error_msg'];
  }
  if($status && $msg)
  {
    $str = '<div id="div_service_message" class="Metronic-alerts alert alert-'.$status.' fade in">';
    $str.= '<button class="close" aria-hidden="true" data-dismiss="alert" type="button"><i class="fa-lg fa fa-warning"></i></button>';   
    if($status == 'danger')
      $status = 'error';
    $str.='<strong>'.ucfirst($status).':&nbsp;</strong>'.strip_tags($msg).'</div>';
    echo $str;
  }
}
function displayData($data = null, $type = 'string', $row = array(), $wrap_tag_open = '', $wrap_tag_close = '')
{
  $CI = & get_instance();
  if(is_null($data) || is_array($data) || (strcmp($data, '') === 0 && !count($row)) )
    return $data;
  switch ($type)
  {
    case 'string':
        break;
    case 'uppercase':
      $data = strtoupper($data);
    break;
    case 'humanize':
    $CI->load->helper("inflector");
        $data = humanize($data);
        break;
    case 'date':
        if($data!='0000-00-00')
            $data = str2USDate($data);
        else
            $data="";
        break;
    case 'status':
       $labels_array = array('COMPLETED' => 'label-success','PROCESSING' => 'label-success','CANCELLED' => 'label-danger','HOLD' => 'label-danger','INPROGRESS'=>'label-warning','Active' => 'label-success','InActive'=>'label-danger','Inactive'=>'label-danger','OPEN'=>'label-success','PENDING'=>'label-danger','Completed'=>'label-success','Created'=>"label-info",'Denied'=>'label-danger','Expired'=>'label-danger','Failed'=>'label-danger','Pending'=>'label-warning','Refunded'=>'label-success','Reversed'=>'label-info','Processed'=>'label-warning','Voided'=>'label-danger','Canceled_Reversal'=>'label-warning');
       $data = "<span class='label {$labels_array[$data]}'>{$data}</span>";
      break;
    case "status_change":
        $labels_array = array('COMPLETED' => 'label-success','PROCESSING' => 'label-success','CANCELLED' => 'label-danger','HOLD' => 'label-danger','PENDING'=>'label-warning');
        $data = "<span class='label status_label label-".$row['id']." {$labels_array[$data]}'>{$data}</span><br><br>";
        $data .= "<a href='javascript:;' class='label-".$row['id']."' onclick='change_status(".$row['id'].",0)'>Change Status</a>";
        $data .= form_dropdown('status',array('PENDING'=>"PENDING",'PROCESSING'=>"PROCESSING","HOLD"=>"HOLD","COMPLETED"=>"COMPLETED"),'',"class='form-control hide select-status-".$row['id']."'");
        $data .="<br><a href='javascript:;' class='select-status-".$row['id']." hide btn btn-danger' onclick='change_status(".$row['id'].",1)'>Save</a>";
    break;
    case "desc";
      $data = "<div style='white-space:nowrap;overflow:hidden;height:40px;width:300px;text-overflow:ellipsis'>".$data."</div>";
    break;
    case 'datetime':
        $data = str2USDate($data);
        break;
    case 'money':
        $data = '$'.number_format((float)$data, 2);
        break;    
  }
  return $wrap_tag_open.$data.$wrap_tag_close;
}

function str2USDate($str)
{
  $intTime = strtotime($str);
  if ($intTime === false)
    return NULL;
  return date("m/d/Y", $intTime);
}

function str2USDT($str)
{
  $intTime = strtotime($str);
  if ($intTime === false)
    return NULL;
  return date("m/d/Y", $intTime);
}

// no logic for server time to local time.
function str2DBDT($str=null)
{
  $intTime = (!empty($str))?strtotime($str):time();
  if ($intTime === false)
    return NULL;
  return date("Y-m-d H:i:s", $intTime);
}

function str2DBDate($str)
{
  $intTime = strtotime($str);
  if ($intTime === false)
    return NULL;
  return date("Y-m-d",$intTime);
}

function addDayswithdate($date,$days)
{
  $date = strtotime("+".$days." days", strtotime($date));
  return  date("m/d/Y", $date);
}

function get_country_list(){

  $CI = & get_instance();

  $q = $CI->db->get("countries")->result_array();
  foreach ($q as $key => $value)
  {
    $res[$value['code']] = $value['name'];
  }
  return $res;

}

function get_states_list(){
  
  $CI = & get_instance();

  $q = $CI->db->get("states")->result_array();
  foreach ($q as $key => $value)
  {
    $res[$value['state_code']] = $value['state_name'];
  }
  return $res;

}

function package_limit_check($gc_name='',$type="contractor")
{

  $CI = & get_instance();
  $output['status'] = "success";
  $user_id="";
  $user = get_user_data();

  if($user['role']=="2")
    $user_id = $user['id'];
  else if($user['role']=="3" || $user=="4")
    $user_id = $user['company_id'];
  else if($user['role']=="1" && !empty($gc_name))
    $user_id = $gc_name;

  if(!$user_id)
    return $output;

  $CI->load->model('contractor_model');

  $get_plan = $CI->contractor_model->get_where(array("id"=>$user_id),"plan_id","admin_users")->row_array();

  $plan_id = $get_plan['plan_id'];
  $plans = $CI->contractor_model->get_plan_users_limit($plan_id);

  if($type=="manager"){
    $user_limit = $plans['project_manager'];
    $role = 3;
  }
  if($type=="superintendent"){
    $user_limit = $plans['superintendent'];
    $role = 4;
  }
  if($type=="contractor"){
    $user_limit = $plans['subcontractor'];
    $role = 5;
  }

  $total_limit = $plans['limited_users'];


  $user_count = $CI->contractor_model->get_users_limit($user_id,$role)['count'];

  if(($user_limit <= $user_count) && $user_limit > 0)
  {
    $output['msg'] = "You can't able to add more than ".$user_limit." ".$type;
    $output['status'] = "error";

  }
  else if($user_limit == 0)
  {
    if($total_limit <= $user_count)
    {
      $output['msg'] = "You can't able to add more than ".$total_limit." users";
      $output['status'] = "error";
    }

  }

  return $output;

}

function day_to_text($date)
{
  $day_no = date("N",strtotime($date));
  $day_array = array(1 => "Monday" , 2 => "Tuesday" , 3 => "Wednesday" , 4 => "Thursday" , 5 => "Friday" , 6 => "Saturday" , 7 => "Sunday"  );
  return $day_array[$day_no];
}

function date_ranges($case = '')
{
    $dt = date('Y-m-d');
    if(empty($case)){
        return false;
    }

    switch($case)
    {
        case 'today':
            $highdateval = $dt;
            $lowdateval = $dt;
        break;
        case 'thisweek':
            $highdateval = date('Y-m-d', strtotime('saturday this week'));
            $lowdateval  = date('Y-m-d', strtotime('sunday last week'));
        break;
        case 'thisweektodate':
            $highdateval = date('Y-m-d', strtotime($dt));
            $lowdateval  = date('Y-m-d', strtotime('sunday last week'));
        break;
        case 'thismonth':
            $highdateval = date('Y-m-d', strtotime('last day of this month'));
            $lowdateval  = date('Y-m-d', strtotime('first day of this month'));
        break;
        case 'thismonthtodate':
            $highdateval = date('Y-m-d', strtotime($dt));
            $lowdateval  = date('Y-m-d', strtotime('first day of this month'));
        break;
        case 'thisyear':
            $highdateval = date('Y-m-d', strtotime('1/1 next year -1 day'));
            $lowdateval  = date('Y-m-d ', strtotime('1/1 this year'));
        break;
        case 'thisyeartodate':
            $highdateval = date('Y-m-d', strtotime($dt));
            $lowdateval  = date('Y-m-d', strtotime('1/1 this year'));
        break;
        case 'thisquarter':
        $quarters = array();
        $first_day_year = date('Y-m-d', strtotime('1/1 this year'));
        $quarters[01] = $quarters[02] = $quarters[03] = array('start_date' => $first_day_year,'end_date' => date('Y-m-d', strtotime('4/1 this year - 1 day')));
        $quarters[04] = $quarters[05] = $quarters[06] = array('start_date' => date('Y-m-d', strtotime('4/1 this year')),'end_date' => date('Y-m-d', strtotime('7/1 this year - 1 day')));
        $quarters[07] = $quarters[08] = $quarters[09] = array('start_date' => date('Y-m-d', strtotime('7/1 this year')),'end_date' => date('Y-m-d', strtotime('10/1 this year - 1 day')));
        $quarters[10] = $quarters[11] = $quarters[12] = array('start_date' => date('Y-m-d', strtotime('10/1 this year')),'end_date' =>  date('Y-m-d', strtotime('1/1 next year -1 day')));
        $cur_month = (int) date("m",strtotime($dt));
       
        
        $date_range = $quarters[$cur_month];
        $highdateval = $date_range['end_date'];
        $lowdateval  = $date_range['start_date'];
        break;
        case 'yesterday':
            $highdateval = date('Y-m-d', strtotime('yesterday'));
            $lowdateval  = date('Y-m-d', strtotime('yesterday'));
        break;
        case 'recent':
            $highdateval =  date('Y-m-d', strtotime($dt));
            $lowdateval = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-4,date("Y")));
        break;
        case 'lastweek':
            $highdateval = date('Y-m-d', strtotime('saturday last week'));
            $lowdateval  = date( 'Y-m-d', strtotime( 'last Sunday', strtotime( 'last Sunday' ) ) );
        break;
        case 'lastweektodate':
            if(date('l')=="Sunday")
            {
                $highdateval  = date( 'Y-m-d', strtotime( 'last Sunday', strtotime( 'last Sunday' ) ) );
            }
            else
            {
                //$lastweek = date('l').' last week';
                $highdateval = date('Y-m-d');
            }
            
            $lowdateval  = date( 'Y-m-d', strtotime( 'last Sunday', strtotime( 'last Sunday' ) ) );
        break;
        case 'lastmonth':
            $lowdateval  = date('Y-m-d', strtotime('first day of last month'));
            $highdateval = date('Y-m-d', strtotime('last day of last month'));
        break;
        case 'lastmonthtodate';
            $lowdateval  = date('Y-m-d', strtotime('first day of last month'));
            $highdateval = date('Y-m-d', strtotime('last month'));
        break;
        case 'lastquater':
            $quarters = array();
            $first_day_year = date('Y-m-d', strtotime('1/1 this year'));
            $quarters[01] = $quarters[02] = $quarters[03] =  array('start_date' => date('Y-m-d', strtotime('10/1 last year')),'end_date' =>  date('Y-m-d', strtotime('1/1 this year -1 day')));
            $quarters[04] = $quarters[05] = $quarters[06] = array('start_date' => $first_day_year,'end_date' => date('Y-m-d', strtotime('4/1 this year - 1 day')));
            $quarters[07] = $quarters[08] = $quarters[09] = array('start_date' => date('Y-m-d', strtotime('4/1 this year')),'end_date' => date('Y-m-d', strtotime('7/1 this year - 1 day')));
            $quarters[10] = $quarters[11] = $quarters[12] = array('start_date' => date('Y-m-d', strtotime('7/1 this year')),'end_date' => date('Y-m-d', strtotime('4/1 this year - 1 day')));
            
            $cur_month = (int) date("m",strtotime($dt));
       
        
            $date_range = $quarters[$cur_month];
            $highdateval = $date_range['end_date'];
            $lowdateval  = $date_range['start_date'];
            break;
        case 'lastquatertodate':
            $quarters = array();
            $todaydate = date('d',strtotime($dt));
            $first_day_year = date('Y-m-d', strtotime('1/1 this year'));
            $quarters[01] = $quarters[02] = $quarters[03] =  array('start_date' => date('Y-m-d', strtotime('10/1 last year')),'end_date' =>  date('Y-m-d', strtotime('10/'.$todaydate.' last year')));
            $quarters[04] = $quarters[05] = $quarters[06] = array('start_date' => $first_day_year,'end_date' => date('Y-m-d', strtotime('1/'.$todaydate.' this year')));
            $quarters[07] = $quarters[08] = $quarters[09] = array('start_date' => date('Y-m-d', strtotime('4/1 this year')),'end_date' => date('Y-m-d', strtotime('4/'.$todaydate.' this year')));
            $quarters[10] = $quarters[11] = $quarters[12] = array('start_date' => date('Y-m-d', strtotime('7/1 this year')),'end_date' => date('Y-m-d', strtotime('7/'.$todaydate.' this year')));
            
            $cur_month = (int) date("m",strtotime($dt));
       
        
            $date_range = $quarters[$cur_month];
            $highdateval = $date_range['end_date'];
            $lowdateval  = $date_range['start_date'];
        break;
        case 'lastyear':
            $lowdateval  = date('Y-m-d', strtotime('1/1 last year'));
            $highdateval = date('Y-m-d', strtotime('1/1 this year -1 day'));
        break;
        case 'lastyeartodate':
            $lowdateval  = date('Y-m-d', strtotime('1/1 last year'));
            $highdateval = date('Y-m-d');
        break;
        case 'since30days':
            $highdateval =  date('Y-m-d', strtotime($dt));
            $lowdateval = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-31,date("Y")));
        break;
        case 'since60days':
            $highdateval =  date('Y-m-d', strtotime($dt));
            $lowdateval = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-61,date("Y")));
        break;
        case 'since90days':
            $highdateval =  date('Y-m-d', strtotime($dt));
            $lowdateval = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-91,date("Y")));
        break;
        case 'since350days':
            $highdateval =  date('Y-m-d', strtotime($dt));
            $lowdateval = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-351,date("Y")));
        break;
        case 'nextweek':
            $lowdateval  = date('Y-m-d', strtotime('this sunday'));
            $highdateval = date('Y-m-d', strtotime('next week saturday'));
        break;
        case 'nextfourweeks':
            $lowdateval  = date('Y-m-d', strtotime('this sunday'));
            $highdateval = date('Y-m-d', strtotime('+4 weeks Saturday'));
        break;
        case 'nextmonth':
            $lowdateval  = date('Y-m-d', strtotime('first day of next month'));
            $highdateval = date('Y-m-d', strtotime('last day of next month'));
        break;
        case 'nextquater':
            $quarters = array();
            $first_day_year = date('Y-m-d', strtotime('1/1 next year'));
            //$quarters[01] = $quarters[02] = $quarters[03] = array('start_date' => $first_day_year,'end_date' => date('Y-m-d', strtotime('4/1 this year - 1 day')));
             $quarters[01] = $quarters[02] = $quarters[03]= array('start_date' => date('Y-m-d', strtotime('4/1 this year')),'end_date' => date('Y-m-d', strtotime('7/1 this year - 1 day')));
             $quarters[04] = $quarters[05] = $quarters[06] = array('start_date' => date('Y-m-d', strtotime('7/1 this year')),'end_date' => date('Y-m-d', strtotime('10/1 this year - 1 day')));
            $quarters[07] = $quarters[08] = $quarters[09]  = array('start_date' => date('Y-m-d', strtotime('10/1 this year')),'end_date' =>  date('Y-m-d', strtotime('1/1 next year -1 day')));
            $quarters[10] = $quarters[11] = $quarters[12] = array('start_date' => $first_day_year,'end_date' => date('Y-m-d', strtotime('4/1 next year - 1 day')));
            $cur_month = (int) date("m",strtotime($dt));
       
        
            $date_range = $quarters[$cur_month];
            $highdateval = $date_range['end_date'];
            $lowdateval  = $date_range['start_date'];
        break;
        case 'nextyear':
            $lowdateval  = date('Y-m-d', strtotime('1/1 next year'));
            $highdateval = date('Y-m-d', strtotime('12/31 next year'));
        break;
        }

        return array('highdateval' => $highdateval, 'lowdateval' => $lowdateval);
   }
   
 

function xml_obj_to_array($xml_obj)
{
  $json = json_encode($xml_obj,TRUE);
  $arr = json_decode($json,TRUE);
  return $arr;                     
}
function site_traffic()
{
  $CI = & get_instance();
}
function actionLogAdd($type,$id = NULL, $action)
{
  $CI = & get_instance();
  $CI->load->model('log_model');
  $CI->log_model->add($type,$id,$action);
}

function send_email($to='',$from='',$from_name='',$cc='',$subject='',$message='',$attachment='')
{
  $CI = & get_instance();
  $CI->load->library('email');
  $CI->email->set_mailtype("html");        
  $CI->email->from($from, $from_name);      
  $CI->email->to($to);      
  $CI->email->cc($cc);      
  $CI->email->subject($subject);      
  $CI->email->message($message);
  if($attachment)
    $CI->email->attach($attachment);  
  if($CI->email->send())
    return true;
  else
    return false;
}

function get_user_by_role($role='',$user_id='')
{
  $CI = & get_instance();
  $user = get_user_data();
  if($user_id=='')
  {
    if($user['role']=="2")
      $CI->db->where("company_id",$user['id']);
    else if($user['role']=="3" || $user['role']=="4" || $user['role']=="5")
      $CI->db->where("company_id",$user['company_id']);
  }
  else
    $CI->db->where("company_id",$user_id);
  $CI->db->where("role",$role);
  $res = array();
  $q = $CI->db->get("admin_users")->result_array();
  foreach ($q as $key => $value)
  {
    $res[$value['id']] = $value['first_name']." ".$value['last_name'];
  }
  return $res;
}
function get_user_by_id($id='',$table='admin_users')
{
  $CI = & get_instance();
  $CI->db->where("id",$id);
  $res = array();
  $q = $CI->db->get($table)->row_array();
  return $q;
}

function get_contractors($project_id=0)
{
  $CI = & get_instance();
  $res = array();
  $CI->db->select("c.*");
  $CI->db->from("admin_users c");
  $CI->db->where("c.role","5");
  if($project_id)
  {
    $CI->db->join('project_contractors p',"FIND_IN_SET(CAST(c.id AS CHAR),p.contractor_id) >0"); 
    $CI->db->where('p.project_id',$project_id);
  }
  $q = $CI->db->get()->result_array();  
  foreach ($q as $key => $value)
  {
    $res[$value['id']] = $value['company_name'];
  }
  return $res;
}

function get_search_bar_values($table='',$field='',$where=array())
{
  $CI = & get_instance();
  $res = array();
  if($where)
    $CI->db->where($where);
  $q = $CI->db->get($table)->result_array();
  foreach ($q as $key => $value)
  {
    $res[$value['id']] = $value[$field];
  }
  return $res;
}

function get_project_pm_recipients($project_id='')
{
  $CI = & get_instance();
  $CI->db->where("a.id",$project_id);
  $CI->db->select("b.email as manager");
  $CI->db->from("project a");
  $CI->db->join("admin_users b","a.manager=b.id");
  $q = $CI->db->get()->row_array();
  return $q;
}

function get_project_super_recipients($project_id='')
{
  $res = array();
  $CI = & get_instance();
  $CI->db->where("a.id",$project_id);
  $CI->db->select("c.email as superintendent");
  $CI->db->from("project a");
  $CI->db->join("admin_users c","FIND_IN_SET(CAST(c.id AS CHAR),a.superintendent) > 0",'left');
  $q = $CI->db->get()->result_array();
  foreach ($q as $key => $value)
  {
    $res[] = $value['superintendent'];
  }
  return $res;
}

function get_project_contractors_recipients($project_id='')
{
  $CI = & get_instance();
  $CI->db->where("a.project_id",$project_id);
  $CI->db->select("GROUP_CONCAT(b.email) as cont_email");
  $CI->db->from("project_contractors a");
  $CI->db->join('admin_users b',"FIND_IN_SET(CAST(b.id AS CHAR),a.contractor_id) >0");
  $q = $CI->db->get()->row_array();
  return $q; 
}
function get_project_details($project_id='')
{
  $CI = & get_instance();
  $CI->db->where("id",$project_id);
  return $CI->db->get("project")->row_array();
}
function get_user_plan($user_id='')
{
  $CI = & get_instance();
  $CI->db->where("a.id",$user_id);
  $CI->db->select("b.name,IF(a.active='Y','Active','Inactive') as active");
  $CI->db->from("admin_users a");
  $CI->db->join("plans b","a.plan_id=b.id");
  return $CI->db->get()->row_array();
}

function check_trial_user($id='')
{
  $output = array("status"=>"success","msg"=>"valid");
  $CI = & get_instance();
  $CI->db->where("id",$id);
  $q =  $CI->db->get("admin_users")->row_array();
  $exp_date = date("Y-m-d",strtotime($q['expiry_date']." +15 days"));
  $curr_date = date("Y-m-d");
  if($q['trial_user']=="Y")
  {
    if(strtotime($exp_date) < strtotime($curr_date))
    {
      $output['status'] = "error";
      $output['msg'] = "expired";
    }
  }
  return json_encode($output);
}


 function check_username($email='',$id='')
{
  $CI = & get_instance();
  $CI->db->where("email",$email);
  $CI->db->or_where("username",$email);
  if($id)
    $CI->db->where("id!=",$id);
  $q = $CI->db->get("admin_users");
  return $q->row_array();
}
function get_company_logo($id='',$type='')
{
  $CI = & get_instance();
  if($type=='project')
  {
    $CI->db->where('id',$id);
    $q = $CI->db->get('project')->row_array();
    $id = $q['company_id'];
  }
  $CI->db->where("company_id",$id);
  $r = $CI->db->get('company_logo')->row_array();
  return $r['image'];
}
function get_user_session_login($user_id='',$type='desktop')
{
  $res = true;

  $CI = & get_instance();
  $CI->db->where('user_id',$user_id);
  $q = $CI->db->get('user_sessions')->row_array();

  if( count($q) > 0){

     $res = ($q[$type] == 'Y')? false:true;

    //for temp enable mobile
    if($type=='mobile' && $res===false)
      $res=true;

    if($res){
      $data[$type]     = 'Y';
      $CI->db->where('user_id', $user_id);
      $CI->db->update('user_sessions',$data);
    }

  }else{
    $data['user_id'] = $user_id;
    $data[$type]     = 'Y';
    
    $CI->db->insert('user_sessions',$data);

    $res = true;

  }

  return $res;
}
?>