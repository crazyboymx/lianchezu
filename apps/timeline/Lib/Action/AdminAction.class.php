<?php
/**
 * AdminAction 
 */
import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction {
	
    public function _initialize() {
		parent::_initialize ();
    }
	public function index()
	{
		       $this->display ();
	}
			
			
	public function addSign()
	{
		$sign=D('Timesign');
		$signs=$sign->select();
		$this->assign('signs',$signs);
		$this->display ();
	}
			
	public function add()
	{
		$sign=D('Timesign');
		$options['allow_exts']	=	'jpg,jpeg,png,gif';
		$info	=	X('Xattach')->upload('timesign', $options);
		if ($info['status']) {
			$data['sign'] = './data/uploads/'.$info['info'][0]['savepath'] . $info['info'][0]['savename'];
		}
		$data['name'] = h(t($_POST['name']));
		$res = $sign->add($data);
			
		if ($res){
            $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->success("添加成功！");
            }else{
               $this->error("添加失败！");
            }
	}
		
    public function change(){
		$id = intval($_GET['id']);
		$map['id'] = $id; 
      	$res  = D('Timesign')->where($map)->setField(); 
		if($res){
            $this->success("更新成功");
        }else{
            $this->error("更新失败");
		}
  
	}
	
	
    public function remove(){
		$id = intval($_GET['id']);
		$map['id'] = $id; 
      	$rs  = M("Share")->where($map)->delete();  // 回收
		if($rs){
            $this->success("删除成功,即将跳转到内容页");
        }else{
            $this->error("删除失败");
		}
  
		}

}
