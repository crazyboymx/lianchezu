<?php 
class FromAction extends Action
{
	public function _initialize()
	{
		// 验证是否允许匿名访问微博广场
		//if ($this->mid <= 0 && intval(model('Xdata')->get('siteopt:site_anonymous_square')) <= 0) {
		//	redirect(U('home'));
		//}

	
	}

    
	public function index(){
	
		$strType = h($_GET['type']);
		
    			
                       
                 $dalei=M('weibo_bc')->field('bc_id')->where("ac_id = $ac_id")->findall();
				 
				 $map['ac_id']=intval( $ac_id );
				 
				 $nowtitle= M('weibo_ac')->getField('title',$map);
               
            		
				
	     
		$data['order'] = $_GET['order'] ? $_GET['order'] : 'all'; 
		switch ($data['order']) {
				
				
				case 'all':
				
				
    			$order = 'weibo_id DESC';
				

    			break;
				
				default:
	
				
	    		$order = 'weibo_id DESC';
				
				
    			exit;
				
		}	
		
		$key = t($_GET['web']);
		$map = " from_data LIKE '%{$key}%' and isdel=0 ";
    	 
		if ($strType){
				$map.=" AND type=".$strType;
				}

            

		
		 //--------------仿知美二次开发------------------------
		 
		$row = $row?$row:20;
		$listCount=M('weibo')->where($map)->count();
		$catelog=M('weibo')->where($map)->order($order)->findPage($row, $listCount);
		
		
		$this->assign('list' , $catelog) ;
		$type_name = $nowtitle;	
		$this->setTitle('来自'.$key);
		$this->display();
		
		//--------------仿知美二次开发 end -------------------

		
	}

   
	/**
	 * 随机获取数组的单元
	 * 
	 * @param array $source_array 原数组
	 * @param int   $numOfRequst  要获取的单元数量
	 * @return array
	 */
	protected function _getRandomSubArray($source_array, $numOfRequst = 1) {
		$res		= array();
		$random_id	= array_rand($source_array, $numOfRequst);
		foreach($random_id as $v) {
			$res[]	= $source_array[$v];
		}
		return $res;
	}
}