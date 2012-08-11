<?php

//活动剩余座位数量
function getLoots($id){
	$data = model('Xdata')->lget('loot');
	$loots=$data['loots'];
	$days=$data['lootdays'];
	$minutes=$days*60*60*24; //星位停留时间
	$loottime=time()-$minutes;
	$map['cid']=$id;
	$map['ctime']= array('gt',$loottime);
	return $loots - M('loot')->where($map)->count();
}

//获取粉丝数
function getFollower($uid){
	$count  = M('weibo_follow')->where("fid=$uid AND type=0")->count();
	return $count;
}

//获取最近一条微博
function getUsermini($uid){
	$data  = M('weibo')->where('uid='.$uid.' AND type=0 AND isdel = 0')->order('weibo_id DESC')->find();
	return $data;
}
