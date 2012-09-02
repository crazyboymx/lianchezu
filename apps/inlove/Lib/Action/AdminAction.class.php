<?php
/**
* IndexAction
* inlove应用的后台操作
*/
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {
	function _initialize(){
		//管理权限判定
        parent::_initialize();
	}
	function index() {
		$inlove = model('Xdata')->lget('inlove');
		$this->assign($inlove);
		$this->display('config');
	}
	function doinloveconfig(){
		if($_POST){
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '3';
			$data[] = '恋爱通告 - 配置 ';
			$data[] = model('Xdata')->lget('inlove', $map);
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$data[] = $_POST;
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			model('Xdata')->lput('inlove', $_POST);
		}
		$this->assign('jumpUrl', U('inlove/admin/index'));
		$this->success("配置成功");
	}
	function inlovelist(){
		if($_GET['recycle'] == 1){
			$map['isdel'] = 1;
			$data['recycle'] = 1;
		}else{
			$map['isdel'] = 0;
			$data['recycle'] = 0;
		}
		$inlove = model('Xdata')->lget('inlove'); 
		$map['uid']=$inlove['inloveid'];
		$data['list'] = D('Weibo')->where($map)->findpage(20);
		$this->assign($data);
		$this->display();
	}
}
?>