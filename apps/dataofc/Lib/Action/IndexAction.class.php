<?php
/*
   2012 
   
   这里的图表的JSON关系必须为散列对应，对于TS的架构没有对微博的数据进行归类记录，所以只能通过计算得出
   若对于百万级的统计，这里会出现很严重的问题，不过仅仅分析了1个月内的数据，1个月百万级不切实际。
   若的确为1月有百万级的数据支撑，可以联系我执行一套解决方案
   初步的解决方案将是在执行此系统前先执行架构升级，将数组阵列入库，需要修改程序与架构上的一切支撑。
   
   谢谢支持，$韭菜饺子$


*/


class IndexAction extends Action {
	// 系统首页
	public function index() {
		$mydata = array();
		$i = 0;
		$mydata = $data = M('weibo') -> where('uid="' . $this -> mid . '"') -> findall();
		$first = M('weibo') -> where('uid="' . $this -> mid . '"') -> order('ctime ASC') -> find();
		$return['count'] = count($mydata);
		foreach($mydata as $key => $weibo) {
			if ($mydata[$key]['isdel'] == 1) {
				$i++;
			} 
			if ($mydata[$key]['transpond_id'] > 0) {
				unset($mydata[$key]);
			} 
			$comment += $mydata[$key]['comment'] + $mydata[$key + 1]['comment'];
		} 
		// first 微博距今时间
		$start_time = time();
		$end_time = $first['ctime'];

		$day = $this -> Cpu_day($start_time, $end_time); 
		// 评论数据
		$return['comment'] = $comment; 
		// 删除数据
		$return['del'] = $i; 
		// 原创微博
		$return['yccount'] = count($mydata); 
		// 平均每天发布
		$return['pjweibo'] = number_format($return['count'] / $day, 2, '.', ' '); 
		// 平均每天被转播
		$return['pjzb'] = number_format(($return['count'] - $return['yccount']) / $day, 2, '.', ' '); 
		// 平均每天原创
		$return['pjyc'] = number_format($return['yccount'] / $day, 2, '.', ' '); 
		// 平均每天被评论
		$return['pjpl'] = number_format($return['comment'] / $day, 2, '.', ' '); 
		// 平均每天被删除
		$return['pjdel'] = number_format($i / $day, 2, '.', ' '); 
		// 遍历时期
		$lastmonth = strtotime("last month");
		$tarday = $this -> Cpu_day($start_time, $lastmonth);
		$today = date('d', time());
		
        //将每天的数量处理成时间对应的散列分布， 格式为30天 [0,0,0,....]，确保时间一一对应
		
		# 散列开始#
		$c = 0;
		for($j = 0, $t = 0;$j < ($tarday + 1);$j++, $t--) {
			if ($j > ($tarday - $today)) {
				$f = (($j + $today) - $tarday);
				$m = date('m');
				$days .= $m . '-' . $f . ',';
			} else {
				$f = (($j + $today));
				$m = date('m')-1;
				$days .= '0' . $m . '-' . $f . ',';
			} 

			$times1 = strtotime("" . ($j - $tarday-1) . " day");
			$times2 = strtotime("" . ($j - $tarday) . " day");
			
			#  通过计算方式得出每天的4种状态  #
			foreach($data as $key => $list) {
				if ($data[$key]['ctime'] > $times1 && $data[$key]['ctime'] < $times2) {
					$h[$j]++;
				} 
				if ($data[$key]['ctime'] > $times1 && $data[$key]['ctime'] < $times2 && $data[$key]['isdel'] == 1) {
					$del[$j]++;
				} 
				if ($data[$key]['ctime'] > $times1 && $data[$key]['ctime'] < $times2 && $data[$key]['transpond_id'] > 0) {
					$zf[$j]++;
				} 
				if ($data[$key]['ctime'] > $times1 && $data[$key]['ctime'] < $times2 && $data[$key]['transpond_id'] == 0) {
					$yc[$j]++;
				} 
				if ($data[$key]['ctime'] > $times1 && $data[$key]['ctime'] < $times2 && $data[$key]['comment'] > 0) {
					$pl[$j] += $data[$key]['comment'] + $data[$key + 1]['comment'];;
				} 
			} 
		} 
		for($ij = 0;$ij < $tarday + 1;$ij++) {
			if ($h[$ij]) $ct[] = $h[$ij];
			else
				$ct[] = 0;
		} 

		for($ij = 0;$ij < $tarday + 1;$ij++) {
			if ($del[$ij]) $dels[] = $del[$ij];
			else
				$dels[] = 0;
		} 
		for($ij = 0;$ij < $tarday + 1;$ij++) {
			if ($yc[$ij]) $ycs[] = $yc[$ij];
			else
				$ycs[] = 0;
		} 
		for($ij = 0;$ij < $tarday + 1;$ij++) {
			if ($zf[$ij]) $zfs[] = $zf[$ij];
			else
				$zfs[] = 0;
		} 
		for($ij = 0;$ij < $tarday + 1;$ij++) {
			if ($pl[$ij]) $pls[] = $pl[$ij];
			else
				$pls[] = 0;
		} 
		$d = substr($days, '0', '-1');
		$tday = explode(',', $d); 
		
		// 导航select
		$return['tab'] = 1;
		if ($_GET['action'] == 'json') {
			echo '{"elements":[
	 {"type":"line","values":' . json_encode($ct) . ',"text":"\u603b\u5e7f\u64ad\u6570","colour":"#73af0b","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u603b\u5e7f\u64ad\u6570 #val#"}},
	 {"type":"line","values":' . json_encode($ycs) . ',"text":"\u539f\u521b\u5fae\u535a\u6570","colour":"#fc7715","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u539f\u521b\u5fae\u535a\u6570 #val#"}},
	 {"type":"line","values":' . json_encode($pls) . ',"text":"\u8bc4\u8bba\u6570","colour":"#2da8d0","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u8bc4\u8bba\u6570 #val#"}},
	 {"type":"line","values":' . json_encode($zfs) . ',"text":"\u8f6c\u53d1\u6570","colour":"#FF0","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u8f6c\u53d1\u6570 #val#"}},
	 {"type":"line","values":' . json_encode($dels) . ',"text":"\u88ab\u5220\u9664\u6570","colour":"#ad67d5","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u88ab\u5220\u9664\u6570 #val#"}}
	 ],';
	        // 取最大值
			rsort($pls, SORT_NUMERIC);
			rsort($zfs, SORT_NUMERIC);
			rsort($ycs, SORT_NUMERIC);
			rsort($dels, SORT_NUMERIC);
			rsort($ct, SORT_NUMERIC);
			$array = array($pls[0], $zfs[0], $ycs[0], $dels[0], $ct[0]);
			rsort($array, SORT_NUMERIC);
			echo '"x_axis":{"labels":
	 {"labels":' . json_encode($tday) . ',"rotate":270},"colour":"#AAAAAA","grid-colour":"#EEEEEE","offset":false},"y_axis":{"min":0,"max":' . $array[0] . ',"steps":' . ceil($array[0] / 10) . ',"colour":"#AAAAAA","grid-colour":"#EEEEEE"},"bg_colour":"#ffffff"}';
		} else {
			$this -> assign($return);
			$this -> display();
		} 
	} 
	public function friend() {
	
	    //降低内存，还是用SQL去处理，不通过计算
		
		
		//总粉丝数
		
		$fans = M('weibo_follow') ->where('fid='.$this->mid.'')->count();
		
		
		//关注数
		
		$idol = M('weibo_follow')->where('uid='.$this->mid.'')->count();
				
		//互相关注数
		$fso= M('') -> query('SELECT COUNT( * ) as count FROM ts_weibo_follow WHERE fid IN (SELECT uid FROM ts_weibo_follow WHERE fid =' . $this -> mid . ') and uid=' . $this -> mid . '');
		
		$return['tab'] = 2;
		if ($_GET['action'] == 'json') {
			echo '{"title":{"text":"\u7c89\u4e1d\u5206\u5e03\u56fe", "style":"font-size: 14px; font-family: Verdana; text-align: center;"}, "legend":{"visible":true, "bg_colour":"#fefefe", "position":"right", "border":true, "shadow":true}, "bg_colour":"#ffffff", "elements":[{"type":"pie", "tip":"#label# #val#<br>#percent#", "values":[{"value":'.$fans.', "label":"\u7c89\u4e1d", "text":"\u7c89\u4e1d"},{"value":'.$idol.', "label":"\u6536\u542c", "text":"\u6536\u542c"},{"value":'.$fso[0]['count'].', "label":"\u76f8\u4e92\u6536\u542c", "text":"\u76f8\u4e92\u6536\u542c"}], "radius":130, "highlight":"alpha", "animate":false, "gradient-fill":true, "alpha":0.5, "no-labels":true, "colours":["#ff0000","#00aa00","#0000ff","#ff9900","#ff00ff"]}]}';
		} else {
		    $return['fans']=$fans;
			$return['idol']=$idol;
			$return['fso']=$fso[0]['count'];
			$this -> assign($return);
			$this -> display();
		} 
	} 
	
	/*
	   统计全站数据，这里暂时未想到好的解决方案，计算方法还算不错，但是在千万级查询面前，也没办法。。。需要系统的架构支撑，但Ts的系统没有这方便的记录，所以计算不切实际。
	   全站数据暂时放在这里吧，与老虾讨论后再确定解决方案
	   
	   
	      * 虽然有笨办法降低负载，但是对于数据库的读取次数>48次，放弃之。
		  
		  * 笨办法而，运行次系统前将所有数据归类散列入库来记录，这个方法讨论再且执行。
	   
	public function admin() {
		$data = M('weibo') -> findall();

		for($i = 1;$i < 13;$i++) {
			$times[$i] = $this -> GetFristAndLastDay(date('Y'), $i);
			foreach($data as $key => $wlist) {
				if ($data[$key]['ctime'] > $times[$i]['F'] && $data[$key]['ctime'] < $times[$i]['L']) {
					$cc[$i]++;
					$pl[$i] += $data[$key]['comment'] + $data[$key + 1]['comment'];
					$del[$i] += $data[$key]['isdel'] + $data[$key + 1]['isdel'];
				} 
				if ($data[$key]['ctime'] > $times[$i]['F'] && $data[$key]['ctime'] < $times[$i]['L'] && $data[$key]['transpond_id'] > 0) {
					$zf[$i]++;
				} else {
					$zf[$i] = 0;
				} 
			} 
		} 

		$return['count'] = $this -> arr_d($cc);
		$return['pl'] = $this -> arr_d($pl);
		$return['zf'] = $this -> arr_d($zf);
		$return['del'] = $this -> arr_d($del);
		foreach($return['count'] as $v) {
			$cct[] = $v;
		} 

		foreach($return['pl'] as $v) {
			$plt[] = $v;
		} 
		foreach($return['zf'] as $v) {
			$zft[] = $v;
		} 
		foreach($return['del'] as $v) {
			$delt[] = $v;
		} 

		if ($_GET['action'] == 'json') {
			echo '{"elements":[ {"type":"line","values":' . json_encode($cct) . ',"text":"\u603b\u5fae\u535a","colour":"#73af0b","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u603b\u5fae\u535a #val#"}}, {"type":"line","values":' . json_encode($plt) . ',"text":"\u603b\u8bc4\u8bba","colour":"#fc7715","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u603b\u8bc4\u8bba #val#"}}, {"type":"line","values":' . json_encode($zft) . ',"text":"\u603b\u8f6c\u53d1","colour":"#2da8d0","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u603b\u8f6c\u53d1 #val#"}}, {"type":"line","values":' . json_encode($delt) . ',"text":"\u603b\u5220\u9664","colour":"#ad67d5","dot-style":{"type":"solid-dot","dot-size":3,"halo-size":0,"tip":"\u603b\u5220\u9664 #val#"}} ],';
			rsort($cc, SORT_NUMERIC);
			rsort($zf, SORT_NUMERIC);
			rsort($pl, SORT_NUMERIC);
			rsort($del, SORT_NUMERIC);
			$array = array($cc[0], $zf[0], $pl[0], $del[0]);
			rsort($array, SORT_NUMERIC);
			echo '"x_axis":{"labels": {"labels":["1","2","3","4","5","6","7","8","9","10","11","12"],"rotate":0},"colour":"#AAAAAA","grid-colour":"#EEEEEE","offset":false},"y_axis":{"min":0,"max":' . $array[0] . ',"steps":1,"colour":"#AAAAAA","grid-colour":"#EEEEEE"},"bg_colour":"#ffffff"}';
		} else {
			$return['tab'] = 3;
			$day = '365';
			$return['counts'] = count($data);
			$return['pjweibo'] = number_format($return['counts'] / $day, 2, '.', ' ');
			foreach($data as $key => $weibo) {
				if ($data[$key]['isdel'] == 1) {
					$i++;
				} 
				if ($data[$key]['transpond_id'] > 0) {
					unset($data[$key]);
				} 
				$comment += $data[$key]['comment'] + $data[$key + 1]['comment'];
			} 
			// 评论数据
			$return['comment'] = $comment; 
			// 原创微博
			$return['yccount'] = count($data); 
			// 平均每天被转播
			$return['pjzb'] = number_format(($return['counts'] - $return['yccount']) / $day, 2, '.', ' '); 
			// 平均每天原创
			$return['pjyc'] = number_format($return['yccount'] / $day, 2, '.', ' '); 
			// 平均每天被评论
			$return['pjpl'] = number_format($return['comment'] / $day, 2, '.', ' '); 
			// 平均每天被删除
			$return['pjdel'] = number_format($i / $day, 2, '.', ' ');
			$this -> assign($return);
			$this -> display();
		} 
	} 
	*/
	// 计算时间差的天数
	private function Cpu_day($star, $end) {
		$day = round(($star - $end) / 3600 / 24) ;
		if ($day == '0') $day = 1;
		return $day;
	} 
	//计算指定月份时间戳最大值和最小值
	private function GetFristAndLastDay($y = "", $m = "") {
		if ($y == "") $y = date("Y");
		if ($m == "") $m = date("m");
		$m = sprintf("%02d", intval($m));
		$y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);

		$m > 12 || $m < 1 ? $m = 1 : $m = $m;
		$firstday = strtotime($y . $m . "01000000");
		$firstdaystr = date("Y-m-01", $firstday);
		$lastday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));
		return array("F" => $firstday, "L" => $lastday);
	} 
  
    //伪装散列
	private function arr_d($arr) {
		for($i = 1;$i < 13;$i++) {
			foreach($arr as $key => $v) {
				$ts[$i] = $arr[$i];
				if (!$arr[$i]) $arr[$i] = 0;
			} 
		} 
		return $ts;
	} 
} 

?>
