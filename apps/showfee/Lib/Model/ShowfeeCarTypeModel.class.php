<?php
class ShowfeeCarTypeModel extends BaseModel{
    public function getAllCarType(){
        //先从缓存里面获取
        $result = $this->field('id,name,brandId,coverId')->findAll();

        //重组数据集结构
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $this->appendContent($value);
        }
        return $newresult;
    }

    public function getList($map, $order, $limit) {
        $result = $this->where($map)->order($order)->findPage($limit);
        //将属性追加
        foreach( $result['data'] as &$value ) {
            $value = $this->appendContent($value);
        }
        return $result;
    }

    //我容易么我。。。 
    public function getCarTypesByBrand($brandId){
        /*$result = $this->where(" brandId ='".$brandId."'")->field("id,name,coverId")->find();
        foreach ( $result as $value ){
            $value['cover'] = getCover($value["coverId"]);
        }
        return $result;*/
        //return $this->getList(array("brandId"=>$brandid),"id",9999);
        $result = $this->field('id,name,brandId,coverId')->where("brandId=".$brandId)->findAll();

        //重组数据集结构
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $this->appendContent($value);
        }
        return $newresult;
    }

    public function getAllCarTypeName(){
        //先从缓存里面获取
        $result = $this->field('id,name')->findAll();

        //重组数据集结构
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $value['name'];
        }
        return $newresult;
    }

    public function addCarType( $map, $cover ){
        if(empty($map['name']) || $cover == null){
            return -1;
        }
        $map['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        return $this->add( $map );
    }

    public function deleteCarType($map, $formCate = null, $obj = null) {
        //先判断合法性
        if( empty( $map ) )
            throw new ThinkException( "不能是空条件删除" );
        //如果这个分类下有内容，就不允许删除
        $id   = D( 'Showfee' )->field( 'distinct(carType)' )->findAll();
        $temp = array();

        foreach ( $id as $value ){
            $temp[] = $value['carType'];
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

    public function editCarType($data, $cover){
        $data['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        $query = $this->save($data);
        return $query;
    }

    public function getTypeName( $id ){
        $map['id'] = $id;
        $result = $this->where( $map )->field('name')->find();
        return $result['name'];
    }

    public function getCarType($id) {
        $map['id'] = intval($id);
        $result = $this->where($map)->find();
        $result = $this->appendContent($result);
        return $result;
    }

    public function appendContent($data) {
        $carBrandM = D('ShowfeeCarBrand');
        $brand = $carBrandM->getCarBrand($data['brandId']);
        $data['brandName'] = isset($brand['name']) ? $brand['name'] : '';
        $data['brandCover'] = $brand['cover'];
        $data['cover'] = getCover($data['coverId']);
        return $data;
    }

}
