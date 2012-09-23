<?php
//获取应用配置
function getConfig_event($key=NULL){
	$config = model('Xdata')->lget('event');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=100;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=10;//换算为秒

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}
