<?php
require_once(SITE_PATH.'/apps/taobaoke/Lib/Model/WeiboModel.class.php');
class OperateModel extends WeiboModel{

    var $tableName = 'taobaoke_weibo';

   function getSavePath(){
        $savePath = SITE_PATH.'/data/uploads/miniblog';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }    
    
    //删除一条微博
    function deleteMini($id,$uid){
    	if( $info = $this->where("weibo_id=$id AND uid=$uid")->find() ){
    		if($info['isdel'] == 0 && $this->setField('isdel',1,'weibo_id='.$info['weibo_id'].' AND isdel=0')){
	    		//关联操作
	    		if( $info['transpond_id'] ){
	    			$this->setDec('transpond','weibo_id='.$info['transpond_id']);
	    		}
	    		//同时删除@用户的微博数据
	    		D('Atme','taobaoke')->where('weibo_id='.$info['weibo_id'])->delete();

	    		//同时删除收藏
	    		D('Favorite','taobaoke')->where('weibo_id='.$info['weibo_id'])->delete();

	    		//同时删除评论
	    		D('Comment','taobaoke')->setField('isdel',1,'weibo_id='.$info['weibo_id']);

	    		//同时更新话题微博数
	    		//preg_match_all('/#(.*)#/isU',$info['content'],$topic_arr);
	    		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is",$info['content'],$topic_arr);
				$topic_arr = array_unique($topic_arr[1]);
				foreach($topic_arr as $v){
					$topic_map['name'] = $v;
					M('weibo_topic')->setDec('count',$topic_map);
				}
    		}elseif($info['isdel'] == 1 && $this->where('weibo_id='.$info['weibo_id'].' AND isdel=1')->delete()){
	    		//同时彻底删除评论
	    		D('Comment','taobaoke')->where('weibo_id='.$info['weibo_id'])->delete();
    		}else{
    			return false;
    		}
    		return true;
    	}else{
    		return false;
    	}
    }
    
	//搜索话题
    function doSearch($key, $type='')
    {
    	global $ts;
    	$key = addslashes(t($key));
    	if (!$key) {
    		$list['count'] = 0;
    		return $list;
    	}
    	// 去除被举报的微博
    	if ($denounce_ids = model('Denounce')->getIdsDenounce('weibo','str'))
    		$map = " AND `weibo_id` NOT IN ( {$denounce_ids} ) ";

    	switch ($type){
    		case '':
    			$list = $this->where("isdel=0 AND (content LIKE '%{$key}%' OR type_data LIKE '%{$key}%')")->order('weibo_id DESC')->findpage(20);
    			break;
    			
    		case 'location':    			
    			$user = M('user')->where('uid='.$ts['user']['uid'])->field('province')->find();
    			$list = $this->where("uid IN (SELECT uid FROM {$this->tablePrefix}user WHERE province=".$user['province'].") AND isdel=0 AND (content LIKE '%{$key}%' OR type_data LIKE '%{$key}%') ")->order('weibo_id DESC')->findpage(20);
    			break;
    			
    		case 'follow':
    			$list = $this->table("{$this->tablePrefix}weibo AS w,(SELECT fid FROM {$this->tablePrefix}weibo_follow WHERE uid={$ts['user']['uid']} AND type=0) AS f")
    						 ->where("w.uid=f.fid AND w.content LIKE '%{$key}%' AND w.isdel=0")
    						 ->order('weibo_id DESC')
    						 ->findpage(20);
    			break;

    		case 'original':
    			$list = $this->where("transpond_id=0 AND content LIKE '%{$key}%' AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;
    			
    		case 'image':
    			$list = $this->where("type=1 AND content LIKE '%{$key}%' AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;
    			
    		case 'music':
    			$list = $this->where("type=4 AND content LIKE '%{$key}%' AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;
    			
    		case 'video':
    			$list = $this->where("type=3 AND content LIKE '%{$key}%' AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;
				
			case 'goods':
    			$list = $this->where("type=5 AND isdel=0 AND (content LIKE '%{$key}%' OR type_data LIKE '%{$key}%')")->order('weibo_id DESC')->findpage(20);
    			break;
			
			default:
    			$list = $this->where("type='{$type}' AND isdel=0 AND (content LIKE '%{$key}%' OR type_data LIKE '%{$key}%')" . $map)->order('weibo_id DESC')->findpage(20);
    	}

    	/*
    	 * 缓存用户信息, 被转发微博的详情
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
		//仿知美二次开发 取消循环
    	//foreach($list['data'] as $key=>$value){
    	//	$list['data'][$key] = $this->getOne('',$value);
    	//}
    	return $list;
    }

	//Topic搜索
	function doSearchTopic($map,$order,$uid) {
		if (!is_string($map))
			return false;
			
		$map .= trim($map)?' AND isdel = 0':'isdel = 0';
		if (model('Denounce')->getIdsDenounce('weibo', 'str')) {
    		$map .= " AND weibo_id NOT IN (" . model('Denounce')->getIdsDenounce('weibo', 'str') . ")";
    	}
    	$maskHotTopic = model('Xdata')->get('weibo:maskHotTopic');
		if( $maskHotTopic ){
			$arr_MaskHotTopic = explode('|', $maskHotTopic);
			foreach($arr_MaskHotTopic as $v){
				$map .= " AND content NOT LIKE '%#{$v}#%' ";
			}	
		}

		$list = $this->where($map)->order($order)->findPage(20);

		/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	
		$weibo_ids = getSubByKey($list['data'], 'weibo_id');
		foreach($list['data'] as $key=>$value){
			$value['is_favorited'] = isfavorited($value['weibo_id'], $uid, $weibo_ids);
			$list['data'][$key] = $this->getOne('', $value);
		}
		return $list;
	}

	//获取未取出来的新微博条数
	function countNew($uid,$lastId){
    	$map="weibo_id>{$lastId} AND isdel=0";
    	$map.=" AND ( uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow WHERE uid=$uid) OR uid=$uid )";
		//获取被举报的微博ID
    	if( model( 'Denounce' )->getIdsDenounce( 'weibo','str' ) ){
    		$map.=" AND weibo_id NOT IN (".model( 'Denounce' )->getIdsDenounce( 'weibo','str' ).")";
    	}
    	$list = $this->where($map)->order('weibo_id DESC')->findAll();
    	return $list;
	}

	function loadNew($uid,$lastId,$limit){
    	$map="weibo_id<={$lastId} AND isdel=0";
    	$map.=" AND ( uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow WHERE uid=$uid) OR uid=$uid )";
		//获取被举报的微博ID
    	if( model( 'Denounce' )->getIdsDenounce( 'weibo','str' ) ){
    		$map.=" AND weibo_id NOT IN (".model( 'Denounce' )->getIdsDenounce( 'weibo','str' ).")";
    	}
    	$list = $this->where($map)->order('weibo_id DESC')->limit($limit)->findAll();
        foreach( $list as $key=>$value){
 			$result[] = $this->getOne('',$value);
        }
        $return['data'] = $result;
        return $return; 
	}

    //获取首页微博列表
    function getHomeList($uid, $type='index', $since, $row=10, $gid='') {
    	$row = $row?$row:20;
		 //--------------仿知美二次开发------------------------
		 //只支持图片
		//$type= 5;
		//--------------仿知美二次开发 end -------------------
		
		$followCount = M('weibo_follow')->where('`uid` = '.$uid.' AND `type` = 0')->count();
    	if($type=='original'){  //原创
			$map = 'transpond_id=0 AND isdel=0';
    		if($since){
    			$map.=" AND weibo_id<$since";
    		}
    	}else if($type=='index' || $type==''){   // 默认全显
    	    if ($since) {
    			$map="weibo_id < $since AND isdel=0";
    		} else {
    			$map = '1=1 AND isdel=0';
    		}
    	}else {
    		if ($since) {
    			$map="weibo_id < $since AND isdel=0";
    		}else {
    			$map = '1=1 AND isdel=0';
    		}
			$map.=" AND type=".$type;
    	}

    	if ($followCount) { // 有关注时, 展示关注的用户的微博
	    	if (is_numeric($gid)) {
	    		$fids = D('FollowGroup','taobaoke')->getUsersByGroup($uid,$gid);
	    		$map.=' AND uid IN ('.implode(',',$fids).')';
	    	}else{
    			$map.=" AND ( uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow WHERE uid=$uid AND type=0) OR uid=$uid)";
	    	}
    	}
		

    	// 去除被举报的微博
    	if ($denounce_ids = model('Denounce')->getIdsDenounce('weibo','str'))
    		$map.=" AND `weibo_id` NOT IN ( {$denounce_ids} ) ";
			
		//新增的推荐	
		$list2['data'] = M('fengmian')->where("`uid`=$uid")->order('`fengid` DESC')->findAll ();
    	$favorite_ids = getSubByKey($list2['data'], 'fengid');
		//$map['bc_id'] = array('in', $favorite_ids);
				
		if($list2['data']){
		$map.=' OR (bc_id IN ('.implode(',',$favorite_ids).') AND isdel = 0) ';
		}
			
		 //--------------仿知美二次开发------------------------ 
		 
		//支持分页
		$listCount= $this->where($map)->count();
    	$list = $this->where($map)->order('weibo_id DESC')->findPage($row, $listCount);
		
		
		//$page= $_GET['page'] ;
		//$qty=0;
		//$list = $this->where($map)->order('weibo_id DESC')->limit($row)->findall();
		//$qty = $this->where($map)->count();
		
	
			//$pagesize=20;
			//$this->assign('page',$page);
			//$this->assign('countpage',ceil($qty/$pagesize));	
		
		
		
		
    	unset($map);

    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	
		$weibo_ids = getSubByKey($list['data'], 'weibo_id');
		//仿知美二次开发 取消循环
        /*foreach( $list['data'] as $key=>$value) {
		
        	$value['is_favorited'] = isfavorited($value['weibo_id'], $uid,$bc_id, $weibo_ids);
 			$list['data'][$key] = $this->getOne('',$value);
        }*/
		
		//解决当没有数据时出现BUG
		if ($listCount){
        return $list;
		}
		
		//--------------仿知美二次开发 end -------------------
		
		
		/*
		
		
		$list = $this->where($map)->order('weibo_id DESC')->limit($row)->findAll();
    	unset($map);

    	
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 
    	$ids = getSubBeKeyArray($list, 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	
        foreach( $list as $key=>$value) {
        	$value['is_favorited'] = isfavorited($value['weibo_id'], $uid, $ids['weibo_id']);
 			$result[] = $this->getOne('',$value);
        }
        $return['data'] = $result;
        
        unset( $result, $list);
        return $return;
		
		*/
		
		
		
		
    }
	
	
       //by 仿知美二次开发 QQ:2451302968
	public function getSpaceList($uid, $type, $bc_id) {
    	if ($type == 'original') { // 原创
    		$map = 'transpond_id=0 AND uid='.$uid.' AND isdel=0 AND bc_id='.$bc_id.'';
    	} else if ($type == '') { // 默认全显
    		$map = "uid=$uid AND isdel=0 AND bc_id=$bc_id";
    	} else {    //其它类型
    		$map = "uid=$uid AND type=".$type.' AND isdel=0 AND bc_id='.$bc_id.'';
    	}
		// 去除被举报的微博
    	if (($denounce_ids = model('Denounce')->getIdsDenounce('weibo','str')))
    		$map.=" AND weibo_id NOT IN ( {$denounce_ids} )";

		$list = $this->where($map)->order('weibo_id DESC')->findPage(20, model('UserCount')->getUserWeiboCount($uid, $type,$bc_id));
		
		/*
    	 * 缓存被转发微博的详情, 被转发微博的作者信息
    	 */
		$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id');
		$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
		$ids['uid'] = getSubByKey($transpond_list, 'uid');
    	D('User', 'home')->setUserObjectCache($ids['uid']);

		$weibo_ids = getSubByKey($list['data'], 'weibo_id');
        foreach( $list['data'] as $key=>$value){
        	$value['is_favorited'] = isfavorited($value['weibo_id'], $uid, $weibo_ids);
 			$list['data'][$key] = $this->getOne('',$value);
        }
        return $list;
    }

    //首页滚动新微博
    function getIndex($num=10){
    	$list = $this->where("transpond_id=0 AND type=0 AND isdel=0")->limit($num)->order('ctime DESC')->findall();
    	return $list;
    }
    
    //提到我的
    function getAtme($uid,$api) {
    	// 手动查询总数, 以提高效率
    	$count_sql = "SELECT count(*) AS count FROM {$this->tablePrefix}weibo AS w INNER JOIN {$this->tablePrefix}weibo_atme AS a ON w.weibo_id = a.weibo_id 
    				  WHERE a.uid = {$uid} 
    				  AND a.uid NOT IN ( SELECT b.fid FROM {$this->tablePrefix}user_blacklist AS b WHERE b.uid = {$uid} )";
    	$count = $this->query($count_sql);
    	$count = $count[0]['count'];
    	
    	$list = $this->where("isdel=0 AND weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_atme WHERE uid=$uid) AND uid NOT IN (SELECT fid FROM {$this->tablePrefix}user_blacklist WHERE uid=$uid)")
    				 ->order('ctime DESC')
    				 ->findPage(10, $count);
    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	//--------------仿知美二次开发------------------------ 
    	//foreach ($list['data'] as $key => $value) {
        //	$value['is_favorited'] = isfavorited($value['weibo_id'], $uid, $ids['weibo_id']);
 		//	$list['data'][$key] = $this->getOneLocation('',$value);
        //}
		//--------------仿知美二次开发 end -------------------
        return $list;
    }

    //我收藏的
    function getCollection($uid,$api){
    	/*
    	$list = $this->where("isdel=0 AND weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)")->order('weibo_id DESC')->findPage(10);
    	*/
    	
    	$list = M('weibo_favorite')->where("`uid`='{$uid}'")->order('`weibo_id` DESC')->findPage(20);
    	$favorite_ids = getSubByKey($list['data'], 'weibo_id');
    	$map['weibo_id'] = array('in', $favorite_ids);
    	$map['isdel']	 = '0';
    	$list['data'] = $this->where($map)->order('`weibo_id` DESC')->limit(count($favorite_ids))->findAll();
    	
    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	//--------------仿知美二次开发------------------------ 
    	//foreach( $list['data'] as $key=>$value){
        //	$value['is_favorited'] = '1';
 		//	$list['data'][$key] = $this->getOneLocation('',$value);
        //}
		//--------------仿知美二次开发 end -------------------
        return $list;
    }
	
	//--------------仿知美二次开发------------------------ 
	function getrecommended($uid,$api){
    	/*
    	$list = $this->where("isdel=0 AND weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)")->order('weibo_id DESC')->findPage(10);
    	*/
    	
    	$list2['data'] = M('fengmian')->where("`uid`='{$uid}'")->order('`fengid` DESC')->findAll();
		//$list = M('fengmian')->where("`uid`='{$uid}'")->order('`fengid` DESC')->findPage(20);
    	$favorite_ids = getSubByKey($list2['data'], 'fengid');
		//$favorite_ids = getSubByKey($list['data'], 'fengid');
		$map['bc_id'] = array('in', $favorite_ids);
    	$map['isdel']	 = '0';
				
		//$list['data'] = $this->where($map)->order('`weibo_id` DESC')->findAll();
		$listCount= $this->where($map)->count();
		$list = $this->where($map)->order('`weibo_id` DESC')->findPage(20, $listCount);
		
    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	//--------------仿知美二次开发------------------------ 
    	//foreach( $list['data'] as $key=>$value){
        //	$value['is_favorited'] = '1';
 		//	$list['data'][$key] = $this->getOneLocation('',$value);
        //}
		//--------------仿知美二次开发 end -------------------
        return $list;
    }
	
	//--------------仿知美二次开发 end -------------------

    //获取手机
    function getMobile($pre,$next,$count=20,$page=1){
    		if($pre){
    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}weibo a LEFT JOIN {$this->tablePrefix}weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 AND a.weibo_id>$pre UNION SELECT * FROM {$this->tablePrefix}weibo WHERE transpond_id=0 AND is_feed=0 AND weibo_id>$pre AND ( type =0 OR type=1) ORDER BY weibo_id ASC  LIMIT $count ");
    			$list = array_reverse($list);
    		}elseif($next){
    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}weibo a LEFT JOIN {$this->tablePrefix}weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 AND a.weibo_id<$next UNION SELECT * FROM {$this->tablePrefix}weibo WHERE transpond_id=0 AND is_feed=0 AND weibo_id<$next AND ( type =0 OR type=1) ORDER BY weibo_id DESC  LIMIT $count ");
    		}else{
    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}weibo a LEFT JOIN {$this->tablePrefix}weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 UNION SELECT * FROM {$this->tablePrefix}weibo WHERE transpond_id=0 AND is_feed=0 AND ( type =0 OR type=1)  ORDER BY weibo_id DESC  LIMIT $count ");
    		}
    	    
    	    foreach($list as $k=>$v){
				$result[$k] = $this->getOneApi('', $v);
	    	}	

    	return $result;
    }
}
