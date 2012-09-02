<?php
header("Content-type:text/html; charset=UTF-8") ;
date_default_timezone_set('Asia/Chongqing') ;
include_once("taobao/Taoapi.php") ;

class Taobao {
    //获得淘宝客数据
    function get_taobaoke_url($nick="" , $num_iid="",$goods_str="")
    {
        $Taoapi_Config = Taoapi_Config::Init() ;
        $Taoapi_Config->setCharset('UTF-8') ;
        $Taoapi = new Taoapi() ;
        $Taoapi->method = 'taobao.taobaoke.items.convert' ;
        $Taoapi->fields = 'num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,seller_credit_score,item_location,shop_click_url' ;
        $Taoapi->nick = $nick ;
        $Taoapi->outer_code=$goods_str;
        $Taoapi->num_iids = $num_iid ;
        $TaobaokeData = $Taoapi->Send('get' , 'xml')->getArrayData() ;
        if($TaobaokeData['total_results']==1)
        {
            $goods_data=$TaobaokeData['taobaoke_items']['taobaoke_item'];
            $data['title'] = $goods_data['title'] ;
            $data['price'] = $goods_data['price'] ;
            $data['nick'] = $goods_data['nick'];
            $data['pic_url'] = $goods_data['pic_url'] ;
            $data['click_url'] = $goods_data['click_url'] ;
            $data['commission'] = $goods_data['commission'] /2;
            $data['commission_rate'] = $goods_data['commission_rate'] / 100 ;
            $data['num_iid']=$goods_data['num_iid'] ;
            $cid_goods=$this->get_taobao_array($num_iid) ;

            if($cid_goods)
            {
                $data['cid'] = $cid_goods['cid'] ;
                $data['seller_cids'] = $cid_goods['seller_cids'] ;

                return $data ;
            }else{
                return FALSE;
            }
        }else{
            return $TaobaokeData;
        }
    }

    //获得淘宝客数据
    function get_taobaoke_array($nick="" , $num_iid="",$goods_str="")
    {
        $Taoapi_Config = Taoapi_Config::Init() ;
        $Taoapi_Config->setCharset('UTF-8') ;
        $Taoapi = new Taoapi() ;
        $Taoapi->method = 'taobao.taobaoke.items.convert' ;
        $Taoapi->fields = 'num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,seller_credit_score,item_location,shop_click_url' ;
        $Taoapi->nick = $nick ;
        $Taoapi->outer_code=$goods_str;
        $Taoapi->num_iids = $num_iid ;
        $TaobaokeData = $Taoapi->Send('get' , 'xml')->getArrayData() ;
        if($TaobaokeData['total_results']==1)
        {
            $goods_data=$TaobaokeData['taobaoke_items']['taobaoke_item'];
            $data['title'] = $goods_data['title'] ;
            $data['price'] = $goods_data['price'] ;
            $data['nick'] = $goods_data['nick'] ;
            $data['pic_url'] = $goods_data['pic_url'] ;
            $data['click_url'] = $goods_data['click_url'] ;
            $data['commission'] = $goods_data['commission'] /2;
            $data['commission_rate'] = $goods_data['commission_rate'] / 100 ;
            $data['num_iid']=$goods_data['num_iid'] ;
            $cid_goods=$this->get_taobao_array($num_iid) ;

            if($cid_goods)
            {
                $data['cid'] = $cid_goods['cid'] ;
                $data['seller_cids'] = $cid_goods['seller_cids'] ;

                return $data ;
            }else{
                return FALSE;
            }
        }else{
            return $TaobaokeData;
        }
    }

    function get_taobao_fl_id($num_iid="")
    {
        $good_arr=  $this->get_taobao_array($num_iid);
        $arr['cid']=$good_arr['item']['cid'];
        $arr['seller_cids']=$good_arr['item']['seller_cids'];
        return $arr;
    }

    function get_taobaoke_fenlei($catid){
        $Taoapi_Config = Taoapi_Config::Init() ;
        $Taoapi_Config->setCharset('UTF-8') ;
        $Taoapi = new Taoapi() ;
        $Taoapi->method = 'taobao.itemcats.get';
        $Taoapi->fields = 'cid,name,parent_cid,is_parent';
        $Taoapi->cids = $catid;
        $TaoapiCat = $Taoapi->Send('get','xml')->getArrayData();
        $result_cat = $TaoapiCat["item_cats"]["item_cat"];
        $cat_name = $result_cat["name"];
        $is_parent = $result_cat["is_parent"];
        return array('fenlei'=>$cat_name);
    }
    function get_taobao_array($num_iid="")
    {
        $Taoapi_Config = Taoapi_Config::Init() ;
        $Taoapi_Config->setCharset('UTF-8') ;
        $Taoapi = new Taoapi() ;
        $Taoapi->method = 'taobao.item.get' ;
        $Taoapi->fields = 'num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,cid,seller_cids,seller_credit_score,item_location,shop_click_url' ;
        $Taoapi->num_iid = $num_iid ;
        $TaobaokeData = $Taoapi->Send('get' , 'xml')->getArrayData();

        if(  array_key_exists("item",$TaobaokeData))
        {
            $goods_data=$TaobaokeData['item'];
            $data['title'] = $goods_data['title'] ;
            $data['price'] = $goods_data['price'] ;
            $data['pic_url'] = $goods_data['pic_url'] ;
            $data['click_url'] = "http://item.taobao.com/item.htm?id=" . $goods_data['num_iid'] ;
            $data['num_iid']=$goods_data['num_iid'];
            $data['cid']=$goods_data['cid'];
            $data['seller_cids']=$goods_data['seller_cids'];
            return $data ;
        }else{
            return FALSE;
        }
    }
}
