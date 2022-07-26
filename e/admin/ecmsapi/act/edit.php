<?php
defined('EmpireCMSAdmin') or die;
$m = strtolower($api->get('m'));
if($m === '' || !isset($allMod[$m])){
	printerror2('参数错误');
}
$mData = $allMod[$m];
$a = strtolower($api->get('a'));

if($a !== '' && !isset($mData['list'][$a])){
	printerror2('参数错误');
}

if($api->isPost()){
	// post
	$post = [];
	$post['name'] = $api->post('name');
	if($post['name'] === ''){
		printerror2('接口名称不能为空');
	}
	$post['a'] = $api->post('a');
	if($post['a'] === ''){
		printerror2('接口文件名不能为空');
	}
	if(!preg_match("/^[a-z0-9\.]+$/" , $post['a'])){
		printerror2('接口文件名只能由字母与数字组成');
	}
	if($post['a'] !== $a && isset($mData['list'][$post['a']])){
		printerror2('接口文件名已被占用');
	}
	$post['open'] = $api->post('open' , 0 , 'intval');
	$post['code'] = $api->post('code');
	$post['description'] = $api->post('description');
	
	$result = api_set_act_code($m , $a , $post);
	
	if(false !== $result){
		printerror2('操作成功' , 'index.php'.$ecms_hashur['whehref'].'&act=edit&m='.$m.'&a='.$post['a']);
	}else{
		printerror2('操作失败');
	}
}else{
	if($a === ''){
		$title = '添加接口';
		$code = '';
		$aData = ['open' => 1 , 'description' => '' , 'name' => ''];
	}else{
		$title = $mData['list'][$a]['name'];
		$aData = $mData['list'][$a];
		$code = api_get_act_code($m , $a);
	}
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td height="26">位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">接口管理</a> &gt; <a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$m?>"><?=$mData['name']?></a>  &gt; <?=($a ? '<a href="'. api_demo_url($m , $a).'" target="_blank">'.$title.'</a>' : $title)?></td>
  </tr>
</table>

<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>&a=<?=$a?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2"><?=$title?></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="150" height="25">接口文件名:(*)</td>
    <td height="25"><input name="a" type="text" value="<?=$a?>" size="42"> (由小写字母与数字组成)</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">状态</td>
    <td height="25"><input type="checkbox" value="1" name="open" <?=($aData['open'] ? 'checked="checked"' : '')?>>开启</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">名称:</td>
    <td height="25"><input name="name" type="text" value="<?=$aData['name']?>" size="42"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">说明</td>
    <td height="25"><textarea name="description" cols="60" rows="3"><?=$aData['description']?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td>程序代码</td>
    <td><textarea name="code" style="width:100%; height:500px;" id="code"><?=htmlspecialchars($code)?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">&nbsp;</td>
    <td height="25">
			<button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
		</td>
  </tr>
</table>
</form>
<script type="text/javascript" src="https://cdn.staticfile.org/require.js/2.3.6/require.min.js" data-main="js/ace"></script>
</body>
</html>

