<?php
defined('EmpireCMSAdmin') or die;

$m = $api->get('m');

if($m !== '' && !isset($allMod[$m])){
	printerror2('参数错误');
}

if($api->isPost()){
	// 保存模块
	
	$post = [];
	$post['name'] = $api->post('name');
	$post['m'] = $api->post('m');
	$post['open'] = $api->post('open' , 0 , 'intval');
	$post['description'] = $api->post('description');

	if($post['name'] === ''){
		printerror2('模块名称不能为空');
	}
	
	if($post['m'] === ''){
		printerror2('模块路径不能为空');
	}
	
	if(!preg_match("/^[a-z0-9]+$/" , $post['m'])){
		printerror2('模块路径只能由字母与数字组成');
	}
	
	if($post['m'] !== $m && isset($allMod[$post['m']])){
		printerror2('模块路径已被占用');
	}
	
	$result = api_save_mod($m , $post);
	
	if(false === $result){
		printerror2('操作失败');
	}else{
		printerror2('操作成功' , 'index.php'.$ecms_hashur['whehref'].'&act=index&time='.time());
	}
	
}else{
	// 编辑模块
	if($m === ''){
		$data = [
			'open' => 1,
			'name' => '',
			'description' => ''
		];
		$title = '添加模块';
	}else{
		$data = $allMod[$m];
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
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">模块管理</a> &gt; <?=$title?></td>
  </tr>
</table>
<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=mod&m=<?=$m?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2"><?=$title?></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="150" height="25">模块:(*)</td>
    <td height="25"><input name="m" type="text" value="<?=$m?>" size="42"> (由小写字母组成)</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">名称:(*)</td>
    <td height="25"><input name="name" type="text" value="<?=$data['name']?>" size="42"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">说明</td>
    <td height="25"><textarea name="description" cols="60" rows="6"><?=$data['description']?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">状态</td>
    <td height="25"><input type="checkbox" value="1" name="open" <?=($data['open'] ? 'checked="checked"' : '')?>>开启</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">&nbsp;</td>
    <td height="25">
			<button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
		</td>
  </tr>
</table>

</form>
</body>
</html>
