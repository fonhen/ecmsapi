<?php
class EapiDb
{
    protected $empire = null;
    protected $dbtbpre = '';
    protected $api = null;
    protected $tableFieldsCache = [];
    protected $errno = 0;

    public function __construct($conf = [] , $api = null)
    {
        global $link,$empire,$dbtbpre;
        $this->empire = $empire;
        $this->dbtbpre = $dbtbpre;
        $this->api = $api;
    }
    
    public function startTrans()
    {
        $this->errno = 0;
        $this->query('begin;');
    }
    
    public function endTrans()
    {
        if( $this->errno > 0){
            $this->query('rollback;');
            $this->errno = 0;
            return false;
        }else{
            $this->query('commit;');
            return true;
        }
    }

    public function query($sql , $exit = false){
        $sql = $this->sql($sql);
        $obj = !$exit ? $this->empire->query1($sql) : $this->empire->query($sql);
        if(is_bool($obj)){
            !$obj AND $this->errno++;
            return $obj;
        }
        $result = [];
        while($r = $this->empire->fetch($obj)){
            $data = [];
            foreach($r as $i=>$v){
                if(is_string($i)){
                    $data[$i] = $v;
                }
            }
            $result[] = $data;
        }
        return $result;
    }

    public function select($table , $field = '*' , $map = '0' , $pagination = '20,1' , $orderby = '')
    {
        $temp = explode(',' , $pagination.',1,1');
        $limit = (int)$temp[0];
        $limit = $limit > 0 ? $limit : 20;
        $limit = $limit > 2000 ? 2000 : $limit;
        $page = (int)$temp[1];
        $page = $page > 1 ? $page : 1;
        $offset = ($page-1) * $limit;
        $orderby = $orderby ? 'order by '.$orderby : '';
        $field = trim($field) !== '' ? trim($field) : '*';
        $sql = "select {$field} from {$table} where {$map} {$orderby} limit {$offset},{$limit};";
        return $this->query($sql , false);
    }

    public function insert($table , $data = [])
    {
        if(empty($table) || empty($data) || !is_array($data)){
            return false;
        }
        $field = "";
        $value = "";
        foreach($data as $f=>$v){
            $field .= "," . $f;
            $value .= ",'" . $v ."'";
        }
        $field = substr($field , 1);
        $value = substr($value , 1);
        $sql = "insert into {$table} ({$field}) values ({$value});";
        $res = $this->query($sql , false);
        if(true === $res){
            return $this->empire->lastid();
        }else{
            return false;
        }
    }

    public function insertAll($table , $datas)
    {
        if(empty($table) || empty($datas) || !is_array($datas)){
            return false;
        }
        $field = "";
        $values = "";
        $num = 0;
        foreach($datas as $i=>$data){
            if(empty($data) || !is_array($data)){
                return false;
            }
            $value = "";
            foreach($data as $f=>$v){
                if($i === 0){
                    $field .= "," . $f;
                }
                $value .= ",'" . $v ."'";
            }
            $values .= ",(".substr($value , 1).")";
            $num++;
        }
        $field = substr($field , 1);
        $values = substr($values , 1);
        $sql = "insert into {$table} ({$field}) values {$values};";
        $res = $this->query($sql , false);
        if(true === $res){
            return $num;
        }else{
            return false;
        }
    }

    public function update($table = '' , $data = '' , $map = '0'){
        if(empty($table) || empty($data) || (!is_string($data) && !is_array($data))){
            return false;
        }
        if(is_string($data)){
            $setField = $data;
        }else{
            $setField = "";
            foreach($data as $f=>$v){
                $v = !is_array($v) ? "'{$v}'" : $v[0]; 
                $setField .= ",{$f}={$v}";
            }
            $setField = substr($setField , 1);
        }
        $sql = "update {$table} set {$setField} where {$map}";
        return $this->query($sql , false);
    }

    public function delete($table , $map = '0')
    {
        if(empty($table)){
            return false;
        }
        $sql = "delete from {$table} where {$map};";
        return $this->query($sql , false);
    }

    public function one($table , $field = '*' ,$map = '' , $orderby = '')
    {
        if(empty($table)){
            return false;
        }
        if($map === ''){
            $sql = $table;
        }else{
            $orderby = $orderby !== '' ? 'order by '.$orderby : '';
            $sql = "select {$field} from {$table} where {$map} {$orderby} limit 0,1;";
        }
        $datas = $this->query($sql , false);
        if(empty($datas)){
            return false;
        }else{
            return $datas[0];
        }
    }

    public function getByPk($table , $value , $field = '*' ,$pk = 'id')
    {
        if(empty($table)){
            return false;
        }
        $map = "{$pk} = '{$value}'";
        return $this->one($table , $field , $map);
    }

    public function total($table , $map = '')
    {
        if($map !== ''){
            $sql = "select count(*) as total from {$table} where {$map};";
        }else{
            $sql = $table;
        }
        $reslut = $this->one($sql);
        return false !== $reslut ? (int)current($reslut) : false;
    }

    public function getTableFields($table)
    {
        if(isset($this->tableFieldsCache[$table])){
            return $this->tableFieldsCache[$table];
        }else{
            $fields = $this->api->load('db')->query("SHOW COLUMNS FROM `{$table}`");
            if(!empty($fields)){
                return array_column($fields , null , 'Field');
            }else{
                return [];
            }
        }
    }

    protected function sql($sql)
    {
        return str_replace('[!db.pre!]'  , $this->dbtbpre , $sql);
    }
}