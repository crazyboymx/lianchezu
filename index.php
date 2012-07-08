<?php
error_reporting(E_ERROR | E_PARSE | E_STRICT);

define('SITE_PATH', getcwd());

define('RUNTIME_ALLINONE', false);	// 是否开启AllInOne模式 (开启时, NO_CACHE_RUNTIME 和 APP_DEBUG将失效)
define('NO_CACHE_RUNTIME', true);	// 是否关闭核心文件的编译缓存 (开启AllInOne模式时设置无效, 将自动置为false)

require(SITE_PATH.'/core/sociax.php');

//实例化一个网站应用实例
$App = new App();
$App->run();