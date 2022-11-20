<?php
class CacheYac {
    protected $yac = NULL;
    protected $cachepre = '';
    protected $error = null;
    public function __construct($conf = []) {
        if(!class_exists('Yac' , false)) {
            exit('请先安装Yac扩展');
        }
        $this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'ecmsapi_';
        $this->yac = new Yac($this->cachepre);
    }
    public function connect(){
    
    }
    public function set($k, $v, $life) {
        return $this->yac->set($k, $v, $life);
    }
    public function get($k) {
        $r = $this->yac->get($k);
        if($r === false) $r = null;
        return $r;
    }
    public function delete($k) {
        return $this->yac->delete($k);
    }
    public function truncate() {
        $this->yac->flush();
        return true;
    }
    public function getError(){
        return $this->error;
    }
    public function __destruct() {

    }
}
?>