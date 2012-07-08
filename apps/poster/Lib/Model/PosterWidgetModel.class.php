<?php
class PosterWidgetModel extends Model{
	public function addWidget($data){
		//检查数据是否为空
        //-1,label为空.-2，widget选择为空.
        if(empty($data['label'])){
            return -1;
        }
        if(empty($data['data'])){
            return -2;
        }
        
        //添加数据
        $rs = $this->add($data);
        return $rs;
	}
	
	public function getWidgetList(){
		$path = APP_PATH."/Lib/Widget/";
	      if(!is_dir($path)){
            return false;
        }
		$result = $this->traversalDir($path);
		return $result;
	}
	
	public function getWidget($id = null){
		if(isset($id)){
			if(is_array($id)){
				$map['id'] = array('in',$id);
			}else{
				$map['id'] = $id;
			}
		}
		$data = $this->where($map)->findAll();
		foreach($data as &$value){
			$value['data'] = stripslashes($value['data']);
			$value['data'] = unserialize($value['data']);
		}
		return $data;
	}
	
	public function getFieldWidget($data){
		$result = array();
		foreach($data as $key=>$value){
			$temp_result['id'] = $value['id'];
			$temp_result['label'] = $value['label'];
			$result[$value['field']][] = $temp_result;
		}
		return $result;
	}
	public function getLeaveField($data,$oldData){
		$field = array();
		foreach($data as $value){
			$field[] = $value['field'];
		}
		$field = array_flip($field);
		foreach($oldData as $key=>$value){
			if(isset($field[$value['field']])){
				unset($oldData[$key]);
			}
		}
		return $oldData;
	}
	
    private function traversalDir ( $path ){
            $result = array();
            $file   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(($path)));
            $i = 0 ;
            foreach ( $file as $key=>$value ) {
            	if(strpos($value->getFilename(),'.php') !== false && strpos($value->getFilename(),'.svn') === false ){
            		require_once($value->getPath().DIRECTORY_SEPARATOR.$value->getFilename());
            		$classname = explode('.',$value->getFilename());
            		$class = new $classname[0]();
            		$result[$i]['name'] = $class->name;
            		$result[$i]['explain'] = $class->explain;
            		$result[$i]['value'] = $classname[0];
            		$i++;
            	}
            }
            return $result;
    }
}