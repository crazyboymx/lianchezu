DROP TABLE IF EXISTS `ts_inlove`;

CREATE TABLE `ts_inlove` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`from_uid` int(11) unsigned NOT NULL,
`to_uid`int(11) unsigned NOT NULL,
`ctime` int(11) unsigned NOT NULL,
`weibo_id` int(11) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_inlove_content`;

CREATE TABLE `ts_inlove_content` (
  `inlove_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int(11) unsigned NOT NULL,
  `from_uid` int(11) unsigned NOT NULL,
  `content` text,
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`inlove_id`),
  KEY `list_id` (`list_id`,`is_del`,`mtime`),
  KEY `list_id_2` (`list_id`,`mtime`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_inlove_list`;

CREATE TABLE `ts_inlove_list` (
  `list_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `member_num` smallint(5) unsigned NOT NULL DEFAULT '0',
  `min_max` varchar(17) DEFAULT NULL,
  `mtime` int(11) unsigned NOT NULL,
  `last_inlove` text NOT NULL,
  PRIMARY KEY (`list_id`),
  KEY `type` (`type`),
  KEY `min_max` (`min_max`),
  KEY `from_uid` (`from_uid`,`mtime`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_inlove_member`;

CREATE TABLE `ts_inlove_member` (
  `list_id` int(11) unsigned NOT NULL,
  `member_uid` int(11) unsigned NOT NULL,
  `new` smallint(8) unsigned NOT NULL DEFAULT '0',
  `inlove_num` int(10) unsigned NOT NULL DEFAULT '1',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  `list_ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`list_id`,`member_uid`),
  KEY `new` (`new`),
  KEY `ctime` (`ctime`),
  KEY `list_ctime` (`list_ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# 积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'inlove';
INSERT INTO `ts_credit_setting` VALUES ('','inlove_add','恋爱表白、吐槽','inlove','{action}{sign}了{score}{typecn}','-5','2');