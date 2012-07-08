<?php
class UserAction extends Action {

    const INDEX_TYPE_WEIBO = 0;
    const INDEX_TYPE_GROUP = 1;
    const INDEX_TYPE_ALL   = 2;

    function _initialize() {
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        global $ts;

        //SamPeng 2011.12.15重构整个方法
        $this->assign('install_app',$ts['install_apps']);
        $this->assign ( $data );
    }

    protected function _empty()
    {
        $this->display('addons');
    }

    //个人首页
    function index() {
        Session::pause();
        global $ts;
        //SamPeng 2011.12.15重构整个方法
        $install_app = $ts['install_apps'];

        $type  = h ( $_GET ['weibo_type'] );
        $weibo_config = model ( 'Xdata' )->lget ( 'weibo' );

        //判断是动态还是微博,兼容1.6的代码

        //if (($show_feed = $weibo_config['openDynamic'] && intval($_COOKIE['feed']))) {
        //    $data = $this->__getDynamic($type); //显示动态
        //} else {
            $data = $this->__getWeiboList($install_app,$type); //显示微博列表
        //}

        //$data['show_feed'] = $show_feed;
        $data['type']      = $type;


       $this->__assignTabSwitch();


        $this->__setAnnouncement();

        if(!empty($_GET['weibo_type'])) {
            $this->assign('typeClass',"on");
            $this->assign('view','block');
        }else{
            $this->assign('typeClass','off');
            $this->assign('view','none');
        }



        $this->assign ( $data );
        $this->setTitle (L('my_index'));
        $this->display ();
    }
	/**
     *
     */
    private function __assignTabSwitch ()
    {
        //判断当前使用哪一个tab
        switch (intval($_GET['type'])) {
            case self::INDEX_TYPE_WEIBO:
                $this->assign('weibo_tab', 'on');
                break;
            case self::INDEX_TYPE_GROUP:
                $this->assign('group_tab', 'on');
                break;
            case self::INDEX_TYPE_ALL:
                $this->assign('all_tab', 'on');
                break;
            default:
                $this->assign('weibo_tab','on');
        }
    }








    //提到我的
    public function atme() {
        model ( 'UserCount' )->setZero ( $this->mid, 'atme' );
        $data ['list'] = D ( 'Operate', 'weibo' )->getAtme ( $this->mid );
        // 同步的设置
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        foreach ( $bind as $v ) {
            $data ['login_bind'] [$v ['type']] = $v ['is_sync'];
        }

        $this->assign ( $data );
        $this->setTitle ( L('at_me_weibo') );
        $this->display ( 'index' );
    }

    //我的收藏
    function collection() {
        $data ['list'] = D ( 'Operate', 'weibo' )->getCollection ( $this->mid );

        // 同步的设置
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        foreach ( $bind as $v ) {
            $data ['login_bind'] [$v ['type']] = $v ['is_sync'];
        }

        $this->assign ( $data );
        $this->setTitle (L('my_fav'));
        $this->display ( 'index' );
    }

    //评论列表
    function comments() {
        $data ['type'] = ($_GET ['type'] == 'send') ? 'send' : 'receive';
        $data ['from_app'] = ($_GET ['from_app'] == 'other') ? 'other' : 'weibo';

        // 优先展示微博，优先展示有未读from_app
        if (model ( 'UserCount' )->getUnreadCount ( $this->mid, 'comment' ) <= 0 && model ( 'GlobalComment' )->getUnreadCount ( $this->mid ) > 0)
            $data ['from_app'] = 'other';

        if ($data ['from_app'] == 'weibo') {
            $data ['type'] == 'receive' && model ( 'UserCount' )->setZero ( $this->mid, 'comment' );

            //$data['person'] = (in_array( $_GET['person'] , array('all','follow','other')) )?$_GET['person']:'all';
            $data ['person'] = 'all';
            $data ['list'] = D ( 'Comment', 'weibo' )->getCommentList ( $data ['type'], $data ['person'], $this->mid );
        } else {
            $dao = model ( 'GlobalComment' );
            $data ['type'] == 'receive' && $dao->setUnreadCountToZero ( $this->mid );

            $data ['person'] = 'all';
            $data ['list'] = $dao->getCommentList ( $data ['type'], $this->mid );

            /*
             * 缓存评论发表者, 被回复的用户,
             */
            $ids = getSubBeKeyArray ( $data ['list'] ['data'], 'appuid,uid,to_uid' );
            D ( 'User', 'home' )->setUserObjectCache ( array_unique ( array_merge ( $ids ['appuid'], $ids ['uid'], $ids ['to_uid'] ) ) );

            foreach ( $data ['list'] ['data'] as $k => $v )
                $data ['list'] ['data'] [$k] ['data'] = unserialize ( $v ['data'] );
        }

        $this->assign ( 'userCount', X ( 'Notify' )->getCount ( $this->mid ) );

        $this->assign ( $data );
        $this->setTitle ( $data ['type'] == 'receive' ? L('receive_comment') : L('send_comment') );
        $this->display ();
    }

    private function __getSearchKey() {
        $key = '';
        // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (isset ( $_REQUEST ['k'] ) && ! empty ( $_REQUEST ['k'] )) {
            if ($_GET ['k']) {
                $key = html_entity_decode ( urldecode ( $_GET ['k'] ), ENT_QUOTES );
            } elseif ($_POST ['k']) {
                $key = $_POST ['k'];
            }
            // 关键字不能超过200个字符
            if (mb_strlen ( $key, 'UTF8' ) > 200)
                $key = mb_substr ( $key, 0, 200, 'UTF8' );
            $_SESSION ['home_user_search_key'] = serialize ( $key );

        } else if (is_numeric ( $_GET [C ( 'VAR_PAGE' )] )) {
            $key = unserialize ( $_SESSION ['home_user_search_key'] );

        } else {
            //unset($_SESSION['home_user_search_key']);
        }
		$key = str_replace(array('%','\'','"','<','>'),'',$key);
        return trim ( $key );
    }

    private function __checkSearchPopedom() {
        if ($this->mid <= 0 && intval ( model ( 'Xdata' )->get ( 'siteopt:site_anonymous_search' ) ) <= 0)
            redirect ( U ( 'home' ) );
    }

    // 专题页
    public function topics()
    {
        $this->__checkSearchPopedom ();
        $data['search_key'] = $this->__getSearchKey ();
         Session::pause();
        // 专题信息
        if (false == $data['topics'] = D('Topics', 'weibo')->getTopics($data['search_key'], $_GET['id'], $_GET['domain'], 1)) {
            if (null == $data['search_key']) {
                $this->error(L('special_not_exist'));
            }
            $data['topics']['name'] = t($data['search_key']);
        }

        $data['search_key'] = $data['search_key'] ? $data['search_key'] : html_entity_decode($data['topics']['name'], ENT_QUOTES);
        $data['search_key_id'] = $data['topics']['topic_id'] ? $data['topics']['topic_id'] : D('Topic', 'weibo')->getTopicId($data['search_key']);

        $data['followState'] = D ('Follow', 'weibo')->getTopicState ($this->mid, $data['search_key']);
        // 其他关注该话题的人
        $data['other_following'] = D('Follow', 'weibo')->field('uid')
                                    ->where("uid<>{$this->mid} AND fid={$data['search_key_id']} AND type=1")
                                    ->limit(9)->findAll();
        // 微博列表
        $data['type'] = h ( $_GET ['type'] );
        $data['list'] = D ( 'Operate', 'weibo' )->doSearchWithTopic ( "#{$data['topics']['name']}#", $data ['type'] );
//      $data['list'] = D ( 'Operate', 'weibo' )->doSearch ( "#{$data['topics']['name']}#", $data ['type'] );
//      $data['list']['count'] = D ( 'Operate', 'weibo' )->where("content LIKE '%#{$data['topics']['name']}#%' AND isdel=0")->count();

        // 微博Tab
        $data['weibo_menu'] = array(
                                ''  => L('all'),
                                'original' => L('original'),
                              );
        Addons::hook('home_index_weibo_tab', array(&$data['weibo_menu']));

        $this->setTitle ( L('special').$data ['search_key']);
        $data['search_key'] = h(t($data['search_key']));

        $this->assign ( $data );
        $this->display();
    }

    // 查找话题
    public function search() {
        $this->__checkSearchPopedom ();
        $data ['search_key'] = $this->__getSearchKey ();
        Session::pause();
        $data ['followState'] = D ( 'Follow', 'weibo' )->getTopicState ( $this->mid, $data ['search_key'] );
        $data ['type'] = t ( $_REQUEST ['type'] );
        $data ['list'] = D ( 'Operate', 'weibo' )->doSearch ( $data ['search_key'], $data ['type'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key_id'] = D ( 'Topic', 'weibo' )->getTopicId ( $data ['search_key'] );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        // 微博Tab
        $data['weibo_menu'] = array(
                                        ''  => L('all'),
                                'original' => L('original'),
                              );
        Addons::hook('home_index_weibo_tab', array(&$data['weibo_menu']));
        $data['weibo_menu'] = array(''  => L('all'), 'location' => L('local'), 'follow' => L('attention')) + $data['weibo_menu'];
        Addons::hook('home_search_weibo_tab', array(&$data['weibo_menu']));

        $this->assign ( $data );
        $this->setTitle ( L('search_weibo').$data['search_key'] );
        $this->display ();
    }

    //查找用户
    public function searchuser() {
        $this->__checkSearchPopedom ();
        $data ['search_key'] = $this->__getSearchKey ();
        Session::pause();
        $data ['list'] = D ( 'Follow', 'weibo' )->doSearchUser ( $data ['search_key'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        $this->assign ( $data );
        $this->setTitle ( L('search_people').$data['search_key'] );
        $this->display ();
    }

    //查找我关注的
    public function searchTips()
    {
        $key = str_replace('_', '\_', h ( $_GET ['key'] ));
        $db_prefix  =  C('DB_PREFIX');
         Session::pause();
        //$list = M ( 'user' )->field('uname')->where ( "uname LIKE '%{$key}%'" )->order ( "LOCATE('{$key}', uname) ASC" )->limit ( 10 )->findAll();
        $list = M('')->field('u.*')->field('u.uname')
                     ->table("{$db_prefix}weibo_follow AS f LEFT JOIN {$db_prefix}user AS u ON f.uid={$this->mid} AND f.fid=u.uid")
                     ->where("u.uname LIKE '%{$key}%'")
                     ->order ( "LOCATE('{$key}', u.uname) ASC" )
                     ->limit ( 10 )->findAll();
        if ($list) {
            exit ( json_encode ( $list ) );
        } else {
            echo '';
        }
    }

    //查找Tag
    public function searchtag() {
        $this->__checkSearchPopedom ();
        $data ['search_key'] = $this->__getSearchKey ();
         Session::pause();
        $data ['list'] = D ( 'UserTag' )->doSearchTag ( $data ['search_key'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        $this->assign ( $data );
        $this->setTitle ( L('search_tag').$data ['search_key']);
        $this->display ();
    }

	//找人 -  2011-11-28 优化 解决推荐用户中还有已关注用户问题
    function findfriend() {
         Session::pause();
        $type_array = array ('followers', 'hot', 'understanding', 'newjoin' );
        $data ['type'] = in_array ( $_GET ['type'], $type_array ) ? $_GET ['type'] : 'newjoin';
        $user_model = D ( 'User', 'home' );

        $db_prefix = C ( 'DB_PREFIX' );
        switch ($data ['type']) {
            case 'followers' :
                $data ['list'] = M ("weibo_follow")->where("fid!=$this->mid AND fid not in ( select fid from ".C('DB_PREFIX')."weibo_follow where uid=$this->mid) ")
											->field('fid as uid,count(uid) as count')
											->group("fid")
											->order('`count` DESC')
											->limit(10)
											->findAll();
                //$data ['list'] = D ( 'Follow', 'weibo' )->getTopFollowerUser ();

                $uids = getSubByKey ( $data ['list'], 'uid' );

                $user_model = D ( 'User', 'home' );
                $user_count_model = model ( 'UserCount' );
                $user_model->setUserObjectCache ( $uids );
                $user_count_model->setUserFollowerCount ( $uids );
                foreach ( $data ['list'] as $key => $value ) {
                    $data ['list'] [$key] = $user_model->getUserByIdentifier ( $value ['uid'] );
                    $data ['list'] [$key] ['follower'] = $user_count_model->getUserFollowerCount ( $value ['uid'] );
                }
                break;

            case 'hot' :

				$data ['list'] = M ("weibo")->where("a.uid!=$this->mid AND a.uid not in ( select fid from ".C('DB_PREFIX')."weibo_follow as b where b.uid=$this->mid) ")
											->field('a.uid,count(a.weibo_id) as weibo_num')
											->table(C('DB_PREFIX').'weibo as a')
											->group("uid")
											->order('weibo_num DESC')
											->limit(10)
											->findAll();

				//$data ['list'] = M ("weibo")->query ( "SELECT uid,count(weibo_id) as weibo_num FROM {$db_prefix}weibo GROUP BY uid ORDER by weibo_num DESC LIMIT 10" );

				$uids = getSubByKey ( $data ['list'], 'uid' );

                $user_model = D ( 'User', 'home' );
                $user_count_model = model ( 'UserCount' );
                $user_model->setUserObjectCache ( $uids );
                $user_count_model->setUserFollowerCount ( $uids );
                foreach ( $data ['list'] as $key => $value ) {
                    $data ['list'] [$key] = $user_model->getUserByIdentifier ( $value ['uid'] );
                    $data ['list'] [$key] ['follower'] = $user_count_model->getUserFollowerCount ( $value ['uid'] );
                    $data ['list'] [$key] ['weibo_num'] = $value ['weibo_num'];
                }
                break;

            case 'understanding' :
                $data ['list'] = model ( 'Friend' )->getRelatedUser ( $this->mid, $max = 10 );
                $uids = getSubByKey ( $data ['list'], 'uid' );

                $user_model = D ( 'User', 'home' );
                $user_count_model = model ( 'UserCount' );
                $user_model->setUserObjectCache ( $uids );
                $user_count_model->setUserFollowerCount ( $uids );
                foreach ( $data ['list'] as $key => $value ) {
                    $data ['list'] [$key] = $user_model->getUserByIdentifier ( $value ['uid'] );
                    $data ['list'] [$key] ['follower'] = $user_count_model->getUserFollowerCount ( $value ['uid'] );
                }
                break;

            case 'newjoin' :
                $data ['list'] = M ("user")->where("a.is_active=1 AND a.is_init=1 AND a.uid!={$this->mid} AND a.uid not in (SELECT fid FROM ".C('DB_PREFIX')."weibo_follow as b WHERE b.uid={$this->mid}) ")
											->field('a.uid,a.uname,a.domain,a.location,a.ctime')
											->table(C('DB_PREFIX').'user as a')
											->order('a.uid DESC')
											->limit(10)
											->findAll();

                D ( 'User', 'home' )->setUserObjectCache ( $data ['list'] );
                $dao = model ( 'UserCount' );
                $dao->setUserFollowerCount ( getSubByKey ( $data ['list'], 'uid' ) );
                foreach ( $data ['list'] as $key => $value )
                    $data ['list'] [$key] ['follower'] = $dao->getUserFollowerCount ( $value ['uid'] );
                break;
        }

        // 粉丝榜
        $data ['topfollow'] = D ( 'Follow', 'weibo' )->getTopFollowerUser ();
        D ( 'User', 'home' )->setUserObjectCache ( getSubByKey ( $data ['topfollow'], 'uid' ) );

        $this->assign ( $data );
        $this->setTitle ( L('find_people') );
        $this->display ();
    }

    //表情
    function emotions() {
         Session::pause();
        exit ( json_encode ( model ( 'Expression' )->getAllExpression () ) );
    }

    //获取统计数据
    function countNew() {
         Session::pause();
        exit ( json_encode ( X ( 'Notify' )->getCount ( $this->mid ) ) );
    }

    // 删除动态
    public function doDeleteMini() {
        echo X ( 'Feed' )->deleteOneFeed ( $this->mid, intval ( $_POST ['id'] ) ) ? '1' : '0';
    }

    public function closeAnnouncement() {
        $announcement_ctime = model ( 'Xdata' )->getField ( 'mtime', '`list`="announcement"' );
        $announcement_ctime = strtotime ( $announcement_ctime );
        cookie ( "announcement_closed_{$this->mid}", $announcement_ctime );
    }

    private function __getLoginBind()
    {
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        $result = array();
        foreach ( $bind as $v ) {
            $result[$v ['type']] = $v ['is_sync'];
        }
        return $result;
    }

    private function __getDynamic($type)
    {
        $data['list'] = X ( 'Feed' )->get ( $this->mid );
        return $data;
    }

    private function __setAnnouncement ()
    {
        // 公告
        if (($announcement = F ( '_home_user_action_announcement' )) === false) {
            $announcement = model ( 'Xdata' )->where ( '`list`="announcement"' )->findAll ();
            foreach ( $announcement as $v ) {
                $announcement [$v ['key']] = unserialize ( $v ['value'] );
            }
            $announcement ['ctime'] = strtotime ( $announcement ['0'] ['mtime'] );

            F ( '_home_user_action_announcement', $announcement );
        }

        if (cookie ( "announcement_closed_{$this->mid}" ) != $announcement ['ctime'])
            $this->assign ( 'announcement', $announcement );
    }



    private function __getWeiboList($install_app,$type)
    {
        global $ts;
        // 关注的分组列表
        $myFollowData = $this->__paramUserFollowGroup($type);
        $data  = $myFollowData;
        $data['indexType']   = $indexType              = intval($_GET['type']);
        $temp = $ts['my_group_list'];
        $group_list = array();
        foreach($temp as $value){
            if($value['openWeibo']){
                $group_list[] = $value;
            }
        }
        $data['group_list']  = $group_list;

        $data['gid']         = intval($_GET['gid']);

        $data['hasGroupWeibo']  = $this->__hasGroupWeibo($group_list);
        if($indexType == self::INDEX_TYPE_WEIBO){
            $data['weibo_menu'] = array(
                    ''  => L('all'),
                    'original' => L('original'),
            );
            Addons::hook('home_index_weibo_tab', array(&$data['weibo_menu']));
        }

        switch ($indexType) {
            case self::INDEX_TYPE_WEIBO:
                $data ['list'] = D ( 'Operate', 'weibo' )->getHomeList ( $this->mid, $type, '', '', $data ['follow_gid'] );
                break;
            case self::INDEX_TYPE_GROUP:
                $data ['list'] = D('WeiboOperate','group')->getHomeList($this->mid, $data['gid'], '', '');
                break;
            case self::INDEX_TYPE_ALL:
                $order = 'weibo_id DESC';
                $data['list'] = D('Operate','weibo')->doSearchTopic("",$order,$this->mid);
                break;
            default:
                $data ['list'] = D ( 'Operate', 'weibo' )->getHomeList ( $this->mid, $type, '', '', $data ['follow_gid'] );
        }

		if($data['list']['data']){
			// 最新一条微博的Id (countNew时使用)
			$_last_weibo = reset($data ['list'] ['data']);
			$data ['lastId'] = $_last_weibo['weibo_id'];
			$_since_weibo = end($data ['list'] ['data']);
			$data['sinceId'] = $_since_weibo['weibo_id'];
		}

        return $data;
    }


    private function __paramUserFollowGroup($type){
        $data ['follow_gid'] = is_numeric ( $_GET ['follow_gid'] ) ? $_GET ['follow_gid'] : 'all';
        $group_list = D ( 'FollowGroup', 'weibo' )->getGroupList ( $this->uid );
        //兼容旧风格包的逻辑生成两个数组
        $split_result = $this->__splitFollowGroup($group_list, $data['follow_gid']);
        $data['group_list_1'] = $split_result['group_list_1'];
        $data['group_list_2'] = $split_result['group_list_2'];

        $firstGroup =  array('follow_group_id'=>'all','title'=>L('following_my'));
        if($data['follow_gid'] == 'all'){
            $data['group_now']    = $firstGroup;
        }else{
            $data['group_now']    = $split_result['now'];
        }

        array_unshift($group_list,$firstGroup);


        $data['follow_group_list']   = $group_list;
        return $data;
    }



    private function __splitFollowGroup($group_list,$gid)
    {
        $res = array();
        if (! empty ( $group_list )) { // 关注分组
            $group_count = count ( $group_list );
            for($i = 0; $i < $group_count; $i ++) {
                if ($group_list [$i] ['follow_group_id'] != $gid) {
                    $group_list [$i] ['title'] = $this->__shortForGroupTitle($group_list[$i]['title']);
                }else{
                    $res['now'] = $group_list[$i];
                }
                if ($i < 2) {
                    $res ['group_list_1'] [] = $group_list [$i];
                } else {
                    if ($group_list [$i] ['follow_group_id'] == $gid) {
                        $res ['group_list_1'] [2] = $group_list [$i];
                        continue;
                    }
                    $res ['group_list_2'] [] = $group_list [$i];
                }
            }
            if (empty ( $res ['group_list_1'] [2] ) && ! empty ( $res ['group_list_2'] [0] )) {
                $res ['group_list_1'] [2] = $res ['group_list_2'] [0];
                unset ( $res ['group_list_2'] [0] );
            }
        }
        return $res;
    }


    private function __hasGroupWeibo($group_list)
    {
        $hasGroupList = $group_list && !empty($group_list);
        return $hasGroupList;
    }

    private function __shortForGroupTitle($title)
    {
        return (strlen ( $title ) + mb_strlen ( $title, 'UTF8' )) / 2 > 8 ? getShort ( $title, 3 ) . '...' : $title;
    }
}
?>