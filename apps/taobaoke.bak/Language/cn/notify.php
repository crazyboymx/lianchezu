<?php
return  array(
    'taobaoke_addComment'   => array(
        'title' => '{actor} 在您的'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 发表了评论',
        'body'  => $content,
    ),
    'taobaoke_replyComment'	=> array(
        'title'	=> '{actor}: ' . $content,
        'body'	=> '回复我在'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 的评论: '.$my_content,
    ),
    'taobaoke_weibo_reply'   => array(
        'title' => '{actor} 回复了你的'.( ($reply_type=='weibo')?'微博':'评论' ),
        'body'  => $content,
        'other' =>'<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">去看看</a>',
    ),
    'taobaoke_weibo_follow'   => array(
        'title' => '{actor} 关注了你',
        'other' =>'<span class="right" id="follow_list_'.$from.'"><script>document.write(followState(\''.getFollowState($receive, $from).'\',\'dolistfollow\','.$from.'))</script></span><a href="'.U('home/space/index',array('uid'=>$from)).'" target="_blank">去TA空间</a>'
    ),
    'taobaoke_weibo_atme'   => array(
        'title' => '{actor} 在微博中提到了你',
        'body'  =>'<a href="'.U('home/space/detail',array('id'=>$weibo_id)).'" target="_blank">'.$content.'</a>',
    ),
);
