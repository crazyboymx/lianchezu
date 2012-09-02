<?php 
return '<div class="feed_img" id="video_mini_show_{rand}"><a id="openvideo" href="javascript:void(0);" onclick="switchVideo({rand},\'open\',\'{data.host}\',\'{data.flashvar}\')"><img src="{data.flashimg}" /></a>
                    <div class="video_play" ><a href="javascript:void(0);" onclick="switchVideo({rand},\'open\',\'{data.host}\',\'{data.flashvar}\')"><img src="__THEME__/images/feedvideoplay.gif" ></a>          </div>
                    </div>
	                <div class="feed_quote" style="margin:auto;display:none;" id="video_show_{rand}"> 
	                  <div class="q_con"> 
	                  
	                  <div id="video_content_{rand}"></div>
					  <p style="margin:0;margin-top:10px;font-size:14px;">{data.title}</p>
	                  </div>
	                </div>';
?>