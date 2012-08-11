<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}showfee`;",
	"DROP TABLE IF EXISTS `{$db_prefix}showfee_car_brand`;",
	"DROP TABLE IF EXISTS `{$db_prefix}showfee_car_type`;",
    "DROP TABLE IF EXISTS `{$db_prefix}showfee_fee_type`;",
    "DROP TABLE IF EXISTS `{$db_prefix}showfee_fee_record;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'showfee'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'showfee';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'showfee';",
);

foreach ($sql as $v)
	M('')->execute($v);
