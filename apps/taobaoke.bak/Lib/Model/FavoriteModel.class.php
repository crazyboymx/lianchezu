<?php 
class FavoriteModel extends Model
{
    var $tableName = 'taobaoke_weibo_favorite';
    
    public function getList($uid, $since_id, $max_id, $count = 20, $page = 1)
    {
    	$limit = ($page-1)*$count.','.$count;
    	$map['_string'] = "weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)";
		if ($since_id) {
			$map['weibo_id'] = array('gt',$since_id);
		} else if ($max_id) {
			$map['weibo_id'] = array('lt',$max_id);
		}
    	$list = M('taobaoke_weibo')->where($map)->order('weibo_id DESC')->limit($limit)->findAll();
    	
    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list, 'weibo_id,transpond_id,uid');
    	$transpond_list = D('Weibo', 'weibo')->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	
    	$weibo_ids = getSubByKey($list, 'weibo_id');
        foreach( $list as $key => $value) {
        	$value['favorited'] = isfavorited($v['weibo_id'], $uid, $weibo_ids);
 			$list[$key] = D('Weibo','weibo')->getOneApi('', $value);
        }
        return $list;
    }
    
    //收藏微博
    function favWeibo( $id ,$uid ){
    	$data['uid']    = $uid;
    	$data['weibo_id']  = $id;
    	return $this->add($data);
    }
    
    //取消收藏
    function dodelete($id,$uid){
    	return $this->where("weibo_id=$id AND uid=$uid")->delete();
    }
    
    function isFavorite($id, $uid) {
    	$ids = explode(',', $id);
    	if (count($ids) <= 1) {
    		return $this->where("weibo_id=$id AND uid=$uid")->find() ? true : false;
    	}else {
    		$map['weibo_id'] = array('in', $ids);
    		$map['uid']		 = $uid;
    		$res = $this->where($map)->field('weibo_id')->findAll();
    		return getSubByKey($res, 'weibo_id');
    	}
    }
}
?>
