<?php
defined('EmpireCMSAdmin') or die;

$functionFile = ECMS_PATH . 'ecmsapi/_common/function.php';

if(!is_file($functionFile)){
    $result = file_put_contents($functionFile , '');
    if(false === $result){
        printerror2('请检查 _common 目录权限是否可写');
    }
}

if($api->isPost()){
    $code = $api->post('code' , '' , 'trim');
    $code = htmlspecialchars_decode($code);

    $result = file_put_contents($functionFile , $code);

    if(false === $result){
        printerror2('请检查 _common 目录权限是否可写');
    }else{
        printerror2('操作成功');
    }

}

$code = file_get_contents($functionFile);
if(false === $code){
    printerror2('请检查_common 目录权限是否可读');
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body style="min-width:900px;">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>
        位置：<a href="<?=$addonLink?>">API管理</a>&nbsp;>&nbsp;全局自定义函数库
    </td>
  </tr>
</table>
<form name="form1" method="post" action="<?=$addonLink?>&act=function">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td height="25">全局自定义函数库</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td style="padding:0;">
            <textarea name="code" id="code" data-autofocus="true" style="width:100%; height:600px;"><?=htmlspecialchars($code)?></textarea>
        </td>
    </tr>
    <tr bgcolor="#ffffff">
        <td height="25" align="center">
            <button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
        </td>
    </tr>
</table>
<div style="padding:10px 0;">
	此功能需要有一定php基础,如果出错可能会引起相关api失效
</div>
</form>
<?php
include $addonFolderLink . '_admin/ace.js.php';
?>
</body>
</html>