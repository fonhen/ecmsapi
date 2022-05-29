<?php
require(dirname(__DIR__) . '/e/class/connect.php');
require(ECMS_PATH . '/e/class/EmpireCMS_version.php');
require(ECMS_PATH . '/e/class/db_sql.php');
require(ECMS_PATH . '/e/class/t_functions.php');
require(ECMS_PATH . '/e/class/functions.php');
require(ECMS_PATH . '/e/data/dbcache/class.php');
require(ECMS_PATH . '/e/data/dbcache/MemberLevel.php');

if(!class_exists('EcmsApi' , false)){
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

if(!function_exists('api_die')){
    function api_die($message = ''){
        global $api;
        $api->json(['code' => 0 , 'message' => $message , 'data' => []]);
    }
}

// 支持命名空间，自动加载
spl_autoload_register(function($name){
    $file = ECMS_PATH . 'ecmsapi/_mod/' . ECMSAPI_MOD . '/_src/' . str_replace('\\' , DIRECTORY_SEPARATOR , $name) . '.php';
    if(file_exists($file)){
        include($file);
    }else{
        api_die('错误信息：' . $name . '类加载失败');
    }
});


$link = db_connect();
$empire = new mysqlquery();
$api = new EcmsApi();

require('./_common/function.php');
$config = require("./_common/conf.php");

define('ECMSAPI_MOD' , strtolower($api->param($config['mod'])) );
define('ECMSAPI_ACT' , strtolower($api->param($config['act'])) );

if(ECMSAPI_MOD === '' || ECMSAPI_ACT === ''){
	api_die('参数错误');
}

$modConf = api_mod_conf(ECMSAPI_MOD);
if(false === $modConf){
	api_die('模块加载出错');
}
if(!$modConf['open']){
	api_die('模块禁止访问');
}

if(!isset($modConf['list'][ECMSAPI_ACT])){
    api_die('方法'.ECMSAPI_ACT.'未定义');
}
if(!$modConf['list'][ECMSAPI_ACT]['open']){
    api_die('方法'.ECMSAPI_ACT.'已禁用');
}
$actPath = './_mod/'.ECMSAPI_MOD.'/'.ECMSAPI_ACT.'.php';
if(!is_file($actPath)){
    api_die('方法'.ECMSAPI_ACT.'加载出错');
}

$funPath = './_mod/'.ECMSAPI_MOD.'/_function.php';
if(is_file($funPath)){
    require($funPath);
}

require($actPath);

db_close();
$empire = null;
$api = null;