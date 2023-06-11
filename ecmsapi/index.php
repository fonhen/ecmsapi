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
    function api_die($message = '' , $code = 0 , $data = []){
        global $api;
        $api->json(['code' => $code , 'message' => $message , 'data' => $data]);
    }
}


$link = db_connect();
$empire = new mysqlquery();
$api = new EcmsApi();


define('ECMSAPI_MOD' , strtolower($api->param('mod' , '' , 'trim')));
define('ECMSAPI_ACT' , strtolower($api->param('act' , '' , 'trim')));
define('ECMSAPI_ADDON' , strtolower($api->param('addon' , '' , 'trim')));

if(ECMSAPI_ADDON === '' && (ECMSAPI_MOD === '' || ECMSAPI_ACT === '')){
    api_die('参数错误');
}

// 支持命名空间，自动加载
spl_autoload_register(function($name){
    $autoLoadPath =  ECMS_PATH . 'ecmsapi/' (ECMSAPI_ADDON === '' ? '_mod/' . ECMSAPI_MOD : '_addons/' . ECMSAPI_ADDON) . '/_src/';
    $file = $autoLoadPath . str_replace('\\' , DIRECTORY_SEPARATOR , $name) . '.php';
    if(file_exists($file)){
        include($file);
    }
});

if(ECMSAPI_ADDON === ''){

    require('./_common/function.php');
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

}else{

    // 插件方式
    try{
        $addonClass = $api->load('addons' , ECMSAPI_ADDON , false);
        $runFile = $addonClass->path('_home/_run.php');
    }catch(Exception $e){
        api_die($e->getMessage());
    }
    
    if(!is_file($runFile)){
        api_die('插件'.ECMSAPI_ADDON.'加载出错');
    }

    require($runFile);


}

db_close();
$empire = null;
$api = null;