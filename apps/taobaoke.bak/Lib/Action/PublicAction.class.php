<?php
class PublicAction extends Action{

	public function _initialize() {
		
	}

	public function adminlogin() {
		if ( service('Passport')->isLoggedAdmin() ) {
			redirect(U('admin/Index/index'));
		}

		$this->display();
	}

	public function doAdminLogin() {
		// 检查验证码
		if ( md5($_POST['verify']) != $_SESSION['verify'] ) {
			$this->error('验证码错误');
		}

		// 数据检查
		if ( empty($_POST['password']) ) {
			$this->error('密码不能为空');
		}
		if ( isset($_POST['email']) && ! isValidEmail($_POST['email']) ) {
			$this->error('email格式错误');
		}

		// 检查帐号/密码
		$is_logged = false;
		if (isset($_POST['email'])) {
			$is_logged = service('Passport')->loginAdmin($_POST['email'], $_POST['password']);
		}else if ( $this->mid > 0 ) {
			$is_logged = service('Passport')->loginAdmin($this->mid, $_POST['password']);
		}else {
			$this->error('参数错误');
		}

		// 提示消息不显示头部
		$this->assign('isAdmin','1');

		if ($is_logged) {
			$this->assign('jumpUrl', U('admin/Index/index'));
			$this->success('登陆成功');
		}else {
			$this->assign('jumpUrl', U('home/Public/adminlogin'));
			$this->error('登陆失败');
		}
	}
	
	public function login()
	{
		if (service('Passport')->isLogged())
			U('home','',true);
			
		unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'],$_SESSION['open_platform_type']);
		
		//验证码
		$opt_verify = model('Xdata')->lget('siteopt');
		$opt_verify = $opt_verify['site_verify'];
		$opt_verify = in_array('login', $opt_verify);
		if ($opt_verify) {
			$this->assign('register_verify_on', 1);
		}

		$data['email'] = t($_REQUEST['email']);
		$data['uid']   = t($_REQUEST['uid']);
		$uids = array();
		
		// 正在热议
		$data['hot_topic'] = D('Topic', 'weibo')->getHot();
		
		// 人气推荐
		$data['hot_user']  = D('Follow', 'weibo')->getTopFollowerUser();
		$uids = array_merge($uids, getSubByKey($data['hot_user'], 'uid'));
		
		// 正在发生 (原创的文字微博)
		$data['lastest_weibo'] = D('Operate', 'weibo')->doSearchTopic('`transpond_id` = 0 AND `type` = 0', 'weibo_id DESC', 0);
		$data['lastest_weibo'] = $data['lastest_weibo']['data'];
		$uids = array_merge($uids, getSubByKey($data['lastest_weibo'], 'uid'));
		$this->assign('since_id', empty($data['lastest_weibo']) ? 0 : intval($data['lastest_weibo'][0]['id']));
		
		// 原创的图片微博
		$map['transpond_id'] = 0;
		$map['type']		 = 1;
		$data['pic_weibo'] = D('Operate', 'weibo')->where($map)->order('weibo_id DESC')->limit(3)->findAll();
		$uids = array_merge($uids, getSubByKey($data['pic_weibo'], 'uid'));
		foreach ($data['pic_weibo'] as $k => $v)
			$data['pic_weibo'][$k]['type_data'] = unserialize($v['type_data']);
		
		D('User', 'home')->setUserObjectCache($uids);
		
		// 第三方平台
		$platform_options = model('Xdata')->lget('platform');
		$platforms = array();
		if (!empty($platform_options['sina_wb_akey']) && !empty($platform_options['sina_wb_skey']))
			$platforms[] = 'sina';
		if (!empty($platform_options['qq_key']) && !empty($platform_options['qq_secret']))
			$platforms[] = 'qq';
		if (!empty($platform_options['douban_key']) && !empty($platform_options['douban_secret']))
			$platforms[] = 'douban';
		$this->assign('platforms', $platforms);

		$this->assign($data);
		$this->assign('regInfo',model('Xdata')->lget('register'));
		$this->setTitle('登陆');
		$this->display();
	}

	//第三方登录页面显示
	function tryOtherLogin(){
		if ( !in_array($_GET['type'], array('sina', 'douban', 'qq')) ) {
			$this->error('参数错误');
		}
		include_once(SITE_PATH . "/addons/plugins/login/{$_GET['type']}.class.php");
        $platform = new $_GET['type']();
        redirect($platform->getUrl());
	}

	// 腾讯回调地址
	public function qqcallback() {
		include_once( SITE_PATH . '/addons/plugins/login/qq.class.php' );
        $qq = new qq();
        $qq->checkUser();
        redirect(U('home/Public/otherlogin'));
	}

	//外站帐号登陆
	public function otherlogin(){
		if ( !in_array($_SESSION['open_platform_type'], array('sina', 'douban', 'qq')) ) {
			$this->error('授权失败');
		}

		$type = $_SESSION['open_platform_type'];
		include_once( SITE_PATH."/addons/plugins/login/{$type}.class.php" );
		$platform = new $type();
		$userinfo = $platform->userInfo();
		// 检查是否成功获取用户信息
		if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
			$this->assign('jumpUrl', SITE_URL);
			$this->error('获取用户信息失败');
		}
		if ( $info = M('login')->where("`type_uid`='".$userinfo['id']."' AND type='{$type}'")->find() ) {
			$user = M('user')->where("uid=".$info['uid'])->find();
			if (empty($user)) {
				// 未在本站找到用户信息, 删除用户站外信息,让用户重新登陆
				M('login')->where("type_uid=".$userinfo['id']." AND type='{$type}'")->delete();
			}else {
				if ( $info['oauth_token'] == '' ) {
					$syncdata['login_id']        	= $info['login_id'];
					$syncdata['oauth_token']        = $_SESSION[$type]['access_token']['oauth_token'];
					$syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
					M('login')->save($syncdata);
				}

				service('Passport')->registerLogin($user);
				
				redirect(U('home/User/index'));
			}
		}
		$this->assign('user',$userinfo);
		$this->assign('type',$type);
		$this->setTitle('第三方账号登陆');
		$this->display();
	}

	// 激活外站登陆
	public function initotherlogin(){
		if ( ! in_array($_POST['type'], array('douban','sina', 'qq')) ) {
			$this->error('参数错误');
		}


		if( !isLegalUsername( t($_POST['uname']) ) ){
			$this->error('昵称格式不正确');
		}

		$haveName = M('User')->where( "`uname`='".t($_POST['uname'])."'")->find();
		if( is_array( $haveName ) && sizeof($haveName)>0 ){
			$this->error('昵称已被使用');
		}

		$type = $_POST['type'];
		include_once( SITE_PATH."/addons/plugins/login/{$type}.class.php" );
		$platform = new $type();
		$userinfo = $platform->userInfo();
		
		// 检查是否成功获取用户信息
		if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
			$this->assign('jumpUrl', SITE_URL);
			$this->error('获取用户信息失败');
		}

		// 检查是否已加入本站
		$map['type_uid'] = $userinfo['id'];
		$map['type']     = $type;
		if ( ($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find()) ) {
			$this->assign('jumpUrl', SITE_URL);
			$this->success('您已经加入本站');
		}
		// 初使化用户信息, 激活帐号
		$data['uname']        = t($_POST['uname'])?t($_POST['uname']):$userinfo['uname'];
		$data['province']     = intval($userinfo['province']);
		$data['city']         = intval($userinfo['city']);
		$data['location']     = $userinfo['location'];
		$data['sex']          = intval($userinfo['sex']);
		$data['is_active']    = 1;
		$data['is_init']      = 1;
		$data['ctime']      = time();
		$data['is_synchronizing']  = ($type == 'sina') ? '1' : '0'; // 是否同步新浪微博. 目前仅能同步新浪微博

		if ( $id = M('user')->add($data) ) {
			// 记录至同步登陆表
			$syncdata['uid']                = $id;
			$syncdata['type_uid']           = $userinfo['id'];
			$syncdata['type']               = $type;
			$syncdata['oauth_token']        = $_SESSION[$type]['access_token']['oauth_token'];
			$syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
			M('login')->add($syncdata);

			// 转换头像
			if ($_POST['type'] != 'qq') { // 暂且不转换QQ头像: QQ头像的转换很慢, 且会拖慢apache
				D('Avatar')->saveAvatar($id,$userinfo['userface']);
			}

			// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
			$userlog = array(
				'uid'		=> $id,
				'action'	=> 'add',
				'type'		=> '0',
				'dateline'	=> time(),
			);
			M('myop_userlog')->add($userlog);

			service('Passport')->loginLocal($id);
			
			$this->registerRelation($id);

			redirect( U('home/Public/followuser') );
		}else{
			$this->error('同步帐号发生错误');
		}
	}

	public function bindaccount() {
		if ( ! in_array($_POST['type'], array('douban','sina','qq')) ) {
			$this->error('参数错误');
		}

		$psd  = ($_POST['passwd']) ? $_POST['passwd'] : true;
		$type = $_POST['type'];

		if ( $user = service('Passport')->getLocalUser($_POST['email'], $psd) ) {
			include_once( SITE_PATH."/addons/plugins/login/{$type}.class.php" );
			$platform = new $type();
			$userinfo = $platform->userInfo();

			// 检查是否成功获取用户信息
			if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
				$this->assign('jumpUrl', SITE_URL);
				$this->error('获取用户信息失败');
			}

			// 检查是否已加入本站
			$map['type_uid'] = $userinfo['id'];
			$map['type']     = $type;
			if ( ($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find()) ) {
				$this->assign('jumpUrl', SITE_URL);
				$this->success('您已经加入本站');
			}

			$syncdata['uid']      = $user['uid'];
			$syncdata['type_uid'] = $userinfo['id'];
			$syncdata['type']     = $type;
			if ( M('login')->add($syncdata) ) {
				service('Passport')->registerLogin($user);
				
				$this->assign('jumpUrl', U('home/User/index'));
				$this->success('绑定成功');
				
			}else {
				$this->assign('jumpUrl', SITE_URL);
				$this->error('绑定失败');
			}
		}else {
			$this->error('帐号输入有误');
		}
	}

	//
	public function callback(){
		include_once( SITE_PATH.'/addons/plugins/login/sina.class.php' );
		$sina = new sina();
		$sina->checkUser();
		redirect(U('home/public/otherlogin'));
	}

	public function doubanCallback() {
		if ( !isset($_GET['oauth_token']) ) {
			$this->error('Error: No oauth_token detected.');
			exit;
		}
		require_once SITE_PATH . '/addons/plugins/login/douban.class.php';
		$douban = new douban();
		if ( $douban->checkUser($_GET['oauth_token']) ) {
			redirect(U('home/Public/otherlogin'));
		}else {
			$this->assign('jumpUrl', SITE_URL);
			$this->error('验证失败');
		}
	}
	
	public function doLogin() {
		// 检查验证码
		$opt_verify = model('Xdata')->lget('siteopt');
		$opt_verify = $opt_verify['site_verify'];
		$opt_verify = in_array('login', $opt_verify);

		if ($opt_verify && md5($_POST['verify'])!=$_SESSION['verify']) {
			$this->error('验证码错误');
		}
		
		$username =	$_POST['email'];
		$password =	$_POST['password'];
		
		if(!$password){
			$this->error('请输入密码');
		}

		if(isValidEmail($username)){
			$user = service('Passport')->getLocalUser($username,$password);
			
			if(UC_SYNC && $user['uid']){
				$uc_user_ref = get_ucenter_user_ref($user['uid']);
				if($uc_user_ref['uc_uid']){
					$uc_user = uc_user_login($uc_user_ref['uc_uid'],$password,1);
					if($uc_user[0] == -1 || $uc_user[0] == -2)$uc_user_ref = array();
				}else if($user['uname']){
					$res_checkname = uc_user_checkname($user['uname']);
					if($res_checkname>=-3 && $res_checkname<=-1){
						$error_param = '用户名';
					}
					$res_checkemail = uc_user_checkemail($username);
					if($res_checkemail>=-6 && $res_checkemail<= -4){
						$error_param = $error_param?$error_param.'和Email':'Email';
					}
					if($error_param){						
						$message_data['title']   = '同步至UCenter时，'.$error_param.'不合法或已被注册';
						$message_data['content'] = '由于您在本站上的'.$error_param.'在UCenter上不合法或已被注册'.$error_param.'冲突，可到此地址 '.U('home/Account/security').'#ucenter 重新设置您在UCenter上的'.$error_param.'。';
						$message_data['to'] = $user['uid'];
						model('Message')->postMessage($message_data, M('user')->getField('uid','admin_level=1'));
					}else{
						$uc_uid = uc_user_register($user['uname'],$password,$username);
						add_ucenter_user_ref($user['uid'],$uc_uid,$user['uname']);
						$uc_user[0] = $uc_uid;
					}
				}
			}
		}else{
			if(UC_SYNC){
				$uc_user = uc_user_login($username,$password);
				if($uc_user[0]>0){
					$uc_user_ref = get_ucenter_user_ref('',$uc_user[0]);
					if(!$uc_user_ref){
						// 注册
						if($this->isValidEmail($uc_user['3']) && $this->isEmailAvailable($uc_user['3'])){
							$user['email'] = $uc_user['3'];
						}else{
							$message_data['title']   = '同步UCenter账号时，Email发送冲突';
							$message_data['content'] = '由于您在UCenter上的账号的Email已在本站使用，可到此地址 '.U('home/Account/bind').'#email 重新设置本站Email，可作为本站登陆账号。';
						}
						if ( isLegalUsername($uc_user['1']) && !M('user')->where("uname='{$uc_user['1']}'")->count())
							$user['uname'] = $uc_user['1'];
						$user['password']  = md5($uc_user['2']);
						$user['ctime']	   = time();
						$user['is_active'] = 1;
						$user['uid'] = M('user')->add($user);
						if ($user['uid']){
							$reg_from_ucenter = 1;
							add_ucenter_user_ref($user['uid'],$uc_user['0'],$uc_user['1']);

							// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
							$userlog = array(
								'uid'		=> $user['uid'],
								'action'	=> 'add',
								'type'		=> '0',
								'dateline'	=> time(),
							);
							M('myop_userlog')->add($userlog);

							if(isset($message_data) && !empty($message_data)){
								$message_data['to'] = $user['uid'];
								model('Message')->postMessage($message_data,  M('user')->getField('uid','admin_level=1'));
							}
							//关联操作
							//$this->registerRelation($user['uid']);
						}else{
							$user = array();
						}
					}else{
						if($username != $uc_user_ref['uc_username']){
							update_ucenter_user_ref('',$uc_user_ref['uc_uid'],$username);
						}
						$user = M('user')->where("uid={$uc_user_ref['uid']}")->find();
						if(md5($password) != $user['password']){
							M('user')->where("uid={$uc_user_ref['uid']}")->setField('password', md5($password));
						}
					}
				}
			}else{
				$uc_user_ref = get_ucenter_user_ref('','',$username);
				if($uc_user_ref['uid']){
					$user = service('Passport')->getLocalUser($uc_user_ref['uid'],$password);
				}
			}
		}

		if($user) {
			//检查是否激活
			if ($user['is_active'] == 0) {
				redirect(U('home/public/login',array('t'=>'unactive','email'=>$username,'uid'=>$user['uid'])));
				exit;
			}

			service('Passport')->registerLogin($user, intval($_POST['remember']) === 1);

			if(UC_SYNC && $reg_from_ucenter){
				//从UCenter导入ThinkSNS，跳转至帐号修改页
				$refer_url = U('home/Public/userinfo');
			}elseif ( $_SESSION['refer_url'] != '' ) {
				//跳转至登录前输入的url
				$refer_url	=	$_SESSION['refer_url'];
				unset($_SESSION['refer_url']);
			}else {
				$refer_url = U('home/User/index');
			}
			$this->assign('jumpUrl',$refer_url);
			$this->success($username.' 登录成功'. ( (UC_SYNC && $uc_user[0])?uc_user_synlogin($uc_user[0]):'' ));
		}else {
			$this->error('登录失败');
		}
	}

	public function logout() {
		service('Passport')->logoutLocal();
		$this->assign('jumpUrl',U('home/index'));
		$this->success('成功退出'. ( (UC_SYNC)?uc_user_synlogout():'' ) );
	}

	public function logoutAdmin() {
		// 成功消息不显示头部
		$this->assign('isAdmin','1');
		
		service('Passport')->logoutLocal();
		$this->assign('jumpUrl',U('home/Public/adminlogin'));
		$this->success('成功退出');
	}
	
	private function __getInviteInfo($invite_code)
	{
		$res = null;
		$invite_option = model('Invite')->getSet();
		switch (strtolower($invite_option['invite_set'])) {
			case 'close':
				$res = null;
				break;
			case 'common':
				$res = D('User', 'home')->getUserByIdentifier($invite_code, 'uid');
				break;
			case 'invitecode':
				$res = model('Invite')->checkInviteCode($invite_code);
				if ($res['is_used'])
					$res = null;
				break;
		}
		
		return $res;
	}

	public function register()
	{
		//验证码
		$opt_verify = model('Xdata')->get('siteopt:site_verify');
		if (in_array('register', $opt_verify))
			$this->assign('register_verify_on', 1);
		
		// 邀请码
		$invite_code = h($_REQUEST['invite']);
		$invite_info = null;
		
		// 是否开放注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option == 'closed') { // 关闭注册
			$this->error('抱歉: 本站已关闭注册');
			
		} else if ($register_option == 'invite') { // 邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error('抱歉: 邀请注册功能已关闭');
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error('抱歉: 目前仅接受邀请注册，请向已注册的用户索要邀请链接');
				else if (!($invite_info = $this->__getInviteInfo($invite_code)))
					$this->error('抱歉: 邀请码错误');
			}
		} else { // 公开注册
			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		}
		// 第三方平台
		$platform_options = model('Xdata')->lget('platform');
		$platforms = array();
		if (!empty($platform_options['sina_wb_akey']) && !empty($platform_options['sina_wb_skey']))
			$platforms[] = 'sina';
		if (!empty($platform_options['qq_key']) && !empty($platform_options['qq_secret']))
			$platforms[] = 'qq';
		if (!empty($platform_options['douban_key']) && !empty($platform_options['douban_secret']))
			$platforms[] = 'douban';
		$this->assign('platforms', $platforms);
		
		$this->assign('invite_info', $invite_info);
		$this->assign('invite_code', $invite_code);
		$this->setTitle('注册');
		$this->display();
	}

	public function doRegister()
	{
		// 验证码
		$verify_option = model('Xdata')->get('siteopt:site_verify');
		if (in_array('register', $verify_option) && md5($_POST['verify']) != $_SESSION['verify'])
			$this->error('验证码错误');
		
		// 邀请码
		$invite_code = h($_REQUEST['invite_code']);
		$invite_info = null;
		
		// 是否允许注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option === 'closed') { // 关闭注册
			$this->error('抱歉: 本站已关闭注册');
			
		} else if ($register_option === 'invite') { //邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error('邀请注册功能关闭');
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error('抱歉: 目前仅接受邀请注册，请向已注册的用户索要邀请链接');
				else if (!($invite_info = $this->__getInviteInfo($invite_code)))
					$this->error('抱歉: 邀请码错误');
			}
		} else { // 公开注册
			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		}
		
		// 参数合法性检查
		$required_field = array(
			'email'		=> 'Email',
			'nickname'  => '用户名',
			'password'	=> '密码',
			'repassword'=> '密码',
		);
		foreach ($required_field as $k => $v)
			if (empty($_POST[$k]))
				$this->error($v . '不可为空');

		if (!$this->isValidEmail($_POST['email']))
			$this->error('Email格式错误，请重新输入');
		if (!$this->isValidNickName($_POST['nickname']))
			$this->error('用户名不符合要求或已被使用');
		if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'])
			$this->error('密码必须为6-16位，且两次必须相同');
		if (!$this->isEmailAvailable($_POST['email']))
			$this->error('Email已经被使用，请重新输入');

		// 是否需要Email激活
		$need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));

		// 注册
		$data['email']     = $_POST['email'];
		$data['password']  = md5($_POST['password']);
		$data['uname']	   = t($_POST['nickname']);
		$data['ctime']	   = time();
		$data['is_active'] = $need_email_activate ? 0 : 1;
		if (!($uid = D('User', 'home')->add($data)))
			$this->error('抱歉: 注册失败，请稍后重试');

		// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
		$user_log = array(
			'uid'		=> $uid,
			'action'	=> 'add',
			'type'		=> '0',
			'dateline'	=> time(),
		);
		M('myop_userlog')->add($user_log);

		// 将邀请码设置已用
		model('Invite')->setInviteCodeUsed($invite_code);
		
		// 同步至UCenter
		if (UC_SYNC) {
			$uc_uid = uc_user_register($_POST['nickname'],$_POST['password'],$_POST['email']);
			//echo uc_user_synlogin($uc_uid);
			if ($uc_uid > 0)
				add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
		}

		if ($need_email_activate == 1) { // 邮件激活
			$this->activate($uid, $_POST['email'], $invite_code);
		} else {
			// 置为已登陆, 供完善个人资料时使用
			service('Passport')->loginLocal($uid);
			
			// 缓存邀请信息, 供完善个人资料后使用
			$_SESSION["invite_info_{$uid}"] = $invite_info;

			if (!is_numeric(stripos($_POST['HTTP_REFERER'], dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])))) {
                //注册完毕，跳回注册页之前
                redirect($_POST['HTTP_REFERER']);
			} else {
				//注册完毕，跳转至帐号修改页
				redirect(U('home/Public/userinfo'));
			}
		}
	}

	// 完善个人资料
	public function userinfo()
	{
		if (!$this->mid)
			redirect(U('home/Public/login'));
			
		// 已初始化的用户, 不允许在此修改资料
		global $ts;
		if ($this->mid && $ts['user']['is_init'])
			redirect(U('home/User/index'));
		
		if ($_POST) {
			$user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
			if (!$user_info['uname']) {
				if (!$this->isValidNickName($_POST['nickname']))
					$this->error('昵称格式不正确或已被使用');
				else
					$data['uname'] = $_POST['nickname'];
			}

			$data['sex']   	  = intval($_POST['sex']);
			$data['province'] = intval($_POST['area_province']);
			$data['city']     = intval($_POST['area_city']);
			$data['location'] = getLocation($data['province'], $data['city']);
			$data['is_init']  = 1;
			M('user')->where("uid={$this->mid}")->data($data)->save();
			
			// 关联操作
			$this->registerRelation($this->mid, $_SESSION["invite_info_{$this->mid}"]);
			unset($_SESSION["invite_info_{$this->mid}"]);

			redirect(U('home/Public/followuser'));
		} else {
			$user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
			$this->assign('nickname', $user_info['uname']);
			$this->setTitle('完善个人资料');
			$this->display();
		}
	}

	//关注推荐用户
	public function followuser()
	{
		if ($_POST) {
			if ($_POST['followuid']) {
				foreach ($_POST['followuid'] as $value) {
					D('Follow','weibo')->dofollow($this->mid,$value,0);
				}
			}
			if ($_POST['doajax']) {
				echo '1';
			} else {
				redirect(U('home/user/index'));
			}
		} else {
			$users      = D('Follow', 'weibo')->getTopFollowerUser($this->mid, 12);
			$user_model = D('User', 'home');
			$user_model->setUserObjectCache(getSubByKey($users, 'uid'));
			foreach ($users as $k => $v) {
				$user = $user_model->getUserByIdentifier($v['uid'], 'uid');
				$users[$k]['uname']    = $user['uname'];
				$users[$k]['location'] = $user['location'];
			}
			
			$this->assign('users', $users);
			$this->setTitle('推荐用户');
			$this->display();
		}
	}

	//使用邀请码注册
	public function inviteRegister() {
		if ( ! $invite = service('Validation')->getValidation() ) {
			$this->error('邀请码错误');
		}

		if ( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] != $invite['target_url'] ) {
			$this->error('URL错误');
		}
		$this->assign('invite', $invite);
		
		$invite['data']			= unserialize($invite['data']);
		$map['tpl_record_id']	= $invite['data']['tpl_record_id'];
		$tpl_record 			= model('Template')->getTemplateRecordByMap($map, '', 1);
		$tpl_record 			= $tpl_record['data'][0]['data'];
		$this->assign('template', $tpl_record);

		//邀请人的好友
		$friend = model('Friend')->getFriendList($invite['from_uid'], null, 9);
		$this->assign($friend);
		
		$this->display('invite');
	}

	public function resendEmail() {
		$invite = service('Validation')->getValidation();
		$this->activate(intval($_GET['uid']), $_GET['email'], $invite, 1);
	}

	//发送激活邮件
	public function activate($uid, $email, $invite = '', $is_resend = 0) {
		//设置激活路径
		$activate_url  = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));
		if ($invite) {
			$this->assign('invite', $invite);
		}
		$this->assign('url',$activate_url);

		//设置邮件模板
		$body = <<<EOD
感谢您的注册!<br>

请马上点击以下注册确认链接，激活您的帐号！<br>

<a href="$activate_url" target='_blank'>$activate_url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
		// 发送邮件
		global $ts;
		$email_sent = service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号",$body);
		
		// 渲染输出
		if ($email_sent) {
			$email_info = explode("@", $email);
			switch ($email_info[1]) {
				case "qq.com"    : $email_url = "mail.qq.com";break;
				case "163.com"   : $email_url = "mail.163.com";break;
				case "126.com"   : $email_url = "mail.126.com";break;
				case "gmail.com" : $email_url = "mail.google.com";break;
				default          : $email_url = "mail.".$email_info[1];
			}
			
			$this->assign("uid",$uid);
			$this->assign('email', $email);
			$this->assign('is_resend', $is_resend);
			$this->assign("email_url",$email_url);
			$this->display('activate');
		}else {
			$this->assign('jumpUrl', U('home/Index/index'));
			$this->error('抱歉: 邮件发送失败，请稍后重试');
		}
	}
	
	public function doActivate() {
		$invite = service('Validation')->getValidation();
		if (!$invite) {
			$this->assign('jumpUrl', U('home/Public/register'));
        	$this->error('抱歉: 激活码错误，请重新注册');
		}
		$uid = $invite['from_uid'];
        
        $user = M('user')->where("`uid`=$uid")->find();
        if ($user['is_active'] == 1) {
        	$this->assign('jumpUrl', U('home/Public/login'));
        	$this->success('您的帐号已激活');
        	exit;
        } else if ($user['is_active'] == 0) {
        	//激活帐户
        	$res = M('user')->where("`uid`=$uid")->setField('is_active', 1);
        	if (!$res) $this->error('抱歉: 激活失败');
        	
			service('Passport')->registerLogin($user);
			
			//关联操作
			$this->registerRelation($user['uid'], $invite);
			
			service('Validation')->unsetValidation();

			$this->assign('jumpUrl', U('home/Account/index'));
			$this->success("恭喜: 激活成功");
        } else {
        	$this->assign('jumpUrl', U('home/Public/register'));
        	$this->error('抱歉: 激活码错误，请重新注册');
        }
	}
	
	public function sendPassword() {
		$this->display();
	}
	
	public function doSendPassword() {
		$_POST["email"]	= t($_POST["email"]);
		if ( !$this->isValidEmail($_POST['email']) )
			$this->error('邮箱格式错误');
		
		$user =	M("user")->where('`email`="' . $_POST['email'] . '"')->find();
		
        if(!$user) {
        	$this->error("该邮箱没有注册");
        }else {
            $code = base64_encode( $user["uid"] . "." . md5($user["uid"] . '+' . $user["password"]) );
            $url  = U('home/Public/resetPassword', array('code'=>$code));
            $body = <<<EOD
<strong>{$user["uname"]}，你好: </strong><br/>

您只需通过点击下面的链接重置您的密码: <br/>

<a href="$url">$url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
			
			global $ts;
			$email_sent = service('Mail')->send_email($user['email'], "重置{$ts['site']['site_name']}密码", $body);
			
            if ($email_sent) {
	            $this->assign('jumpUrl', SITE_URL);
	            $this->success("已把密码发送到你的邮箱$email中，请注意查收");
            }else {
            	$this->error('抱歉: 邮件发送失败，请稍好重试');
            }
		}
	}
	
	public function resetPassword() {
		$code = explode('.', base64_decode($_GET['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();
        
        if ( $code[1] == md5($code[0].'+'.$user["password"]) ) {
	        $this->assign('email',$user["email"]);
	        $this->assign('code', $_GET['code']);
	        $this->display();
        }else {
        	$this->error("抱歉: 链接错误");
        }
	}
	
	public function doResetPassword() {
		if($_POST["password"] != $_POST["repassword"]) {
        	$this->error("输入的两次密码必须一致，请重新输入");
        }
        
		$code = explode('.', base64_decode($_POST['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();
        
        if ( $code[1] == md5($code[0] . '+' . $user["password"]) ) {
	        $user['password'] = md5($_POST['password']);
	        $res = M('user')->save($user);
	        if ($res) {
	        	$this->assign('jumpUrl', U('home/Public/login'));
	        	$this->success('保存成功');
	        }else {
	        	$this->error('抱歉: 保存失败，请稍后重试');
	        }
        }else {
        	$this->error("抱歉: 安全码错误");
        }
	}
	
	public function doModifyEmail() {
    	if ( !$validation = service('Validation')->getValidation() ) {
    		$this->error('验证码错误');
    	}
    	if ( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] != $validation['target_url'] ) {
    		$this->error('URL错误');
		}
    	
    	$validation['data'] = unserialize($validation['data']);
    	$map['uid']			= $validation['from_uid'];
    	$map['email']		= $validation['data']['oldemail'];
		if ( M('user')->where($map)->setField('email', $validation['data']['email']) ) {
			service('Validation')->unsetValidation();
			service('Passport')->logoutLocal();
			$this->assign('jumpUrl', SITE_URL);
			$this->success('激活新Email成功，请重新登录');
		}else {
			$this->error('抱歉: 激活新Email失败');
		}
    }
	
	//检查Email地址是否合法
	public function isValidEmail($email) {
		if(UC_SYNC){
			$res = uc_user_checkemail($email);
			if($res == -4){
				return false;
			}else{
				return true;
			}
		}else{
			return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
		}
	}

	//检查Email是否可用
	public function isEmailAvailable($email = null) {
		$return_type = empty($email) ? 'ajax' 		   : 'return';
		$email		 = empty($email) ? $_POST['email'] : $email;

		$res = M('user')->where('`email`="'.$email.'"')->find();
		if(UC_SYNC){
			$uc_res = uc_user_checkemail($email);
			if($uc_res == -5 || $uc_res == -6){
				$res = true;
			}
		}

		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo '邮箱已被占用';
			else return false;
		}
	}
	
	//检查昵称是否符合规则，且是否为唯一
	
	public function isValidNickName($name)
	{
		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['nickname']) 	: $name;

		if (UC_SYNC) {
			$uc_res = uc_user_checkname($name);
			if($uc_res == -1 || !isLegalUsername($name)){
				if ($return_type === 'ajax') { echo '3-10位的中英文、数字、下划线、中划线';return; }
				else return false;
			}
		} else if (!isLegalUsername($name)) {
			if ($return_type === 'ajax') { echo '2-10位的中英文、数字、下划线、中划线';return; }
			else return false;
		}

		if ($this->mid) {
			$res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
			$nickname = M('user')->getField('uname',"uid={$this->mid}");
			if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
				$res = 1;
			}
		} else {
			$res = M('user')->where("uname='{$name}'")->count();
			if(UC_SYNC && ($uc_res == -2 || $uc_res == -3)){
				$res = 1;
			}
		}

		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo '此用户名已被使用';
			else return false;
		}
	}
	
	//检查是否真实姓名。支持ajax和return
	public function isValidRealName($name = null, $opt_register = null) {
		$return_type  = empty($name) 		 ? 'ajax' 							: 'return';
		$name		  = empty($name) 		 ? t($_POST['uname']) 				: $name;
		$opt_register = empty($opt_register) ? model('Xdata')->lget('register') : $opt_register;
		
		if ($opt_register['register_realname_check'] == 1) {
			$lastname = explode(',', $opt_register['register_lastname']);
			$res = in_array( substr($name, 0, 3), $lastname ) || in_array( substr($name, 0, 6), $lastname );
		}else {
			$res = true;
		}
		
		if ($res) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo 'fail';
			else return false;
		}
	}
	
	// 注册的关联操作
    public function registerRelation($uid, $invite_info = null)
    {
    	if (($uid = intval($uid)) <= 0)
    		return;

    	$dao = D('Follow','weibo');
    	
    	// 使用邀请码时, 建立与邀请人的关系
    	if ($invite_info['uid']) {
    		// 互相关注
    		D('Follow', 'weibo')->dofollow($uid, $invite_info['uid']);
			D('Follow', 'weibo')->dofollow($invite_info['uid'], $uid);
			
			// 添加邀请记录
			model('InviteRecord')->addRecord($invite_info['uid'], $uid);
			
			//邀请人积分操作
			X('Credit')->setUserCredit($invite_info['uid'], 'invite_friend');
    	}
		
        // 默认关注的好友
		$auto_freind = model('Xdata')->lget('register');
		$auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
		foreach($auto_freind['register_auto_friend'] as $v) {
			if (($v = intval($v)) <= 0)
				continue ;
			$dao->dofollow($v, $uid);
			$dao->dofollow($uid, $v);
		}
        
		// 开通个人空间
		$data['uid'] = $uid;
		model('Space')->add($data);

		//注册成功 初始积分
		X('Credit')->setUserCredit($uid,'init_default');
	}
	
	public function verify() {
        require_once(SITE_PATH.'/addons/libs/Image.class.php');
        require_once(SITE_PATH.'/addons/libs/String.class.php');
    	Image::buildImageVerify();
	}
	
    //上传图片
    public function uploadpic(){
    	if( $_FILES['pic'] ){
    		//执行上传操作
    		$savePath =  $this->getSaveTempPath();
    		$filename = md5( time().'teste' ).'.'.substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'],'.')+1);
	    	if(@copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename)) 
	        {
	        	$result['boolen']    = 1;
	        	$result['type_data'] = 'temp/'.$filename;
	        	$result['picurl']    = __UPLOAD__.'/temp/'.$filename;
	        } else {
	        	$result['boolen']    = 0;
	        	$result['message']   = '上传失败';
	        }
    	}else{
        	$result['boolen']    = 0;
        	$result['message']   = '上传失败';
    	}
    	
    	exit( json_encode( $result ) );
    }
    
    //上传临时文件
	public function getSaveTempPath(){
        $savePath = SITE_PATH.'/data/uploads/temp';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }
    
    // 地区管理
    public function getArea() {
    	echo json_encode(model('Area')->getAreaTree());
    }
    
	/**  文章  **/
	public function document() {
		$list	= array();
		$detail = array();
		$res    = M('document')->where('`is_active`=1')->order('`display_order` ASC,`document_id` ASC')->findAll();

		// 获取content为url且在页脚显示的文章
		global $ts;
		$ids_has_url = array();
		foreach($ts['footer_document'] as $v)
			if( !empty($v['url']) )
				$ids_has_url[] = $v['document_id'];

		$_GET['id'] = intval($_GET['id']);

		foreach($res as $v) {
			// 不显示content为url且在页脚显示的文章
			if ( in_array($v['document_id'], $ids_has_url) )
				continue ;

			$list[] = array('document_id'=>$v['document_id'], 'title'=>$v['title']);
			
			// 当指定ID，且该ID存在，且该文章的内容不是url时，显示指定的文章。否则显示第一篇
			if ( $v['document_id'] == $_GET['id'] || empty($detail) ) {
				$v['content'] = htmlspecialchars_decode($v['content']);
				$detail = $v;
				$title = $v['title'];//仿知美二次开发
			}
		}
		unset($res);

		$this->assign('detail', $detail);
		$this->assign('list', $list);
		$this->setTitle($title);
		$this->display();
	}
	
	public function toWap() {
		$_SESSION['wap_to_normal'] = '0';
		cookie('wap_to_normal', '0', 3600*24*365);
		U('wap', '', true);
	}
	
	public function fetchNew()
	{
		$map['weibo_id']	 = array('gt', intval($_POST['since_id']));
		$map['transpond_id'] = 0;
		$map['type']		 = 0;
		$res = D('Weibo', 'weibo')->where($map)->order('weibo_id DESC')->find();
		if ($res) {
			$res['uname'] = getUserSpace($res['uid'], '', '_blank', '{uname}');
			$res['user_pic']	  = getUserSpace($res['uid'], '', '_blank', '{uavatar=m}');
			$res['friendly_date'] = friendlyDate($res['ctime']);
			$res['content']		  = login_emot_format(getShort($res['content'], 60));
			echo json_encode($res);
		} else {
			echo 0;
		}
	}
	
	public function error404() {
		$this->display('404');
	}
}
