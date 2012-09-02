<?php
//获取应用配置
function getConfig($key=NULL){
	$config = model('Xdata')->lget('taobaoke');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=0;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=0;//换算为秒

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}
//获取活动封面存储地址
function getCover($coverId,$width=80,$height=80){
	$cover = D('Attach')->field('savepath,savename')->find($coverId);
	if($cover){
		$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=".get_photo_url($cover['savepath'].$cover['savename']);
	}else{
		$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/event/Tpl/default/Public/images/hdpic1.gif";
	}
	return $cover;
}
//根据存储路径，获取图片真实URL
function get_photo_url($savepath) {
	return './data/uploads/'.$savepath;
}

/**
 * getBlogShort 
 * 去除标签，截取blog的长度
 * @param mixed $content 
 * @param mixed $length 
 * @access public
 * @return void
 */
function getBlogShort($content,$length = 40) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

//获取用户分类个数
function getBcuidCount($uid) {
    if ($uid<>"")
    {
        $bcuidcount=M('taobaoke_bc')->where('uid=' . $uid)->count();
        return $bcuidcount;
    }
}

//获取子类名字
function getBname($id) {
    if ($id <> "") {
        $bname = M('taobaoke')->where('weibo_id=' . $id)->findall();

        foreach ($bname as $bcn) {
            $bcname_arr = $bcn['bc_id'];
        }


        $map['bc_id'] = $bcname_arr;

        $nowtitle = M('taobaoke_bc')->getField('title', $map);

        return $nowtitle;
    }
}

//获取子类数
function getCcCount($bc_id) {
    if ($bc_id<>"")
    {
        $cccount=M('taobaoke')->where( 'type in(1,3,4,5) and isdel=0 and bc_id=' . $bc_id)->count();
        return $cccount;
    }
}
