<?php
//获取应用配置
function getConfig_tbk($key=NULL){
	$config = model('Xdata')->lget('taobaoke');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=0;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=0;//换算为秒

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}

