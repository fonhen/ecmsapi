<?php
class EapiTable
{
    protected $api = null;
    protected $error = null;
    protected $tableFieldsCache = [];

    public function __construct($conf = [] , $api)
    {
        $this->api = $api;
    }

    public function insert($table , $post)
    {
        $result = $this->validate($table , $post);
        if(false === $result){
            return false;
        }
        if(!isset($post['classid'])){
            $this->error = '请选择栏目';
            return false;
        }
        if(!isset($post['title'])){
            $this->error = '标题不能为空';
            return false;
        }
        if(!isset($post['userid'])){
            $this->error = '请选择发布用户ID';
            return false;
        }
        if(!isset($post['username'])){
            $this->error = '请填写发布用户名称';
            return false;
        }
        
        if(!isset($post['newstime'])){
            $post['newstime'] = time();
        }
        if(!isset($post['truetime'])){
            $post['truetime'] = $post['newstime'];
        }
        if(!isset($post['lastdotime'])){
            $post['lastdotime'] = $post['newstime'];
        }

        $db = $this->api->load('db');
        
        // 索引表
        $itb = 'index';
        $idata = $this->getIndexData($post);
        $id = $db->insert($this->getTableName($table , $itb) , $idata);
        
        if(false === $id){
            $this->error = '数据库操作失败';
            return false;
        }
        
        $isChecked = isset($idata['checked']) ? $idata['checked'] : 0;
        $data = $this->filterField($table , $post);
        $data['id'] = $id;
        
        // 主表
        $tname = $this->getTableName($table);
        if(!$isChecked){
            $tname .= '_check';
        }
        $db->insert($tname , $data);
        $d = $db->getByPk($tname , $id);
        if(false === $d){
            $db->delete($tname , "id = {$id}");
            $this->error = '数据库操作失败';
            return false; 
        }
        
        // 副表
        if(!$isChecked){
            $stb = 'check_data';
        }else{
            $stb = 'data_'.$d['stb'];
        }
        $sdata = $this->filterField($table.'_'.$stb , array_merge($post , $d));
        $db->insert($this->getTableName($table , $stb) , $sdata);
        
        // 修改titleurl
        if(!isset($data['titleurl']) || $data['titleurl'] === ''){
            $this->updateTitleUrl($isChecked ? $table : $table . '_check', $d);
        }
        
        // 更新栏目信息数
        $infos = [
            'allinfos' => ['allinfos + 1']
        ];
        if($isChecked){
            $infos['infos'] = ['infos + 1'];
        }
        $db->update('[!db.pre!]enewsclass' , $infos , 'classid = '.$d['classid']);
        return $id;
    }

    public function update($table , $post , $id = 0)
    {
        $result = $this->validate($table , $post);
        if(false === $result){
            return false;
        }
        $id = (int)$id;
        if($id === 0){
            if(isset($post['id'])){
                $id = (int)$post['id'];
                unset($post['id']);
            }
        }
        if($id === 0){
            $this->error = '请指定要更新的内容ID';
            return false;
        }
        
        $db = $this->api->load('db');
        $idata = $db->getByPk($this->getTableName($table , 'index') , $id);
        
        if(false === $idata){
            $this->error = '没要查询到相关数据';
            return false;
        }
        
        $isChecked = (int)$idata['checked'];
        
        $tb = $isChecked ? $table : $table.'_check';
        $data = $this->filterField($tb , $post);
        
        // 删除不允许更新的字段
        foreach(['stb' , 'fstb' , 'restb'] as $i){
            if(isset($data[$i])){
                unset($data[$i]);
            }
        }
        // 如果没有指定更新时间，则自动更新时间
        if(!isset($data['lastdotime'])){
            $data['lastdotime'] = time();
        }else if((int)$data['lastdotime'] === 0){
            unset($data['lastdotime']);
        }
        
        if(empty($data)){
            $this->error = '请填写需要更新的字段';
            return false;
        }
        
        $result = $db->update('[!db.pre!]ecms_'.$tb , $data , 'id = '.$id);
        
        if(false === $result){
            $this->error = '更新失败';
            return false;
        }
        
        if($isChecked){
            $odata = $db->getByPk('[!db.pre!]ecms_'.$tb , $id , 'stb');
            $stb = $table.'_data_'.$odata['stb'];
        }else{
            $stb = $table.'_data';
        }
        
        $sdata = $data = $this->filterField($stb , array_merge($post , $data));
        
        if(!empty($sdata)){
            return $db->update('[!db.pre!]ecms_'.$stb , $sdata , 'id = '.$id);
        }
        
        return true;
    }

    public function delete($table , $id)
    {
        global $class_r;
        $db = $this->api->load('db');
        $idata = $db->getByPk('[!db.pre!]ecms_'.$table.'_index' , $id);
        if(false === $idata){
            $this->error = '没有查询到相关数据';
            return false;
        }
        $classid = $idata['classid'];
        if(!isset($class_r[$classid])){
            $this->error = '当前数据栏目不存在';
            return false;
        }else if($class_r[$classid]['tbname'] !== $table){
            $this->error = '数据栏目与模型不比配';
            return false;
        }
        $result = $db->delete('[!db.pre!]ecms_'.$table.'_index' , 'id = '.$id);
        if( false === $result){
            $this->error = '删除失败';
            return false;
        }
        
        $infos = ['allinfos' => ['allinfos - 1']];
        
        if((int)$idata['checked'] === 0){
            $db->delete('[!db.pre!]ecms_'.$table.'_check' , 'id = '.$id);
            $db->delete('[!db.pre!]ecms_'.$table.'_check_data' , 'id = '.$id);
        }else{
            $odata = $db->getByPk('[!db.pre!]ecms_'.$table , $id , 'stb');
            $db->delete('[!db.pre!]ecms_'.$table , 'id = '.$id);
            $db->delete('[!db.pre!]ecms_'.$table.'_data_'.$odata['stb'] , 'id = '.$id);
            $infos['infos'] = ['infos - 1'];
        }
        
        // 刷列表除信息量
        $db->update('[!db.pre!]enewsclass' , $infos , 'classid = '.$classid);
        
        return true;
    }

    // 获取数据
    public function get($table , $id , $field = '*')
    {
        global $class_r;
        $db = $this->api->load('db');
        $idata = $db->getByPk('[!db.pre!]ecms_'.$table.'_index' , $id);
        if(false === $idata){
            $this->error = '没有查询到相关数据';
            return false;
        }
        $classid = $idata['classid'];
        if(!isset($class_r[$classid])){
            $this->error = '当前数据栏目不存在';
            return false;
        }else if($class_r[$classid]['tbname'] !== $table){
            $this->error = '数据栏目与模型不比配';
            return false;
        }
        
        if(empty($field) || $field === '*'){
            $zf = '*';
            $sf = '*';
        }else{
            $zField = $this->getFields($table);
            $sField = $this->getFields($table.'_data_1');
            $field = is_array($field) ? $field : explode(',' , $field);
            
            $zf = [];
            $sf = [];
            
            foreach($field as $i){
                
                if(isset($zField[$i])){
                    $zf[] = $i;
                }
                if(isset($sField[$i])){
                    $sf[] = $i;
                }
            }
            $zf = empty($zf) ? '*' : implode(',' , $zf);
            $sf = empty($sf) ? '' : implode(',' , $sf);
        }

        if((int)$idata['checked'] === 0){
            $zdata = $db->getByPk('[!db.pre!]ecms_'.$table.'_check' , $id , $zf);
            if($sf !== ''){
                $sdata = $db->getByPk('[!db.pre!]ecms_'.$table.'_check_data' , $id , $sf);
            }else{
                $sdata = [];
            }
        }else{
            $zdata = $db->getByPk('[!db.pre!]ecms_'.$table , $id , $zf);
            
            if($sf !== ''){
                if(!isset($zdata['stb'])){
                    $r = $db->getByPk('[!db.pre!]ecms_'.$table , $id , 'stb');
                    $i = $r['stb'];
                }else{
                    $i = $zdata['stb'];
                }
                $sdata = $db->getByPk('[!db.pre!]ecms_'.$table.'_data_'.$i , $id , $sf);
            }else{
                $sdata = [];
            }
        }
        
        return array_merge($zdata , $sdata);
        
    }

    protected function filterField($table , $data)
    {
        if(empty($data) || !is_array($data)){
            return [];
        }
        $fields = $this->getFields($table);
        foreach($data as $i=>$v){
            if(!isset($fields[$i])){
                unset($data[$i]);
            }
        }
        return $data;
    }

    protected function getTableName($name , $ext = '')
    {
        return '[!db.pre!]ecms_'.$name.($ext !== '' ? '_'.$ext : '');
    }

    protected function getIndexData($data)
    {
        $fields = ['classid' , 'checked' , 'newstime' , 'truetime' , 'lastdotime' , 'havehtml'];
        $r = [];
        foreach($fields as $v){
            if(isset($data[$v])){
                $r[$v] = (int)$data[$v];
            }
        }
        return $r;
    }

    protected function validate($table , $data)
    {
        global $class_r;
        if(empty($data) || !is_array($data)){
            $this->error = '参数错误';
            return false;
        }
        if(isset($data['classid'])){
            $classid = $data['classid'];
            if(!isset($class_r[$classid])){
                $this->error = '所选栏目不存在';
                return false;
            }else if($class_r[$classid]['tbname'] !== $table){
                $this->error = '所选栏目与模型不匹配';
                return false;
            }else if((int)$class_r[$classid]['islast'] !== 1){
                $this->error = '非终级栏目不允许发布';
                return false;
            }
        }
        if(isset($data['title']) && $data['title'] === ''){
            $this->error = '标题不能为空';
            return false;
        }
        return true;
    }

    public function updateTitleUrl($table , $d)
    {
        global $ecms_config,$class_r;
        $c = $class_r[$d['classid']];
        $v = [];
        if($d['filename'] === ''){
            $v['filename'] = $d['id'];
        }else{
            $v['filename'] = $d['filename'];
        }
        $v['newspath'] = $d['newspath'];
        
        $v['titleurl'] = '/'.$c['classpath'].'/'.$v['newspath'].'/'.$v['filename'].$c['classtype'];
        $v['titleurl'] = str_replace('//' , '/' , $v['titleurl']);
        
        $this->api->load('db')->update('[!db.pre!]ecms_'.$table , $v , 'id = '.$d['id']);
    }

    protected function getFields($table){
        
        if(isset($this->tableFieldsCache[$table])){
            return $this->tableFieldsCache[$table];
        }else{
            $fields = $this->api->load('db')->query("SHOW COLUMNS FROM `[!db.pre!]ecms_{$table}`");
            if(!empty($fields)){
                return array_column($fields , null , 'Field');
            }else{
                return [];
            }
        }
    }

    public function getError()
    {
        return $this->error;
    }
}