<?php
/**
 * 用户管理中心
 * @author Nonant
 *
 */

class AccountAction extends Action{

    var $pUser;

    function _initialize(){
        $this->pUser = D('UserProfile');
        $this->pUser->uid = $this->mid;

        // 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        $is_domain_on = $is_domain_on['site_user_domain_on'];

        $menu[] = array( 'url' => 'index',		'name' => '个人资料' );
        $menu[] = array( 'url' => 'privacy',	'name' => '隐私设置' );
        if ($is_domain_on == 1)
        	$menu[] = array( 'url' => 'domain',	'name' => '个性化域名' );
        $menu[] = array( 'url' => 'security',	'name' => '帐号安全' );
        $menu[] = array( 'url' => 'medal',		'name' => '勋章管理');
        $menu[] = array( 'url' => 'bind',		'name' => '外部绑定' );
        //$menu[] = array( 'url' => 'credit',		'name' => '积分规则' );
        $menu[] = array( 'url' => 'verified',	'name' => '申请认证' );
        $this->assign('accountmenu',$menu);
    }

    //个人资料
    function index(){
        $data['userInfo']         = $this->pUser->getUserInfo();
        $data['userTag']          = D('UserTag')->getUserTagList($this->mid);
        $data['userFavTag']       = D('UserTag')->getFavTageList($this->mid);
        $this->assign( $data );
        $this->setTitle('设置 - 个人资料');
        $this->display();
    }

    //更新资料
    function update(){
         exit( json_encode($this->pUser->upDate( t($_REQUEST['dotype']) )) );
    }

    //绑定帐号
    function bind(){
   	    $sinabind = M('login')->where('type="sina" AND uid='.$this->mid)->findall();
   	    $data['sina'] = $sinabind;
   	    $user = M('user')->where('uid='.$this->mid)->field('email')->find();
   	    $replace = substr($user['email'],2,-3);
   	    for ($i=1;$i<=strlen($replace);$i++){
   	    	$replacestring.='*';
   	    }
   	    $data['email'] = str_replace(  $replace, $replacestring ,$user['email'] );
   	    $this->assign($data);
   	    $this->setTitle('设置 - 外部绑定');
    	$this->display();
    }

    //教育、工作情况
    function addproject(){
        $pUserProfile = D('UserProfile');
        $pUserProfile->uid = $this->mid;
        $strType = t( $_POST['addtype'] );
        if( $strType =='education' ){
            $data['school'] = msubstr( t($_POST['school']),0,70,'utf-8',false );
            $data['classes']= msubstr( t($_POST['classes']),0,70,'utf-8',false );
            $data['year']   = $_POST['year'];
            if( empty( $data['school'] ) ){
                $return['message']  = '学校名称不能为空';
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }
        }elseif ($strType == 'career' ){
            $data['company']   = msubstr( t($_POST['company']),0,70,'utf-8',false );
            $data['position']  = msubstr( t($_POST['position']),0,70,'utf-8',false );
            $data['begintime'] = intval( $_POST['beginyear'] ).'-'.intval($_POST['beginmonth']);
            $data['endtime']   = ( $_POST['nowworkflag'] ) ? '现在' : intval( $_POST['endyear'] ).'-'.intval($_POST['endmonth']);
            //2011-03-11 添加
            $date_begin = explode("-", $data['begintime']);
            $date_end = explode("-", $data['endtime']);

            $begin = mktime(0, 0, 0, $date_begin[1], 0, $date_begin[0]);
            $end = mktime(0, 0, 0, $date_end[1], 0, $date_end[0]);

            if( empty( $data['company'] ) ){
                $return['message']  = '公司名称不能为空';
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }

            if($data['endtime'] != '现在' && $begin > $end) {
            	$return['message'] = '就职起始时间不能大于就职结束时间';
            	$return['boolen'] = "0";
            	exit(json_encode($return));
            }
        }
        $data['id'] = $pUserProfile->dosave($strType,$data,'list',true);
        if($data['id']){
            $data['addtype'] = $strType;
            $return['message']  = '公司名称不能为空';
            $return['boolen']   = "1";
            $return['data']   = $data;
            exit( json_encode($return) );
        }
    }

    //个人标签
    function doUserTag(){
    	$strType = h($_REQUEST['type']);
        if( $strType!='deltag' && !$_POST['tagname'] && !$_POST['tagid'] ){
    		echo  json_encode( array('code'=>'3') );
    		exit();
    	}
    	if ($strType=='deltag'){
    		echo D('UserTag')->doDel(intval($_POST['tagid']),$this->mid);
    		exit();
    	}
    	$count = M( 'UserTag' )->where( 'uid='.$this->mid )->count();
    	if( $count >=10 ){
    		echo  json_encode( array('code'=>'2') );
    		exit();
    	}
    	if($strType=='addByname'){
    		$_POST['tagname'] = str_replace('，', ',', $_POST['tagname']);
    		$_POST['tagname'] = str_replace(' ', ',', $_POST['tagname']);
    		echo D('UserTag')->addUserTagByName( $_POST['tagname'] ,$this->mid ,$count);
    	}
    	if ($strType=='addByid'){
    		echo D('UserTag')->addUserTagById( $_POST['tagid'] ,$this->mid);
    	}
    }

    //头像处理
    function avatar(){
        $type = $_REQUEST['t'];
        $pAvatar = D('Avatar');
        $pAvatar ->uid = $this->mid;
        if( $type == 'upload' ){
            echo $pAvatar->upload();
        }elseif ( $type == 'save'){
            $pAvatar->dosave($this->mid);
        }elseif ( $type == 'camera'){
            $pAvatar->getcamera();
        }else{
        	$this->display();
        }
    }
	
	//封面处理 仿站二次开发
    function fengmian(){
        $type = $_REQUEST['t'];
        $pAvatar = D('Avatar');
        $pAvatar ->uid = $this->mid;
        if( $type == 'upload' ){
            echo $pAvatar->upload_fengmian();
        }elseif ( $type == 'save'){
            $pAvatar->dosave_fengmian($this->mid);
        }elseif ( $type == 'camera'){
            $pAvatar->getcamera();
        }else{
        	$this->display();
        }
    }
    //邀请
    public function invite() {
    	if($_POST){
    		if( model('Invite')->getReceiveCode( $this->mid ) ){
    			$this->assign('jumpUrl',U('home/Account/invite'));
    			$this->success('邀请码领取成功');
    			redirect( U('home/Account/invite') );
    		}else{
    			$this->error('邀请码领取失败');
    		}
    	}else{
	    	$invitecode = model('Invite')->getInviteCode( $this->mid );
	    	$receivecount = model('Invite')->getReceiveCount( $this->mid );
			$this->assign('receivecount',$receivecount);
			$this->assign('list',$invitecode);
			$this->setTitle('邀请');
	    	$this->display();
    	}
    }

    public function doInvite() {
    	$_POST['email'] = t($_POST['email']);
    	if ( !isValidEmail($_POST['email']) ) {
    		echo -1; //错误的Email格式
    		return ;
    	}

    	$map['email']  = $_POST['email'];
    	$map['is_active'] = 1;
    	if ( $user = M('user')->where($map)->find() ) {
    		echo $user['id']; //被邀请人已存在
    		return ;
    	}
    	unset($map);

    	//添加验证数据 之1
    	$validation = service('Validation')->addValidation($this->mid, $_POST['email'], U('home/Public/inviteRegister'), 'test_invite');
    	if (!$validation) {
    		echo 0;
    		return ;
    	}

    	//发送邀请邮件
    	global $ts;
    	$data['title'] = array(
    		'actor_name'	=> $ts['user']['uname'],
    		'site_name'		=> $ts['site']['site_name'],
    	);
    	$data['body']  = array(
    		'email'			=> $_POST['email'],
    		'actor'			=> '<a href="' . U('home/Space/index',array('uid'=>$ts['user']['uid'])) . '" target="_blank">' . $ts['user']['uname'] . '</a>',
    		'site'			=> '<a href="' . U('home') . '" target="_blank">' . $ts['site']['site_name'] . '</a>',
    	);
    	$tpl_record = model('Template')->parseTemplate('invite_register', $data);
    	unset($data);

    	if ($tpl_record) {
    		//echo -2; //邀请成功

    		//添加验证数据 之2
    		$map['target_url'] = $validation;
    		M('validation')->where($map)->setField('data', serialize(array('tpl_record_id'=>$tpl_record)));
    		echo $validation;
    	}else {
    		echo 0;
    	}
    }

	//邀请已存在的用户
    public function inviteExisted() {
    	$this->assign('uid', intval($_GET['uid']));
    	$this->display();
    }

    //删除资料
    function delprofile(){
        $intId = intval( $_REQUEST['id'] );
        $pUserProfile = D('UserProfile');
        echo $pUserProfile->delprofile( $intId ,$this->mid );
    }

    //帐号安全
    public function security() {
    	// UCenter账号同步失败则重新设置
    	if(UC_SYNC){
    		global $ts;
	    	$uc_user_ref = get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		if(uc_user_checkname($ts['user']['uname']))$this->assign('uc_username',$ts['user']['uname']);
	    		if(uc_user_checkemail($ts['user']['email']))$this->assign('uc_email',$ts['user']['email']);
	    		$this->assign('set_ucenter_username',1);
	    	}
    	}
    	
    	$this->setTitle('设置 - 账号安全');

    	$this->display();
    }

    //隐私设置
    function privacy(){
    	if($_POST){
    		$r = D('UserPrivacy')->dosave($_POST['userset'],$this->mid);
    	}
    	$userSet = D('UserPrivacy')->getUserSet($this->mid);
    	$blacklist = D('UserPrivacy')->getBlackList($this->mid);
    	$this->assign('userset',$userSet );
    	$this->assign('blacklist',$blacklist );
    	$this->setTitle('设置 - 隐私');
    	$this->display();

    }


    //设置黑名单
    function setBlackList(){
    	if( D("UserPrivacy")->setBlackList( $this->mid , t($_POST['type']) , intval($_POST['uid']) ) ){
    		echo '1';
    	}else{
    		echo '0';
    	}
    }

    //个性化域名
    function domain(){
    	// 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        if ($is_domain_on['site_user_domain_on'] != 1)
        	$this->error('个性化域名未启用');

    	if($_POST){
    		$domain = h($_POST['domain']);

    		if( !ereg('^[a-zA-Z][a-zA-Z0-9]+$', $domain)){
    			$this->error('域名只能以英文字母开头');
    		}

    		if( strlen($domain)<2 ){
    			$this->error('域名需大于1个字符');
    		}

    		if( strlen($domain)>20 ){
    			$this->error('域名需小于20个字符');
    		}
    		if( M('user')->where("uid!={$this->mid} AND domain='{$domain}'")->count()){
    			$this->error('已有人使用');
    		}else{
    			M('user')->setField('domain',$domain,'uid='.$this->mid);
    			$this->success('设置成功');
    		}
    	}else{
	    	$user = M('user')->where('uid='.$this->mid)->find();
	    	$data['userDomain'] = $user['domain'];
	    	$this->assign($data);
	    	$this->setTitle('设置 - 个性化域名');
	    	$this->display();
    	}
    }

    //修改密码
    public function doModifyPassword() {
    	if( strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'] ) {
			$this->error('密码必须为6-16位，且两次必须相同');
		}
		if ($_POST['password'] == $_POST['oldpassword']) {
			$this->error('原始密码和新密码不应该相同');
		}

    	$dao = M('user');
		//$_POST['oldpassword'] = md5($_POST['oldpassword']);
		$map['uid']			  = $this->mid;
		$map['password']	  = md5($_POST['oldpassword']);
    	if ( $dao->where($map)->find() ) {
			include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
			if(UC_SYNC){
				$ucenter_user_ref = get_ucenter_user_ref($this->mid);
				$uc_res = uc_user_edit($ucenter_user_ref['uc_username'],$_POST['oldpassword'],$_POST['password'],'');
				if($uc_res == -8){
					$this->error('抱歉：该用户受保护无权限更改');
				}
			}
    		//$_POST['password']    = md5($_POST['password']);
			if ( $dao->where($map)->setField('password', md5($_POST['password'])) ) {
				$this->success('保存成功');
			}else {
				$this->error('抱歉：保存失败');
			}

    	}else {
    		$this->error('原始密码错误');
    	}
    }

    //修改帐号
    public function modifyEmail() {
    	$_POST['email']    = t($_POST['email']);
    	$_POST['oldemail'] = t($_POST['oldemail']);
    	if ( !isValidEmail($_POST['email']) || !isValidEmail($_POST['oldemail']) ) {
    		echo -1;
    		return ; //$this->error('Email格式错误');
    	}
    	$map['uid']			= $this->mid;
    	$map['email']		= $_POST['oldemail'];
    	if ( ! M('user')->where($map)->find() ) {
    		echo -2;
    		return ; //原始Email错误
    	}
    	if ( !isEmailAvailable($_POST['email']) ) {
    		echo -3;
    		return ; //$this->error('新Emai已存在');
    	}

    	$opt_email_activate = model('Xdata')->lget('register');

    	// 不需要验证邮件时, 直接修改帐号
		if (!$opt_email_activate['register_email_activate']) {
			if ( M('user')->where($map)->setField('email', $_POST['email']) ) {
				service('Passport')->logoutLocal();
				echo 1;
			}else {
				echo 0;
			}
			unset($opt_email_activate);
			exit;
		}

		unset($opt_email_activate);

		// 邮件验证

    	//添加验证
    	$data = array('oldemail'=>$_POST['oldemail'], 'email'=>$_POST['email']);
    	if ( $url = service('Validation')->addValidation($this->mid, '', U('home/Public/doModifyEmail'), 'modify_account', serialize($data)) ) {
    		// 发送验证邮件
    		global $ts;
    		$body = <<<EOD
<strong>{$ts['user']['uname']}，你好：</strong><br/>

您只需通过点击下面的链接重置您的帐号：<br/>

<a href="$url">$url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;

			if (service('Mail')->send_email($_POST['email'], "重置{$ts['site']['site_name']}帐号", $body)) {
				echo '2';
			}else {
				echo '-4';
			}

    	}else {
    		echo '0';
    	}
    }

    // 设置UCenter账号
    public function doModifyUCenter() {
    	include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
    	if(UC_SYNC){
	    	$uc_user_ref = get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		$username = $_POST['username'];
	    		$email = $_POST['email'];
	    		$password = $_POST['password'];
	    		if(uc_user_checkname($username) != 1 || !isLegalUsername($username) || M('user')->where("uname='{$username}' AND uid<>{$this->mid}")->count())$this->error('用户名不合法或已经存在，请重新设置用户名');
	    		if(uc_user_checkemail($email) != 1 || M('user')->where("uname='{$email}' AND uid<>{$this->mid}")->count())$this->error('Email不合法或已经存在，请重新设置Email');
	    		global $ts;
	    		if(md5($password) != $ts['user']['password'])$this->error('密码错误，请重新输入密码');
	    		$uc_uid = uc_user_register($username,$password,$email);
	    		if($uc_uid>0){
	    			add_ucenter_user_ref($this->mid,$uc_uid,$username);
					$this->assign('jumpUrl', U('home/Account/security'));
					$this->success('设置UCenter账号成功');
	    		}else{
	    			$this->error('设置UCenter账号失败');
	    		}
	    	}else{
	    		redirect(U('home/Account/security'));
	    	}
    	}else{
    		redirect(U('home/Account/security'));
    	}
    }

    // 勋章管理
    public function medal() {
    	$_GET['type'] = $_GET['type'] == 'manage' ? 'manage' : 'my';

    	if ($_GET['type'] == 'my') {
    		$data = model('Medal')->getMedalWidgetData($this->mid, false, false);
    	}else {
    		$data = model('Medal')->getMedalWidgetData($this->mid, false, true);
    	}

    	$this->assign($data);
    	$this->assign('type', $_GET['type']);
    	$this->setTitle('设置 - 勋章管理');
    	$this->display();
    }

    public function doMedalManage() {
    	// medal_manage主要是为了防止表单重复提交 :(
    	if ($_POST['medal_manage'] != '1') {
    		$this->error('参数错误');
    	}

    	$dao = model('Medal');
    	$_POST['show_ids'] = explode(',', t($_POST['show_ids']));

    	// 显示OR隐藏仅针对用户已获得的勋章, 用户未获得的勋章(即received_time<=0)不做变化
    	$show_ids = array();
    	$hide_ids = array();
    	$data = model('Medal')->getMedalWidgetData($this->mid, false, true);
    	foreach ($data['user_medal'] as $v) {
    		if (in_array($v['medal_id'], $_POST['show_ids'])) {
    			$show_ids[] = $v['medal_id'];
    		}else {
    			$hide_ids[] = $v['medal_id'];
    		}
    	}

    	if ( !empty($show_ids) ) {
	    	$dao->setUserMedalStatus($this->mid, $show_ids, 1);
    	}
    	if ( !empty($hide_ids) ) {
	    	$dao->setUserMedalStatus($this->mid, $hide_ids, 0);
    	}

    	$this->assign('jumpUrl', U('home/Account/medal', array('type'=>'manage')));
    	$this->success('保存成功');
    }

    //积分规则
    public function credit(){
    	$credit = X('Credit');
    	$credit_type  = $credit->getCreditType();
    	$credit_rules = $credit->getCreditRules();

    	$this->assign('credit_type',$credit_type);
    	$this->assign('credit_rules',$credit_rules);
    	$this->setTitle('设置 - 积分规则');
    	$this->display();
    }

    // 申请认证
    public function verified()
    {
    	$verified = M('user_verified')->where("uid={$this->uid}")->find();

    	$this->assign('verified', $verified);
    	$this->display();
    }

    public function doVerified()
    {
    	$data['uid'] 	  = $this->mid;
    	$data['realname'] = preg_match('/^[\x{4e00}-\x{9fa5}]+$|^[a-zA-Z\.·]+$/u', $_POST['realname']) ? $_POST['realname'] : '';
    	$data['phone']	  = preg_match('/^[\d]{11}$/', $_POST['phone']) ? $_POST['phone'] : '';
    	$data['reason']	  = h(t($_POST['reason']));
    	if (!$data['realname'] || !$data['phone'] || !$data['reason']) {
    		echo 0;
    	}
    	$data['verified'] = '0';
    	if (is_numeric($_POST['id']) && $_POST['id'] > 0) {
    		$data['id'] = $_POST['id'];
    		$res = M('user_verified')->save($data);
    	} else {
    		$res = M('user_verified')->add($data);
    	}
    	if (false !== $res) {
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
}
