<?php

class UserThemeHooks extends Hooks {
    public function init() {

    }//插件后台配置项
	public function themeAdmin() {
		$this->assign('listtheme', M('all_theme')->findall());
		$this->display('listtheme');
	}
	//添加包显示
	public function addtheme() {
		if(!empty($_GET['id'])){
			$data=M('all_theme')->where('id='.$_GET['id'])->find();
			$this->assign($data);
		}
		$this->display('addtheme');
	}
	//添加、编辑操作
	public function doaddtheme() {
		$data['theme_name'] = t($_POST['theme_name']);
		$data['title'] = t($_POST['title']);
		$data['info'] = t($_POST['info']);
		//$data['icon'] = t($_POST['icon']);
		$data['ctime'] = time();
		if(empty($data['theme_name'])||empty($data['title'])||empty($data['info'])){
			$this->error('提交参数不满足条件');
		}
		//上传参数
		$options['max_size']   = '2000000';
		$options['allow_exts'] = 'jpg,gif,png,jpeg';
		$options['save_path']  = './addons/plugins/UserTheme/icon/';
		$options['save_to_db'] = false;
		//$saveName && $options['save_name'] = $saveName;
			//执行上传操作
		$info = X('Xattach')->upload('user_theme',$options);
		if($info['status']){
				$data['icon'] = $info['info'][0]['savename'];
		}
		if(empty($_POST['id'])){
				$res=M('all_theme')->add($data);
		}else{
				$res=M('all_theme')->where('id='.$_POST['id'])->save($data);
		}
		if($res) {
				if($_POST['tongzhi_yes']==1){
					$con['title']="新版本上线了，欢迎体验哦！";
					$con['content']="新版本上线了，欢迎体验哦！".U('home/Account/usertheme')."&addon=UserTheme&hook=home_account_show";
					// 收件人
					$con['to'] = $_POST['to'] = M('user')->where('`is_active`=1 AND `is_init`=1')->field('`uid`')->findAll();
					$con['to'] = getSubByKey($con['to'], 'uid');
					$resto = false;
					// 站内信
					if( $con['title'] && $con['content'] ){
						$resto = model('Message')->postMessage($con, $this->mid);
						$resto = !empty($res);
					}
					if ($resto){
						if($_logpost['title'] || $_logpost['content']){
							$_LOG['uid'] = $this->mid;
							$_LOG['type'] = '1';
							$data[] = '插件 - 版本切换 - 消息群发 ';
							if( $_logpost['__hash__'] )unset( $_logpost['__hash__'] );
							$data[] = $_logpost ;
							$_LOG['data'] = serialize($data);
							$_LOG['ctime'] = time();
							M('AdminLog')->add($_LOG);
						}
					}else{
						$this->error('消息群发失败');
					}
				}
				// 提示成功
				$this->assign('jumpUrl', Addons::adminPage('themeAdmin'));
				$this->success('操作成功！');
		}else {
				 //失败提示
				$this->error('操作失败！');
		}
	}
	// 删除包
	public function deltheme() {
		if(!empty($_GET['id'])){
			$data=M('all_theme')->where('id='.$_GET['id'])->find();
			$map['theme_name']=$data['theme_name'];
			$data['count']=M('user_theme')->where($map)->count();
			$this->assign($data);
		}
		$this->display('deltheme');
	}
	// 删除操作
	public function dodeltheme() {
		if($_POST['tongzhi_yes']==1){
				$con['title']=$_POST['content'];
				$con['content']=$_POST['content'];
				// 收件人
				$data=M('all_theme')->where('id='.$_POST['id'])->find();
				$map['theme_name']=$data['theme_name'];
				$con['to'] = M('user_theme')->where($map)->field('`uid`')->findAll();
				$con['to'] = getSubByKey($con['to'], 'uid');
				$res = false;
				// 站内信
				if( $con['title'] && $con['content'] ){
					$res = model('Message')->postMessage($con, $this->mid);
					$res = !empty($res);
				}
			if ($res){
				if($_logpost['title'] || $_logpost['content']){
					$_LOG['uid'] = $this->mid;
					$_LOG['type'] = '1';
					$data[] = '插件 - 版本切换 - 消息群发 ';
					if( $_logpost['__hash__'] )unset( $_logpost['__hash__'] );
					$data[] = $_logpost ;
					$_LOG['data'] = serialize($data);
					$_LOG['ctime'] = time();
					M('AdminLog')->add($_LOG);
				}
			}else{
				$this->error('删除失败');
			}
		}
		//删除记录
		$data1=M('all_theme')->where('id='.$_POST['id'])->find();
		$res1=M('all_theme')->where('id='.$_POST['id'])->delete();
		if($res1){
			$map1['theme_name']=$data1['theme_name'];
			$res2=M('user_theme')->where($map1)->delete();
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
    public function home_account_tab($param) {
        $param['menu'][] = array(
            'act' => 'usertheme',
            'name' => '版本选择',
            'param' => array(
                'addon' => 'UserTheme',
                'hook' => 'home_account_show'
            )
        );
    }
	public function header_account_tab($param) {
        $param['menu'][] = array(
            'url' => U('home/Account/usertheme',array('addon' => 'UserTheme','hook' => 'home_account_show')),
            'name' => '版本选择',
			'act' => 'usertheme',
        );
    }

    public function home_account_show() {
        if ('usertheme' != ACTION_NAME) {
            return;
        }
		$moren['site']     = model('Xdata')->lget('siteopt');
		$data=M('all_theme')->findall();
		$map['uid']=$this->mid;
		$userinfo=M('user_theme')->where($map)->find();
		foreach($data as $k=>$v){
			if(empty($userinfo)){
				if($v['theme_name']==$moren['site']['site_theme']){
					$data[$k]['used']=1;
				}
			}else if($v['theme_name']==$userinfo['theme_name']){
				$data[$k]['used']=1;
			}
		}
		$this->assign('userinfo',$userinfo);
        $this->assign('data',$data);
        $this->display('home_usertheme_show');
    }
	//用户切换操作
    public function useradd() {
		$data['uid'] = $this->mid;
		$data['ctime']=time();
		$data['theme_name']=$_POST['theme_name'];
		if(!empty($_POST['id'])){
			$res=M('user_theme')->where('id='.$_POST['id'])->save($data);
		}else{
			$res=M('user_theme')->add($data);
		}
        if (false !== $res) {
            echo 1;
        } else {
            echo 0;
        }
    }

}