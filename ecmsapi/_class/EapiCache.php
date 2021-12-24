<?php
class EapiCache
{
    protected $api = null;
    protected $error = null;
    protected $classCache = [];
    protected $type = 'file';
    protected $conf = [];

    public function __construct($type = null , $api = null)
    {
        $this->api = $api;
        if(!empty($type) && is_string($type)){
            $this->type = trim($type);
        }
    }
    
    public function get($name)
    {
        return $this->cache($this->type)->get($name);
    }
    
    public function set($name , $value , $time = 0)
    {
        return $this->cache($this->type)->set($name , $value , $time);
    }
    
    public function delete($name)
    {
        return $this->cache($this->type)->delete($name);
    }
	
	public function truncate()
	{
		return $this->cache($this->type)->truncate();
	}
    
    public function cache($name , $conf = [] , $cache = true)
    {
        $this->type = $name;
        $className = 'Cache'.ucfirst($name);
        if(!class_exists($className)){
            require( dirname(__FILE__) . '/cache/'.$className.'.php');
        }
        if(false === $cache){
            return new $className($conf);
        }else{
            if(!isset($this->classCache[$name])){
                $this->classCache[$name] = new $className($conf);
            }
            return $this->classCache[$name];
        }
    }
    
    
}