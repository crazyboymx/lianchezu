作者：卿万乾 (http://weibo.com/qtvbstar ; )（寻找php实习ing）

使用说明：
  
 	1、天气信息来源于QQ.com；
	
	2、首次使用请装解压后的Myweather文件夹放在thinksns的根目录下的addons/plugins/下面；
		然后在thinksns的 》后台管理 》扩展 》插件》中选择“我的天气” 启用；
		首次启用会在数据库中新建user_weather表，不包括前缀；
		表中结构：
		CREATE TABLE IF NOT EXISTS `{$db_prefix}user_weather` (
			  `uid` int(11) NOT NULL,
			  `cid` int(8) NOT NULL,
			  PRIMARY KEY (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

	3、启用插件后，用户会在首页左边中间看到设置天气提示，设置天气即可。