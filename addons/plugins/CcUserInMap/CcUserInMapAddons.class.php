<?php
class CcUserInMapAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = 'cc';
	protected $site    = 'http://weibo.com/caicai1106';
	protected $info    = '标注用户在地图上位置';
	protected $pluginName = '用户地图';
	protected $tsVersion  = "2.5";     
	
	/**
     * getHooksInfo
     * 获得该插件使用了哪些钩子,钩子和本类的方法是怎样的映射关系
     * @access public
     * @return void
     */
	public function getHooksInfo(){
		$this->apply("home_account_profile_bottom","_mapform");
		$this->apply("home_space_profile_bottom", "_spaceMapshow");
	}
	
	public function _mapform(){
		global $ts;
        $uid = $ts[user][uid];
        $map['uid'] = $uid;
		$res = M('userinmap')->field('positionX,positionY,showlevel')->where($map)->find();
		$this->assign($res);
		if ($res['positionX'] && $res['positionY']){
			$mappostiion = $res['positionX'].",".$res['positionY'];
		}
		$this->assign('mapposition',$mappostiion);
		$this->display('mapform');
	}
	public function _spaceMapshow($param){
		$uid = $param[0];
        $map['uid'] = $uid;
        $mid = $this->mid;
		$res = M('userinmap')->field('uid,username,positionX,positionY,showlevel')->where($map)->find();
		switch ($res['showlevel']){
			case 0 ://自己可见
				if ($uid == $mid){
					$show = 1;
				}else{
					$show = 0;
				}
				break;
			case 1 ://所有人可见
				$show = 1;
				break;
			case 2 ://粉丝可见
				$followState = $this->getUserFollowState($mid,$uid,2);
				if($followState == 'havefollow' || $uid == $mid){
					$show = 1;
				}else{
					$show = 0;
				}
				break;
			case 3 :// 我关注的可见
				$followState = $this->getUserFollowState($mid,$uid,3);
				if($followState == 'belongfollow' || $uid == $mid){
					$show = 1;
				}else{
					$show = 0;
				}
				break;
			case 4 :// 互粉可见
				$followState = $this->getUserFollowState($mid,$uid,4);
				if($followState == 'eachfollow' || $uid == $mid){
					$show = 1;
				}else{
					$show = 0;
				}
				break;
		}
		if ($res['positionX'] && $res['positionY']){
			$mappostiion = $res['positionX'].",".$res['positionY'];
		}
		if($show && $mappostiion){
			$this->assign($res);
			$this->assign('mapposition',$mappostiion);
			$this->display("mapshow");
		}
	}
	public function markPosition(){
		//$data['mapposition'] = trim($_REQUEST['mapposition']);
		
		$this->assign($_GET);
		$this->display('location');
	}
	/**
	 * 地图展示
	 * Enter description here ...
	 */
	public function showMap(){
	
		$this->assign($_GET);
		$this->display('location');
	}
	public function upuserinmap(){
		global $ts;
        $uid = $ts[user][uid];
        $username = $ts[user][uname];
		$data['uid'] = $uid;
		if(!$data['uid']){
			return false;
		}
		$dao = D("userinmap");
		$res = $dao->field('id')->where($data)->find();
		$data['time'] = time();
		$position = trim($_REQUEST['position']);
		$position = explode(",", $position);
		$data['positionX'] = $position[0]; 
		$data['positionY'] = $position[1];
		$data['username'] = $username;
		$data['showlevel'] = intval($_REQUEST['showlevel']);
		if($res){
			$data['id'] = $res['id'];
			$res = $dao->save($data);
			$data['message'] = L('update_done');
			$data['boolen']  = 1;
		}else{
			$dao->add($data);
			$data['message'] = L('update_done');
			$data['boolen']  = 1;
		}
		
		exit( json_encode($data) );
	}
	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}userinmap` (
                    `id` int(12) NOT NULL auto_increment,
                    `uid` int(12) NOT NULL,
                    `username` varchar(50) NOT NULL,
                    `time` int(11) NOT NULL,
                    `positionX` varchar(255) NOT NULL,
                    `positionY` varchar(255) NOT NULL,
                    `showlevel` int(1) NOT NULL default '0',
                    PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		if (false !== M()->execute($sql)) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}userinmap`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
	
	/**
	 * 关注状态
	 */
	public function getUserFollowState($uid, $fid, $config = 0) {
		
		if ($uid <= 0 || $fid <= 0)
			return 'unfollow';
		
		
		
		switch ($config){
			case 0 : //仅自己
				if ($uid == $fid){
					return 'selffollow';
				}else{
					return 'unfollow';
				}
				break;
			case 1 : //所有人
				break;
			case 2 : //粉丝
				if (M ( 'weibo_follow' )->where ( "uid=$uid AND fid=$fid AND type=0" )->count ()) {
					return 'havefollow';
				}else{
					return 'unfollow';
				}
				break;
			case 3 :  //关注
				if (M ( 'weibo_follow' )->where ( "uid=$fid AND fid=$uid AND type=0" )->count ()){
					return 'belongfollow';
				}else {
					return 'unfollow';
				}
				break;
			case 4 :  //互粉
				if (M ( 'weibo_follow' )->where ( "(uid=$uid AND fid=$fid AND type=0) OR (uid=$fid AND fid=$uid AND type=0)" )->count () == 2){
					return 'eachfollow';
				}else {
					return 'unfollow';
				}
				break;
		}
	}
}
?>