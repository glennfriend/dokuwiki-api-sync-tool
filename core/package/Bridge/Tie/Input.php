<?php
namespace Bridge\Tie;

interface Input
{
    public function get($key, $defaultValue=null);
    public function has($key);
    public function query($key, $defaultValue=null);
    public function post($key, $defaultValue=null);
    public function isPost();
    public function isAjax();
    public function getParam($key);
    public function getParams();
    public function files();
}
