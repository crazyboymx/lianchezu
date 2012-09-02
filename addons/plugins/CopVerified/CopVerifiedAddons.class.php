<?php
class CopVerifiedAddons extends NormalAddons
{
	protected $version = '1.0';
	protected $author = '韭菜饺子';
	protected $site = 'http://www.thinksaas.cn';
	protected $info = '团体机构认证';
	protected $pluginName = '团体机构认证';
	protected $sqlfile = 'install.sql'; // 安装时需要执行的sql文件名
	protected $tsVersion = "2.5"; // ts核心版本号
	
	/**
	 * getHooksInfo
	 * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
	 * 
	 * @access public 
	 * @return void 
	 */
	public function getHooksInfo()
	{
		$hooks['list'] = array('CopVerifiedHooks');
		return $hooks;
	}

	public function adminMenu()
	{
		$menu = array(
			'addVerifiedjigou' => '添加机构认证',
			'addVerifiedjigoulist' => '机构认证列表'
			);
		return $menu;
	}

	public function start()
	{
	}

	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `ts_user_cop_verified` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `fuzeren` text NOT NULL,
  `lianxifangshi` text NOT NULL,
  `jieshao` text NOT NULL,
  `huandengpian` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

		M()->execute($sql);
	}

	public function uninstall()
	{
		
	}
}
