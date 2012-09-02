<?php

class IndexAction extends Action
{
    public function _initialize(){
		
	} 
		
//时光轴首页
    public function index()
    {
		$_GET['uid']!=''? $map['uid']=$_GET['uid']:$map['uid']=$this->uid;
		$userinfo=$this->_linehead($map);
		$weibos=$this-> _getweibo(3);
		$blogs=$this->_getblog();
		$photos=$this->_getphoto();
		$datas=$this->_hebing($blogs,$photos);
		foreach ($datas as $key => $row) {
            $volume[$key] = $row['cTime'];
        }
		array_multisort($volume, SORT_DESC, $datas);
		$times=$this->_dates($datas);
		$datas=$this->_createObj($datas);
		$this->assign('time',$times);
		$this->assign('datas',$datas);
		$this->assign('weibo',$weibos);
		$this->assign('userinfo',$userinfo);
        $this->display();
    }
//封面皮肤页面
    public function sign(){
		$map['uid']=$this->uid;
	    $sign=$this->_getsign();
		$userinfo=$this->_linehead($map);
		$this->assign('userinfo',$userinfo);
		$this->assign('sign',$sign);
        $this->display();
	}
//关注的人	
	public function friline(){
		$_GET['uid']!=''? $map['uid']=$_GET['uid']:$map['uid']=$this->uid;
		$userinfo=$this->_linehead($map);
		
		$conditon['uid']=$map['uid'];
		$conditon['type']=0;
	    $following = M('weibo_follow')->field('fid')->where($conditon)->findAll();

		$uids=array();
		$comfol=array();
        foreach($following as $v) {
			$fid=$v['fid'];
			$uids[]=$v['fid'];
			$comfol[$fid][]=$this->_getcomfri($fid);
        }
		$this->assign('com',$comfol);
		$this->assign('fid',$uids);
		$this->assign('userinfo',$userinfo);
        $this->display();

	}
//头部信息
	private function _linehead($map){
		$line = D('timeline')->where($map)->find();
		$linesign=$this->_getsignid($line['sign']);
		$userinfo=D('User')->where($map)->field('uid,uname,sex,location')->find();
		$userinfo['sex']==0?$userinfo['sex']='女':$userinfo['sex']='男';
		$map['type']=0;
		$following = M('weibo_follow')->field('fid')->where($map)->findAll();
		$fcount=count($following);
		$userinfo['fcount']=$fcount;
        $userinfo['lsign']=$linesign;
		return $userinfo;
	}
//获取所有封面	
	private function _getsign(){
		$sign=D('Timesign');
		$signs=$sign->select();
		return $signs;

	}
//获取某封面	
	private function _getsignid($id){
		$map['id']=$id;
		$sign=D('Timesign');
		$signs=$sign->where($map)->find();
		return $signs;

	}
//更换封面
    public function chosign(){
		$timeline=D('timeline');
		$map['uid']=$this->uid;
		$lines=$timeline->where($map)->find();
		$data['sign']=$_POST['id'];
		$data['uid']=$this->mid;
		if(empty($lines)){
		   $res=$timeline->add($data);
		}else{
			$res=$timeline->where($map)->save($data); 
			}
		if($res){
			echo 1;
			}else{echo 0;}
    }
//创建更适合使用的数据格式
    private function _createObj($arr){
		$events = array();
		foreach($arr as $event){
			$day = date('Y,m',$event['cTime']);
			$events[$day][] = $this->_eventObj($event);
		}
		return $events;
	}


    private function _eventObj($event){
		if(is_array($event)){
			$ev['id'] = $event['id'];
			$ev['title'] = $event['title'];
			$ev['type'] = $event['type'];
			$ev['content'] = $event['content'];
			$ev['savepath'] = $event['savepath'];
			$ev['cTime'] = $event['cTime'];
		}
		else{
			throw new Exception("没有动态");
		}
		return $ev;
	}

//合并数据
    private function _hebing($blogs,$weibos,$photos)
    {
		if($blogs && $weibos && $photos){$datas=array_merge($blogs,$weibos,$photos);}
		elseif($blogs && $weibos && !$photos){$datas=array_merge($blogs,$weibos);}
		elseif($blogs && !$weibos && $photos){$datas=array_merge($blogs,$photos);}
		elseif(!$blogs && $weibos && $photos){$datas=array_merge($weibos,$photos);}
		elseif($blogs && !$weibos && !$photos){$datas=$blogs;}
		elseif(!$blogs && $weibos && !$photos){$datas=$weibos;}
		else{$datas=$photos;}
		return $datas;
    }
//获取日期
    private function _dates($data){
    	$times = array();
    	foreach($data as $value){
			$times[]= date('Y,m',$value['cTime']);
    	}
    	return array_unique($times);
    }

//获取日志
    public function _getblog()
    {
		$blog=D('Blog');
		$map['uid']=$this->uid;
		$map['status']='1';
		$blogs=$blog->where($map)->field('id,title,content,cTime')->select();
		$blogs=$this->replace($blogs);
		return $blogs;
    }
//获取微博
    public function _getweibo($limit=NULL)
    {
		isset($limit)&&$limit=$limit;
		$weibo=D('Weibo');
		$map['uid']=$this->uid;
		$map['isdel']=0;
		$weibos=$weibo->where($map)->field('weibo_id,content,ctime')->limit($limit)->select();
		$weibos=$this->replace($weibos);
		return $weibos;
    }

//获取图片信息
    public function _getphoto()
    {
		$photo=D('Photo');
		$map['userId']=$this->uid;
		$photos=$photo->where($map)->field('id,name,cTime,savepath')->select();
		$photos=$this->replace($photos);
		return $photos;
    }


	//编辑图片
	public function edit_photo_tab() {
		$map['id']		=	intval($_REQUEST['id']);
		$map['userId']	=	$this->mid;
		$map['isDel']	=	0;
		$photo			=	D('Photo')->where($map)->field('id,name')->find();
		if(!$photo){
			echo "错误的相册信息！";
		}
		$this->assign('photo',$photo);
		$this->display();
	}
	//执行图片修改操作
	public function do_update_photo() {
		$id		        =	intval($_REQUEST['id']);
		$map['name']	=	t($_REQUEST['name']);
		$photoDao       =   D('Photo');
		$result			=	$photoDao->where("id={$id} AND userId={$this->mid}")->save($map);
		if($result){
                    echo 1;exit;
		}else{
			        echo 0;exit;
		}
	}
//获取共同关注的人
	public function _getcomfri($uid){
		$follow = M('weibo_follow')->field('fid')->where("uid={$uid} AND type=0")->findAll();
		$foid=array();
	    foreach($follow as $v) {
		    $foid[]=$v['fid'];
        }
	    $myfollow = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
		$myid=array();
	    foreach($myfollow as $my) {
		    $myid[]=$my['fid'];
        }
        $comfol=array_intersect($foid,$myid);//取得数据交集

		return $comfol;
	}




    private function replace($data){
    	$result = $data;
    	foreach($result as &$value){
		   if($value['content']){
		   $value['content'] = htmlspecialchars_decode($value['content']);
		   }else{
			   $value['content'] = '';
			   }
		   if($value['savepath']){
              $value['savepath'] = './data/uploads/'.$value['savepath'];
			  $value['type'] = '照片';
		   }else{
			   $value['savepath'] = '';
			   }
		   if($value['name']){	
		   $value['title'] = $value['name'];
		   }elseif($value['title']){
			   $value['title'] = $value['title'];
			   $value['type'] = '日志';
			   }else{
				   $value['title'] = '';
				   }		   
		   if($value['ctime']){
              $value['cTime'] = $value['ctime'];
		   }else{
			   $value['cTime'] = $value['cTime'];
			   }
		   if($value['weibo_id']){
              $value['id'] = $value['weibo_id'];
			  $value['type'] = '微博';
		   }else{
			   $value['id'] = $value['id'];
			   }
    	}
    	return $result;
    }


}