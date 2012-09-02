<?php

class CategoryModel extends Model
{   
    
    protected $tableName = 'shop_category'; 
	public function getCategory(){
	   return $this->select();
	}
    
    public function getCategoryInfo($id){
        $where[id] = $id;
        return $this->where($where)->find();
    }
    public function editCategory($parm){
        if($this->save($parm)){
            return '1';
        }else{
            return '0';
        }
        
    }
    public function addCategory($parm){
        return $this->add($parm);
    }
    
    public function isCategoryEmpty($id){
        $w[categiry] =  $id;
        if(is_array(M('shop_goods')->where($w)->find())){
            return '0';
        }else{
            return '1';
        }
    }
    public function doDeleteCategory($id){
        $map[id] = $id;
        if($this->where($map)->delete()){
            return '1';
        }else{
            return '0';
        }
    }
    
}
?>