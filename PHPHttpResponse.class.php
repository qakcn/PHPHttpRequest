<?php

/**
 * HttpResponse
 *
 * Parse the response of HTTP request
 *
 * @package     PHPHttpRequest
 * @subpackage  PHPHttpResponse
 * @author      qakcn <qakcnyn@gmail.com>
 * @copyright   2015 qakcn
 * @version     0.1
 * @license     http://mozilla.org/MPL/2.0/
 * @link        https://github.com/qakcn/PHPHttpRequest
 */

//namespace PHPHttpRequest;

class PHPHttpResponse {

    private $status;
    private $data;
    private $headers = array();
    private $cookies = array();


    /**
     * parse "Set-Cookie" header
     * 
     * @param string #cookiestr "Set-Cookie" value
     * @access private
     */
    private function parseCookie($cookiestr) {
        $c=array();
        $options = explode(';', trim($cookiestr));
        foreach($options as $o) {
            if(trim($o) == '') {
                continue;
            }else if(strtolower(trim($o)) == 'secure') {
                $c['secure'] = true;
            }else if(strtolower(trim($o)) == 'httponly') {
                $c['httponly'] = true;
            }else {
                list($on, $ov) = explode('=', trim($o));
                if(strtolower($on) == 'expires') {
                    $time=DateTime::createFromFormat('D, d-M-Y H:i:s e', trim($ov));
                    $c['expires']=$time->getTimestamp();
                }else if(in_array(strtolower($on), array('path', 'domain'))) {
                    $c[strtolower($on)] = trim($ov);
                }else {
                    $c['name'] = trim($on);
                    $c['value'] = trim($ov);
                }
            }
        }
        array_push($this->cookies, $c);
    }


    /**
     * constructor, parse HTTP headers and body
     * 
     * @param string $res response data
     * @access public
     */
    public function __construct($res) {
        $pos = strpos($res, "\r\n\r\n");
        $headers = substr($res, 0, $pos);

        $headers = explode("\r\n", $headers);
        foreach($headers as $h) {
            if(preg_match('/^HTTP\/.+ ([1-5][0-9]{2}) .*$/', $h, $match)) {
                $this->status = $match[1];
            }else {
                list($hn, $hv) = explode(':', $h, 2);
                if(strtolower(trim($hn)) == 'set-cookie') {
                    $this->parseCookie($hv);
                }else {
                    array_push($this->headers, array('name'=>trim($hn), 'value' => trim($hv)));
                }
            }
        }

        $this->data = substr($res, $pos+4);
    }


    /**
     * get headers of response
     * 
     * @param string $name header name, empty for all headers
     * @return array/boolean false for no match
     * @access public
     */
    public function getHeader($name='') {
        if(empty($name)) {
            return $this->headers;
        }
        $res = array();
        foreach($this->headers as $h) {
            if(strtolower($name) == strtolower($h['name'])) {
                array_push($res, $h);
            }
        }
        if(count($res)==0){
            return false;
        }else {
            return $res;
        }
    }


    /**
     * get cookies of response
     * 
     * @param string $name cookie name, empty for all cookies
     * @return array/boolean false for no match
     * @access public
     */
    public function getCookie($name='') {
        if(empty($name)) {
            return $this->cookies;
        }
        $res = array();
        foreach($this->cookies as $c) {
            if(strtolower($name) == strtolower($c['name'])) {
                array_push($res, $c);
            }
        }
        if(count($res)==0){
            return false;
        }else {
            return $res;
        }
    }


    /**
     * getter, called when tring to get value of private properties
     * 
     * @param string $name private property name, should be one of $valid
     * @return mixed value of the property
     * @access public
     */
    public function __get($name) {
        $valid = array('status', 'data');
        if(in_array($name, $valid)) {
            return $this->$name;
        }
    }
}