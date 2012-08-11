<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'UserVip1',
	'title'			=> '小有积蓄',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/UserVip1/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/UserVip1/bigicon.png',
									'description'	=> '充值10元以上,即可获得',
									'alert_message'	=> '<a href="'.U('home/Account/userpay',array('addon' => 'UserPay','hook' => 'home_account_show')).'">立即充值</a>, 做至尊会员！',
								)),
);