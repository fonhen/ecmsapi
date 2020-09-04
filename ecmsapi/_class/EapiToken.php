<?php 
class EapiToken
{
    protected $api = null;
    protected $error = null;
    protected $config = [
        'token' =>  'token',
        'time'  =>  'time',
        'timeout' => 600,
        'key' => 'ecmsapitoken'
    ];

    public function __construct($conf = [] , $api)
    {
        $this->api = $api;
        $this->config = array_merge($this->config, $conf);
    }

    public function getOption($name = '')
    {
        if(empty($name)){
            return $this->config;
        }else{
            return isset($this->config[$name]) ? $this->config[$name] : null;
        }
    }

    public function setOption($name = '' , $value = '')
    {
        if(is_array($name)){
            $this->config = array_merge($this->config, $name);
        }elseif(is_string($name) && isset($this->config[$name])){
            $this->config[$name] = $value;
        }
        return $this;
    }

    public function param($param = null , $type = true)
    {
        $param = !is_array($param) ? $_REQUEST : $param;
        if(true === $type && isset($param[$this->config['token']])){
            unset($param[$this->config['token']]);
        }
        return $param;
    }

    public function build($param = null)
    {
        $param = $this->param($param);
        ksort($param);
        return md5($this->query($param , false) . '&token=' . $this->config['key']);
    }

    public function query($param = null , $type = true)
    {
        $param = $this->param($param);
        $str = '';
        foreach($param as $k=>$v){
            $str .= $str ? '&'.$k.'='.$v : $k.'='.$v;
        }
        if(true === $type){
            $str .= '&'.$this->config['token'].'='.$this->build($param);
        }
        return $str;
    }
    
    public function check($param = null){
        $param = $this->param($param , false);
        $token = isset($param[$this->config['token']]) ? $param[$this->config['token']] : '';
        $time = isset($param[$this->config['time']]) ? (int)$param[$this->config['time']] : 0;
        if($time > 0 && !empty($token) && $this->build($param) === $token){
            return time() - $time <= $this->config['timeout'] ? 1 : -1;
        }else{
            return 0;
        }
    }
}