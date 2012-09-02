<?php
class TimePillHooks extends Hooks
{
	public function init()
	{
	}
        //显示钩子
        public function home_index_left_top()
        
        {
            $this->display('timepilltool');
            
        }
        //添加
         public function put()
        
        {
            $this->display('put');
            
        }
        //提取页
          public function get()
        
        {
            $tpdata = M('timepill')->where("uid={$this->mid}")->findall();
            $tpcount=M('timepill')->where("uid={$this->mid}")->count();
            $this->assign('tpcount',$tpcount);
            $this->assign('tpdata', $tpdata);
            $this->display('get');
            
        }
        //提取页
          public function getdo()
        
        {
             $map['key']=h(t($_POST['key']));
             $tp_content= M('timepill')->getField('content', $map);
             $tp_gettime= M('timepill')->getField('gettime', $map);
             $tp_puttime = M('timepill')->getField('puttime', $map);
             $tp_uid= M('timepill')->getField('uid', $map);
             $tp_uid=getUserName($tp_uid);
            
             
         if($tp_uid && $tp_gettime <= time()){ 
              
             $tp_puttime=dateFormat($tp_puttime);
             $return['tpc']	= $tp_content;
             
             $return['tppt']	= $tp_puttime;
             $return['tpu']	= $tp_uid;
	     $return['ok']	= 1;
              
              
             exit(json_encode($return)); 
            
         }else if($tp_uid && $tp_gettime > time()){
             
             $tp_gettime=dateFormat($tp_gettime);
             
             $return['tpgt']	= $tp_gettime; 
	     $return['ok']	= 2;
              
              
             exit(json_encode($return)); 
             
             
        }else{
             
             
             
             echo 0;
             
         }
             
             
        }
        
        
        
        //说明页
          public function what()
        
        {
            $this->display('what');
            
        } 
        //封存
          public function save()
        
        { 
              
    	$data['uid'] 	  = $this->mid;
        $data['content']  = h(t($_POST['content']));
        $data['key']  = h(t($_POST['key']));
        $data['puttime'] = time();
        $data['gettime'] = strtotime(h(t($_POST['gettime']))) ;
    	 
    	$res = M('timepill')->add($data);
     
     
                 $res = M('timepill')->add($data);
 
                $uid = $this->mid;
                $data['ctime'] = time(); 
                $data['content'] = "我封印了一颗#时间胶囊# <img width='25' height='25' src='addons/plugins/TimePill/html/timepills.png'/> " . $data['key'] . " 这个是提取时间哦 " . dateFormat($data['gettime']);
                 M('weibo')->add($data);
              
     
    	if (false !== $res) {
    		echo 1;
    	} else {
    		echo 0;
    	}
        
	}

}