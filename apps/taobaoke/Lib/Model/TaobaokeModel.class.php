<?php
class TaobaokeModel extends BaseModel{
    var $tableName = 'taobaoke';

    /**
     * 
     +----------------------------------------------------------
     * Description 微博发布
     +----------------------------------------------------------
     * @author Nonant nonant@thinksns.com
     +----------------------------------------------------------
     * @param $uid 发布者用户ID
     * @param $data 微博主要数据
     * @param $from 从哪发布的
     * @param $type 微博类型
     * @param $type_data   微博类型传来的数据
     +----------------------------------------------------------
     * @return return_type
     +----------------------------------------------------------
     * Create at  2010-9-17 下午05:02:06
     +----------------------------------------------------------
     */
    function publish($uid,$data,$from=0,$type=0,$type_data, $sync , $goods_url=""  ,$from_data=""){

        $data['bc_id'] =intval( $data['bc_id'] );
        $data['content'] =t( $data['content'] );
        if($id = $this->doSaveWeibo($uid, $data, $from , $type ,$type_data,$sync, $goods_url , $from_data)){
            $this->notifyToAtme($uid, $id, $data['content'] );
            return $id;
        }else{
            return false;
        }
    }

    //发布微博
    function doSaveWeibo($uid,$data,$from=0,$type=0,$type_data,$sync, $goods_url="" ,$from_data=""){
        if (!function_exists('getContentUrl'))
            require_once SITE_PATH . '/apps/weibo/Common/common.php';

        $save['uid'] = $uid;
        if($data['tag']){
            $save['tag'] = $data['tag'];
        }
        if($data['fenlei_input']){
            $save['fenlei_input'] = $data['fenlei_input'];
        }
        if($data['price']){
            $save['price'] = $data['price'];
        }
        $save['transpond_id'] = intval( $data['transpond_id'] );
        $save['bc_id']      = intval( $data['bc_id'] );
        $save['from']       = intval( $from );  //0网站 1手机网页版 2 android 3 iphone
        // 微博内容处理
        $save['content']    = preg_replace('/^\s+|\s+$/i', '', html_entity_decode($data['content'], ENT_QUOTES));
        $save['content']    = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$save['content']);	// 滤掉话题两端的空白
        $save['content']    = preg_replace_callback('/((?:https?|mailto):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)/u',getContentUrl, $save['content']);
        $save['from_data']  = $from_data;
        $save['goods_url']  = $goods_url ;
        $save['content']    = t(getShort($save['content'], 140));

        if($type){
            $save = array_merge( $save , (array)$this->checkWeiboType($type, $type_data) );
        }else{
            if($data['type']) $save['type'] = intval( $data['type'] );
        }
        $save['ctime']      = time();

        if( $id = $this->add( $save ) ){
            if( $save['transpond_id']){
                $this->setInc('transpond','weibo_id='.$save['transpond_id']);
            }

            /*$opt = M('login')->where("uid=".$uid." AND type='sina'")->field('oauth_token,oauth_token_secret,is_sync')->find();
            if($opt['is_sync']){
                include_once( SITE_PATH.'/addons/plugins/login/sina.class.php' );
                $sina = new sina();
                if($type==1){
                    $sina->upload($data['content'],SITE_URL.'/data/uploads/'.$type_data,$opt);
                }elseif($type==5){
                    $sina->upload($data['content'],$opt);
                }elseif($type==0){
                    $sina->update($data['content'],$opt);
                }
            }
            D('Topic','weibo')->addTopic( html_entity_decode($save['content'],ENT_QUOTES) );*/
            return $id;
        }else{
            return false;
        }
    }

    function publish2($uid,$data,$from=0,$type=0,$type_data,$sync, $from_data){
        $data['content'] =t( $data['content'] );
        if($id = $this->doSaveWeibo2($uid, $data, $from , $type ,$type_data, $sync, $from_data)){
            $this->notifyToAtme($uid, $id, $data['content'] );
            return $id;
        }else{
            return false;
        }
    }

    //发布微博
    function doSaveWeibo2($uid,$data,$from=0,$type=0,$type_data,$sync, $from_data){
        /*if(!$data['content']){
            return false;
        }*/

        if (!function_exists('getContentUrl'))
            require_once SITE_PATH . '/apps/weibo/Common/common.php';

        $save['uid']			= $uid;
        $save['transpond_id']	= intval( $data['transpond_id'] );
        $save['from']			= intval( $from );  //0网站 1手机网页版 2 android 3 iphone
        // 微博内容处理
        $save['content'] 		= html_entity_decode($data['content'], ENT_QUOTES);
        $save['content'] 		= preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$save['content']);	// 滤掉话题两端的空白
        $save['content']		= preg_replace_callback('/((?:https?|mailto):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)/u',getContentUrl, $save['content']);
        $save['from_data']		= $from_data;
        $save['content'] = t(getShort($save['content'], 140));

        if($type){
            $save = array_merge( $save , (array)$this->checkWeiboType($type, $type_data) );
        }else{
            if($data['type']) $save['type'] = intval( $data['type'] );
        }

        $save['ctime']      = time();

        if( $id = $this->add( $save ) ){
            if( $save['transpond_id']){
                $this->setInc('transpond','weibo_id='.$save['transpond_id']);
            }

            //if(in_array('sina',$sync)){
            $opt = M('login')->where("uid=".$uid." AND type='sina'")->field('oauth_token,oauth_token_secret,is_sync')->find();
            if($opt['is_sync']){
                include_once( SITE_PATH.'/addons/plugins/login/sina.class.php' );
                $sina = new sina();
                if($type==1){
                    $sina->upload($data['content'],SITE_URL.'/data/uploads/'.$type_data,$opt);
                }elseif($type==0){
                    $sina->update($data['content'],$opt);
                }
            }
            //}
            //话题处理
            D('Topic','weibo')->addTopic( html_entity_decode($save['content'],ENT_QUOTES) );
            return $id;
        }else{
            return false;
        }
    }

    //转发操作
    function transpond($uid,$data,$api=false){
        $post['bc_id']       = intval( $data['bc_id'] );
        $post['content']       = t( $data['content'] );
        $post['transpond_id']  = intval( $data['transpond_id'] );

        $transponInfo = $this->field('weibo_id,uid,content,type')->where('weibo_id='.$post['transpond_id'].' AND isdel=0')->find();
        $post['type'] = $transponInfo['type'];
        if( $data['reply_weibo_id'] ){ //对相应微博ID作出评论
            foreach ( $data['reply_weibo_id'] as $value ){
                if($value == 0) continue;
                $weiboinfo = $this->field('uid')->where('weibo_id='.$value.' AND isdel=0')->find();
                $comment['uid']       = $uid;
                $comment['reply_uid'] = $weiboinfo['uid'];
                $comment['weibo_id']  = $value;
                $comment['content']   = $post['content'];
                $comment['ctime']     = time();
                //D('Comment','taobaoke')->addcomment( $comment );
                //Model('UserCount')->addCount($weiboinfo['uid'],'comment');
            }
        }

        $id = $this->doSaveWeibo( $uid , $post , intval($data['from']) );
        if($id){
            $this->notifyToAtme($uid,$id, $post['content'], $transponInfo['uid']);
            return $id;
        }else{
            return false;
        }
    }

    // 给提到我的发通知
    function notifyToAtme($uid,$id,$content,$transpond_uid,$addCount=true){
        $notify['weibo_id'] = $id;
        $notify['content'] = $content;
        $arrUids= array();
        if( $transpond_uid ){
            array_push($arrUids, $transpond_uid);
        }
        $arrUids = array_merge($arrUids, getUids($content) );
        if( $arrUids ){
            $arrUids = array_unique( $arrUids ); //去重
            if($addCount){
                foreach ($arrUids as $v){
                    if(M('user_blacklist')->where("uid=$v AND fid=$uid")->count()==0){
                        $atUids[] = $v;
                    }
                }
                Model('UserCount')->addCount($atUids,'atme');
            }
            D('Atme','weibo')->addAtme($arrUids,$id);
        }
    }    

    private function checkWeiboType($type,$type_data){
        if( $type_data && $type !=0 ){
            $pluginInfo = D('Plugin', 'weibo')->getPluginInfoById($type);
            $do_type = 'publish';
            include SITE_PATH.'/apps/weibo/Lib/Plugin/'.$pluginInfo['plugin_path'].'/control.php';
            if (!empty($typedata)) {
                $save['type'] = $type;
                $save['type_data']  = serialize( $typedata );
            }
        }else{
            $save['type']   = 0;
        }
        return $save;
    }

    function getOne($id,$value,$api=false){
        if($api){
            return $this->getOneApi($id,$value);
        }else{
            return $this->getOneLocation($id, $value);
        }
    }

    //返回一个站内使用的解析微博
    public function getOneLocation($id, $value, $show_transpond = true)
    {
        if (!$value)
            if (($value = object_cache_get("taobaoke_{$id}")) === false)
                $value = $this->where('weibo_id='.$id.' AND isdel=0')->find();

        if (!$value)
            return false;

        $result['id']   = $value['weibo_id'];
        //增加喜欢
        $result['favcount'] = $value['favcount'] ;
        //增加推荐
        $result['jiancount'] = $value['jiancount'] ;
        //设为封面
        $result['fengcount'] = $value['fengcount'] ;
        $result['tag'] = unserialize($value['tag']);
        $result['fenlei_input'] = $value['fenlei_input'];
        $result['price']          = $value['price'];
        $result['uid']          = $value['uid'];
        $result['content']      = $value['content'];
        $result['ctime']        = $value['ctime'];
        $result['comment']      = $value['comment'];
        $result['from']         = $value['from'];
        $result['transpond_id'] = $value['transpond_id'];
        $result['transpond']    = $value['transpond'];
        $result['is_favorited'] = isset($value['is_favorited']) ? intval($value['is_favorited']) : isfavorited($value['weibo_id'], $value['uid']);
        if ($show_transpond && $result['transpond_id'])
            $result['expend']   = $this->getOne($result['transpond_id']);
        else
            $result['expend']   = $this->__parseTemplate( $value );

        $result['from_data'] = unserialize($value['from_data']);

        return $result;
    }

    //返回一个Api使用的微博信息
    public function getOneApi($id, $value, $uid = 0)
    {
        if (!$value && is_numeric($id))
            if (($value = object_cache_get("weibo_{$id}")) === false)
                $value = $this->where('weibo_id="'.$id.'" AND isdel=0')->find();

        if (!$value)
            return false;

        $value['uname'] = getUserName($value['uid']);
        $value['face']  = getUserFace($value['uid']);
        if ($value['type'] == 1 && $value['transpond_id'] == 0) {
            $value['type_data'] 				  = unserialize($value['type_data']);
            $value['type_data']['picurl'] 		  = SITE_URL.'/data/uploads/'.$value['type_data']['picurl'];
            $value['type_data']['thumbmiddleurl'] = SITE_URL.'/data/uploads/'.$value['type_data']['thumbmiddleurl'];
            $value['type_data']['thumburl'] 	  = SITE_URL.'/data/uploads/'.$value['type_data']['thumburl'];
        }

        $value['transpond_data'] = ($value['transpond_id'] > 0) ? $this->getOneApi($value['transpond_id']) : '';
        $value['timestamp'] 	 = $value['ctime'];
        $value['ctime'] 		 = date('Y-m-d H:i',$value['ctime']);
        $value['from_data'] 	 = unserialize($value['from_data']);
        $value['content'] 		 = keyWordFilter($value['content']);
        $value['favorited']      = intval($value['favorited']);
        return $value;
    }

    private function __parseTemplate( $value ){
        static $rand;
        if ($rand) {
            $rand++;
        }else {
            $rand = time().$value['transpond_id'];
        }

        $typedata = unserialize( $value['type_data'] );
        $type     = $value['type'];
        if ($type==3) {
            $typedata['flashimg'] = ($typedata['flashimg']) ? $typedata['flashimg'] : __THEME__.'/images/nocontent.png';
        }
        $template = $this->templateForType($type);
        if(!$template) return '';
        //$template = preg_replace('/{(.*?)}/eis',"\$this->parseLiteral('\\1')",$template);
        $template = preg_replace('/{data\.(.*?)}/eis',"\$typedata['\\1']",$template);
        $template = preg_replace('/{rand}/eis',$rand,$template);
        $template = preg_replace('/{(.*?)}/eis',"\$value['\\1']",$template);
        return $template;
    }

    //解析类型模板
    private	function templateForType($type) {
        $info = D('Plugin', 'weibo')->getPluginInfoById($type);
        if (!$info)
            return false;
        return require SITE_PATH.'/apps/weibo/Lib/Plugin/'.$info['plugin_path'].'/template.php';
    }

    /**
     * 缓存微博列表
     * 
     * 缓存的key的格式为: weibo_微博ID.
     * 
     * @param array $weibo_list 微博ID列表, 或者微博详情列表. 如果为微博ID列表时, 本方法会首先获取微博详情列表, 然后缓存.
     */
    public function setWeiboObjectCache($weibo_list)
    {
        if (!is_array($weibo_list))
            return false;

        if (!is_array($weibo_list[0]) && !is_numeric($weibo_list[0]))
            return false;

        if (is_numeric($weibo_list[0])) { // 给定的是weibo_id的列表. 查询weibo详情
            $map['weibo_id'] = array('in', $weibo_list);
            $map['isdel']    = 0;
            $weibo_list      = $this->where($map)->findAll();
        }

        foreach ($weibo_list as $v)
            object_cache_set("taobaoke_weibo_{$v['weibo_id']}", $v);

        return $weibo_list;
    }

    protected function _doWeiboAndUserCache($weibo_list)
    {
        if (!is_array($weibo_list) || !is_array($weibo_list[0]))
            return false;

        /*
         * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
         */
        $ids = getSubBeKeyArray($weibo_list, 'weibo_id,transpond_id,uid');
        $transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
        // 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
        $ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
        D('User', 'home')->setUserObjectCache($ids['uid']);

        return true;
    }
}
