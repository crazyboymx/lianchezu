<?php
class ShowfeeFeeRecordModel extends BaseModel {
    public function getFeeRecord($showfeeId) {
        $map['showfeeId'] = intval($showfeeId);
        return $this->where($map)->findAll();
    }

}
