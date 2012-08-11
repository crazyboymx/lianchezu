<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'UserVip3',
	'title'			=> '家财万贯',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/UserVip3/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/UserVip3/bigicon.png',
									'description'	=> '充值100元以上,即可获得',
									'alert_message'	=> '<a href="'.U('home/Account/userpay',array('addon' => 'UserPay','hook' => 'home_account_show')).'">立即充值</a>, 做至尊会员！',
								)),
);