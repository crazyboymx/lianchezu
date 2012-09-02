<?php
if (!defined('SITE_PATH')) exit();
$db_prefix = C('DB_PREFIX');
$sql = array(
	// share数据
	"DROP TABLE IF EXISTS `{$db_prefix}share`;",
	"DROP TABLE IF EXISTS `{$db_prefix}sharelike`;",
    "DROP TABLE IF EXISTS `{$db_prefix}sharetype;",
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'share'",
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'share';",
	"DROP TABLE IF EXISTS `{$db_prefix}pict`;",
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'pict'",
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'pict';",
);

foreach ($sql as $v)
	M('')->execute($v);