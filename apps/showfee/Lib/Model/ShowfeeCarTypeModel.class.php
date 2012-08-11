<?php
class ShowfeeCarTypeModel extends BaseModel{

    public function getAllCarType(){
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

    public function deleteCarType( $map,$formCate = null,$obj = null ){
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

    public function editType( $data, $cover ){
        $data['coverId'] = $cover['status'] ? $cover['info'][0]['id'] : 0;
        $query = $this->save( $data );
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
        return $result;
    }

}
