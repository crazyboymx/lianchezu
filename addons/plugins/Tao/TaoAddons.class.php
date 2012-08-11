<?php
/**
 * XiamiAddons
 */
class TaoAddons extends NormalAddons
{
	protected $version = "1.0";
	protected $author  = "流光";
	protected $site    = "http://t.thinksns.com/space/small";
	protected $info    = "控制商品分享微博的类型插件";
    protected $pluginName = "商品分享";
    protected $tsVersion = '2.5';

    public function getHooksInfo(){
        //,'VideoHooks','MusicHooks','FileHooks'
        $hooks['list'] = array('TaoHooks');
        return $hooks;
    }
	/**
	 * 该插件的管理界面的处理逻辑。
	 * 如果return false,则该插件没有管理界面。
	 * 这个接口的主要作用是，该插件在管理界面时的初始化处理
	 * @param string $page
	 */
    public function adminMenu()
    {
	    return array('set'=>"帐号设置");
    }

    public function start()
    {
        return true;
    }

    public function install()
    {
        $data[type][openid] ='12427504';
        $data[type][openkey] ='6356bde11b6344432186e4f395591a22';
        $data[type][pid] ='13958825';
        model('AddonData')->lput('tao', $data)?true:false;
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
