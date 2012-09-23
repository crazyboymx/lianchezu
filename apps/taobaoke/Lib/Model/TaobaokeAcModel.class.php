<?php
require_once(SITE_PATH.'/apps/taobaoke/Lib/Model/TaobaokeBaseModel.class.php');
class TaobaokeAcModel extends TaobaokeBaseModel{
    var $tableName = 'taobaoke_ac';

    public function getAllAc(){
        return $this->order("display_order ASC")->findAll();
    }

    public function getAllAcName(){
        //先从缓存里面获取
        $result = $this->field('id,name')->order("display_order ASC")->findAll();

        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $value['name'];
        }
        return $newresult;
    }

    public function addAc($map){
        if(empty($map['name'])){
            return -1;
        }
        return $this->add($map);
    }

    public function deleteAc( $map ){
        //先判断合法性
        if(empty( $map ))
            throw new ThinkException( "不能是空条件删除" );
        //如果这个车标下有内容或者有车型，就不允许删除
        $id   = D( 'Taobaoke' )->field( 'distinct(carBrand)' )->findAll();
        $tid  = D( 'ShowfeeCarType' )->field( 'distinct(brandId)' )->findAll();
        $temp = array();
        foreach ( $id as $value ){
            $temp[] = $value['carBrand'];
        }
        foreach ($tid as $value) {
            $temp[] = $value['brandId'];
        }

        if( strpos( $map['id'][1],',' ) ){
            $temp2 = explode( ',',$map['id'][1] );
            $map['id'] = array('in',array_diff( $temp2,$temp));
            if(count($map['id'][1])==count($temp2)){
                return $this->where( $map )->delete();
            }else{
                return 0;
            }
        }else{
            if( !in_array( $map['id'][1],$temp ) ){
                return $this->where( $map )->delete();
            }else{
                return 0;
            }
        }
        return false;

    }

    public function editAc($data) {
        $query = $this->save($data);
        return $query;
    }

    public function getAcName( $id ){
        $map['id'] = $id;
        $result = $this->where( $map )->field('name')->find();
        return $result['name'];
    }

    public function getAc($id) {
        $map['id'] = intval($id);
        return $this->where($map)->find();
    }

    private function appendContent($data) {
    }
}
