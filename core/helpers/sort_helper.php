<?php 

if (!function_exists('sort_link')) {
	function sort_link($link_order, $text, $order, $cur_page, $direction, $default_direction)
	{
		/*
		 * 
		 *<th><a class="sorting" href='<?= $base_url ?><?= $cur_page ?>/name/<?= Listing::reverse_direction($order == 'name' ? $direction : $default_direction) ?>'>Name</a></th>
		*/
		
		return anchor($base_url .$cur_page ."/". $order ."/". Listing::reverse_direction($order == $link_order ? $direction : $default_direction), $text);
	}
	
}

?>