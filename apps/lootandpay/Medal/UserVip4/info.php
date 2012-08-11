<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'UserVip4',
	'title'			=> '富甲一方',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/UserVip4/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/UserVip4/bigicon.png',
									'description'	=> '充值500元以上,即可获得',
									'alert_message'	=> '<a href="'.U('home/Account/userpay',array('addon' => 'UserPay','hook' => 'home_account_show')).'">立即充值</a>, 做至尊会员！',
								)),
);