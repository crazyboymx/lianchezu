<?php
class InviteRecordModel extends Model
{
	protected $tableName = 'invite_record';
	
	public function addRecord($uid, $fid)
	{
		if (($uid = intval($uid)) <= 0 || ($fid = intval($fid)) <= 0)
			return false;

		$time = time();
		$sql = "REPLACE INTO __TABLE__ (`uid`, `fid`, `ctime`) VALUES ('{$uid}', '{$fid}', '{$time}')";
		return $this->execute($sql);
	}
	
	public function getStatistics($uid = 0, $order = 'count DESC')
	{
		$table_name = $this->trueTableName;
		if (empty($uid) || (is_numeric($uid) && ($uid = intval($uid)) <= 0)) {
			$sql = "SELECT `uid`, count(`fid`) AS `count` FROM {$table_name} GROUP BY `uid` ORDER BY {$order}";
		} else {
			if (is_array($uid))
				$uid = t(implode(',', $uid));
			$sql = "SELECT `uid`, count(`fid`) AS `count` FROM {$table_name} WHERE `uid` IN ( {$uid} ) GROUP BY `uid` ORDER BY {$order}";
		}
		return $this->findPageBySql($sql);
	}
	
	public function getInvitedUser($uid)
	{
		if (($uid = intval($_GET['uid'])) <= 0)
			return false;
			
		$map['uid'] = $uid;
		return $this->where($map)->order('invite_record_id DESC')->findPage();
	}
}