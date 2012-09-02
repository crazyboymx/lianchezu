<?php
$h='
<div class="mt10" style="padding:5px">
<div class="taobao_detail">
 
 
<div class="taobao_detail_img">   
<a href="' . U ( 'home/Look/detail', array ('id' => '{weibo_id}' ) ) . '" target="_blank"><img src="{data.picurl}" /></a>
</div>

<div class="taobao_detail_text">
<ul>
<a href="' . U ( 'home/Look/detail', array ('id' => '{weibo_id}' ) ) . '" target="_blank">{data.g_title}</a>
</ul>

<ul>价格：{data.price} 元</ul>
<ul class="ibuy"><a href="{data.goodsurl}"  class="btn_w_buy" target="_blank">查看购买连接</a></ul>';



$t=' <ul><a w="f" href="javascript:void(0)" onclick="ui.loveok(this,\'1\',\'{favcount}\',\'{weibo_id}\',\''.$_SESSION['mid'].'\',\'{uid}\',\''.getUserName($_SESSION['mid']).'\',\''.getUserFace($_SESSION['mid']).'\')" callback="weibo.deletfav(\'{weibo_id}\')" class="love" id="favloe_{weibo_id}">喜欢（<span class="favCount" id="fav_{weibo_id}">{favcount}</span>）</a></ul>
   <ul class="tou" id="favicon_{weibo_id}"></ul>
       

</div>

</div></div>';
                           
               
    



 
                     
return $h.$t;
?>
