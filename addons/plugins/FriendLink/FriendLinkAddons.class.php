<?php

class FriendLinkAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = '程序_小时代';
    protected $site = '暂无';
    protected $info = '站长添加友情链接，利于推广';
    protected $pluginName = '友情链接';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('FriendLinkHooks');
        return $hooks;
    }

    public function adminMenu() {
         $menu = array('linkAdmin' => '链接管理');
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
			// 友情链接数据
			"CREATE TABLE IF NOT EXISTS `{$db_prefix}friend_link` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL,
			  `url` varchar(255) NOT NULL,
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
			"DROP TABLE IF EXISTS `{$db_prefix}friend_link`;",
		);

		foreach ($sql as $v)
			$res = M()->execute($v);

		if (false !== $res) {
			return true;
		}
    }

}