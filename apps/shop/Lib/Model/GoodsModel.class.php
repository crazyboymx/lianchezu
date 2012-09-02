<?php

class GoodsModel extends Model
{   
    
    protected $tableName = 'shop_goods';
    
    protected $_validate  =  array(
            array('name','require','商品名称必须填写'), //默认情况下用正则进行验证
    );
    protected $_auto = array ( 
            array('time','time',3,'function'), // 对create_time字段在更新的时候写入当前时间戳
            array('pic','uppic',3,'callback'),
            array('sy','sy',3,'callback'),
             array('price','zpri',3,'callback')
            

    );
    
    
 	/**
	 * 上传附件
	 * 
	 * description 自动完成
	 */   
    public function uppic(){
        
        if(!empty($_FILES['pic']['name'])){
            $logo = X('Xattach')->upload('shop');
            if($logo['status']){
                $logofile = __ROOT__.'/data/uploads/'.$logo['info'][0]['savepath'].$logo['info'][0]['savename'];
            }
            return $logofile;
        }else{
            return $_POST['pic_url'];
        }
    }
    
    
   	/**
	 * 时间转换
	 * 
	 * description 自动完成 ，未启用
	 */ 
       
    public function zdate($endtime){  
        return $endtime;
    }
    
    
   	/**
	 * 积分转换
	 * 
	 * description 自动完成
	 */   
    public function zpri(){
        foreach($_POST[jf] as $key=>$value){
            if($value =='' || $value=='0'){
                unset($_POST[jf][$key]);
            }else{
                $is_true = '1';
                $_POST[jf][$key] = intval($value);
            }
        }
        
        if($is_true!='1'){exit('消耗的积分不能都为空或者为0');}
        return json_encode($_POST[jf]);
        
    }
    
    
   	/**
	 * 剩余数量
	 * 
	 * description 自动完成
	 */   
    public function sy(){
        return $_POST[num];
    }
    
    
    public function getGoodsList($w = array(),$or = 'id desc',$li = '10'){

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
    
    public function getGoodsInfo($id){
        $w[id] = $id;
        $info = $this->where($w)->find();
        if(!$info){return false;}
        $price = json_decode($info[price],true);
        $info[price3] = $price;
        $p = '';
        $t = getJfType();
        foreach($price as $k => $v){
                $p = $p.$t[$k].'：'.$v.' ';
        }
        $info[price2] = $p;
        return $info;
    }


    
    
}
?>