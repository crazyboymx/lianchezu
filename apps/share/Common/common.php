<?php

/*
  根据专辑Logo的保存路径获取Logo的URL
 */
function logo_path_to_url($save_path)
{
	$path = SITE_PATH . '/data/uploads/' . $save_path;
	if (file_exists($path))
		return SITE_URL . '/data/uploads/' . $save_path;
	else
		return SITE_URL . '/apps/share/Tpl/default/Public/images/share_pic.gif';
}

function get_photo_url($savepath) {
	$path	=	'./data/uploads/' . $savepath;
	if(!file_exists($path))
	   $path = './apps/share/Tpl/default/Public/images/share_pic.gif';
	return $path;
}

function share_get_content_url($url){
	return getShortUrl( $url[1] ).' ';
}

//获取专辑信息
function getshareinfo($id,$field) {
	$data = D('Share')->find($id);
	if(empty($data)) return '';
	return $data[$field];
}

//截取content
function getBlogShort($content,$length = 20) {
	$content	=	real_strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}
