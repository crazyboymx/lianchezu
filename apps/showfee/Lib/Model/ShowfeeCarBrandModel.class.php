<?php
class ShowfeeCarBrandModel extends BaseModel{
    public function getAllCarBrand(){
        //先从缓存里面获取
        $result = $this->field('id,name,coverId')->findAll();

        //重组数据集结构并追加数据
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $this->appendContent($value);
        }
        return $newresult;
    }
    public function getAllCarBrandName(){
        //先从缓存里面获取
        $result = $this->field('id,name')->findAll();

        //重组数据集结构并追加数据
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $value['name'];
        }
        return $newresult;
    }

    public function addCarBrand($map, $cover){
        if(empty($map['name']) || $cover == null){
            return -1;
        }
        $map['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        return $this->add($map);
    }

    public function deleteCarBrand( $map ){
        //先判断合法性
        if( empty( $map ) )
            throw new ThinkException( "不能是空条件删除" );
        //如果这个车标下有内容或者有车型，就不允许删除
        $id   = D( 'Showfee' )->field( 'distinct(carBrand)' )->findAll();
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

    public function editCarBrand( $data, $cover){
        $data['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        $query = $this->save($data);
        return $query;
    }

    public function getBrandName( $id ){
        $map['id'] = $id;
        $result = $this->where( $map )->field('name')->find();
        return $result['name'];
    }

    public function getCarBrand($id) {
        $map['id'] = intval($id);
        $result = $this->where($map)->find();
        $result = $this->appendContent($result);
        return $result;
    }

    private function appendContent($data) {
        $data['cover'] = getCover($data['coverId'], 32, 32);
        return $data;
    }

}
