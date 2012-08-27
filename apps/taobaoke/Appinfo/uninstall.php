<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_ac`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_bc`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_favorite`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_plugin`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'taobaoke'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'taobaoke';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'taobaoke';",
);

foreach ($sql as $v)
	M('')->execute($v);
