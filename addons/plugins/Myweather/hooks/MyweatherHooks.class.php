<?php
class MyweatherHooks extends Hooks{
	public function home_index_left_middle(){
		$model = new Model('user_weather');
		$condition['uid'] = $_SESSION['mid'];
		$result = $model->where($condition)->select();
		if(!is_null($result)){
			if($result[0]['cid']){
				$city = (int) $result[0]['cid'];
				$contents = file_get_contents("http://weather.news.qq.com/inc/07_dc{$city}.htm");
				$contents = iconv('gb2312', 'utf-8', $contents);
				$contents = str_replace(array("\r", "\n", "\t"), '', $contents);
				preg_match('/\&#160;(.[^<]*)<\/strong>/i', $contents, $cityname);//获取城市名字
				preg_match_all('/\<table\s*?width\s*?=\s*?\"\s*?175\s*?\".*?\>.*?\<\/table\>/s', $contents, $tbody);
				
				$t = array();
				for($j = 0; $j < 2; $j++){//只显示两天天气
					$t[$j]['now'] = str_replace('175', '100%', $tbody[0][$j]);//替换css样式
				}
				$this->assign('cityname', $cityname[1]);
				$this->assign('weather', $t);
			}
		}
		$this->display('weather');
	}
	public function saveCid(){
		$cid = (int) $_POST['cid'];
		$model = new Model('user_weather');
		$condition['uid'] = $_SESSION['mid'];
		$result = $model->where($condition)->select();
		if(!is_null($result)){
			$data['cid'] = $cid;
			$res = $model->where($condition)->save($data);
			switch ($res){
				case true : echo 1; break;
				case false : echo 0;
			}
		}else{
			$newdata['uid'] = $_SESSION['mid'];
			$newdata['cid'] = $cid;
			$res = $model->add($newdata);
			switch ($res){
				case true : echo 1; break;
				case false : echo 0;
			}
		}
	}
}