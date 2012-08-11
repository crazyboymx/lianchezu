<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'UserVip5',
	'title'			=> '富可敌国',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/UserVip5/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/UserVip5/bigicon.png',
									'description'	=> '充值1000元以上,即可获得',
									'alert_message'	=> '<a href="'.U('home/Account/userpay',array('addon' => 'UserPay','hook' => 'home_account_show')).'">立即充值</a>, 做至尊会员！',
								)),
);