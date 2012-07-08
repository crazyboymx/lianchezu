<?php
/**
 * 好友模型
 *
 * @author daniel <desheng.young@gmail.com>
 */
class FriendModel extends Model {
	protected $tableName = 'friend';
	protected $default_group_name = '未分组';

	/**
	 * 查询好友表
	 *
	 * @param array|string $map   查询条件
	 * @param string       $field 默认*
	 * @param int          $limit 默认空
	 * @param string       $order 默认空
	 * @param boolean      $is_find_page 是否分页,默认true
	 * @return array
	 */
	public function getFriedByMap($map = array(), $field = '*', $limit = '', $order = '', $is_find_page = true) {
		if ($is_find_page) {
			return $this->Distinct('uid')->where($map)->field($field)->order($order)->findPage($limit);
		}else {
			return $this->Distinct('uid')->where($map)->field($field)->order($order)->limit($limit)->findAll();
		}
	}

	/**
	 * 获取好友列表
	 *
	 * @param int $uid 				用户ID
	 * @param int $friend_group_id  好友分组ID 默认null则不分组查找
	 * @param int $limit 			默认20
	 * @param string $order 		默认 以好友名字升序，好友ID升序
	 * @return array
	 */
	public function getFriendList($uid, $friend_group_id = null, $limit = 20, $order = 'friend_uname ASC, friend_uid ASC') {
		if ( !isset($friend_group_id) ) {
			return $this->where("`uid`=$uid AND `status`=1")->order($order)->findPage($limit);
		}else {
			return M('friend_group_link')->where("`uid`=$uid AND `friend_group_id`=$friend_group_id AND `status`=1")
										 ->order($order)
										 ->findPage($limit);
		}
	}

	/**
	 * 添加好友
	 *
	 * @param int     $uid 					 用户ID
	 * @param int     $friend_uid 			 好友ID
	 * @param array   $friend_group_id 		 好友分组ID
	 * @param boolean $require_authorization 是否需要请求,false:不需要,true:需要
	 * @param string  $message 				 请求信息
	 * @return boolean
	 */
	public function addFriend($uid, $friend_uid, $friend_group_id = 0, $require_authorization = false, $message = '') {
		//ts_friend
		$data['uid'] 			= $uid;
		$data['friend_uid'] 	= $friend_uid;
		$data['friend_uname'] 	= getUserName($friend_uid);
		$data['status'] 		= $require_authorization ? 0 : 1;
		$data['message']		= $message;
		$data['ctime']			= time();
		$res = $this->add($data);

		//ts_friend_group_link
		unset($data['message']);
		$friend_group_id = $friend_group_id === 0 ? array('0') : $friend_group_id;

		foreach ($friend_group_id as $v) {
			unset($data['friend_group_id']);
			$data['friend_group_id'] = $v;
			M('friend_group_link')->add($data);
		}

		return $res;
	}

	/**
	 * 接受好友请求
	 *
     * @param int   $uid			 用户ID
     * @param int   $friend_uid		 好友ID
     * @param array $friend_group_id 好友分组ID
     * @return boolean
	 */
	public function acceptFriend($uid, $friend_uid, $friend_group_id = 0) {

	}

	/**
	 * 删除好友
	 *
	 * @param int     $uid 用户ID
	 * @param int     $friend_uid 好友ID
	 * @param boolean $require_authorization 是否发送通知,双方面删除
	 * @return boolean
	 */
	public function deleteFriend($uid, $friend_uid, $require_authorization = false) {
		if ($require_authorization) {
			//双方面删除
			//发送通知
		}else {
			//单方面删除
		}
	}

	/**
	 * 判断是否为好友
	 *
	 * @param int $uid        用户ID
	 * @param int $friend_uid 对方ID
	 * @return boolean
	 */
	public function isFriends($uid, $friend_uid) {
		return $this->where("`uid`=$uid AND `friend_uid`=$friend_uid AND `status`=1")->find();
	}

	/**
	 * 获取给定用户的所有好友分组
	 *
	 * @param int     $uid
	 * @param boolean $show_count 统计好友数量
	 * @return array
	 */
	public function getGroupList($uid, $show_count = false) {
		$res = M('friend_group')->where("`uid`=$uid OR `uid`=0")->order('friend_group_id DESC')->findAll();

		if ($show_count && $res) {
			$sql = 'SELECT count(friend_uid) AS count, friend_group_id FROM ' . C('DB_PREFIX') . 'friend_group_link
					WHERE `uid` = ' . $uid . ' AND `status` = 1 GROUP BY friend_group_id';
			$tmp = $this->query($sql);
			//格式化统计数据
			foreach ($tmp as $v) {
				$count[$v['friend_group_id']] = $v['count'];
			}

			foreach ($res as $k => $v) {
				$res[$k]['count'] = intval($count[$v['friend_group_id']]);
			}
			//未分组的
			if ($count[0] > 0 ) {
				$res[] = array('friend_group_id'=>0,'title'=>$this->default_group_name,'count'=>$count[0]);
			}
		}

		return $res;
	}

	/**
	 * 获取给定用户的给定好友所在的分组
	 *
	 * @param int $uid
	 * @param int $friend_uid
	 * @return array
	 */
	public function getGroupOfFriend($uid, $friend_uid) {
		$friend_uid = !is_array($friend_uid) ? $friend_uid : implode(',', $friend_uid);
		$db_prefix	= C('DB_PREFIX');
		$field 		= "l.friend_uid AS friend_uid, g.friend_group_id AS friend_group_id, g.title AS title";
		$join 		= "INNER JOIN {$db_prefix}friend_group_link AS l ON g.friend_group_id = l.friend_group_id";
		$where 		= "l.uid = $uid AND l.friend_uid IN ( $friend_uid ) AND l.status = 1";
		$res = $this->table("{$db_prefix}friend_group AS g")->field($field)->join($join)->where($where)->findAll();

		//格式化输出
		foreach ($res as $v) {
			$group[$v['friend_uid']][] = $v;
		}
		return $group;
	}

	/**
	 * 可能认识的人 (可能认识的人 = 有相同tag的用户 || 所在城市相同的用户 || 好友的好友 || 我的粉丝 || 随机推荐)
	 *
	 * 注意: 因为头像信息未保存数据库, 所以当开启"隐藏无头像的用户"时, 结果集数量可能小于$max
	 *
	 * @param int 	  $uid 		  用户ID
	 * @param int 	  $max 		  获取的最大人数
	 * @param boolean $do_shuffle 是否随机次序 (默认:true)
	 * @return boolean|array 用户ID的数组
	 */
	public function getRelatedUser($uid, $max = 100, $do_shuffle = true)
	{
		if (($uid = intval($uid)) <= 0)
			return false;

		// 权重设置
		$config = model('Xdata')->lget('related_user');
		$tag_weight      = isset($config['tag_weight'])      ? intval($config['tag_weight'])      : 4; // 拥有相同Tag
    	$city_weight     = isset($config['city_weight'])     ? intval($config['city_weight'])     : 3; // 设置的城市相同
    	$friend_weight   = isset($config['friend_weight'])   ? intval($config['friend_weight'])   : 2; // 好友的好友
    	$follower_weight = isset($config['follower_weight']) ? intval($config['follower_weight']) : 1; // 我的粉丝
		$total_weight    = $tag_weight + $city_weight + $friend_weight + $follower_weight;

		// 是否隐藏无头像的用户
		$hide_no_avatar  = $config['hide_no_avatar'];

		// 权重对应的数量
		$tag_count 		 = intval($tag_weight      / $total_weight * $max);
		$city_count 	 = intval($city_weight     / $total_weight * $max);
		$friend_count    = intval($friend_weight   / $total_weight * $max);
		$follower_count  = intval($follower_weight / $total_weight * $max);

		$related_uids = array();

		// 按Tag
		if ($tag_count > 0) {
			$tag_uids      = $this->_getRelatedUserFromTag($uid, $related_uids, $tag_count);
			$related_uids  = array_merge($related_uids, $tag_uids);
		}

		// 按设置的城市
		if ($city_count > 0) {
			$limit         = $city_count + ($tag_count - count($related_uids));
			$city_uids     = $this->_getRelatedUserFromCity($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $city_uids);
		}

		// 按好友的好友
		if ($friend_count > 0) {
			$limit 		   = $friend_count + ($tag_count + $city_count - count($related_uids));
			$friend_uids   = $this->_getRelatedUserFromFriend($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $friend_uids);
		}

		// 按粉丝
		if ($follower_count > 0) {
			$limit 		   = $follower_count + ($tag_count + $city_count + $friend_count - count($related_uids));
			$follower_uids = $this->_getRelatedUserFromFollower($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $follower_uids);
		}

		// 随机推荐
		$limit         = $max - count($related_uids);
		$random_uids   = $this->_getRandomRelatedUser($uid, $related_uids, $limit);
		$related_uids  = array_merge($related_uids, $random_uids);

		// 按"好友的好友"推荐时, 可能会产生重复用户
		$related_uids  = array_unique($related_uids);

		// 添加推荐原因
		foreach ($related_uids as $k => $v) {
			if ($hide_no_avatar && !hasUserFace($v)) {
				unset($related_uids[$k]);
				continue ;
			}

			if (in_array($v, $tag_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => 'Tag相同');
			else if (in_array($v, $city_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '城市相同');
			else if (in_array($v, $friend_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '好友的好友');
			else if (in_array($v, $follower_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '您的粉丝');
			else if (in_array($v, $random_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '随机推荐');
		}

		if ($do_shuffle)
			shuffle($related_uids);

		return $related_uids;
	}

	/**
	 * 根据Tag推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromTag($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$model    = D('UserTag', 'home');
		$tag_list = $model->getUserTagList($uid);
		$tag_ids  = getSubByKey($tag_list, 'tag_id');
		$tag_ids  = implode(',', $tag_ids);
		$now_uids = implode(',', array_merge($related_uids, array($uid)));
		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$sql = "SELECT `uid` FROM {$this->tablePrefix}user_tag WHERE `uid` NOT IN ( {$now_following} )  AND `uid` NOT IN ( {$now_uids} ) AND `tag_id` IN ( {$tag_ids} ) LIMIT {$limit}";

		if ($res = $model->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 根据用户设置的城市推荐用户. 如果当前用户没有设置城市, 则返回空数组
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromCity($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$model = D('User', 'home');
		$user  = $model->getUserByIdentifier($uid, 'uid');
		if (empty($user['location']))
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));

		$map['uid']		  = array('exp', " NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} )");
		$map['location']  = $user['location'];
		$map['is_active'] = '1';
		$map['is_init']   = '1';
		if ($res = $model->where($map)->field('uid')->limit($limit)->findAll())
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 根据"好友的好友"推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 * @todo 还需要优化效率 (用户总数8000: 粉丝和关注各7000+时, 执行时间约500ms; 粉丝和互粉各2000+时, 执行时间约3ms)
	 */
	protected function _getRelatedUserFromFriend($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		// DISTINCT在大数据量时对性能影响太大, 所以不加
		$sql = "SELECT `fid` FROM {$this->tablePrefix}weibo_follow " .
			   "WHERE `fid` NOT IN ( {$now_following} ) AND `fid` NOT IN ( {$now_uids} ) AND `uid` IN ( {$now_following} ) AND `type` = '0' " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'fid');
		else
			return array();
	}

	/**
	 * 根据粉丝推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromFollower($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		$sql = "SELECT `uid` FROM {$this->tablePrefix}weibo_follow WHERE " .
			   "`fid` = {$uid} AND `uid` NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} ) " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 随机推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRandomRelatedUser($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		$sql = "SELECT `uid` FROM {$this->tablePrefix}user WHERE " .
			   "`uid` NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} ) " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}
}
?>