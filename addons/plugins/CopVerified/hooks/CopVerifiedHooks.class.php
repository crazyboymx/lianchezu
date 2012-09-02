<?php
class CopVerifiedHooks extends Hooks
{
    static $cache_list=array();
	public function init()
	{
	}

	public function home_account_tab($param)
	{
	   $verified = M('user_cop_verified')->where("uid={$this->mid}")->find();
	   if($verified['uid']>0){
		$param['menu'][] = array(
			'act' => 'copverified',
			'name' => '认证资料',
			'param' => array(
				'addon' => 'CopVerified',
				'hook'  => 'home_account_show'
			)
		);
		}else{
		  return false;
		}
	}
	
	
	 public function public_head($param) {
		
		echo '<link href="' . $this->htmlPath . '/css/nivo-slider.css" rel="stylesheet" type="text/css"/>';
		echo '<link href="' . $this->htmlPath . '/css/style.css" rel="stylesheet" type="text/css"/>';
		
    }
	
	public function dellink(){
	$data=M('user_cop_verified')->where('uid="'.$this->mid.'"')->find();
	$huandeng=unserialize($data['huandengpian']);
	unset($huandeng[$_POST['k']]);
	M('user_cop_verified')->save(array('id'=>$data['id'],'huandengpian'=>serialize($huandeng)));
	
	}
    public function add_flash(){
		$data=M('user_cop_verified')->where('uid="'.$this->mid.'"')->find();
		$h=array(array(
				 'tupian'=>$_POST['tupian'],
				 'wenzi'=>$_POST['wenzi'],
				 'links'=>$_POST['links'],
				 ),
			);
		if(!$data['huandengpian']) {
			$shuju=array_merge($h);
			}else{
		    $huandeng=unserialize($data['huandengpian']);
		   	$shuju=array_merge($huandeng,$h);
			}
	
		M('user_cop_verified')->save(array('id'=>$data['id'],'huandengpian'=>serialize($shuju)));
		echo 1;
		
		}
	public function home_account_show()
	{
    	$verified = M('user_cop_verified')->where("uid={$this->mid}")->find();
		
    	$this->assign($verified);
    	$this->display('home_account_show');
	}
    //媒体汇
	 public function header_square_tab($param)
	{
		$param['menu'][] = array(
						'act' => 'meitihui',
						'name' => '媒体汇',
						'param' => array(
							'addon' => 'CopVerified',
							'hook'	=> 'home_square_show',
						));
					
		
		
	}
    public function home_square_tab($param)
	{
		$param['menu'][] = array(
						'act' => 'meitihui',
						'name' => '媒体汇',
						'param' => array(
							'addon' => 'CopVerified',
							'hook'	=> 'home_square_show',
						));
					
	}
	private function _pagination($count,$perlogs,$page,$url,$suffix=''){
	$pnums = @ceil($count / $perlogs);
	$re = '';
	for ($i = $page-5;$i <= $page+5 && $i <= $pnums; $i++){
		if ($i > 0){
			if ($i == $page){
				$re .= ' <span class="current">'.$i.'</span> ';
			} else {
				$re .= '<a href="'.$url.$i.$suffix.'">'.$i.'</a>';
			}
		}
	}
	if ($page > 6) $re = '<a href="'.$url.'1'.$suffix.'" title="首页">&laquo;</a> ...'.$re;
	if ($page + 5 < $pnums) $re .= '... <a href="'.$url.$pnums.$suffix.'" title="尾页">&raquo;</a>';
	if ($pnums <= 1) $re = '';
	return $re;
}
	public function home_square_show(){
		         $page = isset($_GET['page']) ? $_GET['page'] : '1';
                 $lstart = $page*20-20;
				
                 $url=U('home/Square',array('act'=>'meitihui','addon'=>'CopVerified','hook'=>'home_square_show','page'=>''));
				  $data['data']=M('')->query("select m.*,count(mf.fid) as count,m.uid as c_uid  from ts_user_cop_verified  m LEFT JOIN ts_weibo_follow mf on m.uid=mf.fid GROUP BY m.uid ORDER BY count DESC limit $lstart,20");
				 $Num = M('')->query("select m.*,count(mf.fid) as count,m.uid as c_uid  from ts_user_cop_verified  m LEFT JOIN ts_weibo_follow mf on m.uid=mf.fid GROUP BY m.uid");
				 $data['page'] = $this->_pagination(count($Num), 20, $page, $url);
		         $this->assign($data);
				 $this->display('home_square_show');		
		
				
			
	}
	public function addVerifiedjigoulist(){
	    if ( !empty($_POST) ) {
			$_SESSION['admin_searchVerifiedUser'] = serialize($_POST);
    		$this->assign('type', 'searchUser');
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['admin_searchVerifiedUser']);
    		$this->assign('type', 'searchUser');
		}else {
			unset($_SESSION['admin_searchVerifiedUser']);
		}

		$_POST['uid'] 	   && $map['uid'] 	   = array('IN', t($_POST['uid']));
		$_POST['name'] && $map['name'] = array('exp', 'LIKE "%' . t($_POST['name']) . '%"');
		$_POST['fuzeren']    && $map['fuzeren']    = array('exp', 'LIKE "%' . t($_POST['fuzeren']) . '%"');
		$_POST['lianxifangshi']   && $map['lianxifangshi']   = array('exp', 'LIKE "%' . t($_POST['lianxifangshi']) . '%"');
		$_POST['jieshao']   && $map['jieshao']   = array('exp', 'LIKE "%' . t($_POST['jieshao']) . '%"');

    	
		

    	$this->assign($_POST);
    	$this->assign('verified', $verified);
    	$this->assign(M('user_cop_verified')->where($map)->findPage());
		$this->display('verified');
	     
	}
	public function delrenz(){
	
	$uid = is_array($_POST ['uid']) ? '(' . implode ( ',', $_POST ['uid'] ) . ')' : '(' . $_POST ['uid'] . ')'; // 判读是不是数组
		
		$res = M('user_cop_verified')->where('uid IN ' . t($uid) )->delete(); // 删除认证
    	if ($res) {
			echo 1;

		} else {
			echo 0;
			exit;
		}
	}
	public function home_account_do($param)
	{
    	        $verified = M('user_cop_verified')->where("uid={$this->mid}")->find();
    	        $sdata['id'] 	  = $verified['id'] ;
    	        $sdata['name'] = h(t($_POST['sname']));
    	        $sdata['fuzeren']	  = h(t($_POST['sfuzeren']));
    	        $sdata['lianxifangshi']	  = h(t($_POST['slianxifangshi']));
		        $sdata['jieshao']	  = h(t($_POST['sjieshao']));
    	        if(M('user_cop_verified')->save($sdata)==1){
		          header("Location: index.php?app=home&mod=Account&act=copverified&addon=CopVerified&hook=home_account_show");
		        }else{
		         $this->error();
		        }
	}

	// 用户名图标显示
	public function user_name_end($param)
	{

		$uid  = $param['uid'];
		$html = & $param['html'];
		$verified = M('user_cop_verified')->where("uid={$uid}")->find();
	    if($verified['uid']>0){
	        $html .= '<img class="ts_icon" src="' . SITE_URL . '/addons/plugins/CopVerified/html/icon_j.png" title="团体认证" />';	
		}	
		
	}

	// 个人空间右侧显示
	public function home_space_right_top($param)
	{
	    $uid  = $param['uid'];
	    $verified = M('user_cop_verified')->where("uid={$uid}")->find();
	    if($verified['uid']>0){
		$pattern='/(http:\/\/|https:\/\/|ftp:\/\/)([\w:\/\.\?=&-_#]+)/is';
	     $verified['jieshao'] = preg_replace($pattern, '<a  href="\1\2" style="float:;width: 100%;height:100%px;display: block;">\1\2</a>', $verified['jieshao']);
		$this->assign($verified );
		$this->display('space_verified');
		}else{
		return false;
		}
	}

	/* 插件后台管理项 */
	

	
	
	public function addVerifiedjigou()
	{
    	
    	$this->display('addVerifiedjigou');
	}
    public function saveVerifiedjigou(){
		if(!$_POST['uid']){
			$this->error('请填写用户UID和机构认证信息');
			}else{
			     $verified = M('user_cop_verified')->where("uid='".intval($_POST['uid'])."'")->find();
	             if($verified['uid']>0){
		              $this->error('该用户已认证');
		         }
				$data['uid'] 	  = intval($_POST['uid']);
    	        $data['name'] = h(t($_POST['name']));
    	        $data['fuzeren']	  = h(t($_POST['fuzeren']));
    	        $data['lianxifangshi']	  = h(t($_POST['lianxifangshi']));
		        $data['jieshao']	  = h(t($_POST['jieshao']));
		        M('user_cop_verified')->add($data);
				}
		
		}
	public function jianceuname(){
	  $uid=intval($_POST['uid']);
	  $data['uid']=0;
	  $data=M('user')->where('uid="'.$uid.'"')->find();
	  if($data['uid']>0) exit(json_encode($data));
	  else
	 echo -1;
	  
	}	
  
}