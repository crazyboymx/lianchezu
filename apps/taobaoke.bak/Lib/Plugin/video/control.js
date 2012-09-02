jQuery.extend(weibo.plugin, {
	video:function(element, options){
	   
	    
	}
});


jQuery.extend(weibo.plugin.video, {
	html:'<div id="video_input">请输入新浪播客、优酷网等视频网站的视频播放页链接： <div><input name="publish_type_data" type="text" style="width: 235px" class="text mr5" value="" /><input type="button" id="shangchuan" class="btn_b" onclick="weibo.plugin.video.add_video()" value="添加"></div>&nbsp;&nbsp;<span style="color:red;"  id="video_add_complete">添加完成！</span></div>',
	click:function(options){
	   weibo.publish_type_box(3,this.html,options)
	},
	add_video:function(){
		$("#shangchuan").val('添加中...');
		var video_url = $("input[name='publish_type_data']").val();
		$.post( U('weibo/plugins/before_publish'),{url:video_url,plugin_id:3},function(txt){
			txt = eval('('+txt+')');
			if(txt.boolen){
				html  = '<div class="indeximg"><div class="mytips">请输入新浪播客,优酷网等的视频播放页链接： </div></div><input name="publish_type_data" type="text" style="width: 255px" class="text mr5" value="" /><input id="shangchuan" type="button" class="btn_b" onclick="weibo.plugin.video.add_video()" value="添加">&nbsp;&nbsp;<span style="color:red"    id="video_add_complete">添加完成</span><input id="pickpic" name="publish_type_data" type="hidden" style="width:86%" value='+video_url+' />';
				$("#publish_type_content").html( html);	
				
				$("#shangchuan").val('添加');
				$('#video_input').show();
				$('#video_add_complete').show();
				$("#content_publish").val( $("#content_publish").val( ) + ' ' + txt.data + ' ');
				weibo.checkInputLength('#content_publish',140);
				$('div .talkPop').show();
			}else{
				alert(txt.message);
				$("#shangchuan").val('重新添加');
			}
		})
	}
});

function switchVideo(id,type,host,flashvar){
	if( type == 'close' ){
		$("#video_mini_show_"+id).show();
		$("#video_content_"+id).html( '' );
		$("#video_show_"+id).hide();
	}else{
		$("#video_mini_show_"+id).hide();
		$("#video_content_"+id).html( showFlash(host,flashvar) );
		$("#video_show_"+id).show();
		
	}
}

//显示视频
function showFlash( host, flashvar) {
	var flashAddr = {
		'youku.com' : 'http://player.youku.com/player.php/sid/FLASHVAR/v.swf',
		'ku6.com' : 'http://player.ku6.com/refer/FLASHVAR/v.swf',
		//'sina.com.cn' : 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
		'sina.com.cn' : 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=FLASHVAR/s.swf',
		//'tudou.com' : 'http://www.tudou.com/v/FLASHVAR',
		'tudou.com' : 'http://www.tudou.com/v/FLASHVAR/&autoPlay=true/v.swf',
		'youtube.com' : 'http://www.youtube.com/v/FLASHVAR',
		'5show.com' : 'http://www.5show.com/swf/5show_player.swf?flv_id=FLASHVAR',
		//'sohu.com' : 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
		'sohu.com' : 'http://share.vrs.sohu.com/FLASHVAR/v.swf',
		'mofile.com' : 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
		'music' : 'FLASHVAR',
		'flash' : 'FLASHVAR'
	};
	var videoFlash = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="430" height="400">'
        + '<param value="transparent" name="wmode"/>'
		+ '<param value="FLASHADDR" name="movie" />'
		+ '<embed src="FLASHADDR" wmode="transparent" allowfullscreen="true" type="application/x-shockwave-flash" width="430" height="400"></embed>'
		+ '</object>';
	var flashHtml = videoFlash;

	flashvar = encodeURI(flashvar);
	if(flashAddr[host]) {
		var flash = flashAddr[host].replace('FLASHVAR', flashvar);
		flashHtml = flashHtml.replace(/FLASHADDR/g, flash);
	}

	return flashHtml;
}