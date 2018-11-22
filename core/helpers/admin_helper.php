<?php

function getAdminUserId()
{
    $CI = & get_instance();
    $admin = $CI->session->userdata('admin_data');
    return isset($admin['id'])?$admin['id']:FALSE;
}

function getChannels()
{
	$CI = & get_instance();
	$CI->db->select('id, name');
	$result = $CI->db->get('sales_channel')->result_array();
	$channels = array();
	foreach ($result as $row)
		$channels[$row['id']] = $row['name'];

	return $channels;
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
        case 'humanize':
        $CI->load->helper("inflector");
            $data = humanize($data);
			break;
		case 'date':
            if($data == "0000-00-00 00:00:00" || $data == "0000-00-00" || empty($data))
        		$data= " - ";
          else
        	$data = date('Y-m-d',get_gmt_to_local(strtotime($data)));
			break;
		case 'datetime':
			$data = date('Y-m-d H:i:s',get_gmt_to_local(strtotime($data)));
			break;
		case 'money':
			$data = '$'.number_format((float)$data, 2);
			break;
        case 'china_amt':
            $data = number_format((float)$data, 2).'元';
            break;    
		case 'abbr':
			$abbr = array('I' => 'International', 'D' => 'Domestic');
			$data = isset($abbr[$data])?$abbr[$data]:$data;
			break;
		case 'status':
			$labels_array = array(
					'PENDING' => 'label-warning',
					'FAILED' => 'label-danger',
					'ACCEPTED' => 'label-success',
					'PROCESSING' => 'label-info',
					'SHIPPED' => 'label-green1',
					'COMPLETE' => 'label-green1',
					'AMAZON-SHIPPED' => 'label-amazon',
					'CANCELLED' => 'label-danger',
					'HOLD' => 'label-rose',
                    'PAID' => 'label-success',
                    'NOT PAID' => 'label-danger',
					);
			if(isset($labels_array[strtoupper($data)]))
			{
				$label = $labels_array[strtoupper($data)];
				$data = "<span class='label $label'>{$data}</span>";
			}

			break;
		case 'short_info':
			$data = "<a href='javascript:;' onclick='order_short_info({$row['id']}, this)' id='short_info_{$row['id']}' title='click to view short-info' data-toggle='tooltip' data-placement='top'><u><b>{$data}</b></u></a>";

			break;
		case 'tracking':
			$data  = "<input type='text' id='tracking[{$row['id']}]' name='tracking[{$row['id']}]'";
			$data .= "value='{$row['tracking']}' onkeyup='ship_enable({$row['id']}, this)' class='input-medium' />"; 
			break;
		case 'services':
			$data = form_dropdown("shipment_service[{$row['id']}]", get_shipping_services(), $row['shipment_service'], 'class="input-small boot_select_false"');
			break;
		case 'external_link':
			if(isset($row['api_name']) && strcmp(trim($row['api_name']), 'amazon') === 0)
				$data = "<a href = 'https://sellercentral.amazon.com/gp/orders-v2/details?ie=UTF8&orderID=$data' target = '_blank'>$data</a>";
			elseif(isset($row['api_name']) && strcmp(trim($row['api_name']), 'sears') === 0)
				$data = "<a href = 'https://seller.marketplace.sears.com/SellerPortal/d/oms/fbm/orders.jsp' target = '_blank'>$data</a>";
			break;
		case 'address':
			$data = format_address($row);
			break;
		case 'mailto':
			$data = '<a href="mailto:'.$data.'">'.$data.'</a>';
			break;
		case 'formated_number':
			$data = number_format((float)$data, 2);
			break;
        case 'so_link':
			$data = '<a href="'.site_url('sales_orders/view')."/".$data.'">'.$data.'</a>';
			break;
        case 'product_link':
			$data = '<a href="'.site_url('product/view')."/".$data.'">'.$data.'</a>';
			break;
        case 'product_name_link':
			$data = '<a href="'.site_url('product/view')."/".$row['id'].'">'.$data.'</a>';
			break;
        case 'ra_link':
			$data = '<a href="'.site_url('return_authorization/view')."/".$data.'">'.$data.'</a>';
			break;
        case 'rf_link':
			$data = '<a href="'.site_url('refunds/view')."/".$data.'">'.$data.'</a>';
			break;
        case 'eo_link':
			$data = '<a href="'.site_url('exceptional_orders/view')."/".$data.'">'.$data.'</a>';
			break;
        case 'po_link':
			$data = '<a href="'.site_url('purchase/view')."/".$data.'">'.$data.'</a>';
			break;
       case 'ship_link':
			$data = '<a href="'.site_url('shipment/view')."/".$data.'">'.$data.'</a>';
			break;

        case 'adjusted_price_calc':

            $CI->load->model('purchase_model');
            $product_details = $CI->purchase_model->get_po_item_details($row['id']);
            $total_ship_weight = 0;
            foreach ($product_details as $val)
            {               
                $total_ship_weight += ($val['quantity'] * $val['shipping_weight']);
            }

            $CI->load->model('purchase_order_prices_model');
            //get Invoice fee
            $invoice_charges = $CI->purchase_order_prices_model->get_total_charge_by_po($row['id'],array('Drop Ship/Handling','Shipping and Handling','Other'));

            //get additional fee
            $additional_charges = $CI->purchase_order_prices_model->get_total_charge_by_po($row['id'],array("Inbound 3rd party UPS","Inbound 3rd party Fedex","Outbound to FBA","Other Charge"));
            
            $shipping_charge = ($invoice_charges+$additional_charges);

            $items_array = array();

            foreach ($product_details as $detail)
            {
                $total_weight = $detail['quantity'] * $detail['shipping_weight'];
                $percent_weight = ($total_ship_weight!=0)?round((($total_weight/$total_ship_weight)*100),2):0;
                $total_ship_price = round((($percent_weight*$shipping_charge)/100),2);
                $unit_ship_price = round(($total_ship_price/$detail['quantity']),2);

                $actual_price = ($detail['unit_price'] + $unit_ship_price);

                $items_array[$detail['product_id']] = array('product_id'=>$detail['product_id'],'sku' => $detail['sku'],'qty'=> $detail['quantity'],'product_weight' => $detail['shipping_weight'],'total_weight' => $total_weight,'percent_weight' => $percent_weight, 'total_ship_price' => $total_ship_price, 'unit_ship_price' => $unit_ship_price, 'actual_price' => $actual_price); 

            }

            $data = $row['unit_price'];

            if(isset($items_array[$row['product_id']]))
                $data = $items_array[$row['product_id']]['actual_price'];

            $data = '$'.number_format((float)$data, 2);
        break;

	}
	
	return $wrap_tag_open.$data.$wrap_tag_close;
}




function get_product_details($product_details = array(), $channel_id)
{
	$CI = & get_instance();

	if($channel_id == 7 || $channel_id == 8)
	{
		if(isset($product_details['amazon_sku']) && isset($product_details['product_id']))
		{
			$where = array('amazon_sku' => $product_details['amazon_sku'], 'product_id' => $product_details['product_id']);
			$result = $CI->db->get_where('listing_amazon', $where);
			if($result->num_rows())
				return $result->row_array();
		}
	}
	elseif(isset($product_details['product_id']))
	{
		$result = $CI->db->get_where('product', array('id' => $product_details['product_id']));
		if($result->num_rows())
			return $result->row_array();
	}

	return FALSE;
}

//Get Address Information
function getAdddressBySoId($so_id = '',$type = 'S',$output_type = 'both', $address_tag = TRUE)
{
	$CI = & get_instance();
	$CI->load->model('address_model');
	$address = $CI->address_model->get_address_by_sales_order($so_id,$type);
	
	if(!count($address))
		return FALSE;
	
	if(strcmp($output_type, 'data') === 0)
		return $address;
	
	$address_format = format_address($address, $address_tag);
	
	if(strcmp($output_type, 'html') === 0)
		return $address_format;

	return array('data' => $address, 'html' => $address_format);
}

function get_origin_address($output_type = 'both')
{
	$CI = & get_instance();
	$result = $CI->db->get_where('settings', array('type' => 'po_address'))->result_array();
	$address = array();
	foreach ($result as $row) 
	{
		$address[$row['name']] = $row['value'];
	}

	if(!count($address))
		return FALSE;
	
	if(strcmp($output_type, 'data') === 0)
		return $address;
	
	$address_format = format_address($address);
	
	if(strcmp($output_type, 'html') === 0)
		return $address_format;

	return array('data' => $address, 'html' => $address_format);
}

function get_address_by_contact_id($cid = 0, $output_type = 'both', $address_tag = TRUE)
{
	if(!$cid)
		return FALSE;

	$CI = & get_instance();

	$CI->load->model('address_model');
	$address = $CI->address_model->get_address_by_contact_id($cid);

	if(!count($address))
		return FALSE;
	
	if(strcmp($output_type, 'data') === 0)
		return $address;
	
	$address_format = format_address($address, $address_tag);
	
	if(strcmp($output_type, 'html') === 0)
		return $address_format;

	return array('data' => $address, 'html' => $address_format);

}

function format_address($address = array(), $address_tag = TRUE)
{
	if(!count($address))
		return FALSE;

	$address_format = ($address_tag)?"<address>":"<p>";
	$address_format .= "<strong>{$address['first_name']} {$address['last_name']}</strong> <br />";
	if(strcmp(trim($address['company']),'') !== 0)
		$address_format .= "{$address['company']}<br />";
	$address_format .= "{$address['address1']} <br />";
	if(strcmp($address['address2'],'') !== 0)
		$address_format .= "{$address['address2']} <br />";
	$address_format .= "{$address['city']} {$address['state']} {$address['zip']} <br />";
	$address_format .= "{$address['country']} <br />";
	if(strcmp($address['phone'],'') !== 0)
		$address_format .= "<abbr title='Phone'>P:</abbr> {$address['phone']}";
	$address_format .= ($address_tag)?"</address>":"</p>";

	return $address_format;

}

function show_alert($message = '')
{
	
	$html = ' <div class="container">
			    <div class="alert fade in m_top_15">
			      <button data-dismiss="alert" class="close" type="button">×</button>
			      <strong>'.$message.'</strong> </div>
			  </div>';
	
	return $html;
}

function get_brands($flag = FALSE){
    
    $CI = & get_instance();
    
    $CI->load->model("brand_model");
    return $CI->brand_model->getBrands_filer($flag);
}

function category_tree($check_box = false, $buttons = false, $check_box_ids = array(),$channel_id = ""){
        
        $CI = & get_instance();
        $CI->load->model("category_model");
        
        $data = array();
        $sales_channel = getChannels();
        
        if($channel_id){
            $category[$sales_channel[$channel_id]] = $CI->category_model->get_category_list(array("category.enabled" => 1,"category.sales_channel_id" => $channel_id))->result_array();
        }else
        {
            if(!empty($sales_channel)) {
                foreach($sales_channel as $key => $val){
                  $category[$val] = $CI->category_model->get_category_list(array("category.enabled" => 1,"category.sales_channel_id" => $key))->result_array();
                }
            }
        }
        
     
        $CI->load->library("menu_category");
        
        if(!empty($category)){
            foreach($category as $key => $cat) {
                $menu_array = array();
                foreach($cat as $row){
                    $row['parent_id'] = (empty($row['parent_id']))?0:$row['parent_id'];
                    $menu_array[$row['id']] = array('title' => $row['name'], 'parent' => $row['parent_id']);
                }
                
                $CI->menu_category->menu_array_init($menu_array,array_search($key,$sales_channel));
                
                $data['list'][$key] = $CI->menu_category->generate(0,$check_box,$buttons,$check_box_ids);
            }
        }
        
        return $data;

}

function get_product_sku($product_id) {
        $CI = & get_instance();
        $CI->load->model("product_model");
        $res = $CI->product_model->get_where(array("id" => $product_id),"sku")->row_array();
        return $res['sku'];
}

function get_vendor_name($vendor_id) {
        $CI = & get_instance();
        $CI->load->model("vendor_model");
        $res = $CI->vendor_model->get_where(array("id" => $vendor_id),"name")->row_array();
        return displayData($res['name'],"humanize");
}

function get_dashboard_widgets()
{
       
    $CI = & get_instance();
  
    $result = $CI->widget_model->get_all_widgets()->result_array();
    
    $widgets = array();        
    foreach($result as $val)
    {
        $widgets[$val['id']] = $val;
    }
    return $widgets;
}


function get_menus()
{
	$CI = & get_instance();
	$current_class = strtolower($CI->router->fetch_class());	

	$uri_string = trim(strtolower( uri_string() ), '/').'/';

	//echo "$current_class $uri_string";die;

	$sql = "SELECT a.id, a.label, a.link, a.parent, Deriv1.Count 
     				FROM menu a 
     				LEFT JOIN (SELECT parent, COUNT(*) AS Count FROM menu GROUP BY parent) Deriv1 
     					ON a.id = Deriv1.parent 
     				order by sort ASC";
    $result = $CI->db->query($sql)->result_array();

    $menus = array();

    $sel_parent = 0;
    $sel_child  = 0;
    foreach ($result as $row) 
    {

    	$link = rtrim(strtolower($row['link']), '/').'/';

    	if(strpos($uri_string, $link) === 0 )
    	{
    		$sel_parent = $row['parent'];
    		$sel_child  = $row['id'];
    	}
    	else if(strpos($link, $current_class) === 0 && ! $sel_parent)
    	{
    		$sel_parent = $row['parent'];
    		$sel_child  = $row['id'];
    	}
    	
    	$menus[$row['parent']][] =  $row;
    }

    $CI->selected_parent = $sel_parent;
    $CI->selected_child  = $sel_child;

    $CI->menus = $menus;

    return $menus;
}

function round_amount( $amount = 0)
{
	return round( (float)$amount, 2);
}

function get_vendors( $where = array() )
{
	$CI = & get_instance();
	$CI->load->model('vendor_model');

	return $CI->vendor_model->get_vendors( $array_format = TRUE, $where );
}

function enabled_val_set($value){
    if(strtolower($value) == 'yes' || strtolower($value) == 'no'){
        return ucwords(strtolower($value));
    }
    else if($value == 0 || $value == "0"){
        return 'No';
    }
    else if($value == 1 || $value == "1"){
        return 'Yes';
    }
}
function check_fraudulent_ip(){
	$CI = & get_instance();
    $current_ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "SELECT so_id,ip_address FROM fraudulent_orders WHERE ip_address='$current_ip' AND status='1'";
    $result = $CI->db->query($sql)->row_array();

    if(count($result) && isset($result['ip_address']))
        return $result['so_id'];

    return 0;
}

function update_usermeta($key = '',$value = '',$user_id = '') {
    
    if(!$key || !$user_id)
        return false;
        
    $CI = & get_instance();    
    $CI->load->model('user_model');
    
    $meta_row = $CI->user_model->get_where(array('meta_key' => $key, 'user_id' => $user_id),"*",'usermeta');
    
    $data = $return_data = array();
    $data['meta_value'] = $value;
    $data['updated_id'] = getAdminUserId();
    $data['updated_time'] = date('Y-m-d', local_to_gmt());
    
    if($meta_row->num_rows() > 0){
        $meta_row_data = $meta_row->row_array();
        $return_data['prev_value'] = $meta_row_data['meta_value'];
        $CI->user_model->update(array('umeta_id' => $meta_row_data['umeta_id']),$data,'usermeta');
        $return_data['id'] = $meta_row_data['umeta_id'];
        $return_data['status'] =  "update";
        
    }
    else
    {
        $data['meta_key'] = $key;
        $data['user_id'] = $user_id;
        $data['created_id'] = getAdminUserId();
        $data['created_time'] = date('Y-m-d', local_to_gmt());
        $umeta_id = $CI->user_model->insert($data,'usermeta');
        $return_data['id'] = $umeta_id;
        $return_data['status'] =  "add";
    }
    
    return $return_data;
    
}

function get_usermeta($key = '',$user_id = '') {
    
    if(!$key || !$user_id)
        return false;
        
    $CI = & get_instance();    
    $CI->load->model('user_model');
    $meta_row = $CI->user_model->get_where(array('meta_key' => $key, 'user_id' => $user_id),"*",'usermeta');
      
    if($meta_row->num_rows() > 0){
        $meta_row_data = $meta_row->row_array();
    
        return $meta_row_data['meta_value'];
    }
    else
    {
        return false;
    }
}

function update_ordermeta($key = '',$value = '',$order_id = '') {
    
    if(!$key || !$order_id)
        return false;
        
    $CI = & get_instance();    
    $CI->load->model('user_model');
    
    $meta_row = $CI->user_model->get_where(array('meta_key' => $key, 'order_id' => $order_id),"*",'order_meta');
    
    $data = $return_data = array();
    $data['meta_value'] = $value;
    $data['updated_id'] = getAdminUserId();
    $data['updated_time'] = date('Y-m-d', local_to_gmt());
    
    if($meta_row->num_rows() > 0){
        $meta_row_data = $meta_row->row_array();
        $return_data['prev_value'] = $meta_row_data['meta_value'];
        $CI->user_model->update(array('ometa_id' => $meta_row_data['ometa_id']),$data,'order_meta');
        $return_data['id'] = $meta_row_data['ometa_id'];
        $return_data['status'] =  "update";
        
    }
    else
    {
        $data['meta_key'] = $key;
        $data['order_id'] = $order_id;
        $data['created_id'] = getAdminUserId();
        $data['created_time'] = date('Y-m-d', local_to_gmt());
        $ometa_id = $CI->user_model->insert($data,'order_meta');
        $return_data['id'] = $ometa_id;
        $return_data['status'] =  "add";
    }
    
    return $return_data;
    
}

function get_ordermeta($key = '',$order_id = '') {
    
    if(!$key || !$order_id)
        return false;
        
    $CI = & get_instance();    
    $CI->load->model('user_model');
    $meta_row = $CI->user_model->get_where(array('meta_key' => $key, 'order_id' => $order_id),"*",'order_meta');
      
    if($meta_row->num_rows() > 0){
        $meta_row_data = $meta_row->row_array();
    
        return $meta_row_data['meta_value'];
    }
    else
    {
        return false;
    }
}

function get_user_details_by_id($id){
    
    if(!$id){
        return false;
    }
    
    $CI = & get_instance();    
    $CI->load->model('user_model');
    $row = $CI->user_model->get_where(array('id' => $id),"*")->row_array();
    
    if(empty($row)){
        return false;
    }
    else
    {
        return $row;
    }
}

function get_adminuser_details_by_id($id)
{
    if(!$id){
        return false;
    }
    
    $CI = & get_instance();    
    $CI->load->model('admin_user_model');
    $row = $CI->admin_user_model->get_where(array('id' => $id),"*")->row_array();
    
    if(empty($row)){
        return false;
    }
    else
    {
        return $row;
    }
    
}
function get_payment_terms( $option = 'name' )
{
	$CI = & get_instance();
	$result = $CI->db->get('payment_terms')->result_array();

	$terms = array();
	foreach ($result as $row) 
	{
		$terms[$row['id']] = $row[$option];
	}

	return $terms;
}
function get_sales_rep($option = 'name')
{
    $CI = & get_instance();
	$result = $CI->db->get('sales_rep')->result_array();

	$reps = array();
	foreach ($result as $row) 
	{
		$reps[$row['id']] = $row[$option];
	}

	return $reps;
}
function str2USDate($str)
{
    $intTime = strtotime($str);
    if ($intTime === false)
         return NULL;
    return date("m/d/Y", $intTime);
}

    // no logic for server time to local time.
function str2DBDT($str)
{
    $intTime = strtotime($str);
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

function addDayswithdate($date,$days){

    $date = strtotime("+".$days." days", strtotime($date));
    return  date("m/d/Y", $date);

}

function day_to_text($date) {
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
    
    function date_range_options($sel ="",$display_for = ''){
        
        $selected = ($sel == 'all')?'selected=selected':'';
        $str = "<option value='all' $selected >All dates</option>";
        $selected = ($sel == 'custom')?'selected=selected':'';
        $str.="<option value='custom' $selected>Custom</option>";
       // 'thisweek' => 'This Week','thisweektodate' => 'This Week-to-date''thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter'
        if($display_for == "sales_report"){
            $cases = array('today' => 'Today','thisweek' => 'This Week','thisweektodate' => 'This Week-to-date','thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter','yesterday'=>'Yesterday','recent'=>'Recent','lastweek'=>'Last Week','lastweektodate'=>'Last Week To Date','lastmonth'=>'Last Month','lastmonthtodate'=>'Last Month To Date','lastquater'=>'Last Quater','lastquatertodate'=>'Last Quater To Date','lastyear'=>'Last Year','lastyeartodate'=>'Last Year To Date','since30days'=>'Since 30 Days','since60days'=>'Since 60 Days','since90days'=>'Since 90 Days','since350days'=>'Since 350 Days'); 
        }
        else if($display_for =="shipment_report") {
            $cases = array('today' => 'Today','thisweek' => 'This Week','thisweektodate' => 'This Week-to-date','thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter','yesterday'=>'Yesterday','recent'=>'Recent','lastweek'=>'Last Week','lastweektodate'=>'Last Week To Date','lastmonth'=>'Last Month','lastmonthtodate'=>'Last Month To Date','lastquater'=>'Last Quater','lastquatertodate'=>'Last Quater To Date','lastyear'=>'Last Year','lastyeartodate'=>'Last Year To Date','since30days'=>'Since 30 Days','since60days'=>'Since 60 Days','since90days'=>'Since 90 Days','since350days'=>'Since 350 Days');
        }
        else if($display_for == "invoice_report") {
            $cases = array('today' => 'Today','thisweek' => 'This Week','thisweektodate' => 'This Week-to-date','thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter','yesterday'=>'Yesterday','recent'=>'Recent','lastweek'=>'Last Week','lastweektodate'=>'Last Week To Date','lastmonth'=>'Last Month','lastmonthtodate'=>'Last Month To Date','lastquater'=>'Last Quater','lastquatertodate'=>'Last Quater To Date','lastyear'=>'Last Year','lastyeartodate'=>'Last Year To Date','since30days'=>'Since 30 Days','since60days'=>'Since 60 Days','since90days'=>'Since 90 Days','since350days'=>'Since 350 Days');
        }
        else
        { $cases = array('today' => 'Today','thisweek' => 'This Week','thisweektodate' => 'This Week-to-date','thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter','yesterday'=>'Yesterday','recent'=>'Recent','lastweek'=>'Last Week','lastweektodate'=>'Last Week To Date','lastmonth'=>'Last Month','lastmonthtodate'=>'Last Month To Date','lastquater'=>'Last Quater','lastquatertodate'=>'Last Quater To Date','lastyear'=>'Last Year','lastyeartodate'=>'Last Year To Date','since30days'=>'Since 30 Days','since60days'=>'Since 60 Days','since90days'=>'Since 90 Days','since350days'=>'Since 350 Days','nextweek'=>'Next Week','nextfourweeks'=>'Next Four Weeks','nextmonth'=>'Next Month','nextquater'=>'Next Quater','nextyear'=>'Next Year');}
       
        foreach($cases as $case => $label) {
           $date_ranges =  date_ranges($case);
           $selected = ($sel == $case)?'selected=selected':'';
           $str .= "<option value='$case' highdateval ='{$date_ranges['highdateval']}' $selected lowdateval = '{$date_ranges['lowdateval']}'>$label</option>";
        }
        return $str;
    }

   function get_commision_types()
   {
   		$commision_types = array(
   						'percent' => 'Percent',
   						'flat'	  => 'Flat'
   					);
   		
   		return $commision_types;
   		
   }
   
   function rpt_negative_format_set($val,$bright_red,$neg_no_type) {
    
    if($val < 0){
        
    switch($neg_no_type){
            case 'in_parentheses':
                    $return_val = "(".$val.")";
                break;
            case 'with_trailing_minus':
                    $return_val = abs($val)."-";
                break;
            default:
                $return_val = displayData($val,'money');
            break;
    
        }
        if($bright_red == "yes")
            $return_val = "<span style='color:red'>".$return_val."</span>";
    }
    else
    {
        $return_val = displayData($val,'money');
    }
    
    return $return_val;
    
   }
   function rpt_negative_format_set_excel($val,$neg_no_type) {
    
    if($val < 0){
        
    switch($neg_no_type){
            case 'in_parentheses':
                    $return_val = "(".$val.")";
                break;
            case 'with_trailing_minus':
                    $return_val = abs($val)."-";
                break;
            default:
                $return_val = displayData($val,'money');
            break;
    
        }
       
    }
    else
    {
        $return_val = displayData($val,'money');
    }
    
    return $return_val;
    
   }
   
   function trans_date_label($key){
    
       $cases = array('all'=>'ALL','today' => 'Today','thisweek' => 'This Week','thisweektodate' => 'This Week-to-date','thismonth' => 'This Month','thismonthtodate' => 'This Month-to-date','thisyear' => 'This Year','thisyeartodate' => 'This Year-to-date','thisquarter' => 'This Quarter','yesterday'=>'Yesterday','recent'=>'Recent','lastweek'=>'Last Week','lastweektodate'=>'Last Week To Date','lastmonth'=>'Last Month','lastmonthtodate'=>'Last Month To Date','lastquater'=>'Last Quater','lastquatertodate'=>'Last Quater To Date','lastyear'=>'Last Year','lastyeartodate'=>'Last Year To Date','since30days'=>'Since 30 Days','since60days'=>'Since 60 Days','since90days'=>'Since 90 Days','since350days'=>'Since 350 Days','nextweek'=>'Next Week','nextfourweeks'=>'Next Four Weeks','nextmonth'=>'Next Month','nextquater'=>'Next Quater','nextyear'=>'Next Year');
   
       if(isset($cases[$key]))
       {
        return $cases[$key];
       }
   }
   
   function get_billing_or_shipping_address($user_id = null, $type)
	{
	   	$CI = & get_instance();
		if(is_null($user_id) || !$user_id || !isset($type))
			return array();
		
		$CI->load->model('address_model');
		
		$result = $CI->address_model->get_address_by_userid($user_id, $type);
		
        return $result;

	
	}
    
    function xml_obj_to_array($xml_obj) {
        
            $json = json_encode($xml_obj,TRUE);
            $arr = json_decode($json,TRUE);
        
        return $arr;                     
    }
   
   function get_adminusers( $flag = FALSE )
    {
        $CI = & get_instance();
        $CI->db->select('*');
        $CI->db->from('admin_user');
        $result = $CI->db->get()->result_array();
       
        if( !$flag )
            return $result;
        
        $opt = array();
        foreach($result as $row)
        {
            $opt[$row['id']] = trim($row['first_name'].' '.$row['last_name']); 
        }
        
        return $opt;
    }

    function get_followup_uesr($followup,$followed_by){

        if(empty($followup) || $followup=='no')
            return FALSE;

        if(empty($followed_by))
            return FALSE;

        $followd_array = explode(",",$followed_by);

        if($followup=='yes' && in_array(getAdminUserId(),$followd_array)){
            return TRUE;
        }else{
            return FALSE;
        }
        
    }

    function get_followup_uesrlist($followed_by){

        if(empty($followed_by))
            return 'No followup users';


        $CI = & get_instance();
        $CI->db->select('group_concat(first_name) as user_names');
        $CI->db->from('admin_user');
        $CI->db->where_in('id',explode(",",$followed_by));
        $result = $CI->db->get()->result_array();

        if(!empty($result))
            return $result[0]['user_names'];
        else
            return 'No followup users';

    }

    function update_po_total($po_id = 0) {
    
        if(empty($po_id))
            return false;
            
        $CI = & get_instance();    
        $CI->load->model('purchase_model');
        $CI->load->model('purchase_order_prices_model');
        
        //get product details
        $product_details = $CI->purchase_model->get_po_item_details($po_id);

        $total = 0;
        foreach ($product_details as $row)
        {
            //for received quantity based total
            //$qty = check_received_qty($row['quantity'],$row['quantity_received']);
            $total += ($row['quantity']*$row['unit_price']);
        }

        $sub_total = (float)$total;

        //get additional/invoice charges
        //$reconciled_charges = $CI->purchase_order_prices_model->get_total_charge_by_po($po_id);

        //get Invoice fee
        $invoice_charges = $CI->purchase_order_prices_model->get_total_charge_by_po($po_id,array('Drop Ship/Handling','Shipping and Handling','Other'));

        //get additional fee
        $additional_charges = $CI->purchase_order_prices_model->get_total_charge_by_po($po_id,array("Inbound 3rd party UPS","Inbound 3rd party Fedex","Outbound to FBA","Other Charge"));
        
        //get drop-ship fee
        $dropship_fee = $CI->purchase_model->get_dropship_fee($po_id);

        $order_total = ($sub_total+$invoice_charges+$dropship_fee);


        $po_row = $CI->purchase_model->get_where(array('po_id' => $po_id),"po_id",'purchase_order_total');
        
        $data = array();
        $data['po_id']            = $po_id;
        $data['sub_total']        = round((float)$sub_total, 2);
        $data['dropship_fee']     = round((float)$dropship_fee, 2);
        $data['reconcile_charge'] = round((float)$invoice_charges, 2);
        $data['additional_charges'] = round((float)$additional_charges, 2);
        $data['order_total']      = round((float)$order_total, 2);
        
        if($po_row->num_rows() > 0){

            $po_data = $po_row->row_array();

            $data['updated_time'] = date('Y-m-d H:i:s', local_to_gmt());

            $CI->purchase_model->update(array('po_id'=>$po_id),$data,'purchase_order_total');
        }
        else
        {
           $data['created_time'] = date('Y-m-d H:i:s', local_to_gmt());

           $CI->purchase_model->insert($data,'purchase_order_total');

        }
        
        return true;
    
    }

    function check_received_qty($order_qty=0,$received_qty=0)
    {
        if($received_qty > 0)
            return $received_qty;

        return $order_qty;
    }

    function get_username_byId($id,$table='admin_user'){
        $CI = & get_instance();

        $CI->db->select('*');
        $CI->db->from($table);
        $CI->db->where('id',$id);
        $result = $CI->db->get()->row_array();

        if(count($result) > 0)
            return $result['first_name'];

        return 'HMS';
    }

    function random_generate_string($length = 8)
    {

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';

        // Length of character list
        $chars_length = (strlen($chars) - 1);

        // Start our string
        $string = $chars{rand(0, $chars_length)};
       
        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string))
        {
            // Grab a random character from our list
            $r = $chars{rand(0, $chars_length)};
           
            // Make sure the same two characters don't appear next to each other
            if ($r != $string{$i - 1}) $string .=  $r;
        }
       
        // Return the string
        return $string;
    }
