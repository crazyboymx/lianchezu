jQuery.extend(weibo.plugin, {
	music:function(element, options){
	}
});


jQuery.extend(weibo.plugin.music, {
	html:'<div class="indexmusic" id="end"><div class="mytips">虾米音乐提供技术支持，请输入歌曲名称： </div></div><div id="music_input"><input name="publish_type_data" id="music_url" type="text" style="width: 235px" class="text mr5" value="" /><input type="button" class="btn_b" onclick="weibo.plugin.music.add_music(\'1\')" value="搜索"></div><div id="music_ok" style="display:none; z-index:999">loading</div><div id="page" style="display:none">1</div>',
	click:function(options){
	   weibo.publish_type_box(4,this.html,options)
	},
	add_music:function(page){
		$("#shangchuan").val('搜索中...');
		var music_url = $("input[name='publish_type_data']").val();
		$.post( U('weibo/plugins/before_publish'),{s:music_url,page:page,plugin_id:'4'},function(txt){
			  $('#music_ok').html('<img src="img/loading.gif" align="center" style="text-align:center" />');							 
			txt= eval('('+txt+')');
			var json=eval(txt.results);
		
			if(json){
				
				 $('#load').hide();
				$('#music_more').hide('');
				$('#music_ok').show();
				$('#music_ok').html('');
				$('#end').show('');
				if(eval(txt.total)=='0'){
					$('#end').html('共找'+eval(txt.total)+'首相应歌曲，请重试！');
					$("#shangchuan").val('搜索');
				}else{
					
					var pages=parseInt($('#page').html())+1;
					
					$('#end').html('共找'+eval(txt.total)+'首相应歌曲,<a href="javascript:;" onclick="nextpage('+pages+',\'next\')"">【下一页】</a>,<a href="javascript:;" onclick="nextpage('+(parseInt($('#page').html())-1)+',\'star\')"">【上一页】</a');
					}
				
				 for(var i=0; i<8; i++)  {
var html='<li class="musiclist"><a href="javascript:;" onclick="add('+json[i].song_id+',\''+decodeURI(json[i].album_logo)+'\',\''+decodeURI(json[i].artist_name)+'\',\''+decodeURI(json[i].song_name)+'\');">'+decodeURI(json[i].song_name)+' —— '+decodeURI(json[i].artist_name)+'</a></li>';
				  $('#music_ok').append(html);
			      }
				  $("#shangchuan").val('搜索');
				  weibo.checkInputLength('#content_publish',140);
				  
			
				
			}else{
			
			   $("#content_publish").val('Sorry!可能是我们现在的歌曲还不够?你可以将歌曲'+ music_url+'提交给管理员,谢谢！');
			  weibo.checkInputLength('#content_publish',140);
			 $('div .talkPop').show();
			 $("#shangchuan").val('重新搜索');
			}
		})
	}
});
function add(id,logo,uname,name){
  
	var h1='<input style="display:none" id="pickpic" name="publish_type_data[mp3id]"  value="'+id+'" />';
	var h2='<input style="display:none" name="publish_type_data[logo]"  value="'+logo+'" />';
	$('#test_input').html(h1+h2);
	
	var v1='<div class="pt10"><object width="257" height="33" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="http://www.xiami.com/widget/0_'+id+'/singlePlayer.swf" name="movie"><param value="transparent" name="wmode"><param value="high" name="quality"><embed width="257" height="33" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" quality="high" wmode="transparent" menu="false" src="http://www.xiami.com/widget/0_'+id+'/singlePlayer.swf"></object></div>';
	$('#test_music').html(v1);
    $('#content_publish').val('#分享歌曲#'+name+' —— '+uname+'');
	$('div .talkPop').show();
	  weibo.checkInputLength('#content_publish',140);
	}
function nextpage(id,type){
     $('#music_ok').html('<img src="img/loading.gif" align="center" style="text-align:center" />');
	weibo.plugin.music.add_music(id);
	if(type=='next'){
	$('#page').html(id);
	}else if(type=='star'){
	$('#page').html(id);
	 }
	}	