<?php

class TimePillAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = 'Alan QQ:8510001';
    protected $site = 'http://fakesay.com';
    protected $info = '时间胶囊';
    protected $pluginName = '时间胶囊';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() { 
        
        $hooks['list'] = array('TimePillHooks');
        return $hooks;
    }
      public function adminMenu(){
        $menu = array('config' => '胶囊管理');
        return $menu;
    }
      public function start()
    {

    }


    public function install() {
        $db_prefix = C('DB_PREFIX');
     
        
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}timepill` (
				  `id` int(12) NOT NULL auto_increment,
				  `uid` int(12) NOT NULL,
				  `gettime` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `puttime` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, 
				  `content` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  PRIMARY KEY (`id`), 
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

        if (false !== M()->execute($sql)) {
            return true;
        }
    }

    public function uninstall() {
        $db_prefix = C('DB_PREFIX');
        $sql = "DROP TABLE IF EXISTS `{$db_prefix}timepill`;";
 
        
        
        if (false !== M()->execute($sql)) {
            return true;
        }
    }

}