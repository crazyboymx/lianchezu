<?php
/**
* IndexAction
* loot应用的后台操作
*/
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {
	function _initialize(){
		//管理权限判定
        parent::_initialize();
	}
	function index() {
		$loot = model('Xdata')->lget('loot');
		$this->assign($loot);
		$this->display('config');
	}
	function dolootconfig(){
		if($_POST){
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '3';
			$data[] = '抢星位 - 配置 ';
			$data[] = model('Xdata')->lget('loot', $map);
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$data[] = $_POST;
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			model('Xdata')->lput('loot', $_POST);
		}
		$this->assign('jumpUrl', U('loot/admin/index'));
		$this->success("配置成功");
	}
	function clist(){
		$lootc = D('loot_category')->order('display_order ASC')->findpage(20);
		$this->assign('lootc',$lootc);
		$this->display();
	}
	//添加显示
	public function addC() {
		$this->display('editC');
	}
	// 添加操作
	public function doAddC() {
		$_POST['title']	= t($_POST['title']);
		$_POST['status']=1;
		$_POST['ctime']=time();
		if (empty($_POST['title'])) {
			echo 0;
			return ;
		}
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '应用 - 抢星位 - 分类添加';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		$res = M('loot_category')->add($_POST);
		if ($res) {
			//为排序方便，将order = id
			M('loot_category')->where('`id`='.$res)->setField('display_order', $res);
		}
		echo $res;
	}
	// 编辑显示
	public function editC() {
		$_GET['id'] = intval($_GET['id']);
		$lootc = M('loot_category')->where('id='.$_GET['id'])->find();
		$lootc['id'] = $_GET['id'];
		$this->assign('lootc', $lootc);
		$this->display();
	}
	//编辑操作
	public function doEditC() {
		$_POST['title']		= t($_POST['title']);
		$_POST['id']	= intval($_POST['id']);
		if (empty($_POST['title'])) {
			echo 0;
			return ;
		}
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 抢星位 - 分类编辑';
		$data[] =  M('loot_category')->where( array( 'id'=>intval($_POST['id']) ) )->find();
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		echo M('loot_category')->where('`id`='.$_POST['id'])->setField('title', $_POST['title']) ? '1' : '0';
	}
	//删除操作
	public function doDeleteC() {
		$_POST['ids']	= explode(',', t($_POST['ids']));
		if (empty($_POST['ids'])) {
			echo 0;
			return ;
		}
		$map['id']	= array('in', $_POST['ids']);
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '应用 - 抢星位 - 删除分类';
		$data[] =  M('loot_category')->where( $map )->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		echo M('loot_category')->where($map)->delete() ? '1' : '0';
	}
	//设置状态
	public function doSetStatus() {
		$post['id'] = intval($_POST['id']);
		$post['status'] = intval($_POST['status']);
		
		$_LOG['uid']  = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 抢星位  - 分类状态';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		if (M('loot_category')->save($post)) {
			echo '1';
		}else {
			echo '0';
		}
	}
	// 排序设置
	public function doOrder() {
		$_POST['id'] = intval($_POST['id']);
		$_POST['baseid'] = intval($_POST['baseid']);
		if ( $_POST['id'] <= 0 || $_POST['baseid'] <= 0 ) {
			echo 0;
			exit;
		}
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 抢星位  - 设置排序';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		$dao =M('loot_category');
		$loot_id =  array($_POST['id'], $_POST['baseid']);
		
		$lootList = $dao->order('display_order ASC')->findAll();
		$loot = array();
		$loot_id = is_array($loot_id) ? $loot_id : explode(',', $loot_id);
		foreach ($lootList as $v){
			if(in_array($v['id'],$loot_id)){
				$loot[] = $v;
			}
		}
		$res = count($loot_id) <= 1 ? $loot[0] : $loot;
		
		
		if ( count($res) < 2 ) {
			echo 0;
			exit;
		}

		//转为结果集为array('id'=>'order')的格式
    	foreach($res as $v) {
    		$order[$v['id']] = intval($v['display_order']);
    	}
    	unset($res);
	
    	//交换order值
    	$res = 		   $dao->where('`id`=' . $_POST['id'])->setField( 'display_order', $order[$_POST['baseid']] );
    	if($res){
    	 	$res = $dao->where('`id`=' . $_POST['baseid'])->setField( 'display_order', $order[$_POST['id']] );
    	}
		
    	if($res) echo 1;
    	else	 echo 0;
	}
}
?>