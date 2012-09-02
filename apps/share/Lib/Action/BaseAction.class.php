<?php
    class BaseAction extends Action
    {
        protected $gid;
        /**
         * 初始化
         */
        protected $isadmin;
		protected $iscreater;
        protected $config;
        protected $shareinfo;
        protected $siteTitle;

        protected function _initialize()
        {
            // 基本配置
            $this->config = model('Xdata')->lget('share');
        	$this->assign('config',$this->config);
        }
		
		
		// 系统的配置文件
        protected function base()
        {
            $this->assign($class);
        	$this->assign('need_login',1);
        	$this->config = model('Xdata')->lget('share');
        	$this->assign('config',$this->config);
      		$this->siteTitle = getSiteTitle();
        }


 	}