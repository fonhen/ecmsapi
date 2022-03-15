<?php
// 支持命名空间，自动加载
spl_autoload_register(function($name){
    $file = ECMS_PATH . 'ecmsapi/_src/' . $name . '.php';
    if(file_exists($file)){
        include($file);
    }
});

class EcmsApi
{

    protected $classCache = [];

    public function __construct()
    {

    }

    public function get($name , $default = '' , $fn = ''){
        $value = isset($_GET[$name]) ? $_GET[$name] : $default;
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }

    public function post($name , $default = '' , $fn = ''){
        $value = isset($_POST[$name]) ? $_POST[$name] : $default;
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }

    public function param($name , $default = '' , $fn = ''){
        $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }

    public function input($name = '' , $default = '' , $fn = ''){
        $input = json_decode(file_get_contents('php://input') , true);
        $input = !empty($input) ? $input : array();
        if(empty($name)){
            return $input;
        }else if(!empty($input)){
            $value = isset($input[$name]) ? $input[$name] : '';
            return !empty($fn) && function_exists($fn) ? $fn($value) : $value;	
        }else{
            return $this->param($name , $default , $fn);
        }
    }

    public function isGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='GET';
    }
    
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='POST';
    }
    
    public function isDelete()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='DELETE';
    }
    
    public function isHead()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='HEAD';
    }
    
    public function isPut()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='PUT';
    }
    
    public function isTrace()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='TRACE';
    }
    
    public function isOption()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='OPTION';
    }
    
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
    }
    
    public function isHttps()
    {
        if( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        }elseif( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' ) {
            return true;
        }elseif( isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }
    
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
    }

    public function load($name = '' , $conf = [] , $cache = true)
    {
        $className = 'Eapi'.ucfirst($name);
        if(!class_exists($className , false)){
            require(ECMS_PATH . '/ecmsapi/_class/'.$className.'.php');
        }
        if(false === $cache){
            return new $className($conf , $this);
        }else{
            if(!isset($this->classCache[$name])){
                $this->classCache[$name] = new $className($conf , $this);
            }
            return $this->classCache[$name];
        }
    }
    
    public function extend($name = '' , $conf = [] , $cache = true)
    {
        $className = 'EapiExtend'.ucfirst($name);
        if(!class_exists($className , false)){
            require(ECMS_PATH . '/ecmsapi/_extend/'.$className.'.php');
        }
        if(false === $cache){
            return new $className($conf , $this);
        }else{
            if(!isset($this->classCache[$name])){
                $this->classCache[$name] = new $className($conf , $this);
            }
            return $this->classCache[$name];
        }
    }

    public function show($str , $type = 'text/html' , $charset='utf-8'){
        header('Content-Type: '.$type.'; charset='.$charset);
        exit($str);
    }

    public function error($str , $code = 404 , $type = 'text/html' , $charset='utf-8'){
        $this->sendCode($code);
        $this->show($str , $type , $charset);
    }

    public function json($arr , $options = 0){
        $json = is_array($arr) ? json_encode($arr , $options) : trim($arr);
        $this->show($json , 'application/json');
    }

    public function jsonp($arr , $cb = 'callback' , $options = 0){
        $json = is_array($arr) ? json_encode($arr , $options) : trim($arr);
        $cb = $cb ? $cb : 'callback';
        $json = $cb.'('.$json.');';
        $this->show($json , 'application/json');
    }
    
    public function location($url = '/' , $code = 0)
    {
        if($code >= 100){
            $this->sendCode($code);
        }
        $url = trim($url);
        $url = $url === '' ? '/' : $url;
        header("Location: {$url}");
        exit;
    }

    public function sendCode($code) {
        static $_status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            header('Status:'.$code.' '.$_status[$code]);
        }
    }
}