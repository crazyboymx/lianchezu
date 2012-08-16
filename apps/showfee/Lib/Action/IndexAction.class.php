<?php
class IndexAction extends Action {
    private $appName;
    private $showfee;

    public function _initialize() {
        //应用名称
        global $ts;
        $this->appName = $ts['app']['app_alias'];
        //设置活动的数据处理层
        $this->showfee = D('Showfee');
        //读取推荐列表
        $is_hot_list = $this->showfee->getHotList();
        $this->assign('is_hot_list',$is_hot_list);
        // 汽车品牌分类
        $cate = D('ShowfeeCarBrand')->getAllCarBrandName();
        $this->assign( 'category',$cate );
    }

    public function index() {
        $order = NULL;
        switch( $_GET['order'] ) {
        case 'new':    //最新排行
            $order = 'cTime DESC';
            $this->setTitle('最新' . $this->appName);
            break;
        case 'following':    //关注的人的
            $following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
            foreach($following as $v) {
                $in_arr[] = $v['fid'];
            }
            $map['uid'] = array('in',$in_arr);
            $this->setTitle('我关注的人的' . $this->appName);
            break;
        default:      //默认热门排行
            $order = 'commentCount DESC, cTime DESC';
            $this->setTitle('热门' . $this->appName);
        }

        //查询
        $title = t($_POST['title']);
        if ($_POST['title']) {
            $map['title'] = array( 'like',"%".t($_POST['title'])."%" );
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_GET['cid']) {
            $map['carBrandId']  = intval($_GET['cid']);
            $this->setTitle('分类浏览');
        }
        $result  = $this->showfee->getShowfeeList($map,$order,$this->mid);

        $this->assign($result);
        $this->display();
    }

    public function personal() {
        if ($this->uid == $this->mid)
            $name = '我';
        else
            $name = getUserName($this->uid);

        switch( $_GET['action'] ) {
        default:      //发起的
            $map['uid'] = $this->uid;
            $this->setTitle("{$name}的{$this->appName}");
        }
        $result = $this->showfee->getShowfeeList($map,'id DESC',$this->mid);
        $this->assign($result);
        $this->assign('name', $name);
        $this->display();
    }

    public function getCatTypesByBrand(){
        $data = D('ShowfeeCarType')->getCarTypesByBrand($_REQUEST["brandId"]);
        $this->ajaxReturn($data,'info',1);
    }
    public function addShowfee() {
        $this->_createLimit($this->mid);
        $this->setTitle('添加' . $this->appName);
        $this->assign('carBrandList', D('ShowfeeCarBrand')->getAllCarBrand());
        $this->assign ("feeTypeList",D('ShowfeeFeeType')->getAllFeeType());
        $this->display();
    }

    private function _createLimit($uid){
        $config = getConfig();

        if(!$config['canCreate']){
            $this->error('禁止发起'.$this->appName);
        }
        if($config['credit']){
            $userCredit = X('Credit')->getUserCredit($uid);
            if($userCredit[$config['credit_type']]['credit']<$config['credit']){
                $this->error($userCredit[$config['credit_type']]['alias'].'小于'.$config['credit'].'，不允许发起'.$this->appName);
            }
        }
        if( $timeLimit = $config['limittime'] ){
            $regTime = M('user')->getField('ctime',"uid={$uid}");
            $difference = (time()-$regTime)/3600;

            if($difference<$timeLimit){
                $this->error('账户创建时间小于'.$timeLimit.'小时，不允许发起'.$this->appName);
            }
        }
    }

    public function doAddShowfee() {
        $this->_createLimit($this->mid);
        $map['title']      = t($_POST['title']);
        $map['explain']    = h($_POST['explain']);
        $map['carTime']        = $_POST['carTime'];
        $map['uid']        = $this->mid;
        $map['carBrandId'] = intval(t($_POST['carBrandId']));
        $map['carTypeId'] = intval(t($_POST['carTypeId']));
        //http://bbs.phpchina.com/home.php?mod=space&uid=68442&do=blog&id=48792
        //json_decode 的第二个参数灰常有用哦
        $feeRecord = json_decode(t($_POST['feeRecord']),true);

        if(strlen(text($map['explain'])) < 4){
            $this->error('介绍不得小于4个字符');
        }

        if($addId = $this->showfee->addShowfee($map, $feeRecord)) {
            X('Credit')->setUserCredit($this->mid, 'add_showfee');
            $this->assign('jumpUrl',U('/Index/showfeeDetail',array('id'=>$addId, 'uid'=>$this->mid)));
            $this->success($this->appName.'添加成功');
        }else{
            $this->error($this->appName.'添加失败');
        }
    }

    public function showfeeDetail() {
        $id   = intval( $_GET['id'] );
        $uid  = intval($_GET['uid']);
        $test = array($id,$uid);
        //检测id和uid是否为0
        if( false == $this->checkUrl($test)) {
            $this->error("错误的访问页面，请检查链接");
        }

        $this->showfee->setMid( $this->mid );
        if($result = $this->showfee->getShowfeeContent($id,$uid)) {
            $this->assign('showfee', $result);
            $this->setTitle($result['title']);
            $this->display();
        }else {
            $this->error( '错误的访问页面，请检查链接' );
        }
    }

    public function editShowfee() {
        $id = intval( $_GET['id'] );
        $uid = $this->showfee->where('id=' . $id)->getField('uid');
        if( $uid != $this->mid ) {
            $this->error( '您没有权限编辑这个'.$this->appName ) ;
        }

        $this->showfee->setMid($this->mid);
        if($result = $this->showfee->getShowfeeContent($id, $uid)) {
            $this->assign($result);
            $this->assign('carBrandList', D('ShowfeeCarBrand')->getAllCarBrand());
            $this->assign('carTypeList',D('ShowfeeCarType')->getCarTypesByBrand($result['carBrandId']));
            $this->assign ("feeTypeList",D('ShowfeeFeeType')->getAllFeeType());
            $this->setTitle('编辑' . $this->appName);
            $this->display();
        }else {
            $this->error('错误的访问页面，请检查链接');
        }
    }

    public function doEditShowfee() {
        $id = intval($_POST['id']);
        $map['id']      =$id;
        $map['title']      = t($_POST['title']);
        $map['explain']    = h($_POST['explain']);
        $map['carTime']    = h($_POST['carTime']);
        $map['uid']        = $this->mid;
        $map['carBrandId'] = intval(t($_POST['carBrandId']));
        $map['carTypeId'] = intval(t($_POST['carTypeId']));
        $feeRecords = json_decode(t($_POST['feeRecord']),true);

        if(strlen(text($map['explain'])) < 4){
            $this->error('介绍不得小于4个字符');
        }

        if($addId = $this->showfee->editShowfee($id, $map, $feeRecords)) {
            $this->assign('jumpUrl',U('showfee/Index/showfeeDetail',array('id'=>$id, 'uid'=>$this->mid)));
            $this->success($this->appName.'修改成功');
        }else{
            $this->error($this->appName.'修改失败');
        }
    }

    public function deleteShowfee() {
        $id   = array('in', $_REQUEST['id']); //要删除的id.
        $uid = $this->mid;
        $result = $this->showfee->deleteShowfee($id, $uid);
        if( false != $result){
            echo 1;
        }else{
            echo 0;               //删除失败
        }
    }

    private function _paramDate($date) {
        $date_list = explode( ' ',$date );
        list( $year,$month,$day ) = explode( '-',$date_list[0] );
        list( $hour,$minute,$second ) = explode( ':',$date_list[1] );
        return mktime( $hour,$minute,$second,$month,$day,$year );
    }

    public function checkUrl(array $data) {
        $count1 = count($data);
        $count2 = count(array_filter($data));
        if($count2 < $count1) {
            return false;
        }else {
            return true;
        }
    }
}
