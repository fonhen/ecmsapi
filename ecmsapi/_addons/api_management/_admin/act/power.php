<?php
defined('EmpireCMSAdmin') or die;

if($api->isPost()){

    $gids = $api->post('gid');

    if(empty($gids)){
        printerror2('请至少设置一个管理组');
    }

    if(!is_array($gids)){
        printerror2('非法操作');
    }

    $temp = [];
    foreach($gids as $v){
        $temp[] = (int)$v;
    }

    if(false === file_put_contents($adminApiInstall , implode(',' , $temp))){
        printerror2('设置失败，请检查权限。');
    }
	printerror2('设置成功');
}



$sql = $empire->query("select groupid,groupname from {$dbtbpre}enewsgroup order by groupid limit 100");
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
    <td>
        位置：<a href="<?=$addonLink?>">API管理</a> &gt; 权限管理
    </td>
  </tr>
</table>
<form name="form1" method="post" action="<?=$addonLink?>&act=power">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td height="25" colspan="2">权限管理 (选中代表具有权限)</td>
    </tr>
    <?php
        while($r=$empire->fetch($sql)){
            $groupid = (int)$r['groupid'];
            $checked = in_array($groupid , $adminApiGroupIds);
    ?>
    <tr bgcolor="#FFFFFF">
        <td width="300" height="25"><?=$r['groupname']?></td>
        <td height="25"><input type="checkbox" name="gid[]" value="<?=$groupid?>" <?=($checked ? 'checked' : '')?>></td>
    </tr>
    <?php
        }
    ?>
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