<?php
function yan_url( $str )
{
		$Model = new Model( );
		$weibo_data = $Model->query( "select * from ".c( "DB_PREFIX" )."taobaoke_weibo where goods_url='".$str."'" );
		if ( count( $weibo_data ) <= 0 )
		{
				return FALSE;
		}
		else
		{
				return TRUE;
		}
}

function rand_str( )
{
		$str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$n = 8;
		$len = strlen( $str ) - 1;
		$i = 0;
		for ( ;	$i < $n;	++$i	)
		{
				$s .= $str[rand( 0, $len )];
		}
		return $s;
}

function goods_getarrayinfo( $good_id, $host, $good_url )
{
		$return = "";
		if ( "taobao.com" == $host || "tmall.com" == $host )
		{
				while ( TRUE )
				{
						$goods_str = rand_str( );
						$yan = yan_url( $goods_str );
						if ( !$yan )
						{
								break;
						}
				}
				$return['rand_url'] = $goods_str;
				include_once( SITE_PATH."/addons/libs/goods/Taobao.php" );
				$goods = new Taobao( );
				$goods_data = $goods->get_taobaoke_array( "flewhigh", $good_id, $goods_str );
				$goods_fenlei = $goods->get_taobaoke_fenlei( $goods_data['cid'] );
				$goods_taobaoke_array = array_merge( $goods_fenlei, $goods_data );
				if ( $goods_taobaoke_array['num_iid'] != "" )
				{
						$return['host'] = "taobao";
						$return['data'] = $goods_taobaoke_array;
						$return['taoke'] = 1;
						return $return;
				}
				else
				{
						$goods_arrayss = $goods->get_taobao_array( $good_id );
						$goods_fenlei = $goods->get_taobaoke_fenlei( $goods_arrayss['cid'] );
						$goods_array = array_merge( $goods_fenlei, $goods_arrayss );
						if ( $goods_array )
						{
								$return['host'] = "taobao";
								$return['data'] = $goods_array;
								$return['taoke'] = 0;
								return $return;
						}
						else
						{
								$return['error'] = 1;
								return $return;
						}
				}
		}
		if ( "paipai.com" == $host )
		{
				include_once( SITE_PATH."/addons/libs/goods/Paipai.php" );
				$paipai_array = get_paipai_goods( $good_id, "xml", $good_url );
				if ( $paipai_array['error_Code'] == 0 )
				{
						$return['host'] = "paipai";
						$return['data']['title'] = $paipai_array['itemName'];
						$return['data']['price'] = $paipai_array['itemPrice'] / 100;
						$return['data']['pic_url'] = $paipai_array['picLink'];
						$return['data']['click_url'] = $good_url;
						$return['taoke'] = 0;
						while ( TRUE )
						{
								$goods_str = rand_str( );
								$yan = yan_url( $goods_str );
								if ( !$yan )
								{
										break;
								}
						}
						$return['rand_url'] = $goods_str;
						return $return;
				}
		}
}

?>
