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
       
        /**
         * __initialize
         * 初始化
         * @access public
         * @return void
         */
         
        private $order;
        public function _initialize() {
            $this->assign('shop',D('AppConfig')->getConfig());
        	//parent::_initialize();
                $this->order = D('Order');
            $this->assign('jftype',D('Category')->getCategory());
            if(MODULE_NAME != 'my'){
                $this->assign('_header_tab_on_my','0');
            }else{
                $this->assign('_header_tab_on_my','1');
            }
            
            
        }
        
        public function index(){
            $c = intval($_GET[category]);
            $w = array();
            if($c != ''){
                $w[category] = $c;
            }
            $this->assign('res',D('Goods')->getGoodsList($w,'id desc','20'));
            $this->assign('category',$_GET[category]);
            $this->display();
        }
        
        public function info(){
            
            $goodsId = intval($_GET[id]);
            $this->assign('goods',D('Goods')->getGoodsInfo($goodsId));
            $this->assign('follow',getUserFollow($this->mid));
            $this->assign('userAdr',getUserAddress($this->mid));
            
             $info = D('Order')->getOrderList(array('gid'=>$goodsId),'id desc',10);
             $this->assign('list',$info[data]);
             $this->display();
        }
        public function dh(){
            global $ts;
            $gid = intval($_GET[id]);
            $goodsInfo = D('Goods')->getGoodsInfo($gid);
            if(!$goodsInfo){$this->error('该商品不存在或者已经下架');}
            if($goodsInfo[sy]<1){$this->error('对不起本商品已经没有了额');}
            if(!$this->order->chackxz($gid,$this->mid)){$this->error('你还不满足领取条件');}
            if(!$this->order->chackscore($gid)){
                $this->error('您的积分不足哟。');
            }
      
            $data = $this->order->create();
            if(!$data){$this->error($this->order->getError());}
            
            $data[gid] = $gid;
            $data[gname] = $goodsInfo[name];
            $data[price] = $goodsInfo[price];
            $data[number] = '1';
            $data[oneprice] = $goodsInfo['price'];
            $data[state] = '0';
            $data[username] = $ts['user'][uname];
            $data[time]  = time();
            $data[uid]   = $this->mid;
            $orid = $this->order->add($data);
            if(!$orid){$this->error('写入订单失败,请联系管理员');}
            $kou = $this->order->kou($gid,$this->mid);
            S('S_userInfo_'.$this->mid,null);
                $d[id] = $orid;
                $d[state] ='1';
                $this->order->save($d);
            $d3[id] = $gid;
            $d3[sy] = $goodsInfo[sy]-1;
            M('shop_goods')->save($d3);
            $this->success('兑换成功');
           print_r($data);
           // $this->display();
        }
	
}