<?php
require(dirname(__DIR__) . '/e/class/connect.php');
require(ECMS_PATH . '/e/class/EmpireCMS_version.php');
require(ECMS_PATH . '/e/class/db_sql.php');
require(ECMS_PATH . '/e/data/dbcache/class.php');
require(ECMS_PATH . '/e/data/dbcache/MemberLevel.php');
require(ECMS_PATH . '/e/class/userfun.php');
if(!class_exists('EcmsApi')){
    require('EcmsApi.php');
}

function api_mod_conf($mod)
{
    $path = './_mod/'.$mod.'/_conf.php';
    if(is_file($path)){
        $conf = require($path);
        return is_array($conf) && !empty($conf) ? $conf : false;
    }else{
        return false;
    }
}

function api_error_format($api , $msg = ''){
    $api->error($msg);
    exit;
}

$link = db_connect();
$empire = new mysqlquery();
$api = new EcmsApi();

require('./_common/function.php');
$config = require("./_common/conf.php");

$mod = strtolower($api->param($config['mod']));
$act = strtolower($api->param($config['act']));

if($mod === '' || $act === ''){
	api_error_format($api , '参数错误');
}

$modConf = api_mod_conf($mod);
if(false === $modConf){
	api_error_format($api , '模块加载出错');
}
if(!$modConf['open']){
	api_error_format($api , '模块禁止访问');
}

if(!isset($modConf['list'][$act])){
    api_error_format($api , '方法'.$act.'未定义');
}
if(!$modConf['list'][$act]['open']){
    api_error_format($api , '方法'.$act.'已禁用');
}
$actPath = './_mod/'.$mod.'/'.$act.'.php';
if(!is_file($actPath)){
    api_error_format($api , '方法'.$act.'加载出错');
}

define('ECMSAPI_MOD' , $mod);
define('ECMSAPI_ACT' , $act);

$funPath = './_mod/'.$mod.'/_function.php';
if(is_file($funPath)){
    require($funPath);
}

require($actPath);

db_close();
$empire = null;
$api = null;