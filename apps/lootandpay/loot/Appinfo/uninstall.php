<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// loot数据
	"DROP TABLE IF EXISTS `{$db_prefix}loot`;",
	"DROP TABLE IF EXISTS `{$db_prefix}loot_category`;",
	"DROP TABLE IF EXISTS `{$db_prefix}loot_count`;",

	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'loot'",
);

foreach ($sql as $v)
	M('')->execute($v);