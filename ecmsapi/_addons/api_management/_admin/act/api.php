<?php
defined('EmpireCMSAdmin') or die;

// 获取模块名称
$m = $api->get('m' , '' , 'trim');

if($m === ''){
    printerror2('未指定模块名称');
}

// 获取接口名称
$name = $api->get('name' , '' , 'trim');

// 获取操作方式
$do = $api->get('do' , 'index' , 'trim');


$mods = adminGetApiModByName($m);

if(!isset($mods[$m])){
    printerror2('指定模块不存在');
}

// 获取当前模型的信息
$mod = $mods[$m];


if($name !== '' && !isset($mod['list'][$name])){
    printerror2('指定接口不存在');
}

// 获取当前模块下的接口
$apiList = $mod['list'];


// 当前模型的路径
$modDir = ECMS_PATH . 'ecmsapi/_mod/' . $m . '/';


$file = __DIR__ . '/api/' . $do . '.php';

if(is_file($file)){
    include($file);
}else{
    printerror2('参数错误');
}