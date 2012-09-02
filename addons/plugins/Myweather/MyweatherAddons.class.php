<?php
class MyweatherAddons extends NormalAddons{
	protected $version = '1.0.0';
	protected $author  = '卿万乾';
	protected $site    = 'http://weibo.com/qtvbstar';
	protected $info    = '定制我的天气，在微博中时刻关注当前天气';
	protected $pluginName = '我的天气';
	protected $sqlfile = '';
	protected $tsVersion  = "2.5";
	
	public function getHooksInfo(){
		$hooks['list'] = array('MyweatherHooks');
        return $hooks;
	}
	public function adminMenu(){
      
    }
	public function start()
    {
        return true;
    }

    public function install()
    {
        $db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_weather` (
			  `uid` int(11) NOT NULL,
			  `cid` int(8) NOT NULL,
			  PRIMARY KEY (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

		return M()->execute($sql) !== false;
    }

    public function uninstall()
    {
        $db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}user_weather`;";
		return M()->execute($sql);
    }
}