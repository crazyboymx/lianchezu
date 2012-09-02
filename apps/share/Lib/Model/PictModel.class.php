<?php
class PictModel extends Model{
	private $api;
	public function _initialize(){
	}
//获取专辑的内容
	public function getPictList($gid = null){
		isset($gid) && $map['gid'] = $gid;
	    $result = $this->where($map)->order('cTime DESC')->field('id,gid,uid,title,cover,content,price,link,cTime')->findPage(40);
	    $result['data'] = $this->replace($result['data']);
	    return $result;
	}
//此为次要内容 累赘  获取5页单页
	public function getPicts($gid){
		$map['gid'] = $gid;
	    $result = $this->where($map)->order('cTime DESC')->limit(5)->select();
	    foreach($result as &$value){
           $value['cover'] && $value['cover'] = './data/uploads/'.$value['cover'];
    	}
	    return $result;
	}
//获取具体单页的详细内容		
	public function getPict($id){
		$map['id'] = $id;
        $result = $this->where($map)->find();
        if(!$result) return false;
        isset($result['cover']) && $result['cover'] = './data/uploads/'.$result['cover'];
		$size = GetImageSize($result['cover']);
        $Width = $size[0] < 300? $size[0]:300;
        $scale = $Width/$size[0];
        $height = (int)($size[1]*$scale);
		$result['width'] = $Width;
		$result['height'] = $height;

        return $result;
	}
//删除专辑单页		
	public function deletePict($id,$mid){
		$pict = $this->where('id='.$id)->find();
		if(!$pict) return -2;
		if($pict['uid'] != $mid) return -1;
		$comment = D('comment','home')->where('appid='.$id)->delete();
		$rs = $this->where('id='.$id)->delete();
		if($rs){
			return 1;
		}else{
			return 0;
		}
	}
	
	
//获取xihuande
	public function getLovePict($id){
		if(is_array($id)&&!empty($id)){
			$map['id'] = array('in',$id);
		}elseif(intval($id)){
			    $map['id'] = $id;
		        }
	    $result = $this->where($map)->order('cTime DESC')->
		                 field('id,gid,uid,title,cover,content,price,link,cTime')->findPage(40);
	    $result['data'] = $this->replace($result['data']);
	    return $result;
	}
	
	
	
	public function getSharePict($uid = null){
		isset($uid) && $map['uid'] = $uid;
	    $result = $this->where($map)->order('cTime DESC')->field('id,gid,title,cover')->select();
	    return $result;
	}
	
	
    private function replace($data){
    	$result = $data;
    	foreach($result as &$value){
           $value['content'] = getBlogShort($value['content'],50);
           $value['cover'] && $value['cover'] = './data/uploads/'.$value['cover'];
		   $size = GetImageSize($value['cover']);
           $Width = $size[0] < 220? $size[0]:230;
           $scale = $Width/$size[0];
           $height = (int)($size[1]*$scale);
		   $value['width'] = $Width;
		   $value['height'] = $height;

    	}
    	return $result;
    }
}