<?php
//////////////////////���ο��� START//////////////////////////////
//���޸Ľ�ȡͼƬ��С
$WIDTH=60;//���
$HEIGHT=60;//�߶�
//////////////////////���ο��� END////////////////////////////////

function getSaveTempPath() {
	$savePath = SITE_PATH . '/data/uploads/temp';
	if (! file_exists ( $savePath ))
		mk_dir ( $savePath );
	return $savePath;
}