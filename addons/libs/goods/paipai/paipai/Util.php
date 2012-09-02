<?php
require_once 'Snoopy.class.php';

class Util {
	static private $snoopy = NULL;
	
	/**
	 * 实例化Snoopy
	 */
	static public function instanceSnoopy() {
		if (self::$snoopy == NULL) {
			self::$snoopy = new Snoopy();
		}
	}
	
	//生成cmdid
	/*
	 *例如：http://api.paipai.com/item/addItem.xhtml，其requestURLPath="/item/addItem.xhtml "，对应的cmdid="item.addItem"。
	 *又如：http://api.paipai.com/deal/getDeal.xhtml，其requestURLPath="/deal/getDeal.xhtml "，对应的cmdid="deal.getDeal"。
	*/
	static public function createCmdid($requestURLPath)
	{	
		if(strlen($requestURLPath)==0)return false;
		if($requestURLPath{0} != '/')return false;
		if(strpos($requestURLPath,'/')===false)return false;
		if(strpos($requestURLPath,'.')===false)return false;
		$pos_start = 1;
		$pos_end = strpos($requestURLPath,'.');
		$cmd = substr($requestURLPath,$pos_start,$pos_end-1);
		$cmd = str_replace('/','.',$cmd);
		return $cmd;
		
	}
	
	/**
	 * 生成签名
	 * @param $paramArr：api参数数组
	 * @return $sign
	 */
	static public function createSign ($paramArr,$cmdid='') {
		ksort($paramArr);
		$sign = $cmdid;
		foreach ($paramArr as $key => $val) {
			if ($key !='' && $val !='') {
				$sign .= $key.$val;
			}
		}
		$sign .= PAIPAI_API_SECRETKEY;
		$sign = md5($sign);
		return $sign;
	}
	
	/**
	 * 生成字符串参数 
	 * @param $paramArr：api参数数组
	 * @return $strParam
	 */
	static public function createStrParam ($paramArr) {
		$strParam = '';
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val !='') {
				$strParam .= $key.'='.urlencode($val).'&';
			}
		}
		return $strParam;
	}
	
	/**
	 * 以GET方式访问api服务
	 * @param $paramArr：api参数数组
	 * @return $result
	 */
	static public function getResult($paramArr,$requestURLPath='') {
		self::instanceSnoopy();
		//组织参数
		$cmdid = self::createCmdid($requestURLPath);
		$sign = self::createSign($paramArr,$cmdid);
		$strParam = self::createStrParam($paramArr);
		$strParam .= 'sign='.$sign;
		$strParam = PAIPAI_API_URL.$requestURLPath.'?'.$strParam;
		//访问服务
		self::$snoopy->fetch($strParam);
		
		//echo "<a href='".$strParam."' style='color:#fff' target='_blank'>点击</a>";
		
		
		
		$result = self::$snoopy->results;
		//返回结果
		return $result;
	}
	
	
	/**
	 * 以POST方式访问api服务
	 * @param $paramArr：api参数数组
	 * @return $result
	 */
	static public function postResult($paramArr,$requestURLPath) {
		self::instanceSnoopy();
		//组织参数，Snoopy类在执行submit函数时，它自动会将参数做urlencode编码，所以这里没有像以get方式访问服务那样对参数数组做urlencode编码
		$cmdid = self::createCmdid($requestURLPath);
		$sign = self::createSign($paramArr,$cmdid);
		$paramArr['sign'] = $sign;
		$api_url = PAIPAI_API_URL . $requestURLPath . "?charset=";
		$api_url .= isset($paramArr['charset'])?$paramArr['charset']:'gbk';
		//访问服务
		self::$snoopy->submit($api_url, $paramArr);
		$result = self::$snoopy->results;
		//返回结果
		return $result;
	}
	
	/**
	 * 以POST方式访问api服务，带图片
	 * @param $paramArr：api参数数组
	 * @param $imageArr：图片的服务器端地址，如array('pic' => '/tmp/cs.jpg')形式
	 * @return $result
	 */
	static public function postImageResult($paramArr,$requestURLPath,$imageArr) {
		self::instanceSnoopy();
		//组织参数
		$cmdid = self::createCmdid($requestURLPath);
		$sign = self::createSign($paramArr,$cmdid);
		$paramArr['sign'] = $sign;
		$api_url = PAIPAI_API_URL . $requestURLPath . "?charset=";
		$api_url .= isset($paramArr['charset'])?$paramArr['charset']:'gbk';
		//访问服务
		self::$snoopy->_submit_type = "multipart/form-data";
		self::$snoopy->submit($api_url,$paramArr,$imageArr);
		$result = self::$snoopy->results;
		//返回结果
		return $result;
	}
	
	/**
	 * 解析xml
	 */
	static public function getXmlData ($strXml) {		
	
		//ADD BY ROGER。解决XML非法字符过滤	
		$strXml = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$strXml);
		
		//全角转半角
		$strXml = SBC_DBC($strXml,1);		
		
		$pos = strpos($strXml, 'xml');
		if ($pos !== false) {
			$xmlCode=simplexml_load_string($strXml,'SimpleXMLElement', LIBXML_NOCDATA);
			$arrayCode=self::get_object_vars_final($xmlCode);
			return $arrayCode ;
		} else {
			return '';
		}
	}
	
	static private function get_object_vars_final($obj){
		if(is_object($obj)){
			$obj=get_object_vars($obj);
		}
			
		if(is_array($obj)){
			foreach ($obj as $key=>$value){
				$v = self::get_object_vars_final($value);
				//ADD BY ROGER。解决空值被转成空数组的问题
				if(is_array($v)&&(count($v)==0))$v=NULL;
				$obj[$key] = $v;
			}
		}
		return $obj;
	}


}
?>