<?php
class UserVerifiedHooks extends Hooks
{
    static $cache_list=array();
	public function init()
	{
	}

	public function home_account_tab($param)
	{
		$param['menu'][] = array(
			'act' => 'verified',
			'name' => '申请认证',
			'param' => array(
				'addon' => 'UserVerified',
				'hook'  => 'home_account_show'
			)
		);
	}

	public function home_account_show()
	{
    	$verified = M('user_verified')->where("uid={$this->mid}")->find();
    	$this->assign('verified', $verified);
    	$this->display('home_account_show');
	}

	public function home_account_do($param)
	{
    	$data['uid'] 	  = $this->mid;
    	$data['realname'] = preg_match('/^[\x{4e00}-\x{9fa5}]+$|^[a-zA-Z\.·]+$/u', $_POST['realname']) ? $_POST['realname'] : '';
    	$data['phone']	  = preg_match('/^[\d]{11}$/', $_POST['phone']) ? $_POST['phone'] : '';
    	$data['reason']	  = h(t($_POST['reason']));
    	if (!$data['realname'] || !$data['phone'] || !$data['reason']) {
    		echo 0;
    	}
    	$data['verified'] = '0';
    	if (is_numeric($_POST['id']) && $_POST['id'] > 0) {
    		$data['id'] = $_POST['id'];
    		$res = M('user_verified')->save($data);
    	} else {
    		$res = M('user_verified')->add($data);
    	}
    	if (false !== $res) {
    		echo 1;
    	} else {
    		echo 0;
    	}
	}

	// 用户名图标显示
	public function user_name_end($param)
	{

		$uid  = $param['uid'];
		$html = & $param['html'];
		if(empty(self::$cache_list[$uid])|| !self::$cache_list['uid']){
		    self::$cache_list[$uid] = $this->_getVerifiedCache($uid);
		    if(false === self::$cache_list[$uid]) self::$cache_list[$uid] = $this->_setVerifiedCache($uid);
		}
		$user_verified = self::$cache_list[$uid];
	    if (isset($user_verified) && !empty($user_verified)) {
	    	$html .= '<img class="ts_icon" src="' . SITE_URL . '/addons/plugins/UserVerified/html/icon.gif" title="' . $user_verified['info'] . '" />';
		}
	}

	// 个人空间右侧显示
	public function home_space_right_top($param)
	{
	    $uid = $param['uid'];
	    if(empty(self::$cache_list[$uid])|| !self::$cache_list['uid']){
	        self::$cache_list[$uid] = $this->_getVerifiedCache($uid);
	        if(false === self::$cache_list[$uid]) self::$cache_list[$uid] = $this->_setVerifiedCache($uid);
	    }
	    $user_verified = self::$cache_list[$uid];
		$this->assign('user_verified', $user_verified);
		$this->display('space_verified');
	}

	/* 插件后台管理项 */
	public function verifying()
	{
		$this->verified();
	}

	public function verified()
	{
    	//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if ( !empty($_POST) ) {
			$_SESSION['admin_searchVerifiedUser'] = serialize($_POST);
    		$this->assign('type', 'searchUser');
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['admin_searchVerifiedUser']);
    		$this->assign('type', 'searchUser');
		}else {
			unset($_SESSION['admin_searchVerifiedUser']);
		}

		$_POST['uid'] 	   && $map['uid'] 	   = array('IN', t($_POST['uid']));
		$_POST['realname'] && $map['realname'] = array('exp', 'LIKE "%' . t($_POST['realname']) . '%"');
		$_POST['phone']    && $map['phone']    = array('exp', 'LIKE "%' . t($_POST['phone']) . '%"');
		$_POST['reason']   && $map['reason']   = array('exp', 'LIKE "%' . t($_POST['reason']) . '%"');

    	$verified = ('verifying' == $_GET['page']) ? '0' : '1';
		$map['verified'] = "{$verified}";

    	$this->assign($_POST);
    	$this->assign('verified', $verified);
    	$this->assign(M('user_verified')->where($map)->findPage());
		$this->display('verified');
	}

	public function doVerifiedTab()
	{
		if (intval($_GET['uid']) > 0) {
			$verified = M('user_verified')->field('reason')->where("uid={$_GET['uid']}")->find();
			$this->assign('info', $verified['reason']);
		}
		$this->display('doVerifiedTab');
	}

	public function addVerifiedUser()
	{
    	if (intval($_GET['uid']) > 0) {
    		$verified = M('user_verified')->where('uid=' . intval($_GET['uid']))->find();
    		$verified['uid'] = intval($_GET['uid']);
    		$this->assign('verified', $verified);
    		$this->assign('jumpUrl', $_SERVER['HTTP_REFERER']);
    	}
    	$this->display('addVerifiedUser');
	}

    public function doVerified()
    {
		$uid = is_array($_POST ['uid']) ? '(' . implode ( ',', $_POST ['uid'] ) . ')' : '(' . $_POST ['uid'] . ')'; // 判读是不是数组
		foreach($uid as $value){
		    $this->_removeVerifiedCache($value);
		}
		$data['info'] = t(urldecode($_POST['info']));
		if (!$data['info']) {
			echo 0;
			exit;
		}
		$data['verified'] = '1';
		$res = M('user_verified')->where('uid IN ' . t($uid))->save($data); // 通过认证
    	if ($res) {
			if (strpos ($_POST['uid'], ',')) {
				echo 1;
				exit;
			} else {
				echo 2;
				exit;
			}

			// 发送通知
			$uids = explode(',', $_POST['uid']);
			$notify_dao = service ( 'Notify' );
			foreach ( $uids as $v ) {
				$notify_dao->sendIn ($v, 'admin_verified');
			}
		} else {
			echo 0;
			exit;
		}
    }

    public function saveVerified()
    {
    	$data = M('user_verified')->create();
    	if (!$data['uid']) {
    		$this->error('请选择用户');
    	} else if (!$data['info']) {
    		$this->error('请填写认证资料');
    	} else {
    		if ($data['id'] > 0) {
    			$res = M('user_verified')->save();
    		} else {
    			$res = M('user_verified')->add();
    		}
    		if (false !== $res) {
    		    $this->_removeVerifiedCache($data['uid']);
    			$jumpUrl = $_POST['jumpUrl'] ? $_POST['jumpUrl'] : U('admin/User/addVerifiedUser');
    			$this->assign('jumpUrl', $jumpUrl);
    			$this->success();
    		} else {
    			$this->error();
    		}
    	}
    }

    public function deleteVerified()
    {
		$uid = is_array($_POST ['uid']) ? '(' . implode ( ',', $_POST ['uid'] ) . ')' : '(' . $_POST ['uid'] . ')'; // 判读是不是数组
		foreach($uid as $value){
		    $this->_removeVerifiedCache($value);
		}
		$res = M('user_verified')->where('uid IN ' . t($uid) )->delete(); // 删除认证
    	if ($res) {
			if (strpos($_POST['uid'], ',') !== FALSE) {
				echo 1;
				exit;
			} else {
				echo 2;
				exit;
			}

			// 发送通知
			$uids = explode(',', $_POST['uid']);
			$notify_dao = service ( 'Notify' );
			$notify_tpl = (1 == $_POST['verified']) ? 'admin_delverified' : 'admin_rejectverified';
			foreach ( $uids as $v ) {
				$notify_dao->sendIn ($v, $notify_tpl, array('reason'=>t(urldecode($_POST['reason']))));
			}
		} else {
			echo 0;
			exit;
		}
    }
    
    public function deleteVerifiedTab()
    {
    	$this->display('deleteVerifiedTab');
    }

    private function _getListData($uid){
        return M('user_verified')->field('uid,realname,phone,reason,info')->where("verified='1' and uid={$uid}")->find();
    }

    private function _getVerifiedCache($uid){
        return unserialize(S('verified_'.$uid));
    }
    private function _removeVerifiedCache($uid){
        return S('verified_'.$uid,null);
    }

    private function _setVerifiedCache($uid){
        $list = $this->_getListData($uid);
        S('verified_'.$uid,serialize($list));
        return $list;
    }
}