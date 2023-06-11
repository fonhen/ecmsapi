<?php
defined('EmpireCMSAdmin') or die;

$mods = adminGetApiModByName();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API接口管理 - 管理中心</title>
<link href="../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
<script>
function del(link , msg){
    msg = msg || '确认要删除此所选数据吗，删除后将无法恢复!';
    if(confirm(msg)){
        self.location.href = link;
    }
}
</script>
</head>
<body>

<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr> 
    <td>
        位置：<a href="<?=$addonLink?>">API管理</a>&nbsp;>&nbsp;管理首页
    </td>
    <td align="right">
        <div align="right" class="emenubutton">
            <input type="button" value="增加模块" onclick="self.location.href='<?=$addonLink?>&act=mod&do=edit';">
            &nbsp;
            <input type="button" value="权限设置" onclick="self.location.href='<?=$addonLink?>&act=power';">
            &nbsp;
            <input type="button" value="自定义函数库" onclick="self.location.href='<?=$addonLink?>&act=function';">
        </div>
    </td>
  </tr>
</table>

<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td width="150" align="center">模块</td>
        <td width="200" align="center">名称</td>
        <td height="28" >说明</td>
        <td width="100" align="center">开启状态</td>
        <td width="240" align="center">操作</td>
    </tr>
    <?php foreach($mods as $name=>$r):?>
    <?php
    $link = $addonLink . '&act=mod&name=' . $name;
    ?>
    <tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
        <td align="center"><a href="<?=$link?>"><?=$name?></a></td>
        <td align="center"><a href="<?=$link?>"><?=$r['name']?></a></td>
        <td style="color:#999;" height="28" ><?=$r['description']?></td>
        <td align="center"><?=$r['open'] ? '是' : '否'?></td>
        <td align="center">
            <a href="<?=$link?>&do=index">管理</a>
            &nbsp;&nbsp;
            <a href="<?=$link?>&do=edit">编辑</a>
            &nbsp;&nbsp;
            <a href="javascript:;" onclick="del('<?=$link?>&do=delete')">删除</a>
        </td>
    </tr>
    <?php endforeach;?>

    <?php if(empty($mods)): ?>
    <tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
        <td colspan="5" height="28" style="color:#999;" align="center">
            当前没有可管理的模块
        </td>
    </tr>
    <?php endif;?>
</table>

</body>
</html>
