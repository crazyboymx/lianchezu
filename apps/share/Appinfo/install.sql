

DROP TABLE IF EXISTS `ts_share`;
CREATE TABLE `ts_share` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL,
  `intro` text NOT NULL,
  `logo` varchar(255) NOT NULL,
  `cid` smallint(6) unsigned NOT NULL,
  `city` int(11) unsigned NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `like` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS `ts_sharetype`;
CREATE TABLE `ts_sharetype` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `ts_sharelike`;
CREATE TABLE `ts_sharelike` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL default '0',
  `uid` int(11) unsigned NOT NULL default '0',
  `appid` smallint(6) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS `ts_pict`;
CREATE TABLE `ts_pict` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cover` varchar(255) default NULL,
  `content` text NOT NULL,
  `price` varchar(255) default NULL,
  `link` varchar(255) default NULL,
  `cTime` int(11) default NULL,
  `like` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------




INSERT INTO `ts_sharetype` (`id`, `name`) VALUES
(1, '美好创意'),
(2, '惬意旅游'),
(3, '兴趣爱好'),
(4, '摄影人生'),
(5, '生活时尚'),
(6, '幸福家居'),
(7, '乐享美食');


#添加ts_system_data数据
INSERT INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'share','version_number','s:5:"95876";','2012-05-20 10:00:00');