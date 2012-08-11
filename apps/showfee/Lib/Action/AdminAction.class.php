<?php
	import('admin.Action.AdministratorAction');
	class AdminAction extends AdministratorAction {


        /**
         * config 
         * EventConfig的实例化对象
         * @var mixed
         * @access private
         */

        public function _initialize(){
	        //管理权限判定
	        parent::_initialize();
        }

        /**
         * basic 
         * 基础设置管理
         * @access public
         * @return void
         */
        public function index (){
        	$config   = model('Xdata')->lget('showfee');
            $this->assign($config);

            $credit_types = X('Credit')->getCreditType();
            $this->assign('credit_types',$credit_types); 

            $this->display();

        } 

        /**
         * doChangeBase 
         * 修改全局设置
         * @access public
         * @return void
         */
        public function doChangeBase (){
	        //变量过滤 todo:更细致的过滤
	        foreach($_POST as $k=>$v){
	            $config[$k] =   t($v);
	        }
	        //$config['limitsuffix'] = preg_replace("/bmp\|||\|bmp/",'',$config['photo_file_ext']);//过滤bmp
	        if(model('Xdata')->lput('event',$config)){
	            $this->assign('jumpUrl', U('event/Admin/index'));
	            $this->success('设置成功！');
	        }else{
	            $this->error('设置失败！');
	        }
        }

        /**
         * eventlist 
         * 获得所有人的eventlist
         * @access public
         * @return void
         */
        public function feetypelist (){
        	//get搜索参数转post
	        if(!empty($_GET['type'])){
	           $_POST['type'] = $_GET['type'];
	        }
            //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
	        if ( !empty($_POST) ) {
	            $_SESSION['admin_search'] = serialize($_POST);
	        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
	            $_POST = unserialize($_SESSION['admin_search']);
	        }else {
	            unset($_SESSION['admin_search']);
	        }   
	        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');   
	
	        $_POST['uid']   && $map['uid']    =   intval($_POST['uid']);
	        $_POST['id']    && $map['id']     =   intval($_POST['id']);
            $_POST['type']  && $map['type']   =   intval($_POST['type']);
            $_POST['title'] && $map['title'] =   array( 'like','%'.t( $_POST['title'] ).'%' );
            //处理时间
            $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t( $_POST['sTime'] ),t( $_POST['eTime'] ) );
	        //处理排序过程
            $order = isset( $_POST['sorder'] )?t( $_POST['sorder'] )." ".t( $_POST['eorder'] ):"cTime DESC";
	        $_POST['limit']     && $limit         =   intval( t( $_POST['limit'] ) );
            
            $list  = D('ShowfeeFeeType')->getAllFeeType();
            //$order && $list  = $this->feetype->getList($map,$order,$limit);
            //$type_list = D('EventType')->getType();
            $this->assign( $_POST );
            $this->assign( "list",$list );
            //$this->assign( 'type_list',$type_list );
            $this->display();
        }


        /*
        /**
         * doEditType 
         * 修改分类
         * @access public
         * @return void
         */
        public function doEditFeeType(){
            $_POST['id']   = intval($_POST['id']);
            $_POST['name'] = t($_POST['name']);
            $feetype= D('ShowfeeFeeType');
            if($_POST['id']==''){
                $result =$feetype->addFeeType($_POST);
            }else{
                $result = $feetype ->editFeeType($_POST); 
            }
            echo $result;
        }

        /**
         * doEditType 
         * 删除分类
         * @access public
         * @return void
         */
        public function deleteFeeType(){
            $id['id']      = array( "in",$_POST['id']);
            $feetype = D( 'ShowfeeFeeType' );
            if( $result = $feetype->deleteFeeType( $id ) ){
                if ( !strpos($_POST['id'],",") ){
                    echo 2;            //说明只是删除一个
                }else{
                    echo 1;            //删除多个
                }
            }else{
                echo $result;
            }
        }
    }
