<?php
class EapiFile
{
    protected $api = null;
    protected $error = null;
    protected $conf = [
        'rootpath'  => ECMS_PATH,
        'size'      =>       0,
        'mimes'     =>       [],
        'exts'      =>       [],
        'fpath'     => 1,
        'ftb'       => 1,
        'user'      => 'admin',
        'modtype'   => 0
    ];

    public function __construct($config = [] , $api = null)
    {
        $this->setOption($config);
        $this->api = $api;
    }
    
    public function setOption($name , $value = null)
    {
        if(is_array($name)){
            $this->conf = array_merge($this->conf, $name);
        }else{
            $this->conf[$name] = $value;
        }
        return $this;
    }
    
    public function getOption($name)
    {
        if(isset($this->conf[$name])){
            return $this->conf[$name];
        }else{
            return null;
        }
    }
    
    /*
    * $file 要上传的文件
    * $id 内容的ID
    * $classid 栏目id
    * $filepass 文件临时变量一般为time()
    * $type 文件类型 1为图片，2为Flash文件，3为多媒体文件，0为附件
    */
    public function upload($file , $id = 0 , $classid = 0 , $filepass = 0 , $type = 1)
    {
        if(empty($file) || !is_array($file)){
            $this->error = '请选择要上传的文件';
            return false;
        }
        $filepass = $filepass ? $filepass : time();
        
        $fpath = $this->getFpath($classid , $this->getOption('fpath'));
        
        $filename = $this->buildFileName($file['name'] , $classid);
        
        $up = $this->api->load('upload' , [
            'rootpath' => $this->getOption('rootpath'),
            'size' => $this->getOption('size'),
            'mimes' => $this->getOption('mimes'),
            'exts' => $this->getOption('exts')
        ]);
        
        $fileinfo = $up->upload($file , $filename , $fpath);
        
        if(false === $fileinfo){
            $this->error = $up->getError();
            return false;
        }
        $fileinfo['path'] = $this->getFileDatePath();
        
        $fdata = $this->insert($fileinfo , $id , $classid , $filepass , $type);
        
        if(false === $fdata){
            return false;
        }
        
        return $fdata;
    }
    
    // 写入数据表
    public function insert($file , $id , $classid , $filepass = 0 , $type = 1)
    {
        // 要入库的数据
        $data = [];
        
        $data['filesize'] = $file['size'];
        $data['path'] = $file['path'];
        $data['filename'] = $file['fullname'];
        $data['no'] = $file['original'];
        $data['adduser'] = $this->getOption('user');
        $data['filetime'] = time();
        
        if($id !== 0){
            $data['pubid'] = ReturnInfoPubid($classid , $id);
            $data['id'] = $id;
        }else{
            $data['cjid'] = $filepass;
            $data['id'] = $filepass;
        }
        
        $data['type'] = $type;
        $data['modtype'] = $this->getOption('modtype');
        $data['fpath'] = $this->getOption('fpath');
        $data['classid'] = $classid;
        
        $table = '[!db.pre!]enewsfile_'.$this->getOption('ftb');
        $fileid = $this->api->load('db')->insert($table  , $data);
        if(false === $fileid){
            $this->error = $this->api->load('db')->getError();
            return false;
        }
        $data['fileid'] = $fileid;
        return $data;
    }
    
    // 更新附件数据
    public function update($id , $classid , $filepass = 0 , $more = [])
    {
        $filepass = (int)$filepass;
        if($filepass === 0){
            $this->error = '请输入filepass';
            return false;
        }
        $data = [];
        $data['cjid'] = 0;
        $data['id'] = $id;
        $data['pubid'] = ReturnInfoPubid($classid , $id);
        $result = $this->api->load('db')->update('[!db.pre!]enewsfile_'.$this->getOption('ftb') , $data , 'cjid=' . $filepass);
        if(false === $result){
            $this->error = $this->api->load('db')->getError();
            return false;
        }
        return $result;
    }
    
    // 删除附件
    public function delete($id , $classid  = 0, $ftb = null)
    {
        $ftb = is_null($ftb) ? $this->getOption('ftb') : $ftb;
        $map = is_numeric($id) ? 'id = '.$id.' and classid = '.$classid : $id;
        $files = $this->api->load('db')->select('[!db.pre!]enewsfile_'.$ftb , 'filename,fpath,path,classid' , $map , '2000' , 'fileid desc');
        if(false === $files){
            $this->error = '删除附件失败';
            return false;
        }else if(empty($files)){
            return true;
        }
        foreach($files as $i=>$f){
            $fullpath = $this->getFullPath($f);
            $this->deleteFile($fullpath);
        }
        $result = $this->api->load('db')->delete('[!db.pre!]enewsfile_'.$ftb , $map);
        if(false === $result){
            $this->error = '删除附件失败';
            return false;
        }
        return true;
    }
    
    public function deleteFile($fullpath)
    {
        $truepath = rtrim(ECMS_PATH , '/') . $fullpath;
        return @unlink($truepath);
    }
    
    function buildFileName($str , $classid = 0)
    {
        return ReturnDoTranFilename($str , $classid);
    }
    
    // 通过数据库获取文件路径
    public function getFullPath($file)
    {
        $d = rtrim( $this->getFpath($file['classid'] , $file['fpath']) , '/' ) . '/' . $file['filename'];
        return $d;
    }
    
    // 获取附件存放路径
    public function getFpath($classid = 0 , $type = null)
    {
        global $public_r , $class_r;
        $type = is_null($type) ? (int)$public_r['fpath'] : (int)$type;
        if($type === 0){
            if( isset($class_r[$classid]) ){
                $fp = '/'.trim($class_r[$classid]['classpath'] , '/').'/';
            }else{
                $type = 1;
                $fp = '/d/file/p/';
            }
        }else if($type === 1){
            $fp = '/d/file/p/';
        }else{
            $fp = '/d/file/';
        }
        $this->setOption('fpath' , $type);
        $fp .= $this->getFileDatePath();
        return $fp;
    }
    
    public function getFileDatePath($code = '')
    {
         global $public_r;
         $code = $code === '' ? $public_r['filepath'] : $code;
         $param = trim($code);
         if($param !== ''){
            return trim(date($param) , '/');
         }else{
             return '';
         }
    }
    
    public function getError()
    {
        return $this->error;
    }


}