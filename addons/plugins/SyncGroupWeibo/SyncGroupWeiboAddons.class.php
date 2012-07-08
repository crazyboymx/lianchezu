<?php
/**
 * SyncGroupWeiboAddons
 * 微博同步到我的群组
 * @uses SyncGroupWeiboAddons
 * @package
 * @version 1.0
 * @copyright 2001-2013 小川
 * @author 小川 <chenweichuan@zhishisoft.com>
 * @license PHP Version 5.2
 */
class SyncGroupWeiboAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '陈伟川';
	protected $site    = 'http://weibo.com/cchhuuaann';
	protected $info    = '微博可同步发布到我的群组';
	protected $pluginName = '群组微博同步';
	protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

	private   $_app = 'group';

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子,钩子和本类的方法是怎样的映射关系
     * @access public
     * @return void
     */
	public function getHooksInfo(){
		$this->apply("home_index_middle_publish", "checkBox");
		$this->apply("weibo_publish_after", "weibo_publish_after");
		//$this->apply("weibo_transpond_after", "weibo_transpond_after");
	}

	private function _isAppExistForUser()
	{
		return model('App')->isAppExistForUser($this->mid, $this->_app);
	}

	/* 同步设置显示 */
	public function checkBox()
	{
		if (!$this->_isAppExistForUser()) {
			return ;
        }

        $user_group_list = $this->_getUserGroupList();
        $user_group_sync = $this->_getUserGroupSync();

        if ($user_group_list) {
			$this->assign('user_group_sync', (array)$user_group_sync);
	        $this->assign('user_group_list', $user_group_list);
			$this->display('sync');
        }
	}

	private function _getUserGroupList()
	{
		return D('Group', 'group')->getAllMyGroup($this->mid, 0);
	}

	// 获取用户设置同步的群组GIDs
	private function _getUserGroupSync()
	{
		$res = M('group_sync')->field('gid')->where("`uid`={$this->mid}")->findAll();
		return getSubByKey($res, 'gid');
	}

	/* 同步设置操作 */
	public function sync_group_weibo()
	{
		if (!$this->_isAppExistForUser()) {
			return ;
        }

		$data['uid'] = $this->mid;
		$data['gid'] = intval(str_replace('group_', '', $_GET['group']));
		if ($this->_checkGid($data['gid']) && M('group_sync')->add($data)) {
			echo 1;
			exit;
		} else {
			echo '设置群组微博同步失败';
			exit;
		}
	}

	public function unset_sync_group_weibo()
	{
		if (!$this->_isAppExistForUser()) {
			return ;
        }
		$data['uid'] = $this->mid;
		$data['gid'] = intval(str_replace('group_', '', $_GET['group']));
		if ($this->_checkGid($data['gid']) && M('group_sync')->where($data)->delete()) {
			echo 1;
			exit;
		} else {
			echo '取消群组微博同步失败';
			exit;
		}
	}

	//　验证gid合法性
	private function _checkGid($gid)
	{
		return in_array($gid, getSubByKey($this->_getUserGroupList(), 'id'));
	}

	/* 微博同步至群组 */
	public function weibo_publish_after($param) {
		if (!$this->_isAppExistForUser() || 'group' == $param['app']) {
			return ;
        }

        $data = $param['post'];
		$data['from_data'] = unserialize($data['from_data']);
		$pWeibo = D('GroupWeibo', 'group');

        $user_group_list = $this->_getUserGroupList();
        $user_group_sync = $this->_getUserGroupSync();

        foreach ($user_group_list as $_v) {
			//如果未设置同步到该微博.则不发微博
			if (!in_array($_v['id'], $user_group_sync)) continue;
        	//如果来源就是该群组.则不发微博
			if($data['from_data']['gid']==$_v['id']) continue;
			$_POST['gid']     =  $_v['id'];
	        $id = $pWeibo ->publish( $this->mid, $data ,0 ,intval( $data['type']), h($data['type_data']));
	        if( $id ){
				X('Credit')->setUserCredit($this->mid,'add_weibo');
	        }
        }
	}

	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}group_sync` (
				  `uid` int(11) unsigned NOT NULL,
				  `gid` int(11) unsigned NOT NULL,
				  UNIQUE KEY `uid` (`uid`,`gid`),
				  KEY `uid_2` (`uid`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}group_sync`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
}