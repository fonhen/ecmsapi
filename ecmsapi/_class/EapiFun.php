<?php 
class EapiFun
{
    protected $api = null;
    protected $error = null;

    public function __construct($conf = [] , $api)
    {
        $this->api = $api;
    }

    public function toInt($num = 0 , $min = 0 , $max = 0)
    {
        $num = (int)$num;
        $min = (int)$min;
        $max = (int)$max;
        $num = $num < $min ? $min : $num;
        if($max > $min){
            $num = $num > $max ? $max : $num;
        }
        return $num;
    }
    
    public function toString($val = '' , $defaults = '')
    {
        $val = trim($val);
        return $val !== '' ? $val : $defaults;
    }
    
    public function toNumArray($value , $separator = ''){
        if(!is_array($value)){
            if($separator === ''){
                $separator = ',';
                $value = str_replace(['$' , '|' , ' ' , '，' , '、' , '/' , '\\' , '' , '#'] , $separator , $value);
            }
            $value = explode($separator , $value);
        }
        $value = array_filter($value , function(&$v){
            $v = abs((int)$v);
            return $v;
        });
        $value = array_flip(array_flip($value));
        return $value;
    }
    
    public function toStrArray($value , $separator = ''){
        if(!is_array($value)){
            if($separator === ''){
                $separator = ',';
                $value = str_replace(['$' , '|' , ' ' , '，' , '、' , '/' , '\\' , '' , '#'] , $separator , $value);
            }
            $value = explode($separator , $value);
        }
        $value = array_filter($value , function(&$v){
            $v = trim($v);
            return $v;
        });
        $value = array_flip(array_flip($value));
        return $value;
    }
    
    public function isEmail($value = '')
    {
        $rule = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
        return $this->regex($rule , $value);
    }
    
    public function isMobile($value = '')
    {
        $rule = "/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\d{8}$/";
        return $this->regex($rule , $value);
    }
    
    public function regex($rule = '' , $code = '')
    {
        return !!preg_match($rule , $code);
    }
    
    public function json($code , $data , $message = '' , $option = 0)
    {
        if(is_string($data)){
            $message = $message === '' ? $data : $message;
            $data = [];
        }else if(!is_array($data)){
            $data = [];
        }
        return $this->api->json([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ] , $option);
    }
    
    public function jsonp($code , $data , $message = '' , $cb = 'callback', $option = 0)
    {
        if(is_string($data)){
            $message = $message === '' ? $data : $message;
            $data = [];
        }else if(!is_array($data)){
            $data = [];
        }
        return $this->api->jsonp([
            'code' => $code,
            'data' => $data,
            'message' => $message
        ] , $cb , $option);
    }
    
    public function getAttrs($text , $mode = true)
    {
        $text = $mode ? str_replace(array("\r\n", "\r", "\n"), "||||||", $text) : $text;
        $temp = explode('||||||' , $text);
        $result = [];
        foreach($temp as $v){
            $v = trim($v);
            if($v !== ''){
                $result[] = explode('::::::' , $v);
            }
        }
        return $result;
    }
    
    public function getError()
    {
        return $this->error;
    }

}