<?php 
class OperateAction extends Action{
    
    //发布
    function publish(){
    	 $pWeibo = D('Weibo') ;
                $data['content'] = $_POST['content'] ;
				//组ID 仿知美二次开发
        		$data['bc_id'] =  $_POST['bc_id'];
		
				
				if($_POST['goodstag']){
				$data['tag'] = serialize($_POST['goodstag']);
				}
				if($_POST['fenlei_input']){
				$data['fenlei_input'] = $_POST['fenlei_input'];
				$data['price'] = $_POST['price'];
				}
				foreach($_POST['goodstag'] as $tag){
			$tag=trim($tag);
			$result=M()->query("SELECT * FROM ts_plugins_label WHERE label_name='".$tag."'");
			if (count($result)>0){
			}else{
				M()->query("INSERT INTO ts_plugins_label(label_name) VALUES ('".$tag."')");
			}
		}
                if($_POST['g_url']==""){
					$id = $pWeibo->publish($this->mid , $data , 0 , intval($_POST['publish_type']) , $_POST['publish_type_data'] , $_POST['sync']) ;
					if($_POST['goodstag']){
					foreach($_POST['goodstag'] as $v){
					$tagdata['goodsid']=$id;
					$tagdata['tag']=$v;
					M('goodstag')->add($tagdata);
					}
				 }
				}else{
					$id = $pWeibo->publish($this->mid , $data , 0 , intval($_POST['publish_type']) , $_POST['publish_type_data'] , $_POST['sync'], $_POST['g_url']) ;
					if($_POST['goodstag']){
					foreach($_POST['goodstag'] as $v){
					$tagdata['goodsid']=$id;
					$tagdata['tag']=$v;
					$tagdata['fenlei']=$_POST['fenlei_input'];
					M('goodstag')->add($tagdata);
					}
				 }
				}
        if( $id ){
        	//发布成功后，检测后台是否开启了自动举报功能
        	$weibo_option = model('Xdata')->lget('weibo');
        	if( $weibo_option['openAutoDenounce'] && checkKeyWord( $data['content'] ) ){
        		$map['from'] = 'weibo';
				$map['aid'] = $id;
				$map['uid'] = '0';
				$map['fuid'] = $this->mid;
				$map['content'] = $data['content'];
				
					 			//组id 仿知美二次开发
                                $map['bc_id'] = $data['bc_id'];
								
				$map['reason'] = '内容中含有需要过滤的敏感词';
				$map['ctime'] = time();
				$map['state'] = '1';
        		M( 'Denounce' )->add( $map );
        		echo '0';exit;
        	}
			X('Credit')->setUserCredit($this->mid,'add_weibo');
        	$data = $pWeibo->getOneLocation( $id );
        	$this->assign('data',$data);
        	$this->display();
        }
    }

 //喜欢
        function love()
        {
			//ALTER TABLE  `ts_fav` ADD  `favuid` INT( 10 ) NOT NULL ;
                if ( $_POST )
                {
                        $favtable = M('fav') ;
                        $myfav = $favtable->where("favid=$_POST[id] AND uid=" . $this->mid . "")->findall() ;
                        $favcount = M('weibo')->where("weibo_id=$_POST[id]")->findall() ;
						
                        if ( $myfav )
                        {
                                echo 1 ;
                        } else
                        {

                                $favstatus = array(
                                        'uid' => $this->mid ,
                                        'favid' => $_POST['id'] ,
									    'favuid' => $favcount[0]['uid'] ,
                                        'dateline' => time() ,
                                ) ;

                                $favtable->add($favstatus) ;
                                D('Weibo' , 'weibo')->setInc('favcount' , 'weibo_id=' . $_POST['id'] . '') ;
                                echo 0 ;
                        }
                }
        }
       //删除喜欢
	   function delfav(){
	       if ( D('fav')->where("favid=$_POST[id] AND uid=".$this->mid."")->delete() )
                {       
				        D('Weibo' , 'weibo')->setDec('favcount' , 'weibo_id=' . $_POST['id'] . '') ;
                        echo '1' ;
                }else
				{
				echo 0;
				}
	   
	   
	   
	   }
	   
	   
	   //推荐
        function tuijian()
         {
			
                if ( $_POST )
                {
                        $favtable = M('tuijian') ;
                        $myfav = $favtable->where("jianid=$_POST[id55] AND uid=" . $this->mid . "")->findall() ;
                        $jiancount = M('weibo')->where("weibo_id=$_POST[id55]")->findall() ;
						
                        if ( $myfav )
                        {
                                echo 1 ;
                        } else
                        {

                                $favstatus = array(
                                        'uid' => $this->mid ,
                                        'jianid' => $_POST['id55'] ,
									    'jianuid' => $jiancount[0]['uid'] ,
                                        'dateline' => time() ,
                                ) ;

                                $favtable->add($favstatus) ;
                                D('Weibo')->setInc('jiancount' , 'weibo_id=' . $_POST['id55'] . '') ;
                                echo 0 ;
                        }
                }
        }
       //删除推荐
	   function deltuijian(){
	       if ( D('tuijian')->where("jianid=$_POST[id55] AND uid=".$this->mid."")->delete() )
                {       
				        D('Weibo')->setDec('jiancount' , 'weibo_id=' . $_POST['id55'] . '') ;
                        echo '1' ;
                }else
				{
				echo 0;
				}
	   
	   
	   
	   
	   
	   }
	   
	   //推荐达人
        function daren()
         {
			
                if ( $_POST )
                {
                        $favtable = M('daren') ;
                        $myfav = $favtable->where("darenid=$_POST[id] AND uid=" . $this->mid . "")->findall() ;
                        $fengcount = M('weibo')->where("weibo_id=$_POST[id]")->findall() ;
						
                        if ( $myfav )
                        {
                                echo 1 ;
                        } else
                        {

                                $favstatus = array(
                                        'uid' => $this->mid ,
                                        'darenid' => $_POST['id'] ,
									    'darenuid' => $fengcount[0]['uid'] ,
                                        'dateline' => time() ,
                                ) ;

                                $favtable->add($favstatus) ;
                                D('Weibo' , 'weibo')->setInc('fengcount' , 'weibo_id=' . $_POST['id'] . '') ;
                                echo 0 ;
                        }
                }
        }
       //删除推荐
	   function deldaren(){
	       if ( D('daren')->where("darenid=$_POST[id] AND uid=".$this->mid."")->delete() )
                {       
				        D('Weibo' , 'weibo')->setDec('fengcount' , 'weibo_id=' . $_POST['id'] . '') ;
                        echo '1' ;
                }else
				{
				echo 0;
				}
	   
	   
	   
	   }
	   
	   
	   //推荐
         function fengmian()
         {
			
                if ( $_POST )
                {
                        $favtable = M('fengmian') ;
                        $myfav = $favtable->where("fengid=$_POST[id] AND uid=" . $this->mid . "")->findall() ;
                        $fengcount = M('weibo_bc')->where("bc_id =$_POST[id]")->findall() ;
						
                        if ( $myfav )
                        {
                                echo 1 ;
                        } else
                        {

                                $favstatus = array(
                                        'uid' => $this->mid ,
                                        'fengid' => $_POST['id'] ,
									    'fenguid' => $fengcount[0]['uid'] ,
                                        'dateline' => time() ,
                                ) ;

                                $favtable->add($favstatus) ;
                                D('weibo_bc')->setInc('fengcount' , 'bc_id =' . $_POST['id'] . '') ;
                                echo 0 ;
                        }
                }
        }
       //删除推荐
	   function delfengmian(){
	       if ( D('fengmian')->where("fengid=$_POST[id] AND uid=".$this->mid."")->delete() )
                {       
				        D('weibo_bc')->setDec('fengcount' , 'bc_id =' . $_POST['id'] . '') ;
                        echo '1' ;
                }else
				{
				echo 0;
				}
	   
	   
	   
	   }
	   function mygoods()
        {
			if($_GET['id']){
                        
                
                //echo $_GET['id'];
                $a = D('Goods')->where('goods_url=' . '"' . $_GET['id'] . '"')->find() ;
                //echo D('Goods')->getLastSql();
                $b = unserialize($a['type_data']) ;
                //print_r($b) ;
                $aaaaa=$b['goodsurl'];
                
                //echo "zhVrLsYX";
                
                //redirect($aaaaa);
               // $url = "/index.php?app=home&mod=Test&act=url&id=10245949481" ;
               // $aaaa = "&ref=" . $url ;
               /// $abcd="http://s.click.taobao.com/t_js?tu=" . urlencode($aaaaa) . urlencode($aaaa) ;
                //echo "<a href='".$abcd."'>sdfasdf</a>";
                //redirect($abcd);
                //echo "Lacation:".$abcd."";
               /// header("Location:".$abcd."");
                }else{
                     //  $Model=new Model();
                    //   echo $DB_PREFIX;
                    //   $a=$Model->query("select * from ".C('DB_PREFIX')."weibo where goods_url='zhVrLsYX'");
                   //    print_r($a);
                       
                      //  echo U('home/user/index',array('id'=>'zhVrLsYX'));
                        
                       // echo "<a target='_blank' href='/index.php?app=weibo&mod=goods&act=index&id=zhVrLsYX'>测试淘宝客连接</a>";
					    echo "zhVrLsYX";
                }
		
		}
	   
    //转发
    function transpond(){
		
		$bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
		foreach ( $bind as $v ) {
			$data ['login_bind'] [$v ['type']] = $v ['is_sync'];
		}
		
		//仿知美二次开发
        $map['uid']=$this->mid;
        $bcdisplay = M('weibo_bc')->where($map)->order('bc_id DESC')->findAll();
		$this->assign('bcdisplay',$bcdisplay);  
		//仿知美二次开发
		$nobc= M('weibo_bc')->getField('bc_id',$map);
            
        $this->assign('nobc',$nobc); 
		//仿知美二次开发 end
	
	
    	$pWeibo = D('Weibo');
    	if($_POST){
					//仿知美二次开发
                 	$post['bc_id']         = $_POST['bc_id'];
					
	        $post['content']         = $_POST['content'];
	        $post['transpond_id']    = intval( $_POST['transpond_id'] );
	        $post['reply_weibo_id']  = $_POST['reply_weibo_id'];
	        if( $id = $pWeibo->transpond($this->mid,$post) ){
	        	$data = $pWeibo->getOneLocation($id);
				X('Credit')->setUserCredit($this->mid,'forward_weibo')
						   ->setUserCredit($data['expend']['uid'],'forwarded_weibo');
        		$this->assign('data',$data);
        		$this->display('publish');
	        }
    	}else{
	    	$intId = intval( $_GET['id'] );
	    	$info = $pWeibo->where( 'weibo_id='.$intId)->find();
	    	if( $info['transpond_id'] ){
	    		$info['transponInfo'] = D('Operate')->field('weibo_id,uid,content,type_data')->where('weibo_id='.$info['transpond_id'])->find();
	    	}else{
	    		$info['old_content'] = $info['content'];
	    	}
	    	$info['upcontent'] = intval($_GET['upcontent']);
	    	$this->assign( 'data' , $info );
	    	$this->display();
    	}
    }
	
	//--------------仿知美二次开发------------------------ 
	//编辑功能
    function myedit(){
        
        $map['uid']=$this->mid;
        $bcdisplay = M('weibo_bc')->where($map)->order('bc_id DESC')->findAll();
        $this->assign('bcdisplay',$bcdisplay);  
 
    	$pWeibo = D('Weibo');
    	if($_POST){
                 $post['bc_id']         = $_POST['bc_id'];
              
                 $post['content']         = $_POST['content'];
				 
				 $post['weibo_id']         = $_POST['weibo_id']; 
				 
				 $_POST['ctime']		= time();
				 $post['ctime']		= $_POST['ctime'];
			
	    	
			$res =  M('weibo')->save($_POST) ;
			
			if($res) {

						$this->success('修改成功');
					 }else {
						$this->error('修改失败');
					 }
			return 1;
    	}else{
	    	$intId = intval( $_GET['id'] );
			$bc_id = intval( $_GET['bc_id'] );
	    	$info = $pWeibo->where( 'weibo_id='.$intId)->find();
	    	if( $info['transpond_id'] ){
	    		$info['transponInfo'] = D('Operate')->field('weibo_id,uid,content,type_data')->where('weibo_id='.$info['transpond_id'])->find();
	    	}else{
	    		$info['old_content'] = $info['content'];
	    	}
	    	$info['upcontent'] = intval($_GET['upcontent']);
			$info['bc_id'] = intval( $_GET['bc_id'] );
	    	$this->assign( 'data' , $info );
	    	$this->display();
    	}
    }
	
	//--------------仿知美二次开发 end -------------------

    //添加评论
    function addcomment(){
    	$post['reply_comment_id'] = intval( $_POST['reply_comment_id'] );   //回复 评论的ID
    	$post['weibo_id']         = intval( $_POST['weibo_id'] );           //回复 微博的ID
    	$post['content']          = $_POST['comment_content'];         //回复内容
    	$post['transpond']        = intval($_POST['transpond']);            //是否同是发布一条微博
		echo D('Comment')->doaddcomment($this->mid, $post);
		if(intval($_POST['transpond_weibo_id'])){//同时评论给原文作者
			unset($post['reply_comment_id']);
			unset($post['transpond']);
			$post['weibo_id'] = intval($_POST['transpond_weibo_id']);
			D('Comment')->doaddcomment($this->mid, $post, true);
		}
    }
    
    //删除评论
    function docomments(){
    	$result = D('Comment')->deleteComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }
    
    //批量删除评论
    function deleteMuleComment(){
    	$result = D('Comment')->deleteMuleComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }
    
    //删除微博
    function delete(){
    	$arrWeiInfo = D( 'Operate' )->where( 'weibo_id='.$_POST['id'] )->field('isdel')->find();
    	if( !$arrWeiInfo['isdel'] ){
	    	if( D('Operate')->deleteMini( intval($_POST['id']) , $this->mid ) ){
				X('Credit')->setUserCredit($this->mid,'delete_weibo');
	    		echo '1';
	    	}
    	}else{
    		echo '1';
    	}
    }
    
    //收藏
    function stow(){
    	if( D('Favorite')->favWeibo( intval( $_POST['id'] ), $this->mid ) ){
    		echo '1';
       	}
    }
    
 	function unstow(){
    	if( D('Favorite')->dodelete( intval( $_POST['id'] ), $this->mid ) ){
    		echo '1';
       	}
    }
    
    //关注人
    function follow(){
    	if($_POST['type']=='dofollow'){
    		echo D('Follow')->dofollow( $this->mid,intval($_POST['uid']) );
    	}else{
    		echo D('Follow')->unfollow( $this->mid,intval($_POST['uid']) );
    	}
    }
    
    //关注话题
    function followtopic(){
    	$name = $_POST['name'];
    	$topicId = D('Topic')->getTopicId($name);
    	if($topicId){
    		$id = D('Follow')->dofollow($this->mid, $topicId, 1);
    	}
    	echo json_encode(array('code'=>$id,'topicId'=>$topicId,'name'=>h(t(mStr(preg_replace("/#/",'',$name),150,'utf-8',false)))));
    }
    
    //取消关注话题
    function unfollowtopic(){
        $topicId = intval($_POST['topicId']);
    	if($topicId){
    		$id = D('Follow')->unfollow($this->mid,$topicId,1);
    	}
    	echo $id;
    }
    
    //上传图片
    function uploadpic(){

    }
    
    function quickpublish(){
		
		$bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
		foreach ( $bind as $v ) {
			$data ['login_bind'] [$v ['type']] = $v ['is_sync'];
		}
		$data ['type'] = $strType;
		$this->assign ( $data );
	
    	$label=M()->query("SELECT DISTINCT node_name FROM ts_plugins_node WHERE node_name<>'逛街啦'  AND node_name<>'搭配秀' AND node_name<>'晒货' AND node_name<>'潮STYLE'  ORDER BY node_seq DESC");
		$this->assign('label',$label);
	
    	$this->assign('text', $_POST['text'] );
		
		 //--------------------仿知美二次开发 --------------------- 
		 $map['uid'] = $this->mid;
       
        $bcdisplay = M('weibo_bc')->where($map)->order('bc_id DESC')->findAll();
        
        $nobc= M('weibo_bc')->getField('bc_id',$map);
            
        $this->assign('nobc',$nobc);  
         
        $this->assign('bcdisplay',$bcdisplay);  
		 //--------------------仿知美二次开发 end--------------------- 
		
    	$this->display();	
    }
    
    //上传临时文件

    // 预同步 (如果已绑定过, 自动同步; 否则展示"开始绑定"按钮)
    function beforeSync() {
    	if ( !in_array($_GET['type'], array('sina')) ) {
    		echo 0;
    	}
    	
    	// 展示"开始绑定"按钮
    	$map['uid']  = $this->mid;
    	$map['type'] = 'sina';
   		if( M('login')->where("uid={$this->mid} AND type='{$_GET['type']}' AND oauth_token<>''")->count() ){
   			M('login')->setField('is_sync',1,$map);
   			echo '1';
   		}else{
   			$_SESSION['weibo_bind_target_url'] = U('home/User/index');
   			$this->assign('url', U('weibo/Operate/bind',array('type'=>$_GET['type'])));
   			$this->display();
   		}
    }
    
    //绑定帐号
    function bind() {
    	if ( !in_array($_GET['type'], array('sina')) ) {
    		if ($this->isAjax()) {
    			echo 0;
    			exit;
    		}else {
    			$this->error('参数错误');
    		}
    	}
    	include_once SITE_PATH."/addons/plugins/login/{$_GET['type']}.class.php";
		$platform = new $_GET['type']();
		$call_back_url = U("weibo/Operate/bind{$_GET['type']}CallBack");
		$url = $platform->getUrl($call_back_url);
		redirect($url);
    }
    
    function bindSinaCallBack() {
    	include_once( SITE_PATH.'/addons/plugins/login/sina.class.php' );
		$sina = new sina();
    	$sina->checkUser();
    	
    	if ( !in_array($_SESSION['open_platform_type'], array('sina')) ) {
    		if ($this->isAjax()) {
				echo 0;
				exit;
    		}else {
    			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
    			$this->error('授权失败');
    		}
		}
		
		// 检查是否成功获取用户信息
		$userinfo = $sina->userInfo();
		if ( !is_numeric($userinfo['id']) || !is_string($userinfo['uname']) ) {
			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
			$this->error('获取用户信息失败');
		}
		
		$syncdata['uid']                = $this->mid;
		$syncdata['type_uid']           = $userinfo['id'];
		$syncdata['type']               = 'sina';
		$syncdata['oauth_token']        = $_SESSION['sina']['access_token']['oauth_token'];
		$syncdata['oauth_token_secret'] = $_SESSION['sina']['access_token']['oauth_token_secret'];
		$syncdata['is_sync']			= '1';
		if ( $info = M('login')->where("type_uid={$userinfo['id']} AND type='sina'")->find() ) {
			// 该新浪用户已在本站存在, 将其与当前用户关联(即原用户ID失效)
			M('login')->where("`login_id`={$info['login_id']}")->save($syncdata);
		}else {
			// 添加同步信息
			M('login')->add($syncdata);
		}
		
		if ( isset($_SESSION['weibo_bind_target_url']) ) {
			$this->assign('jumpUrl', $_SESSION['weibo_bind_target_url']);
			unset($_SESSION['weibo_bind_target_url']);
		}else {
			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
		}
		$this->success('绑定成功');
    }
    
    /**
     * @deprecated
     */
    function bind_backup(){
    	$type = h($_POST['value']);
    	if($_POST){
	    	include_once( SITE_PATH.'/addons/plugins/login/sina.class.php' );
			$sina = new sina();
			$weiboAuth =   $sina->getJSON($_POST['username'],$_POST['password']);
			if( $weiboAuth['oauth_token'] ){
				$data['type']     = 'sina';
				$data['type_uid'] =  $weiboAuth['user_id'];
				$data['uid']      = $this->mid;
				if($info = M('login')->where($data)->find()){
					if($info['oauth_token']){
						M('login')->setField('is_sync',1,$data);
					}else{
						$savedata['oauth_token'] 		= $weiboAuth['oauth_token'];
						$savedata['oauth_token_secret'] = $weiboAuth['oauth_token_secret'];
						$savedata['is_sync'] = 1;
						M('login')->where('login_id='.$info['login_id'])->data($savedata)->save();
					}
				}else{
					$data['oauth_token'] 		= $weiboAuth['oauth_token'];
					$data['oauth_token_secret'] = $weiboAuth['oauth_token_secret'];
					$data['is_sync'] = 1;
					M('login')->add($data);
				}
				echo '1';
			}else{
				echo '0';
			}
    	}else{
    		$map['uid'] = $this->mid;
    		$map['type'] = 'sina';
    		if( M('login')->where("uid={$this->mid} AND type='sina' AND oauth_token<>''")->count() ){
    			M('login')->setField('is_sync',1,$map);
    			echo '1';
    		}else{
    			$this->display();
    		}
    	}
    }
    
    //绑定email
    function bindemail(){
    	$email = $_POST['email'];
    	$passwd = $_POST['passwd'];
		if (!preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)){  
			$return['boolen'] = false;
    		$return['message'] = '邮箱格式错误';
    		exit(json_encode($return));
		}   	
    	if( M('user')->where("email='{$email}'")->count() ){
    		$return['boolen'] = false;
    		$return['message'] = '邮箱已存在';
    		exit(json_encode($return));
    	}
    	
    	$data['email']    = $email;
    	$data['password'] = md5($passwd);
    	if( M('user')->where('uid='.$this->mid)->data($data)->save() ){
    		$return['boolen'] = true;
    		exit(json_encode($return));
    	}else{
    		$return['boolen'] = false;
    		$return['message'] = '绑定失败';
    		exit(json_encode($return));
    	}
    	
    }
    
    //取消绑定
    function delbind(){
    	if( M('login')->where("uid={$this->mid} AND type='sina'")->delete() ){
    		echo '1';
    	}else{
    		echo '0';
    	}
    }
    
    function unbind(){
    	$type = h($_POST['value']);
    	echo M("login")->setField('is_sync',0,"uid={$this->mid} AND type='{$type}'" );
    }
}
?>