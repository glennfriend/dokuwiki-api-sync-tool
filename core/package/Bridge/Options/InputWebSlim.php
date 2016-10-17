<?php
namespace Bridge\Options;


// TODO: 該程式未完成


class InputWebSlim implements \Bridge\Tie\Input
{
    protected $request;
    protected $response;
    protected $args;

    /**
     *  init
     */
    public function init($arguments)
    {
        $this->request  = $arguments[0];
        $this->response = $arguments[1];
        $this->args     = $arguments[2];
    }

    /* --------------------------------------------------------------------------------

    -------------------------------------------------------------------------------- */

    public function get($key, $defaultValue=null)
    {
        $value = $this->request->getParam($key);
        if (null === $value) {
            return $defaultValue;
        }
        return $value;
    }

    public function has( $key )
    {
        throw new Exception('無實作 has');
    }

    public function query($key, $defaultValue=null)
    {
        throw new Exception('無實作 query');
        return $this->request->getQueryParam($key);
    }

    public function post($key, $defaultValue=null)
    {
        throw new Exception('無實作 post');
    }

    public function isPost()
    {
        return $this->request->isPost();
    }

    public function files($filename='')
    {
        throw new Exception('無實作 files');
        $files = $this->request->getUploadedFiles();
    }

    public function getParam($key)
    {
        return $this->request->getAttribute($key);
    }

    public function getParams()
    {
        throw new Exception('無實作 params');
        //$this->request->getAttributes()
        //return $this->request->getParams($key);
    }

    public function isAjax()
    {
        return $this->request->isXhr();
    }

}
