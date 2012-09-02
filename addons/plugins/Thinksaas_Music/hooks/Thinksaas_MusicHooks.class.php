<?php
#  部分代码改于 Xiami hooks #

class Thinksaas_MusicHooks extends Hooks
{
	

	public function weibo_js_plugin()  //JSinit
	{
		echo '<script type="text/javascript" src="' . __ROOT__ . '/addons/plugins/Thinksaas_Music/html/common.js"></script>';
		echo '<script>var Plugin_path="' . $this->htmlPath . '";</script>';
		
	}

    public function public_head($param)   //CssInit
    {
       echo '<link href="' . $this->htmlPath . '/html/base.css" rel="stylesheet" type="text/css">';
    }
	
	public function home_index_middle_publish_type()
	{
		$html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.tsas.click(this)' class='a52'><img class='icon_add_tsas' src='%s' />音乐</a>", $this->htmlPath . "/html/zw_img.gif");
		echo $html;
	}

	
	public function weibo_type($param)
	{
		if ($param['typeId'] == '999')
		{
			$res = &$param['result'];
			$data = $param['typeData'];
			$res['type'] = $param['typeId'];
			$data['mp3']=$this->_music_str($data);
			$res['type_data'] = serialize($data);
			
			
		}else{
				return false;
			}
	}
     
	 
	/*
	  
	    $韭菜饺子$
		
	    解析虾米的绝对音乐播放地址 ， 得到可直接播放的mp3格式文件
		
		此解密用于 &copy;xiami.com 中收费的歌曲 以及 移动终端的播放（移动终端不支持xiami flex）
		
		音乐资源取自于虾米，本程序仅作为学习使用，本程序不负责任何法律上的责任
	
	*/ 
    private function _music_str($data){
	       $typeData=$data['song_id'];
		   $math = file_get_contents('http://www.xiami.com/widget/xml-single/uid/8230560/sid/' . $typeData . '');
			//得到加密的MP3地址
			preg_match_all("/\<location\>(.*?)\<\/location\>/", $math, $result);
			$pattern = "/<!\[CDATA\[(.*)\]\]>/isU";
			$string = $result[1][0];
			preg_match($pattern, $string) and $getCdata = preg_replace($pattern, "\\1", $string); 
			// 将虾米进行解密
			$location = $getCdata;
			$loc_2 = (int)substr($location, 0, 1);
			$loc_3 = substr($location, 1);
			$loc_4 = floor(strlen($loc_3) / $loc_2);
			$loc_5 = strlen($loc_3) % $loc_2;
			$loc_6 = array();
			$loc_7 = 0;
			$loc_8 = '';
			$loc_9 = '';
			$loc_10 = '';
			while ($loc_7 < $loc_5)
			{
				$loc_6[$loc_7] = substr($loc_3, ($loc_4 + 1) * $loc_7, $loc_4 + 1);
				$loc_7++;
			}
			$loc_7 = $loc_5;
			while ($loc_7 < $loc_2)
			{
				$loc_6[$loc_7] = substr($loc_3, $loc_4 * ($loc_7 - $loc_5) + ($loc_4 + 1) * $loc_5, $loc_4);
				$loc_7++;
			}
			$loc_7 = 0;
			while ($loc_7 < strlen($loc_6[0]))
			{
				$loc_10 = 0;
				while ($loc_10 < count($loc_6))
				{
					$loc_8 .= $loc_6[$loc_10][$loc_7];
					$loc_10++;
				}
				$loc_7++;
			}
			$loc_9 = str_replace('^', 0, urldecode($loc_8));
			$mp3 = $loc_9;
          
		return $mp3;
		
		 
	  
	} 
	public function weibo_type_parse_tpl($param)
	{
	
	    /*   模板将通过CSS来模拟虾米播放器   */
		
		$type = $param['typeId'];
		$typeData = $param['typeData'];
		$rand = $param['rand'];
		$res = &$param['result'];
		
		if ($type == '999')
		{
			 $this->assign('data',$typeData);
			 $this->assign('rand',$rand);
			 $this->assign('path',$this->htmlPath);
             $res = $this->fetch('Thinksaas_music');
		}
		else
		{
		return false;
		}
	}
}
