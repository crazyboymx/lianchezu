<?php 
class ShoppingAction extends Action
{
	public function _initialize()
	{
		

	
	}


   
	
	public function index(){
	
		$acdisplay=M('weibo_ac')->order('`display_order` ASC')->findAll();  
        $this->assign('acdisplay',$acdisplay);
		
		$strType = 5;
        $ac_id=intval($_GET['ac_id']);
		
		
		
		
    			
                       
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
    	 	  
	    if($_GET['tag']){
		   $tag = M('goodstag')->where('tag="'. t($_GET['tag']).'"')->findall();
		    $key = $_GET['tag'];
		       foreach($tag as $s){
		          $sq[]=$s['goodsid'];
		        }
		  $map.=' AND weibo_id IN ('.implode(',',$sq).') ';
		}  	
		
		
		
		if($_GET['minPrice'] AND $_GET['maxPrice']){
		
			$minPrice=intval($_GET['minPrice']);
			$minPrice=$minPrice - 1;
		
			$maxPrice=intval($_GET['maxPrice']);
		
			$maxPrice=$maxPrice + 1;
		
			$map.= " AND  price>{$minPrice} AND price<{$maxPrice} ";
		}else if($_GET['minPrice'] AND !$_GET['maxPrice']){
		
			$minPrice=intval($_GET['minPrice']);
			$minPrice=$minPrice - 1;
		
			$map.= " AND  price>{$minPrice}";
		
		} else if(!$_GET['minPrice'] AND $_GET['maxPrice']){
		

		
			$maxPrice=intval($_GET['maxPrice']);
		
			$maxPrice=$maxPrice + 1;
		
			$map.= " AND price>{$maxPrice}";
		
		} 
            

		
		 //--------------仿知美二次开发------------------------
		$row = $row?$row:20;
		$listCount=M('weibo')->where($map)->count();
		$catelog=M('weibo')->where($map)->order($order)->findPage($row, $listCount);
		
		$this->assign('list' , $catelog) ;
		
		$this->setTitle('淘宝_淘宝商城宝贝分享_Shopping');
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