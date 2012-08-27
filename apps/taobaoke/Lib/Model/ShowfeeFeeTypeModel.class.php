<?php 
class ShowfeeFeeTypeModel extends BaseModel{
    public function getAllFeeType(){
        //先从缓存里面获取
        $result = $this->field('id,name')->findAll();

        //重组数据集结构
        $newresult = array();
        foreach ( $result as $value ){
            $newresult[$value['id']] = $value['name'];
        }
        return $newresult;
    }

    public function addFeeType( $map ){
        if(empty($map['name'])){
            return -1;
        }
        return $this->add( $map );
    }

    public function deleteFeeType( $map,$formCate = null,$obj = null ){
        //先判断合法性
        if( empty( $map ) )
            throw new ThinkException( "不能是空条件删除" );
        //如果这个分类下有内容，就不允许删除
        $id   = D( 'Event' )->field( 'distinct(FeeType)' )->findAll();
        $temp = array();

        foreach ( $id as $value ){
            $temp[] = $value['FeeType'];
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
            //如果选择的分类中已有了的内容
            if( !in_array( $map['id'][1],$temp ) ){
                return $this->where( $map )->delete();
            }else{
                return 0;
            }
        }
        return false;

    }

    public function editFeeType( $data ){
        $query = $this->save( $data );
        return $query;
    }

    public function getFeeTypeName( $id ){
        $map['id'] = $id;
        $result = $this->where( $map )->field('name')->find();
        return $result['name'];
    }

    public function getFeeType($id) {
        $map['id'] = intval($id);
        $result = $this->where($map)->find();
        return $result;
    }

}
