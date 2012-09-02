<?php 
class AtmeModel extends Model{
    var $tableName = 'taobaoke_weibo_atme';
    
    function addAtme($uids,$weibo_id){
		foreach ($uids as $k=>$v){
			$sqlArr[] = "($v,$weibo_id)";
		}
		if( $sqlArr ){
			$result = $this->query("INSERT INTO ".C('DB_PREFIX')."taobaoke_weibo_atme (`uid`,`weibo_id`) values ".implode(',',$sqlArr) );
		}
		return $result;
    }
}
?>
