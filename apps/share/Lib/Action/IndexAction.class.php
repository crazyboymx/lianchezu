<?php

class IndexAction extends Action
{
	protected $share;
    public function _initialize(){
	    	
	        $this->icopath = '../Public/images/ico/';
	        $this->assign('icopath',$this->icopath);
	        global $ts;
	        $this->app_alias = $ts['app']['app_alias'];
	    }


    //首页
    public function index()
    {
		$cid=$_GET['cid'];
		$type= M('Sharetype')->select();
		$shareinfo= D('Share')->getAllShare($uid=null,$cid);
		$newshare= D('Share')->getRecommend();
		$this->assign('newshare',$newshare);
		$this->assign($shareinfo);
		$this->assign('type',$type);
        $this->display();
    }

    //我的专辑
    public function someOne()
    {
		$uid=$_GET['uid']? $_GET['uid']:$this->mid;
		$share= D('Share')->getShareList($uid);
		$this->assign('share',$share);
		$newshare= D('Share')->getRecommend();
		$myshar=$this->_createObj();
		$this->assign('myshar',$myshar);
		$this->assign('newshare',$newshare);
        $this->display();
    }

    private function _createObj(){
		
		$arr=D('Pict')->getSharePict($this->mid);
		$shar = array();
		foreach($arr as $mysha){
			$gid = $mysha['gid'];
			$shar[$gid][] = $this->_shareObj($mysha);
		}
		return $shar;
	}


    private function _shareObj($mysha){
		if(is_array($mysha)){
			$shar['id'] = $mysha['id'];
			$shar['gid'] = $mysha['gid'];
			$shar['title'] = $mysha['title'];
			$shar['cover'] = './data/uploads/'.$mysha['cover'];
		}
		else{
			throw new Exception("没有专辑相关");
		}
		return $shar;
	}


    //好友专辑
    public function friend()
    {
	    	$uid=array();
	        $following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
            foreach($following as $v) {
                     $uid[] = $v['fid'];
                 }
	        $friend = D('Share');
            $friend = $friend->getAllShare($uid);
            $this->assign($friend);
            $this->setTitle("好友专辑");
	        $this->display();
    }

    //单页探索
    public function find()
    {
        $picts = D('Pict')->getPictList();
		$this->assign($picts);
		$this->setTitle("单页探索");
        $this->display();
    }




    //整张专辑首页
    public function shareDetail()
    {
		$gid = $_GET['gid'];
		if(!$gid){
			$this->error('专辑不存在');
			}
		$shareinfo= D('Share')->find($gid);
		if(!$shareinfo){
			$this->error('专辑不存在');
			}
        $picts = D('Pict')->getPictList($gid);
		$map['type']='pict';
		$comment = D('Comment')->where($map)->select();
        $this->assign('comment',$comment);
		$this->assign($picts);
		$this->assign('shareinfo',$shareinfo);
        $this->display();
    }
    //专辑单张详细
	public function pictDetail(){
	    $pict = D('Pict');
	    $id = intval($_GET['id']);
	    if($id == 0){
	    	$this->error("错误的信息地址.请检查后再访问");
            exit;
	    }
	    $pictData = $pict->getPict($id);
	    if(!$pictData){
            $this->error("该信息被删除或者不允许查看");
            exit;
        }
	    if($pictData['uid'] == $this->mid){
	    	$this->assign('admin',1);
	    }
		$gid=$_GET['gid'];
		$shareinfo=D('Share')->getShare($gid);
		$samepicts = $pict->getPicts($gid);
		$this->assign('samepicts',$samepicts);
		$this->assign('share',$shareinfo);
	    $this->assign('pict',$pictData);
	    $this->display();
	 }

    public function myLikes(){
    	$sharelike = D('Sharelike');		
    	$uid = $this->mid;
		$like1=$sharelike->getLike($uid,1);
		$like=D('Share')->getLoveShare($like1);
		$this->assign($like);
	    $this->display();

    }

    public function lovePict(){
    	$sharelike = D('Sharelike');		
    	$uid = $this->mid;
		$like2=$sharelike->getLike($uid,2);
		$like=D('Pict')->getLovePict($like2);
		$this->assign($like);
	    $this->display();

    }

		

	public function recommend()
	{
		$types= M('Sharetype')->select();
		$share= D('Share');
		$recom=$share->getRecommend(80);
		$reds=$this->_redObj($recom);
		$this->assign('red',$reds);
		$this->assign('recom',$recom);
		$this->assign('type',$types);
	    $this->display();
	}

    public function _redObj($arra){
		$shr = array();
		foreach($arra as $mys){
			$cid = $mys['cid'];
			$shr[$cid][] = $this->_sdObj($mys);
		}
		return $shr;
	}


    public function _sdObj($mys){
		if(is_array($mys)){
			$sar['id'] = $mys['id'];
			$sar['uid'] = $mys['uid'];
			$sar['name'] = $mys['name'];
			$sar['logo'] = $mys['logo'];
		}
		return $sar;
	}


    //添加喜欢
    public function like(){
		
    	$sharelike = D('Sharelike');		
		$data['appid'] = $_REQUEST['id'];
		$data['type'] = $_REQUEST['type'];
    	$data['uid'] = $this->mid;
		
		$chack=$sharelike->where($data)->find();
		if(empty($chack)){
			$result=$sharelike->add($data);
    	    if($result){
    		   $code = 1;
    	    }else{$code = 0;}
		}else{
			$code = 2;
			}
        echo "$code";
    }



	//专辑的创建
	function add()
	{
		if($_GET['gid']){
	    $title='编辑专辑';
		$share=D('Share')->find($_GET['gid']);
		$this->assign('share',$share);
		}else{$title='创建新专辑';}
	    $sharetype = M('Sharetype')->select();
		$this->assign('biaoti',$title);
	    $this->assign('type',$sharetype);
        $this->setTitle("创建专辑");
		$this->display();
	}

	//做创建操作
	public function doAdd()
	{
		if (trim($_POST['dosubmit'])) {
			$share['city']  = $_POST['city'];
			$share['uid']   = $this->mid;
			$share['name']  = h(t($_POST['name']));
			$share['intro'] = h(t($_POST['intro']));
			$share['cid']   = $_POST['sharenav'];

			if (!$share['name']) {
				$this->error('标题不能为空');
			} else if (get_str_length($_POST['name']) > 20) {
				$this->error('标题不能超过20个字');
			}

			if (get_str_length($_POST['intro']) > 100) {
				$this->error('专辑简介请不要超过100个字');
			}
			if($_FILES['logo']['size'] <1) {
				$this->error('封面不能为空');
			}
			
			$share['ctime'] = time();

			if (1 == $this->config['createAudit']) {
				$share['status'] = 0;
			}
			
		 	$options['userId']		=	$this->mid;
			$options['max_size']    =   2*1024*1024;  //2MB
		    $info	=	X('Xattach')->upload('share_logo',$options);
			if($info['status']) {
				$share['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
			}
			
			
			if($_POST['id']){
				$condition['id']=intval($_POST['id']);
                $result = D('share')->where($condition)->save($share);
			    if($result) {
					$this->assign('jumpUrl',U('share/Index/shareDetail', array('gid'=>$condition['id'])));
					$this->success('更新成功');exit;
				}
		        else {
			        $this->error('更新失败');exit;
			        }
				
			}else{
		              $result = D('share')->add($share);
			          }

			if($result) {
					$this->assign('jumpUrl',U('share/Index/shareDetail', array('gid'=>$result)));
					$this->success('创建成功');
				}
		    else {
			        $this->error('创建失败');
			}
		}
	}
        //发行专辑
	    public function addPict(){
		   $gid = intval($_GET['gid']);
		   if(empty($gid)){
			   $this->error('您没有选择要添加到的专辑');
			   exit;
			   }
		   $sharename=D('Share')->getShareName($gid);
		   $this->assign('sharename',$sharename);
		   $this->assign('gid',$gid);
           $this->setTitle("添加专辑内容");
	       $this->display();
	   }
	   
	   //发行专辑动作
	   public function doAddPict(){
		$map['gid']        = intval($_POST['gid']);
	   	$map['title']      = t($_POST['title']);
        $map['uid']        = $this->mid;
        $map['content']    = h($_POST['content']);
        $map['price']    = t($_POST['price']);
		$map['link']    = t($_POST['link']);
        $map['cTime']      = time();
	            
        // 检查详细介绍
        if (get_str_length($map['content']) <= 0) {
        	$this->error('详细介绍不能为空');
        }

        //得到上传的图片
        $option = array();
        $options['userId'] = $this->mid;
        $cover  =   X('Xattach')->upload('pict_cover',$options);
        $map['cover'] = $cover['status']?$cover['info'][0]['savepath'].$cover['info'][0]['savename']:NULL;
        $dao = D('Pict');
        $rs = $dao->add($map);
        if($rs){
            $this->assign('jumpUrl',U('share/Index/shareDetail',array('gid'=>$map['gid'])));
            $this->success("发布成功,即将跳转到内容页");
        }else{
            $this->error("发布失败");
        }
	   }
        //编辑专辑单页
		public function editPict(){
			$pict = D('Pict');
	    	$id = intval($_GET['id']);
	    	$pictData = $pict->getPict($id,$mid);
	        if(!$pictData){
                $this->error("这个信息被删除或者不允许查看");
                exit;
            }
	    	$this->assign('pict',$pictData);
            $this->display();
			}
		//编辑动作
		public function doEditPict(){
	   	$map['title']      = t($_POST['title']);
        $map['content']    = h($_POST['content']);
        $map['price']    = t($_POST['price']);
		$map['link']    = t($_POST['link']);
	            
        // 检查详细介绍
        if (get_str_length($map['content']) <= 0) {
        	$this->error('详细介绍不能为空');
        }

        $dao = D('Pict');
		$condition['id']=intval($_POST['id']);
        $rs = $dao->where($condition)->save($map);
        if($rs){
            $this->assign('jumpUrl',U('share/Index/pictDetail',array('gid'=>$_POST['gid'],'id'=>$_POST['id'])));
            $this->success("编辑成功,即将跳转到内容页");
        }else{
            $this->error("编辑失败");
        }
		    }
		//删除单页
		public function doDeletePict(){
	    	$id = intval($_POST['id']);
	    	if(0 == $id){
	    		echo -3;
	    	}else{
	    		$pict = D('Pict');
	    		if($res = $pict->deletePict($id,$this->mid)){
					$res=1;
	    		}
	    		echo $res;
	    	}
		}
			
	//删除专辑
	  public function remove(){
		  $id = intval($_POST['id']);
		  if(0 == $id){
			  echo -3;
			  }else{
				$pict = D('Share');
	    		$res = $pict->remove($id);
	    		echo $res;
				  }
			  
		  }
		  
	//删除专辑
	  public function deletes(){
		  $id = intval($_GET['gid']);
		  $map['gid']=$id;
		  $result = D('share')->delete($id);
		  if($result) {
			        $res =D('Pict')->where($map)->delete();
					$this->assign('jumpUrl',U('share/Index/someOne'));
					$this->success('删除成功');
				}
		    else {
			        $this->error('删除失败');
			}
		  }
	    	
}