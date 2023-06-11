<?php
class EapiAddons
{
    protected $api = null;
    protected $error = null;
    protected $name = '';

    public function __construct($name = '' , $api)
    {
        $this->api = $api;
        $this->name($name);
    }

    protected function dir()
    {
        $dir = __DIR__ . '/../_addons/';
        return realpath($dir);
    }

    protected function getPath($path = '')
    {
        
        if($path === ''){
            return $path;
        }
        
        $path = str_replace('\\' , '/' , $path);
        
        while(false !== strpos($path , './')){
            $path = str_replace('./' , '/' , $path);
        }
        while(false !== strpos($path , '//')){
            $path = str_replace('//' , '/' , $path);
        }

        $path = trim(trim($path) , '/');

        return $path;
    }

    // 设置插件名称
    public function name($name = null)
    {
        if(null === $name){
            return $this->name;
        }
        $this->name = $this->getPath($name);

        if($this->name === ''){
            throw new \Exception("请指定插件名称");
        }
        return $this;
    }

    // 创建配置目录
    public function mkdir($path = '')
    {
        $dir = $this->path($path);
        if(!is_dir($dir)){
            mkdir($dir , 0777 , true);
        }
        return realpath($dir);
    }

    // 删除配置目录
    public function rmdir($path = '')
    {
        $path = $this->getPath($path);
        $dir = $this->dir() . '/' . $this->name . '/' . $path;
        $dh = opendir($dir);
        while ($v = readdir($dh) ) {
            if($v == '.' || $v == '..'){
                continue;
            }
            $file = $dir . "/" . $v;
            if(is_dir($file)){
                $this->rmdir($path . '/' . $v);
            }else{
                unlink($file);
            }
        }
        rmdir($dir);
        closedir($dh);
    }

    public function path($file = '')
    {
        $file = $this->getPath($file);
        return $this->dir() . '/' . $this->name . ( $file !=='' ? '/' . $file : '');
    }


    // 生成或读取配置文件
    public function config($name = null , $value = null)
    {
        $configDir = $this->mkdir('_config');
        if(false === $configDir){
            throw new \Exception("请检查 _config 目录权限");
        }
        if(!is_string($name)){
            $files = glob($configDir . '/*.config.php');
            $config = [];
            foreach($files as $file){
                $key = basename($file);
                $key = substr($key , 0 , strlen($key) - 11);
                $config[$key] = require($file);
            }
            return $config;
        }

        $file = $configDir . '/' . $name . '.config.php';

        if(null === $value){
            return is_file($file) ? require($file) : [];
        }else if(is_array($value)){
            $content = "<?php". PHP_EOL ."return " . var_export($value , true) . ";";
            return file_put_contents($file , $content);
        }else if(false === $value){
            unlink($file);
        }
    }


    // 获取后台目录
    public function getAdminFolder()
    {
        return $this->dir() . '/' . $this->name . '/_admin/';
    }

    // 获取后台目录相对链接
    public function getAdminFolderLink()
    {
        return '../../../ecmsapi/_addons/' . $this->name . '/';
    }




}