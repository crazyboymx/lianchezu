<?php
if (!defined('SITE_PATH')) exit();

header('Content-Type: text/html; charset=utf-8');
$sql_file  = APPS_PATH.'/shop/Appinfo/install.sql';
//ִ��sql�ļ�
$res = M('')->executeSqlFile($sql_file);
if(!empty($res)){//����
	echo $res['error_code'];
	echo '<br />';
	echo $res['error_sql'];
	//����ѵ��������
	include_once(APPS_PATH.'/shop/Appinfo/uninstall.php');
	exit;
}