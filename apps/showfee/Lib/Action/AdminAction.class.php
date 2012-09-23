<?php
	import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {
    public $brand;
    public $carType;
    public $showfee;

    public function _initialize(){
        $this->brand = D('ShowfeeCarBrand');
        $this->carType = D('ShowfeeCarType');
        $this->showfee = D('Showfee');
        //管理权限判定
        parent::_initialize();
    }

    public function index (){
        $config   = model('Xdata')->lget('showfee');
        $this->assign($config);

        $credit_types = X('Credit')->getCreditType();
        $this->assign('credit_types',$credit_types); 

        $this->display();

    } 

    public function doChangeBase (){
        //变量过滤 todo:更细致的过滤
        foreach($_POST as $k=>$v){
            $config[$k] =   t($v);
        }
        //$config['limitsuffix'] = preg_replace("/bmp\|||\|bmp/",'',$config['photo_file_ext']);//过滤bmp
        if(model('Xdata')->lput('event',$config)){
            $this->assign('jumpUrl', U('event/Admin/index'));
            $this->success('设置成功！');
        }else{
            $this->error('设置失败！');
        }
    }

    /**
     * eventlist 
     * 获得所有人的eventlist
     * @access public
     * @return void
     */
    public function feetypelist (){
        $list  = D('ShowfeeFeeType')->getAllFeeType();
        $this->assign( $_POST );
        $this->assign( "list",$list );
        $this->display();
    }


    public function doEditFeeType(){
        $_POST['id']   = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $feetype= D('ShowfeeFeeType');
        if($_POST['id']==''){
            $result =$feetype->addFeeType($_POST);
        }else{
            $result = $feetype ->editFeeType($_POST); 
        }
        echo $result;
    }

    public function deleteFeeType(){
        $id['id']      = array( "in",$_POST['id']);
        $feetype = D( 'ShowfeeFeeType' );
        if( $result = $feetype->deleteFeeType( $id ) ){
            if ( !strpos($_POST['id'],",") ){
                echo 2;            //说明只是删除一个
            }else{
                echo 1;            //删除多个
            }
        }else{
            echo $result;
        }
    }


    public function brands() {
        $brands = $this->brand->getAllCarBrand();
        $this->assign("brands",$brands);
        $this->display();
    }

    public function addBrand() {
        $map['name'] = t($_REQUEST['name']);
        //得到上传的图片
        $config = getConfig_sf();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('showfee',$options);
        if ($addId = $this->brand->addCarBrand($map, $cover)) {
            $this->assign('jumpUrl', U('showfee/Admin/brands'));
            $this->success('添加成功');
        }
        else {
            $this->assign('jumpUrl', U('showfee/Admin/brands'));
            $this->error(L('添加失败'));
        }
    }

    public function editBrand() {
        $map['name'] = t($_REQUEST['name']);
        $map['id'] = intval(t($_REQUEST['id']));
        //得到上传的图片
        $config = getConfig_sf();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('showfee',$options);
        if ($this->brand->editCarBrand($map, $cover)) {
            $this->assign('jumpUrl', U('showfee/Admin/brands'));
            $this->success(L('编辑成功'));
        }
        else {
            $this->assign('jumpUrl', U('showfee/Admin/brands'));
            $this->error(L('编辑失败'));
        }
    }

    public function deleteBrand() {
        $id['id'] = array( "in",$_REQUEST['id']);
        if($result = $this->brand->deleteCarBrand($id)){
            if (!strpos($_REQUEST['id'],",")) {
                echo 2;            //说明只是删除一个
            }else {
                echo 1;            //删除多个
            }
        }else {
            echo $result;
        }
    }

    public function carTypes() {
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');
        $_POST['id']    && $map['id']   = intval($_POST['id']);
        $_POST['name']  && $map['name'] = array('like' , '%' . t($_POST['name']) . '%');
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder'] ) : "id DESC";
        $_POST['limit'] && $limit       = intval(t( $_POST['limit']));
        $ctypes = $this->carType->getList($map, $order, $limit);
        $this->assign($ctypes);
        $brands = $this->brand->getAllCarBrand();
        $this->assign("brands",$brands);
        $this->display();
    }

    public function addCarType() {
        $map['name'] = t($_REQUEST['name']);
        $map['brandId'] = intval(t($_REQUEST['brandId']));
        //得到上传的图片
        $config = getConfig_sf();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('showfee',$options);
        if ($addId = $this->carType->addCarType($map, $cover)) {
            $this->assign('jumpUrl', U('showfee/Admin/carTypes'));
            $this->success('添加成功');
        }
        else {
            $this->assign('jumpUrl', U('showfee/Admin/carTypes'));
            $this->error(L('添加失败'));
        }
    }

    public function editCarType() {
        $map['name'] = t($_REQUEST['name']);
        $map['brandId'] = intval(t($_REQUEST['brandId']));
        $map['id'] = intval(t($_REQUEST['id']));
        //得到上传的图片
        $config = getConfig_sf();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('showfee',$options);
        if ($this->carType->editCarType($map, $cover)) {
            $this->assign('jumpUrl', U('showfee/Admin/carTypes'));
            $this->success(L('编辑成功'));
        }
        else {
            $this->assign('jumpUrl', U('showfee/Admin/carTypes'));
            $this->error(L('编辑失败'));
        }
    }

    public function deleteCarType() {
        $id['id'] = array( "in",$_REQUEST['id']);
        if($result = $this->carType->deleteCarType($id)){
            if (!strpos($_REQUEST['id'],",")) {
                echo 2;            //说明只是删除一个
            }else {
                echo 1;            //删除多个
            }
        }else {
            echo $result;
        }
    }

    public function showfees() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if ( !empty($_POST) ) {
            $_SESSION['showfees_admin_search'] = serialize($_POST);
        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
            $_POST = unserialize($_SESSION['showfees_admin_search']);
        }else {
            unset($_SESSION['showfees_admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');
        $_POST['id']    && $map['id']   = intval($_POST['id']);
        $_POST['uid']   && $map['uid']  = intval($_POST['uid']);
        $_POST['title'] && $map['title']= array( 'like','%'.t( $_POST['title'] ).'%' );
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder'] ) : "cTime DESC";
        $_POST['limit'] && $limit       = intval(t( $_POST['limit']));

        $fees = $this->showfee->getList($map, $order, $limit);
        $this->assign($fees);
        $this->display();
    }

    public function deleteShowfee() {
        $id['id']  = array("in", $_REQUEST['id']);
        if($result = $this->showfee->deleteShowfee($id, $this->mid)) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            }else {
                echo 1;            //删除多个
            }
        }else {
            echo $result;
        }
    }

    //推荐操作
    public function doChangeIsHot(){
        $showfee['id'] = array('in', $_REQUEST['id']); //要推荐的id.
        $act  = $_REQUEST['type'];  //推荐动作
        $result  = $this->showfee->setIsHot($showfee,$act);
        if( false != $result){
            echo 1;     //推荐成功
        }else{
            echo -1;    //推荐失败
        }
    }
}
