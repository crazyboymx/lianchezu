<?php

header("Content-type: text/html; charset=utf-8") ;
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT") ;
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT") ;
header("Cache-Control: no-cache, must-revalidate") ;
header("Pragma: no-cache") ;


//测试
require_once 'paipai/common.fun.php' ;
require_once 'paipai/paipai/config.inc.php' ;


//API系统参数
/*
  以下系统参数可以免登陆
  若WEB或者CLIENT登陆了，以下参数可以省去
 */


function get_paipai_goods($itemCode,$format='xml')
{
        $paipaiParamArr = array(
        'uin' => PAIPAI_API_UIN ,
        'token' => PAIPAI_API_TOKEN ,
        'spid' => PAIPAI_API_SPID ,
        ) ;
        //API用户参数
        $userParamArr = array(
                'charset' => 'utf-8' ,
                'format' => $format ,
                'sellerUin' => PAIPAI_API_UIN ,
                'pageIndex' => 1 ,
                'pageSize' => 10 ,
                'itemCode' => $itemCode
                ) ;

        //总参数数组
        $paramArr = $paipaiParamArr + $userParamArr ;
        
        //请求数据
       $result = Util::getResult($paramArr , '/item/getItem.xhtml') ;
        
        //解析xml结果
        $result = Util::getXmlData($result) ;
        return $result;
        
}

?>