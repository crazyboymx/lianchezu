-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 26, 2012 at 08:43 AM
-- Server version: 5.1.48
-- PHP Version: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `ts_taobaoke`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke` (
    `weibo_id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
    `content` text NOT NULL,
    `ctime` int(11) NOT NULL,
    `from` tinyint(1) NOT NULL,
    `comment` mediumint(8) NOT NULL,
    `transpond_id` int(11) NOT NULL DEFAULT '0',
    `transpond` mediumint(8) NOT NULL,
    `type` varchar(255) DEFAULT '0',
    `type_data` text,
    `from_data` text,
    `isdel` tinyint(1) NOT NULL DEFAULT '0',
    `price` int(11) NOT NULL,
    `bc_id` int(11) NOT NULL,
    `favcount` int(11) NOT NULL,
    `jiancount` int(11) NOT NULL,
    `fengcount` int(11) NOT NULL,
    `goods_url` text NOT NULL,
    `tag` text NOT NULL,
    `fenlei_input` text NOT NULL,
    PRIMARY KEY (`weibo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2194 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_ac`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_ac` (
    `ac_id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `ctime` int(11) DEFAULT NULL,
    `display_order` smallint(2) NOT NULL DEFAULT '0',
    `title_other` varchar(255) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`ac_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_bc`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_bc` (
    `bc_id` int(11) NOT NULL AUTO_INCREMENT,
    `ac_id` int(11) NOT NULL,
    `uid` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `ctime` int(11) DEFAULT NULL,
    `fengcount` int(11) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '0',
    `title_other` varchar(255) NOT NULL,
    PRIMARY KEY (`bc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=303 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_favorite`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_favorite` (
    `uid` int(11) NOT NULL,
    `weibo_id` int(11) NOT NULL,
    PRIMARY KEY (`uid`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-----------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_fav`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_fav` (
      `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `favid` varchar(255) NOT NULL,
      `dateline` char(20) NOT NULL,
      `favuid` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=937 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_plugin`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_plugin` (
    `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(120) NOT NULL,
    `icon_pic` varchar(120) NOT NULL,
    `plugin_path` varchar(255) NOT NULL,
    PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_comment`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_comment` (
      `comment_id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `reply_comment_id` int(11) NOT NULL,
      `reply_uid` int(11) NOT NULL,
      `weibo_id` int(11) NOT NULL,
      `content` text NOT NULL,
      `ctime` int(11) NOT NULL,
      `isdel` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'taobaoke';
INSERT INTO `ts_credit_setting`
VALUES
    ('', 'add_taobaoke', '添加购物记录', 'showfee', '{action}{sign}了{score}{typecn}', '10', '10'),
    ('', 'delete_taobaoke', '删除购物记录', 'showfee', '{action}{sign}了{score}{typecn}', '-10', '-10');

