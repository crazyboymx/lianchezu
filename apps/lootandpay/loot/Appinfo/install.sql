DROP TABLE IF EXISTS `ts_loot`;

CREATE TABLE `ts_loot` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(11) unsigned NOT NULL,
`cid`int(11) unsigned NOT NULL,
`num`int(11) unsigned NOT NULL,
`ctime` int(11) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_loot_category`;

CREATE TABLE `ts_loot_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `status`  tinyint(1) unsigned NULL DEFAULT '0',
  `display_order` smallint(5) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ts_loot_category`
--

INSERT INTO `ts_loot_category` (`id`, `title`, `status`, `display_order`, `ctime`) VALUES
(12, '音乐', 1, 12, 1336823428),
(9, '艺术', 1, 9, 1336823372),
(10, '时尚', 1, 10, 1336823401),
(11, '影视', 1, 11, 1336823414),
(8, '摄影', 1, 8, 1336823363),
(13, '阅读', 1, 13, 1336823497),
(14, '旅游', 1, 14, 1336823505),
(15, '美食', 1, 15, 1336823512),
(16, '宠物', 1, 16, 1336823528),
(17, '汽车', 1, 17, 1336823537),
(18, '数码', 1, 18, 1336823544),
(19, '动漫', 1, 19, 1336823560),
(20, '游戏', 1, 20, 1336823567),
(21, '文化', 1, 21, 1336823579),
(22, '育儿', 1, 22, 1336823592),
(23, '家居', 1, 23, 1336823599),
(24, '运动', 1, 24, 1336823607),
(25, '科学', 1, 25, 1336823621),
(26, '恋物', 1, 26, 1336823630),
(27, '心情', 1, 27, 1336823637),
(28, '奇趣', 1, 28, 1336823655),
(29, 'IT', 1, 29, 1336823667),
(30, '创意', 1, 30, 1336823687),
(31, '健康', 1, 31, 1336823700),
(32, '生活', 1, 32, 1336823707),
(33, '星座', 1, 33, 1336823721),
(34, '媒体', 1, 34, 1336823738),
(35, '插画', 1, 35, 1336823747);

DROP TABLE IF EXISTS `ts_loot_count`;

CREATE TABLE `ts_loot_count` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(11) unsigned NOT NULL,
`counts`int(11) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

# 配置数据
INSERT INTO `ts_system_data` (`id`, `uid`, `list`, `key`, `value`, `mtime`) VALUES
(391, 0, 'loot', 'isnotify', 's:1:"1";', '2012-05-11 23:17:00'),
(390, 0, 'loot', 'jifeng', 's:3:"100";', '2012-05-11 23:17:00'),
(389, 0, 'loot', 'loots', 's:2:"30";', '2012-05-11 23:17:00'),
(388, 0, 'loot', 'lootdays', 's:1:"1";', '2012-05-11 23:17:00'),
(387, 0, 'loot', 'version_number', 's:3:"001";', '2012-05-11 15:15:09');