<?php

class OrderModel extends Model
{   
    
    protected $tableName = 'shop_order';
    protected $_validate  =  array(
            array('name','require','收货人姓名必须填写'), //默认情况下用正则进行验证
            array('address','require','送货地址必须填写'),
            array('postcode','require','邮编必须填写'),
            array('mobile','require','手机号码必须填写'),
            array('qq','require','联系QQ必须填写')
    );
    public function chackscore($gid){
        $goodsInfo = D('Goods')->getGoodsInfo($gid);
        global $ts;
        $userInfo = $ts[user][credit];

        foreach($goodsInfo[price3] as $key =>$value){
           if($userInfo[$key][credit] < $value){
            return false;
           }
        }
        return true;
    }
    
    public function chackxz($gid,$uid){
         $goodsInfo = D('Goods')->getGoodsInfo($gid);
         $userInfo = getUserFollow($uid);
         if($goodsInfo[x_w]>$userInfo[weibo] || $goodsInfo[x_f] > $userInfo[follower] || $goodsInfo[x_f] >$userInfo[following] ){
            return false;
         }else{
            return true;
         }
    }
    
    public function kou($gid,$uid,$type='-1'){
        $goodsInfo = D('Goods')->getGoodsInfo($gid);
        if($type == '-1'){
            if(!$this->chackscore($gid)) return false;
        }
        return service('Credit')->setUserCredit($uid,$goodsInfo[price3],$type);
    }
    
    public function getOrderList($w = array(),$or = 'id desc',$li = '10'){

        $result      = $this->where( $w )->order( $or )->findPage($li) ;
        foreach($result[data] as $key=>$value){
            $result[data][$key][time2] = date('Y-m-d H:i:s',$value[time]);
            $price = json_decode($value[price]);
            $p = '';
            $t = getJfType();
            foreach($price as $k => $v){
                $p = $p.$t[$k].'：'.$v.' ';
            }
            $result[data][$key][price2] = $p;
        }
        return $result;
    }
    
    
 


    
    
}
?>