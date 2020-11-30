<?php

function api_get_all_mod()
{
	global $apiConf,$apiDir;
	$modPath = $apiDir.'_mod/';
	$files = scandir($modPath);
	
	$mods = [];
	
	foreach($files as $f){
		if($f === '.' || $f === '..'){
			continue;
		}
		$fpath = $modPath.$f;
		if(is_dir($fpath) && is_file($fpath.'/_conf.php')){
			$conf = require($fpath.'/_conf.php');
			if(is_array($conf) && !empty($conf)){
				$mods[$f] = $conf;
			}
		}
	}
	
	return $mods;
}

function api_get_act_code($mod , $act)
{
	global $apiConf,$apiDir;
	$modPath = $apiDir.'_mod/';
	$code = file_get_contents($modPath.$mod.'/'.$act.'.php');
	return false !== $code ? $code : '';
}

function api_get_mod_fcode($mod)
{
	global $apiConf,$apiDir;
	if($mod === ''){
		$modPath = $apiDir.'_common/function.php';
	}else{
		$modPath = $apiDir.'_mod/'.$mod.'/_function.php';
	}
	$code = file_get_contents($modPath);
	return false !== $code ? $code : '';
}

function api_save_mod_fcode($mod , $code)
{
	global $apiConf,$apiDir;
	if($mod === ''){
		$modPath = $apiDir.'_common/function.php';
	}else{
		$modPath = $apiDir.'_mod/'.$mod.'/_function.php';
	}
	$code = file_put_contents($modPath , htmlspecialchars_decode($code));
	return $code;
}

function api_set_act_code($mod , $act , $post){
	global $apiConf,$apiDir,$allMod;
	$modPath = $apiDir.'_mod/'.$mod.'/';
	$code = htmlspecialchars_decode($post['code']);
	if($act === ''){
		$name = $post['a'];
		$rename = false;
	}else if($act !== $post['a']){
		$name = $act;
		$rename = true;
	}else{
		$name = $act;
		$rename = false;
	}
	$result = file_put_contents($modPath.$name.'.php' , $code);
	if(false === $result){
		return false;
	}
	$conf = $allMod[$mod];
	$conf['list'][$post['a']] = [
		'open' => $post['open'],
		'name' => $post['name'],
		'description' => $post['description']
	];
	
	if($rename){
		$result = rename($modPath.$name.'.php' , $modPath.$post['a'].'.php');
		if(true === $result){
			unset($conf['list'][$name]);
		}
	}
	$result = api_build_conf($modPath.'_conf.php' , $conf);
	
	
	return $result;
	
}

function api_del_act($mod , $act){
	global $apiConf,$apiDir,$allMod;
	$modPath = $apiDir.'_mod/'.$mod.'/';
	
	$conf = $allMod[$mod];
	unset($conf['list'][$act]);
	
	$result = api_build_conf($modPath.'_conf.php' , $conf);
	
	if(false !== $result){
		return unlink($modPath.$act.'.php');
	}else{
		return false;
	}
}

function api_save_mod($mod , $post){
	global $apiConf,$apiDir,$allMod;
	$modDir = $apiDir.'_mod/';
	$conf = [
		'open' => $post['open'],
		'name' => $post['name'],
		'description' => $post['description']
	];
	if($mod === ''){
		$modPath = $modDir.$post['m'];
		$conf['list'] = [];
		mkdir($modPath , 0777);
		if(is_dir($modPath)){
			file_put_contents($modPath.'/_function.php' , '');
		}else{
			return false;
		}
	}else{
		$modPath = $modDir.$mod;
		$conf['list'] = $allMod[$mod]['list'];
	}
	$rename = false;
	if($mod !== '' && $mod !== $post['m']){
		$rename = true;
	}
	
	$result = api_build_conf($modPath.'/_conf.php' , $conf);
	if(false !== $result){
		if($rename){
			return rename($modPath , $modDir.$post['m']);
		}else{
			return true;
		}
	}else{
		return false;
	}
}

function api_del_mod($mod){
	global $apiConf,$apiDir,$allMod;
	$modPath = $apiDir.'_mod/'.$mod.'/';
	return api_del_dir($modPath);
}

function api_del_dir($dir = ''){
	$res = true;
	if( is_dir($dir) ){
		$dh  = @opendir($dir);
		if(false !== $dh ){
			while(false !== ($filename = readdir($dh))){
				if($filename !== '.' && $filename !== '..'){
					$filedir = $dir .'/'. $filename;
					if(is_dir($filedir)){
						api_del_dir($filedir);
					}else{
						@chmod($filedir , 0777);
						@unlink($filedir);
					}
				}
			}
			if(!readdir($dh)){
				@rmdir($dir);
			}
			@closedir($dh);	
		}else{
			$res = false;
		}
	}else{
		$res = false;
	}
	return $res;
}

function api_build_conf($path , $conf = array()){
	$content = "<?php"."\r\n"."return ".var_export($conf,true).";";
	return file_put_contents($path , $content);
}

function api_demo_url($mod , $act)
{
	global $apiConf , $apiDir , $apiFolder;
	return '../../../'.$apiFolder.'index.php?'.$apiConf['mod'].'='.$mod.'&'.$apiConf['act'].'='.$act;
}