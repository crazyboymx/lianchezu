<?php
class SharelikeModel extends Model {

	
	public function getLike($uid,$type){
		$map['uid']=$uid;
		$map['type']=$type;
		$result=$this->where($map)->select();
		$appid=array();
		foreach($result as &$value){
           $appid[] = $value['appid'];
    	}
		return $appid;
		}

}
?>