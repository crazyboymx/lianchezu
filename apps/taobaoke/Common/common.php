<?php

//是否已设置头像
function isSetAvatar($uid){
    return is_file( SITE_PATH.'/data/uploads/avatar/'.$uid.'/small.jpg');
}

//获取微博条数
function getMiniNum($uid){
	return M('weibo')->where('uid='.$uid)->count();
}

//仿知美二次开发 获取微博条数
function getMiniNum_zhuan($uid){
	return M('weibo')->where('uid='.$uid.' AND isdel=0 AND type<>0')->count();
}

//获取关注数
function getUserFollow($uid){
	$count['following'] = M('weibo_follow')->where("uid=$uid AND type=0")->count();
	$count['follower']  = M('weibo_follow')->where("fid=$uid AND type=0")->count();
	return $count;
}

// 短地址
function getContentUrl($url) {
	return getShortUrl( $url[1] ).' ';
}

// 登陆页微博表情解析
function login_emot_format($content)
{
    echo preg_replace_callback('/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is', 'replaceEmot', $content);
}

//仿知美二次开发 获取喜欢数
function getfavnum($uid){
	return M('fav')->where('uid='.$uid)->count();
}

//仿知美二次开发  获取被喜欢数
function getfavednum($uid){
	return M('fav')->where('favuid='.$uid)->count();
}

