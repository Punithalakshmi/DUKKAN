<?php

//

// Layout config for the site admin 

//

$config['layout']['default']['template'] = 'layouts/frontend';

$config['layout']['default']['title']    = 'Punchlist App - Surefire Punchlist';

$config['layout']['default']['js_dir']   = "assets/js/admin";

$config['layout']['default']['css_dir']  = "assets/css/admin";

$config['layout']['default']['img_dir']  = "assets/img";

$config['layout']['default']['javascripts'] = array('jquery.min','bootstrap.min','metronic','layout','jquery.fancybox.min','jquery.plugin','jquery.realperson','select2.min','function');

$config['layout']['default']['stylesheets'] = array('bootstrap.min','font-awesome.min','components.min','layout','darkblue','custom','login','jquery.fancybox.min','jquery.realperson','select2.min');

$config['layout']['default']['description'] = 'Affordable, Easy to Use, Mobile Punchlist App. for General Contractors, Project Managers, Superintendents and Subcontractors';

$config['layout']['default']['keywords']    = 'punchlist app, punch list app, surefire punchlist, surefire punch list, mobile punchlist software, mobile punchlist app';

$config['layout']['default']['http_metas'] = array(

	'X-UA-Compatible' => 'IE=edge',

  'Content-Type' => 'text/html; charset=utf-8',

	'viewport'     => 'width=device-width, initial-scale=1.0',

  'author' => 'Surefire Punchlist');

?>