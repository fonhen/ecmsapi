<?php
defined('EmpireCMSAdmin') or die;
$m = strtolower($api->get('m'));
if($m === '' || !isset($allMod[$m])){
	printerror2('参数错误');
}
$data = $allMod[$m];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
<script>
function del(name){
	if(confirm('确认要删除此控制器吗，删除后将无法恢复!')){
		self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=del&m=<?=$m?>&a=' + name;
	}
}
</script>
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">模块管理</a> &gt; <a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$m?>"><?=$data['name']?>(<?=$m?>)</a> &gt; 接口管理</td>
		 <td><div align="right" class="emenubutton">
        <input type="button" value="增加接口" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>';">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="自定义函数库" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=fun&m=<?=$m?>';">
      </div></td>
  </tr>
</table>

		
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
	<tr class="header">
		<td width="100" align="center">预览</td>
		<td width="150" align="center">控制器</td>
		<td width="200" align="center">名称</td>
		<td>说明</td>
		<td width="100" align="center">状态</td>
		<td width="240" height="25"  align="center">操作</td>
	</tr>
	<?php
	if(empty($data['list'])){
	?>
	<tr bgcolor="#FFFFFF" align="center" height="30">
		<td colspan="6" style="color:#555;">没有相关数据</td>
	</tr>
	<?php
	}else{
		foreach($data['list'] as $k=>$r){
	?>
	<tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
		<td align="center"><a href="<?=api_demo_url($m , $k)?>" target="_blank">[预览]</a></td>
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>&a=<?=$k?>"><?=$k?></a></td>
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>&a=<?=$k?>"><?=$r['name']?></a></td>
		<td style="color:#666;"><?=$r['description']?></td>
		<td align="center"><?=($r['open'] ? '正常' : '已关闭')?></td>
		<td align="center"><button type="button" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>&a=<?=$k?>';">修改</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del('<?=$k?>')">删除</button></td>
	</tr>
	<?php
		}
	}
	?>
</table>

<br/>

<div>注：预览功能,仅仅只是简单的仿问到模块的接口上,其它参数请自行拼写</div>
</body>
</html>

