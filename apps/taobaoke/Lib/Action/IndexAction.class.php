<?php
class IndexAction extends Action {
    private $appName;
    private $taobaoke;

    public function _initialize() {
        //应用名称
        global $ts;
        $this->appName = $ts['app']['app_alias'];
        if (!is_numeric($_GET['uid']) && is_string($_GET['uid'])) {
            $domainuser = D('User')->getUserByIdentifier(h($_GET['uid']), 'domain');
            if ($domainuser) {
                $this->uid = $domainuser['uid'];
                $this->assign('uid',$this->uid);
            }else {
                $this->error('用户不存在');
            }
        }
        //设置活动的数据处理层
        $this->taobaoke = D('Taobaoke');
        $data['spaceCount']['miniblog']   = $this->taobaoke->where(array('uid' => $this->uid))->count();
        $this->assign($data);
    }

    public function index() {
        $acModel = D('TaobaokeAc');
        $acdisplay=$acModel->order('`display_order` ASC')->findAll();
        $this->assign('acdisplay', $acdisplay);

        $strType = h($_GET['type']);
        $uid=$_GET['uid'];
        $ac_id=$_GET['ac_id'];
        $bc_id=$_GET['bc_id'];
        if ($ac_id) {
            $map['ac_id'] = intval($ac_id);
        }
        if ($bc_id) {
            $map['bc_id'] = intval($bc_id);
        }
        $dalei=D('TaobaokeBc')->field('bc_id')->where($map)->findAll();
        $nowtitle= $acModel->getField('title', $map);
        if ($nowtitle == null) {
            $nowtitle = D('TaobaokeBc')->getField('title', $map);
        }
        foreach ($dalei as $dl)
        {
            $dl_arr=$dl['bc_id'].',';
            $dl_a.=$dl_arr;
        }
        $dl_as = substr($dl_a,0,strlen($dl_a)-1);
        $time_range = model('Xdata')->get('square:comment');
        if(!is_numeric($time_range) || $time_range<1)$time_range = 30;
        $now        = time();
        $yesterday  = mktime(0,0,0,date("m"),date("d"),date("Y"))-$time_range*24*3600;
        $data['order'] = $_GET['order'] ? $_GET['order'] : 'all';
        switch ($data['order']) {
        case 'comment':
            if ($dl_as){
                $map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now}";
            }else{
                $map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now}";
            }
            $order = 'comment DESC';
            $type_name = '热门回复';
            $this->setTitle($type_name.分享);
            break;
        case 'all':
            if ($dl_as){
                $map = " bc_id in ($dl_as) and isdel=0";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 and";
                $type_name = '全部分享';
            }
            $order = 'weibo_id DESC';
            $this->setTitle($type_name);
            break;
        case 'hot':
            if ($dl_as){
                $map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now}";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now}";
                $type_name = '热门分享';
            }
            $order = 'transpond DESC';
            $this->setTitle($type_name);
            break;
        default:
            if ($dl_as){
                $map = " bc_id in ($dl_as) and isdel=0 and uid=$this->uid";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 and uid=$this->uid";
                $type_name = '全部分享';
            }
            $order = 'weibo_id DESC';
            $this->setTitle($type_name);
            break;
        }
        if ($strType){
            $map.=" AND type=".$strType;
        }
        $row = $row ? $row : 20;
        $listCount=M('taobaoke')->where($map)->count();
        $catelog=M('taobaoke')->where($map)->order($order)->findPage($row, $listCount);
        $this->assign('list' , $catelog) ;
        $this->display();
    }

    //转发
    public function transpond(){
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        foreach ( $bind as $v ) {
            $data ['login_bind'] [$v ['type']] = $v ['is_sync'];
        }

        $map['uid']=$this->mid;
        $bcdisplay = D('TaobaokeBc')->where($map)->order('bc_id DESC')->findAll();
        $this->assign('bcdisplay',$bcdisplay);
        $nobc= D('TaobaokeBc')->where($map)->getField('bc_id');

        $this->assign('nobc',$nobc);

        $pWeibo = D('taobaoke');
        if($_POST){
            $post['bc_id']         = $_POST['bc_id'];

            $post['content']         = $_POST['content'];
            $post['transpond_id']    = intval( $_POST['transpond_id'] );
            $post['reply_weibo_id']  = $_POST['reply_weibo_id'];
            if( $id = $pWeibo->transpond($this->mid,$post) ){
                $data = $pWeibo->getOneLocation($id);
                X('Credit')->setUserCredit($this->mid,'forward_weibo')
                    ->setUserCredit($data['expend']['uid'],'forwarded_weibo');
                $this->assign('data',$data);
                $this->display('publish');
            }
        }else{
            $intId = intval( $_GET['id'] );
            $info = $pWeibo->where( 'weibo_id='.$intId)->find();
            if( $info['transpond_id'] ){
                $info['transponInfo'] = D('Operate')->field('weibo_id,uid,content,type_data')->where('weibo_id='.$info['transpond_id'])->find();
            }else{
                $info['old_content'] = $info['content'];
            }
            $info['upcontent'] = intval($_GET['upcontent']);
            $this->assign( 'data' , $info );
            $this->display();
        }
    }

    public function zhuanji() {
        $strType = h($_GET['type']);
        $data['user'] = D('User')->getUserByIdentifier($this->uid);
        $data['type'] = $strType;
        $bcdata = M('taobaoke_bc')->where('uid=' . $this->uid)->order('bc_id DESC')->findPage(20);
        $this->assign($bcdata);

        //用户数据
        /*$userInfo = D('User')->getUserByIdentifier($this->uid);
        if ($this->mid > 0 && $this->uid != $this->mid) {
            // 记录访问时间
            M('user_visited')->setField('ctime', time(), "uid={$this->mid} AND fid={$this->uid}")
                || M('user_visited')->add(array('uid'=>$this->mid, 'fid'=>$this->uid, 'ctime'=>time()));
            // 空间被访问积分
            X('Credit')->setUserCredit($this->uid,'space_visited');
        }*/
        $this->assign('userinfo', $userInfo);
        //名言
        //$userdata = D('user_profile')->where('uid=' . $this->uid)->findAll() ;
        //$this->assign('userdata' , $userdata) ;
        //$follow['type'] = follower;
        //$follow2['type'] = following;
        // 关注的人列表
        //$data['follower'] = D('Follow','weibo')->getList($this->uid,$follow['type'],0,$data['gid']);
        //$data['following'] = D('Follow','weibo')->getList($this->uid,$follow2['type'],0,$data['gid']);

        $this->assign($data);
        $this->setTitle($data['user']['uname'] . '的购物空间');
        $this->display();
    }

    public function share() {
        $strType = h($_GET['type']);
        $order = NULL;
        switch( $_GET['order'] ) {
        case 'hot':    //热门排行
            $order = 'comment DESC';
            break;
        default:      //默认最新排行
            $order = 'weibo_id DESC';
        }
        $row = $row?$row:20;
        if($strType=='fav'){
            $db_prefix  = C('DB_PREFIX');
            $map = "weibo_id IN ( SELECT favid FROM {$db_prefix}taobaoke_fav WHERE uid = '".$this->uid."' )";
            $listCount=M('taobaoke')->where($map)->count();
            $catelog=M('taobaoke')->where($map)->order($order)->findPage($row, $listCount);
        }else if($strType==''){
            $listCount=M('taobaoke')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."' ")->count();
            $catelog=M('taobaoke')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."'")->order($order)->findPage($row, $listCount);
        }else{
            $listCount=M('taobaoke')->where("type = '".$strType."' and  isdel=0 and uid = '".$this->uid."' ")->count();   
            $catelog=M('taobaoke')->where("type = '".$strType."' and  isdel=0 and uid = '".$this->uid."'")->order($order)->findPage($row, $listCount);
        }
        $this->assign('list' , $catelog);

        /*$menu[''] = '购物';
        $data['bc_id']=intval( $_GET['bc_id'] );
        $data['type'] = in_array($_GET['type'], array_flip($menu)) ? $_GET['type'] : '';
        if (in_array($data['type'], array('', 'original', '1', '3','4','5'))) {
            $data['list'] = D('TaobaokeOperate')->getSpaceList($this->uid, $data['type'],$data['bc_id']);
        }*/

        //$data['user'] = D('User')->getUserByIdentifier($this->uid);
        //用户数据
        //$userInfo = D('User')->getUserByIdentifier($this->uid);
        //$this->assign('userinfo',$userInfo);
        //名言
        //$userdata = D('user_profile')->where('uid=' . $this->uid)->findAll() ;
        //$this->assign('userdata' , $userdata) ;

        //$follow['type'] = follower;
        //$follow2['type'] = following;

        // 关注的人列表
        //$data2['follower'] = D('Follow','weibo')->getList($this->uid,$follow['type'],0,$data['gid']);
        //$data2['following'] = D('Follow','weibo')->getList($this->uid,$follow2['type'],0,$data['gid']);

        //$this->assign($data2);
        $this->setTitle($data['user']['uname'] . '的全部分享');
        $this->display();
    }

    public function detail() {
        $intId = intval( $_GET['id'] );
        //标签
        $tagdata=M('goodstag')->where("goodsid=".$intId . "")->findAll();
        $this->assign('tagdata',$tagdata);

        //喜欢
        $fav=M('taobaoke_fav')->where("favid=".$intId. "")->findAll();
        $this->assign('fav' , $fav) ;

        $bcdata = M('taobaoke')->where("weibo_id=" . $intId . "")->findAll() ;
        $nowbc_id2= $bcdata[0]['bc_id'];
        $this->assign('nowbc_id2',$nowbc_id2);
        if ($bcdata[0]['transpond_id']==0) {
            $map['bc_id']= $bcdata[0]['bc_id'] ;
            $map['uid']= $bcdata[0]['uid'];
            //商品数据
            $data['type_data'] = unserialize($bcdata[0]['type_data']) ;
            $data['type'] = $bcdata[0]['type'] ;
            $data['from'] = $bcdata[0]['from'] ;
            $data['from_data'] = $bcdata[0]['from_data'] ;
        }else{
            $transpond_id = $bcdata[0]['transpond_id'] ;
            $bcdata2 = M('taobaoke')->where("weibo_id=" . $transpond_id . "")->findAll() ;
            $map['bc_id']= $bcdata2[0]['bc_id'] ;
            $map['uid']= $bcdata2[0]['uid'];
            //商品数据
            $data['type_data'] = unserialize($bcdata2[0]['type_data']);
            $data['type'] = $bcdata2[0]['type'] ;
            $data['from'] = $bcdata2[0]['from'] ;
            $data['from_data'] = $bcdata2[0]['from_data'] ;
        }

        if ( $data ) {
            $this->assign('data' , $data) ;
        }

        $nowtitle= M('taobaoke_bc')->getField('title',$map);
        $nowbc_id= M('taobaoke_bc')->getField('bc_id',$map);
        $this->assign('nowtitle',$nowtitle);
        $this->assign('nowbc_id',$nowbc_id);

        $bc_id=M('taobaoke')->field('bc_id')->where('weibo_id='.$intId)->findall();
        foreach ($bc_id as $bcid)
        {
            $bcid_arr=$bcid['bc_id'];
        }
        $bcbc = $bcid_arr;
        $bcdata = M ( 'taobaoke_bc' )->where ( 'bc_id = '.$bcbc )->limit(1)->order('bc_id DESC')->findall();
        $this->assign ('bcdata', $bcdata);
        $key = get_domain($data['from_data']);
        $bcdata_from = M('taobaoke')->where ("from_data LIKE '%{$key}%' and isdel=0")->limit(3)->order('weibo_id DESC')->findall();
        $this->assign ('bcdata_from', $bcdata_from);

        $data['mini'] = D('TaobaokeOperate')->getOneLocation($intId);
        if(!$data['mini'])
            $this->error('提交错误参数');
        $data['comment']   =  D('TaobaokeComment')->getComment( $intId );
        $this->assign( $data );
        $this->uid = $data['mini']['uid'];

        $user_info = D('User')->getUserByIdentifier($this->uid);
        if ($user_info) {
            $this->assign('userinfo', $user_info);
        }
        $this->setTitle(getShort($data['mini']['content'], 30).'_'.getUserName($this->uid) .'');
        $this->assign(model('Xdata')->lget('platform'));
        $this->display();
    }
}
