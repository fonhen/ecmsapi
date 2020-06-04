<?php
defined('EmpireCMSAdmin') or die;
$m = strtolower($api->get('m'));
if($m === '' || !isset($allMod[$m])){
	printerror2('参数错误');
}
$mData = $allMod[$m];

$a = $api->get('a');

if($a !== '' && !isset($mData['list'][$a])){
	printerror2('删除成功');
}

if($a !== ''){
	// 删除接口
	if(false === api_del_act($m , $a)){
		printerror2('删除失败');
	}else{
		printerror2('操作成功' , 'index.php'.$ecms_hashur['whehref'].'&act=list&m='.$m.'&time='.time());
	}
}else{
	// 删除模块
	if(false === api_del_mod($m)){
		printerror2('删除失败');
	}else{
		printerror2('成功删除模块'.$m , 'index.php'.$ecms_hashur['whehref'].'&act=index&time='.time());
	}
}
