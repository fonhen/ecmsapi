<?php
defined('EmpireCMSAdmin') or die;

// 获取模块名称
$name = $api->get('name' , '' , 'trim');

// 获取操作方式
$do = $api->get('do' , 'index' , 'trim');

$mods = adminGetApiModByName();

if($do !== 'edit' && $name === ''){
    printerror2('未指定模块名称');
}

if($name !== '' && !isset($mods[$name])){
    printerror2('指定模块不存在');
}




// 获取当前模型的信息
$mod = $name !== '' ? $mods[$name] : [
    'name'          => '',
    'open'          => 1,
    'description'   => '',
    'list'          => []
];


$file = __DIR__ . '/mod/' . $do . '.php';

if(is_file($file)){
    include($file);
}else{
    printerror2('参数错误');
}


?>