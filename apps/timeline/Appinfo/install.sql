DROP TABLE IF EXISTS `ts_timeline`;

CREATE TABLE `ts_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `sign` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_timesign`;

CREATE TABLE `ts_timesign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sign` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0,'timeline','timeline','year','2012-05-25 10:11:16');
    
