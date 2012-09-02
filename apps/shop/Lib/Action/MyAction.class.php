<?php
class MyAction extends Action {
	private $order;
	public function _initialize() {
		$this->order = D('Order');
        $this->assign('_header_tab_on_my','1');
        $this->assign('shop',D('AppConfig')->getConfig());
    }
    
    public function index(){
        
        $this->assign('order',D('Order')->getOrderList(array('uid'=>$this->mid),'id desc',15));
        $this->display();
    }
    
    public function orderdo(){
        $orid = intval($_GET['id']);
        $info = D('Order')->getOrderList(array('id'=>$orid,'uid'=>$this->mid),'id desc',1);
        $info = $info[data][0];if(!is_array($info)){exit('订单不存在或者无权限');}
        
        if($_POST){
                $data[id] = $orid;
                $data[state] ='4';
                $data[uid] = $this->mid;
                M('shop_order')->save($data); 
                exit('1');
            }
        $this->assign('order',$info);
        $this->display();
    }
}
