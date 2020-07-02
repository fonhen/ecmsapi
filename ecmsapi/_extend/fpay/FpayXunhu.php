<?php
class FpayXunhu
{
    protected $appId = '';
    protected $appSecret = '';
    protected $error;
    
    public function __construct($conf = [])
    {
        if(isset($conf['appId'])){
            $this->appId = $conf['appId'];
        }
        if(isset($conf['appSecret'])){
            $this->appSecret = $conf['appSecret'];
        }
    }

    public function qrcode($conf = [])
    {
        $params = [
            'version' => '1.1',
            'appid' => $this->appId,
            'trade_order_id' => $conf['orderid'],
            'total_fee' => $conf['price'],
            'title' => $conf['info'],
            'time' => time(),
            'notify_url' => $conf['notify'],
            'nonce_str' => time(),
        ];
        $params['hash'] = $this->createHash($params);
        $url = 'https://api.xunhupay.com/payment/do.html';

        $json = json_decode($this->curl($url , $params) , true);

        if(false !== $json && $json['errcode'] === 0){
            return $json['url_qrcode'];
        }else{
            $this->error = '获取二维码失败';
            return false;
        }
    }

    /* 获取支付地址 */
    public function redirect($conf = [])
    {
        $params = [
            'version' => '1.1',
            'appid' => $this->appId,
            'trade_order_id' => $conf['orderid'],
            'total_fee' => $conf['price'],
            'title' => $conf['info'],
            'time' => time(),
            'notify_url' => $conf['notify'],
            'nonce_str' => time(),
            'redirect' => 'Y'
        ];
        if(isset($conf['return_url'])){
            $params['return_url'] = $conf['return_url'];
        }
        if(isset($conf['callback_url'])){
            $params['callback_url'] = $conf['callback_url'];
        }
        $params['hash'] = $this->createHash($params);
        $url = 'https://api.xunhupay.com/payment/do.html';
        
        $link = $url . '?' . http_build_query($params);
        return $link;
    }
    
    /* 异步验证 */
    public function notify($data)
    {
        foreach ($data as $k=>$v){
            $data[$k] = stripslashes($v);
        }
        if(!isset($data['hash']) || !isset($data['trade_order_id'])){
           return false;
        }
        $hash = $this->createHash($data);
        
        if( $data['hash'] != $hash ){
            return false;
        }
        if( isset($data['status']) && $data['status']=='OD' ){
            return $data['trade_order_id'];
        }else{
            return false;
        }
    }
    
    public function createHash($datas){
        ksort($datas);
        reset($datas);
        $pre =array();
        foreach ($datas as $key => $data){
            if( is_null($data) || $data==='' || $key == 'hash'){
                continue;
            }
            $pre[$key] = stripslashes($data);
        }
        $arg  = '';
        $qty = count($pre);
        $index=0;
        foreach ($pre as $key=>$val){
            $arg.="$key=$val";
            if($index++<($qty-1)){
                $arg.="&";
            }
        }
        return md5($arg . $this->appSecret);

    }
    
    
    public function curl($url , $params , $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $code = curl_exec($ch);
        curl_close($ch);
        return $code;
    }
    
    
    public function getError()
    {
        return $this->error;
    }
}