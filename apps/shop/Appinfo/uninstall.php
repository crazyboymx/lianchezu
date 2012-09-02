<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// Blog数据
	"DROP TABLE IF EXISTS `{$db_prefix}shop_category`;",
	"DROP TABLE IF EXISTS `{$db_prefix}shop_goods`;",
	"DROP TABLE IF EXISTS `{$db_prefix}shop_order`;",
);

foreach ($sql as $v)
	M('')->execute($v);