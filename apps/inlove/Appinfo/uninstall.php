<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// inlove数据
	"DROP TABLE IF EXISTS `{$db_prefix}inlove`;",
	"DROP TABLE IF EXISTS `{$db_prefix}inlove_content`;",
	"DROP TABLE IF EXISTS `{$db_prefix}inlove_list`;",
	"DROP TABLE IF EXISTS `{$db_prefix}inlove_member`;",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'inlove';",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'inlove'",
);

foreach ($sql as $v)
	M('')->execute($v);