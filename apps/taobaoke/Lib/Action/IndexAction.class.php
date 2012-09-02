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
            //仿知美二次开发
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
}
