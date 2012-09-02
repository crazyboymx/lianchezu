<?php
/**
 * 个人空间
 */
class SpaceAction extends Action
{
    function _initialize() {
        if (!is_numeric($_GET['uid']) && is_string($_GET['uid'])) {
            $domainuser = D('User')->getUserByIdentifier(h($_GET['uid']), 'domain');
            if ($domainuser) {
                $this->uid = $domainuser['uid'];
                $this->assign('uid',$this->uid);
            }else {
                $this->error('用户不存在');
            }
        }

        if ('detail' != ACTION_NAME) {
            $user_info = D('User')->getUserByIdentifier($this->uid);
            if ($user_info) {
                $userinfo = array(
                    '微博地址' => U('home/Space/index', array('uid' => $user_info['domain'] ? $user_info['domain'] : $this->uid)),
                    '性别'    => getSex($user_info['sex']),
                    '所在地'  => $user_info['location'],
                );	   	   		

                $this->assign('userinfo', $userinfo);
            } else {
                $this->error('用户不存在');
            }

            $this->__getSpaceCount( $this->uid );
        }
    }

    private function __getSpaceCount($uid) {
        $followInfo = getUserFollow($uid);
        $data['followstate'] = D('Follow','taobaoke')->getState($this->mid, $uid, 0);
        $data['isBlackList'] = isBlackList($this->mid,$uid);
        $data['privacy']     = D('UserPrivacy','home')->getPrivacy($this->mid,$uid);
        $data['spaceCount']['miniblog']   = model('UserCount')->getUserWeiboCount($uid);
        $data['spaceCount']['following']  = $followInfo['following'];
        $data['spaceCount']['follower']   = $followInfo['follower'];
        $data['spaceCount']['message']   = 0;
        $data['hotTopic'] = D('Topic','taobaoke')->getHot();
        $data['usertags'] = D('UserTag', 'home')->getUserTagList( $this->uid );
        $data['verified'] = M('user_verified')->field('verified,info')->where("uid={$this->uid}")->find();
        $this->assign( $data );
    }

    // 用户空间首页
    public function index()
    {
        $strType = h($_GET['type']);
        $data['user'] = D('User')->getUserByIdentifier($this->uid);
        $data['type'] = $strType;
        $bcdata = M( 'taobaoke_weibo_bc' )->where ( 'uid=' . $this->uid )->order('bc_id DESC')->findPage(20);
        $this->assign ($bcdata);

        //用户数据
        $userInfo = D('User')->getUserByIdentifier($this->uid);
        if ($this->mid > 0 && $this->uid != $this->mid) {
            // 记录访问时间
            M('user_visited')->setField('ctime', time(), "uid={$this->mid} AND fid={$this->uid}")
                || M('user_visited')->add(array('uid'=>$this->mid, 'fid'=>$this->uid, 'ctime'=>time()));
            // 空间被访问积分
            X('Credit')->setUserCredit($this->uid,'space_visited');
        }

        $this->assign('userinfo',$userInfo);

        //名言
        $userdata = D('user_profile')->where('uid=' . $this->uid)->findAll() ;
        $this->assign('userdata' , $userdata) ;
        $follow['type'] = follower;
        $follow2['type'] = following;

        // 关注的人列表
        $data['follower'] = D('Follow','taobaoke')->getList($this->uid,$follow['type'],0,$data['gid']);
        $data['following'] = D('Follow','taobaoke')->getList($this->uid,$follow2['type'],0,$data['gid']);

        $this->assign($data);
        $this->setTitle($data['user']['uname'] . '的空间');
        $this->display();
    }

    function board()
    {
        $intbc_id = intval( $_GET['bc_id'] );
        $bcdata = M( 'taobaoke_weibo_bc' )->where ("uid = '".$this->uid."' AND bc_id<> '".$intbc_id."'")->order('bc_id DESC')->findall();
        $this->assign ('bcdata',$bcdata);
        $map['bc_id']=$intbc_id;
        $map['uid']=$this->uid;

        $nowtitle= M('taobaoke_weibo_bc')->getField('title',$map);

        $this->assign('nowtitle',$nowtitle);
        //增加分类选择，针对快速建图格
        $ac_id= M('taobaoke_weibo_bc')->getField('ac_id',$map);
        $this->assign('ac_id',$ac_id);
        $acdisplay = M('taobaoke_weibo_ac')->order('`display_order` ASC')->findAll();
        $this->assign('acdisplay',$acdisplay);

        $fengcount= M('taobaoke_weibo_bc')->getField('fengcount',$map);
        $this->assign('fengcount',$fengcount);
        //喜欢
        $guanzhu=M('fengmian')->where("fengid =".$intbc_id. "")->findAll();
        $this->assign('guanzhu' , $guanzhu) ;

        $row = $row?$row:20;
        $listCount=M('taobaoke_weibo')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."' AND bc_id = '".$intbc_id."'")->count();
        $catelog=M('taobaoke_weibo')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."' AND bc_id = '".$intbc_id."'")->order('weibo_id DESC')->findPage($row, $listCount);
        $this->assign('list' , $catelog) ;

        $menu[''] = '微博';
        $data2['bc_id']=intval( $_GET['bc_id'] );
        $data2['type'] = in_array($_GET['type'], array_flip($menu)) ? $_GET['type'] : '';
        if (in_array($data2['type'], array('', 'original', '1', '3','4','5'))) {
            $data['list'] = D('Operate','weibo')->getSpaceList($this->uid, $data2['type'],$data2['bc_id']);
        }

        $data2['user'] = D('User')->getUserByIdentifier($this->uid);
        $this->assign($data2);
        $this->setTitle($nowtitle . ' - '.$data2['user']['uname']);
        $this->display();
    }

    function share()
    {
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
            $map = "weibo_id IN ( SELECT favid FROM {$db_prefix}fav WHERE uid = '".$this->uid."' )";
            $listCount=M('weibo')->where($map)->count();
            $catelog=M('weibo')->where($map)->order($order)->findPage($row, $listCount);


        }else if($strType==''){
            $listCount=M('taobaoke_weibo')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."' ")->count();
            $catelog=M('taobaoke_weibo')->where("type in(1,3,4,5) and  isdel=0 and uid = '".$this->uid."'")->order($order)->findPage($row, $listCount);


        }else{ 
            $listCount=M('taobaoke_weibo')->where("type = '".$strType."' and  isdel=0 and uid = '".$this->uid."' ")->count();   
            $catelog=M('taobaoke_weibo')->where("type = '".$strType."' and  isdel=0 and uid = '".$this->uid."'")->order($order)->findPage($row, $listCount);

        }
        $this->assign('list' , $catelog) ;
        $menu[''] = '微博';
        $data['bc_id']=intval( $_GET['bc_id'] );
        $data['type'] = in_array($_GET['type'], array_flip($menu)) ? $_GET['type'] : '';
        if (in_array($data['type'], array('', 'original', '1', '3','4','5'))) {
            $data['list'] = D('Operate','weibo')->getSpaceList($this->uid, $data['type'],$data['bc_id']);
        }

        $data['user'] = D('User')->getUserByIdentifier($this->uid);

        //用户数据
        $userInfo = D('User')->getUserByIdentifier($this->uid);
        $this->assign('userinfo',$userInfo);

        //名言
        $userdata = D('user_profile')->where('uid=' . $this->uid)->findAll() ;
        $this->assign('userdata' , $userdata) ;

        $follow['type'] = follower;
        $follow2['type'] = following;

        // 关注的人列表
        $data2['follower'] = D('Follow','weibo')->getList($this->uid,$follow['type'],0,$data['gid']);
        $data2['following'] = D('Follow','weibo')->getList($this->uid,$follow2['type'],0,$data['gid']);

        $this->assign($data2);
        $this->setTitle($data['user']['uname'] . '的全部分享');
        $this->display();
    }

    //个人资料
    public function profile()
    {
        $pUserProfile = D('UserProfile');
        $pUserProfile->uid = $this->uid;
        $data['userInfo']  = $pUserProfile->getUserInfo();
        $data['user'] = D('User')->getUserByIdentifier($this->uid);

        $this->assign( $data );
        $this->setTitle(getUserName($this->uid) . '的详细资料');
        $this->display();
    }

    // 查看微博详细
    function detail(){

        $intId = intval( $_GET['id'] );
        //标签
        $tagdata=M('goodstag')->where("goodsid=".$intId . "")->findAll();
        $this->assign('tagdata',$tagdata);

        //喜欢
        $fav=M('fav')->where("favid=".$intId. "")->findAll();
        $this->assign('fav' , $fav) ;


        //--------------仿知美二次开发------------------------
        $bcdata = M('weibo')->where("weibo_id=" . $intId . "")->findAll() ;
        $nowbc_id2= $bcdata[0]['bc_id']; 
        $this->assign('nowbc_id2',$nowbc_id2); 
        if ($bcdata[0]['transpond_id']==0){
            $map['bc_id']= $bcdata[0]['bc_id'] ;
            $map['uid']= $bcdata[0]['uid'];
            //商品数据
            $data['type_data'] = unserialize($bcdata[0]['type_data']) ;
            $data['type'] = $bcdata[0]['type'] ;
            $data['from'] = $bcdata[0]['from'] ;
            $data['from_data'] = $bcdata[0]['from_data'] ;
        }else{
            $transpond_id = $bcdata[0]['transpond_id'] ;
            $bcdata2 = M('weibo')->where("weibo_id=" . $transpond_id . "")->findAll() ;
            $map['bc_id']= $bcdata2[0]['bc_id'] ;
            $map['uid']= $bcdata2[0]['uid'];
            //商品数据
            $data['type_data'] = unserialize($bcdata2[0]['type_data']);
            $data['type'] = $bcdata2[0]['type'] ;
            $data['from'] = $bcdata2[0]['from'] ;
            $data['from_data'] = $bcdata2[0]['from_data'] ;
        }

        if ( $data )
        {
            $this->assign('data' , $data) ;
        }


        $nowtitle= M('weibo_bc')->getField('title',$map);
        $nowbc_id= M('weibo_bc')->getField('bc_id',$map);   
        $this->assign('nowtitle',$nowtitle); 
        $this->assign('nowbc_id',$nowbc_id); 
        $bc_id=M( 'taobaoke_weibo' )->field('bc_id')->where('weibo_id='.$intId)->findall();

        foreach ($bc_id as $bcid)
        {
            $bcid_arr=$bcid['bc_id'];
        }
        $bcbc = $bcid_arr;
        $bcdata = M( 'taobaoke_weibo_bc' )->where ( 'bc_id = '.$bcbc )->limit(1)->order('bc_id DESC')->findall();
        $this->assign ('bcdata',$bcdata);
        $key = get_domain($data['from_data']);
        $bcdata_from = M( 'weibo' )->where ("from_data LIKE '%{$key}%' and isdel=0")->limit(3)->order('weibo_id DESC')->findall();
        $this->assign ('bcdata_from',$bcdata_from);

        $data['mini']      =  D('Operate','weibo')->getOneLocation( $intId );
        if(!$data['mini']) $this->error('提交错误参数');
        $data['comment']   =  D('Comment','weibo')->getComment( $intId );
        $data['privacy'] = D('UserPrivacy','home')->getPrivacy($this->mid,$data['mini']['uid']);
        $this->assign( $data );
        $this->uid = $data['mini']['uid'];

        $user_info = D('User')->getUserByIdentifier($this->uid);
        if ($user_info) {
            $this->assign('userinfo',$user_info);
        }
        $this->__getSpaceCount( $this->uid );
        $this->setTitle(getShort($data['mini']['content'],30). '_'.getUserName($this->uid) .'');
        //$this->setTitle(getUserName($this->uid) . '的微博');
        $this->assign( model('Xdata')->lget('platform') );
        $this->display();
    }

    //关注
    function follow(){
        $data['type'] = ($_GET['type']=='follower')?'follower':'following';
        if($data['type'] == 'following'){
            //关注分组列表
            $data['gid']  = is_numeric($_GET['gid'])?$_GET['gid']:'all';
            $group_list = D('FollowGroup','weibo')->getGroupList($this->uid);
            //调整分组列表
            if(!empty($group_list)){
                $group_count = count($group_list);
                for($i=0;$i<$group_count;$i++){
                    if($group_list[$i]['follow_group_id'] != $data['gid']){
                        $group_list[$i]['title'] = (strlen($group_list[$i]['title'])+mb_strlen($group_list[$i]['title'],'UTF8'))/2>8?getShort($group_list[$i]['title'],3).'...':$group_list[$i]['title'];
                    }
                    if($i<2){
                        $data['group_list_1'][] = $group_list[$i];
                    }else{
                        if($group_list[$i]['follow_group_id'] == $data['gid']){
                            $data['group_list_1'][2]  = $group_list[$i];
                            continue;
                        }
                        $data['group_list_2'][] = $group_list[$i];
                    }
                }
                if(empty($data['group_list_1'][2]) && !empty($data['group_list_2'][0])){
                    $data['group_list_1'][2] = $data['group_list_2'][0];
                    unset($data['group_list_2'][0]);
                }
            }
        }
        // 关注的人列表
        $data['list'] = D('Follow','weibo')->getList($this->uid,$data['type'],0,$data['gid']);

        $this->assign($data);
        $this->setTitle(getUserName($this->uid) . '的' . ($data['type'] == 'follower' ? '粉丝' : '关注'));
        $this->display();
    }
}
