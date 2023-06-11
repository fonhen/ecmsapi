<?php
defined('EmpireCMSAdmin') or die;


unset($mod['list'][$name]);
unlink($modDir . $name . '.php');

$result = adminBuildConfig($modDir .'_conf.php' , $mod);

if(false === $result){
    printerror2('操作失败 请检查 _mod 目录仅限');
}

printerror2('操作成功' , $addonLink . '&act=mod&name='.$m);

?>
