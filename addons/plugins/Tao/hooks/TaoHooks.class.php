<?php
class TaoHooks extends Hooks
{
    protected static $validTypeAlias = array(
            '23'=>'商品'
    );

    public function weibo_js_plugin()
    {
        echo '<script type="text/javascript" src="'.__ROOT__.'/addons/plugins/Tao/html/tao.js'.'"></script>';
        echo '<style>.xiami_s_r{padding:0px 10px 10px 10px;margin-top:-10px;}
.xiami_s_r ul li a{ display:block; height:25px; line-height:25px; width:400px; overflow:hidden}
.xiami_s_r ul li a:hover{ background:#E6F2FF; text-decoration:none}</style>';
    }

    public function home_index_middle_publish_type()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.tao.click(this)' class='a52'><img class='icon_add_file_d' src='%s' />商品</a>",$this->htmlPath."/html/zw_img.gif");
		echo $html;
    }


    public function searchshop(){
            $url = $_POST[url];
            $info = $this->getshopinfo($url);

           
           
            if(!is_array($info[item])){
                $data[s] = '0';
                $data[error] ='获取信息失败';
                exit( json_encode($data)); 
            }else{
                foreach($info[item][item_imgs][item_img] as $key => $v){
                    $pics[] = $v[url];
                }
                
                session_start();
                $_SESSION[shoppics][pic] = $pics;
                $_SESSION[shoppics][title] = $info[item][title];
                
                $_SESSION[shoppics][p] = $info[item]['price'];
                
                $data[s] ='1';
                $data[t] = $info[item][title];
                $data[p] = $info[item]['price'];
                $data[url] = service('ShortUrl')->getShort($this->geturl($url));
                $_SESSION[shoppics][url] =  $data[url] ;
                $data[html] = '<strong>'.$data[t].'</strong><br><br>';
                
                foreach($pics as $key=>$value){
                    $data[html] = $data[html].'<img src="'.$value.'_120x120.jpg" width="80">';
                }
                exit( json_encode($data)); 
            }
    }

    public function weibo_type($param)
    {
        if($param[typeId] =='23'){
            $res = &$param['result'];
            session_start();
            $data = $_SESSION[shoppics];
            unset($_SESSION[shoppics]);

            if($data){
                $res['type'] = $param[typeId];
                $res['type_data'] =serialize($data);
            }else{
                $res['type'] ='';
            }
        }            

    }

    public function weibo_type_parse_tpl($param)
    {
        $type     = $param['typeId'];
        $typeData = $param['typeData'];
        $rand     = $param['rand'];
        $res = &$param['result'];
        $hasMore = true;
        
        if($type =='23'){
            unset($typeData[pic][4]);
            $this->assign('hasMore',$hasMore);
            $this->assign('data',$typeData[pic]);
            $this->assign('ot',$typeData);
            $this->assign('rand',$rand);
            $res = $this->fetch('tao');
            return $res;
        }else{
            
        }
    }
    
    
          public function geturl($nid){
            require_once('TaobaokeItemsConvertRequest.php');
            $addata = model('AddonData')->lget('tao');
            $tapi = $this->tapi();
            $req =new TaobaokeItemsConvertRequest;
            $req->setFields("click_url");
            $req->setNumIids($nid);
            $req->setPid($addata[type][pid]);
            $resp = $this->tapi()->execute($req);
            if(is_array($resp[taobaoke_items])){
                return $resp[taobaoke_items][taobaoke_item][0][click_url];
            }else{
                 return "http://item.taobao.com/item.htm?id=".$nid;
             }
            
        }
        public function tapi(){
            require_once('TopClient.class.php');
            require_once('RequestCheckUtil.class.php');
            $addata = model('AddonData')->lget('tao');
            $tapi = new TopClient();
            $tapi->appkey = $addata[type][openid];
            $tapi->secretKey = $addata[type][openkey];
            return $tapi;
        }
        
        public function getshopinfo($id){
            
            require_once('ItemGetRequest.php');
            
            $tapi = $this->tapi();
            $req =new ItemGetRequest;
            $req->setFields("title,item_img,price");
            $req->setNumIid($id);
            $resp = $this->tapi()->execute($req);
            return $resp;
        }
        
        
      public function set(){
        
        if($_POST){
            $data[type][openid] =$_POST[openid];
            $data[type][openkey] =$_POST[openkey];
            $data[type][pid] =$_POST[pid];
            model('AddonData')->lput('tao',$data,TRUE)?true:false;
            $this->success();
        }
        $this->assign('set',model('AddonData')->lget('tao'));
        $this->display("set");
        
    }
}
