<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(COREPATH.'libraries/models/App_model.php');

class Promo_model extends App_model
{
  function __construct()
  {
    parent::__construct();
    $this->_table = 'promo_code';
  }
  
  function listing()
  {  
    
    $this->_fields = "*,IF(active='Y','Active','Inactive') as status";
    //$this->db->where('role',1);
    $this->db->group_by('id');

    foreach ($this->criteria as $key => $value)
    {
      if( !is_array($value) && strcmp($value, '') === 0 )
        continue;
      switch ($key)
      {
        case 'promo_name':
          $this->db->like($key, $value);
        break;
        // case 'active':
        //   $this->db->like($key, $value);
        // break;
      }
    }
    return parent::listing();
  }

  function get_promo_by_code($code=''){

        $table_name = $this->_table;
        $return     = [];

        $result     = $this->db->get_where($table_name, array('promo_name' =>$code,'active'=>'Y'));
        if(!empty($result)){
            $return = $result->row_array();
        }

        return $return;
    }

}
?>