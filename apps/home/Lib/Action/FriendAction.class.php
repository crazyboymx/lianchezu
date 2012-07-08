<?php
class FriendAction extends Action {
	private $require_authorization;
	
	public function _initialize() {
		$this->require_authorization = false;
	}
	
	public function index() {
		$users = M('user')->field('`uid`,`email`,`uname`')->findAll();
		$this->assign('users', $users);
//		dump($users);
		
		$friend = model('Friend')->getFriendList($this->mid);
		$this->assign($friend);
//		dump($friend);
		
		$fuids  = getSubByKey($friend['data'], 'friend_uid');
//		dump($fuids);
		$group  = model('Friend')->getGroupOfFriend($this->mid, $fuids);
		$this->assign('group', $group);
//		dump($group);
		
		$groups = model('Friend')->getGroupList($this->mid, true);
		$this->assign('groups', $groups);
//		dump($groups);
//		exit;
		$this->display();
	}
	
	public function doAddFriend() {
		//准备参数，合法性检验
		$gid  = explode( ',', rtrim(t($_POST['gid']), ',') );
		$fuid = intval($_POST['fuid']);
		if ($fuid <= 0 || $this->mid == $fuid) {
			echo 0; 
			return;
		}
		foreach ($gid as $k => $v) {
			if ($v <= 0) {
				unset($gid[$k]);
				continue;
			}
		}
		$gid  = empty($gid) ? 0 : $gid;
		
		//已经是好友
		if ( model('Friend')->isFriends($this->mid, $fuid) ) {
			echo -1;
			return ;
		}
		
		//添加好友
		$res = model('Friend')->addFriend($this->mid, $fuid, $gid, $this->require_authorization, t($_POST['message']));
		if ($res) {
			//发送通知
			//发送动态
			//添加积分
			echo 1;
		}else {
			echo 0;
		}
	}
	
	public function selectFriendGroup() {
		$groups = model('Friend')->getGroupList($this->mid);
		
		$this->assign('groups', $groups);
		$this->assign('fuid', intval($_GET['fuid']));
		$this->assign('require_authorization', $this->require_authorization ? 1 : 0);
		$this->display();
	}
}