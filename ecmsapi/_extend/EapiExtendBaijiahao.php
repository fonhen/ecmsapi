<?php
class EapiExtendBaijiahao
{
    protected $config = array(
        'app_id' => '',
        'app_token' => ''
    );
    
    protected $uri = 'https://baijiahao.baidu.com/builderinner/open/resource/';

    protected $api = null;

    public function __construct($config = [] , $api = null){
        $this->config = array_merge($this->config, $config);
        $this->api = $api;
    }
    
    public function setOption($name = '' , $value = '')
    {
        if(is_array($name)){
            $this->config = array_merge($this->config, $name);
        }else if(is_string($name) && isset($this->config[$name])){
            $this->config[$name] = $value;
        }
        return $this;
    }
    
    public function curl($uri , $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($output , true);
        if($json && isset($json['errno'])){
            return $json;
        }else{
            return null;
        }
    }
    
    public function appid($data = [])
    {
        $conf = [
            'app_id' => $this->config['app_id'],
            'app_token' => $this->config['app_token']
        ];
        return array_merge($conf , $data);
    }
    
    /*
    *  获取接口数据
    *  @param $name 接口类型 通过 https://baijiahao.baidu.com/docs/#/normalcomplex/developer/serviceIntroduction 查询
    *  @param $data 接口数据 无需传 app_id 与 app_token
    *  @return Array
    */
    public function query($name = '' , $data = [])
    {
        $data = $this->appid($data);
        $uri = $this->uri . $name;
        return $this->curl($uri , $data);
    }
}