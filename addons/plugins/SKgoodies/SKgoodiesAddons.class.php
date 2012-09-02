<?php
/**
 *      ThinkSNS SK书签栏采集小工具 插件 v1.0 (C)2012-2099 啊.Qin.
 *      This is NOT a freeware, use is subject to license terms
 *		任何人修改代码需要告诉作者，并开源。不得商业出售！
 *      author  啊.Qin <172376799#qq.com>
 *      $Id: SKgoodiesAddons.class.php 7 2012-04-28 08:24:24Z 阿Qin $
 */
class SKgoodiesAddons extends NormalAddons {

    protected $version = '1.0';
    protected $author = 'Hi.Qin QQ:172376799';
    protected $site = 'http://www.SongKiss.com';
    protected $info = 'SK书签栏采集工具';
    protected $pluginName = 'SK采集小工具';
   // protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('SKgoodiesHooks');
        return $hooks;
    }

    public function adminMenu() {
        // $menu = array('config' => '采集小工具设置');
        // return $menu;
    }

    public function start() {

    }

    public function install() {
		
		return true;
		
    }

    public function uninstall() {

        return true;
    }

}