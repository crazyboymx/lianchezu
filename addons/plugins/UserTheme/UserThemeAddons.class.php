<?php

class UserThemeAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = '程序_小时代';
    protected $site = '暂无';
    protected $info = '用户自由切换主题包版本，真正的风格包切换';
    protected $pluginName = '主站风格版本选择';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('UserThemeHooks');
        return $hooks;
    }

    public function adminMenu() {
         $menu = array('themeAdmin' => '版本包管理');
         return $menu;
    }

    public function start()
    {
        return true;
    }

    public function install()
    {
        $db_prefix = C('DB_PREFIX');
		$sql = array(
			// 主题包数据
			"CREATE TABLE IF NOT EXISTS `{$db_prefix}all_theme` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `theme_name` varchar(255) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `icon` varchar(255) NOT NULL,
			  `info` varchar(255) NOT NULL,
			  `ctime` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
			
			//自带的2个版本包
			"INSERT INTO `{$db_prefix}all_theme` (`id`, `theme_name`, `title`, `icon`, `info`, `ctime`) VALUES
(8, 'newstyle', '体验版', '4faa3191bce3e.png', '微博新版本（社区版），拓宽了界面视野，更加大气。如果你想玩微群、玩应用、逛广场......这个版本能帮你更快找到有趣内容、有趣功能。', 1336553873),
(9, 'weibo', '标准版', '4faa31ca02d12.png', '微博经典版本，和过去两年来的微博界面几乎一致，如果你是个怀旧派，可以试试这个版本。', 1336553930);",
			
			// 用户主题包数据
			"CREATE TABLE IF NOT EXISTS `{$db_prefix}user_theme` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` int(11) NOT NULL,
			  `theme_name` varchar(255) NOT NULL,
			  `ctime` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;", 
		);

		foreach ($sql as $v)
			$res = M()->execute($v);
		if (false !== $res) {
			return true;
		}
    }

    

    public function uninstall() {

        $db_prefix = C('DB_PREFIX');
		$sql = array(
			// 卸载数据
			"DROP TABLE IF EXISTS `{$db_prefix}all_theme`;",
		    "DROP TABLE IF EXISTS `{$db_prefix}user_theme;",
		);

		foreach ($sql as $v)
			$res = M()->execute($v);

		if (false !== $res) {
			return true;
		}
    }

}