<?php
/**
 * AdminAction 
 * 专辑管理
 */
import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction {
	public function _initialize() {
		parent::_initialize ();
	}
	
	/**
	 *专辑管理
	 */
	public function index()
	{
			   $map['name']=$_POST['name'];
			   if(!empty($map['name'])){
		           $order = 'cTime DESC';
		           $record = D("Share")->where($map)->order($order)->select() ;
				   $this->assign('data',$record);
			   }else{
				   $records = D("Share")->getAllShare();
				   }
			   $this->assign($records);
		       $this->display ();
	}
	
    //推荐编辑
	public function tui()
	{
		$share= M('Share');
			$map['id'] = $_GET['id'];
            $status = '2';
			$result=$share->where($map)->setField('status',"$status");
		if ($result){
            $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->success("推荐成功！");
            }else{
               $this->error("推荐失败！");
            }
	}
	
	//专辑单页管理
	public function managePict()
	{
			   $map['id']=$_POST['id'];
			   if(!empty($map['id'])){
		           $order = 'cTime DESC';
		           $record = D("Pict")->where($map)->order($order)->select() ;
				   $this->assign('data',$record);
			   }else{
				   $records = D("Pict")->getPictList();
				   }
			   $this->assign($records);
		$this->display ();
	}

    //分类管理
	public function manageType()
	{
		$dtype= M('Sharetype');
		$type= $dtype->select();
		$this->assign('type',$type);
        $this->display();
	}
	
    //编辑分类
	public function editType()
	{
		$dtype= M('Sharetype');
		if(isset($_POST['addtype'])){
		    $data['name'] = $_POST['type'];
		    $result=$dtype->add($data);
			}
		if(isset($_POST['edit'])){
			$map['id'] = $_POST['cid'];
            $name = $_POST['type'];
			$result=$dtype->where($map)->setField('name',"$name");
			}
		if(isset($_POST['del'])){
			$map['id'] = $_POST['cid'];
			$result=$dtype->where($map)->delete();
			}
		if ($result){
            $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->success("编辑成功！");
            }else{
               $this->error("编辑失败！");
            }
	}
		
	
	//删除专辑
    public function remove(){
		$id = intval($_GET['id']);
		$map['id'] = $id; 
		$condition['gid'] = $id;
      	$rs  = M("Share")->where($map)->delete();  // 回收
		if($rs){
			$res =D('Pict')->where($condition)->delete();
            $this->success("删除成功,即将跳转到内容页");
        }else{
            $this->error("删除失败");
		}
  
		}
    //删除专辑单页
    public function deletePict(){
		$id = intval($_GET['id']);
		$map['id'] = $id; 
      	$rs  = M("Pict")->where($map)->delete();  // 回收
		if($rs){
            $this->success("删除成功,即将跳转到内容页");
        }else{
            $this->error("删除失败");
		}
  
		}

}
