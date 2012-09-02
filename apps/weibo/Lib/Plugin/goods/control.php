<?php

include_once 'function.php' ;


switch ($do_type)
{
    case 'before_publish':

        $good_id = t($_POST['id']) ;
        $good_fl = t($_POST['fenlei']) ;
        $good_url = t($_POST['url']) ;
        if ( 'taobao.com' == $good_fl || 'tmall.com' == $good_fl )
        {

            $arrayinfo = goods_getarrayinfo($good_id , $good_fl) ;
        }

        if ( 'paipai.com' == $good_fl )
        {
            $arrayinfo = goods_getarrayinfo($good_id , $good_fl , $good_url) ;
        }

        exit(json_encode($arrayinfo)) ;
        break ;
    case 'publish':
        if ( $_POST['g_host'] == "taobao" )
        {
            $typedata['small_pic'] = t($_POST['publish_type_data']) . "_60x60.jpg" ;
			$typedata['middle_pic'] = t($_POST['publish_type_data']) . "_210x1000.jpg" ;
            $typedata['big_pic'] = t($_POST['publish_type_data']) . "_310x310.jpg" ;
            $typedata['picurl'] = t($_POST['publish_type_data']) ."_160x160.jpg" ;
			$typedata['originalurl'] = t($_POST['publish_type_data']) ;
			
        } elseif ( $_POST['g_host'] == "paipai" )
        {
            $picurl = substr(t($_POST['publish_type_data']) , 0 , -3) ;
            $typedata['small_pic'] = $picurl . "60x60.jpg" ;
			$typedata['middle_pic'] = $picurl ."200x200.jpg" ;
            $typedata['big_pic'] = $picurl ."300x300.jpg" ;
            $typedata['picurl'] = $picurl . "160x160.jpg" ;
			$typedata['originalurl'] = $picurl ."jpg" ;

        }
        $typedata['num_iid']=t($_POST['num_iid']) ;
        $typedata['goodsurl'] = t($_POST['goods_url']) ;
        $typedata['gurl'] = t($_POST['g_url']) ;
        $typedata['g_host'] = t($_POST['g_host']) ;
        $typedata['g_title'] = js_unescape($_POST['g_title']) ;
        $typedata['price'] = t($_POST['price']) ;
        $typedata['taoke'] = t($_POST['taoke']) ;

        $typedata['seller_cids'] = t($_POST['seller_cids']) ;
        $typedata['cid'] = t($_POST['cid']) ;
		
					
			$Mpic=getimagesize($typedata['middle_pic']);
				
				$typedata['mwidth']   = $Mpic[0];	
				$typedata['mheight']   = $Mpic[1];

        if ( t($_POST['taoke']) == 1 )
        {
            $typedata['commission'] = t($_POST['commission']) ;
            $typedata['commission_rate'] = t($_POST['commission_rate']) ;
        }
        break ;
}
