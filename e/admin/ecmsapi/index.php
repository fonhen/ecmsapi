<?php
define('EmpireCMSAdmin','1');
require("../../class/connect.php");
require("../../class/db_sql.php");
require("../../class/functions.php");
require "../".LoadLang("pub/fun.php");
require(ECMS_PATH . '/e/data/dbcache/class.php');
$link=db_connect();
$empire=new mysqlquery();

require(ECMS_PATH.'ecmsapi/EcmsApi.php');

$editor=2;
//验证用户
$lur=is_login();
$logininid=$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();
$ecms_hashur['whehref'] = !isset($ecms_hashur['whehref']) || trim($ecms_hashur['whehref']) === '' ? '?_hash=' : $ecms_hashur['whehref'];

$api = new EcmsApi();

$addonName = $api->get('addons' , '' , 'trim');

// 当前插件的控制器文件
$act = $api->get('act' , 'index' , 'trim');

// 插件当前链接
$addonLink = 'index.php' . $ecms_hashur['whehref'] . '&addons=' . $addonName;

try{
    // 获取当前插件对象
    $addonClass = $api->load('addons' , $addonName , false);
}catch(Exception $e){
    printerror2($e->getMessage());
}

$addonFolder = $addonClass->getAdminFolder();
$addonFolderLink = $addonClass->getAdminFolderLink();

$commonFile = $addonFolder . 'common.php';
if(is_file($commonFile)){
    include $commonFile;
}

$filepath = $addonFolder . '/act/'.$act.'.php';

if(is_file($filepath)){
    include($filepath);
}else{
    printerror2('参数错误');
}

db_close();
$empire=null;
?>