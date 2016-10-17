<?php
namespace Bridge\Options;

class InputWeb implements \Bridge\Tie\Input
{
    protected $_post   = [];
    protected $_query  = [];
    protected $_files  = [];
    protected $_server = [];

    /**
     *  init
     */
    public function __construct()
    {
        $this->_post   = $_POST;
        $this->_query  = $_GET;
        $this->_files  = $_FILES;
        $this->_server = $_SERVER;
    }

    /* --------------------------------------------------------------------------------

    -------------------------------------------------------------------------------- */

    public function get( $key, $defaultValue=null )
    {
        if ( !self::has($key) ) {
            return $defaultValue;
        }
        return self::post($key) ?: self::query($key);
    }

    public function has( $key )
    {
        return self::post(  $key ) ? true :
               self::query( $key ) ? true :
               false;
    }

    public function query( $key, $defaultValue=null )
    {
        return isset($this->_get[$key]) ? $this->_get[$key] : $defaultValue;
    }

    public function post( $key, $defaultValue=null )
    {
        return isset($this->_post[$key]) ? $this->_post[$key] : $defaultValue;
    }

    public function isPost()
    {
        return isset($this->_server['REQUEST_METHOD']) && !strcasecmp($this->_server['REQUEST_METHOD'], 'POST' );
    }

    public function files( $filename='' )
    {
        if ( $filename && isset($this->_files[$filename]) ) {
            return $this->_files[$filename];
        }
        return $this->_files;
    }

    public function getParam( $key )
    {
        throw new Exception('無實作');
    }

    public function getParams()
    {
        throw new Exception('無實作');
    }

    public function isAjax()
    {
        return 
            isset($this->_server['HTTP_X_REQUESTED_WITH']) 
            && $this->_server['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
    }

}
