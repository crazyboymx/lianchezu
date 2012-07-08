<?php
/**
 * 新系统上线后，在后台上传宣传图片，设置播放周期。
 * 用户登录网站首页，自动展示宣传图片，只播放一次。
 */
class NewFeaturesAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '海虾';
	protected $site    = 'http://www.thinksns.com';
	protected $info    = '新功能上线，宣传图片展示插件';
	protected $pluginName = '新功能展示';
    protected $tsVersion  = "2.5";                               // ts核心版本号

	public function getHooksInfo()
	{
		$this->apply("public_footer","showNewFeaturesLayer");
	}

    //在public_footer插件新功能上线引导js代码
    public function showNewFeaturesLayer()
	{
		//只在个人首页展示
		if(strtolower(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME)!='home/user/index'){
			return false;
		}
		//检查Cookie
		$is_checked = cookie('user_new_feature');
		$data = model('AddonData')->lget('new_features');
		//如果后台关闭提醒.返回false
		if( $data['isopen']==0){
			return false;
		}
		//如果cookie已经设置.返回false
		if( $is_checked && $data['version']==$is_checked ){
			return false;
		}
		//如果用户数据已经设置.返回false
		$uid = intval($_SESSION['mid']);
		$udata = model('UserData')->get($uid,'addon:new_feature');
		if( $udata && $data['version']==$udata ){
			return false;
		}
		//如果没有图片可展示.返回false
		if( !$data['pics'] ){
			return false;
		}
		//获取图片附件.输出新功能展示.
		$data['picdata'] = X('Xattach')->getAttach($data['pics']);
		$this->assign($data);
        $this->display('layer');
    }

	//设置已读
	public function setVersion(){
		$version= intval($_GET['version']);
		$uid	= intval($_SESSION['mid']);
		if( $version>0 && $uid>0 ){
			//设置数据库
			$result = model('UserData')->save($uid,'addon:new_feature',$version);
			//设置cookie
			cookie('user_new_feature',$version,(24*3600*365));
			echo intval($result);
			return;
		}else{
			echo -1;
			return;
		}
	}

	/* 后台管理 */

    public function adminMenu()
	{
        return array('config' => '配置');
    }

	/* 插件后台配置项 */
	public function config()
	{
		$data = model('AddonData')->lget('new_features');
		$this->assign($data);
		$this->display('config');
	}

	public function saveConfig($param)
	{
		$post['isopen']	=	intval($_POST['isopen']);
		if( $post['isopen']==1 && (!is_array($_POST['attach']) || count($_POST['attach'])==0 ) ){
			$this->error('请上传图片.');
			exit;
		}
		$post['tpl']	=	'layer';
		$post['version']=	time();
		foreach($_POST['attach'] as $v){
			$post['pics'][]	=	intval($v);
		}
		$res = model('AddonData')->lput('new_features', $post);
		if ($res) {
			$this->assign('jumpUrl', Addons::adminPage('config'));
    		$this->success();
		} else {
    		$this->error();
		}
	}
}