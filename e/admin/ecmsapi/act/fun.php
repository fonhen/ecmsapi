<?php
defined('EmpireCMSAdmin') or die;

$m = $api->get('m');

if($m !== '' && !isset($allMod[$m])){
	printerror2('参数错误');
}

if($api->isPost()){
	$code = $api->post('code');
	if(false === api_save_mod_fcode($m , $code)){
		printerror2('保存文件失败，请检查文件夹权限');
	}else{
		printerror2('操作成功');
	}
}else{
	// 编辑函数
	$code = api_get_mod_fcode($m);
	if($m === ''){
		$title = '全局函数库编辑';
	}else{
		$data = $allMod[$m];
		$title = '自定义函数库编辑';
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>接口管理</title>
<link href="../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body style="min-width:900px;">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">模块管理</a> &gt;<?php if($m !== ''){?> <a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$m?>"><?=$data['name']?>(<?=$m?>)</a> &gt;<?php }?> <?=$title?></td>
  </tr>
</table>
<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=fun&m=<?=$m?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25"><?=$title?></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td><textarea name="code" id="code" data-autofocus="true" style="width:100%; height:600px;"><?=htmlspecialchars($code)?></textarea></td>
  </tr>
	<tr bgcolor="#f4f4f4">
    <td height="25" align="center">
			<button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
		</td>
  </tr>
</table>
<div style="padding:10px 0;">
	此功能需要有一定php基础,如果出错可能会引起相关api失效
</div>
</form>
<script type="text/javascript" src="https://cdn.staticfile.org/require.js/2.3.6/require.min.js" data-main="js/ace"></script>
</body>
</html>
