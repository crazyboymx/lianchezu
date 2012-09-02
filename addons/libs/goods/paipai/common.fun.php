<?php
/*通用函数*/

// 函数名：chk_bool_money($c_money)     
// 作 用：检查数据是否是99999.99格式     
// 参 数：$c_money（待检测的数字）     
// 返回值：布尔值     
// 备 注：无     
//----------------------------------------------------------------------------------- 
function is_money($c_money)     
{     
	if (!ereg("^[0-9][.][0-9]$", $c_money)) return false;     
	return true;     
}     
//-----------------------------------------------------------------------------------     
// 函数名：is_email($C_mailaddr)     
// 作 用：判断是否为有效邮件地址     
// 参 数：$C_mailaddr（待检测的邮件地址）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------     
function is_email($c_mailaddr) {
	return strlen($c_mailaddr) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $c_mailaddr);
}


//-----------------------------------------------------------------------------------     
// 函数名：is_url($C_weburl)     
// 作 用：判断是否为有效网址     
// 参 数：$C_weburl（待检测的网址）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------     
function is_url($c_weburl)     
{     
	if (!ereg("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$", $c_weburl))     
	{     
		return false;     
	}     
	return true;     
}  
//-----------------------------------------------------------------------------------     
// 函数名：is_empty($c_char)     
// 作 用：判断字符串是否为空     
// 参 数：$C_char（待检测的字符串）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------     
function is_empty($c_char)     
{     
	if (!is_string($c_char)) return false; //是否是字符串类型     
	if (empty($c_char)) return false; //是否已设定     
	if ($c_char=='') return false; //是否为空     
	return true;     
}     
//-----------------------------------------------------------------------------------     
// 函数名：chk_bool_length($c_char, $i_len1, $i_len2=100)     
// 作 用：判断是否为指定长度内字符串     
// 参 数：$C_char（待检测的字符串）     
// $I_len1 （目标字符串长度的下限）     
// $I_len2 （目标字符串长度的上限）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------     
function chk_bool_length($c_cahr, $i_len1, $i_len2=100)     
{     
	$c_cahr = trim($c_cahr);     
	if (strlen($c_cahr) < $i_len1) return false;     
	if (strlen($c_cahr) > $i_len2) return false;     
	return true;     
}     
//-----------------------------------------------------------------------------------     
// 函数名：is_user($C_user)     
// 作 用：判断是否为合法用户名     
// 参 数：$C_user（待检测的用户名）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------
function is_user($c_user)     
{     
	if (!chk_bool_length($c_user, 4, 20)) return false; //宽度检验     
	if (!ereg("^[_a-zA-Z0-9]*$", $c_user)) return false; //特殊字符检验     
	return true;     
}     
//-----------------------------------------------------------------------------------     
// 函数名：is_phone($c_telephone)     
// 作 用：判断是否为合法电话号码     
// 参 数：$C_telephone（待检测的电话号码）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------     
function is_phone($c_telephone)     
{     
	if (!ereg("^[+]?[0-9]+([xX-][0-9]+)*$", $c_telephone)) return false;     
	return true;     
}     
//-----------------------------------------------------------------------------------  
// 函数名：is_zip($c_post)     
// 作 用：判断是否为合法邮编（固定长度）     
// 参 数：$c_post（待check的邮政编码）     
// 返回值：布尔值     
// 备 注：无     
//-----------------------------------------------------------------------------------
function is_zip($c_post)     
{     
	$c_post=trim($c_post);     
	if (strlen($c_post) == 6)     
	{     
		if(!ereg("^[+]?[_0-9]*$",$c_post))     
		{     
			return true;   
		}
		else     
		{     
			return false;     
		}     
	}
	else     
	{     
		return false;   
	}     
}

//-----------------------------------------------------------------------------------      
// 函数名：ReplaceSpacialChar($C_char)     
// 作 用：特殊字符替换函数     
// 参 数：$C_char（待替换的字符串）     
// 返回值：字符串     
// 备 注：这个函数有问题，需要测试才能使用 
//-----------------------------------------------------------------------------------      
function replace_special_char($c_char)     
{     
	$c_char=HTMLSpecialChars($c_char); //将特殊字元转成 HTML 格式。     
	$c_char=nl2br($c_char); //将回车替换为     
	$c_char=str_replace(" "," ",$c_char); //替换空格为     
	return $c_char;     
}


function is_date($ymd, $sep='-')
{
	if(empty($ymd)) return false;
	list($year, $month, $day) = explode($sep, $ymd);
	return checkdate($month, $day, $year);
}

function is_time($his, $sep=':')
{
	if(empty($his)) return false;
	list($hour, $minute, $second) = explode($sep, $his);
	return (intval($hour)<=24) && (intval($minute)<=59) && (intval($second)<=59);
}



//-----------------------------------------------------------------------------------      
// 函数名：chk_user_login()     
// 作 用：检测用户是否登陆     
// 参 数：     
// 返回值：     
// 备 注： 
//-----------------------------------------------------------------------------------   
function chk_user_login()
{
	$userid = trim($_COOKIE[g_cookies_prefix.'userid']);
	if($userid == '')
	{
		die("<script>window.open('?mod=login&url=".base64_encode($_SERVER['REQUEST_URI'])."','_top');</script>");	
	}
}
//-----------------------------------------------------------------------------------      
// 函数名：user_auto_login()     
// 作 用：用户自动登陆     
// 参 数：     
// 返回值：     
// 备 注： 
//-----------------------------------------------------------------------------------   
function user_auto_login()
{
	$userid = trim($_COOKIE[g_cookies_prefix.'userid']);
	if(is_numeric($userid))
	{
		die("<script>window.open('?mod=customer','_top');</script>");	
	}
}
//-----------------------------------------------------------------------------------      
// 函数名：cut_str()     
// 作 用：文字截取     
// 参 数：     
// 返回值：     
// 备 注： 
//----------------------------------------------------------------------------------- 
function cut_str($string, $length, $dot = ' ...') 
{
	global $charset;

	if(strlen($string) <= $length) {
		return $string;
	}

	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

	$strcut = '';
	if(strtolower($charset) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.$dot;
}
//-----------------------------------------------------------------------------------      
// 函数名：get_fopen()     
// 作 用：远程采集     
// 参 数：     
// 返回值：     
// 备 注： 
//----------------------------------------------------------------------------------- 
function get_fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) 
{
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

//-----------------------------------------------------------------------------------      
// 函数名：get_random()     
// 作 用：随机种子     
// 参 数：     
// 返回值：     
// 备 注： 
//----------------------------------------------------------------------------------- 
function get_random($length, $numeric = 0) 
{
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

//-----------------------------------------------------------------------------------      
// 函数名：filter_htmlspecialchars()     
// 作 用：过滤html标签     
// 参 数：     
// 返回值：     
// 备 注： 
//----------------------------------------------------------------------------------- 
function filter_htmlspecialchars($string) 
{
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		//$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function read_from_file($file_name) 
{ //File Reading 
    if (file_exists($file_name)) { 
        if (PHP_VERSION >= "4.3.0") return file_get_contents($file_name); 
        $filenum=fopen($file_name,"r"); 
        $sizeofit=filesize($file_name); 
        if ($sizeofit<=0) return ''; 
        @flock($filenum,LOCK_EX); 
        $file_data=fread($filenum, $sizeofit); 
        fclose($filenum); 
        return $file_data; 
    } else return ''; 
}

//$mode 为 r,r+,w,w+,a,a+ 

function write_to_file($filename, $data ,$mode='w')
{ //File Writing 
    $filenum=@fopen($filename,$mode); 
    if (!$filenum) { 
        return false; 
    } 
    flock($filenum,LOCK_EX); 
    $file_data=fwrite($filenum,$data); 
    fclose($filenum); 
    return true; 
}

//创建文件夹   
  
function create_folders($path)   
{   
  
    //递归创建   
  
    if (!file_exists($path))//如果文件夹不存在   
    {   
  
        create_folders(dirname($path));//取得最后一个文件夹的全路径返回开始的地方   
  
        mkdir($path, 0777);   
  
    }   
  
}   
  
//创建并写文件   
  
function create_file($filename,$content)     
{   
  
    create_folders($filename);//创建文件夹   
  
    file_put_contents($filename,$content);//写文件   
  
    chmod($filename,0777);     
  
}   
//文件下载
function file_down($filepath, $filename = '')
{
	if(!$filename) $filename = basename($filepath);
	if(is_ie()) $filename = rawurlencode($filename);
	$filetype = fileext($filename);
	$filesize = sprintf("%u", filesize($filepath));
	if(ob_get_length() !== false) @ob_end_clean();
	header('Pragma: public');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: pre-check=0, post-check=0, max-age=0');
	header('Content-Transfer-Encoding: binary');
	header('Content-Encoding: none');
	header('Content-type: '.$filetype);
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-length: '.$filesize);
	readfile($filepath);
	exit;
}

//处理post字符串，过滤反斜杠
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value))
   {
       $value = mysql_escape_string($value);
   }
   return $value;
}

//处理接口参数
//@return a=a&b=b&c=c
function build_param($array)
{
	if(!is_array($array))return false;
	foreach($array as $k => $v)
	{
		$param .= $k.g_api_param_equal_split.$v.g_api_param_and_split;
	}
	return substr($param,0,-strlen(g_api_param_and_split));
}

//解析接口参数
//@return array('a'=>'a','b'=>'b')
function parse_param($param)
{
	//先解码
	
	//再&号分割
	$arr = explode(g_api_param_and_split,$param);
	//再=号分割
	for($i=0;$i<count($arr);$i++)
	{
		$arr_tmp_i = explode(g_api_param_equal_split,$arr[$i]);
		$array[$arr_tmp_i[0]] = $arr_tmp_i[1];
	}
	
	return $array;
	
	
}

/* 
* 用处 ：此函数用来逆转javascript的escape函数编码后的字符。
* 参数：javascript编码过的字符串。
*/

if(!function_exists('js_unescape')){
function js_unescape($str) 
{ 
        $ret = ''; 
        $len = strlen($str); 

        for ($i = 0; $i < $len; $i++) 
        { 
                if ($str[$i] == '%' && $str[$i+1] == 'u') 
                { 
                        $val = hexdec(substr($str, $i+2, 4)); 

                        if ($val < 0x7f) $ret .= chr($val); 
                        else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f)); 
                        else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f)); 

                        $i += 5; 
                } 
                else if ($str[$i] == '%') 
                { 
                        $ret .= urldecode(substr($str, $i, 3)); 
                        $i += 2; 
                } 
                else $ret .= $str[$i]; 
        } 
        return $ret; 
}
}
function HtmlEncode($fString){
	if($fString!=""){
		$fString = str_replace( '>', '&gt;',$fString);
		$fString = str_replace( '<', '&lt;',$fString);
		$fString = str_replace( chr(32), '&nbsp;',$fString);
		$fString = str_replace( chr(13), ' ',$fString);
		$fString = str_replace( chr(10) & chr(10), '<br>',$fString);
		$fString = str_replace( chr(10), '<BR>',$fString);
	}
	return $fString;
}

function EncodeHtml($fString){
	if($fString!=""){
		$fString = str_replace("&gt;" , ">", $fString);
		$fString = str_replace("&lt;", "<", $fString);
		$fString = str_replace("&nbsp;",chr(32),$fString);
		$fString = str_replace("",chr(13),$fString);
		$fString = str_replace("<br>",chr(10) & chr(10),$fString);
		$fString = str_replace("<BR>",chr(10),$fString);
	}
	return $fString;
}


function SBC_DBC($str,$args2) { //半角和全角转换函数，第二个参数如果是0,则是半角到全角；如果是1，则是全角到半角
    $DBC = array( 
        '０' , '１' , '２' , '３' , '４' ,  
        '５' , '６' , '７' , '８' , '９' , 
        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,  
        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' , 
        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,  
        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' , 
        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,  
        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' , 
        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,  
        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' , 
        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,  
        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' , 
        'ｙ' , 'ｚ' , '－' , '　'  , '：' ,
  '．' , '，' , '／' , '％' , '＃' ,
  '！' , '＠' , '＆' , '（' , '）' ,
  '＜' , '＞' , '＂' , '＇' , '？' ,
  '［' , '］' , '｛' , '｝' , '＼' ,
  '｜' , '＋' , '＝' , '＿' , '＾' ,
  '￥' , '￣' , '｀'
    );
  $SBC = array( //半角
         '0', '1', '2', '3', '4',  
         '5', '6', '7', '8', '9', 
         'A', 'B', 'C', 'D', 'E',  
         'F', 'G', 'H', 'I', 'J', 
         'K', 'L', 'M', 'N', 'O',  
         'P', 'Q', 'R', 'S', 'T', 
         'U', 'V', 'W', 'X', 'Y',  
         'Z', 'a', 'b', 'c', 'd', 
         'e', 'f', 'g', 'h', 'i',  
         'j', 'k', 'l', 'm', 'n', 
         'o', 'p', 'q', 'r', 's',  
         't', 'u', 'v', 'w', 'x', 
         'y', 'z', '-', ' ', ':',
   '.', ',', '/', '%', '#',
   '!', '@', '&', '(', ')',
   '<', '>', '"', '\'','?',
   '[', ']', '{', '}', '\\',
   '|', '+', '=', '_', '^',
   '$', '~', '`'
    );
if($args2==0) 
   return str_replace($SBC,$DBC,$str);  //半角到全角
if($args2==1)
   return str_replace($DBC,$SBC,$str);  //全角到半角
else
   return false;
} 

function cut_name($str,$start,$len){
	if($str) {
		$str = urlencode($str);
		if(strpos($str,'%') !==false)
		{
			$start = $start * 9;
			$len = $len * 9;
		}
		if($len) {
			$str = substr($str,$start,$len);
		}else{
			$str = substr($str,$start);	
		}
		return urldecode($str);
	}
}

//产生随机密码。
//$pw_length : 密码长度
function domake_password($pw_length)
{
	$low_ascii_bound=48;
	$upper_ascii_bound=57;
	$notuse=array(58,59,60,61,62,63,64,73,79,91,92,93,94,95,96,108,111);
	
	while($i<$pw_length){
		mt_srand((double)microtime()*1000000);
		$randnum=mt_rand($low_ascii_bound,$upper_ascii_bound);
		if(!in_array($randnum,$notuse)){
			 if ($i==0 && $randnum==0) {
				  $i=0;
				  $password1='';
				  domake_password(4);
			 }
			 $password1=$password1.chr($randnum);
			 $i++;
		}
	}
	return $password1;
}

/*
 * 将Excel日期格式转换成 系统正常日期格式
*/

function parse_excel_date($days, $time=false){
	if(is_numeric($days)){
		//based on 1900-1-1 by excel
		$jd = GregorianToJD(1, 1, 1970);//将格利高里历法转换成为儒略日计数
		$gregorian = JDToGregorian($jd+(intval($days)-25569));
		$myDate = explode('/',$gregorian);
		$myDateStr = str_pad($myDate[2],4,'0', STR_PAD_LEFT)
				."-".str_pad($myDate[0],2,'0', STR_PAD_LEFT)
				."-".str_pad($myDate[1],2,'0', STR_PAD_LEFT)
				.($time?" 00:00:00":'');
		return $myDateStr;
	}
	return $days;
}



//XML标准规定的无效字节为：0x00 - 0x08    0x0b - 0x0c    0x0e - 0x1f 
function xml_safe_str($s)
{
	return preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$s); 
} 


/*
 * 
*/

function get_owner()
{
	$debug = 1;
	if($debug == 1)
	{
		return $_COOKIE[g_cookies_prefix."owner"];
	}
	else
	{
		return g_test_owner;
	}
}

/*
 * 
*/
function chk_sign_in()
{
	$debug = 1;
	if($debug == 1)
	{
		if(trim($_COOKIE[g_cookies_prefix."owner"]) == '')
		{
			//检测是否签入
			die("<script>alert('您未签入,请先登录');top.location='msg.php?n=1';</script>");
		}

	}
}

?>