/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-01-20 15:16:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_showfee
-- ----------------------------
DROP TABLE IF EXISTS `ts_showfee`;
CREATE TABLE `ts_showfee` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
  `explain` text NOT NULL,
  `carBrand` int(11) NOT NULL,
  `carType` int(11) NOT NULL,
  `carTime` int(11) NOT NULL,
  `cTime` int(11) NOT NULL,
  `attentionCount` int(11) NOT NULL default '0',
  `commentCount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_showfee_car_brand
-- ----------------------------
DROP TABLE IF EXISTS `ts_showfee_car_brand`;
CREATE TABLE `ts_showfee_car_brand` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_showfee_car_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_showfee_car_type`;
CREATE TABLE `ts_showfee_car_type` (
  `id` int(11) NOT NULL auto_increment,
  `brandId` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_showfee_fee_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_showfee_fee_type`;
CREATE TABLE `ts_showfee_fee_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_showfee_fee_type
-- ----------------------------
INSERT INTO `ts_showfee_fee_type` VALUES ('1', '保养');
INSERT INTO `ts_showfee_fee_type` VALUES ('2', '保险');
INSERT INTO `ts_showfee_fee_type` VALUES ('3', '购车');

-- ----------------------------
-- Table structure for ts_showfee_fee_record
-- ----------------------------
DROP TABLE IF EXISTS `ts_showfee_fee_record`;
CREATE TABLE `ts_showfee_fee_record` (
  `id` int(11) NOT NULL auto_increment,
  `showfeeId` int(11) NOT NULL,
  `feeType` int(11) NOT NULL,
  `explain` varchar(255) NOT NULL default '',
  `fee` float(10) NOT NULL default 0,
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0, 'showfee', 'limitpage', 's:2:"10";', '2011-01-20 15:19:10'),
    (0, 'showfee', 'canCreate', 's:1:"1";', '2011-01-20 15:19:10'),
    (0, 'showfee', 'credit', 's:1:"0";', '2011-01-20 15:19:10'),
    (0, 'showfee', 'credit_type', 's:10:"experience";', '2011-01-20 15:19:10'),
    (0, 'showfee', 'limittime', 's:1:"0";', '2011-01-20 15:19:10');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'showfee';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('showfee_create_weibo', '晒费用', '','我晒了一个费用：【{title}】{url}', 'zh', 'showfee', 'weibo', 0, 1290417734),
    ('showfee_share_weibo', '分享费用', '', '分享@{author} 的费用:【{title}】 {url}', 'zh',  'showfee', 'weibo', 0, 1290595552);

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'showfee';
INSERT INTO `ts_credit_setting`
VALUES
    ('', 'add_showfee', '添加晒费用记录', 'showfee', '{action}{sign}了{score}{typecn}', '10', '10'),
    ('', 'delete_showfee', '删除晒费用记录', 'showfee', '{action}{sign}了{score}{typecn}', '-10', '-10');
INSERT INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES
    (0,'showfee','version_number','s:5:"28172";','2012-08-14 10:00:00');
