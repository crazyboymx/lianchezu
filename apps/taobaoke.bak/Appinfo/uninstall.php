<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_ac`;",
	"DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_atme`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_bc`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_comment`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_favorite`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_follow`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_follow_group`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_follow_group_link`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_plugin`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_star`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_star_group`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_topic`;",
    "DROP TABLE IF EXISTS `{$db_prefix}taobaoke_weibo_topics`;",
    // ts_system_data数据
    "DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'taobaoke'",
    // 模板数据
    "DELETE FROM `{$db_prefix}template` WHERE `type` = 'taobaoke';",
    // 积分规则
    "DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'taobaoke';",
);

foreach ($sql as $v)
    M('')->execute($v);
