<?php
class EapiUser
{
    protected $api = null;
    protected $error = null;
    protected $tableFieldsCache = [];

    public function __construct($conf = [] , $api)
    {
        $this->api = $api;
    }

    // 添加
    public function insert($post)
    {
        global $public_r,$ecms_config,$level_r;
        
        if(!is_array($post) || empty($post)){
            $this->error = '参数错误';
            return false;
        }
        
        if(!isset($post['username']) || $post['username'] === '' ){
            $this->error = '用户名不能为空';
            return false;
        }
        
        if(!isset($post['password']) || $post['password'] === '' ){
            $this->error = '登陆密码不能为空';
            return false;
        }
        
        $has = $this->hasUserByUsername($post['username']);
        
        if(false === $has || $has > 0){
            $this->error = '用户名已存在';
            return false;
        }
        
        if(isset($post['userid'])){
            unset($post['userid']);
        }
        
        $data = $this->filterField('enewsmember' , $post);
        
        //groupid
        if(!isset($data['groupid'])){
            $gid = (int)$public_r['defaultgroupid'];
        }else{
            $gid = (int)$data['groupid'];
        }
        if(isset($level_r[$gid])){
            $data['groupid'] = $gid;
        }else{
            $this->error = '会员组不存在';
            return false;
        }
        //userkey
        $data['userkey'] = $this->getRand(12);
        //rnd
        $data['rnd'] = $this->getRand(20);
        //salt
        $data['salt'] = $this->getRand($ecms_config['member']['saltnum']);
        //password
        $data['password'] = $this->createPassword($data['password'] , $data['salt']);
        //checked
        if(!isset($data['checked'])){
            $data['checked'] = $level_r[$gid]['regchecked'] == 1 ? 1 : 0;
            if($data['checked'] && $public_r['regacttype']==1){
                $data['checked'] = 0;
            }
        }
        //registertime
        if(!isset($data['registertime'])){
            $data['registertime'] = time();
        }
        //userfen
        $data['userfen'] = isset($data['userfen']) ? (int)$data['userfen'] : (int)$public_r['reggetfen'];
        
        // 写入主表
        $uid = $this->api->load('db')->insert('[!db.pre!]enewsmember' , $data);
        
        if(false === $uid ){
            $this->error = '数据写入出错';
            return false;
        }
        
        // 写入副表
        $sdata = $this->filterField('enewsmemberadd' , $post);
        $sdata['userid'] = $uid;
        if(!isset($sdata['regip'])){
            $sdata['regip'] = egetip();
        }
        if(!isset($sdata['regipport'])){
            $sdata['regipport'] = egetipport();
        }
        $this->api->load('db')->insert('[!db.pre!]enewsmemberadd' , $sdata);

        return $uid;
    }
    
    // 更新
    public function update($data , $uid = 0)
    {
        global $public_r,$ecms_config,$level_r;

        $user = $this->one($uid , 'userid');
        
        if($user){
            $map = 'userid = '.$user['userid'];
        }else{
            $this->error = '用户不存在';
            return false;
        }
        
        if(isset($data['userid'])){
            unset($data['userid']);
        }
        
        if(isset($data['password'])){
            $data['salt'] = $this->getRand($ecms_config['member']['saltnum']);
            $data['password'] = $this->createPassword($data['password'] , $data['salt']);
        }

        $mdata = $this->filterField('enewsmember' , $data); //主表数据
        
        if(!empty($mdata)){
            $result = $this->api->load('db')->update('[!db.pre!]enewsmember' , $mdata , $map);
        }else{
            $result = true;
        }

        if(false === $result){
            $this->error = '会员主表数据更新失败';
            return false;
        }

        $sdata = $this->filterField('enewsmemberadd' , $data); //副表数据
        
        if(!empty($sdata)){
            $result = $this->api->load('db')->update('[!db.pre!]enewsmemberadd' , $sdata , $map);
            if(false === $result){
                $this->error = '副表更新失败';
            }
            return $result;
        }else{
            return true;
        }
    }

    // 将会员设置成登陆状态
    public function setSession($user , $time = 0)
    {
        $db = $this->api->load('db');
        if(!is_array($user)){
            $map = is_string($user) ? 'username = "'.$user.'"' : 'userid = '.(int)$user;
            $user = $db->one('[!db.pre!]enewsmember'  , 'userid,username,groupid,checked' , $map , 'userid desc');
            if(false === $user){
                $this->error = '没有获取到用户';
                return false;
            }
        }
        if((int)$user['checked'] !== 1){
            $this->error = '用户还有没有通过审核';
            return false;
        }
        
        $rnd = $this->getRand(20);
        $lasttime = time();
        $user['groupid'] = (int)$user['groupid'];
        $lastip = egetip();
        $lastipport = egetipport();
        $time = $time ? time()+ $time : 0;
        //update
        $map = 'userid = '.(int)$user['userid'];
        $db->update("[!db.pre!]enewsmember" , ['rnd' => $rnd] , $map);
        $db->update("enewsmemberadd" , [
            'lasttime' => ['lasttime + 1'],
            'lastip' => $lastip,
            'loginnum' => ['loginnum + 1'],
            'lastipport' => $lastipport
        ] , $map);
        //cookie
        esetcookie("mlusername" , $user['username'] , $time);
        esetcookie("mluserid" , $user['userid'] , $time);
        esetcookie("mlgroupid" , $user['groupid'] , $time);
        esetcookie("mlrnd" , $rnd , $time);
        esetcookie('mlauth', $this->getAuthCode($user['userid'], $user['username'], $user['groupid'] , $rnd) , $time);
        return true;
    }

    // 将会员设置为登出状态
    public function clearSession()
    {
        esetcookie("mlusername","",0);
        esetcookie("mluserid","",0);
        esetcookie("mlgroupid","",0);
        esetcookie("mlrnd","",0);
        esetcookie("mlauth","",0);
    }

    // 检测会员状态
    public function getSession($fields = '*'){
        $userid = (int)getcvar('mluserid');
        $username = RepPostVar(getcvar('mlusername'));
        $rnd = RepPostVar(getcvar('mlrnd'));
        
        if(!$userid || !$username || !$rnd){
            return false;
        }
        
        if($fields !== '*'){
            $fs = $this->api->load('db')->getTableFields('[!db.pre!]enewsmember');
            $temp = is_array($fields) ? $fields : explode(',' , $fields);
            $trueFields = [];
            $allFields = [];
            foreach($temp as $i=>$v){
                $f = explode(' ' , trim($v)); //支持 userid as id 写法
                $f = $f[0];
                if($f !== '' && isset($fs[$f])){
                    $trueFields[] = $v;
                    $allFields[] = $f;
                }
            }
            foreach(['userid' , 'username' , 'userdate' , 'groupid' , 'zgroupid'] as $i){
                if(!in_array($i , $allFields)){
                    $trueFields[] = $i;
                }
            }
            $fields = implode(',' , $trueFields);
            
        }
        
        $user = $this->one($userid , $fields);

        //检测用户是否已过期
        if($user['userdate']){
            if($user['userdate'] - time() <= 0){
                $this->setGroup($user['userid'] , $user['zgroupid']);
                if($user['zgroupid']){
                    $user['groupid'] = $user['zgroupid'];
                    $user['zgroupid'] = 0;
                }
            }
        }
        return $user;
    }

    // 删除
    public function delete($uid)
    {
        $db = $this->api->load('db');
        $map = is_string($uid) ? 'username = "'.$uid.'"' : 'userid = '.(int)$uid;
        $user = $db->one('[!db.pre!]enewsmember' , 'userid' , $map);
        if(false === $user){
            $this->error = '没有查询到相关用户';
            return false;
        }
        $userid = $user['userid'];
        $map = 'userid = '.$userid;
        
        $db->delete('[!db.pre!]enewsmember' , $map);
        $db->delete('[!db.pre!]enewsmemberadd' , $map);
        
        return $userid;
    }

    // 获取验证码或验证
    public function code($name = 'login' , $code = false)
    {
        $name = $name === 'login' ? 'checkloginkey' : 'checkregkey';
        if($code !== false){
            //验证
            return $this->api->load('check')->code($name , $code , 0);
        }else{
            //设置
            esetcookie($name , '' , 0 , 0);
        }
    }

    // 设置用户组
    public function setGroup($uid , $gid)
    {
        $uid = (int)$uid;
        $gid = (int)$gid;
        return $uid ? $this->api->load('db')->update("[!db.pre!]enewsmember" , ['groupid' => $gid , 'userdate' => 0] , "userid=".$uid) : false;
    }

    // 获取指定用户用户名或ID的数据
    public function one($user , $field = '*')
    {
        $map = is_string($user) ? 'username = "'.$user.'"' : 'userid = '.(int)$user;
        return $this->api->load('db')->one('[!db.pre!]enewsmember' , $field , $map);
    }

    // 获取会员列表
    public function getList($field = '*' , $map = '0' , $pagination = '20,1' , $orderby = 'userid desc')
    {
        return $this->api->load('db')->select('[!db.pre!]enewsmember' , $field , $map , $pagination , $orderby);
    }

    // 查询用户是否已存在
    public function hasUser($map)
    {
        return $this->api->load('db')->total('[!db.pre!]enewsmember' , $map);
    }

    public function hasUserByUsername($username)
    {
        return $this->hasUser('username = "'.$username.'"');
    }

    public function hasUserByUserid($userid)
    {
        return $this->hasUser('userid = "'.$userid.'"');
    }

    public function hasUserByEmail($email)
    {
        return $this->hasUser('email = "'.$email.'"');
    }

    // 验证用户帐号与密码是否一值,成功返回会员主表所有数据
    public function checkAccounts($accounts , $password , $type = 'username')
    {
        $map = $type . ' = "' . $accounts .'"';
        $user = $this->api->load('db')->one('[!db.pre!]enewsmember' , '*' , $map , 'userid desc');
        if(false === $user){
            $this->error = '没有查询到用户';
            return false;
        }
        if($this->createPassword($password , $user['salt']) !== $user['password']){
            $this->error = '帐号与密码不匹配';
            return false;
        }
        return $user;
    }

    // 生成密码
    public function createPassword($value , $salt)
    {
        global $ecms_config;
        $type = (int)$ecms_config['member']['pwtype'];
        if($type === 0){
            return md5($value);
        }else if($type === 1){
            return $value;
        }else if($type === 3){
            return substr(md5($value),8,16);
        }else{
            return md5(md5($value).$salt);
        }
    }

    // 登陆验证字符
    public function getAuthCode($userid , $username , $groupid , $rnd)
    {
        global $ecms_config;
        return $code = md5(md5($rnd.'--d-i!'.$userid.'-(g*od-'.$username.$ecms_config['cks']['ckrndtwo'].'-'.$groupid).'-#empire.cms!--p)h-o!me-'.$ecms_config['cks']['ckrndtwo']);
    }

    protected function filterField($table , $data)
    {
        if(empty($data) || !is_array($data)){
            return [];
        }
        $fields = $this->api->load('db')->getTableFields('[!db.pre!]'.$table);
        foreach($data as $i=>$v){
            if(!isset($fields[$i])){
                unset($data[$i]);
            }
        }
        return $data;
    }

    // 获取随即字符
    protected function getRand($len)
    {
        return make_password($len);
    }

    public function getError()
    {
        return $this->error;
    }
}