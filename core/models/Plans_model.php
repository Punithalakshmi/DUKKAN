<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Plans_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'plans';
  }
  
  function listing()
  {  
    
    $this->_fields = "*";
   // $this->db->where("role",3);
  //  $this->db->group_by('id');

    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'name':
          $this->db->like($key, $value);
        break;
        case 'limited_users':
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

  function plans_list()
  {
    $this->db->select("*");
    $this->db->from($this->_table);
    return $this->db->get()->result_array();
  }

  /**
    * This method handles to retrieve a plans detail by plan Id
    **/
    function get_plan_by_id($id){
        $table_name = $this->_table;
        $return     = [];

        $result     = $this->db->get_where($table_name, array('id' =>(int) $id));
        if(!empty($result)){
            $return = $result->row_array();
        }

        return $return;
    }

    public function check_coupon_applied($table,$where)
    {
      $this->db->where($where);

      $result = $this->db->get($table);
     
      return $result->result_array();
      

    }
}
?>