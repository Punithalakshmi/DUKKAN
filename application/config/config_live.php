<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
|  Depending on how you set it up on your system you may not need to change this.
*/
//$config['base_url']	= (empty($_SERVER['HTTPS'])?'http://':'https://').$_SERVER['HTTP_HOST'];

$root = (empty($_SERVER['HTTPS'])?'http://':'https://');

$host = $_SERVER['HTTP_HOST']."/";
if($_SERVER['HTTP_HOST']=='localhost')
 $host = 'localhost/DUKKAN/';


$config['base_url'] = $root.$host;

$config['base_path'] = $root.$host;

$config['permitted_uri_chars'] = 'a-zA-Z 0-9~%.:_\-@&,()+=';

$config['sess_cookie_name']		= 'ci_session_order_processing';


//$this->output->enable_profiler(true);

