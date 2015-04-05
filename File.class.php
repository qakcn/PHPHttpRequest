<?php

/**
 * File API
 *
 * @package     PHPHttpRequest
 * @subpackage  File
 * @author      qakcn <qakcnyn@gmail.com>
 * @copyright   2015 qakcn
 * @version     0.1
 * @license     http://mozilla.org/MPL/2.0/
 * @link        https://github.com/qakcn/PHPHttpRequest
 */

//namespace PHPHttpRequest;

class File {

    private $filepath;
    private $mimetype;
    private $filesize;
    private $filename;


    /**
     * get MIME type of file, store in $mimetype
     * 
     * @return boolean true if successfully get
     * @access private
     */
    private function getMimeType() {
        if($finfo = finfo_open(FILEINFO_MIME_TYPE)) {
            $this->mimetype = finfo_file($finfo, $this->filepath);
            finfo_close($finfo);
            return true;
        }
        return false;
    }


    /**
     * get size of file, store in $filesize
     * 
     * @return boolean true if successfully get
     * @access private
     */
    private function getFileSize() {
        if(false !== ($filesize = filesize($this->filepath))) {
            $this->filesize = $filesize;
            return true;
        }
        return false;
    }


    /**
     * constructor
     * 
     * @param string $filepath the path of a file
     * @return boolean false if $filepath is empty or is not exist or is not a file
     * @access public
     */
    public function __construct($filepath) {
        if(!empty($filepath) && file_exists($filepath) & is_file($filepath)) {
            $this->filepath = $filepath;
            $this->getMimeType();
            $this->getFileSize();
            $this->filename = basename($filepath);
            return true;
        }
        return false;
    }


    /**
     * getter, called when tring to get value of private properties
     * 
     * @param string $name private property name, should be one of $valid
     * @return mixed value of the property
     * @access public
     */
    public function __get($name) {
        $name = strtolower($name);
        $valid = array('mimetype', 'filesize', 'filename', 'filepath');
        if(in_array($name, $valid)) {
            return $this->$name;
        }
        return false;
    }


    /**
     * get file content as string
     * 
     * @param integer $offset start position of a file in byte, defalut -1
     * @param integer $maxlen max length of content in byte, default NULL
     * @return string content of the file
     * @access public
     */
    public function readAsString($offset = -1, $maxlen = null) {
        if(!is_int($offset) || $offset < -1) {
            $offset = -1;
        }
        if(is_int($maxlen) && $maxlen > 0) {
            return file_get_contents($this->filepath, false, null, $offset, $maxlen);
        }else {
            return file_get_contents($this->filepath, false, null, $offset);
        }
    }


    /**
     * get file content as data URI string
     * 
     * @return string base64 encoded data URI
     * @access public
     */
    public function readAsDataURI() {
        return 'data:' . $this->mimetype . ';base64,' . base64_encode( $this->readAsString() );
    }
}