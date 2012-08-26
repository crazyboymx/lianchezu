<?php 
class MallAction extends Action
{
	public function _initialize()
	{
		// 验证是否允许匿名访问微博广场
		//if ($this->mid <= 0 && intval(model('Xdata')->get('siteopt:site_anonymous_square')) <= 0) {
		//	redirect(U('home'));
		//}

	
	}

    
    //全部  暂未用
	public function all(){
	
		$acdisplay=M('weibo_ac')->order('`display_order` ASC')->findAll(); 
        $this->assign('acdisplay',$acdisplay);
		
		$strType = h($_GET['type']);
        $ac_id=$_GET['ac_id'];
                
    
            
                       
                 $dalei=M('weibo_bc')->field('bc_id')->where("ac_id = $ac_id")->findall();
				 $map['bc_id']=intval( $_GET['bc_id'] );
        		 $map['uid']=$this->uid;
				 
				 $nowtitle= M('weibo_bc')->getField('title',$map);
               
                //  echo  $dl_s=serialize($dalei);
                    foreach ($dalei as $dl)
                   {
                    $dl_arr=$dl['bc_id'].',';
                     
                     $dl_a.=$dl_arr;
                      
                   }
                  // echo $dl_a;
                   $dl_as = substr($dl_a,0,strlen($dl_a)-1); 
                  // dump($dl_arr); 
                 
            //   $neirong=M('weibo')->where("bc_id in (select bc_id from {$this->tablePrefix}weibo_bc where ac_id=$ac_id)")->findpage(20);

            
    	
				
				if ($ac_id){
				$map = " bc_id in ($dl_as) and isdel=0 ";
				
				}else{
				$map = " isdel=0 ";
				}
				if ($strType){
				$map.=" AND type=".$strType;
				}
				
	    $data['order'] = $_GET['order'] ? $_GET['order'] : 'all'; 
		switch ($data['order']) {
				
				case 'comment':
    			
    			$order = 'comment DESC';
    			break;
				
				case 'transpond':
    			$order = 'transpond DESC';
    			break;
				
				case 'all':
    			$order = 'weibo_id DESC';
    			break;
				
				default:
	    		$order = 'weibo_id DESC';
    			exit;
				
		}
		
		
		 //--------------仿知美二次开发------------------------
		$page= $_GET['page'] ;
		$qty=0;
		
		$catelog=M('weibo')->where($map)->limit(20)->order($order)->page($page)->findall();
		$qty=M('weibo')->where($map)->count();
		$this->assign('catelog' , $catelog) ;

		$pagesize=20;
		$this->assign('page',$page);
		$this->assign('countpage',ceil($qty/$pagesize));
		$this->setTitle($nowtitle . '全部分享');
		$this->display();
		
		//--------------仿知美二次开发 end -------------------

		
	}
	
	public function index(){
	
		$acdisplay=M('weibo_ac')->order('`display_order` ASC')->findAll();  
        $this->assign('acdisplay',$acdisplay);
		
		$strType = h($_GET['type']);
        $ac_id=$_GET['ac_id'];
		
    			
                       
                 $dalei=M('weibo_bc')->field('bc_id')->where("ac_id = $ac_id")->findall();
				 
				 $map['ac_id']=intval( $ac_id );
				 
				 $nowtitle= M('weibo_ac')->getField('title',$map);
               
                //  echo  $dl_s=serialize($dalei);
                    foreach ($dalei as $dl)
                   {
                    $dl_arr=$dl['bc_id'].',';
                     
                     $dl_a.=$dl_arr;
                      
                   }
                  // echo $dl_a;
                   $dl_as = substr($dl_a,0,strlen($dl_a)-1); 
                  // dump($dl_arr); 
                 
            //   $neirong=M('weibo')->where("bc_id in (select bc_id from {$this->tablePrefix}weibo_bc where ac_id=$ac_id)")->findpage(20);
       
	   			$time_range = model('Xdata')->get('square:comment');
    			if(!is_numeric($time_range) || $time_range<1)$time_range = 30;
				$now        = time();
				$yesterday  = mktime(0,0,0,date("m"),date("d"),date("Y"))-$time_range*24*3600;
				
		$web = t($_GET['web']);	
	     
		$data['order'] = $_GET['order'] ? $_GET['order'] : 'all'; 
		switch ($data['order']) {
				
				case 'comment':
    			
				if ($ac_id){
				$map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
				
				}else{
				$map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
				}
				
    			$order = 'comment DESC';
				$type_name = '热门回复';
				$this->setTitle($type_name.分享);
    			break;
				
				case 'all':
				
				if ($ac_id){
				$map = " bc_id in ($dl_as) and isdel=0 ";
				$type_name = $nowtitle;
				
				}else{
				$map = " isdel=0 ";
				if ($web =='taomm'){
				$type_name = '淘女郎潮搭';
				}else{
					$type_name = '全部分享';
					}
				}	
				
    			$order = 'weibo_id DESC';
				
			
				$this->setTitle($type_name);
				
    			break;
				
				case 'hot':
				if ($ac_id){
				$map = " bc_id in ($dl_as) and isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
				$type_name = $nowtitle;
				}else{
				$map = " isdel=0 AND ctime>{$yesterday} AND ctime<{$now} ";
				$type_name = '热门分享';
				}
    			$order = 'transpond DESC';
				
				$this->setTitle($type_name);
    			break;
				
				default:
				
				if ($ac_id){
				$map = " bc_id in ($dl_as) and isdel=0 ";
				$type_name = $nowtitle;
				
				}else{
				$map = " isdel=0 ";
				$type_name = '全部分享';
				}	
				
    			$order = 'weibo_id DESC';
				
			
				$this->setTitle($type_name);
				
    			exit;
				
		}	
		
		if ($web =='taomm'){
			$key='mm.taobao.com';
			$map .= " AND from_data LIKE '%{$key}%' ";
    	}	
		
		if ($strType){
				$map.=" AND type=".$strType;
				}

            

		
		 //--------------仿知美二次开发------------------------
		 
		$row = $row?$row:20;
		$listCount=M('weibo')->where($map)->count();
		$catelog=M('weibo')->where($map)->order($order)->findPage($row, $listCount);
		
		
		$this->assign('list' , $catelog) ;

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