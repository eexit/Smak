<?php
/*
*   ###########
*   #__________#
*   __________#
*   ________#
*   _____###_____²xiT development
*   _________#
*   ___________#
*   #__________#
*   _#________#
*   __#______#
*   ____####
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
* IN THE SOFTWARE.
*/

class Smak_Exception extends Exception {}

/**
*   @author     Joris Berthelot <admin@eexit.net>
*   @copyright  Copyright (c) 2009, Joris Berthelot
*   @version    1.10
*/
class Smak {
    
    /**
     *  The GET param where templates are redirected
     *  See the Rewrite Rule
     *  @since 1.00
     *  @version 1.00
     */
    const REWRITE_PARAM = 'tpl';
    
    /**
     *  The RegEx to validate URL
     *  Here it allows URL like: foo-bAr/baZ_12
     *  @since 1.00
     *  @version 1.00
     */
    const URL_PATTERN = '/^[[:alnum:]-\/_]+$/i';
    
    /**
     *  The menu generation list element pattern
     *  %target% is the link content
     *  %title% is the link title
     *  %class% is used to set {@link NAV_CURRENT_CSS_CLASS}
     *  %label% is the link text content
     *  @since 1.00
     *  @version 1.01
     */
    const NAV_LI_PATTERN = '<li><a href="%target%" title="%title%" 
                            %class%>%label%</a></li>';
    
    /**
     *  The CSS class which is added to the menu list item when the current
     *  template is a list item or sub list item (in page tree)
     *  @since 1.00
     *  @version 1.01
     */
    const NAV_CURRENT_CSS_CLASS = 'class="current"';
    
    /**
     *  The script execution include path. When a Smak object is created
     *  the new include path goes here.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_inc_path;
    
    /**
     *  The requested template (the result of GET param)
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_tpl_req;
    
    /**
     *  The template file extension. Could be set by {@link setTplExt()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_tpl_ext = '.tpl';
    
    /**
     *  The template file path. Could be set by{@link setTplPath()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_tpl_path = 'tpl/';
    
    /**
     *  The side template file path. Could be set by {@link setTplSidePath()}
     *  method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_tpl_side_path = 'tpl/side/';
    
    /**
     *  The default template to include in index page.
     *  You could set this value using {@link setTplDefault()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_tpl_default = 'index';
    
    /**
     *  The shell file extension. Could be set by {@link setShlExt()} method.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     */
    protected $_shl_ext = '.shl';
    
    /**
     *  The shell file path. Could be set by {@link setShlPath()} method.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     */
    protected $_shl_path = 'shl/';
    
    /**
     *  The default shell to apply.
     *  You could set this value using {@link setShlDefault()} method.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     */
    protected $_shl_default = 'default';
    
    /**
     *  The title format. It must contain %title% keyword which is
     *  replaced by the template title (which is in fact the file name).
     *  Could be set by {@link setTitleFormat()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_title_format = '%title%';
    
    /**
     *  The index template title. Title is usually generated by the requested
     *  template. On index page, there is no template request it needs to have
     *  a default title. Additionally, it could be useful to custom only index
     *  title.
     *  Could be set by {@link setTitleIndex()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_title_index = 'Homepage';
    
    /**
     *  The title separator. When a sub(-sub..)-template is loaded, the title
     *  is generated from template name. The breadcrumb use this pattern to
     *  seperate template names.
     *  Could be set by {@link setTitleTplSeparator()} method.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     */
    protected $_title_tpl_separator = ' &raquo; ';
    
    /**
     *  The navigation links goes here once the navigation JSON file is loaded
     *  by {@link loadNav()} method.
     *  @since 1.00
     *  @version 1.03
     *  @access protected
     */
    protected $_nav = null;
    
    /**
     *  The plugins stack. Contains all loaded plugins. This attribute is
     *  managed by {@link registerPlugin()} and {@link unregisterPlugin()}
     *  methods. It content is reachable directly via {@link __get()}
     *  magic method.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     */
    protected $_plugins = array();
    
    /**
     *  Class constructor. When called, it inits the new include path and
     *  retrieves the requested template. See {@link $_inc_path}.
     *  @since 1.00
     *  @version 1.02
     *  @access public
     *  @param [string $inc_path = './../inc' The default include path]
     */
    public function __construct($inc_path = './../inc') {
        try {
            if (is_dir($inc_path)
                && ini_set('include_path', $inc_path . PATH_SEPARATOR . '.'))
            {
                $this->_inc_path = $inc_path . DIRECTORY_SEPARATOR;
                $this->_getRequestedTpl();
            } else {
                throw new Smak_Exception(
                    'Unable to set the new include directory &laquo; '
                    . $inc_path
                    . ' &raquo;!');
            }
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  This magic method is used to retrieve a plugin instance which is stocked
     *  in {@link $_plugins} attribute.
     *  Note: All plugin name are formatted with CamelCase case
     *  (e.g. class FooBar will be formatted as fooBar).
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $plugin_name The wanted plugin instance name
     *  @return bool
     */
    public function __get($plugin_name) {
        try {
            if (isset($plugin_name)) {
                return $this->_plugins[$plugin_name];
            }
            throw new Smak_Exception('There is no registered plugin &laquo; '
                . $plugin_name
                . ' &raquo; in the plugins stack!');
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  This magic methog checks if a plugin is loaded.
     *  Note: All plugin name are formatted with CamelCase case
     *  (e.g. class FooBar will be formatted as fooBar).
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $plugin_name The plugin name to test
     *  @return bool
     */
    public function __isset($plugin_name) {
        return array_key_exists($plugin_name, $this->_plugins);
    }
    
    /**
     *  Sets the new template path.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $path A template path
     *  @return object $this The current class instance
     */
    public function setTplPath($path) {
        return $this->_setDir($this->_tpl_path, $path);
    }
    
    /**
     *  Sets the new side template path.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $path A side template path
     *  @return object $this The current class instance
     */
    public function setTplSidePath($path) {
        return $this->_setDir($this->_tpl_side_path, $path);
    }
    
    /**
     *  Sets the new default template name.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $tpl_name A template name
     *  @return object $this The current class instance
     */
    public function setTplDefault($tpl_name) {
        return $this->_setDefault('tpl', $tpl_name);
    }
    
    /**
     *  Sets the new template file extension.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $tpl_ext A file extention
     *  @return object $this The current class instance
     */
    public function setTplExt($tpl_ext) {
        return $this->_setExt($this->_tpl_ext, $tpl_ext);
    }
    
    /**
     *  Gets the current body template.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param [string $tpl_name A template name]
     */
    public function getBodyTpl($tpl_name = null) {
        $this->_getTpl($this->_tpl_path, $tpl_name);
    }
    
    /**
     *  Gets the current side template.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param [string $tpl_name A side template name]
     */
    public function getSideTpl($tpl_name = null) {
        $this->_getTpl($this->_tpl_side_path, $tpl_name);
    }
    
    /**
     *  Loads the JSON menu file.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $nav_json_file JSON menu path + filename
     *  @return object $this The current class instance
     */
    public function loadNav($nav_json_file) {
        $this->_nav = $this->_loadJson($nav_json_file);
        return $this;
    }
    
    /**
     *  Sets the new shell path.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $path A shell path
     *  @return object $this The current class instance
     */
    public function setShlPath($path) {
        return $this->_setDir($this->_shl_path, $path);
    }
    
    /**
     *  Sets the new default shell name.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $shl_name A shell name
     *  @return object $this The current class instance
     */
    public function setShlDefault($shl_name) {
        return $this->_setDefault('shl', $shl_name);
    }
    
    /**
     *  Sets the new shell file extension.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $shl_ext A file extension
     *  @return object $this The current class instance
     */
    public function setShlExt($shl_ext) {
        return $this->_setExt($this->_shl_ext, $shl_ext);
    }
    
    /**
     *  Sets the new default index template title. See {@link $_title_index}
     *  property for more details.
     *  @since 1.00
     *  @version 1.02
     *  @access public
     *  @param string $title The new title
     *  @return object $this The current class instance
     */
    public function setTitleIndex($title) {
        $this->_title_index = htmlentities($title);
        return $this;
    }
    
    /**
     *  Sets the title format pattern. This pattern is used to display your 
     *  template title. See {@link $_title_format}.
     *  @since 1.00
     *  @version 1.02
     *  @access public
     *  @param string $pattern The title pattern
     *  @return object $this The current class instance
     */
    public function setTitleFormat($pattern) {
        try {
            if (strstr($pattern, '%title%')) {
                $this->_title_format = htmlentities($pattern);
                return $this;
            }
            throw new Smak_Exception(
                'Unable to find the title pattern &laquo; %title% &raquo;
                in the new title format!');
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Sets the title template separator pattern. When title is generated from
     *  template tree, all template name are separated by this pattern.
     *  See {@link $_title_tpl_separator}.
     *  @since 1.00
     *  @version 1.02
     *  @access public
     *  @param string $separator The new sperator
     *  @return object $this The current class instance
     */
    public function setTitleTplSeparator($separator) {
        $this->_title_tpl_separator = htmlentities($separator);
        return $this;
    }
    
    /**
     *  Generates the navigation menu. The only one parameter is used if you
     *  need to insert some custom HTML attributes (like id or class).
     *  List item pattern is available here: {@link NAV_LI_PATTERN}.
     *  @since 1.00
     *  @version 1.10
     *  @access public
     *  @param [array $html_attr = null Custom list container HTML attributes]
     *  @return string $output The final HTML generated menu
     */
    public function getNav(array $html_attr = null) {
        if (empty($html_attr)) {
            $output = '<ul>' . "\n\t";
        } else {
            $output = '<ul ';
            
            // Loops to add HTML attributes
            foreach ($html_attr as $attr) {
                $output .= $attr .' ';
            }
            
            $output = substr($output, 0, -1) . '>' . "\n\t";
        }
        
        // Loops on nav items
        foreach ($this->_nav as $nav_item) {
            
            $css_class = property_exists($nav_item, 'tpl')
                         && $this->_isCurrentTpl($nav_item)
                         ? self::NAV_CURRENT_CSS_CLASS
                         : null;
            
            $target = property_exists($nav_item, 'tpl')
                      ? '/' . $nav_item->tpl
                      : $nav_item->url;
            
            $output .= str_replace(
                array('%target%', '%title%', '%class%', '%label%'),
                array($target, $nav_item->title, $css_class, $nav_item->label),
                self::NAV_LI_PATTERN)
                . "\n\t";
        }
        return substr($output, 0, -1) . "</ul>";
    }
    
    /**
     *  Generates the title from the current template. See
     *  {@link $_title_index}, {@link $_title_format} and
     *  {@link $_title_tpl_separator}.
     *  @since 1.00
     *  @version 1.03
     *  @access public
     *  @return string $output The final template title
     */
    public function getTplTitle() {
        if (is_null($this->_tpl_req)) {
            return str_replace('%title%'
                , $this->_title_index
                , $this->_title_format);
        }
        
        $words = explode('/', $this->_tpl_req);
        
        count($words) > 1 && in_array('index', $words)
        ? array_pop($words)
        : null;
        
        foreach ($words as &$word) {
            $word = ucfirst($word);
        }
        
        return str_replace('%title%'
            , implode($this->_title_tpl_separator, $words)
            , $this->_title_format);
    }
    
    /**
     *  This method allows to load a plugin into Smak. Very useful to add
     *  application features.
     *  Note: All plugin name are formatted with CamelCase case
     *  (e.g. class FooBar will be formatted as fooBar).
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param object $plugin_instance An instance of a plugin class
     *  @return object $this The current class instance
     */
    public function registerPlugin($plugin_instance) {
        $plugin_name = $this->_lcFirst(get_class($plugin_instance));
        
        if (!$this->__isset($plugin_name)) {
            $this->_plugins[$plugin_name] = $plugin_instance;
        }
        return $this;
    }
    
    /**
     *  This method allows to unload a plugin from Smak.
     *  Note: All plugin name are formatted with CamelCase case
     *  (e.g. class FooBar will be formatted as fooBar).
     *  @since 1.10
     *  @version 1.00
     *  @access public
     *  @param string $plugin_name The plugin name to unload
     *  @return object $this The current class instance
     */
    public function unregisterPlugin($plugin_name) {
        if (isset($plugin_name)) {
            unset($this->_plugins[$plugin_name]);
        }
        return $this;
    }
    
    /**
     *  This method is the most important: it compiles all the templates
     *  and return the generated content to the front page.
     *  @since 1.10
     *  @version 1.00
     *  @access public
     */
    public function render() {
        $final_shl = $this->_getShlCustom();
        
        try {
            ob_start();
            if (is_file($this->_inc_path
                . $this->_shl_path
                . $final_shl
                . $this->_shl_ext))
            {
                require_once($this->_shl_path
                . $final_shl
                . $this->_shl_ext);
            } else {
                throw new Smak_Exception('Unable to load the shell &laquo; '
                    . $final_shl
                    . $this->_shl_ext
                    . ' &raquo;!');
            }
            ob_end_flush();
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  String formatter to lowercase the first letter if uppercased.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     *  @param string $input The string to convert
     *  @return string The formatted string
     */
    protected function _lcFirst($input) {
        return preg_replace('/^([A-Z])+/e'
            , 'strtolower($1)'
            , $input);
    }
    
    /**
     *  Loads a given JSON file and returns it content as object.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     *  @param string $file The path and the name to the JSON file
     *  @param [bool $assoc = false Associative array]
     *  @return object $json The converted JSON file
     */
    protected function _loadJson($file, $assoc = false) {
        try {
            if (is_file($this->_inc_path . $file)) {
                $json = json_decode(file_get_contents(
                    $this->_inc_path . $file), $assoc);
                if (!$json) {
                    throw new Smak_Exception(
                        'Unable to parse &laquo; '
                        . $file
                        . ' &raquo; JSON file!');
                }
                return $json;
            }
            throw new Smak_Exception(
                'Unable to load &laquo; ' . $file . ' &raquo; JSON file!');
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Gets the shell to execute. Loops on nav items to check if the
     *  current requested template has a custom shell and return it if true.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     *  @return string A shell template name
     */
    protected function _getShlCustom() {
        try {
            if (!is_null($this->_nav)) {
                foreach ($this->_nav as $nav_item) {
                    if ($this->_isCurrentTpl($nav_item)
                        && property_exists($nav_item, 'shl'))
                    {
                        return $nav_item->shl;
                    }
                }
                return $this->_shl_default;
            } else {
                throw new Smak_Exception('Navigation file not found.
                Please check you well called '
                . get_class($this)
                . '::loadNav() before the rendering!');
            }
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Sends a HTTP code if the template is not found or other custom HTTP
     *  code. It's followed by a redirection to the / because the HTTP is
     *  generated from a normal page.
     *  @since 1.00
     *  @version 1.02
     *  @access protected
     *  @param mixed $code Wanted HTTP code
     */
    protected function _sendHttpCode($code) {
        header('HTTP/1.0 '. $code); 
        exit;
    }
    
    /**
     *  Directory internal setter method.
     *  @since 1.00
     *  @version 1.03
     *  @access protected
     *  @param string &$attr The path to update
     *  @param string $value The new path value
     *  @return object $this The current class instance
     */
    protected function _setDir(&$attr, $value) {
        try {
            if ($value[strlen($value)-1] == DIRECTORY_SEPARATOR
                && is_dir($this->_inc_path . $value))
            {
                $attr = $value;
                return $this;
            }
            throw new Smak_Exception(
                'Unable to set the new template path &laquo; '
                . $value
                . ' &raquo;!');
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Sets a new default file extension. You are allowed to set
     *  an extension name until 6 characters alphanumeric after the dot.
     *  @since 1.10
     *  @version 1.00
     *  @access protected
     *  @param string &$attr The file extension to update
     *  @param string $ext The new default file extension
     *  @return object $this The current class instance
     */
    protected function _setExt(&$attr, $ext) {
        try {
            if (preg_match('/^\.([[:alnum:]]){1,6}$/i', $ext)) {
                $attr = htmlentities($ext);
                return $this;
            }
            throw new Smak_Exception(
                'Unable to set file extension &laquo; '
                . $ext
                . ' &raquo;! Must respect format /^\.([[:alnum:]]){1,6}$/i');
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Retrieves the requested template. Gets the GET parameter sent
     *  by the rewrite rule and assigns the requested template to
     *  {@link $_tpl_req} if it exists.
     *  @since 1.00
     *  @version 1.03
     *  @access private
     */
    private function _getRequestedTpl() {
        try {
            if (isset($_GET[self::REWRITE_PARAM])
                || $_SERVER['REQUEST_URI'] == '/')
            {
                $req = isset($_GET[self::REWRITE_PARAM])
                       ? $_GET[self::REWRITE_PARAM]
                       : null;
                if (!is_null($req)) {
                    if (preg_match(self::URL_PATTERN, $req)
                        && $this->_issetTpl($this->_tpl_path, $req))
                    {
                        $this->_tpl_req = strtolower($req);
                    } else {
                        $this->_sendHttpCode('404 Not found');
                    }
                }
            } else if ($_SERVER['REQUEST_URI'] == '/' . 'index.php') {
                header('Location: /');
                exit;
            } else {
                throw new Smak_Exception(
                    'Unable to get requested template.
                    Check rewrite rules and Smak::REWRITE_PARAM.');
            }
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Checks if the navigation item given as parameter is part of the
     *  current applied template. If true, the {@link NAV_CURRENT_CSS_CLASS}
     *  CSS class will be applied to the current navigation item.
     *  @since 1.00
     *  @version 1.03
     *  @access private
     *  @param object $nav_item Navigation item
     *  @return bool
     */
    private function _isCurrentTpl($nav_item) {
        if ($this->_isIndex($nav_item)) {
            return true;
        } else if (!empty($this->_tpl_req)) {
            if (substr($nav_item->tpl, 0, strpos($nav_item->tpl,'.'))
                == $this->_tpl_req)
            {
                return true;
            } else {
                $nav_tpl = explode('/', $nav_item->tpl);
                $current_tpl = explode('/', $this->_tpl_req);
                
                if (count(array_intersect($nav_tpl, $current_tpl))) {
                   return true;
                }
            }
        }
        return false;
    }
    
    /**
     *  Tests if the current navigation menu is the Website index.
     *  @since 1.00
     *  @version 1.03
     *  @access private
     *  @param object $nav_item Navigation item
     *  @return bool
     */
    private function _isIndex($nav_item) {
        return property_exists($nav_item, 'tpl')
               && is_null($nav_item->tpl)
               && (empty($this->_tpl_req)
               || $this->_tpl_req == $this->_tpl_default);
    }
    
    /**
     *  Checks if the template file name given in parameter exists.
     *  @since 1.00
     *  @version 1.02
     *  @access private
     *  @param string $tpl The template path + name
     *  @return bool
     */
    private function _issetTpl($tpl_path, $tpl) {
        if (is_file($this->_inc_path
            . $tpl_path
            . $tpl
            . $this->_tpl_ext))
        {
            return true;
        }
        return false;
    }
    
    /**
     *  Sets a new default value. This template is the one which is
     *  included at the index page. Enter file path + name without
     *  file extension.
     *  @since 1.10
     *  @version 1.00
     *  @access private
     *  @param string $type The type of file to update (tpl or shl)
     *  @param string $file The new default file name
     *  @return object $this The current class instance
     */
    private function _setDefault($type, $file) {
        try {
            if (is_file(
                $this->_inc_path
                . $this->{'_' . $type . '_path'}
                . $file
                . $this->{'_' . $type . '_ext'}))
            {
                $this->{'_' . $type . '_default'} = $file;
                return $this;
            }
            throw new Smak_Exception(
                'Unable to find the new default file &laquo; '
                . $file
                . $this->{'_' . $type . '_ext'}
                . ' &raquo; in '
                . $this->{'_' . $type . '_path'});
        } catch (Smak_Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     *  Returns the requested template file if it exists.
     *  @since 1.00
     *  @version 1.10
     *  @access private
     *  @param string $part The path to look for the template file
     *  @param string $tpl_name A specific template name to include
     */
    private function _getTpl($tpl_path, $tpl_name) {
        
        if (!is_null($tpl_name)) {
            $req_tpl_name = $tpl_name;
        } else if (is_null($this->_tpl_req)) {
            $req_tpl_name = $this->_tpl_default;
        } else {
            $req_tpl_name = $this->_tpl_req;
        }
        
        if ($this->_issetTpl($tpl_path, $req_tpl_name)) {
            ob_start();
            require_once($this->_inc_path
                . $tpl_path
                . $req_tpl_name
                . $this->_tpl_ext);
            ob_end_flush();
        }
    }
}
?>