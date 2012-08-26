<?php
class Taobao_main
{

				public function get_taobaoke_url_main( $nick = "", $num_iid = "", $goods_str = "" )
				{
								$Taoapi_Config = ( );
								$Taoapi_Config->setCharset( "UTF-8" );
								$Taoapi = new Taoapi( );
								$Taoapi->method = "taobao.taobaoke.items.convert";
								$Taoapi->fields = "num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,seller_credit_score,item_location,shop_click_url";
								$Taoapi->nick = $nick;
								$Taoapi->outer_code = $goods_str;
								$Taoapi->num_iids = $num_iid;
								$TaobaokeData = $Taoapi->Send( "get", "xml" )->getArrayData( );
								if ( $TaobaokeData['total_results'] == 1 )
								{
												$goods_data = $TaobaokeData['taobaoke_items']['taobaoke_item'];
												$data['title'] = $goods_data['title'];
												$data['price'] = $goods_data['price'];
												$data['pic_url'] = $goods_data['pic_url'];
												$data['click_url'] = $goods_data['click_url'];
												$data['commission'] = $goods_data['commission'];
												$data['commission_rate'] = $goods_data['commission_rate'] / 100;
												$data['num_iid'] = $goods_data['num_iid'];
												$cid_goods = $this->get_taobao_array_main( $num_iid );
												if ( $cid_goods )
												{
																$data['cid'] = $cid_goods['cid'];
																$data['seller_cids'] = $cid_goods['seller_cids'];
																return $data;
												}
												else
												{
																return FALSE;
												}
								}
								else
								{
												return $TaobaokeData;
								}
				}

				public function get_taobaoke_array_main( $nick = "", $num_iid = "", $goods_str = "" )
				{
								$Taoapi_Config = ( );
								$Taoapi_Config->setCharset( "UTF-8" );
								$Taoapi = new Taoapi( );
								$Taoapi->method = "taobao.taobaoke.items.convert";
								$Taoapi->fields = "num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,seller_credit_score,item_location,shop_click_url";
								$Taoapi->nick = $nick;
								$Taoapi->outer_code = $goods_str;
								$Taoapi->num_iids = $num_iid;
								$TaobaokeData = $Taoapi->Send( "get", "xml" )->getArrayData( );
								if ( $TaobaokeData['total_results'] == 1 )
								{
												$goods_data = $TaobaokeData['taobaoke_items']['taobaoke_item'];
												$data['title'] = $goods_data['title'];
												$data['price'] = $goods_data['price'];
												$data['pic_url'] = $goods_data['pic_url'];
												$data['click_url'] = $goods_data['click_url'];
												$data['commission'] = $goods_data['commission'];
												$data['commission_rate'] = $goods_data['commission_rate'] / 100;
												$data['num_iid'] = $goods_data['num_iid'];
												$cid_goods = $this->get_taobao_array_main( $num_iid );
												if ( $cid_goods )
												{
																$data['cid'] = $cid_goods['cid'];
																$data['seller_cids'] = $cid_goods['seller_cids'];
																return $data;
												}
												else
												{
																return FALSE;
												}
								}
								else
								{
												return $TaobaokeData;
								}
				}

				public function get_taobao_fl_id_main( $num_iid = "" )
				{
								$good_arr = $this->get_taobao_array_main( $num_iid );
								$arr['cid'] = $good_arr['item']['cid'];
								$arr['seller_cids'] = $good_arr['item']['seller_cids'];
								return $arr;
				}

				public function get_taobao_array_main( $num_iid = "" )
				{
								$Taoapi_Config = ( );
								$Taoapi_Config->setCharset( "UTF-8" );
								$Taoapi = new Taoapi( );
								$Taoapi->method = "taobao.item.get";
								$Taoapi->fields = "num_iid,iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,cid,seller_cids,seller_credit_score,item_location,shop_click_url";
								$Taoapi->num_iid = $num_iid;
								$TaobaokeData = $Taoapi->Send( "get", "xml" )->getArrayData( );
								if ( array_key_exists( "item", $TaobaokeData ) )
								{
												$goods_data = $TaobaokeData['item'];
												$data['title'] = $goods_data['title'];
												$data['price'] = $goods_data['price'];
												$data['pic_url'] = $goods_data['pic_url'];
												$data['click_url'] = "http://item.taobao.com/item.htm?id=".$goods_data['num_iid'];
												$data['num_iid'] = $goods_data['num_iid'];
												$data['cid'] = $goods_data['cid'];
												$data['seller_cids'] = $goods_data['seller_cids'];
												return $data;
								}
								else
								{
												return FALSE;
								}
				}

}

$goods = new Taobao( );
$md = "ts".$this->mid;
$goods_taobaoke_array = $data[0]['type_data']['num_iid']( "flewhigh", $data[0]['type_data']['num_iid'], $md );
header( "Location: ".$goods_taobaoke_array['click_url']."" );
exit( );
?>
