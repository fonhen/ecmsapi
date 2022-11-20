<?php
class CacheRedis
{
    protected $redis = NULL;
    protected $config = [];
    protected $error = null;

    public function __construct($conf = []) {
        
        $this->config = array_merge([
            'port' => 6379,
            'host'  => '127.0.0.1',
            'auth'  => '',
            'pre'   => 'ecmsapi_'
        ] , $conf);

        try {
            if(!extension_loaded("Redis")){
                throw new \Exception("请先安装Redis扩展");
            }
            $this->redis = new \Redis();
            $this->redis->connect($this->config['host'], $this->config['post']);
            if($this->config['auth'] && !$this->redis->auth($this->config['auth'])){
                throw new \Exception("请使用正确的auth");
            }
        }catch (\Exception $e){
            exit('Redis: '.$e->getMessage());
        }
    }


    public function get($name = ''){
        $name = $this->name($name);
        $value = $this->redis->get($name);
        return false === $value ? NULL : unserialize($value);
    }

    public function set($name = '' , $value = '' , $time = 0){
        $name = $this->name($name);
        $value = serialize($value);
        $result = $this->redis->set($name , $value);
        if($time > 0 && $result){
            $this->redis->expire($name , $time);
        }
        return $result;
    }

    public function delete($name = ''){
        $name = $this->name($name);
        return $this->redis->del($name);
    }

    public function truncate(){
        $keys = $this->redis->keys($this->name('*'));
        foreach($keys as $key){
            $this->redis->del($key);
        }
        return true;
    }

    public function getError(){
        return $this->error;
    }

    protected function name($name = ''){
        return $this->config['pre'].$name;
    }





}