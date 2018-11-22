<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Layout {

    var $template = '';

    var $css_dir = '';
    var $js_dir = '';
    var $img_dir= '';
	var $amazion_img_dir = '';

    var $title = '';
    var $javascripts = array();
    var $stylesheets = array();
    var $metas = array();
    var $http_metas = array();
    var $html5_metas = array();
    var $links = array();
    var $raws = array();

    var $data = array();

    var $enabled = true;

    function __construct()
    {
        $this->CI =& get_instance();

        
    }

    /**
     * Controller
     *
     * @return Layout
     */
    /*
    function Layout()
    {
        $CI =& get_instance();

        if (isset($CI->config) && $CI->config->item('admin', 'layout')) {
            $this->initialize($CI->config->item('admin', 'layout'));
        }
    }
    */

    public function view($file_name, $type = 'default')
    {

        //$this->initialize($this->CI->config->item($type, 'layout'));
        $this->CI->data['content'] = $this->CI->load->view($file_name, $this->CI->data, TRUE);
        $this->CI->load->view($this->template, $this->CI->data);

        /*
        //load header
        $this->CI->load->view('admin/_partials/header.php', $this->CI->data);
        
        //load left menu
        $this->CI->load->view('admin/_partials/left_menu',$this->CI->data);

        //load page content
        $this->CI->load->view($file_name, $this->CI->data);

        //load footer
        $this->CI->load->view('admin/_partials/footer.php', $this->CI->data);
        */

    }

    /**
     * Controller
     *
     * @return Layout
     */
    function initialize($config = array())
    {
        foreach ($config as $key => $val)
        {
            if (isset($this->$key))
            {
                $method = 'set_'.$key;


                if (method_exists($this, $method))
                {
                    $this->$method($val);
                }
                else
                {
                    $this->$key = $val;
                }
            }
        }

        if (isset($config['keywords']))
        {
            $this->set_meta('keywords', $config['keywords']);
        }
        if (isset($config['description']))
        {
            $this->set_meta('description', $config['description']);
        }
    }

    /**
     * Enable/disable layouts. If arguments isn't passed then returns the current state.
     *
     * @param boolean $flag
     * @return null
     */
    function enabled($flag = null)
    {
        if ($flag !== null)
        {
            $old_flag = $this->enabled;
            $this->enabled = (bool) $flag;
            return $old_flag;
        }
        else
        {
            return $this->enabled;
        }
    }

    /**
     * Set a layout template name
     *
     * @param string $template template name
     */
    function set_layout($template)
    {
        $this->template = $template;
    }

    /**
     * Returns a layout template name
     *
     * @return string
     */
    function get_layout()
    {
        return $this->template;
    }

    /**
     * Assigns a variable for a layout template
     *
     * @param string $name
     * @param mix $value
     *
     */
    function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Returns value of a variable for a layout template
     *
     * @param string $name
     * @return mix
     */
    function get($name)
    {
        return $this->data[$name];
    }

    /**
     * Returns all variables for a layout template in an associative array
     *
     * @return array
     */
    function get_all()
    {
        return $this->data;
    }

    /**
     * Sets content of the <title> tag
     *
     * @param string $title
     */
    function set_title($title)
    {
        $this->title = $title;
    }

    /**
     * Returns content of the <title> tag
     *
     * @return string
     */
    function get_title()
    {
        return $this->title;
    }

    /**
     * Sets JavaScripts. The $javascripts param must be an array of file names without paths and extensions
     *
     * @param array $javascripts
     */
    function set_javascripts($javascripts)
    {
        $javascripts = empty($javascripts) ? array() : (array) $javascripts;
        $this->javascripts = $javascripts;
    }

    /**
     * Sets JavaScripts. The $javascripts param must be an array of file names
     * or a string contains a file name (in case if we need to set only one script).
     * File names must be without paths and extensions.
     *
     * @param array $javascripts
     */
    function add_javascripts($javascripts)
    {
        $javascripts = (array) $javascripts;
        $this->javascripts = array_merge($this->javascripts, $javascripts);
    }

    /**
     * Removes all JS scripts
     *
     */
    function clean_javascripts()
    {
        $this->javascripts = array();
    }

    /**
     * Returns names of JavaScripts.
     *
     * @return array
     */
    function get_javascripts()
    {
        return $this->javascripts;
    }

    /**
     * Set the directory for JavaScripts
     *
     * @param string $js_dir
     */
    function set_js_dir($js_dir)
    {
        $this->js_dir = trim($js_dir, ' /');
    }

    /**
     * Return the directory for JavaScripts
     *
     * @param string $js_dir
     */
    function get_js_dir()
    {
        return $this->js_dir;
    }

    /**
     * Sets stylesheets. The $stylesheets param must be an array of file names
     * or a string contains a file name (in case if we need to set only one script).
     * File names must be without paths and extensions.
     * If we need to set media attribute with a value different from 'all',
     * then it can be defined after the file name separated with '|'.
     * For example: array('style', 'print_style|print', 'screen_style|screen')
     *
     * @param array $stylesheets
     */
    function set_stylesheets($stylesheets)
    {
        $this->clean_stylesheets();
        $this->add_stylesheets($stylesheets);
    }

    /**
     * Adds stylesheets without removing existing values.
     * The $stylesheets param must be an array of file names
     * or a string contains a file name (in case if we need to set only one script).
     * File names must be without paths and extensions.
     * If we need to set media attribute with a value different from 'all',
     * then it can be defined after the file name separated with '|'.
     * For example: array('style', 'print_style|print', 'screen_style|screen')
     *
     * @param array $stylesheets
     */
    function add_stylesheets($stylesheets)
    {
        $stylesheets = empty($stylesheets) ? array() : (array) $stylesheets;

        foreach ($stylesheets as $item)
        {
            $parts = explode('|', $item);
            $file_name = $parts[0];
            $media = isset($parts[1]) ? $parts[1] : null;
			$media = (empty($media))?('all'):($media);
            $this->add_stylesheet($file_name, $media);
        }
    }

    /**
     * Adds a stylesheet
     *
     * @param string $file_name
     * @param string $media
     */
    function add_stylesheet($file_name, $media = 'all')
    {
        $this->stylesheets[] = array(
            'file_name' => $file_name,
            'media' => $media,
        );
    }

    /**
     * Removes all stylesheets
     *
     */
    function clean_stylesheets()
    {
        $this->stylesheets = array();
    }

    /**
     * Returns all stylesheets
     *
     * @return array
     */
    function get_stylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * Set the directory for stylesheets
     *
     * @param string $css_dir
     */
    function set_css_dir($css_dir)
    {
        $this->css_dir = trim($css_dir, ' /');
    }

    /**
     * Return the directory for stylesheets
     *
     * @param string $css_dir
     */
    function get_css_dir()
    {
        return $this->css_dir;
    }

    /**
     * Return the directory for images
     *
     * @param string $css_dir
     */
    function get_img_dir()
    {
        return $this->img_dir;
    }
	
	/**
     * Return the directory for images
     *
     * @param string $css_dir
     */
    function get_amazion_img_dir()
    {
        return $this->amazion_img_dir;
    }

    /**
     * Sets keywords
     *
     * @param string $keywords
     */
    function set_keywords($keywords)
    {
        $this->metas['keywords'] = $keywords;
    }

    /**
     * Adds keywords without removing existing values.
     *
     * @param string $keywords
     * @param string $sprt
     */
    function add_keywords($keywords, $sprt = ' ')
    {
        $this->metas['keywords'] = (isset($this->metas['keywords']) ? ($this->metas['keywords'] . $sprt) : '') . $keywords;
    }

    /**
     * Removes all keywords
     *
     */
    function clean_keywords()
    {
        if (isset($this->metas['keywords']))
        {
            unset($this->metas['keywords']);
        }
    }

    /**
     * Returns keywords
     *
     */
    function get_keywords()
    {
        return isset($this->metas['keywords']) ? $this->metas['keywords'] : null;
    }

    /**
     * Set all meta tags. The $metas param must be an associative array of name => content pairs
     * For example: array('robots' => 'INDEX, FOLLOW', 'title' => 'Project title')
     *
     * @param array $metas
     */
    function set_metas($metas)
    {
        $metas = empty($metas) ? array() : (array) $metas;
        $this->metas = $metas;
    }

    /**
     * Set single meta tag.
     *
     * @param string $name
     * @param string $content
     */
    function set_meta($name, $content)
    {
        $this->metas[$name] = $content;
    }

    /**
     * Append a given $content to existing one of meta tag $name
     *
     * @param string $name
     * @param string $content
     */
    function add_meta($name, $content, $sprt = ' ')
    {
        $this->metas[$name] = (isset($this->metas[$name]) ? $this->metas[$name] : '') . $sprt . $content;
    }

    /**
     * Removes all meta tags
     *
     */
    function clean_metas($name = null)
    {
        if ($name !== null)
        {
            unset($this->metas[$name]);
        }
        else
        {
            $this->metas = array();
        }
    }

    /**
     * Returns all meta tags
     *
     * @param unknown_type $name
     * @return unknown
     */
    function get_metas($name = null)
    {
        if ($name !== null)
        {
            return isset($this->metas[$name]) ? $this->metas[$name] : null;
        }

        return $this->metas;
    }

    /**
     * Set all http-equiv meta tags. The $metas param must be an associative array of name => content pairs
     * For example: array('robots' => 'INDEX, FOLLOW', 'title' => 'Project title')
     *
     * @param array $metas
     */
    function set_http_metas($metas)
    {
        $metas = empty($metas) ? array() : (array) $metas;
        $this->http_metas = $metas;
    }

    /**
     * Set single meta http-equiv tag.
     *
     * @param string $name
     * @param string $content
     */
    function set_http_meta($name, $content)
    {
        $this->http_metas[$name] = $content;
    }

    /**
     * Append a given $content to existing one of meta http-equiv tag $name
     *
     * @param string $name
     * @param string $content
     */
    function add_http_meta($name, $content, $sprt = ', ')
    {
        $this->metas[$name] = (isset($this->metas[$name]) ? $this->metas[$name] : '') . $sprt . $content;
    }

    /**
     * Removes all meta http-equiv tags
     *
     */
    function clean_http_metas($name = null)
    {
        if ($name !== null)
        {
            unset($this->http_metas[$name]);
        }
        else
        {
            $this->http_metas = array();
        }
    }

    /**
     * Returns all meta http-equiv tags
     *
     */
    function get_http_metas($name = null)
    {
        if ($name !== null)
        {
            return isset($this->http_metas[$name]) ? $this->http_metas[$name] : null;
        }

        return $this->http_metas;
    }


    /**
     * Set all http-equiv meta tags. The $metas param must be an associative array of name => content pairs
     * For example: array('robots' => 'INDEX, FOLLOW', 'title' => 'Project title')
     *
     * @param array $metas
     */
    function set_html5_metas($metas)
    {
        $metas = empty($metas) ? array() : (array) $metas;
        $this->html5_metas = $metas;
    }

    /**
     * Set single meta http-equiv tag.
     *
     * @param string $name
     * @param string $content
     */
    function set_html5_meta($name, $content)
    {
        $this->html5_metas[$name] = $content;
    }

    /**
     * Append a given $content to existing one of meta http-equiv tag $name
     *
     * @param string $name
     * @param string $content
     */
    function add_html5_meta($name, $content, $sprt = ', ')
    {
        $this->metas[$name] = (isset($this->metas[$name]) ? $this->metas[$name] : '') . $sprt . $content;
    }

    /**
     * Removes all meta http-equiv tags
     *
     */
    function clean_html5_metas($name = null)
    {
        if ($name !== null)
        {
            unset($this->html5_metas[$name]);
        }
        else
        {
            $this->html5_metas = array();
        }
    }

    /**
     * Returns all meta http-equiv tags
     *
     */
    function get_html5_metas($name = null)
    {
        if ($name !== null)
        {
            return isset($this->html5_metas[$name]) ? $this->html5_metas[$name] : null;
        }

        return $this->html5_metas;
    }

    /**
     * Set all <link> tags. The $links param must be an array of associative arrays with attributes
     * for links (arrtibute_name => arrtibute_value pairs).
     *
     * @param array $links
     */
    function set_links($links)
    {
        $links = empty($links) ? array() : (array) $links;

        $this->links = $links;
    }

    /**
     * Add a <link> tag. Takes associative array of arrtibute_name => arrtibute_value pairs
     *
     * @param array $attr
     */
    function add_link($attr)
    {
        $this->links[] = $attr;
    }

    /**
     * Removes all <link> tags
     *
     */
    function clean_links()
    {
        $this->links = array();
    }

    /**
     * Returns all links
     *
     * @return array
     */
    function get_links()
    {
        return $this->links;
    }

    /**
     * Sets all raw tags for <head> element
     *
     * @param array $raw
     */
    function set_raws($raw)
    {
        $raws = empty($raw) ? array() : (array) $raw;

        $this->raws = $raw;
    }

    /**
     * Adds a raw tag for <head> element.
     *
     * @param array $raw
     */
    function add_raw($raw)
    {
        $this->raws[] = $raw;
    }

    /**
     * Removes all raw tags
     *
     */
    function clean_raws()
    {
        $this->raws = array();
    }

    /**
     * Returns all raw tags
     *
     * @return array
     */
    function get_raws()
    {
        return $this->raws;
    }

    /**
     * Method for placing in display_override hook.
     *
     * Example:
     * <pre>
     * $hook['display_override'][] = array(
     *     'class'    => 'Layout',
     *     'function' => 'execute',
     *     'filename' => 'Layout.php',
     *     'filepath' => 'libraries',
     *     'params'   => array()
     * );
     * </pre>
     */
    function execute()
    {
        $CI =& get_instance();

        if (!isset($CI->layout) || !$CI->layout->enabled())
        {
            // skip layout output
            if (isset($CI->output)) {
                $CI->output->_display();
            }
            return;
        }

        // layout instance in the current controller
        $layout = $CI->layout;

        $content = $CI->output->get_output();
        $CI->output->set_output('');

        $data = $layout->get_all();
        $data['content'] = $content;

        $CI->load->view($layout->get_layout(), $data);
        $CI->output->_display();
    }
}

?>
