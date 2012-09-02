DROP TABLE IF EXISTS `ts_shop_category`;
CREATE TABLE `ts_shop_category` (
  `id` int(12) NOT NULL auto_increment,
  `name` varchar(120) NOT NULL,
  `other` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- 表的结构 `ts_shop_goods`
-- 
DROP TABLE IF EXISTS `ts_shop_goods`;
CREATE TABLE `ts_shop_goods` (
  `id` int(12) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `pic` varchar(250) NOT NULL,
  `category` int(12) NOT NULL,
  `price` varchar(250) NOT NULL,
  `x_w` int(12) NOT NULL,
  `x_f` int(12) NOT NULL,
  `x_s` int(12) NOT NULL,
  `time` varchar(12) NOT NULL,
  `endtime` varchar(12) NOT NULL,
  `num` int(12) NOT NULL,
  `sy` int(12) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- 表的结构 `ts_shop_order`
-- 
DROP TABLE IF EXISTS `ts_shop_order`;
CREATE TABLE `ts_shop_order` (
  `id` int(12) NOT NULL auto_increment,
  `uid` int(12) NOT NULL,
  `username` varchar(120) NOT NULL,
  `gid` int(12) NOT NULL,
  `gname` varchar(250) NOT NULL,
  `price` varchar(120) NOT NULL,
  `number` int(12) NOT NULL,
  `oneprice` varchar(120) NOT NULL,
  `time` varchar(12) NOT NULL,
  `state` int(1) NOT NULL,
  `name` varchar(250) NOT NULL,
  `address` varchar(250) NOT NULL,
  `postcode` int(8) NOT NULL,
  `telphone` varchar(120) NOT NULL,
  `mobile` int(12) NOT NULL,
  `qq` int(12) NOT NULL,
  `fhd` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;