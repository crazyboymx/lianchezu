<?php
    /**
     * IndexAction 
     * 抢星位
     */
class IndexAction extends Action {
       public function _initialize() {
		   $loot_inc_s=model('Xdata')->lget('loot');//获取配置
		   $userscore=M('credit_user')->where('uid='.$this->mid)->getField('score');
		   $this->assign('userscore',$userscore);
		   $this->assign('loot_inc_s',$loot_inc_s);
	    }
		// 应用首页
	   public function index(){
		    $loot_inc_s=model('Xdata')->lget('loot');//获取配置
		    $map['status']=1;
		    $lootc= D('loot_category')->order('display_order ASC')->where($map)->findall();
			$cran_num=rand() % (count($lootc)-1);
			if($loot_inc_s['iscran']!=1){
				$k_cid=$_GET['cid']?$_GET['cid']:$lootc[$cran_num]['id']; //随即取样
			}else{
				$k_cid=$_GET['cid']?$_GET['cid']:$loot_inc_s['iscran_fenlei']; 
			}
			$days=$loot_inc_s['lootdays'];
			$minutes=$days*60*60*24; //星位停留时间
			$loottime=time()-$minutes;
			$maps['cid']=$k_cid;
			$maps['ctime']= array('gt',$loottime);
			for($i=0;$i<$loot_inc_s['loots'];$i++){
				$maps['num']=$i+1;
				$res=M('loot')->where($maps)->find();
				if(!empty($res)){
					$list[$i]=$res;
					$fow_status= getFollowState( $this->mid , $res['uid']);
					$list[$i]['fow_status']=($fow_status=='unfollow')?1:0;
				}else{
					$list[$i]['is']='yes';//表示空位
				}
				$list[$i]['num']=$i+1;
			}
			$this->assign('list',$list);
			$this->assign('lootc',$lootc);
			$this->assign('k_cid',$k_cid);
			$this->setTitle("达人秀");
			$this->display("loot");
			
	   }
	   
	   // 用户抢位操作
	   public function userloot(){
	  		$map['uid']=$this->mid;
			$userscore=M('credit_user')->where($map)->getField('score');
			$loot_inc_s=model('Xdata')->lget('loot');//获取配置
			if($userscore<$loot_inc_s['jifeng']){
				echo '05';exit;//不足显示
			}else{
				$map['cid']=$_POST['cid'];
				$map['num']=$_POST['num'];
				$map['ctime']=time();
				$res=M('loot')->where('uid='.$this->mid)->count();
				if($res){
					if($loot_inc_s['isqiang']!=1){
						$days=$loot_inc_s['lootdays'];
						$minutes=$days*60*60*24; //星位停留时间
						$loottime=time()-$minutes;
						$maps['uid']= $this->mid;
						$maps['ctime']= array('gt',$loottime);
						$res1=M('loot')->where($maps)->count();
						if($res1){//数据是否有效
							echo '01';exit;//用户已经有其他座位
						}
					}
					if(getLoots($_POST['cid'])<=0){
						echo '02';exit; //位置已空
					}else{
						$res2=M('loot')->where('uid='.$this->mid)->save($map);//保存数据
						if($res2){
							M('credit_user')->setDec('score','uid='.$this->mid,$loot_inc_s['jifeng']); //扣除用户积分
							if($loot_inc_s['isnotify']==1){
								$data['url']     =  U('//index', array('cid' => $_POST['cid']));
								$username=getUserName($this->mid);
								$userurl= U('home/Space/index', array('uid' => $this->mid));
								$ctitle=M('loot_category')->where('id='.$_POST['cid'])->getField('title');
								$data['title']   = "恭喜<a href=\"{$userurl}\" target=\"_blank\">@{$username}</a>抢到 {$ctitle} 分类下的第{$_POST['num']}号明星位！！最多可以在该位置停留{$loot_inc_s['lootdays']}天";
								X('Notify')->send( $this->mid ,'loot_show',$data,1);
							}
							$username=getUserName($this->mid);
							$data['content']="恭喜@{$username } 抢到 {$ctitle} 分类下的第{$_POST['num']}号明星位！！";
							$from_data['source']="抢星位";
							$from_data['url']=U('loot/Index/index');
							$from_data=serialize($from_data);
							D('Weibo','weibo')->publish( $this->mid , $data, 0 , $type , $type_data ,$_POST['sync'],$from_data); 
							//用户进行积分操作后，登录用户的缓存将修改
							$creditUser    =M('credit_user')->where("uid={$this->mid}")->find();
							$userLoginInfo = S('S_userInfo_'.$this->mid);
							if(!empty($userLoginInfo)) {
								$userLoginInfo['credit']['score']['credit'] = $creditUser['score'];
								$userLoginInfo['credit']['experience']['credit'] = $creditUser['experience'];
								S('S_userInfo_'.$this->mid, $userLoginInfo);
							}
							if( M('loot_count')->where('uid='.$this->mid )->count()){
								M('loot_count')->where('uid='.$this->mid)->setInc('counts');
							}else{
								$usecount['counts']=1;
								$usecount['uid']=$this->mid;
								M('loot_count')->add($usecount);
							}
							echo '03';exit; //成功
						}else{
							echo '04';exit; //失败
						}
					}
				}else{
					if(getLoots($_POST['cid'])<=0){
						echo '02';exit; //位置已空
					}else{
						$res2=M('loot')->add($map);//保存数据
						if($res2){
							M('credit_user')->setDec('score','uid='.$this->mid,$loot_inc_s['jifeng']); //扣除用户积分
							if($loot_inc_s['isnotify']==1){
								$data['url']     =  U('//index', array('cid' => $_POST['cid']));
								$username=getUserName($this->mid);
								$userurl= U('home/Space/index', array('uid' => $this->mid));
								$ctitle=M('loot_category')->where('id='.$_POST['cid'])->getField('title');
								$data['title']   = "恭喜<a href=\"{$userurl}\" target=\"_blank\">@{$username}</a>抢到 {$ctitle} 分类下的第{$_POST['num']}号明星位！！最多可以在该位置停留{$loot_inc_s['lootdays']}天";
								X('Notify')->send($this->mid , 'loot_show',$data,1);
							}	
								$username=getUserName($this->mid);
								$data['content']="恭喜@{$username } 抢到 {$ctitle} 分类下的第{$_POST['num']}号明星位！！";
								$from_data['source']="抢星位";
								$from_data['url']=U('loot/Index/index');
								$from_data=serialize($from_data);
								 D('Weibo','weibo')->publish( $this->mid , $data, 0 , $type , $type_data ,$_POST['sync'],$from_data); 
							//用户进行积分操作后，登录用户的缓存将修改
							$creditUser    =M('credit_user')->where("uid={$this->mid}")->find();
							$userLoginInfo = S('S_userInfo_'.$this->mid);
							if(!empty($userLoginInfo)) {
								$userLoginInfo['credit']['score']['credit'] = $creditUser['score'];
								$userLoginInfo['credit']['experience']['credit'] = $creditUser['experience'];
								S('S_userInfo_'.$this->mid, $userLoginInfo);
							}
							if( M('loot_count')->where('uid='.$this->mid )->count()){
								M('loot_count')->where('uid='.$this->mid)->setInc('counts');
							}else{
								$usecount['counts']=1;
								$usecount['uid']=$this->mid;
								M('loot_count')->add($usecount);
							}
							echo '03';exit; //成功
						}else{
							echo '04';exit; //失败
						}	
					}
						
				}
			}
		
			
	   }
	  	//用户积分不足时提示
		public function userscore() {
			$this->display();		
		}
   }	
?>
