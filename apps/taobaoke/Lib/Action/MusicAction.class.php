<?php 
class MusicAction extends Action
{
	public function _initialize()
	{
		

	
	}


   
	
	public function index(){
	
		$acdisplay=M('weibo_ac')->order('`display_order` ASC')->findAll();  
        $this->assign('acdisplay',$acdisplay);
		
		$strType = 4;
        $ac_id=$_GET['ac_id'];
		
    			
                       
                 $dalei=M('weibo_bc')->field('bc_id')->where("ac_id = $ac_id")->findall();
               
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
				
	     
		$data['order'] = $_GET['order'] ? $_GET['order'] : 'index'; 
		switch ($data['order']) {
				
				case 'comment':
    			
    			$order = 'comment DESC';
    			break;
				
				case 'transpond':
    			$order = 'transpond DESC';
    			break;
				
				case 'index':
    			$order = 'weibo_id DESC';
    			break;
				
				default:
	    		$order = 'weibo_id DESC';
    			exit;
				
		}	
    	 	  
	      	
		
            

		
		 //--------------仿知美二次开发------------------------
		$row = $row?$row:20;
		$listCount=M('weibo')->where($map)->count();
		$catelog=M('weibo')->where($map)->order($order)->findPage($row, $listCount);
		
		$this->assign('list' , $catelog) ;
		$this->setTitle('音乐分享_虾米音乐_Music');
		$this->display();
		
		//--------------仿知美二次开发 end -------------------

		
	}

	public function music_list(){
	
		//by 仿知美二次开发 qq:2451302968
         $intbc_id = intval( $_GET['bc_id'] );

         $map['bc_id']=$intbc_id;
         $map['uid']=$this->uid;
		 
         //$uid= $this->uid;
         $nowtitle= M('weibo_bc')->getField('title',$map);
            
        $this->assign('nowtitle',$nowtitle);  
		
		 
		 
		
		
		
	
		
        //--------------仿知美二次开发------------------------

		$row = $row?$row:20;
		$listCount=M('weibo')->where("type = 4 and  isdel=0 and uid = '".$this->uid."' AND bc_id = '".$intbc_id."'")->count();
		$catelog=M('weibo')->where("type = 4 and  isdel=0 and uid = '".$this->uid."' AND bc_id = '".$intbc_id."'")->order('weibo_id DESC')->findPage($row, $listCount);
		$this->assign('list' , $catelog) ;
		
		$data2['user'] = D('User')->getUserByIdentifier($this->uid);
		
		$this->setTitle($nowtitle . ' - '.$data2['user']['uname']);
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