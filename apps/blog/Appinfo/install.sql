DROP TABLE IF EXISTS `ts_blog`;

CREATE TABLE `ts_blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` mediumint(5) DEFAULT NULL,
  `category_title` varchar(255) default NULL,
  `cover` varchar(255) DEFAULT NULL,
  `content` longtext,
  `readCount` int(11) NOT NULL DEFAULT '0',
  `commentCount` int(11) NOT NULL DEFAULT '0',
  `recommendCount` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `cTime` int(11) DEFAULT NULL,
  `mTime` int(11) DEFAULT NULL,
  `rTime` int(11) NOT NULL DEFAULT '0',
  `isHot` varchar(1) NOT NULL DEFAULT '0',
  `type` int(1) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT '1',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `private_data` varchar(255) DEFAULT NULL,
  `hot` int(11) NOT NULL DEFAULT '0',
  `canableComment` tinyint(1) NOT NULL DEFAULT '1',
  `attach` text,
  PRIMARY KEY (`id`),
  KEY `hot` (`hot`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ts_blog_category`;

CREATE TABLE `ts_blog_category` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ts_blog_category` (`name`,`uid`,`pid`) VALUES ('未分类',0,0);

DROP TABLE IF EXISTS `ts_blog_item`;

CREATE TABLE `ts_blog_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) DEFAULT NULL,
  `snapday` int(11) DEFAULT NULL,
  `pubdate` int(11) DEFAULT NULL,
  `boot` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `summary` text,
  PRIMARY KEY (`id`),
  KEY `source_id` (`sourceId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ts_blog_mention`;

CREATE TABLE `ts_blog_mention` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blogid` int(20) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blogid` (`blogid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_blog_outline`;

CREATE TABLE `ts_blog_outline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` mediumint(5) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `content` longtext,
  `readCount` int(11) NOT NULL DEFAULT '0',
  `commentCount` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `cTime` int(11) DEFAULT NULL,
  `mTime` int(11) DEFAULT NULL,
  `isHot` varchar(1) NOT NULL DEFAULT '0',
  `type` int(1) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT '1',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `hot` int(11) NOT NULL DEFAULT '0',
  `friendId` text,
  `canableComment` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `hot` (`hot`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_blog_source`;

CREATE TABLE `ts_blog_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(10) DEFAULT NULL,
  `username` char(30) DEFAULT NULL,
  `cTime` int(11) DEFAULT NULL,
  `lastSnap` int(11) DEFAULT NULL,
  `isNew` tinyint(1) DEFAULT NULL,
  `changes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ts_blog_subscribe`;

CREATE TABLE `ts_blog_subscribe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0',
  `newNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sourceId` (`sourceId`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'blog','allorder','year','2010-12-02 18:18:16'),
    (0,'blog','savetime','5','2010-11-19 10:52:26'),
    (0,'blog','smiletype','mini','2010-11-19 10:52:38'),
    (0,'blog','leadingnum','100','2010-11-19 10:52:56'),
    (0,'blog','leadingin','1','2010-11-19 10:53:05'),
    (0,'blog','notifyfriend','1','2010-11-19 10:53:27'),
    (0,'blog','fileaway','0','2010-12-03 16:26:02'),
    (0,'blog','fileawaypage','6','2010-12-03 11:03:53'),
    (0,'blog','all','1','2010-12-02 19:05:40'),
    (0,'blog','delete','0','2010-12-02 19:05:40'),
    (0,'blog','suffix','...','2010-11-19 10:54:58'),
    (0,'blog','titleshort','200','2010-12-03 14:50:57'),
    (0,'blog','limitpage','20','2010-12-03 13:11:32');
    
# 模版数据
DELETE FROM `ts_template` WHERE `name` = 'blog_create_weibo' OR `name` = 'blog_share_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('blog_create_weibo','发表日志','','我发表了一篇日志:【{title}】 {url}','zh','blog','weibo',0,1290417734),
    ('blog_share_weibo','分享日志','','分享@{author} 的日志:【{title}】 {url}','zh','blog','weibo',0,1290595552);

# 积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'blog';
INSERT INTO `ts_credit_setting` VALUES ('','add_blog','发表博客','blog','{action}{sign}了{score}{typecn}','5','5');
INSERT INTO `ts_credit_setting` VALUES ('','delete_blog','删除博客','blog','{action}{sign}了{score}{typecn}','-5','-5');
INSERT INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) VALUES (0,'blog','version_number','s:5:"28172";','2012-02-14 00:30:00');