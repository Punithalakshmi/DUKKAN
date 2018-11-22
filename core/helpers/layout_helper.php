<?php

function include_title($title='')
{
    if (!($layout =& get_layout())) return;

    //echo '<title>' . $layout->get_title() . '</title>';
    $title = (!empty($title))? $title:$layout->get_title();
    echo '<title>' . $title . '</title>';
}

function include_img_path() {
  if (!$layout =& get_layout()) return;
  $CI =& get_instance();

  $img_dir = base_path();
  $img_dir .= $CI->layout->get_img_dir();

  return $img_dir;


}

function include_amazon_img_path() {
  if (!$layout =& get_layout()) return;
  $CI =& get_instance();

  return $CI->layout->get_amazion_img_dir();


}

function include_javascripts()
{
    if (!$layout =& get_layout()) return;
    $CI =& get_instance();
    
    foreach ($layout->get_javascripts() as $file_name)
    {
        if (preg_match("/^(http)|(https)/i", $file_name)) {
            $href = $file_name;
        } else {
            $href =  base_path();
            $href .= $CI->layout->get_js_dir() ? $CI->layout->get_js_dir() . '/' : '';
            $href .= $file_name . '.js';
        }
        echo '<script type="text/javascript" src="' . $href . '"></script>' . "\n";
    }
}

function include_stylesheets()
{
    if (!$layout =& get_layout()) return;
    $CI =& get_instance();
    
    foreach ($layout->get_stylesheets() as $item)
    {
        if (preg_match("/^(http)|(https)/i", $item['file_name'])) {
            $href = $item['file_name'];
        } else {
        	if (preg_match("/^(http)|(https)/i",$CI->layout->get_css_dir() )) {
        		$href = $CI->layout->get_css_dir() ? $CI->layout->get_css_dir() . '/' : '';
        		$href .= $item['file_name'] . '.css';
        	} else {
            		
            		$href = base_path();
            		$href .= $CI->layout->get_css_dir() ? $CI->layout->get_css_dir() . '/' : '';
            		$href .= $item['file_name'] . '.css';
            }
        }
    
        echo '<link rel="stylesheet" type="text/css"  href="' . $href  . '" media="' . $item['media'] . '"/>' . "\n";
    }
}

function include_metas()
{
    if (!$layout =& get_layout()) return;

    foreach ($layout->get_metas() as $name => $meta_content)
    {
        echo '<meta name="' . $name . '" content="' . $meta_content . '" />' . "\n";
    }
    foreach ($layout->get_http_metas() as $name => $meta_content)
    {
        echo '<meta http-equiv="' . $name . '" content="' . $meta_content . '" />' . "\n";
    }
    foreach ($layout->get_html5_metas() as $meta_content)
    {
        $str = '';
        if(is_array($meta_content))
        {
            $str .= "<meta ";
            foreach ($meta_content as $name => $value) 
            {
               $str .= $name.'="'.$value.'" ';
            }
            $str .= " />\n";
        }
        echo $str;
    }
}

function include_links()
{
    if (!$layout =& get_layout()) return;

    foreach ($layout->get_links() as $arributes)
    {
        $link = '<link ';    
        foreach ($attributes as $name => $value)
        {
            $link .= $name . '="' . $value . '" ';
        }
        $link .= '/>' . "\n";
         
        echo $link;
    }
}

function include_raws()
{
    if (!$layout =& get_layout()) return;

    foreach ($layout->get_raws() as $raw)
    {
        echo $raw . "\n";
    }
}

function &get_layout()
{
    $CI =& get_instance();
        
    if (!isset($CI->layout))
    {
        return false;
    }
    return $CI->layout;
}

/**
* The goal of this function is to display the path of the current view file as a html comment 
* at the first and last line of all view files (not layout files).
* 
* - The helper function should take in a single parameter which is a full path to a view file
* - The function should return an html comment that contains that path relative to the view folder
*/
function get_view_path($path)
{
	
	$CI = & get_instance();

	$appsPath = dirname(dirname(dirname(__FILE__))).'/apps/';

	$appViewParent = basename(dirname($CI->load->_ci_view_path));

	$appPath = ($appsPath . $appViewParent);

	return '<!-- view: ' . str_replace($appPath, '', $path) . '-->';

}


function get_global_messages(){
         
         $CI = & get_instance();
         $CI->config->load('support');
         $sales_channel_id = $CI->config->item('sales_channel_id', 'support');
         $email_schedulers = $CI->db->query("select *,et.content  from email_scheduler es join email_template et on es.template_id = et.id where es.channel_id = $sales_channel_id AND es.type = 'GLOBAL_MESSAGE' AND status != 'COMPLETED' AND start_date <= now() AND end_date >= now()")->result_array();

        $str ="";
         if(!empty($email_schedulers)){
            
              $str = "<div class ='panel'>";
            foreach($email_schedulers as $message){
                
                //generating script to hide the global message with in the time limit
                
                $dif_time = strtotime($message['end_date']) - strtotime(date('Y-m-d H:i:s', local_to_gmt()));
                $diff_time_in_ms = $dif_time*1000;
                $str .="<script>
                         $(document).ready(function() {
                            var t=setTimeout(function()
                            {
                                $('#global_message_".$message['id']."').hide();
                                },".$diff_time_in_ms.");
                            });
                </script>";

               $str .="<div id='global_message_".$message['id']."'>".$message['content']."</div>";
            }
            $str .="</div><a href='#' class='trigger'>infos</a>";
         }
         
                  
         echo $str;
    }


    function get_settings($channel_id = 0, $type = 'all', $autoload = 'all', $key = NULL)
    {
    	$CI = & get_instance();
    
    	if($channel_id == 0)
    		return FALSE;
    
    	$where = array('channel_id' => $channel_id);
    	if(strcmp($type, 'all') !== 0)
    		$where['type'] = $type;
    	if(strcmp($autoload, 'all') !== 0)
    		$where['autoload'] = $autoload;
        

    
    	$CI->db->where($where);
    	$result = $CI->db->get('settings')->result_array();
    
    	$settings = array();
    	foreach ($result as $row)
    	{
    		if(strcmp($type, 'all') === 0)
    			$settings[$row['type']][$row['name']] = $row['value'];
    		else
    			$settings[$row['name']] = $row['value'];
    	}
        
        if(!is_null($key) && isset($settings[$key]))
            return $settings[$key];

    	return $settings;
    }
?>
