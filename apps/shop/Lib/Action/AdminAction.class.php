<?php
    /**
     * AdminAction 
     * 积分商城管理
     * @uses Action
     * @package Admin
     * @version $id$
     * @copyright 2009-2011 small 
     * @author small <137283358@qq.com> 
     * @license PHP Version 5.2 {@link }
     */
    import('admin.Action.AdministratorAction');
	  class AdminAction extends AdministratorAction {
        private $shop;
        private $config;
        private $category;
        public function _initialize(){
        	parent::_initialize();
        	$this->config = D( 'AppConfig' );
            $this->shop  = D( 'Shop' );
        }
        /**
         * basic 
         * 基础设置管理
         * @access public
         * @return void
         */
        public function index (){
            if($_POST){
                $data[gg] = $_POST[gg];
                $this->config->editConfig($data);
            }
            
            $this->assign('shop',$this->config->getConfig());
            $this->display();
        }
        
        public function category (){
            $this->assign('category',D('Category')->getCategory());
            $this->display();
            
        }
        public function editCategory(){
            $gid = $_GET['gid'];
            $category = D('Category')->getCategoryInfo($gid);
            $this->assign('category',$category);
            $this->display();
        }
        public function editCategoryDo(){
            if($_GET[tdo] =='edit'){
                
                echo D('Category')->editCategory($_POST);
            }else{
                echo D('Category')->addCategory($_POST);
            }
            
        }
        public function isCategoryEmpty(){
            echo D('Category')->isCategoryEmpty(intval($_POST[gid]));
        }
        
        public function doDeleteCategory(){
             echo D('Category')->doDeleteCategory(intval($_POST[gid]));
        }
        
        
        public function goodslist(){
            $this->assign('res',D('Goods')->getGoodsList(array(),'id desc','20'));
            $this->display();
        }
        
        public function addgoods(){
            $jftype = service('Credit')->getCreditType();
            if($_GET[id] != ''){
                $this->assign('goods',D('Goods')->getGoodsInfo($_GET[id]));
            }
            $this->assign('jftype',$jftype);
            $this->assign('category',D('Category')->getCategory());
            $this->display();
        }
        
        public function addGoodsDo(){
            $data = D('Goods')->create();
            if(!$data){$this->error(D('Goods')->getError());}
            if($data[id] ==''){
                D('Goods')->add($data);
            }else{
                D('Goods')->save($data);
            }
            $this->success('操作成功');
        }
        
        public function order(){
            $this->assign('res',D('Order')->getOrderList(array(),'id desc','20'));
            $this->display();
        }
        
        public function orderdo(){
            $orid = intval($_GET['id']);
            $info = D('Order')->getOrderList(array('id'=>$orid),'id desc',1);
            $info = $info[data][0];
            if($_POST){
                $data[id] = $orid;
                $data[state] = $_POST[type];
                if($data[state] == '2'){
                    $data[fhd] = $_POST[fhd];
                }
                M('shop_order')->save($data);
                
                if($data[state] == '3'){
                     D('Order')->kou($info[gid],$this->uid,'1');
                }
                   echo '1';
                exit();
            }
            
            
            $this->assign('order',$info);
            $this->display();
        }
        

    }
