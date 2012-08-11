<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'LootKing',
	'title'			=> '抢占至尊',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/LootKing/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/LootKing/bigicon.png',
									'description'	=> '在"抢星位"中抢得次数累计大于100,即可获得',
									'alert_message'	=> '<a href="'.U('loot/Index/index').'">抢占星位</a>, 做魅力达人！',
								)),
);