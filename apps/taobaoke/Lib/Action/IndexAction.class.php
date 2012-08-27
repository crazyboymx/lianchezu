<?php
class IndexAction extends Action {
    private $appName;
    private $taobaoke;

    public function _initialize() {
        //应用名称
        global $ts;
        $this->appName = $ts['app']['app_alias'];
        //设置活动的数据处理层
        $this->taobaoke = D('Taobaoke');
    }

    public function index() {
        $acModel = D('TaobaokeAc');
        $acdisplay=$acModel->order('`display_order` ASC')->findAll();
        $this->assign('acdisplay', $acdisplay);

        $strType = h($_GET['type']);
        $ac_id=$_GET['ac_id'];
        $dalei=D('TaobaokeBc')->field('bc_id')->where("ac_id = $ac_id")->findAll();
        $map['ac_id']=intval( $ac_id );
        $nowtitle= $acModel->getField('title', $map);
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
            if ($ac_id){
                $map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
            }else{
                $map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
            }
            $order = 'comment DESC';
            $type_name = '热门回复';
            $this->setTitle($type_name.分享);
            break;
        case 'all':
            if ($ac_id){
                $map = " bc_id in ($dl_as) and isdel=0 ";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 ";
                $type_name = '全部分享';
            }
            $order = 'weibo_id DESC';
            $this->setTitle($type_name);
            break;
        case 'hot':
            if ($ac_id){
                $map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
                $type_name = '热门分享';
            }
            $order = 'transpond DESC';
            $this->setTitle($type_name);
            break;
        default:
            if ($ac_id){
                $map = " bc_id in ($dl_as) and isdel=0 ";
                $type_name = $nowtitle;
            }else{
                $map = " isdel=0 ";
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
        /*
        echo $catelog['data'][3]['type_data'];
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        var_dump( unserialize($catelog['data'][3]['type_data']));
        exit;*/
        //unserialize  貌似不行。。
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
