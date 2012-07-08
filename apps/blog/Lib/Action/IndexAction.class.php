<?php
/**
 * IndexAction
 * blog的Action.接收和过滤网页传参
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {
        private $filter;
        private $blog;
        private $lastblog;
        private static $friends=array();
        /**
         * __initialize
         * 初始化
         * @access public
         * @return void
         */
        public function _initialize() {
        	//parent::_initialize();

			//设置日志Action的数据处理层
            $this->blog = D( 'Blog' );
            $this->follow=D('Follow');
        }
        protected $app = null;
        /**
         * index
         * 好友的日志
         * @access public
         * @return void
         */
        public function index() {

			//获得日志数据集,自动获得当前登录用户的好友日志
			$list = $this->__getBlog(null,'*','cTime desc');
			//检查是否可以查看全部日志
			if( $this->__checkAllModel() ) {
					$list = $this->blog->getAllData('popular');
					$relist= $this->blog->getIsHot();
					$this->assign('relist',$relist);
					$this->assign( 'api',$this->api);
					$this->assign( 'uid',$this->mid );
					$this->assign( 'order',$_GET['order'] );
					$this->assign( $list );
					$this->assign( 'all','true' );
					global $ts;
					$this->setTitle("热门{$ts['app']['app_alias']}");
					$this->display();

			}else {
					$this->error( L( 'error_all' ) );
			}
			//是否是好友,需要api辅助
        }
        /**
         * search
         * 搜索日志
         * @access public
         * @return void
         */
        public function search() {
			$keyword	=	h($_GET['key']);
			//获得日志数据集,自动获得当前登录用户的好友日志
			$map['title']  = array('like',"%{$keyword}%");
			if($keyword)
				$list = $this->blog->getBlogList($map,'*','cTime desc',10);
			$relist= $this->blog->getIsHot();
			$this->assign('relist',$relist);
			$this->assign( 'api',$this->api);
			$this->assign( 'uid',$this->mid );
			$this->assign( $list );
			$this->assign( 'all','true' );
			$this->setTitle("搜索文章: ".$keyword);
			$this->display();


			//是否是好友,需要api辅助
        }

        /**
         * my
         * 我的日志
         * @access public
         * @return void
         */
        public function my() {
        	//获得日志数据集
            $outline = D( 'BlogOutline' );
            $list    = isset( $_GET['outline'] )?
            	$outline->getList( $this->mid ): //草稿箱
            	$this->__getBlog( $this->mid,'*','cTime desc' ); //我的日志

            foreach($list['data'] as $k => $v) {
            	if ( empty($v['category']['name']) && !empty($v['category']['id']) )
            		$list['data'][$k]['category']['name'] = M('blog_category')->where('id='.$v['category']['id'])->getField('name');
            }

            //归档数据
            $url = isset( $_GET['cateId'] )? 'Index&act=my&cateId='.$_GET['cateId']:'Index&act=my';
            $file_away = $this->_getWiget( $url,$this->mid );

            //获得分类的计数
            $category = $this->__getBlogCategoryCount($this->mid);

            //草稿箱计数
            $outline = D( 'BlogOutline' )->where( 'uid ='.$this->mid )->count();

            //检查是否可以查看全部日志
            $this->__checkAllModel();

			$relist= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            //获得归档传输数据
            $this->assign( 'oc',$outline );
            $this->assign( 'file_away',$file_away );
            $this->assign('category',$category);
            $this->assign( $list );
            global $ts;
            $this->setTitle("我的{$ts['app']['app_alias']}");
            $this->display('index');
        }

        public function news() {
	        //检查是否可以查看这个页面
			if( $this->__checkAllModel() ) {
    			$list = $this->blog->getAllData('new');
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',$_GET['order'] );
                $this->assign( $list );
                $this->assign( 'all','true' );
                global $ts;
                $this->setTitle("最新{$ts['app']['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
            }
        }
        public function followsblog() {
        	//检查是否可以查看这个页面
        	$mid=$this->mid;
            if( $this->__checkAllModel() ) {
            	$list = $this->blog->getFollowsBlog($mid);
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',$_GET['order'] );
                $this->assign( $list );
                $this->assign( 'all','true' );
                global $ts;
                $this->setTitle("我的关注的人的{$ts['app']['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
			}
        }
        private function __checkAllModel() {
        	return true;

        	//获取配置，是否可以查看全部的日志
            if( $this->blog->getConfig( 'all' ) ) {
            	$this->assign( 'all','true' );
                return true;
            }
            return false;
        }


        /**
         * show
         * 日志显示页
         * @access public
         * @return void
         */
        public function show() {
				unset($_SESSION['blog_use_widget_share']);
        //获得日志id
                $id      = $_GET['id'];
                $this->blog->setUid( $this->mid );

                //全站日志
                if( $this->blog->getConfig( 'all' ) ) {
                        $this->assign( 'all','true' );
                }


                //日志所有者
                $bloguid = $_GET['mid'];


                //获得日志的详细内容,第二参数通知是当前还是上一篇下一篇
                isset( $_GET['action'] ) && $how = $_GET['action'];
                $list     = $this->blog->getBlogContent($id,$how,$bloguid);

                //检测是否有值。不允许非正常id访问
                if( false == $list ) {
                		$this->assign('jumpUrl',U('blog/Index'));
                        $this->error( '日志不存在或者已删除！' );
                }
                 //Converts special HTML entities back to characters.
                $list['content'] = htmlspecialchars_decode($list['content']);
                
                //获得正确的当前日志ID
                $id = $list['id'];
                //是否是好友
                $this->assign( 'isFriend',friend_areFriends( $bloguid,$this->mid ) );

                //检测密码
                if (isset($_POST['password'])) {
                        if(md5(t($_POST['password'])) == $list['private_data']) {
                                Cookie::set($id.'password',md5(t($_POST['password'])));
                                $list['private'] = 0;
                        }

                } else {
                        if( 3 == $list['private'] && Cookie::get($id.'password') == $list['private_data']) {
                                $list['private'] = 0;
                        }
                }

                //不是日志所有人读日志才会刷新阅读数.只有非日志发表人才进行阅读数刷新
                if( !empty( $bloguid ) && $this->mid != $bloguid ) {
                        $options = array( 'id'=>$id,'uid'=>$this->mid,'type'=>APP_NAME,'lefttime'=>"30" );
                        //浏览计数，防刷新
                        //if(  browseCount( APP_NAME,$id,$this->mid,'30') ) {
                                $this->blog->changeCount( $id );
                        //}
                }


                //获取发表人的id
                $name          = $this->blog->getOneName( $bloguid );

                //他人日志渲染特殊的变量和数据
                if( $this->mid != $bloguid ) {
                //查看这篇日志，访问者是否推荐过
                        $recommend = D( 'BlogMention' )->checkRecommend( $this->mid,$list['id'] );

                        //如果是其它人的日志。需要获得最新的10条日志
                        $bloglist  = $this->blog->getBlogTitle( $list['uid'] );
                        $this->assign( 'bloglist',$bloglist );
                        $this->assign( 'recommend',$recommend );
                }

                //渲染公共变量
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( $list );
                $this->assign( 'blog', $list );
                $this->assign( 'guest',$this->mid );
                $this->assign( 'name',$name['name'] );
                $this->assign( 'uid',$bloguid );
                $this->assign('isOwner', $this->mid == $bloguid ? '1' : '0');

                global $ts;
                $this->setTitle(getUserName($list['uid']).'的文章: '.$list['title']);
                $this->display('blogContent');
        }

        /**
         * personal
         * 个人的日志列表
         * @access public
         * @return void
         */
        public function personal() {
        //获得日志数据集
                $uid   = intval($_GET['uid']);
                if($uid <= 0)
                	$this->error('参数错误');

                //获得blog的列表
                $list             = $this->__getBlog($uid,'*','cTime desc');

                //获得分类的计数
                $category = $this->__getBlogCategoryCount($uid);

                //归档数据
                $url       = isset( $GET['cateId'] )?
                    'Index/personal/uid/'.$uid.'/cateId/'.$_GET['cateId']:
                    'Index/personal/uid/'.$uid;
                $file_away = $this->_getWiget( $url,$uid);

                //组装数据
                $this->assign( 'file_away',$file_away );
                $this->assign('api',$this->api);

                $this->assign('category',$category);
                $name = getUserName($uid);
                $this->assign('name', $name);
                $this->assign( $list );

                global $ts;
                $this->setTitle($name . '的' . $ts['app']['app_alias']);
                $this->display('index');
        }

        private function __getBlogCategoryCount($uid) {
                $cateId = null;
                if(isset($_GET['cateId'])) {
                        $cateId = intval($_GET['cateId']);
                }
                $category = $this->blog->getBlogCategory($uid,$cateId);
                if(!$category) {
                        $this->error(L('参数错误'));
                        exit;
                }
                return $category;
        }

        /**
         * doDeleteblog
         * 删除blog
         * @access public
         * @return void
         */
        public function doDeleteblog(  ) {

                $this->blog->id = $_REQUEST['id']; //要删除的id;
                $result         = $this->blog->doDeleteblog(null,$this->mid);

                if( false != $result) {
					X('Credit')->setUserCredit($this->mid,'delete_blog');
                    redirect( U('blog/Index/my') );
                }else {
                    $this->error( "删除日志失败" );
                }
        }

        /**
         * deleteCategory
         * 删除分类
         * @access public
         * @return void
         */
        public function deleteCategory(  ) {
                $data['id'] = intval($_POST['id']);
                if( 0 === $data['id'] )
                        return false;

                //删除分类和将分类的日志转移到其它分类里
                isset( $_POST['toCate'] ) && !empty( $_POST['toCate'] ) && $toCate   = $_POST['toCate'];

                $category   = D( 'BlogCategory' );
                return $category->deleteCategory( $data,$toCate,$this->blog );
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addBlog() {

                $category  = $this->blog->getCategory($this->mid);
                $savetime  = $this->blog->getConfig( 'savetime' );

                //表情控制
                $smile     = array();
                $smileType = $this->opts['ico_type'];
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                //$smileList = $this->getSmile($smileType);
                //$smilePath = $this->getSmilePath($smileType);
                $this->assign( 'smileList',$smileList );
                $this->assign( 'smilePath',$smilePath );
                $this->assign( 'savetime',$savetime );
                $this->assign( 'blog_category',$category );
                global $ts;
                $this->setTitle("发表{$ts['app']['app_alias']}");
                $this->display();
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addAjaxBlog() {
				$use = intval($_POST['used']);
                $category  = $this->blog->getCategory($this->mid);
                $savetime  = $this->blog->getConfig( 'savetime' );

                //表情控制
                $smile     = array();
                $smileType = $this->opts['ico_type'];
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'savetime',$savetime );
                $this->assign( 'category',$category );
                if($use){
                	$this->display('addAjaxBlog_used');
                }else{
                	 $this->display();
                }

        }

        public function edit() {
                $category = $this->blog->getCategory($this->mid);
                $this->assign( 'blog_category',$category );
                $id = $_GET['id'];
                if( $_GET['edit'] ) {
                        $outline = D( 'BlogOutline' );
                        //检查是否存在这篇日志
                        if( false == $list = $outline->getBlogContent( $id,null,$_GET['mid']))
                                $this->error( L( 'error_no_blog' ) );
                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if( $list['uid'] != $this->mid ) {
                                $this->error( L( 'error_no_role' ) );
                        }

                        //处理提到的好友的格式数据
                        $mention = array_filter(unserialize( $list['friendId'] ));
                        if( !empty($mention) ) {
                                $friends = $this->api->user_getInfo( $mention,'id,name' );

                                foreach ( $friends as &$value ) {
                                        $value['uid'] = $value['id'];
                                        unset( $value['id'] );
                                }

                                $list['mention'] = $friends;
                        }else {
                                $list['mention'] = null;
                        }

                        $list['saveId'] = $list['id'];
                        unset( $list['id'] );

                        //定义连接
                        $link = __URL__."&act=doAddBlog";
                        unset ( $list['friendId'] );
                //编辑新的日志
                }else {
                        $link = __URL__."&act=doUpdate";
                        $dao = $this->blog;

                        if( false == $list = $this->blog->getBlogContent( $id,null,$_GET['mid'] ))
                                $this->error( L( 'error_no_blog' ) );

                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if( $list['uid'] != $this->mid )
                                $this->error( L( 'error_no_role' ) );
                }

                foreach ( $list['mention'] as &$value ) {
                        $value['face']  = getUserFace( $value['uid'] );
                }

                $list['mention'] = json_encode( $list['mention'] );
				                         $relist= $this->blog->getIsHot();
                        $this->assign('relist',$relist);
                 //表情控制
//                $smile     = array();
//                $smileType = $this->opts['ico_type'];
//
//
//                $smileList = $this->getSmile($smileType);
//                $smilePath = $this->getSmilePath($smileType);
//                $this->assign( 'smileList',$smileList );
//                $this->assign( 'smilePath',$smilePath );
                $this->assign( 'link',$link );
                $this->assign( $list );
                $this->display();
        }

        /**
         * doAddblog
         * 添加blog
         * @access public
         * @return void
         */
        public function doAddBlog() {
            $title = text($_POST['title']);


        	if(empty($title)) {
            	$this->error( "请填写标题" );
            }
        		if( mb_strlen($title, 'UTF-8') > 25 ) {
					$this->error( "标题不得大于25个字符" );
                }

                $content = text(html_entity_decode($_POST['content']));

                //检查是否为空
                if( empty($_POST['content']) || empty( $content )  ) {
                        $this->error( "请填写内容" );
                }

                //得到发日志人的名字
                $userName = $this->blog->getOneName( $this->mid );

                //处理发日志的数据
                $data = $this->__getPost();
                //添加日志
                $add = $this->blog->doAddBlog($data,true);

                //如果是有自动保存的数据。删除自动保存数据
                if( isset( $_POST['saveId'] ) && !empty( $_POST['saveId'] ) ) {
                        $mention = D( 'BlogOutline' );
                        $mention->where( 'id = '.$_POST['saveId'] )->delete();
                }

                if( $add ) {
					X('Credit')->setUserCredit($this->mid,'add_blog');
					$this->assign('jumpUrl', U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid)));
					$html = '【'.text($data['title']).'】'.getShort($content,80).U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid));
					$images = matchImages($data['content']);
					$image  = $images[0]?$images[0]:false;

					$this->ajaxData = array('url'=>U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid)),
						'id' =>$add,
					    'html'=>$html,
					    'image'=>$image,
						'title'=>t($_POST['title']),
					);
					$this->success('发表成功');
                }else {
                    $this->error( "添加失败" );
                }
        }

        /**
         * doUpdate
         * 执行更新日志动作
         * @access public
         * @return void
         */
        public function doUpdate() {
        		if (empty($_POST['title'])) {
                    $this->error( "请填写标题" );
                }
        		if (mb_strlen($_POST['title'], 'UTF-8') > 25 ) {
                	$this->error( "标题不能大于25个字符" );
                }
                $content = h($_POST['content']);

                if( empty( $content ) ) {
                    $this->error( "请填写内容" );
                }

                $userName = $this->blog->getOneName( $this->mid );

                $id       = intval($_POST['id']);
                //检查更新合法化
                if( $this->blog->where( 'id = '.$id )->getField( 'uid' ) != $this->mid ) {
                        $this->error( L('error_no_role') );
                }
                $data = $this->__getPost();
                $save = $this->blog->doSaveBlog($data,$id);

                if ($save) {
                    redirect(U('blog/Index/show', array('id'=>$id, 'mid'=>$this->mid)));
                } else {
                    $this->error( "修改失败" );
                }
        }

        private function __getPost() {
        		//得到发日志人的名字
                $userName = $this->blog->getOneName( $this->mid );
                $data['name']     = $userName['name'];
                $data['content']  = safe($_POST['content']);
                $data['uid']      = $this->mid;
                $data['category'] = intval($_POST['category']);
                $data['password'] = text($_POST['password']);
                $data['mention']  = $_POST['fri_ids'];
                $data['title']    = !empty($_POST['title']) ?text($_POST['title']):"无标题";
                $data['private']  = text( $_POST['privacy'] );
                $data['canableComment'] = intval(t($_POST['cc']));

                //处理attach数据
                $data['attach']         = serialize($this->__wipeVerticalArray($_POST['attach']));
                if(empty($_POST['attach']) || !isset($_POST['attach'])) {
                        $data['attach'] = null;
                }
                return $data;
        }

        private function __wipeVerticalArray($array) {
                $result = array();
                foreach($array as $key=>$value) {
                        $temp = explode('|', $value);
                        $result[$key]['id'] = $temp[0];
                        $result[$key]['name'] = $temp[1];
                }
                return $result;

        }

        /**
         * autoSave
         * 自动保存
         * @access public
         * @return void
         */
        public function autoSave(  ) {
                $content = trim(str_replace('&amp;nbsp;','',t($_POST['content'])));
                //检查是否为空
                if( empty($_POST['content']) || empty( $content )  ) {
                        $this->error( "请填写内容" );
                        exit();
                }

                $add="";
                $userName = $this->blog->getOneName( $this->mid );

                //处理数据
                $data['name']     = $userName['name'];
                $data['content']  = $_POST['content'];
                $data['uid']      = $this->mid;
                $data['category'] = $_POST['category'];
                $data['password'] = $_POST['password'];
                $data['mention']  = $_POST['mention'];
                $data['title']    = !empty($_POST['title']) ?$_POST['title']:"无标题";
                $data['private']  = $_POST['privacy'];
                $data['canableComment'] = intval(t($_POST['cc']));
                if( isset( $_POST['updata'] ) ) {
                //更新数据，而不是添加新的草稿
                        $add = intval(trim($_POST['updata']));
                        $result = $this->blog->updateAuto( $data,$add );
                }else {
                //自动保存
                        $add = $this->blog->autoSave($data);
                }
                if( $add || $result) {
                        echo date('Y-m-d h:i:s',time()).",".$add;
                }else {
                        echo -1;
                }
        }

        /**
         * outline
         * 草稿箱
         * @access public
         * @return void
         */
        public function outline(  ) {
                $this->assign( $list );
                $this->display();
        }

        /**
         * deleteOutline
         * 删除
         * @access public
         * @return void
         */
        public function deleteOutline(  ) {
                if( empty($_POST['id']) ) {
                        echo -1;
                        return;
                }


                $map['id'] = array( "in",array_filter( explode( ',' , $_POST['id'] ) ));
                $outline = D( 'BlogOutline' );
                //检查合法性
                if( $outline->where( $map )->getField( 'uid' ) != $this->mid ) {
                        echo -1;
                }

                if( $result = $outline->where( $map )->delete() ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * blogImport
         * 外站博客导入
         * @access public
         * @return void
         */
        public function blogImport() {
        //检测和返回本登录用户已经订阅的源地址
                $subscribe = $this->blog->checkGetSubscribe( $this->mid );

                $this->assign( "subscribe",$subscribe );
                $this->display();
        }

        /**
         * importList
         * 导入日志的列表
         * @access public
         * @return void
         */
        public function importList() {
                Import( '@.Unit.LeadIn' );
                $url = $_REQUEST['url'];
                //解析url。确定服务名和用户名
                $paramUrl = $this->_paramUrl($url);
                if ( false == $paramUrl ) $this->error( "URL解析失败" );
                $options = array(
                    "username" => $paramUrl['username'],
                    "service"  => $paramUrl['service'],
                );
                if(!is_string($paramUrl['username']) || !is_string($paramUrl['username'])){
                	    $this->error( "用户名必须为字符串" );
                        return false ;
                }
                $lead = new LeadIn( $options );
                //采集站点日志
                $result = $lead->get_source_data( $this->mid );
                if( false === $result ) {
                        $this->error( "此格式博客URL暂不支持，请检查链接" );
                        return false ;
                }

                //调用私有方法处理得到已经采集到但未导入的日志
                $importBlog = $this->_getImportData( $result );
                $category   = $this->blog->getCategory( $this->mid );



                //显示数据，供用户选择
                $this->assign( "importBlog",$importBlog );
                $this->assign( "category",$category );

                $this->display();

        }


        /**
         * doUpdateImport
         * 更新日志列表
         * @access public
         * @return void
         */
        public function doUpdateImport() {
                $sourceId = $_POST['id'];

                //根据源id获得服务名和用户名
                $map['id']                          = $sourceId[0];
                count( $sourceId ) >1 && $map['id'] = array('in',implode(",",$sourceId));
                $source_data                        = D( 'BlogSource' )->getSource( $map );
                //根据结果集进行更新采集
                Import( '@.Unit.LeadIn' );
                $leadIn = new LeadIn();

                $result = $leadIn->update_data( $source_data );

                //调用私有方法处理得到已经采集到但未导入的日志
                $importBlog = array();
                if( count( $sourceId ) >1 ) {
                        foreach ( $result as $value ) {
                                $temp = $this->_getImportData( $value );
                                if( empty($temp) ) {
                                        continue;
                                }
                                $importBlog = array_merge($importBlog,$temp);
                        }
                }else {
                        $importBlog = $this->_getImportData( $result );
                }
                $category   = $this->blog->getCategory( $this->mid );


                //显示数据，供用户选择
                $this->assign( "importBlog",$importBlog );
                $this->assign( "category",$category );

                $this->display('importList');
        }

        /**
         * doDeleteSubscribe
         * 删除订阅源
         * @access public
         * @return void
         */
        public function doDeleteSubscribe() {
                Import( '@.Unit.LeadIn' );

                $sourceId = array_filter( explode( ',' , $_POST['sourceId'] ) );

                if( empty($sourceId) ) {
                        echo -1;
                        exit;
                }
                $leadIn = new LeadIn();
                if( $leadIn->deleSubscribe( $sourceId,$this->mid ) ) {
                        echo 1;
                        exit;
                }

        }
        /**
         * doImport
         * 执行导入日志到本地日志数据库
         * @access public
         * @return void
         */
        public function doImport() {
                $id        = $_POST['id'];
                //从item取出数据信息

                $map['id'] = array( 'in',$id );
                $blog      = D( 'BlogItem' )->getItem( $map,'*' );
                unset( $map );
                foreach( $id as $key=>$value ) {
                        $map['title']    = $blog[$key]['title'];
                        $map['cTime']    = $blog[$key]['pubdate'];
                        $map['type']     = $blog[$key]['sourceId'];
                        $map['uid']      = $this->mid;
                        $name            = $this->blog->getOneName( $this->mid );
                        $map['content']  = $blog[$key]['summary'];
                        $map['name']     = $name['name'];
                        $map['category'] = $_POST["class_".$value];
                        $map['private']  = $_POST["privacy_".$value];
                        $result[$value] = $this->blog->doAddBlog( $map,false );
                        $feedTitle[] = $result[$value]['title'];
                        $result[$value] = $result[$value]['appid'];
                }
                //发送动态
                $title['count'] = count($feedTitle);
                $feedTitle = array_slice($feedTitle,0,3);
                $body['title'] = implode('<br />', $feedTitle);
                $title['uid'] = $this->mid;

                $this->blog->doFeed("blog_import",$title,$body);
                if( !empty( $result ) ) {
                //删除已删除的
                        D( 'BlogItem' )->deleteImportBlog( $result );
                        $this->redirect( 'my','Index' );
                }

        }

        /**
         * admin
         * 个人管理页面
         * @access public
         * @return void
         */
        public function admin() {
        	//获得分类名称
        	//获得分类下的日志数
            $category   = $this->__getBlogCategoryCount( $this->mid );
            $relist		= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            $this->assign( 'category',$category );
            global $ts;
            $this->setTitle("{$ts['app']['app_alias']}管理");
            $this->display();
        }


        /**
         * deleteCateFrame
         * 删除分类时，转移其中的日志
         * @access public
         * @return void
         */
        public function deleteCateFrame(  ) {
                $id       = $_GET['id'];
                $category = $this->blog->getCategory( $this->mid );
                foreach( $category as $key=>$value ) {
                        if( $value['id'] == $id)
                                unset( $category[$key] );
                }
                $this->assign( 'category',$category );
                $this->display();

        }
        /**
         * addCategory
         * 添加分类
         * @access public
         * @return void
         */
        public function addCategory() {
                $data['name'] = h(t($_POST['name']));
                $data['uid']  = $this->mid;

                $category   = D( 'BlogCategory' );
                $result = $category->addCategory($data,$this->blog);
        }

        public function addCategorys() {
                $this->display();
        }


        /**
         * editCategory
         * 修改分类
         * @access public
         * @return void
         */
        public function editCategory() {
        	foreach($_POST['name'] as $k => $v)
        		$_POST['name'][$k] = h(t($v));

        	if ( count($_POST['name']) != count(array_unique($_POST['name'])) )
        		$this->error('分类名不允许重复, 请重新输入');

			$category = D( 'BlogCategory' );
            $result   = $category->editCategory( $_POST['name'] );

            // 更新博客信息
            foreach ($_POST['name'] as $k => $v) {
            	M('blog')->where("`category`='{$k}'")->setField('category_title', $v);
            }

            $this->assign('jumpUrl', U('blog/Index/admin'));
            $this->success('保存成功');
        }

        /**
         * TODO 删除
         * recommend
         * 推荐操作
         * @access public
         * @return void
         */
        public function recommend(  ) {
                $name          = $this->blog->getOneName($this->mid);
                $map['blogid'] = $_POST['id'];
                $map['uid']    = $this->mid;
                $map['name']   = $name['name'];
                $map['type']   = "recommend";
                $action        = $_POST['act'];

                //添加推荐和推荐人数据。并且更新日志的推荐数
                if( $result = D( 'BlogMention' )->addRecommendUser( $map,$action ) ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * TODO 删除
         */
        public function commentSuccess() {
        //$post = str_replace('\\', '', stripslashes($_POST['data']));
                $result = json_decode(stripslashes($_POST['data']));  //json被反解析成了stdClass类型
                $count = $this->__setBlogCount($result->appid);

                //发送两条消息
                $data = $this->__getNotifyData($result);
                $this->api->comment_notify('blog',$data,$this->appId);
                echo $count;
        }
        /**
         * TODO 删除
         */
        private function __getNotifyData($data) {
        //发送两条消息
                $result['toUid'] = $data->toUid;
                $need  = $this->blog->where('id='.$data->appid)->field('uid,title')->find();


                $result['uids'] =$need['uid'];
                $result['url'] = sprintf('%s/Index/show/id/%s/mid/%s','{'.$this->appId.'}',$data->appid,$result['uids']);
                $result['title_body']['comment'] = $data->comment;
                $result['title_data']['title'] = sprintf("<a href='%s'>%s</a>",$result['url'],$need['title']);
                $result['title_data']['type']  = "日志";
                if(empty($data->toUid) && $this->mid != $need['uid'] && $data->quietly == 0){
                    $title['title'] = $result['title_data']['title'];
                    $uid = $result["uids"];
                    $title['user'] = '<a href="__TS__/space/'.$uid.'">'.getUserName($uid)."</a>";
                    $body['comment'] = $data->comment;
                    $this->blog->doFeed('blog_comment',$title,$body);
                }
                return $result;
        }
        /**
         * TODO 删除
         */
        public function deleteSuccess() {
                $id = $_POST['id'];
                echo $this->__setBlogCount($id);;
        }
        /**
         * TODO 删除
         */
        private function __setBlogCount($id) {
                $count = $this->api->comment_getCount('blog',$id);
                $result = $this->blog->setCount($id,$count);
                return $count;
        }

        /**
         * fileAway
         * 获取归档查询的数据
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function fileAway($uid,$cateId = null) {
                $findTime           = $_GET['date']; //获得传入的参数
                $this->blog->status = 1;
                $this->blog->uid    = $uid;
                isset( $cateId ) && $this->blog->category = $cateId;

                return $this->blog->fileAway( $findTime ) ;
        }

        /**
         * __getblog
         * 获得blog列表
         * @param int|array|string $uid uid
         * @access private
         * @return void
         */
        private function __getBlog ($uid=null,$field=null,$order=null,$limit=null) {
        	//将数字或者数字型字符串转换成整型
                is_numeric( $uid ) && $uid = intval( $uid );

                //获取被提到的好友数据列表
                if( isset( $_GET['mention'] ) ) {
                        $this->assign( "mention",1 );
                        return $this->blog->getMentionBlog($uid);
                }

                //归档
                if( isset( $_GET['date'] ) ) {
                        return $this->fileAway( $uid,$_GET['cateId'] );
                }

                //分类
                if( isset( $_GET['cateId'] ) ) {
                        $this->blog->category = $_GET['cateId'];
                        $this->assign( 'cateId',$_GET['cateId'] );
                }

                //给blog对象的uid属性赋值
                if( isset( $uid ) ) {
                        $map['uid']   = $uid;
                }else {
                        $gid     = $_GET['gid'];
                       // $friends = $this->api->friend_getGroupUids($gid);
                        if(empty($friends)) return false;
                        $map['uid']  = array( "in",$friends);
                        $this->blog->private = array('neq',2);
                }
                return $this->blog->getBlogList ($map, $field, $order);
        }

        /**
         * _getWiget
         * 获得需要传递给widget的数据
         * @param mixed $link
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function _getWiget($link,$uid) {
                $condition['uid'] = $uid;
                if( empty( $uid) )
                        unset( $condition);
                $map['fileaway']  = L( 'fileaway' );
                $map['link']      = $link;
                $map['condition'] = $condition ;
                $map['limit']     = $this->blog->getConfig( 'fileawaypage' );
                $map['tableName'] = C('DB_PREFIX').'_blog';
                $map['APP']       = __APP__;
                return $map;
        }

        /**
         * _paramUrl
         * 解析导入的路径
         * @param mixed $url
         * @access private
         * @return void
         */
        private function _paramUrl( $url ) {
        //判断合法性
                if ( false == preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $url)) {
                        $this->error( "不是合法的URL格式" );
                        return false;
                }
                $result = array( 'service'=>'','username'=>'' );
                $url      = str_replace( 'http://','',$url );
                $urlarray = explode( '/',$url );
                $target   = array( '163','baidu','spaces','sohu','sina','msn' );
                foreach( $target as $value ) {
                        if( strpos( $urlarray[0],$value ) ) {
                                switch( $value ) {
                                        case '163':
                                                $result['service'] = '163';
                                                $temp = explode( '.',$urlarray[0] );
                                                $result['username'] = $temp[0];
                                                break;
                                        case 'baidu':
                                                $result['service'] = 'baidu';
                                                $result['username'] = $urlarray[1];
                                                break;
                                        case 'sohu':
                                                $result['service'] = 'sohu';
                                                $temp = explode( '.',$urlarray[0] );
                                                $result['username'] = $temp[0];
                                                break;
                                        case 'sina':
                                                $result['service'] = 'sina';
                                                $result['username'] = isset( $urlarray[2] )?$urlarray[2]:$urlarray[1];
                                                break;
                                        case 'msn':
                                                $result['service'] = 'msn';
                                                $result['username'] = $urlarray[1];
                                                break;
                                        case 'spaces':
                                                $result['service'] = 'msn';
                                                $temp = explode( '.',$urlarray[0] );
                                                $result['username'] = $temp[0];
                                                break;
                                        default:
                                                $this->assign( '错误的URL' );
                                                return false;
                                //throw new ThinkException( "错误的url" );
                                }
                        }
                }
                //检测格式。过滤掉特殊格式。防止解析出错
                if( strpos( '.', $result['username'] ) ) {
                        return false;
                }elseif( strpos( '/',$result['username'] ) ) {
                        return false;
                }
                return $result;
        }

        /**
         * _getImportData
         * 获得引入日志的数据
         * @param mixed $result
         * @access private
         * @return void
         */
        private function _getImportData( $result ) {
                if( !empty( $result['change_ids'] ) ) {
                        $map = "`id` IN ( ".implode( ",",$result['change_ids'] )." )";
                }

                if( !empty( $result['change_ids'] ) && !empty( $result['source_id'] ) ) {
                        $map .= " OR ";
                }

                if( !empty( $result['source_id'] ) ) {
                        $map .= '(`sourceId` = '.$result['source_id']." AND `boot` = 0)";
                }

                $item = D( 'BlogItem' );
                $importBlog = $item->getItem( $map,'id,link,title' );
                return $importBlog;
        }



        /**
         * _checkCategory
         * 检查分类是否合法
         * @param mixed $cateId
         * @param mixed $category
         * @static
         * @access private
         * @return void
         */
        private static function _checkCategory( $cateId,$category ) {
                $temp = array();
                foreach( $category as $value ) {
                        $temp[] = $value['id'];
                }
                return in_array($cateId,$temp);
        }
        private function _checkUser( $uid ) {
                $result = $this->api->user_getInfo($uid,'id');
                return $result;
        }
}