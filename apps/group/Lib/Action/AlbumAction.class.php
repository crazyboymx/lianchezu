<?php

class AlbumAction extends BaseAction {
	var $Album;
	public function _initialize(){
		parent::_initialize();
		//系统关闭状态
		if(!$this->groupinfo['openAlbum']) $this->error('群相册关闭状态');   
		$this->Album = D('Album');
		$this->assign('current','album');
		
	}
	//相册列表
	function index() {
		
		$albumList = $this->Album->getAlbumList($html=1,array('gid='.$this->gid));
		
		$this->assign('albumList',$albumList);
		$this->setTitle($this->siteTitle['all_album']);
		$this->display();
	}

	function createAlbum(){
		//判读权限
		//dump($this->config);
		if(!isJoinGroup($this->mid,$this->gid)){
			$this->alert();
		}
		
		$this->display();
	}
	function doCreateAlbum(){
		$name	=	trim(t($_POST['name']));
		if(strlen($name)==0){
			$this->error('相册名称不能为空！');
		}
		$album	=	D('Album');
		$album->cTime	=	time();
		$album->mTime	=	time();
		$album->userId	=	$this->mid;
		$album->gid = $this->gid;
		$album->name	=	$name;
		if($_POST['share']) $album->share	=	1;
		$result	=	$album->add();
		//ajax返回
		if(intval($_POST['ajax'])==1){
			if($result){
				
				//添加动态
				$title_data["actor"] = getUserName($this->mid);
				$title_data['group_name'] = $this->groupinfo['name'];
				$title_data['gid'] = $this->gid;
   				
   				$body_data['title'] = $name;
   				$body_data['gid'] = $this->gid;
   				$body_data['albumId'] = $result;
   				$appid= 'group_'.$this->gid;
				
				$this->api->feed_publish('group_album',$title_data,$body_data,$this->appId,0,$this->gid);
	
				echo json_encode(array('albumId'=>$result,'albumName'=>$name));
			}else{
				echo false;
			}
		}else{
			//普通返回
			if(!$result){
				$this->error('相册创建失败！');
			}else{
				
				$this->assign('jumpUrl',__APP__.'/Album/index/gid/'.$this->gid);
				//$this->success('相册创建成功！');
			}
		}
	}
	
	//编辑相册
	function editAlbum(){
		
		$albumId = intval($_GET['albumId']) > 0 ? intval($_GET['albumId']) : 0;
		
		if(isset($_POST['doSubmit'])){
			
			$name = t($_POST['name']);
			if(empty($name)) exit();
			$ret = $this->Album->setField('name',$name,'id='.$albumId.' AND gid='.$this->gid);
			exit($this->Album->getLastSql());
			if($ret){
				exit(1);
			}else{
				exit(0);
			}
		}
		$albumInfo = $this->Album->where('id='.$albumId)->find();
		if(!$albumInfo) exit(0);
		//判读权限
		if(!($this->isadmin || $albumInfo['userId'] == $this->mid)) $this->error('你没有权限修改');
		$this->assign('albumInfo',$albumInfo);
		$this->assign('albumId',$albumId);
		$this->display();
	}
	
	function delAlbum(){
		$map['id']		=	intval($_REQUEST['id']);
		$map['gid']	=	$this->gid;
		$album			=	D('Album')->field('is_del,userId')->where($map)->find();
		
		//判读权限
		if(!($this->isadmin || $album['userId'] == $this->mid)) $this->error('你没有权限修改');
		
		if($album['is_del']==0){
			$result	=	D('Album')->deleteAlbum($map['id'],$this->gid);
			echo "1";
			
		}else{
			//不存在或已被删除
			echo "-1";
		}
	}
	
	
	
	//相册里图片列表
	function getAlbum() {
		$albumId = intval($_GET['albumId']) > 0 ? intval($_GET['albumId']) : 0;
		//判读存不存在
		$albumInfo = $this->Album->where('id='.$albumId.' AND is_del=0')->find();
		if(!$albumInfo) $this->error('相册不存在');
		$thumbPhoto = D('Photo')->getPhotoList($html=1,array('albumId='.$albumId,'gid='.$this->gid));
		$albumMember = D('Photo')->field('distinct userId')->where('albumId='.$albumId.' AND is_del=0')->findAll();
		
		$this->assign('albumMember',$albumMember);
		$this->assign('album',$albumInfo);
		$this->assign('thumbPhoto',$thumbPhoto);
		$this->setTitle($albumInfo['name']);
		$this->display();
	}


}

?>