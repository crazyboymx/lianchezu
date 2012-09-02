<?php 
class WelcomeAction extends Action
{
	public function _initialize()
	{
		// 验证是否允许匿名访问微博广场
		//if ($this->mid <= 0 && intval(model('Xdata')->get('siteopt:site_anonymous_square')) <= 0) {
		//	redirect(U('home'));
		//}
		

	
	}

    
   
	public function index(){
	
		$acdisplay=M('weibo_ac')->where("is_active = 1")->order('`display_order` ASC')->findAll();  
        $this->assign('acdisplay',$acdisplay);
		
		$bcdata = M ( 'weibo_bc' )->where ("is_active = 1")->order('ctime DESC')->limit(4)->order('ctime DESC')->findAll();   	
		$this->assign('bcdata' , $bcdata) ;	
		
		$gatherings=M('weibo')->where("type in(1,4,5) AND isdel=0 AND transpond_id=0 AND jiancount=1")->limit(6)->order('weibo_id DESC')->findall();
		$this->assign('gatherings' , $gatherings) ;
		
		//首页达人推荐
		
		$daren=M('weibo')->where("type=1 AND isdel=0 AND transpond_id=0 AND fengcount=1")->limit(1)->order('weibo_id DESC')->findall();
		$this->assign('daren' , $daren) ;
		
		//达人
		$star_list = D('Star','weibo')->getAllStart();
    	if (count($star_list) > 4) {
			$star_list = $this->_getRandomSubArray($star_list,4);
		}
		
    	if ($star_list) {
    		/*
    		 * 缓存用户数据
    		 */
    		$uids = getSubByKey($star_list, 'uid');
			D('User', 'home')->setUserObjectCache($uids);
			
	    	$this->assign('star_list',$star_list);
	    	
    	}

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