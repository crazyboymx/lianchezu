<?php 
class TopicModel extends Model{
	var $tableName = 'taobaoke_weibo_topic';
	
	//添加话题
	function addTopic( $content ){
		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is",$content,$arr);
		$arr = array_unique($arr[1]);
		foreach($arr as $v){
			$this->addKey($v);
		}
	}

	//添加话题
	private function addKey($key){
		$map['name'] = trim(t(mStr(preg_replace("/#/",'',trim($key)),150,'utf-8',false)));
		if( $this->where($map)->count() ){
			$this->setInc('count',$map);
		}else{
			$map['count'] = 1;
			$map['ctime'] = time();
			return $this->add($map);
		}
	}
	
	/**
	 * 获取给定话题名的话题ID
	 * 
	 * @param string $name 话题名
	 * @return int 话题ID
	 */
	public function getTopicId($name)
	{
		$topic_length = 20;
		$map['name'] = trim(t(mStr(preg_replace("/#/",'',$name), $topic_length, 'utf-8', false)));
		if (empty($map['name']))
			return 0;
		
		$info = $this->where($map)->find();
		if ($info['topic_id']) {
			return $info['topic_id'];
		} else {
			$map['count'] = 0;
			$map['ctime'] = time();
			return $this->add($map);
		}
	}
	
	/**
	 * 获取站点的热门话题
	 * 
	 * @return array 热门话题
	 */
	public function getHot($type = null)
	{
		$hot_list = array();
		$type     = 'recommend' == $type ? 'recommend' : 'auto';
		$cache_id = '_weibo_topic_model_hot_topic_' . $type;
		$expire   = 1 * 3600; // 1 hour
		if (($hot_list = S($cache_id)) === false) {
			if ('recommend' == $type) {
				$hot_list = D('Topics', 'weibo')->getHot();
			} else if ('auto' == $type) {
				$config   = model('Xdata')->lget('weibo');
				$map['count'] = array('gt', '0');
				if ($config['hotTopicTime'] >= 1) {
					$map['ctime'] = array('gt', mktime(0,0,0,date("m"),date("d")+1,date("Y"))-intval($config['hotTopicTime'])*24*3600);
				}
				if ($config['maskHotTopic']) {
					$mask_topics = explode('|', $config['maskHotTopic']);
					$map['name']  = array('not in', $mask_topics);
					$hot_list = $this->where($map)->order('`count` DESC')->limit(10)->findAll();
				}else {
					$hot_list = $this->where($map)->order('`count` DESC')->limit(10)->findAll();
				}
			}
			S($cache_id, $hot_list, $expire);
		}
/*
		$cache_id = '_weibo_topic_model_hot_topic';
		$expire   = 1 * 3600; // 1 hour

		if (($hot_list = S($cache_id)) === false) {
			if ($config['openHotTopic'] && $config['hotTopic']) {
				$map['name'] = array('in', $config['hotTopic']);
				$res = $this->where($map)->limit(count($config['hotTopic']))->findAll();
				
				// 转换为array($name => $count)格式
				foreach ($res as $v) {
					$temp[$v['name']] = $v['count'];
				}
				
				// 组装最后结果
				foreach ($config['hotTopic'] as $k => $v) {
					$hot_list[] = array(
						'name'  => $v,
						'count' => intval($temp[$v]),
						'note'  => $config['hotTopicNote'][$k],
					);
				}
			}else {
				if ($config['maskHotTopic']) {
					$mask_topics = explode('|', $config['maskHotTopic']);
					$map['name']  = array('not in',$mask_topics);
					$map['count'] = array('gt','0');
					$hot_list = $this->where($map)->order('`count` DESC')->limit(10)->findAll();
				}else {
					$hot_list = $this->where('`count` > 0')->order('`count` DESC')->limit(10)->findAll();
				}
			}
			
			S($cache_id, $hot_list, $expire);
		}
*/
		return $hot_list;
	}
	
	//最新话题
	function getNew($num){
		return $this->order('cTime DESC')->limit($num)->findall();
	}
}
