<?php
return array(
	/**
	 * 敏感词过滤配置.表明（无前缀）\ 需要过滤的字段名（多个字段以逗号分割）. * 代表所有的表
	 */
	'badwords' => array(

		//全局配置
		'*'				=>	array('title','content'),

		//微博配置
		'attach'		=>  array('name'),//附件名
		'comment'		=>  array('comment'),//评论内容
		'message'		=>  array('title','content'),//站内信
		'message_content' 	=>array('content'),
		'message_list'	=>	array('title'),
		'tag'			=> 	array('tag_name'),//标签
		'user_group_link'	=>array('user_group_title '),
		'user'			=>	array('uname'),
		'weibo'			=>	array('content'),
		'weibo_comment'	=>	array('content'),//微博评论
		'weibo_follow_group'	=>	array('title'),
		//博客配置
		'blog'			=>  array('title'),
		'blog_category' =>	array('name'),
		//活动配置
		'event'			=>	array('title','title','contact','explain','address'),
		//礼物配置
		'gift_user'		=>	array('sendInfo'),
		//群组配置
		'group'			=>	array('name','intro','announce'),
		'group_album'	=>	array('name','info'),
		'group_member'	=>	array('reason'),
		'group_photo'	=>	array('name'),
		'group_post'	=>	array('content'),
		'group_topic'	=>	array('title'),
		'group_topic_category'	=>	array('title'),
		//招贴配置
		'poster'		=>	array('title','content','contact'),
		//投票配置
		'vote'			=>	array('title','explain'),
		'vote_opt'		=>	array('name'),
	)
);