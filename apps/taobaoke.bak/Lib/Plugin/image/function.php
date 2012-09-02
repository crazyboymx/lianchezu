<?php
//////////////////////二次开发 START//////////////////////////////
//请修改截取图片大小
$WIDTH=60;//宽度
$HEIGHT=60;//高度
//////////////////////二次开发 END////////////////////////////////

function getSaveTempPath() {
	$savePath = SITE_PATH . '/data/uploads/temp';
	if (! file_exists ( $savePath ))
		mk_dir ( $savePath );
	return $savePath;
}