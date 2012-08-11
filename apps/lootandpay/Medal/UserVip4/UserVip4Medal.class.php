<?php
/**
 * 程序_小时代 提供
 */
class UserVip4Medal extends BaseMedal {
	
	protected $_is_UserVip4;
	
	public function getMedalStatus($uid, $medal_id, $user_medal_data, $medal_data) {
		!empty($user_medal_data['data']) && $user_medal_data['data'] = unserialize($user_medal_data['data']);
		!empty($medal_data['data']) 	 && $medal_data['data']		 = unserialize($medal_data['data']);
		
		$result = array();
		if ( empty($user_medal_data) ) {
			// 添加用户的勋章信息
			if ( $this->__isUserVip4($uid) ) {
				$data['received_time'] = time();
				$data['alert_message'] = '';
				$data['is_alert_on']   = 0;
				
				$this->__addUserCredit($uid);
			}else {
				$data['received_time'] = 0;
				$data['alert_message'] = $medal_data['data']['alert_message'];
				$data['is_alert_on']   = 1;
			}
			$this->dao->addUserMedal($uid, $medal_id, serialize($data));
			
			$result['received_time'] = $data['received_time'];
			$result['alert_message'] = $data['alert_message'];
			$result['is_alert_on']   = $data['is_alert_on'];
			
		}else {
			// 重新检查是否有头像, 如果与原有状态不符(如先上传,后取消), 更新用户勋章信息
			$user_medal_data['data']['received_time'] = intval($user_medal_data['data']['received_time']);
			if ( $user_medal_data['data']['received_time'] <= 0 && $this->__isUserVip4($uid) ) {
				$user_medal_data['data']['received_time'] = time();
				$user_medal_data['data']['alert_message'] = '';
				$user_medal_data['data']['is_alert_on']   = 0;
				
				$this->__addUserCredit($uid);
				
			}else if ( $user_medal_data['data']['received_time'] > 0 && !$this->__isUserVip4($uid) ) {
				$user_medal_data['data']['received_time'] = 0;
				$user_medal_data['data']['alert_message'] = $medal_data['data']['alert_message'];
				$user_medal_data['data']['is_alert_on']   = 1;
				
				$this->__deleteUserCredit($uid);
			}
			$this->dao->updateUserMedal($user_medal_data['user_medal_id'], serialize($user_medal_data['data']));
			
			$result['received_time'] = $user_medal_data['data']['received_time'];
			$result['alert_message'] = $user_medal_data['data']['alert_message'];
			$result['is_alert_on']   = $user_medal_data['data']['is_alert_on'];
		}
		
		$result['title']			= $medal_data['title'];
		$result['icon_url']			= SITE_URL . '/addons/plugins/Medal/lib/' . $medal_data['path_name'] . '/icon.png';
		$result['big_icon_url']		= SITE_URL . '/addons/plugins/Medal/lib/' . $medal_data['path_name'] . '/bigicon.png';
		$result['description']		= $medal_data['data']['description'];
		$result['next_level_time']	= $this->__isUserVip4($uid) ? '0' : '抢占星位';
		return $result;
	}
	
	/**
	 * 关闭勋章的提示消息
	 * 
	 * @param int $uid
	 * @param int $medal_id
	 */
	public function closeMedalAlert($uid, $medal_id) {
		$medal_data = $this->dao->getUserMedal($uid, $medal_id);
		$medal_data['data'] = unserialize($medal_data['data']);
		$medal_data['data']['is_alert_on'] = '0';
		$this->dao->updateUserMedal($medal_data['user_medal_id'], serialize($medal_data['data']));
	}
	
	private function __isUserVip4($uid) {
		if (!isset($this->_is_UserVip4)) {
			$this->_is_UserVip4 = false;
			 $map['uid']=$uid;	
			 $map['issucess']=1;	
			 $map['tosucess']=1;	
  		 	 $sum=M('user_pay')->where($map)->sum('amount');
				if($sum > 500){
					$this->_is_UserVip4 = true;
				}
		}
		
		return $this->_is_UserVip4;
	}
	
	private function __addUserCredit($uid) {
		X('Credit')->setUserCredit($uid,'add_medal');
	}
	
	private function __deleteUserCredit($uid) {
		X('Credit')->setUserCredit($uid,'delete_medal');
	}
}