<?php

class UserPayAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = '程序_小时代';
    protected $site = '暂无';
    protected $info = '用户充值兑换积分插件';
    protected $pluginName = '充值兑换';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('UserPayHooks');
        return $hooks;
    }

    public function adminMenu() {
         $menu = array('payConf' => '充值配置','payAdmin' => '充值管理');
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
			//用户充值记录表
			"CREATE TABLE IF NOT EXISTS `ts_user_pay` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` int(11) NOT NULL,
			  `billno` varchar(30) NOT NULL,
			  `date` int(11) NOT NULL,
			  `amount` int(11) NOT NULL,
			  `gateway` tinyint(4) NOT NULL DEFAULT '0',
			  `issucess` tinyint(4) NOT NULL DEFAULT '0',
			  `tosucess` tinyint(4) NOT NULL DEFAULT '0',
			  `ipsbillno` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66 ;",			
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
			"DROP TABLE IF EXISTS `{$db_prefix}user_pay`;",
		);

		foreach ($sql as $v)
			$res = M()->execute($v);

		if (false !== $res) {
			return true;
		}
    }

}