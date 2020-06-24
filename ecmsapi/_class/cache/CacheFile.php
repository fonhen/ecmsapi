<?php
class CacheFile {

    protected $error = null;
    protected $dir = '';
    protected $pre = 'eapi_';
    
    public function __construct($conf = []) {
        //$dir = dirname(__FILE__) .'/../../_cache/file/';
        $dir = ECMS_PATH .'ecmsapi/_cache/file/';
        !is_dir($dir) AND mkdir($dir , 0777 , true);
        $this->dir = $dir;
        if(isset($conf['pre'])){
            $this->pre = $conf['pre'];
        }
    }
    
    public function connect(){
    
    }
    
    public function set($name, $value, $time = 0) {
        $data = [
            'timeout' => $time,
            'ctime' => time(),
            'value' => $value
        ];
        return file_put_contents($this->filepath($name) , serialize($data));
    }
    
    public function get($name) {
        $file = $this->filepath($name);
        
        if(!is_file($file)){
            return null;
        }
        $code = file_get_contents($file);
        if(false === $code){
            $this->error = '权限不足';
            return null;
        }
        $data = unserialize($code);
        if(empty($data) || !isset($data['timeout'])){
            unlink($file);
            return null;
        }
        if($data['timeout'] !== 0 && time() - $data['timeout'] > $data['ctime']){
            unlink($file);
            return null;
        }else{
            return $data['value'];
        }
    }
    
    public function delete($name) {
        return unlink($this->filepath($name));
    }
    
    public function truncate() {
        
    }
    
    public function getError(){
        return $this->error;
    }
    
    protected function filepath($name)
    {
        return $this->dir . md5($this->pre.$name) . '.cache';
    }

}
?>