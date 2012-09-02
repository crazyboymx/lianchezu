<?php

$key = model('Xdata')->lget('platform');
define('taobao_AKEY', $key['taobao_key']);
define('taobao_SKEY', $key['taobao_secret']);
?>