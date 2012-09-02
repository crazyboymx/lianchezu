<?php

class FriendLinkHooks extends Hooks {
    public function init() {

    }
	//友情链接管理
	public function linkAdmin() {
		$this->assign('listlink', M('friend_link')->findall());
		$this->display('listlink');
	}
	//添加显示
	public function addlink() {
		if(!empty($_GET['id'])){
			$data=M('friend_link')->where('id='.$_GET['id'])->find();
			$this->assign($data);
		}
		$this->display('addlink');
	}
	//添加、编辑操作
	public function doaddlink() {
		$data['title'] = t($_POST['title']);
		$data['url'] = t($_POST['url']);
		$data['ctime'] = time();
		if(empty($data['title'])||empty($data['url'])){
			$this->error('提交参数不满足条件');
		}
		if(empty($_POST['id'])){
				$res=M('friend_link')->add($data);
		}else{
				$res=M('friend_link')->where('id='.$_POST['id'])->save($data);
		}
		if($res){
				// 提示成功
				$this->success('操作成功！');
		}else{
				$this->error('操作失败');
		}
	}
	// 删除
	public function doDeleteLink() {
		$_POST['id']	= explode(',', t($_POST['id']));
		if (empty($_POST['id'])) {
			echo 0;
			return ;
		}
		$map['id']	= array('in', $_POST['id']);
		echo M('friend_link')->where($map)->delete() ? '1' : '0';
	}
	//底部显示
    public function public_footer() {
		$data=M('friend_link')->order('ctime desc') ->limit(8)->findall();
        $this->assign('data',$data);
        $this->display('public_footer');
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
			}
		}
		unset($res);
		$data=M('friend_link')->order('ctime desc')->findall();
        $this->assign('data',$data);
		$this->assign('list', $list);
		$this->display("document");
	}

}