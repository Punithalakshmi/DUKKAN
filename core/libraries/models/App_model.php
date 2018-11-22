<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
abstract class App_model extends CI_Model
{
    protected $db;

    protected $_CI;
    protected $_table;

    protected $_search_fields  = array();
    protected $_search_methods = array();

    protected $_primary = array(
        'id'
    );
    
    protected $_debug = FALSE;

    protected $escape = TRUE; //Set this as TRUE if tilt symbol needs to be added with table fields.
    
    public function __construct()
    {
        parent::__construct();
        $this->_CI = get_instance();
        if (!$this->_table) {
            $this->_table = $this->getTableName();
        }
        $this->db = $this->_CI->db;
    }

    public function listing()
    {
       
        $this->_fields = 'SQL_CALC_FOUND_ROWS '.$this->_fields;
        $this->escape = FALSE;
        $this->db->select($this->_fields, $this->escape);
         
        $this->db->limit($this->listing->_get_per_page(), $this->listing->_get_offset());
        $this->db->order_by($this->listing->_get_order() , $this->listing->_get_direction());
         
        $list = $this->db->get($this->_table)->result_array();
     
        $count = $this->db->query("select FOUND_ROWS() as count")->row()->count;
         
        return array(
                    'list'  => $list,
                    'count' => $count
        );
         
    }

    public function get_where($where = array(), $fields = '*',$table = NULL, $order_by = NULL)
    {
    	if(!is_array($where)) return FALSE;
    	
    	$this->db->select($fields);
    	
    	foreach ($where as $f => $v)
        {
        	if(is_array($v))
        		$this->db->where_in($f, $v);
        	else
        		$this->db->where($f, $v);
        }
   		
        if( !is_null($order_by) )
            $this->db->order_by($order_by); 
        
        $table = ($table)?$table:$this->_table;
    	
        return $this->db->get($table);
    }
    
    public function get_records_count($where = array(), $field = '*')
    {
    	if(!is_array($where)) return FALSE;
    	 
    	$this->db->select("count($field) as count");
    	 
    	$this->db->where($where);
    	 
    	return $this->db->get($this->_table)->row()->count;
    }
    
    public function insert($data,$table = NULL)
    { 
        $table = ($table)?$table:$this->_table;
        
    	$this->db->insert($table, $data);
    
    	if ($this->_debug){
    		log_message('debug', $this->db->last_query());
    	}
    
    	return $this->get_last_id();
    }
    
    public function update($where = array(), $data,$table = NULL)
    {
    	if(!is_array($where)) return FALSE;
        
    	$table = ($table)?$table:$this->_table;
        
        foreach ($where as $f => $v)
        {
        	if(is_array($v))
        		$this->db->where_in($f, $v);
        	else
        		$this->db->where($f, $v);
        }
    	
    
    	$this->db->update($table, $data);
    
    	if ($this->_debug){
    		log_message('debug', $this->db->last_query());
    	}
    
    	return $this->db->affected_rows();
    }
    
    
    public function delete($where = array(),$table = NULL)
    {
    	if(!is_array($where)) return FALSE;
    	
        $table = ($table)?$table:$this->_table;
        
    	foreach ($where as $f => $v)
        {
        	if(is_array($v))
        		$this->db->where_in($f, $v);
        	else
        		$this->db->where($f, $v);
        }
    
    	return $this->db->delete($table);
    }
    
    public function get_by_id($id)
    {
        if (!$this->_where_primary($id)) {
            return null;
        }

        $this->_prepare_fields(new Criteria());
        $this->_prepare_from();
		
		$res = $this->db->get();
		
		if( is_object($res) && $res->num_rows()){
			return $res->row_array();
		} else {
			return false;
		}
		
    }

    public function get_by_ids($ids)
    {
        if (count($this->_primary) > 1) {
            throw new Exception("get_by_ids() can't be used for a table with a composite primary key");
        }

        if (!count($ids)) {
            return array();
        }

        $primary = current($this->_primary);

        $this->_prepare_fields(new Criteria());
        $this->_prepare_from();

        $this->db->where_in($primary, $ids);

        return $this->db->get()->result_array();
    }
    
    public function get_list(Criteria $criteria = null)
    {
        if (!$criteria) {
            $criteria = new Criteria();
        }

        if ($criteria->order()) {
            $this->db->order_by($criteria->order());
        }

        if ($criteria->limit()) {
            $this->db->limit($criteria->limit(), $criteria->offset());
        }
        
        $this->_prepare_fields($criteria);
        $this->_prepare_from();
        $this->_prepare_where($criteria);
        
        if($this->_debug){
        	$res = $this->db->get();
        	log_message('debug', $this->db->last_query());
        	return $res->result_array();
        }
        $result = $this->db->get();
		if ($result === false) {
            throw new Exception($this->db->_error_message());
        }
        return $result->result_array();
    }
    
    public function get_count(Criteria $criteria = null)
    {
    	//if DB driver is MYSQL , use FOUND_ROWS() to find record count.
    	if(strcmp($this->db->dbdriver, 'mysql') === 0)
        	return $this->db->query("select FOUND_ROWS() as count")->row()->count;
    		
        if (!$criteria) {
            $criteria = new Criteria();
        }	
        $this->_prepare_from();
        $this->_prepare_where($criteria);
        
        $res = $this->db->count_all_results();
		
        return (int) $res;
    }
 
    public function search(Criteria $criteria = null, $search_type = null)
    {
        if ($search_type) {
            if (isset($this->_search_methods[$search_type])) {
                $list_method  = $this->_search_methods[$search_type]['list'];
                $count_method = $this->_search_methods[$search_type]['count'];
            } else {
                throw new Exception("Call to undefined method " . get_class($this) . "::search_" . $search_type);
            }
        } else {
            $list_method  = 'get_list';
            $count_method = 'get_count';
        }

        
        return array(
            'list'  => $this->$list_method($criteria),
            'count' => $this->$count_method($criteria)
        );
    }
    
    public function __call($method, $args)
    {
        if (substr($method, 0, 7) == 'search_') {
            $search_type = substr($method, 7);
            $criteria    = current($args);

            if (!$criteria instanceof Criteria) {
                $criteria = null;
            }

            return $this->search($criteria, $search_type);
        }
        throw new Exception("Call to undefined method " . get_class($this) . "::" . $method);
    }

	
    
    public function save($data)
    {
        $ids        = array();
        $ids_passed = true;

        foreach ($this->_primary as $field) {
            if (!isset($data[$field])) {
                $ids_passed = false;
                break;
            }
            $ids[$field] = $data[$field];
        }

        $count = 0;
        if ($ids_passed) {
            if (!$this->_where_primary($ids)) {
                return false;
            }
            $count = $this->db->from($this->_table)->count_all_results();
        }

        if ($ids_passed && $count) {
            return $this->update($ids, $data);
        } else { 
            return $this->insert($data);
        }
    }

    
    
    public function update_list($primary_keys = array(), $data )
    {  
    	foreach($primary_keys as $key)
    	{
    		$this->db->or_where($this->_primary[0], $key);

    	}    
    	
    	$this->db->update($this->_table, $data);
        foreach($primary_keys as $key)
        {
            $this->_after_save($key);

        }
		$result = $this->db->affected_rows();
		return $result;
    }
    
    
    
    public function get_last_id()
    {
        return $this->db->insert_id();
    }

    protected function _prepare_fields(Criteria $criteria = null)
    {
        $fields = $this->_table . '.*';
        if ($criteria && $criteria->fields()) {
            $fields = $criteria->fields();
        }
        
        //if DB driver is MYSQL , use SQL_CALC_FOUND_ROWS to find record count.
        if(strcmp($this->db->dbdriver, 'mysql') === 0)
        	$fields = 'SQL_CALC_FOUND_ROWS '.$fields;
        
        $this->db->select($fields,$this->escape);
    }

    protected function _prepare_from()
    {
        $this->db->from($this->_table);
    }

    protected function _prepare_where(Criteria $criteria = null)
    {
        if ($where = $criteria->where()) {
            $this->db->where($where);
        }
        $this->_prepare_spec_where($criteria);
    }

    protected function _prepare_spec_where(Criteria $criteria = null)
    {
        if ($criteria->spec_where('keyword')) {
            $this->db->where($this->_sql_for_search($criteria->spec_where('keyword')), null, false);
        }
    }

    protected function _sql_for_search($keyword, $fields = null, $type = 'AND')
    {
        if (!$fields) {
            $fields = $this->_search_fields;
        }

        $keywords       = explode(' ', $this->db->escape_str($keyword));
        $sub_conditions = array();
      
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if ($keyword) {
                $sub_condition = '';
                foreach ($fields as $field) {
                    $sub_condition .= $sub_condition ? ' OR ' : '';
                    $sub_condition .= "{$field} LIKE '%{$keyword}%'";
                }
                $sub_conditions[] .= "({$sub_condition})";
            }
        }

        if (count($sub_conditions)) {
            $type = strtoupper($type);
            if ($type != 'OR' && $type != 'AND') {
                $type = 'AND';
            }
            return "(" . implode(" {$type} ", $sub_conditions) . ")";
        }
        return "1=1";
    }

    protected function _where_primary($ids)
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids) && count($this->_primary) == 1) {
            $ids = array($this->_primary[0] => $ids);
        }
        foreach ($this->_primary as $field) {
            if (!array_key_exists($field, $ids)) {
                throw new Exception("The primary key '{$field}' wasn't passed");
            }
            $this->db->where($this->_table . '.' . $field, $ids[$field]);
        }
        return true;
    }
    
    function getTableName()
    {
    	$class = strtolower(get_class($this));
    	return substr($class, 0, strlen($class) - 6);
    }
    
    function getAll($columns, $sort=false, $limit=false, &$total, $like)
    {
        
        $this->db->select('count(*) as i');
        if ($like[1] != '') {
            $this->db->like($like[1], $like[0]);
        }
        $query = $this->db->get($this->getTableName());
        $total = $query->result();
        
        $total = $total[0]->i;

        $this->db->select($columns);
        if ($sort) {
            $this->db->order_by($sort);
        }
        if (is_array($limit)) {
            $this->db->limit($limit[1], $limit[0]);
        }
        if ($like[1] != '') {
            $this->db->like($like[1], $like[0]);
        }
        $query = $this->db->get($this->getTableName());
        return $query->result();
    }
    
    public function update_table($table,$where = array(), $data)
    {
    	if(!is_array($where)) return FALSE;
    	
    	$this->db->where($where);
    
    	$this->db->update($table, $data);
    
    	if ($this->_debug){
    		log_message('debug', $this->db->last_query());
    	}
    
    	return $this->db->affected_rows();
    }
    
    public function insert_table($table,$data)
    {
    	$this->db->insert($table, $data);
    
    	if ($this->_debug){
    		log_message('debug', $this->db->last_query());
    	}
    
    	return $this->get_last_id();
    }
    
    protected function _before_save($data) {}
    
    protected function _after_save($last_id) {}

    public function get_plan_users_limit($plan_id='')
    {
        $this->db->where('id',$plan_id);
        return $this->db->get("plans")->row_array();
    }

    public function get_users_limit($user_id,$role='')
    {
        $this->db->where('company_id',$user_id);
        if($role)
        $this->db->where('role',$role);
        $this->db->select("count(id) as count");
        return $this->db->get("admin_users")->row_array();
    }
    
}

?>
