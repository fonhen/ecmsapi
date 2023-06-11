<?php
defined('EmpireCMSAdmin') or die;


$result = delete_mod_dir(ECMS_PATH . 'ecmsapi/_mod/' . $name . '/');

if(false === $result){
    printerror2('模块删除失败 请检查目录权限');
}else{
    printerror2('模块删除成功' , $addonLink);
}


function delete_mod_dir($dir = ''){
    if(!is_dir($dir)){
        return false;
    }

    $dh  = opendir($dir);

    if(false === $dh){
        return false;
    }

    while( $v = readdir($dh) ){
        if($v === '.' || $v === '..'){
            continue;
        }
        $file = $dir .'/'. $v;
        if(is_dir($file)){
            delete_mod_dir($file);
        }else{
            unlink($file);
        } 
    }

    rmdir($dir);
    closedir($dh);

    return true;

}