<?php

class Listing
{
    const DIRECTION_ASC  = 'ASC';
    const DIRECTION_DESC = 'DESC';

    private $_CI;

    private $_view = '_partials/listing/default';
    private $_init_scripts = '';
    public $_per_page    = 20;
    private $_per_page_options    = array(20, 50, 100);
    public $_uri_segment = 3;
    public $_base_url    = '';

    private $_order_segment    = 1;
    private $_dir_segment      = 2;
    private $_per_page_segment = 4;

    private $_default_order     = 'id';
    private $_default_direction = self::DIRECTION_DESC;

    private $_fields = array();
    private $_order_fields = array();   
    private $_map          = array();
    private $_disallow_empty = false;
	private $_listing_action = '';
	
	
    public function __construct()
    {                                   
        $this->_CI = get_instance();
        
        $this->_CI->load->library('pagination');
        $this->_CI->load->library('parser');
        $this->_CI->load->library('encrypt');
        
        $this->_CI->pagination->full_tag_open = '<ul class="pagination pull-right">';
        $this->_CI->pagination->full_tag_close = '</ul>';
        
        $this->_CI->pagination->first_tag_open = '<li class="paginate_button disabled">';
        $this->_CI->pagination->first_tag_close = '</li>';
        
        $this->_CI->pagination->prev_tag_open = '<li class="paginate_button previous disabled">';
        $this->_CI->pagination->prev_tag_close = '</li>';
        
        $this->_CI->pagination->cur_tag_open = '<li class="paginate_button active"><span>';
        $this->_CI->pagination->cur_tag_close = '</span></li>';
        
        $this->_CI->pagination->num_tag_open = '<li class="paginate_button disabled">';
        $this->_CI->pagination->num_tag_close = '</li>';
        
        $this->_CI->pagination->next_tag_open = '<li class="paginate_button disabled">';
        $this->_CI->pagination->next_tag_close = '</li>';
        
        $this->_CI->pagination->last_tag_open = '<li class="paginate_button disabled">';
        $this->_CI->pagination->last_tag_close = '</li>';
        
        

    }

    public function initialize($params = array())
    {
        if ($params) 
        {
            foreach ($params as $key => $val) 
            {
                $key = "_{$key}";
                if (isset($this->$key)) 
                {
                    $this->$key = $val;
                }
            }   

            $this->set_order_fields();
            if(isset($params['init_scripts']) && $this->_init_scripts)
            	$this->_CI->init_scripts[] = $this->_init_scripts;
        }
    }

    protected function set_order_fields()
    {
    	foreach ($this->_fields as $key => $row)
    	{
    		if($row['sortable'] === TRUE)
    			$this->_order_fields[] = $key;
    	}
    }
    
    public function render($callback, $criteria = array(), $variables=array())
    {
        
		$result = call_user_func($callback, $criteria);

		$config['base_url']   = base_url().$this->_base_url;
	    $config['per_page']   = $this->_get_per_page();
        $config['cur_page']   = $this->_get_offset();
        $config['uri_segment']= $this->_uri_segment;
        $config['total_rows'] = $result['count'];

        $this->_CI->pagination->initialize($config);

        $data['order']             = $this->_get_order();
        $data['direction']         = $this->_get_direction();
        $data['default_direction'] = $this->_default_direction;
        $data['cur_page']          = $this->_get_offset();
        $data['per_page']          = $this->_get_per_page();
        $data['base_url']          = base_url().$this->_base_url;
        $data['list']              = $result['list'];
        $data['count']             = $result['count'];
        $data['pagination']        = $this->_CI->pagination->create_links();
		$data['listing_action']	   = $this->_listing_action;
		$data['namespace'] = $this->_CI->encrypt->encode($this->_CI->namespace."|{$variables['model']}|{$variables['method']}");
        
        foreach ($variables as $k => $v) {
            $data[$k] = $v;
        }
       
        return $this->_CI->load->view($this->_view, $data, true);
    } 

    public function get_result($callback, Criteria $criteria = null, $namespace_model_method = FALSE)
    {
    
    	if (!$criteria) 
    	{
    		$criteria = new Criteria();
    	}
    	
    	if($this->_CI->uri->segment(2, 'index') != 'get_notes' && $this->_CI->uri->segment(2, 'index') != 'get_logs')
    		$criteria->order($this->_get_order() . ' '. $this->_get_direction());
    	
    	if($namespace_model_method !== FALSE)
    	{
    		$temp = explode( '|', $this->_CI->encrypt->decode($namespace_model_method) );
    		if(count($temp) == 3)
    		{
    			$namespace 	= $temp[0];
    			$model 		= $temp[1];
    			$method 	= $temp[2];
    			
    			$callback = array($this->_CI->$model, $method);
    			
    			//get search conditions from session using namespace. 
    			$search_conditions 			= $this->_CI->session->userdata($namespace.'_search_conditions');
    			$narrow_search_conditions 	= $this->_CI->session->userdata($namespace.'_search_narrow_conditions');

    			//now convert these above conditions to criteria
    			$criteria = $this->_CI->get_search_criteria($search_conditions);
    			$criteria = $this->_CI->get_search_criteria($narrow_search_conditions, $criteria);

    			//get order_field and its direction
    			$order_field = FALSE;
    			$direction   = FALSE;
    			if($this->_CI->session->userdata($namespace.'_order_field'))
    				$order_field = $this->_CI->session->userdata($namespace.'_order_field');
    			if($this->_CI->session->userdata($namespace.'_direction'))
    				$direction = $this->_CI->session->userdata($namespace.'_direction');
    			
    			if($order_field !== FALSE && $direction !== FALSE)
    				$criteria->order($order_field . ' '. $direction);
    			
    		}
    	}
    	
    	
    	
    	
    	
    	if ($this->_disallow_empty && count($criteria->spec_where()) < 2) 
    	{
    		$result = array('list' => array(), 'count' => 0);
    		$data['empty_request'] = true;
    	} else 
    	{
    		$result = call_user_func($callback, $criteria);
    	}
    
    	if ($this->_map) 
    	{
    		$result['list'] = call_user_func($this->_map, $result['list']);
    	}

    	$data['list']              = $result['list'];
    	$data['count']             = $result['count'];
    
    
    	return $data;
    }

    function get_listings($model = null, $method = null)
    {
    	if(is_null($model) || is_null($method))
    		return FALSE;
    	
    	//load model from which data is going to be fetched
    	$this->_CI->load->model($model);
    	
    	//load listing config file 
    	$this->_CI->load->config("listing", TRUE);
    	
    	//load helpers for searching and sorting 
    	$this->_CI->load->helper(array('search_helper', 'sort_helper'));
    	
    	//retrieve the search conditions and if there was a previous search
    	$search_conditions = prepare_search_conditions($this->_CI->_search_conditions, $this->_CI->namespace.'_search_conditions');
    	
    	//retrieve the narrow search conditions and if there was a previous search
    	$narrow_search_conditions = prepare_search_conditions($this->_CI->_narrow_search_conditions,$this->_CI->namespace.'_search_narrow_conditions');
    	//echo '<pre>';print_r($this->_CI->session->all_userdata());die;
    	//set custom search flag
    	$custom_search = strcmp($this->_CI->input->post('custom_search'), 'yes') === 0?TRUE:FALSE;
    	
    	//unset simple search if request comes for clear
    	if( strcmp($this->_CI->input->post('clear_simple_search'), 'yes') === 0)
    	{
    		$tmp = $this->clear_filter($search_conditions, $custom_search);
    		if($tmp !== FALSE && is_array($tmp))
    			$search_conditions = $tmp;
    	}
    	$this->_CI->session->set_userdata($this->_CI->namespace.'_search_conditions', $search_conditions);
    	
    	//unset narrow search if request comes for clear
    	if( strcmp($this->_CI->input->post('clear_advance_search'), 'yes') === 0)
    	{
    		$tmp = $this->clear_filter($narrow_search_conditions, $custom_search);
    		if($tmp !== FALSE && is_array($tmp))
    			$narrow_search_conditions = $tmp;
    	}
    	$this->_CI->session->set_userdata($this->_CI->namespace.'_search_narrow_conditions', $narrow_search_conditions);
    	
    	
    	//now convert these above conditions to criteria
    	$criteria = $this->get_search_criteria($search_conditions);
    	$criteria = $this->get_search_criteria($narrow_search_conditions);
    	
    	// init the pagination settings using the values from the pagination config file
    	$pag_config = $this->_CI->namespace;
    	
    	if(strpos($this->_CI->namespace, 'get_notes') !== FALSE)
    		$pag_config = 'notes_index';
    	if(strpos($this->_CI->namespace, 'get_logs') !== FALSE)
    		$pag_config = 'logs_index';
    	
    	$this->initialize($this->_CI->config->item($pag_config, 'listing'));
    	
    	if(strpos($this->_CI->namespace, 'get_notes') !== FALSE)
    		$this->_base_url = $this->_CI->uri->segment(1)."/get_notes/{$search_conditions['search_type']}/{$search_conditions['search_text']}/";
    	if(strpos($this->_CI->namespace, 'get_logs') !== FALSE)
    		$this->_base_url = $this->_CI->uri->segment(1)."/get_logs/{$search_conditions['search_type']}/{$search_conditions['search_text']}/";
    	
    	//update listing filed's status		 
    	$listing_fields = $this->update_listing_fields();
    	
    	
    	//perform the search function and return back the listing view with data
    	return $this->render( array($this->_CI->$model, $method), $criteria, array('fields' => $listing_fields, 'model' => $model, 'method' => $method));
    	
    }
    
    function clear_filter($conditions = array(), $custom_search = FALSE)
    {
    	if(!count($conditions))
    		return FALSE;
    	
    	$post_data = array();
    	
    	if($custom_search)
    	{
    		foreach ($_POST as $key => $val)
    			$post_data[] = $key;
    	}
    	
    	foreach ($conditions as $key => $value)
    	{
    		if(!in_array($key, $post_data))
    			$conditions[$key] = '';
    	}
    	
    	return $conditions;
    	
    }
    
    public function update_listing_fields()
    {
    	$field_namespace = $this->_CI->namespace.'_fields';
    //	$this->_CI->session->unset_userdata($field_namespace);die;
    	$listing_fields = $this->_fields;
    	if( $this->_CI->session->userdata($field_namespace) && is_array($this->_CI->session->userdata($field_namespace)) )
    		$listing_fields = $this->_CI->session->userdata($field_namespace);
    	
   	 
    	$action = $this->_CI->input->post('action')?$this->_CI->input->post('action'):'';
    	$field  = $this->_CI->input->post('field')?$this->_CI->input->post('field'):'';
    	
    	if( isset($listing_fields[$field]['default_view']) && (strcmp($action, 'add_field') === 0 || strcmp($action, 'remove_field') === 0) )
    		$listing_fields[$field]['default_view'] = strcmp($action, 'add_field') === 0?1:0;
    	
    	$this->_CI->session->set_userdata($field_namespace, $listing_fields);
    	
    	return $this->_CI->session->userdata($field_namespace);
    }
    
    /**
    * This function transforms conditions in to Criteria array.
    *@param array conditions <p>
    * It is an array of 'filed as key and search-string as value'
    * </p>
    * @return object criteria array.
    */
    function get_search_criteria($conditions)
    {
    	foreach($conditions as $key => $value)
    	{
    		if(strcmp($key, 'search_type') === 0 && strcmp($value, '') !== 0)
    		{
    			$this->_CI->criteria[$conditions['search_type']] = $conditions['search_text'];
    		}
    		$value = is_array($value)?$value:trim($value);
    		$this->_CI->criteria[$key] = $value;
    	}
    	 
    	return $this->_CI->criteria;
    }
    
    public static function get_directions()
    {
        return array(
            self::DIRECTION_ASC,
            self::DIRECTION_DESC        
        );
    }

    public static function is_valid_direction($direction)
    {
        $direction = strtoupper($direction);
        return in_array($direction, self::get_directions());
    }

    public static function reverse_direction($direction = null)
    {
        $direction = strtoupper($direction);

        if ($direction == self::DIRECTION_ASC) {
            return self::DIRECTION_DESC;
        }  else if ($direction == self::DIRECTION_DESC) {
            return self::DIRECTION_ASC;  
        }   

        return self::DIRECTION_ASC;
    }
    
    function _get_offset()
    {
        $offset = $this->_CI->uri->segment($this->_uri_segment);
        return $offset ? $offset : 0;
    }

    function _get_order()
    {
    	$order_field = $this->_CI->uri->segment($this->_uri_segment + $this->_order_segment);
    	if (!$order_field && $this->_CI->session->userdata($this->_CI->namespace.'_order_field')) {
    		$order_field = $this->_CI->session->userdata($this->_CI->namespace.'_order_field');
    	}
    	if (in_array($order_field, $this->_order_fields)) {
    		$this->_CI->session->set_userdata($this->_CI->namespace.'_order_field', $order_field);
    		return $order_field;
    	}
    	return $this->_default_order;
    }

    function _get_direction()
    {
        $direction = $this->_CI->uri->segment($this->_uri_segment + $this->_dir_segment);
        if (!$direction && $this->_CI->session->userdata($this->_CI->namespace.'_direction')) {
            $direction = $this->_CI->session->userdata($this->_CI->namespace.'_direction');
        }
        if (self::is_valid_direction($direction)) {
        	$this->_CI->session->set_userdata($this->_CI->namespace.'_direction', $direction);
            return $direction;
        }
        return $this->_default_direction;
    }

    function _get_per_page()
    {
    	if($this->_CI->session->userdata($this->_CI->namespace.'_per_page'))
    		return $this->_CI->session->userdata($this->_CI->namespace.'_per_page');
    	
        return $this->_per_page;
    }
    
    function _get_per_page_options()
    {
    	return $this->_per_page_options;
    }
}

?>
