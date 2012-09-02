<?php

class QianMingHooks extends Hooks {

    public function init() {
        
    }

    //显示钩子
    public function home_space_qianming($param) {
        
        $map['uid'] = $param['uid'];
        
        if($param['uid'] == $this->mid){
            
            $oky = 1;
        }else{
            
            $oky = 0;
        }
	// dump($oky);
        $qianming = M('user')->where($map)->field('qianming')->find();
        $this->assign($qianming);
         $this->assign('oky',$oky);
        $this->display('qianming');
       
    }

 
      public function qianmingsave($param)
	{
	 
         $map['uid'] = $this->mid;
         
         echo M('user')->where($map)->setField('qianming', $_POST[qianming]) ? '1' : '0';
	 
    
	}

 

 

}