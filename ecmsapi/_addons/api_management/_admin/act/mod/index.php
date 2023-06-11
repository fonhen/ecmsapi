<?php
defined('EmpireCMSAdmin') or die;

$modLink = $addonLink . '&act=api&m='.$name;

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../adminstyle/1/adminstyle.css" rel="stylesheet" type="text/css">
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
            位置：<a href="<?=$addonLink?>">API管理</a>&nbsp;>&nbsp;<a href="<?=$addonLink?>&act=mod&name=<?=$name?>"><?=$mod['name']?></a>&nbsp;>&nbsp;接口列表
        </td>
        <td>
            <div align="right" class="emenubutton">
                <input type="button" value="添加接口" onclick="self.location.href='<?=$modLink?>&do=edit';">
                &nbsp;
                <input type="button" value="自定义函数库" onclick="self.location.href='<?=$addonLink?>&act=mod&name=<?=$name?>&do=function';">
            </div>
        </td>
    </tr>
</table>

		
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td width="100" align="center">预览</td>
        <td width="150" align="center">标识</td>
        <td width="200" align="center">名称</td>
        <td>说明</td>
        <td width="100" align="center">状态</td>
        <td width="240" height="25"  align="center">操作</td>
    </tr>
    <?php foreach($mod['list'] as $key=>$v):?>
    
    <tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
        <td align="center"><a href="../../../ecmsapi/index.php?mod=<?=$name?>&act=<?=$key?>" target="_blank">[预览]</a></td>
        <td align="center"><a href="<?=$modLink?>&name=<?=$key?>&do=edit"><?=$key?></a></td>
        <td align="center"><a href="<?=$modLink?>&name=<?=$key?>&do=edit"><?=$v['name']?></a></td>
        <td style="color:#999;" height="28"><?=$v['description']?></td>
        <td align="center"><?= $v['open'] ? '正常':'关闭' ?></td>
        <td align="center">
            <a href="<?=$modLink?>&name=<?=$key?>&do=edit">编辑</a>
            &nbsp;&nbsp;
            <a href="javascript:;" onclick="del('<?=$modLink?>&name=<?=$key?>&do=delete')">删除</a>
        </td>
    </tr>
    <?php endforeach;?>

    <?php if(empty($mod['list'])): ?>
    <tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
        <td colspan="6" align="center" style="color: #999;" height="28">
            当前没有接口
        </td>
    </tr>
    <?php endif;?>
</table>

<br/>

<div>注：预览功能,仅仅只是简单的仿问到模块的接口上,其它参数请自行拼写</div>
</body>
</html>
