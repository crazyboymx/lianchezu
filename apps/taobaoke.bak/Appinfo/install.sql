-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 26, 2012 at 06:21 AM
-- Server version: 5.1.48
-- PHP Version: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `ts_taobaoke_weibo`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2190 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_ac`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_ac` (
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
-- Table structure for table `ts_taobaoke_weibo_atme`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_atme` (
    `uid` int(11) NOT NULL,
    `weibo_id` int(11) NOT NULL,
    UNIQUE KEY `uid` (`uid`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_bc`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_bc` (
    `bc_id` int(11) NOT NULL AUTO_INCREMENT,
    `ac_id` int(11) NOT NULL,
    `uid` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `ctime` int(11) DEFAULT NULL,
    `fengcount` int(11) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '0',
    `title_other` varchar(255) NOT NULL,
    PRIMARY KEY (`bc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=300 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_comment`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_comment` (
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

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_favorite`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_favorite` (
    `uid` int(11) NOT NULL,
    `weibo_id` int(11) NOT NULL,
    PRIMARY KEY (`uid`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_follow`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_follow` (
    `follow_id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
    `fid` int(11) NOT NULL,
    `type` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`follow_id`),
    KEY `fid` (`fid`),
    KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=83 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_follow_group`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_follow_group` (
    `follow_group_id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `ctime` int(11) DEFAULT NULL,
    PRIMARY KEY (`follow_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_follow_group_link`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_follow_group_link` (
    `follow_group_link_id` int(11) NOT NULL AUTO_INCREMENT,
    `follow_group_id` int(11) NOT NULL,
    `follow_id` int(11) NOT NULL,
    `uid` int(11) NOT NULL,
    PRIMARY KEY (`follow_group_link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_plugin`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_plugin` (
    `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(120) NOT NULL,
    `icon_pic` varchar(120) NOT NULL,
    `plugin_path` varchar(255) NOT NULL,
    PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_star`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_star` (
    `star_id` int(11) NOT NULL AUTO_INCREMENT,
    `star_group_id` int(11) NOT NULL,
    `uid` int(11) NOT NULL,
    `ctime` int(11) DEFAULT NULL,
    PRIMARY KEY (`star_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_star_group`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_star_group` (
    `star_group_id` int(11) NOT NULL AUTO_INCREMENT,
    `top_group_id` int(11) NOT NULL DEFAULT '0',
    `title` varchar(255) NOT NULL,
    `display_order` int(11) NOT NULL DEFAULT '0',
    `ctime` int(11) DEFAULT NULL,
    PRIMARY KEY (`star_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_topic`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_topic` (
    `topic_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(150) NOT NULL,
    `count` int(11) NOT NULL,
    `ctime` int(11) NOT NULL,
    PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=388 ;

-- --------------------------------------------------------

--
-- Table structure for table `ts_taobaoke_weibo_topics`
--

CREATE TABLE IF NOT EXISTS `ts_taobaoke_weibo_topics` (
    `topics_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` int(11) unsigned NOT NULL,
    `domain` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    `recommend` enum('1','0') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
    `ctime` int(11) DEFAULT NULL,
    `isdel` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`topics_id`),
    UNIQUE KEY `page` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

