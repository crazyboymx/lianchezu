<?php
require_once(SITE_PATH.'/apps/showfee/Lib/Model/ShowfeeBaseModel.class.php');

class ShowfeeFeeRecordModel extends ShowfeeBaseModel {
    public function getFeeRecord($showfeeId) {
        $map['showfeeId'] = intval($showfeeId);
        $result = $this->where($map)->findAll();
        foreach ($result as &$data) {
            $data = $this->appendContent($data);
        }
        return $result;
    }

    public function addFeeRecord($map) {
        $map['cTime'] = isset($map['cTime']) ? $map['cTime'] : time();
        if (empty($map['fee'])  || empty($map['feeTypeId']) || empty($map['showfeeId'])) {
            return -1;
        }
        return $this->add($map);
    }

    public function editFeeRecord($map) {
        if (empty($map['id']) || empty($map['fee']) || empty($map['explain']) || empty($map['feeType']) || empty($map['showfeeId'])) {
            return -1;
        }
        return $this->save($map);
    }

    public function deleteFeeRecord($map) {
        //先判断合法性
        if( empty( $map ) )
            throw new ThinkException( "不能是空条件删除" );
        return $this->where($map)->delete();
    }

    public function appendContent($data) {
        $data['feeTypeName'] = D('ShowfeeFeeType', 'showfee')->getFeeTypeName($data['feeTypeId']);
        return $data;
    }
}
