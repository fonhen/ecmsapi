<?php
defined('EmpireCMSAdmin') or die;


if($api->isPost()){
	$post['mod'] = strtolower($api->post('mod'));
	$post['act'] = strtolower($api->post('act'));
	
	if(!preg_match("/^[a-z]+$/" , $post['mod'])){
		printerror2('模块变量只能由英文字母组成');
	}
	if(!preg_match("/^[a-z]+$/" , $post['act'])){
		printerror2('接口变量只能由英文字母组成');
	}
	if($post['mod'] === $post['act']){
		printerror2('模块变量不能与接口变量相同');
	}
	
	if($post['mod'] === $apiConf['mod'] && $post['act'] === $apiConf['act']){
		printerror2('操作成功' , 'index.php'.$ecms_hashur['whehref'].'&act=conf&time='.time());
	}
	
	$cpath = $apiDir.'_common/conf.php';
	
	$result = api_build_conf($cpath , $post);
	
	if(false !== $result ){
		printerror2('操作成功' , 'index.php'.$ecms_hashur['whehref'].'&act=conf&time='.time());
	}else{
		printerror2('保存失败，请检查目录权限。' );
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
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">模块管理</a> &gt; 基本设置</td>
  </tr>
</table>
<form method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=conf">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2">基本设置</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="200" height="25">默认模块获取变量</td>
    <td height="25"><input name="mod" type="text" value="<?=$apiConf['mod']?>" size="42"> 字母,区分大小写</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">默认接口获取变量</td>
    <td height="25"><input name="act" type="text" value="<?=$apiConf['act']?>" size="42"> 字母,区分大小写</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">示例(注意红色部分)</td>
    <td height="25" style="color:#555;">/ecmsapi/index.php?<b style="color:red;"><?=$apiConf['mod']?></b>=[模块路径]&<b style="color:red;"><?=$apiConf['act']?></b>=[接口文件名]<br/></td>
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
