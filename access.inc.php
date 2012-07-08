<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 */
return array(
	"access"	=>	array(
		'home/Widget/renderWidget' 	=> true, // 渲染插件
		'home/User/countNew'		=> true, // 获取最新微博
		'home/Public/*'				=> true, // 登录注册
		'home/Space/*'      		=> true, // 个人空间
		'phptest/*/*'				=> true, // 测试应用
		'api/*/*'					=> true, // Api接口
		'wap/*/*'					=> true, // Wap版
        'w3g/*/*'					=> true, // 3G版
		'admin/*/*'					=> true, // 管理后台的权限由它自己控制
		'home/Square/*'				=> true, // 微博广场的权限由管理后台控制
		'home/User/topics'			=> true, // 话题列表
		'home/Widget/addonsRequest' => true, // 便于匿名下的钩子异步
		'home/Widget/weiboShow'		=> true, // 微博秀
		'home/Widget/share'			=> true, // 站外分享

		'blog/Index/news'			=> true, // 最新博客
		'blog/Index/show'			=> true, // 博客内容
		'blog/Index/personal'		=> true, // 个人博客

		'photo/Index/photo'			=> true, // 照片展示
		'photo/Index/album'			=> true, // 相册展示
		'photo/Index/photos'		=> true, // 所有照片

		'group/Index/index'		=> true, // 群组首页
		'group/Index/newIndex'		=> true, // 群组首页
		'group/Index/search'		=> true, // 分类列表
		'group/Group/index'			=> true, // 单群首页
	)
);