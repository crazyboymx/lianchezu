<?php 
class BoardsAction extends Action
{

    public function _initialize()
	{
		
	
	}
	
	public function index(){
	
	   			$time_range = model('Xdata')->get('square:comment');
    			if(!is_numeric($time_range) || $time_range<1)$time_range = 30;
				$now        = time();
				$yesterday  = mktime(0,0,0,date("m"),date("d"),date("Y"))-$time_range*24*3600;
				
				
		$row = $row?$row:20;
		$listCount=M('weibo_bc')->where()->count(); 		
		$bcdata = M ( 'weibo_bc' )->where ()->order('fengcount DESC')->findPage($row, $listCount);		
		//$bcdata = M ( 'weibo_bc' )->where ('ctime>{$yesterday} AND ctime<{$now}')->order('fengcount DESC')->findPage($row, $listCount);		
       	
		$this->assign('list' , $bcdata) ;		
				
	     
		$this->setTitle('热门图格');

		$this->display();
		
	

		
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