<?php
if($api->isPost()){


    $data = [];
    $data['name']           = $api->post('name' , '' , 'trim');
    $data['open']           = $api->post('open' , 0 , 'intval');
    $data['description']    = $api->post('description' , '' , 'trim');

    if($data['name'] === ''){
        printerror2('接口名称不能为空');
    }

    $key = $api->post('key' , '' , 'trim');
    if($key === ''){
        printerror2('接口文件名不能为空');
    }

    if(!preg_match("/^[a-z0-9\.]+$/" , $key)){
        printerror2('接口文件名只能由字母与数字组成');
    }

    if($name !== $key && isset($apiList[$key])){
        printerror2('接口文件名已被占用');
    }

    if($name !== $key && $name !== ''){
        unset($apiList[$name]);
        rename($modDir . $name . '.php' , $modDir . $key . '.php');
    }

    

    $apiList[$key] = $data;
    $mod['list'] = $apiList;

    $result = adminBuildConfig($modDir .'_conf.php' , $mod);

    if(false === $result){
        printerror2('操作失败 请检查 _mod 目录仅限');
    }

    $code = $api->post('code' , '' , 'trim');

    $result = file_put_contents($modDir . $key . '.php' , htmlspecialchars_decode($code));

    if(false === $result){
        printerror2('操作失败 请检查 _mod 目录仅限');
    }else{
        printerror2('操作成功' , $addonLink . '&act=api&m='.$m.'&do=edit&name='.$key);
    }
    
}

$data = $name === '' ? [
    'name'          => '',
    'open'          => 1,
    'description'   => ''
] : $apiList[$name];

if($name === ''){
    $code = '';
}else{
    $file = $modDir . $name . '.php';
    $code = file_get_contents($file);
    if(false === $code){
        printerror2('接口文件数据获取失败');
    }
}

$titlelink = '添加接口';
if($name !== ''){
    $titlelink = '<a href="../../../ecmsapi/index.php?mod='.$m.'&act='.$name.'" target="_blank">'.$data['name'].'</a>';
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
    <td height="26">
        位置：<a href="<?=$addonLink?>">API管理</a>&nbsp;>&nbsp;<a href="<?=$addonLink?>&act=mod&name=<?=$m?>"><?=$mod['name']?></a>&nbsp;>&nbsp;<?=$titlelink;?>
    </td>
  </tr>
</table>

<form name="form1" method="post" action="<?=$addonLink?>&act=api&m=<?=$m?>&do=edit&name=<?=$name?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
    <tr class="header">
        <td height="25" colspan="2">接口信息</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td width="150" height="25">接口文件名:(*)</td>
        <td height="25"><input name="key" type="text" value="<?=$name?>" size="42"> (由小写字母与数字组成)</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">状态</td>
        <td height="25"><input type="checkbox" value="1" name="open" <?=($data['open'] ? 'checked="checked"' : '')?>>开启</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">名称:</td>
        <td height="25"><input name="name" type="text" value="<?=$data['name']?>" size="42"></td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">说明</td>
        <td height="25"><textarea name="description" cols="60" rows="3"><?=$data['description']?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td>程序代码</td>
        <td><textarea name="code" style="width:100%; height:500px; padding:0;" id="code"><?=htmlspecialchars($code)?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td height="25">&nbsp;</td>
        <td height="25">
            <button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
        </td>
    </tr>
</table>
</form>
<?php
include $addonFolderLink . '_admin/ace.js.php';
?>
</body>
</html>