<?php
class UserAction extends Action {

    function _initialize() {
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $this->assign ( $data );
    }

    //悬浮名片
    function usercard() {
        $data['user_id']=$_POST['user_id'];

        $user_info = D('User')->getUserByIdentifier($data['user_id']);
        if ($user_info) {
            $this->assign('userinfo',$user_info);
        }
        $data['followstate'] = D('Follow','weibo')->getState($this->mid, $data['user_id'], 0);
        $this->assign($data);
        $this->display();
    }

    //个人首页
    function index() {
        $strType = h ( $_GET ['type'] );
        $weibo_config = model ( 'Xdata' )->lget( 'taobaoke' );
        if ($weibo_config ['openDynamic']) // 是否开启动态
            $data ['show_feed'] = isset ( $_COOKIE ['feed'] ) ? intval ( $_COOKIE ['feed'] ) : 0;

        if ($data ['show_feed']) {
            $data ['list'] = X ( 'Feed' )->get ( $this->mid );
        } else {
            // 关注的分组列表
            $data ['gid'] = is_numeric ( $_GET ['gid'] ) ? $_GET ['gid'] : 'all';
            $group_list = D ( 'FollowGroup', 'taobaoke' )->getGroupList ( $this->uid );
            if (! empty ( $group_list )) { // 关注分组
                $group_count = count ( $group_list );
                for($i = 0; $i < $group_count; $i ++) {
                    if ($group_list [$i] ['follow_group_id'] != $data ['gid']) {
                        $group_list [$i] ['title'] = (strlen ( $group_list [$i] ['title'] ) + mb_strlen ( $group_list [$i] ['title'], 'UTF8' )) / 2 > 8 ? getShort ( $group_list [$i] ['title'], 3 ) . '...' : $group_list [$i] ['title'];
                    }
                    if ($i < 2) {
                        $data ['group_list_1'] [] = $group_list [$i];
                    } else {
                        if ($group_list [$i] ['follow_group_id'] == $data ['gid']) {
                            $data ['group_list_1'] [2] = $group_list [$i];
                            continue;
                        }
                        $data ['group_list_2'] [] = $group_list [$i];
                    }
                }
                if (empty ( $data ['group_list_1'] [2] ) && ! empty ( $data ['group_list_2'] [0] )) {
                    $data ['group_list_1'] [2] = $data ['group_list_2'] [0];
                    unset ( $data ['group_list_2'] [0] );
                }
            }

            $data['weibo_menu'] = array(
                ''  => '全部',
                'original' => '原创',
                '1' => '图片',
                '5' => '宝贝',
                '3' => '视频',
                // '4' => '音乐',
            );

            $data ['list'] = D ( 'Operate', 'taobaoke' )->getHomeList ( $this->mid, $strType, '', '', $data ['gid'] );

            // 最新一条微博的Id (countNew时使用)
            if (is_numeric ( $data ['gid'] )) {
                $lastWeibo = D ( 'Operate', 'taobaoke' )->getHomeList ( $this->mid, $strType, '', 1, '' );
                $data['lastId']  = $lastWeibo ['data'] [0] ['weibo_id'];
            } else {
                $data ['lastId'] = $data ['list'] ['data'] [0] ['weibo_id'];
            }
            $_since_weibo = end($data ['list'] ['data']);
            $data['sinceId'] = $_since_weibo['weibo_id'];
        }

        $bind = M( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        foreach ( $bind as $v ) {
            $data ['login_bind'] [$v ['type']] = $v ['is_sync'];
        }
        $data ['type'] = $strType;

        $this->assign ( $data );

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

        $this->setTitle ( '我的首页' );
        $this->display ();
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
        $this->setTitle ( '@我的微博' );
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
        $this->setTitle ( '我的收藏' );
        $this->display ( 'index' );
    }

    //仿知美二次开发  我的推荐
    function recommended() {
        $data ['list'] = D ( 'Operate', 'weibo' )->getrecommended ( $this->mid );

        // 同步的设置
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        foreach ( $bind as $v ) {
            $data ['login_bind'] [$v ['type']] = $v ['is_sync'];
        }

        $this->assign ( $data );
        $this->setTitle ( '我的推荐' );
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
        $this->setTitle ( $data ['type'] == 'receive' ? '收到的评论' : '发出的评论' );
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
            // 关键字不能超过30个字符
            if (mb_strlen ( $key, 'UTF8' ) > 30)
                $key = mb_substr ( $key, 0, 30, 'UTF8' );
            $_SESSION ['home_user_search_key'] = serialize ( $key );

        } else if (is_numeric ( $_GET [C ( 'VAR_PAGE' )] )) {
            $key = unserialize ( $_SESSION ['home_user_search_key'] );

        } else {
            //unset($_SESSION['home_user_search_key']);
        }

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
        // 专题信息
        if (false == $data['topics'] = D('Topics', 'weibo')->getTopics($data['search_key'], $_GET['id'], $_GET['domain'], 1)) {
            if (null == $data['search_key']) {
                $this->error('活动专题不存在');
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
        $data['list'] = D ( 'Operate', 'weibo' )->doSearch ( "#{$data['topics']['name']}#", $data ['type'] );
        $data['list']['count'] = D ( 'Operate', 'weibo' )->where("content LIKE '%#{$data['topics']['name']}#%' AND isdel=0")->count();

        $this->setTitle ( $data ['search_key'] . ' - 活动专题' );
        $data['search_key'] = h(t($data['search_key']));
        $this->assign ( $data );
        $this->display();
    }

    // 查找话题
    public function search() {
        $this->__checkSearchPopedom ();
        $data ['search_key'] = $this->__getSearchKey ();
        $data ['followState'] = D ( 'Follow', 'weibo' )->getTopicState ( $this->mid, $data ['search_key'] );
        $data ['type'] = t ( $_REQUEST ['type'] );
        $data ['list'] = D ( 'Operate', 'weibo' )->doSearch ( $data ['search_key'], $data ['type'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key_id'] = D ( 'Topic', 'weibo' )->getTopicId ( $data ['search_key'] );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        // 微博Tab
        $data['weibo_menu'] = array(
            ''  => '全部',
            'original' => '原创',
            'image'    => '图片',
            'goods'    => '宝贝',
            'video'    => '视频',
            //'music'    => '音乐',
        );

        $data['weibo_menu'] = array(''  => '全部', 'location' => '同城', 'follow' => '关注') + $data['weibo_menu'];


        $this->assign ( $data );
        $this->setTitle ( '搜分享' );
        $this->display ();
    }

    //查找用户
    public function searchuser() {
        $this->__checkSearchPopedom ();
        $data ['search_key'] = $this->__getSearchKey ();
        $data ['list'] = D ( 'Follow', 'weibo' )->doSearchUser ( $data ['search_key'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        $this->assign ( $data );
        $this->setTitle ( '搜人' );
        $this->display ();
    }

    //查找我关注的
    public function searchTips()
    {
        $key = str_replace('_', '\_', h ( $_GET ['key'] ));
        $db_prefix  =  C('DB_PREFIX');
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
        $data ['list'] = D ( 'UserTag' )->doSearchTag ( $data ['search_key'] );
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        $data ['search_key'] = h ( t ( $data ['search_key'] ) );
        $this->assign ( $data );
        $this->setTitle ( '搜标签' );
        $this->display ();
    }

    function findfriend() {
        $type_array = array ('followers', 'hot', 'understanding', 'newjoin' );
        $data ['type'] = in_array ( $_GET ['type'], $type_array ) ? $_GET ['type'] : 'newjoin';
        $user_model = D ( 'User', 'home' );

        $db_prefix = C ( 'DB_PREFIX' );
        switch ($data ['type']) {
        case 'followers' :
            $data ['list'] = D ( 'Follow', 'weibo' )->getTopFollowerUser ();
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
            $data ['list'] = M ()->query ( "SELECT uid,count(weibo_id) as weibo_num FROM {$db_prefix}weibo GROUP BY uid ORDER by weibo_num DESC LIMIT 10" );
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
            $data ['list'] = M ( "user" )->where ( "is_active=1 AND is_init=1 AND (uid<{$this->mid} OR uid>{$this->mid})" )->order ( 'uid DESC' )->field ( '`uid`,`uname`,`domain`,`location`,`ctime`' )->limit ( 10 )->findAll ();
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
        $this->setTitle ( '找人' );
        $this->display ();
    }

    //表情
    function emotions() {
        exit ( json_encode ( model ( 'Expression' )->getAllExpression () ) );
    }

    //获取统计数据
    function countNew() {
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
}
