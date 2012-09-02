<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// 时光轴数据
	"DROP TABLE IF EXISTS `{$db_prefix}timeline`;",
	"DROP TABLE IF EXISTS `{$db_prefix}timesign`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'timeline'",
);

foreach ($sql as $v)
	M('')->execute($v);