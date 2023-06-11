<?php
$adminApiInstall = $addonFolder . 'install.lock';

if(!is_file($adminApiInstall)){
    // 开始安装
    $result = file_put_contents($adminApiInstall , $loginlevel);
    if(false === $result){
        exit('请检查 '. $addonFolder .' 目录是否可写');
    }
}

$adminApiGroupIds = explode(',' , (string)file_get_contents($adminApiInstall));

if(!in_array($loginlevel , $adminApiGroupIds)){
    printerror2('权限不足');
}


function adminGetApiModByName($name = '*')
{
    $mods = glob(ECMS_PATH . 'ecmsapi/_mod/*/_conf.php');
    $result = [];
    foreach($mods as $v){
        $name = end(explode('/' , str_replace('/_conf.php' , '' , $v)));
        $result[$name] = require $v;
    }

    return $result;
}


function adminBuildConfig($path , $conf = []){
    $content = "<?php" . PHP_EOL . "return ". var_export($conf, true) . ";";
    return file_put_contents($path , $content);
}