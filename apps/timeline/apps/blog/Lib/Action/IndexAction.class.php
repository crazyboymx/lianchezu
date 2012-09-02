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
         //、、、、、、、、、、、、、、
        }
		
		
		//在增加下面两个方法
		public function eblog() {
			$this->edit();
			}
			
		public function doDelBlog(  ) {

                $this->blog->id = $_REQUEST['id']; //要删除的id;
                $result         = $this->blog->doDeleteblog(null,$this->mid);

                if( false != $result) {
					X('Credit')->setUserCredit($this->mid,'delete_blog');
					echo 1;exit;
                }else {
                    echo 0;exit;
                }
        }

        ////增加内容结束、、、、、、、
		
}