<?php
    /**
     * EventOptsModel 
     * 活动的选项模型
     * @uses EventBaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
require_once(SITE_PATH.'/apps/event/Lib/Model/EventBaseModel.class.php');

    class EventOptsModel extends EventBaseModel{
        public function getOpts( $optId ){
            $map['id'] = intval($optId);
            return $this->where( $map )->find();
        }
    }
