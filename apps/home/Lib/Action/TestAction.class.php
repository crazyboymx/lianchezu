<?php
error_reporting(E_ALL);
class TestAction extends Action{

	public function index()
	{
		// 未指定参数时, 加载系统配置
		$config = model('Xdata')->lget('top_follower');
		!isset($hide_auto_friend) && $hide_auto_friend = intval($config['hide_auto_friend']);
		!isset($hide_no_avatar)   && $hide_no_avatar   = intval($config['hide_no_avatar']);

		$uid       = intval($uid);
		$count 	   = intval($count);
		$limit     = 10;       // 查询的结果数
		$following = array(); // 已关注的用户
		$top_user  = array(); // 最终结果

		$cache_id = '_weibo_top_followed_' . $count .'_'. $uid .'_'. intval($hide_auto_friend) . intval($hide_no_avatar);

		// 缓存有效时间: 1 Hour
		$expire   = 1 * 3600;

		//if (($top_user = S($cache_id)) === false) {

			// 隐藏无头像用户时, 为了保证最后结果满足$limit, 查询时使用3倍的$limit
			$limit   += $hide_no_avatar ? $count * 3 : $count;

			$where = 'WHERE `type` = 0 ';
			if ($hide_auto_friend) { // 隐藏默认关注的用户时
				$auto_friend = model('Xdata')->get('register:register_auto_friend');
				$auto_friend = explode(',', $auto_friend);
				if (count($auto_friend) > 1)
					$where .= 'AND `fid` NOT IN ( ' . implode(',', $auto_friend) . ' )';
			}
			$sql = "SELECT `fid` AS `uid`, count(`uid`) AS `count` FROM ts_weibo_follow " .
				   $where . " GROUP BY `fid` " .
				   "ORDER BY `count` DESC LIMIT {$limit}";
			dump($sql);
			$res = M()->query($sql);
			dump($res);
			$res = $res ? $res : array();

			if (!empty($res)) { // 过滤
				$index = 1;
				$noPic = array();
				foreach ($res as $k => $v) {
					if ($index > $count) {
						break;
					} else if ($hide_no_avatar && !hasUserFace($v['uid'])) { // 剔除无头像的用户
						$noPic[] = $v;
						unset($res[$k]);
						continue ;
					} else if ($uid > 0 && in_array($v['uid'], $following)) { // 剔除已关注的用户
						unset($res[$k]);
						continue ;
					}
					$top_user[] = $v;
					++ $index;
				}
			}
			unset($res);
			if(empty($top_user) && !empty($noPic)){
				$top_user = $noPic;
			}

			//S($cache_id,empty($top_user)?array():$top_user,$expire);
		//}

		return $top_user;
	}

	public function getMessage(){
		service('Notify')->getNotifityCount($this->mid,1,0,0,20,1);
	}
	
	public function getSite(){
		$map['site_id'] = array('gt', 0);
		$sql = ' and status=1';
		D('Site','sitelist')->where($map.$sql)->findAll();
		dump(M('')->getLastSql());
	}
}