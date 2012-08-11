<?php

class ChangeStyleAddons extends NormalAddons {

    protected $version = '2.0';
    protected $author = 'Alan QQ:8510001';
    protected $site = 'http://fakesay.com';
    protected $info = '用户自定义风格';
    protected $pluginName = '换肤功能';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('ChangeStyleHooks');
        return $hooks;
    }

    public function adminMenu() {
        // $menu = array('config' => '皮肤管理');
        // return $menu;
    }

    public function start() {

    }

    public function install() {

        $db_prefix = C('DB_PREFIX');

        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_changestyle` (

				  `uid` int(11) unsigned NOT NULL,
				  `classname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,

				  `diybg` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `diybgcolor` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,

				  UNIQUE KEY `uid` (`uid`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

        return M()->execute($sql);
    }

    public function uninstall() {

        $db_prefix = C('DB_PREFIX');

        $sql = "DROP TABLE IF EXISTS `{$db_prefix}user_changestyle`;";
		return M()->execute($sql);
    }

}