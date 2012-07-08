<?php

class PhotoAction extends BaseAction {


	var $Photo;
	public function _initialize(){
		parent::_initialize();
		//系统关闭状态

		if(!$this->groupinfo['openAlbum']) $this->error('群相册关闭状态');

		$this->Photo = D('Photo');
		$this->assign('current','album');
	}

	//图片列表
	function index(){
		$photoList = $this->Photo->getPhotoList($html=1,array('gid='.$this->gid));

		$this->assign('photoList', $photoList);
		$this->setTitle($this->siteTitle['all_photo']);
		
		$this->display();
	}

	//图片显示
	function getPhoto(){
		$albumId = intval($_GET['albumId']) > 0 ? intval($_GET['albumId']) : 0;
		$photoId = intval($_GET['photoId']) > 0 ? intval($_GET['photoId']) : 0;
		$album = D('Album')->where('id='.$albumId.' AND is_del=0')->find();
		$photoInfo = $this->Photo->where('gid='.$this->gid.' AND albumId='.$albumId." AND id=$photoId AND gid={$this->gid} AND is_del=0")->find();
		//验证照片信息是否正确
		if(!$photoInfo){
			$this->error('照片不存在或已被删除！');
		}

		//获取所有照片数据
		$photos	=	$this->Photo->where('gid='.$this->gid.' AND albumId='.$albumId.' AND is_del=0')->findAll();

		$this->assign('photos',$photos);


		//获取上一页 下一页 和 预览图
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	intval($v['id']);
			}
			$photoCount	=	count($photoIds);

			//颠倒数组，取索引
			$pindex		=	array_flip($photoIds);

			//当前位置索引
			$now_index	=	$pindex[$photoId];

			//上一张
			$pre_index	=	$now_index-1;
			if( $now_index <= 0 )	{
				$pre_index	=	$photoCount-1;
			}
			$pre_photo	=	$photos[$pre_index];

			//下一张
			$next_index	=	$now_index+1;
			if( $now_index >= $photoCount-1 ) {
				$next_index	=	0;
			}
			$next_photo	=	$photos[$next_index];

			//预览图的位置索引
			$start_index	=	$now_index - 2;
			if( ($photoCount+1-$now_index)<2){
				$start_index	=	($photoCount+1-5);
			}
			if($start_index<0){
				$start_index	=	0;
			}

			//取出预览图列表 最多5个
			$preview_photos	=	array_slice($photos,$start_index,5);
		}else{
			$this->error('照片列表数据错误！');
		}


		$this->assign('photoCount',$photoCount);
		$this->assign('now',$now_index+1);
		$this->assign('pre',$pre_photo);
		$this->assign('next',$next_photo);
		$this->assign('previews',$preview_photos);

		//unset($pindex);
		//unset($photos);

		//unset($preview_photos);


		$this->assign('album',$album);
		$this->assign('photo',$photoInfo);
		$this->setTitle($album['name'].'相册');
		$this->display();
	}

	function upload(){

		if(!isJoinGroup($this->mid,$this->gid)){
			$this->alert();
		}

		
		if($this->groupinfo['whoUploadPic']==1 && !$this->isadmin) $this->error('仅管理员可以上传图片'); 
		$this->setTitle($this->siteTitle['upload_pic']);
		$this->display();
	}

	function doUpload(){


		$albumId	=	intval($_POST['albumId']);
		if($albumId < 0) exit('选择相册');
		$options['save_photo']['albumId']	=	$albumId;
		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	'jpg,bmp,gif,png';
		$attachInfo	=	$this->api->attach_upload('photo',$options);

		if($attachInfo['status']){
			 $map['albumId'] = $albumId;
			 $map['gid'] = $this->gid;
			 $map['userId'] = $this->mid;
			 $map['cTime'] = time();
			 $map['mTime'] = time();
			 $num = 0;
			 $str = '';

			foreach ($attachInfo['info'] as $k=>$v ){

			 	$map['attachId'] = $v['id'];
			 	$map['name'] = $v['name'];
			 	$map['savepath'] = $v['savepath'].$v['savename'];
			 	$map['size'] = $v['size'];
			 	$ret = $this->Photo->add($map);
			 	//添加首张图片作为默认相册封面
			 	if($this->Photo->where("albumId=$albumId AND gid={$this->gid} AND is_del=0")->count() == 1){

					$result		=	D('Album')->where(" id='$albumId' ")->save(array('coverImageId'=>$ret,'coverImagePath'=>$map['savepath']));
			 		
			 	}
			 	$str .= "<a href='".__APP__."/Photo/getPhoto/gid/{$this->gid}/albumId/$albumId/photoId/".$ret."'>{$v['name']}</a>, ";
				$num++;
				setScore($this->mid,'group_photo_upload');
			}
			
			if($num>0){
			//添加动态
			    //$albumInfo = $this->Album->where('id='.$albumId.' AND is_del=0')->find();
				$title_data["actor"] = getUserName($this->mid);
				$title_data['gid'] = $this->gid;
				$title_data['group_name'] = $this->groupinfo['name'];
				$title_data['num'] = $num;
				
   				//$body_data['url'] = __APP__."/Photo/getPhoto/gid/{$this->gid}/albumId/$albumId/photoId/".$ret;
   				//$body_data['gid'] = $this->gid;
   				//$body_data['albumId'] = $albumId;
   				//$body_data['title'] = $albumInfo['name'];
   				$body_data['url'] = $str;
   				
				
				$this->api->feed_publish('group_photo',$title_data,$body_data,$this->appId,0,$this->gid);
			}
			//启用session记录flash上传的图片数，也可以防止意外提交。
			//$_SESSION['upload_count']	=	count($info['info']);
			  D('Album')->updateAlbumPhotoCount($albumId);




			$this->redirect("/Album/getAlbum/gid/{$this->gid}/albumId/".$albumId);

		}else{
			$this->error('上传出错：'.$info['info']);
		}
	}


	//图片设置成封面

	function setCover(){
		$albumId	=	intval($_POST['albumId']);
		$photoId	=	intval($_POST['photoId']);
		if(!$albumId || !$photoId) exit(0);

		$photo		=	D('Photo')->where("gid = {$this->gid} AND id='$photoId' AND albumId='$albumId' AND is_del=0")->find();

		if($photo){
			$map['coverImageId']	=	intval($photoId);
			$map['coverImagePath']	=	$photo['savepath'];
			$result		=	D('Album')->where(" id='$albumId' ")->save($map);
			if($result){
				//设置成功
				echo "1";
			}else{
				//设置失败
				echo "0";
			}
		}else{
			//该图片不存在
			echo "-1";
		}
	}

	//编辑照片
	public function editPhoto() {
		$map['id']		=	intval($_REQUEST['photoId']);
		$map['albumId']	=	intval($_REQUEST['albumId']);
		$map['gid']	=	$this->gid;
		$photo			=	D('Photo')->where($map)->find();
		if(!$photo){
			echo "错误的相册信息！";
		}
		$this->assign('photo',$photo);
		$this->display();
	}

	//执行照片修改操作
	public function do_update_photo() {
		$id			=	intval($_REQUEST['id']);
		$oldInfo	=	D('Photo')->where("id='$id' AND gid='$this->gid'")->find();
		if(!$oldInfo){
			//只能修改自己的照片
			echo "-1";
		}
		$map['albumId']	=	intval($_REQUEST['albumId']);
		$map['name']	=	t($_REQUEST['name']);
		$result			=	D('Photo')->where("id='$id' AND gid='$this->gid'")->save($map);
		//重置相册照片数
		D('Album')->updateAlbumPhotoCount($map['albumId']);
		D('Album')->updateAlbumPhotoCount($oldInfo['albumId']);
		//ajax返回
		if(intval($_REQUEST['ajax'])==1){
			if($result){
				echo 1;
			}else{
				echo 0;
			}
		}else{
			//普通返回
			if(!$result){
				$this->error('照片编辑失败！');
			}else{
				$this->assign('jumpUrl',__APP__.'/Index/photo/id/'.$id.'/aid/'.$map['albumId'].'/uid/'.$this->mid);
				$this->success('照片编辑成功！');
			}
		}
	}


	//删除照片
	public function delPhoto() {
		$map['id']		=	intval($_POST['id']);
		$map['albumId']	=	intval($_POST['albumId']);
		$map['gid']	=	$this->gid;
		$photo	=	D('Photo')->field('id,is_del')->where($map)->find();

		if($photo && $photo['is_del']==0){
			$result	=	D('Album')->deletePhoto($map['id'],$this->gid);
			if($result){
				//删除成功
				setScore($this->mid,'group_photo_delete');
				echo "1";
			}else{
				//删除失败
				echo "0";
			}
		}else{
			//不存在或已被删除
			echo "-1";
		}
	}
}


?>