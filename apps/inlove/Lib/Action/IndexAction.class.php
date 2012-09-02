<?php
    /**
     * IndexAction 
     * 恋爱通告
     */
class IndexAction extends Action {
       public function _initialize() { 
	   		$app_id=M('app')->where('app_name="inlove"')->getField('app_id');
			$appcount['user']=M('user_app')->where("app_id=".$app_id)->count(); // 应用的使用人数
			$appcount['inlove']=M('inlove')->count(); // 用户告白数量
			$inlove = model('Xdata')->lget('inlove');
			$inloveid = $inlove['inloveid'];
			$appcount['tucao']=model('UserCount')->getUserWeiboCount($inloveid)-$appcount['inlove'];
			$from_uids=array_unique(getSubByKey( M('inlove')->order('ctime desc')->select(),'from_uid'));
			if(count($from_uids)>16){
				for($i=0;$i<16;$i++){
					$from_uids[]=$from_uids[$i];
				}
	   		}
			$appcount['followstate'] = D('Follow','weibo')->getState($this->mid, $inloveid, 0);
			$appcount['isBlackList'] = isBlackList($this->mid,$inloveid);
			$this->assign('inloveid',$inloveid);
			$this->assign('appcount',$appcount);
			$this->assign('from_uids',$from_uids);
	    }
		// 通告大厅
	   public function index(){
			$inlove = model('Xdata')->lget('inlove');
			$inloveid = $inlove['inloveid'];
		    $list = D ('Weibo')->where("uid=$inloveid AND isdel=0")->order('weibo_id DESC')->findpage(20);
				foreach($list['data'] as $key=>$value){
    				$list['data'][$key] = D('Weibo','weibo')->getOne('',$value);
					if(count(getUids($value['content']))>0){
						$list['data'][$key]['users']=1;//是吐槽还是告白
					}
    			}
			$inlove = model('Xdata')->lget('inlove');
			$this->assign('list',$list);
			$this->setTitle("通告大厅");
			$this->display();
			
	   }
		   // my 告白
		public function frommy() {
			$islove=$this->islove();
			$dao = D('Inlove');
			$list = $dao->getInloveListByUid($this->mid, array(1, 2),1,$islove);
			$this->assign($list);
			// 设置告白私聊为已读
			model('Inlove')->setInloveIsRead(null, $this->mid);
			$this->setTitle("我的勇敢告白");
			$this->display('list');
		}
		 // TO my 告白
		public function tomy() {
			$islove=$this->islove();
			$dao = D('Inlove');
			$list = $dao->getInloveListByUid($this->mid, array(1, 2),2,$islove);
			$this->assign($list);
			// 设置告白私聊为已读
			model('Inlove')->setInloveIsRead(null, $this->mid);
			$this->setTitle("我收到的告白");
			$this->display('list');
		}
		// 成功的告白
		public function inlove() {
			$islove=$this->islove();
			$dao = D('Inlove');
			$list = $dao->getInloveListByUid($this->mid, array(1, 2),'no',$islove);
			$this->assign($list);
			// 设置告白私聊为已读
			model('Inlove')->setInloveIsRead(null, $this->mid);
			$this->setTitle("成功告白");
			$this->display('list');
		}
		// 告白详情
		public function detail()
		{
			$inlove = D('Inlove')->isMember($_GET['id'], $this->mid, true);
			if (empty($inlove))
				$this->error(L('inlove_notexist'));
	
			$inlove['member'] = D('Inlove')->getInloveMembers($_GET['id'], 'member_uid');
	
			$inlove['to']     = array();
	
			foreach($inlove['member'] as $v){
				$this->mid != $v['member_uid'] && $inlove['to'][] = $v;
			}
			$this->assign('inlove', $inlove);
	
			$this->assign('type', intval($_GET['type']));
			$this->assign('acname',$_GET['acname']);
			$this->setTitle("对话详情");
			$this->display();
		}
		//加载对话
		public function loadInlove()
		{
			$inlove = D('Inlove')->getInloveByListId($_POST['list_id'], $this->mid, $_POST['since_id'], $_POST['max_id']);
			$this->assign('inlove', $inlove);
			$this->assign('type', intval($_POST['type']));
			$this->assign('acname',$_POST['acname']);
			$inlove['data'] = $inlove['data'] ? $this->fetch() : null;
			echo json_encode($inlove);
		}
		//发送对话
		public function doReply() {
			if ( !$_POST['id'] || empty($_POST['reply_content']) ) {
				echo 0;
				exit;
			}
		  $res =D('Inlove')->replyInlove( intval($_POST['id']), t($_POST['reply_content']), $this->mid );
		  $list_map['list_id']=intval($_POST['id']);
		  $listR=M('inlove_list')->where($list_map)->find();
		  $min_max_uids = explode('_', $listR['min_max']);
			// 去除当前用户UID
		  $to_uid=array_values(array_diff($min_max_uids, array($listR['from_uid'])));
		  $listR['to_uid']=$to_uid['0'];
		  //我是否对TA表白
		  $islove['from_uid']=$listR['from_uid'];
		  $islove['to_uid']=$listR['to_uid'];
		  $resR[]=M('inlove')->where($islove)->count();
		  //TA是否对我表白
		  $islove['from_uid']=$listR['to_uid'];
		  $islove['to_uid']=$listR['from_uid'];
		  $resR[]=M('inlove')->where($islove)->count();
		  $inlove = model('Xdata')->lget('inlove');
		  if($resR[0]&&$resR[1]){ //表白成功
				$data1['url']     =  U('//inlove');
				$to_mid=$listR['from_uid']==$this->mid?$this->mid:$listR['to_uid']; 
				$to_uid=$listR['from_uid']==$this->mid?$listR['to_uid']:$listR['from_uid'];
				$username1=getUserName($to_mid);
				$userurl1= U('home/Space/index', array('uid' => $to_mid));
				$data1['title']   = "<a href=\"{$userurl1}\" target=\"_blank\">@{$username1}</a>";  
				X('Notify')->send($to_uid,'inlove_toonew',$data1,$inlove['inloveid']);
		  }else{
				$data['url']=$listR['from_uid']==$this->mid? U('//tomy') : U('//frommy'); 
				$to_mid=$listR['from_uid']==$this->mid?$this->mid:$listR['to_uid']; 
				$to_uid=$listR['from_uid']==$this->mid?$listR['to_uid']:$listR['from_uid'];
				$to_type=$listR['from_uid']==$this->mid?'inlove_isyesfrom':'inlove_isyes';
				if($listR['from_uid']!=$this->mid){
					$username1=getUserName($to_mid);
					$userurl1= U('home/Space/index', array('uid' => $to_mid));
					$data['title']   = "<a href=\"{$userurl1}\" target=\"_blank\">@{$username1}</a>";  
				}
				X('Notify')->send($to_uid,$to_type,$data,$inlove['inloveid']);
		  }
			if ($res) {
				echo 1;
			}else {
				echo 0;
			}
		}
		//恋爱通告发布
	  public  function publish(){
			$pWeibo = D('Weibo','weibo');
			$type = intval( $_POST['publish_type']);
			$data['content'] =  $_POST['content'];
			$type_data = $_POST['publish_type_data'];
			$inlove = model('Xdata')->lget('inlove');
			if(!empty($_SESSION['inloveweiboid'][$this->mid])){
				$map['weibo_id'] = $_SESSION['inloveweiboid'][$this->mid];
				$inloveaddtime = D('Weibo')->where($map)->order('weibo_id DESC')->find();
			}
			$from_data['source']="恋爱通告";
			$from_data['url']=U('inlove/Index/index');
			$from_data=serialize($from_data);
			$nowtime = time();
			$restime = $nowtime-$inloveaddtime['ctime'];
			$timewait = $inlove['timewait'];	//后台定义的等待时间
			if(in_array($this->mid,getUids($data['content']))){
					echo '01';exit;//@中不包括自己
			}
			if($inlove['tcount']!=0||!empty($inlove['tcount'])){//后台设置不等于0或者空的时候起作用
				if(in_array($this->mid,getUids($data['content']))){
					echo '01';exit;//@中不包括自己
				}elseif($inlove['tcount']==1&&$inlove['tcount']>count(getUids($data['content']))){
					echo '02';exit;//设置@数量为1的时候
				}elseif($inlove['tcount']<count(getUids($data['content']))){
					echo '03';exit;//超出设置的@数量
				}
			}
			if($restime > $timewait){
				$check = checkKeyWord($data['content']);
				//dump($checknum);
				if(false==$check){
					//关键字过滤
					$data['content'] = keyWordFilter($data['content']);	
					if($inlove['jifenopen']){//积分消耗
							$credit      = X('Credit');
							$creditUser    = M('credit_user')->where("uid={$this->mid}")->find(); // 用户积分
							$credit_ruls = $credit->getCreditRules();
							foreach ($credit_ruls as $v) 
								if ($v['name'] == 'inlove_add')
									$creditSet = $v;
								if(($creditUser['score']+$creditSet['score'])<0){
									echo '05';exit;//积分不够
								}
							X('Credit')->setUserCredit($this->mid,'inlove_add');
							$id = $pWeibo->publish( $inlove['inloveid'] , $data, 0 , $type , $type_data ,$_POST['sync'],$from_data); 
					}else{
							$id = $pWeibo->publish( $inlove['inloveid'] , $data, 0 , $type , $type_data ,$_POST['sync'],$from_data); 
					} 
							$_SESSION['inloveweiboid'][$this->mid]=$id;
					if( $id ){
						foreach (getUids($data['content']) as $k => $v) {
							$con['from_uid']=$this->mid;
							$con['to_uid']=$v;
							$con['ctime']=time();
							$con['weibo_id']=$id;
							$inlovecon['to']=$v;
							$inlovecon['title']='';
							$inlovecon['content']=$data['content'];
							D('Inlove')->postInlove($inlovecon, $this->mid,1);
							M('inlove')->add($con);
							$fids[]=$v;
							$islove['from_uid']=$v;
							$islove['to_uid']=$this->mid;
							$res=M('inlove')->where($islove)->count();
							if($res){
								$Notify['from']=$this->mid;
								$Notify['receive']=$v;
								$Notify['type']='inlove_yes';
								if(!M('Notify')->where($Notify)->count()){
									$data1['url']     =  U('//inlove'); 
									//$username1=getUserName($this->mid); 
									$userurl1= U('home/Space/index', array('uid' => $this->mid));
									$data1['title']   = "<a href=\"{$userurl1}\" target=\"_blank\">我</a>";  
									X('Notify')->send($fids,'inlove_yes',$data1,$this->mid);
								}
								$Notify['from']=$v;
								$Notify['receive']=$this->mid;
								if(!M('Notify')->where($Notify)->count()){
									$data2['url']     =  U('//inlove'); 
									//$username2=getUserName($v); 
									$userurl2= U('home/Space/index', array('uid' => $v));
									$data2['title']   = "<a href=\"{$userurl2}\" target=\"_blank\">我</a>";  
									X('Notify')->send($this->mid,'inlove_yes',$data2,$v);
								}
								$data1['url']     =  U('//inlove'); 
								$username1=getUserName($this->mid); 
								$userurl1= U('home/Space/index', array('uid' => $this->mid));
								$data1['title']   = "<a href=\"{$userurl1}\" target=\"_blank\">@{$username1}</a>";  
								X('Notify')->send($fids,'inlove_too',$data1,$inlove['inloveid']);
							}else{
									$data['url']     =  U('//tomy');  
									X('Notify')->send($fids,'inlove_to',$data,$inlove['inloveid']);
							}
						}
						$data = $pWeibo->getOneLocation( $id );
						if(!in_array($this->mid,getUids($data['content']))&&count(getUids($data['content']))>0){
							$data['users']=1;//是吐槽还是告白
						}
						$this->assign('data',$data);
						$this->display();
					}
				}else{
					echo '04';exit;
				}
			}else{
				echo $timewait; exit;
			}
			
		}

		//查询成功告白的
	  public  function islove(){
	 		  $toinlove=  array_unique(getSubByKey(M('inlove')->where("from_uid=".$this->mid)->findall(), 'to_uid'));
			  $tolove['from_uid']=array('in',$toinlove);
			  $tolove=  array_unique(getSubByKey( M('inlove')->where($tolove)->findall(),'from_uid'));
			  foreach ($tolove as $k => $v) {
				  if($this->mid > $v){
						$islove[]=$v."_".$this->mid;
				  }else{
						$islove[]=$this->mid."_".$v;
				  }
			  }
			  return $islove;  
	  }
   }	
?>
