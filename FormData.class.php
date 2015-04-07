<?php

/**
 * FormData API
 *
 * Simulate HTML Form
 *
 * @package     PHPHttpRequest
 * @subpackage  FormData
 * @author      qakcn <qakcnyn@gmail.com>
 * @copyright   2015 qakcn
 * @version     0.1
 * @license     http://mozilla.org/MPL/2.0/
 * @link        https://github.com/qakcn/PHPHttpRequest
 */

//namespace PHPHttpRequest;

class FormData {
    private $member = array();
    private $member_s = array();
    private $member_p = array();
    private $hasFile = false;
    private $multipart = false;


    /**
     * append form member
     * 
     * @param string $name "name" attribute
     * @param string/File $value "value" attribute or File instance
     * @param string $filename override File instance filename
     * @return boolean false if $name is empty
     * @access public
     */
    public function append($name, $value, $filename = null) {
        if(empty($name)) {
            return false;
        }
        $m['name'] = $name;
        if(is_a($value, 'File')) {
            if(!empty((string)$filename)) {
                $m['filename'] = (string)$filename;
            }
            $m['value'] = $value;
            $this->hasFile = true;
        }else {
            $m['value'] = (string)$value;
        }
        array_push($this->member, $m);
        return true;
    }


    /**
     * getter, called when tring to get value of private properties
     * 
     * @param string $name private property name, should be one of $valid
     * @return mixed value of the property
     * @access public
     */
    public function __get($name) {
        $valid = array('hasFile', 'multipart');
        if(in_array($name, $valid)) {
            return $this->$name;
        }
        return false;
    }


    /**
     * setter, called when tring to set value of private properties
     * 
     * @param string $name private property name, only 'multipart' allowed
     * @param mixed $value value of the property, only boolean allowed for 'multipart'
     * @access public
     */
    public function __set($name, $value) {
        if($name=='multipart') {
            $this->$name = (bool)$value;
        }
    }


    /**
     * get first member of FormData and move it to buffer
     * 
     * @return array first member of FormData
     * @access public
     */
    public function shift() {
        if($m = array_shift($this->member)) {
            array_push($this->member_s, $m);
            return $m;
        }
        return false;
    }


    /**
     * get last member of FormData and move it to buffer
     * 
     * @return array last member of FormData
     * @access public
     */
    public function pop() {
        if($m = array_pop($this->member)) {
            array_unshift($this->member_p, $m);
            return $m;
        }
        return false;
    }


    /**
     * reset the members as never have used shift() or pop()
     * 
     * @access public
     */
    public function reset() {
        $this->member = array_merge($this->member_s, $this->member, $this->member_p);
        $this->member_s = $this->member_p = array();
    }
}
