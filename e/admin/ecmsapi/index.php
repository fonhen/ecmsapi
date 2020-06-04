<?php
define('EmpireCMSAdmin','1');
require("../../class/connect.php");
require("../../class/db_sql.php");
require("../../class/functions.php");
$apiFolder = 'ecmsapi/';
$apiDir = ECMS_PATH . $apiFolder;
$link=db_connect();
$empire=new mysqlquery();
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

require($apiDir.'EcmsApi.php');
require('common.php');

$apiConf = require($apiDir.'_common/conf.php');

if(!is_file('install.lock')){
	if(false === file_put_contents('./install.lock' , $loginlevel)){
		exit('请检查'. __DIR__ .'目录权限');
	}
}else{
	$apiLevel = file_get_contents('./install.lock');
	if(empty($apiLevel)){
		printerror2('未设置权限或读取不到' . __DIR__ . '/install.lock的内容');
	}
	$apiLevel = explode(',' , $apiLevel);
	if(!in_array($loginlevel , $apiLevel)){
		printerror2('权限不足');
	}
}

$api = new EcmsApi();

$act = strtolower($api->get('act'));

$act = in_array($act , ['index' , 'list' , 'edit' , 'del' , 'mod' , 'fun' , 'conf' , 'level']) ? $act : 'index';

$allMod = api_get_all_mod();

require('./act/'.$act.'.php');







