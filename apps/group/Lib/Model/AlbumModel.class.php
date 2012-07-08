<?php
	
class AlbumModel extends Model
{
	var $tableName = 'group_album';
	//获取相册
	  /**
      * getGroupList 
    
     */
     public function getAlbumList($html=1,$where = null,$fields=null,$order = null,$limit = null,$isDel=0) {
     	
            //处理where条件
            if(!$isDel)$where[] = 'is_del=0';
            else $where[] = 'is_del=1';
            
   			$where = is_array($where) ? implode(' AND ',$where) : $where ;
            //连贯查询.获得数据集
            $result         = $this->where( $where )->field( $fields )->order( $order )->findPage( $limit ) ;
          
            if($html) return $result;
            return $result['data'];

     }
     
     
     //更新相册照片数量
	function updateAlbumPhotoCount($albumId) {
		$count	=	D('Photo')->where("albumId='$albumId' AND is_del=0")->count();
		$map['photoCount']	=	$count;
		return $this->where("id='$albumId'")->save($map);
	}
	//设置相册封面
	function setAlbumCover($albumId,$cover=0) {
		//插入照片封面
		$cover_info	=	D('Photo')->where("id='$cover'")->find();
		if($cover>0 && $cover_info){
			$map['coverImageId']	=	$cover_info['id'];
			$map['coverImagePath']	=	$cover_info['savepath'];
		}
		$map['mTime']	=	time();
		//更新相册信息
		$result	=	$this->where("id='$albumId'")->save($map);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//通过相册ID 获取照片ID集
	function getPhotoIds($uid,$albumId,$type) {
		$photos	=	$this->getPhotos($uid,$albumId,$type);
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	$v['photoId'];
			}
			return $photoIds;
		}else{
			return false;
		}
	}

	//通过相册ID 获取照片集
	function getPhotos($uid,$albumId,$type,$order='id ASC',$shownum=5) {
		//我的全部照片
		if($type=='mAll'){
			$map['userId']	=	$uid;
		//好友的全部照片
		}elseif($type=='fAll'){
			$api			=	new TS_API();
			$friends		=	$api->friend_get();
			$map['userId']	=	array('in',$friends);
		}else{
		//某个专辑的全部照片
			$map['albumId']	=	$albumId;
			$map['userId']	=	$uid;
		}
		$map['isDel']	=	0;
		$result	=	 D('Photo')->order($order)->where($map)->findAll();
		return $result;
	}

	//删除相册
	function deleteAlbum($aids,$gid,$delFile=false) {
		//解析ID成数组
		if(!is_array($aids)){
			$aids	=	explode(',',$aids);
		}
		
		//标记为已删除
		$map['id']		=	array('in',$aids);
		//$map['gid'] = $gid;
		$save['is_del']	=	1;
		$result	=	$this->where($map)->save($save);

		if($result){
			//同步删除照片及附件
			$album['albumId']	=	array('in',$aids);
			$photos		=	D('Photo')->field('id')->where($album)->findAll();
			
			foreach($photos as $v){
				$photoIds[]	=	$v['id'];
			}

			//处理照片及附件
			if(!empty($photoIds)) $this->deletePhoto($photoIds,$gid,$delFile);

			return true;
		}else{
			return false;
		}
	}

	//删除照片
	function deletePhoto($pids,$gid,$delFile=false) {
		
		if(empty($pids)) return false;
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		

		//标记为已删除
		$map['id']	=	array('in',$pids);
		//$map['gid'] = $gid;
		$save['is_del']	=	1;
		$result	=	D('Photo')->where($map)->save($save);
		
		if($result){

			$photos		=	D('Photo')->where($map)->findAll();
			
			foreach($photos as $v){
				$attachIds[]	=	$v['attachId'];
				//重置相册照片数
				$this->updateAlbumPhotoCount($v['albumId']);
			}
			

			//处理附件
			if(!empty($attachIds))$this->deleteAttach($attachIds);
			return true;
		}else{
			return false;
		}
	}

	//删除附件记录
	function deleteAttach($attachIds,$delFile=false) {
		//解析ID成数组
		if(empty($attachIds)) return false;
		
		if(!is_array($attachIds)){
			$attachIds	=	explode(',',$attachIds);
		}

		$map['id']	=	array('in',$attachIds);
		//在应用中只能标记删除附件，需要在后台进行清理
		if($delFile){
			return false;
		}else{
			$save['isDel']	=	1;
			$result	=	D('Attach')->where($map)->save($save);
			if($result){
				return true;
			}else{
				return false;
			}
		}
	}
	
	//删除相册
	function removeAlbum($ids){
		//解析ID成数组
		if(!is_array($aids)){
			$aids	=	explode(',',$aids);
		}
		
		//标记为已删除
		$map['id']		=	array('in',$aids);
		
		$result	=	$this->where($map)->delete();

		if($result){
			//同步删除照片及附件
			$album['albumId']	=	array('in',$aids);
			$photos		=	D('Photo')->field('id')->where($album)->findAll();

			foreach($photos as $v){
				$photoIds[]	=	$v['id'];
			}

			//处理照片及附件
			if(!empty($photoIds)) $this->removePhoto($photoIds);

			return true;
		}else{
			return false;
		}
	}
	
	//删除照片
	function removePhoto($pids) {
		
		if(empty($pids)) return false;
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		//标记为已删除
		$map['id']	=	array('in',$pids);
		
		$result	=	D('Photo')->where($map)->delete();

		if($result){

			$photos		=	D('Photo')->where($map)->findAll();
			
			foreach($photos as $v){
				$attachIds[]	=	$v['attachId'];
				//重置相册照片数
				$this->updateAlbumPhotoCount($v['albumId']);
			}
			

			//处理附件
			//if(!empty($attachIds))$this->deleteAttach($attachIds);
			return true;
		}else{
			return false;
		}
	}
	
	
	function recoverAlbum($aids){
		if(!is_array($aids)){
			$aids	=	explode(',',$aids);
		}
		
		//标记为已删除
		$map['id']		=	array('in',$aids);
		$save['is_del']	=	0;
		
		$result	=	$this->where($map)->save($save);
		if($result){
			//同步删除照片及附件
			$album['albumId']	=	array('in',$aids);
			$photos		=	D('Photo')->field('id')->where($album)->findAll();
			
			foreach($photos as $v){
				$photoIds[]	=	$v['id'];
			}
		
			//处理照片及附件
			if(!empty($photoIds)) $this->recoverPhoto($photoIds);

			return true;
		}else{
			return false;
		}
	}
	
	function recoverPhoto($pids){
		print_r($pds);
		if(empty($pids)) return false;
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		//标记为已删除
		$map['id']	=	array('in',$pids);
		//$map['gid'] = $gid;
		$save['is_del']	=	0;
		$result	=	D('Photo')->where($map)->save($save);
		
	}
	
	
}