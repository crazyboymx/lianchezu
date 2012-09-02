<?php
class ShareModel extends Model {
	function  _initialize()
	{
	}
	
//获取所有专辑或者分类专辑、、、
	public function getAllShare($uid = null,$cid=null){
		isset($cid) && $map['cid'] = $cid;
		if(is_array($uid)&&!empty($uid)){
			$map['uid'] = array('in',$uid);
		    }elseif(intval($uid)){
			    $map['uid'] = $uid;
		        }
	    $result = $this->where($map)->order('cTime DESC')->
		                 field('id,uid,name,intro,logo,cid,city,ctime,status')->findPage(40);
	    $result['data'] = $this->replace($result['data']);
	    return $result;
	}
//获取某人的专辑
	public function getShareList($uid = null,$limit=null){
		isset($limit) && $limit = $limit;
		isset($uid) && $map['uid'] = $uid;
	    $result = $this->where($map)->order('cTime DESC')->field('id,uid,name,intro,logo,ctime,cid,status')->limit($limit)->select();
        $result=$this->replace($result);
	    return $result;
	}


//获取专辑信息
	public function getShare($gid){
		$map['id'] = $gid;
	    $result = $this->where($map)->find();
		$result['logo'] = './data/uploads/'.$result['logo'];
	    return $result;
	}


//获取专辑名
	public function getShareName($gid){
		$map['id'] = $gid;
	    $result = $this->where($map)->getfield('name');
	    return $result;
	}


//推荐专辑
	public function getRecommend($limit=5){
		$map['status'] = '2';
		isset($limit) && $limit = $limit;
	    $result = $this->where($map)->field('id,uid,name,intro,logo,ctime,cid,status')->limit($limit)->select();
    	foreach($result as &$value){
           $value['logo'] && $value['logo'] = './data/uploads/'.$value['logo'];
    	}
	    return $result;
	}


//获取xihuande
	public function getLoveShare($gid){
		if(is_array($gid)&&!empty($gid)){
			$map['id'] = array('in',$gid);
		}elseif(intval($gid)){
			    $map['id'] = $gid;
		        }
	    $result = $this->where($map)->order('cTime DESC')->
		                 field('id,uid,name,intro,logo,cid,city,ctime,status')->findPage(40);
	    $result['data'] = $this->replace($result['data']);
	    return $result;
	}


    private function replace($data,$w=230){
    	$result = $data;
    	foreach($result as &$value){
           $value['logo'] && $value['logo'] = './data/uploads/'.$value['logo'];
		   $size = GetImageSize($value['logo']);
           $Width = $size[0] < 220? $size[0]:$w;
           $scale = $Width/$size[0];
           $height = (int)($size[1]*$scale);
		   $value['width'] = $Width;
		   $value['height'] = $height;
    	}
    	return $result;
    }


}