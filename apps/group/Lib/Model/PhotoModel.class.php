<?php

class PhotoModel extends Model {
	var $tableName = 'group_photo';
	
	//获取图片
     public function getPhotoList($html=1,$where = null,$fields=null,$order = null,$limit = null,$isDel=0) {
            //处理where条件
            if(!$isDel)$where[] = 'is_del=0';
            else $where[] = 'is_del=1';
            
   			$where = is_array($where) ? implode(' AND ',$where) : $where ;
            //连贯查询.获得数据集
            $result         = $this->where( $where )->field( $fields )->order( $order )->findPage( $limit ) ;
            if($html) return $result;
            return $result['data'];

     }
     
     
     //图片数目增加
     function addPhotoCount($num, $albumId){
     	//$str = '';
     	//D('Album')->setField('photoCount','(photoCount+1)','id='.$albumId);
     	//D('Album')->query('UPDATE ');
     	//echo D('Album')->getLastSql();
     	//$this->setField()
     	
     	//exit;
     }
}
?>