<?php
namespace Bridge\Tie;

interface Cache
{
    public function get($key);
  //public function has($key);
    public function set($key, $value);
    public function remove($key);
    public function removePrefix($prefix);
    public function flush();
}
