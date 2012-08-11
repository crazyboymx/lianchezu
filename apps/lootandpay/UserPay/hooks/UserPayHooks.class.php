<?php

class UserPayHooks extends Hooks {
    public function init() {

    }//插件后台配置项
	public function payConf() {
		if (! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->error('您无权限查看');
		}
		$payConf = model('Xdata')->lget('UserPay');
		$this->assign($payConf);
		$this->display('payConf');
	}
	//充值管理
	public function payAdmin() {
		if (! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->error('您无权限查看');
		}
		$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
		$db_preifx =  C('DB_PREFIX');
		$list=M()->query("SELECT SUM(amount) AS sum,gateway FROM {$db_preifx}user_pay WHERE ( `issucess` = 1 ) GROUP BY gateway order by gateway");
		foreach ($list as $k => $v) {
			$list[$k]['gateway']=$gateway[$v['gateway']];
			$list[$k]['scale']=sprintf('%.2f%%',$v['sum']/M('user_pay')->where("`issucess` = 1")->sum('amount')*100);
		}
		
		$issucess=array('0'=>'还未付款','1'=>'已经付款');
		$tosucess=array('0'=>'还未到账','1'=>'已经到账');
		$res=M('user_pay')->order('id desc')->findpage(10);
		foreach ($res['data'] as $k => $v) {
			$res['data'][$k]['issucess']=$issucess[$v['issucess']];
			$res['data'][$k]['tosucess']=$tosucess[$v['tosucess']];
			$res['data'][$k]['gateway']=$gateway[$v['gateway']];
		}
		$payed=M('user_pay')->where("issucess=1 ")->sum('amount');
		$accounted=M('user_pay')->where("issucess=1  AND tosucess=1")->sum('amount');
		$payed_noaccount=M('user_pay')->where("issucess=1  AND tosucess=0")->sum('amount');
		$all_nopay=M('user_pay')->where("issucess=0  AND tosucess=0")->sum('amount');
		$this->assign("payed",$payed);
		$this->assign("accounted",$accounted);
		$this->assign("payed_noaccount",$payed_noaccount);
		$this->assign("all_nopay",$all_nopay);
		$this->assign("paymap",$paymap);
		$this->assign("res",$res);
		$this->assign("list",$list);
		$this->display('payAdmin');
	}
	//查询
	public function searchPay() {
		if (! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->error('您无权限查看');
		}
		if ( !empty($_POST) ) {
			$_SESSION['admin_searchPay'] = serialize($_POST);
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['admin_searchPay']);
		}else {
			unset($_SESSION['admin_searchPay']);
		}
		$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
		$issucess=array('0'=>'还未付款','1'=>'已经付款');
		$tosucess=array('0'=>'还未到账','1'=>'已经到账');
		if($_POST['st']!=null && $_POST['sj']!=null){
			if($_POST['sj']>=$_POST['st']){
			$paymap['date']=array(array('egt',$_POST['st']),array('elt',$_POST['sj']));
			}
		}
		$_POST['billno']&&$paymap['billno']=$_POST['billno'];
		$_POST['uid']&&$paymap['uid']=$_POST['uid'];
		if($_POST['sucess_is']=='all'){
				$paymap['issucess']=1;
			}elseif($_POST['sucess_is']==1){
				$paymap['issucess']=1;
				$paymap['tosucess']=1;
			}else{
				$paymap['issucess']=1;
				$paymap['tosucess']=0;		
		}
		$res=M('user_pay')->where($paymap)->order('id desc')->findpage(10);
		foreach ($res['data'] as $k => $v) {
			$res['data'][$k]['issucess']=$issucess[$v['issucess']];
			$res['data'][$k]['tosucess']=$tosucess[$v['tosucess']];
			$res['data'][$k]['gateway']=$gateway[$v['gateway']];
		}
		$paymap['sucess_is']=$_POST['sucess_is'];
		$this->assign("paymap",$paymap);
		$this->assign("res",$res);
		$this->display("searchPay");
	}
	//数据导出
	 public function payexport(){ 
	   if (! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->error('您无权限查看');
		}
	   $gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
	   $issucess=array('0'=>'还未付款','1'=>'已经付款');
	   $tosucess=array('0'=>'还未到账','1'=>'已经到账');
				if($_GET['sucess_is']!=null){
				if($_GET['sucess_is']=='all'){
					$paymap['issucess']=1;
				}elseif($_GET['sucess_is']==0){
					$paymap['issucess']=1;
					$paymap['tosucess']=0;
				}else{
					$paymap['issucess']=1;
					$paymap['tosucess']=1;
				}
				}
				$_GET['billno']&&$paymap['billno']=$_GET['billno'];
	  			$_GET['uid']&&$paymap['uid']=$_GET['uid'];
				$_GET['date']&&$paymap['date']=$_GET['date'];
				$res=M('user_pay')->where($paymap)->order('id desc')->select();
			   foreach ($res as $k => $v) {
					$res[$k]['issucess']=$issucess[$v['issucess']];
					$res[$k]['tosucess']=$tosucess[$v['tosucess']];
					$res[$k]['gateway']=$gateway[$v['gateway']];
				}

		$time = "充值数据".date('Y-m-d H：i：s');
	  	$time=mb_convert_encoding($time,"gb2312","utf-8");
	   	header("Content-Type: application/vnd.ms-execl;charset=gb2312");
		header("Content-Disposition: attachment; filename=$time.xls");    //导出为excel的格式
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->assign('res',$res);
        $this->display('format');	
	}
	//配置保存
	function dopayConf(){
		if (! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->error('您无权限查看');
		}
		$_POST['gateway_0']=$_POST['gateway_0']?$_POST['gateway_0']:0;
		$_POST['gateway_1']=$_POST['gateway_1']?$_POST['gateway_1']:0;
		$_POST['gateway_2']=$_POST['gateway_2']?$_POST['gateway_2']:0;
		$_POST['gateway_3']=$_POST['gateway_3']?$_POST['gateway_3']:0;
		if($_POST){
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '3';
			$data[] = '充值配置 - 配置 ';
			$data[] = model('Xdata')->lget('UserPay', $paymap);
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$data[] = $_POST;
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			model('Xdata')->lput('UserPay', $_POST);
		}
		$this->success("配置成功");
	}
	
    public function home_account_tab($param) {
        $param['menu'][] = array(
            'act' => 'userpay',
            'name' => '积分充值',
            'param' => array(
                'addon' => 'UserPay',
                'hook' => 'home_account_show'
            )
        );
    }
	
	 public function header_account_tab($param) {
        $param['menu'][] = array(
            'url' => U('home/Account/userpay',array('addon' => 'UserPay','hook' => 'home_account_show')),
            'name' => '积分充值',
			'act' => 'userpay',
        );
    }
	public function header_topnav($param) {
        $param['menu'][] = array(
            'url' => U('loot/Index/index'),
            'name' => '抢星位',
			'act' => 'userpay',
        );
    }

    public function home_account_show() {
        if ('userpay' != ACTION_NAME) {
            return;
        }
		$UserPay     = model('Xdata')->lget('UserPay');
		$userscore   =M('credit_user')->where('uid='.$this->mid)->getField('score');
		$this->assign('userscore',$userscore);
		$this->assign('UserPay',$UserPay);
        $this->assign('data',$data);
		//充值记录查询
		$paylist=M('user_pay')->where("issucess!=0 AND uid=".$this->mid)->order('id desc')->findall();
		$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
		$tosucess=array('0'=>'未到账','1'=>'充值成功');
		foreach ($paylist as $k => $v) {
			$paylist[$k]['gateway']=$gateway[$v['gateway']];
			$paylist[$k]['tosucess']=$tosucess[$v['tosucess']];
			$paylist[$k]['date']=date("Y-m-d",strtotime($v['date']));
		}
		$this->assign("paylist",$paylist);
        $this->display('home_userpay_show');
    }
	//充值数据写入数据库
	public function pay_add(){
		$paymap['uid']=$this->mid;
		$paymap['billno']=date('YmdHis') . mt_rand(100000,999999);
		$paymap['date']=date('Ymd');
		$paymap['amount']= intval($_POST['pay_amount']);
		$arr_gateway=array("0", "1", "2", "3");
		if(in_array($_POST['gateway'], $arr_gateway)){ 
			$paymap['gateway']=intval($_POST['gateway']);
		}else{
			echo '01';//表示失败
			exit;
		}
		$res=M('user_pay')->add($paymap);
		if($res){//成功输出订单号
			echo $paymap['billno'];
		}else{
			echo '01';//表示失败
		}
    }
	//充值信息确认显示
	public function pay_confirm(){
		$paymap['billno']=$_GET['billno'];
		$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
		$res=M('user_pay')->where($paymap)->find();
		$res['Gateway']=$gateway[$res['gateway']];
		$UserPay     = model('Xdata')->lget('UserPay');
		if($UserPay['itest']==1){//正式
			$res['form_url']= 'https://pay.ips.com.cn/ipayment.aspx';
			$res['Mer_code']= $UserPay['Mer_code'];
			$res['Mer_key']=  $UserPay['Mer_key'];
		}else{//测试
			$res['form_url']= 'http://pay.ips.net.cn/ipayment.aspx';
			$res['Mer_code']= '000015';
			$res['Mer_key']='GDgLwwdK270Qj1w4xho8lyTpRQZV9Jm5x4NwWOTThUa4fMhEBK9jOXFrKRT6xhlJuU2FEa89ov0ryyjfJuuPkcGzO5CeVx5ZIrkkt1aBlZV36ySvHOMcNv8rncRiy3DQ';
		}
		$res['Amount']= number_format($res['amount'], 2, '.', '');
		$res['Currency_Type']= 'RMB';
		$res['Gateway_Type']= '01';
		$res['Lang']= 'GB';
		$res['Merchanturl']= U('home/Widget/addonsRequest',array('addon'=>'UserPay','hook'=>'OrderReturn'));
		$res['attach']= $res['id'];
		$res['OrderEncodeType']= 2;
		$res['RetEncodeType']= 12;
		$res['Rettype']= 1;
		$res['ServerUrl']= U('home/Widget/addonsRequest',array('addon'=>'UserPay','hook'=>'OrderReturnForCheckAccount'));
		$res['SignMD5'] = md5($res['billno'] . $res['Amount'] . $res['date'] . $res['Currency_Type'] . $res['Mer_key']);
		$this->assign("data",$res);
		$this->assign("UserPay",$UserPay);
		$this->display('pay_confirm');
	}
	//浏览器返回
	public function OrderReturn(){
		$UserPay     = model('Xdata')->lget('UserPay');
		header("Content-type:text/html; charset=utf-8"); 
		//----------------------------------------------------
		//  接收数据
		//  Receive the data
		//----------------------------------------------------
		$billno = $_GET['billno'];
		$amount = $_GET['amount'];
		$mydate = $_GET['date'];
		$succ = $_GET['succ'];
		$msg = $_GET['msg'];
		$attach = $_GET['attach'];
		$ipsbillno = $_GET['ipsbillno'];
		$retEncodeType = $_GET['retencodetype'];
		$currency_type = $_GET['Currency_type'];
		$signature = $_GET['signature'];
		
		//'----------------------------------------------------
		//'   Md5摘要认证
		//'   verify  md5
		//'----------------------------------------------------
		$content = $billno . $amount . $mydate . $succ . $ipsbillno . $currency_type;
		//请在该字段中放置商户登陆merchant.ips.com.cn下载的证书
		if($UserPay['itest']==1){//正式
			$cert=  $UserPay['Mer_key'];
		}else{//测试
			$cert='GDgLwwdK270Qj1w4xho8lyTpRQZV9Jm5x4NwWOTThUa4fMhEBK9jOXFrKRT6xhlJuU2FEa89ov0ryyjfJuuPkcGzO5CeVx5ZIrkkt1aBlZV36ySvHOMcNv8rncRiy3DQ';
		}
		$signature_1ocal = md5($content . $cert);
		
		if ($signature_1ocal == $signature)
		{
			//----------------------------------------------------
			//  判断交易是否成功
			//  See the successful flag of this transaction
			//----------------------------------------------------
			if ($succ == 'Y'){
				$paymap['id']=$attach;
				$res=M('user_pay')->where($paymap)->find();
				$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
				$res['Gateway']=$gateway[$res['gateway']];
				/**----------------------------------------------------
				*比较返回的订单号和金额与您数据库中的金额是否相符
				*compare the billno and amount from ips with the data recorded in your datebase
				*----------------------------------------------------
				**/
				if($res['amount']==$amount&&$res['billno']==$billno){
						if($res['issucess']==0){
							$ipsmap['issucess']=1;
							$ipsmap['ipsbillno']=$ipsbillno;
							$issucess=M('user_pay')->where($paymap)->save($ipsmap);
							if($issucess){
								$tosucess=M('credit_user')->setInc('score','uid='.$this->mid,$amount*$UserPay['scale']); 
									if($tosucess){
										$yes['tosucess']=1;
										$issucess=M('user_pay')->where($paymap)->save($yes);
										//充值成功缓存操作
										$creditUser    =M('credit_user')->where("uid={$this->mid}")->find();
										$userLoginInfo = S('S_userInfo_'.$this->mid);
										if(!empty($userLoginInfo)) {
											$userLoginInfo['credit']['score']['credit'] = $creditUser['score'];
											$userLoginInfo['credit']['experience']['credit'] = $creditUser['experience'];
											S('S_userInfo_'.$this->mid, $userLoginInfo);
										}
										$con['title']="充值成功！";
										$con['content']="充值成功！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
										$con['to'] = $this->mid;
										$resto = false;
										// 站内信
										if( $con['title'] && $con['content'] ){
											$resto = model('Message')->postMessage($con, 1);
											$resto = !empty($res);
										}
										$this->display();
									}else{
										$con['title']="付款成功，但充值失败！";
										$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
										$con['to'] = $this->mid;
										$resto = false;
										// 站内信
										if( $con['title'] && $con['content'] ){
											$resto = model('Message')->postMessage($con, 1);
											$resto = !empty($res);
										}
										$this->display();
									}
								exit;
							}else{
									$con['title']="付款成功，但充值失败！";
									$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
									// 收件人
									$con['to'] = $this->mid;
									$resto = false;
									// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);
									}
									$this->display();
								exit;
							}
						}else{
								if($res['tosucess']==0){
									$con['title']="付款成功，但充值失败！";
									$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
									$con['to'] = $this->mid;
									$resto = false;
										// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);
									}
									$this->display();
									exit;
								}else{
									$con['title']="充值成功！";
									$con['content']="充值成功！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
									// 收件人
									$con['to'] = $this->mid;
									$resto = false;
									// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);
									}
									$this->display();
									exit;
								}
						}
				}else{
					echo ('从IPS返回的数据和本地记录的不符合，失败！');
					$this->display();
					exit;
				}
			}
			else
			{
				echo ('交易失败');
				$this->display();
				exit;
			}
		}
		else
		{
			echo('签名不正确');
			$this->display();
			exit;
		}
	}
	//服务器返回
	public function OrderReturnForCheckAccount(){
		$UserPay     = model('Xdata')->lget('UserPay');
		header("Content-type:text/html; charset=utf-8"); 
		//----------------------------------------------------
		//  接收数据
		//  Receive the data
		//----------------------------------------------------
		$billno = $_GET['billno'];
		$amount = $_GET['amount'];
		$mydate = $_GET['date'];
		$succ = $_GET['succ'];
		$msg = $_GET['msg'];
		$attach = $_GET['attach'];
		$ipsbillno = $_GET['ipsbillno'];
		$retEncodeType = $_GET['retencodetype'];
		$currency_type = $_GET['Currency_type'];
		$signature = $_GET['signature'];
		
		//'----------------------------------------------------
		//'   Md5摘要认证
		//'   verify  md5
		//'----------------------------------------------------
		$content = $billno . $amount . $mydate . $succ . $ipsbillno . $currency_type;
		//请在该字段中放置商户登陆merchant.ips.com.cn下载的证书
		if($UserPay['itest']==1){//正式
			$cert=  $UserPay['Mer_key'];
		}else{//测试
			$cert='GDgLwwdK270Qj1w4xho8lyTpRQZV9Jm5x4NwWOTThUa4fMhEBK9jOXFrKRT6xhlJuU2FEa89ov0ryyjfJuuPkcGzO5CeVx5ZIrkkt1aBlZV36ySvHOMcNv8rncRiy3DQ';
		}
		$signature_1ocal = md5($content . $cert);
		
		if ($signature_1ocal == $signature)
		{
			//----------------------------------------------------
			//  判断交易是否成功
			//  See the successful flag of this transaction
			//----------------------------------------------------
			if ($succ == 'Y'){
				$paymap['id']=$attach;
				$res=M('user_pay')->where($paymap)->find();
				$gateway=array('0'=>'网银充值','1'=>'神州行充值','2'=>'联通卡充值','3'=>'电信卡支付');
				$res['Gateway']=$gateway[$res['gateway']];
				/**----------------------------------------------------
				*比较返回的订单号和金额与您数据库中的金额是否相符
				*compare the billno and amount from ips with the data recorded in your datebase
				*----------------------------------------------------
				**/
				if($res['amount']==$amount&&$res['billno']==$billno){
						if($res['issucess']==0){
							$ipsmap['issucess']=1;
							$ipsmap['ipsbillno']=$ipsbillno;
							$issucess=M('user_pay')->where($paymap)->save($ipsmap);
							if($issucess){
								$tosucess=M('credit_user')->setInc('score','uid='.$this->mid,$amount*$UserPay['scale']); 
									if($tosucess){
										$yes['tosucess']=1;
										$issucess=M('user_pay')->where($paymap)->save($yes);
										//充值成功缓存操作
										$creditUser    =M('credit_user')->where("uid={$this->mid}")->find();
										$userLoginInfo = S('S_userInfo_'.$this->mid);
										if(!empty($userLoginInfo)) {
											$userLoginInfo['credit']['score']['credit'] = $creditUser['score'];
											$userLoginInfo['credit']['experience']['credit'] = $creditUser['experience'];
											S('S_userInfo_'.$this->mid, $userLoginInfo);
										}
										$con['title']="充值成功！";
										$con['content']="充值成功！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
										$con['to'] = $this->mid;
										$resto = false;
										// 站内信
										if( $con['title'] && $con['content'] ){
											$resto = model('Message')->postMessage($con, 1);
											$resto = !empty($res);
										}
										echo "ipscheckok";
									}else{
										$con['title']="付款成功，但充值失败！";
										$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
										$con['to'] = $this->mid;
										$resto = false;
										// 站内信
										if( $con['title'] && $con['content'] ){
											$resto = model('Message')->postMessage($con, 1);
											$resto = !empty($res);
										}
									}
								exit;
							}else{
									$con['title']="付款成功，但充值失败！";
									$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
									// 收件人
									$con['to'] = $this->mid;
									$resto = false;
									// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);

									}
								exit;
							}
						}else{
								if($res['tosucess']==0){
									$con['title']="付款成功，但充值失败！";
									$con['content']="付款成功，但充值失败！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
										// 收件人
									$con['to'] = $this->mid;
									$resto = false;
										// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);
									}
									exit;
								}else{
									$con['title']="充值成功！";
									$con['content']="充值成功！去查看>>".U('home/Account/userpay')."&addon=UserPay&hook=home_account_show";
									// 收件人
									$con['to'] = $this->mid;
									$resto = false;
									// 站内信
									if( $con['title'] && $con['content'] ){
										$resto = model('Message')->postMessage($con, 1);
										$resto = !empty($res);
									}
									echo "ipscheckok";
									exit;
								}
						}
				}else{
					echo ('从IPS返回的数据和本地记录的不符合，失败！');
					exit;
				}
			}
			else
			{
				echo ('交易失败');
				exit;
			}
		}
		else
		{
			echo('签名不正确');
			exit;
		}
	}
}