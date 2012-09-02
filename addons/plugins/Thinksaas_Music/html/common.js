jQuery.extend(weibo.plugin, {
	tsas:function(element, options){
	   
	    
	}
});


jQuery.extend(weibo.plugin.tsas, {
	html:'<div id="input_data"></div><div class="input_box"><div class="input_search"><label class="t_df">请输入要搜索的歌曲</label><input type="text" onblur="onblurs(this);" onfocus="onfocuss();" class="input" name="kw"><input type="button" class="btn_b" value="搜索" style="margin-bottom:10px;margin-left:5px;" onclick="weibo.plugin.tsas.searchMusicList(1)"></div><div class="data"><span class="num"></span><span class="shitingt">试听</span></div><div id="load"></div><div class="song_list"><ul id="list"></ul></div><div id="page"></div></div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options)
	},
	
	 searchMusicList:function(page) {
        var name = $('input[name=\'kw\']').val();
        musicPage = page;
        $('#load').html('<img class=\'loading\' src=\''+ _THEME_+'/images/zw_img.gif\'>搜索中。。').show();
       
        var src1 = "http://kuang.xiami.com/app/nineteen/search/key/" + encodeURIComponent(name) + "/diandian/1/page/" + musicPage + "/callback/weibo.plugin.tsas.Music";
        var __musicList = '';
        var JSONP = document.createElement("script");
        JSONP.type = "text/javascript";
        JSONP.src = src1;
        document.getElementsByTagName("head")[0].appendChild(JSONP);
        JSONP.onload = JSONP.onreadystatechange = function() {
            if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
                JSONP.onload = JSONP.onreadystatechange = null;  //防止IE内存
            }
        }
    },
	
	Music:function(jsonData){
	     __musicList = '';
		 $('#load').hide();
		 $('#page').html('');
        for (var i in jsonData.results) {
            __musicList += '<li><a href="javascript:;" onclick="weibo.plugin.tsas.init(this)" song_id="'+jsonData.results[i].song_id+'" song_name="'+decodeURIComponent(jsonData.results[i].song_name)+'" artist_name="'+decodeURIComponent(jsonData.results[i].artist_name)+'" album_name="'+decodeURIComponent(jsonData.results[i].album_name)+'" album_logo="'+decodeURIComponent(jsonData.results[i].album_logo)+'">' + decodeURIComponent(jsonData.results[i].song_name) + '  -- ' + decodeURIComponent(jsonData.results[i].artist_name) + '</a><span class="play" title="试听" onclick="shiting(this,'+jsonData.results[i].song_id+');" id="play_icon_'+jsonData.results[i].song_id+'"></span><div style="display:none;visibility:hidden;" id="play_box_'+jsonData.results[i].song_id+'" class="ed"><embed src="http://st.xiami.com/res/player/widget/singlePlayer.swf?dataUrl=http://data.xiami.com/widget/xml-single/sid/' + jsonData.results[i].song_id+ '&autoplay=y" type="application/x-shockwave-flash" width="257" height="1em" wmode="transparent" /></div></li>';
        }
        if (jsonData.total == 0) {
            __musicList = '<div class="nosong"><img height="48" width="48" src=\''+Plugin_path+'/html/images/no.gif\' align="absmiddle"/> 抱歉，没有找到关于 <font color="red">' + $('input[name=\'kw\']').val() + '</font> 的音乐。</div>';
			
            $('#list').html(__musicList);
            return false;
        }
        $('#list').html(__musicList);
        $('.num').html('搜索结果('+jsonData.total+')首');
		$('.data').show();
        if (jsonData.total > 8) {
          $('#page').html(pagerView(parseInt(jsonData.total), parseInt(musicPage)));
        }
	},
	init:function(o){
	  var text='#音乐分享# ' +attr_value(o,'song_name') + '--'+attr_value(o,'artist_name')+'';
	  var patt   =   new   RegExp(text,"g");  
	  var content_publish=$("#content_publish");
	  if( content_publish.val().search(patt) == '-1' ){
		content_publish.val( content_publish.val() + text);
	  }
	        weibo.publish_type_val(999);
			var input='<input name="publish_type_data[song_id]" value="'+attr_value(o,'song_id')+'" type="hidden"><input name="publish_type_data[song_name]" value="'+attr_value(o,'song_name')+'"  type="hidden"><input name="publish_type_data[artist_name]" value="'+attr_value(o,'artist_name')+'"  type="hidden"><input name="publish_type_data[album_logo]" value="'+attr_value(o,'album_logo')+'"  type="hidden">';
			$("#input_data").html(input);
			$('div .talkPop').addClass('vh');
			weibo.checkInputLength(_LENGTH_);
			
	
	 }
});

function onblurs(o){
	var txt=o.value;
	if(txt==''){
	  $('.t_df').show();
	}
}

function onfocuss(){
	$('.t_df').hide();
}

 function pagerView(dataCount, currentPage) {
        var __musicListPager = '<div class="page" id="music_pager"><a href="javascript:weibo.plugin.tsas.searchMusicList(' + (currentPage <= 1 ? 1 : currentPage - 1) + ');" onFocus="this.blur()" unselectable="on" title="上一页">上一页</a>';
        var __totalPage = dataCount / 8;
        __totalPage = __totalPage > parseInt(__totalPage) ? parseInt(__totalPage) + 1 : parseInt(__totalPage);
        var __forLength = currentPage > 10 ? (currentPage > 1000 ? 2 : 3) : 4;
        var __forStep = 2;
        var __forStart = (__totalPage > 4 && currentPage > __forStep) ? (currentPage < __totalPage - __forLength ? currentPage - __forStep: __totalPage - __forLength) : 1;
        var __forEnd = __forStart + __forLength < __totalPage + 1 ? __forStart + __forLength + 1 : __totalPage + 1;
        if (__totalPage > 4 && currentPage > __forStep + 1) __musicListPager += '<a href="javascript:weibo.plugin.tsas.searchMusicList(1)" onFocus="this.blur()" unselectable="on"  title="' + i + '" id="page_to_' + i + '" >1...</a>';
        for (var i = __forStart; i < __forEnd; i++) {
            if (currentPage == i) {
                __musicListPager += '<span class="current">' + i + '</span>';
            } else {
                __musicListPager += '<a href="javascript:weibo.plugin.tsas.searchMusicList(' + i + ')" onFocus="this.blur()" unselectable="on"  title="' + i + '" id="page_to_' + i + '" >' + i + '</a>';
            }
        }
        if (__forEnd < __totalPage) __musicListPager += '<a href="javascript:weibo.plugin.tsas.searchMusicList(' + __totalPage + ')" onFocus="this.blur()" unselectable="on" title="' + __totalPage + '" id="page_to_' + __totalPage + '">...' + __totalPage + '</a>';
        if (currentPage < __totalPage) {
            currentPage++;
			__musicListPager += '<a href="javascript:weibo.plugin.tsas.searchMusicList(' + currentPage + ')" onFocus="this.blur()" unselectable="on" title="下一页" id="page_to_' + currentPage + '">下一页</a>';
        }
        __musicListPager += '<a href="javascript:weibo.plugin.tsas.searchMusicList(' + __totalPage + ')" onFocus="this.blur()" unselectable="on" title="最后一页" id="page_to_' + __totalPage + '">最后一页</a></div>';
        return __musicListPager;
}

var playing=0;
function shiting(o,song_id){
	if(song_id==playing){
		$('#play_box_'+song_id).hide();
		$(o).removeClass('playend').addClass('play').attr('title','试听');
		playing=0;
	}else{
	$('.ed').hide();
	$('.playend').addClass('play').removeClass('playend');
	$(o).removeClass('play').addClass('playend').attr('title','停止');
	$('#play_box_'+song_id).show();
	 playing=song_id;
	}
}	
function attr_value(obj,attr){
	var value=$(obj).attr(attr);
	return value;
}