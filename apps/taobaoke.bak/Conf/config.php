<?php
//由ThinkPHP工具箱生成的配置文件
if (!defined('THINK_PATH')) exit();
$miniConfig = array (
    'LANG_SWITCH_ON' => true,
    'DEBUG_MODE'     => true,
    'DEFAULT_ACTION' => 'index',
);
$array = require_once( SITE_PATH.'/config.inc.php' );
$array = array_merge( $miniConfig,$array );
return $array;
