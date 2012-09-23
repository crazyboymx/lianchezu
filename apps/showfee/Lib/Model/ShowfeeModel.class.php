<?php

require_once(SITE_PATH.'/apps/showfee/Lib/Model/ShowfeeBaseModel.class.php');

class ShowfeeModel extends ShowfeeBaseModel {
    var $mid;

    public function getShowfeeList($map='', $order='id DESC', $mid) {
        $this->mid = $mid;
        $result  = $this->where($map)->order($order)->findPage(getConfig_sf('limitpage'));
        //追加必须的信息
        if( !empty( $result['data'] )){
            foreach( $result['data'] as &$value ){
                $value = $this->appendContent($value);
            }
        }
        return $result;
    }

    public function appendContent( $data ){
        $carTypeM = self::factoryModel('CarType');
        $feeRecordM = self::factoryModel('FeeRecord');
        $cartype = $carTypeM->getCarType($data['carTypeId']);
        $data['carBrandName'] = $cartype['brandName'];
        $data['carBrandCover'] = $cartype['brandCover'];
        $data['carTypeName'] = isset($cartype['name']) ? $cartype['name'] : '';
        $data['carTypeCover'] = $cartype['cover'];
        $feerecord = $feeRecordM->getFeeRecord($data['id']);
        $data['totalFee'] = 0;
        foreach ($feerecord as $r) {
            $data['totalFee'] += $r['fee'];
        }
        $data['cover'] = $data['carTypeCover'];
        return $data;
    }

    public function addShowfee($map, $feeRecord) {
        $map['cTime'] = isset($map['cTime']) ? $map['cTime'] : time();

        $this->startTrans();
        $addId = $this->add($map);
        if ($addId) {
            foreach ($feeRecord as &$fr) {
                $fr['showfeeId'] = $addId;
                D('ShowfeeFeeRecord', 'showfee')->addFeeRecord($fr);
            }
            //发布到微薄
            $_SESSION['new_event']=1;
            $this->commit();
            return $addId;
        }else{
            $this->rollback();
            return false;
        }
    }

    public function getShowfeeContent($showfeeId, $uid) {
        $map['id'] = $showfeeId;
        $result = $this->where($map)->find();

        //检查是否发起者
        if(empty($result)) {
            return false;
        }

        $result = $this->appendContent($result);
        $result['feeRecord'] = D('ShowfeeFeeRecord', 'showfee')->getFeeRecord($showfeeId);
        return $result;
    }

    public function editShowfee($showfeeId, $map, $feeRecord) {
        $this->startTrans();
        $addId = $this->where('id ='.$showfeeId)->save($map);
        if ($addId) {
            //删除旧费用记录
            $frm = D('ShowfeeFeeRecord', 'showfee');
            $frm->where('showfeeId=' . $showfeeId)->delete();
            foreach ($feeRecord as &$fr) {
                $fr['showfeeId'] = $map["id"];
                $frm->addFeeRecord($fr);
            }
            //$frm->addAll($feeRecord);
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }

    public static function factoryModel( $name ){
        return D("Showfee".ucfirst( $name ), 'showfee');
    }

    /**
     * 供后台管理获取列表的方法
     */
    public function getList($map, $order, $limit) {
        $result = $this->where($map)->order($order)->findPage($limit);
        //将属性追加
        foreach( $result['data'] as &$value ){
            $value = $this->appendContent( $value );
        }
        return $result;
    }

    public function deleteShowfee($showfeeId, $uid) {
        $uids = $this->field('uid')->where($showfeeId)->findAll();
        if (service('Passport')->isLoggedAdmin() == false) {
            foreach ($uids as $u) {
                if ($u != $uid) {
                    return false;
                }
            }
        }
        if (empty($showfeeId)) {
            return false;
        }

        //取出费用记录ID
        $feeRecordM = D('ShowfeeFeeRecord', 'showfee');
        $temp = $feeRecordM->field('id')->where(array('showfeeId' => $showfeeId['id']))->findAll();
        $feeRecordIds = array();
        foreach ($temp as $fid) {
            $feeRecordIds[] = $fid['id'];
        }
        $feeRecordIds = implode(',', $feeRecordIds);
        $feeRecord_map['id'] = array('in', $feeRecordIds);
        //扣除积分
        foreach ($uids as $uid) {
            X('Credit')->setUserCredit($uid, 'delete_showfee');
        }
        //删除记录
        if( $this->where($showfeeId)->delete()){
            //删除费用记录
            $feeRecordM->where($feeRecord_map)->delete();
            return true;
        }
        return false;
    }

    public function setIsHot($map, $act) {
        if(empty($map)){
            throw new ThinkException( "不允许空条件操作数据库" );
        }

        $data = array();
        $result = -1;
        switch($act) {
        case "recommend":   //推荐
            $data['isHot'] = 1;
            $result = $this->where($map)->save($data);
            break;
        case "cancel":   //取消推荐
            $data['isHot'] = 0;
            $result = $this->where($map)->save($data);
            break;
        }
        return $result;
    }

    public function getHotList() {
        $this->mid = $mid;
        $result  = $this->where(array('isHot'=>1))->order('cTime DESC')->findAll();
        //追加必须的信息
        foreach( $result as &$value ){
            $value = $this->appendContent($value);
        }
        return $result;
    }
}
