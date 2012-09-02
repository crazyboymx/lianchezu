<?php 
class BcAction extends Action{
	// 分组选择
	public function selector($type = 'box'){
		$fid = intval($_REQUEST['fid']);

		$followGroupDao = D('FollowGroup');
		$group_list = $followGroupDao->getGroupList($this->mid);
		$f_group_status = $followGroupDao->getGroupStatus($this->mid,$fid);

		if($type == 'list'){
			foreach($group_list as &$v){
				$v['title'] = (strlen($v['title'])+mb_strlen($v['title'],'UTF8'))/2>10?getShort($v['title'],4).'...':$v['title'];
			}
		}

		$this->assign('fid',$fid);
		$this->assign('group_list',$group_list);
		$this->assign('f_group_status',$f_group_status);
	}

	// 下拉式-分组选择
	public function selectorList(){
		$this->selector('list');
		$this->display();
	}

	// 弹窗式-分组选择
	public function selectorBox(){
		$this->selector();
		$this->display();
	}

	// 设置某一个好友的分组状态
	public function setFollowGroup(){
		$gid = intval($_REQUEST['gid']);
		$fid = intval($_REQUEST['fid']);
		
		$followGroupDao = D('FollowGroup');
		$followGroupDao->setGroupStatus($this->mid,$fid,$gid);

		$follow_group_status = $followGroupDao->getGroupStatus($this->mid,$fid);
		foreach($follow_group_status as $k => $v){
			$v['title']      = (strlen($v['title'])+mb_strlen($v['title'],'UTF8'))/2>6?getShort($v['title'],3).'...':$v['title'];
			$_follow_group_status .= $v['title'].',';
			if(!empty($follow_group_status[$k+1]) && (strlen($_follow_group_status)+mb_strlen($_follow_group_status,'UTF8'))/2>=13){
				$_follow_group_status .= '···,';
				break;
			}
		}
        $_follow_group_status = substr($_follow_group_status,0,-1);
        exit($_follow_group_status);
	}

	// 为分组添加好友
	/*public function addFollows(){
		$fids = explode(',',$_REQUEST['fri_ids']);
		$gid = $_REQUEST['gid'];
		$groupDao = D('FollowGroup');
		foreach($fids as $fid){
			$groupDao->setGroupStatus($this->mid,$fid,$gid,'add');
		}
	}*/

	public function setBcTab(){
		if(is_numeric($_REQUEST['gid'])){
			$title = D('Bc')->getField('title',"bc_id={$_REQUEST['gid']}");
                        $acid = D('Bc')->getField('ac_id',"bc_id={$_REQUEST['gid']}");
                      
			$this->assign('gid',$_REQUEST['gid']);
                        $this->assign('acid',$acid);
			$this->assign('title',$title);
                       
		}
               
                $acdisplay = M('weibo_ac')->order('`display_order` ASC')->findAll();
                $this->assign('acdisplay',$acdisplay);           
		$this->display();
                
                
                
                
                
	}

      
 
	// 操作分组
	public function setBc(){
		$uid = $_REQUEST['uid'];
		$title = $_REQUEST['title'];
                $acid = $_REQUEST['acid'];
		if(!$_REQUEST['gid']){
                       // $acid = $_REQUEST['acid'];
			$res = D('Bc')->setBc($uid,$title,$acid);
		}else{
			$gid   = $_REQUEST['gid'];
                       // $acid = $_REQUEST['acid'];
			$res = D('Bc')->setBc($uid,$title,$acid,$gid);
		}
		echo $res;
	} 
        
 
        
	// 删除某个关注分组
	public function deleteBc($uid,$gid){
		$gid = $_REQUEST['gid'];
		$res = D('Bc')->deleteBc($this->mid,$gid);
		if(!$_SERVER['HTTP_REFERER']){
	            //$this->redirect('home/space/follow',array('uid'=>$this->mid,'type'=>'following'));
		}elseif($res){
			header('Location:'.preg_replace('/&bc_id=[0-9]*/i','',$_SERVER['HTTP_REFERER']));
		}else{
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}
	}
        
        
        
 
        
        
        
        
        
        
}
?>