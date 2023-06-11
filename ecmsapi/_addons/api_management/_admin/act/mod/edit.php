<?php
defined('EmpireCMSAdmin') or die;

if($api->isPost()){

    $key = $api->post('key' , '' , 'trim');
    

    $mod['name'] = $api->post('name' , '' , 'trim');
    if($mod['name'] === ''){
        printerror2('模块名称不能为空');
    }

    if($key === ''){
        printerror2('模块标记不能为空');
    }

    $key = strtolower($key);

    if(!preg_match("/^[a-z0-9]+$/" , $key)){
        printerror2('模块标记只能由字母与数字组成');
    }

    if($key !== $name && isset($mods[$key])){
        printerror2('模块标记已被占用');
    }


    $path = ECMS_PATH . 'ecmsapi/_mod/';

    if($key !== $name){
        if($name === ''){
            $result = mkdir($path . $key , 0777);
        }else{
            $result = rename($path . $name , $path . $key);
        }
        if(false === $result){
            printerror2('模块目录保存失败 请检查 _mod 目录权限');
        }
    }

    $mod['description'] = $api->post('description' , '' , 'trim');
    $mod['open'] = $api->post('open' , 0 , 'intval');

    
    $result = adminBuildConfig($path . $key . '/_conf.php' , $mod);

    if(false === $result){
        printerror2('操作失败 请检查 _mod 目录权限');
    }else{
        printerror2('操作成功' , $addonLink);
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
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
    <tr>
        <td>
            位置：<a href="<?=$addonLink?>">API管理</a>&nbsp;>&nbsp;<?=$name ? '模块编辑' : '模块添加'?>
        </td>
    </tr>
</table>
<form name="form1" method="post" action="<?=$addonLink?>&act=mod&name=<?=$name?>&do=edit">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td height="25" colspan="2">模块信息</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td width="150" height="25">标记:(*)</td>
        <td height="25"><input name="key" type="text" value="<?=$name?>" size="42"> (由字母与数字组成)</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">名称:(*)</td>
        <td height="25"><input name="name" type="text" value="<?=$mod['name']?>" size="42"></td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">说明</td>
        <td height="25"><textarea name="description" cols="60" rows="6"><?=$mod['description']?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">状态</td>
        <td height="25"><input type="checkbox" value="1" name="open" <?=($mod['open'] ? 'checked="checked"' : '')?>>开启</td>
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
