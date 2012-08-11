<?php
/**
 * 程序_小时代 提供
 */
return array(
	'path_name'		=> 'LootStarUser',
	'title'			=> '抢占达人',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/LootStarUser/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/LootStarUser/bigicon.png',
									'description'	=> '在"抢星位"中抢得次数累计大于50,即可获得',
									'alert_message'	=> '<a href="'.U('loot/Index/index').'">抢占星位</a>, 做魅力达人！',
								)),
);