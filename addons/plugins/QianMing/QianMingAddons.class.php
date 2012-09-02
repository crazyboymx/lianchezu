<?php

class QianMingAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = 'Alan QQ:8510001';
    protected $site = 'http://fakesay.com';
    protected $info = '个性签名';
    protected $pluginName = '个性签名';
    protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.8";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() { 
        
        $hooks['list'] = array('QianMingHooks');
        return $hooks;
    }
      public function adminMenu(){
      //  $menu = array('config' => '管理');
       // return $menu;
    }
      public function start()
    {
         return true;
    }


    public function install() {
        
        
        $db_prefix = C('DB_PREFIX');
  
        $sql="ALTER TABLE  `{$db_prefix}user` ADD  `qianming` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER  `uname`";
          
    
       if (false !== M()->execute($sql)) {
            return true;
        }
        
    
    
        
    }

    public function uninstall() {
        
        
            $db_prefix = C('DB_PREFIX');
        $sql = " ALTER TABLE  `{$db_prefix}user` DROP  `qianming`";



        if (false !== M()->execute($sql)) {
            return true;
        }
        
    }

}