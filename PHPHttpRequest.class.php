<?php

/**
 * HttpRequest API
 *
 * Methods are like JavaScript XMLHttpRequest's
 * Response store in private property $response as PHPHttpResponse instance
 *
 * @package     PHPHttpRequest
 * @subpackage  PHPHttpRequest
 * @author      qakcn <qakcnyn@gmail.com>
 * @copyright   2015 qakcn
 * @version     0.1
 * @license     http://mozilla.org/MPL/2.0/
 * @link        https://github.com/qakcn/PHPHttpRequest
 */

//namespace PHPHttpRequest;

class PHPHttpRequest {

    private $method;
    private $scheme;
    private $host;
    private $port;
    private $user;
    private $password;
    private $path;
    private $query;
    private $response;
    private $headers;
    private $cookies;

    const HTTP_EOL = "\r\n";


    /**
     * generate HTTP header string
     * 
     * @return string generated header
     * @access private
     */
    private function genHeader() {
        $header = $this->method . ' ' . $this->path . (isset($this->query) ? '?' . $this->query : '') . ' HTTP/1.1' . PHPHttpRequest::HTTP_EOL;
        $header .= 'Host: ' . $this->host . PHPHttpRequest::HTTP_EOL;
        foreach($this->headers as $hn => $hv) {
            $header .= $hn . ': ' . $hv . PHPHttpRequest::HTTP_EOL;
        }
        if(count($this->cookies)>0) {
            $header .= 'Cookie: ';
            foreach($this->cookies as $cn => $cv) {
                $header .= $cn . '=' . $cv . '; ';
            }
            $header = substr($header, 0, -2) . PHPHttpRequest::HTTP_EOL;
        }
        $header .= PHPHttpRequest::HTTP_EOL;
        return $header;
    }


    /**
     * reset to default properties, make PHPHttpRequest instance again to use
     * 
     * @access private
     */
    private function reset() {
        unset($this->method);
        unset($this->scheme);
        unset($this->host);
        unset($this->port);
        unset($this->user);
        unset($this->password);
        unset($this->path);
        unset($this->query);
        $this->headers = array(
            'User-Agent' => 'PHPHttpRequest/0.1',
            'Accept' => '*/*',
            'Connection' => 'close',
            'Cache-Control' => 'no-cache'
        );
        $this->cookies = array();
    }


    /**
     * send request
     * 
     * @param string $data data ready for send
     * @return false if unable to establish connection
     * @access private
     */
    private function sendRequest($data) {
        $host = ($this->scheme=='http' ? '' : 'tls://').$this->host;
        $fp = @fsockopen($host, $this->port);
        if($fp !== false) {
            fwrite($fp, $this->genHeader() . $data);
            $result = '';
            while(!feof($fp)) {
                $result .= fgets($fp);
            }
            fclose($fp);
            $this->response = new PHPHttpResponse($result);
            $this->reset();
            return true;
        }
        return false;
    }


    /**
     * constructor, do some initiation
     * 
     * @access public
     */
    public function __construct() {
        $this->reset();
    }


    /**
     * set request header
     * 
     * @param string $name header name
     * @param string $value header value
     * @access public
     */
    public function setRequestHeader($name, $value) {
        if(strtolower(trim($name))!='cookie') {
            $this->headers[trim($name)] = trim($value);
    }


    /**
     * set request cookie
     * 
     * @param string $name cookie name
     * @param string $value cookie value
     * @access public
     */
    public function setCookie($name, $value) {
        $this->cookies[$name] = $value;
    }

    public function __get($name) {
        $valid = array('response');
        if(in_array($name, $valid)) {
            return $this->$name;
        }
    }


    /**
     * open link stage
     * 
     * @param string $method HTTP method, now support HEAD, GET, PUT, POST and DELETE
     * @param string $url URL to send request, must be absolute path
     * @return boolean false if method not support or URL not conform format
     * @access public
     */
    public function open($method, $url) {
        $method = strtoupper($method);
        $valid_method = array('HEAD', 'GET', 'PUT', 'POST', 'DELETE');
        if(in_array($method, $valid_method)) {
            $this->method = $method;
            $url = parse_url($url);
            if(isset($url['scheme']) && isset($url['host']) && ($url['scheme'] == 'http' || $url['scheme'] == 'https')) {
                $this->scheme = $url['scheme'];
                $this->host = $url['host'];
                $this->port = isset($url['port']) ? $url['port'] : ($url['scheme']=='http' ? 80 : 443);
                $this->path = isset($url['path']) ? $url['path'] : '/';
                isset($url['user']) ? $this->user = $url['user'] : '';
                isset($url['pass']) ? $this->password = $url['pass'] : '';
                isset($url['query']) ? $this->query = $url['query'] : '';
                return true;
            }
        }
        return false;
    }


    /**
     * send request
     * 
     * @param string/FormData/File $data data ready for send
     * @return boolean true if send successfully
     * @access public
     */
    public function send($data='') {
        if(isset($this->scheme)) {
            $postdata = '';
            if($this->method != 'GET' && $this->method != 'HEAD' && $this->method != 'DELETE') {
                if(is_a($data, 'FormData')) {
                    if($data->hasFile || $data->multipart) {
                        srand((double)microtime()*1000000);
                        $boundary = '---------------------------'.substr(md5(rand(0,32000)),0,10);
                        $this->setRequestHeader('Content-Type', 'multipart/form-data; boundary='.$boundary);
                        $postdata .= '--' . $boundary;
                        while($m = $data->shift()) {
                            $postdata .= PHPHttpRequest::HTTP_EOL;
                            if(is_a($m['value'], 'File')) {
                                $file = $m['value'];
                                $filename = isset($m['filename'])? $m['filename'] : $file->filename;
                                $postdata .= 'Content-Disposition: form-data; name="' . $m['name'] . '"; filename="' . $filename . '"' . PHPHttpRequest::HTTP_EOL;
                                $postdata .= 'Content-Type: ' . $file->mimetype . PHPHttpRequest::HTTP_EOL;
                                $postdata .= PHPHttpRequest::HTTP_EOL;
                                $postdata .= $file->readAsString() . PHPHttpRequest::HTTP_EOL;
                                $postdata .= '--' . $boundary;
                            }else {
                                $postdata .= 'Content-Disposition: form-data; name="'.$m['name'].'"' . PHPHttpRequest::HTTP_EOL.PHPHttpRequest::HTTP_EOL;
                                $postdata .= $m['value'] . PHPHttpRequest::HTTP_EOL;
                                $postdata .= '--' . $boundary;
                            }
                        }
                        $postdata .= '--' . PHPHttpRequest::HTTP_EOL;
                    }else {
                        $this->setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        while($m = $data->shift()) {
                            $postdata .= rawurlencode($m['name']) . '=' . rawurlencode($m['value']) . '&';
                        }
                        $postdata = substr($postdata, 0, -1);
                    }
                }else if(is_a($data, 'File')) {
                    $this->setRequestHeader('Content-Type', $data->mimetype);
                    $postdata .= $data->readAsString();
                }else {
                    $postdata .= $data;
                }
                $this->setRequestHeader('Content-Length', strlen($postdata));
            }
            return $this->sendRequest($postdata);
        }
        return false;
    }


}