function callback(fun,argum){
	fun(argum);
}

// 删除类型框
function delTypeBox(){
	$('input[name="publish_type"]').val( 0 );
	$('.talkPop').remove();
}

$(document).ready(function(){
	  // 评论切换
	  $("a[rel='comment']").live('click',function(){
	      var id = $(this).attr('minid');
	      var $comment_list = $("#comment_list_"+id);
		  if( $comment_list.html() == '' ){
			  $comment_list.html('<div class="feed_quote feed_wb" style="text-align:center"><img src="'+ _THEME_+'/images/icon_waiting.gif" width="15"></div>');
			$.post( U("weibo/Index/loadcomment"),{id:id},function(txt){
				$comment_list.html( txt ) ;
			});
		  }else{
			  $comment_list.html('');
		  }
	  });
	  
	

	// 发布评论
	$("form[rel='miniblog_comment']").live("submit", function(){
		var _this = $(this);
		var callbackfun = _this.attr('callback');
		var _comment_content = _this.find("textarea[name='comment_content']");
		if( _comment_content.val()=='' ){
			ui.error('内容不能为空');
			return false;
		}
		_this.find("input[type='submit']").val( '评论中...').attr('disabled','true') ;
		var options = {
		    success: function(txt) {
				txt = eval('('+txt+')');
				_this.find("input[type='submit']").val( '确定');
			       _this.find("input[type='submit']").removeAttr('disabled') ;
				   _comment_content.val('');
				if(callbackfun){
					callback(eval(callbackfun),txt);
				}else{
					_comment_content.css('height','');
			       $("#comment_list_before_"+txt.data['weibo_id']).after( txt.html );

				   $("#replyid_" + txt.data['weibo_id'] ).val('');
				   //更新评论数
				   $("a[rel='comment'][minid='"+txt.data['weibo_id']+"']").html("评论("+txt.data['comment']+")");
				 //  _this.find("textarea[name='comment_content']").focus();
				   
				}
		    }
		};
		_this.ajaxSubmit( options );
	    return false;
	});
});

weibo = function(){
	
}

weibo.prototype = {
	//初始化分享发布
	init:function(option){
		var __THEME__ = "<?php echo __THEME__;?>";
		var Interval;
		$("#publish_type_content_before").html("<span>添加：</span><a href=\"javascript:void(0)\" target_set=\"content_publish\" onclick=\"ui.emotions(this)\" class=\"a52\"><img class=\"icon_add_face_d\" src=\""+__THEME__+"/images/zw_img.gif\" />表情</a> <a href=\"javascript:void(0)\" onclick=\"addtheme()\" class=\"a52\"><img class=\"icon_add_topic_d\" src=\""+__THEME__+"/images/zw_img.gif\" />话题</a><a href=\"javascript:void(0)\" onclick=\"weibo.plugin.goods.click(150)\" class=\"a52\" id=\"shoplogo\">宝贝</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.image.click(200)\" class=\"a52\"><img class=\"icon_add_img_d\" src=\""+__THEME__+"/images/zw_img.gif\" />图片</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.dapei.click(250)\" class=\"a52\"><img class=\"icon_add_img_d2\" src=\""+__THEME__+"/images/zw_img.gif\" />搭配</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.shaihuo.click(310)\" class=\"a52\"><img class=\"icon_add_img_d3\" src=\""+__THEME__+"/images/zw_img.gif\" />晒货</a><a href=\"javascript:void(0)\" onclick=\"weibo.plugin.video.click(360)\" class=\"a52\"><img class=\"icon_add_video_d\" src=\""+__THEME__+"/images/zw_img.gif\" />视频</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.music.click(405)\" class=\"a52\"><img class=\"icon_add_music_d\" src=\""+__THEME__+"/images/zw_img.gif\" />音乐</a>");

		$("#content_publish").keypress(function(event){
			var key = event.keyCode?event.keyCode:event.which?event.which:event.charCode;
	        if (key == 27) {
	        	clearInterval(Interval);
	        }
			weibo.checkInputLength(this,140);
		}).blur(function(){
			clearInterval(Interval);
			weibo.checkInputLength(this,140);
		}).focus(function(){
			//分享字数监控
			clearInterval(Interval);
		    Interval = setInterval(function(){
		    	weibo.checkInputLength('#content_publish',140);
			},300);
		});
		weibo.checkInputLength('#content_publish',140);
		shortcut('ctrl+return',	function(){weibo.do_publish();clearInterval(Interval);},{'target':'miniblog_publish'});
	},
	//发布前的检测
	before_publish:function(){
		
		if( $.trim( $('#content_publish').val() ) == '' ){
            ui.error('内容不能为空');		
			return false;
		}
		return true;
	},
	//发布操作
	do_publish:function(){
		if( weibo.before_publish() ){
			weibo.textareaStatus('sending');
			var options = {
			    success: function(txt) {
			      if(txt){
			    	   weibo.after_publish(txt);
			      }else{
	                  alert( '发布失败' );
			      }
				}
			};		
			$('#miniblog_publish').ajaxSubmit( options );
		    return false;
		}
	},
	//分享专题发布操作
	do_publish_zhuanti:function(){
		if( weibo.before_publish() ){
			weibo.textareaStatus('sending');
			var options = {
			    success: function(txt) {
			      if(txt){
			    	   weibo.after_publish_zhuanti(txt);
			      }else{
	                  alert( '发布失败' );
			      }
				}
			};		
			$('#miniblog_publish').ajaxSubmit( options );
		    return false;
		}
	},
	//发布后的处理
	after_publish:function(txt){
		if(txt==0) {
			ui.success('您发布的分享含有敏感词，请等待审核！');
		}else {
			delTypeBox();
		    $("#feed_list").prepend( txt ).slideDown('slow');
		    var sina_sync = $('#sina_sync').attr('checked');
		    $('#miniblog_publish').clearForm();
		    if (sina_sync) {
		    	$('#sina_sync').attr('checked', true);
		    }
		    weibo.upCount('weibo');
		    ui.success('分享发布成功');
			$("#taobao").hide();
		    weibo.checkInputLength('#content_publish',140);
		}
	},
	//分享专题发布后的处理
	after_publish_zhuanti:function(txt){
		if(txt==0) {
			ui.success('您发布的分享含有敏感词，请等待审核！');
		}else {
			delTypeBox();
		    $("#feed_list").prepend( txt ).slideDown('slow');
		    var sina_sync = $('#sina_sync').attr('checked');
		    $('#miniblog_publish').clearForm();
		    if (sina_sync) {
		    	$('#sina_sync').attr('checked', true);
		    }
		    weibo.upCount('weibo');
			location.reload();
			$("#taobao").hide();
		    weibo.checkInputLength('#content_publish',140);
		}
	},
	
	//发布按钮状态
	textareaStatus:function(type){
		var obj = $('#publish_handle');
		if(type=='on'){
			obj.removeAttr('disabled').attr('class','btn_big hand');
		//}else if( type=='sending'){
		//	obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}else{
			obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}
	},
	
	//----------------------------仿知美二次开发 ---------------------//
	//删除一条分享
	deleted:function(weibo_id){
		$.post(U("taotaoke/Operate/delete"),{id:weibo_id},function(txt){
			if( txt ){
				$("#list_li_"+weibo_id).slideUp('fast');
				weibo.downCount('weibo');
				location.reload();

			}else{
				alert('删除失败');
			}
		});
	},
	
	
	//仿知美二次开发 
	deleted2:function(weibo_id){
		$.post(U("taobaoke/Operate/delete"),{id:weibo_id},function(txt){
			if( txt ){
				$("#list_li_"+weibo_id).slideUp('fast');
				weibo.downCount('weibo');
				//location.reload();
				var url= U('home/User/index');
				window.location = url; 

			}else{
				alert('删除失败');
			}
		});
	},
	
	
	//删除喜欢
	deletfav:function(weibo_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/delfav"),{id:weibo_id},function(txt){
			if( txt ){
				if(type=='mylove'){
					$("#list_li_"+weibo_id).slideUp('fast');
					   weibo.upfavCount('del');
					}else{
				var text='<span class="love">已取消</span>';
			   $("#favicon_"+weibo_id).html(text);
			   $("#favloe_"+weibo_id).slideUp('fast');
			      weibo.upfavCount('del');
					}
			}else{
				alert('取消喜欢失败，请检查网络或联系管理员');
			}
		});
	},
	
	
	//删除喜欢
	deletfav2:function(weibo_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/delfav"),{id:weibo_id},function(txt){
			if( txt ){
				if(type=='mylove'){
					$("#list_li_"+weibo_id).slideUp('fast');
					   weibo.upfavCount('del');
					}else{
				var text='<a href="javascript:;" class="hb favourite-btn">喜欢</a>';
			   $("#favicon_"+weibo_id).html(text);
			   $("#favloe_"+weibo_id).slideUp('fast');
			      weibo.upfavCount('del');
					}
			}else{
				alert('取消喜欢失败，请检查网络或联系管理员');
			}
		});
	},
	
	
	//删除推荐
	delettuijian:function(weibo_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/deltuijian"),{id55:weibo_id},function(txt){
			if( txt ){
				if(type=='mytuijian'){
					$("#list_li_"+weibo_id).slideUp('fast');
					   weibo.uprjcount('del');
					}else{
				var text='<span class="love">已取消</span>';
			   $("#tuijianicon_"+weibo_id).html(text);
			   $("#tuijianloe_"+weibo_id).slideUp('fast');
			      weibo.uprjcount('del');
					}
			}else{
				alert('取消推荐失败，请检查网络或联系管理员');
			}
		});
	},
	
	//删除推荐达人
	deletdaren:function(weibo_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/deldaren"),{id:weibo_id},function(txt){
			if( txt ){
				if(type=='mydaren'){
					$("#list_li_"+weibo_id).slideUp('fast');
					   weibo.updrcount('del');
					}else{
				var text='<span class="love">已取消</span>';
			   $("#darenicon_"+weibo_id).html(text);
			   $("#darenloe_"+weibo_id).slideUp('fast');
			      weibo.updrcount('del');
					}
			}else{
				alert('取消推荐失败，请检查网络或联系管理员');
			}
		});
	},
	
	//删除关注
	deletfengmian:function(bc_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/delfengmian"),{id:bc_id},function(txt){
			if( txt ){
				if(type=='myfengmian'){
					$("#list_li_"+bc_id).slideUp('fast');
					   weibo.upfmcount('del');
					}else{
				var text='<a href="javascript:;" class="longbutton unconcern">已取消</a>';
			   $("#fengmianicon_"+bc_id).html(text);
			   $("#fengmianloe_"+bc_id).slideUp('fast');
			      weibo.upfmcount('del');
					}
			}else{
				alert('取消失败，请检查网络或联系管理员');
			}
		});
	},
	
	//删除关注 图格详细页
	delettuijiantuge:function(bc_id,type){
		//alert("asdf");
		$.post(U("weibo/Operate/delfengmian"),{id:bc_id},function(txt){
			if( txt ){
				if(type=='myfengmian'){
					$("#list_li_"+bc_id).slideUp('fast');
					   weibo.upfmcount('del');
					}else{
				var text='<a href="javascript:;" class="winbtn unaddin">已取消</a>';
			   $("#fengmianicon_"+bc_id).html(text);
			   $("#fengmianloe_"+bc_id).slideUp('fast');
			      weibo.upfmcount('del');
					}
			}else{
				alert('取消失败，请检查网络或联系管理员');
			}
		});
	},
	
	//----------------------------仿知美二次开发  end ---------------------//
	
	//收藏
	favorite:function(id,o){
		$.post( U("weibo/Operate/stow") ,{id:id},function(txt){
			if( txt ){
				$(o).wrap('<span class=nwl_share id=content_'+id+'></span>');
				$('#content_'+id).html('已收藏');
			}else{
				alert('收藏失败！已收藏过？');
			}
		});
	},
	//取消收藏
	unFavorite:function(id,o){
		$.post( U("weibo/Operate/unstow") ,{id:id},function(txt){
			if( txt ){
				$('#list_li_'+id).slideUp('slow');
			}else{
				alert('取消失败');
			}
		});
	},
	//仿知美二次开发
	//发表评论
	pinglun:function(id){
		ui.box.load( U("weibo/index/publishcomment",["id="+id] ),{title:'发表评论',closeable:true});
	},
	
	//转发
	transpond:function(id,upcontent){
		upcontent = ( upcontent == undefined ) ? 1 : 0;
		ui.box.load( U("taobaoke/operate/transpond",["id="+id,"upcontent="+upcontent] ),{title:'转发',closeable:true});
	},
	
	//仿知美二次开发 编辑功能
	myedit:function(id,bc_id,upcontent){
		upcontent = ( upcontent == undefined ) ? 1 : 0;
		ui.box.load( U("taobaoke/operate/myedit",["id="+id,"bc_id="+bc_id,"upcontent="+upcontent] ),{title:'编辑',closeable:true});
	},
	
	//关注话题
	followTopic:function(name){
		$.post(U('weibo/operate/followtopic'),{name:name},function(txt){
			txt = eval( '(' + txt + ')' );
			if(txt.code==12){
				$('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.unfollowTopic(\''+txt.topicId+'\',\''+name+'\')">取消该话题</a>');
			}
		});
	},
	unfollowTopic:function(id,name){
		$.post(U('weibo/operate/unfollowtopic'),{topicId:id},function(txt){
			if(txt=='01'){
				$('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.followTopic(\''+name+'\')">关注该话题</a>');
			}
		});	
	},
	quickpublish:function(text){
		$.post(U('weibo/operate/quickpublish'),{text:text},function(txt){
			ui.box.show(txt,{title:'添加分享',closeable:true});
		});
	},
	//更新计数器
	upCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())+1 );
		}
	},
	//----------------仿知美二次开发--------------------
	
	//更新喜欢
	upfavCount:function(type){
		if(type=='weibo'){
			$("#fav_count").html( parseInt($('#fav_count').html())+1 );
		}else{
			$("#fav_count").html( parseInt($('#fav_count').html())-1 );
			}
	},
	
	//更新推荐
	updrcount:function(type){
		if(type=='weibo'){
			$("#daren_count").html( parseInt($('#daren_count').html())+1 );
		}else{
			$("#daren_count").html( parseInt($('#daren_count').html())-1 );
			}
	},
	
	//更新推荐
	uprjcount:function(type){
		if(type=='weibo'){
			$("#tuijian_count").html( parseInt($('#tuijian_count').html())+1 );
		}else{
			$("#tuijian_count").html( parseInt($('#tuijian_count').html())-1 );
			}
	},
	//更新关注
	upfmcount:function(type){
		if(type=='weibo'){
			$("#fengmian_count").html( parseInt($('#fengmian_count').html())+1 );
		}else{
			$("#fengmian_count").html( parseInt($('#fengmian_count').html())-1 );
			}
	},
	
	
	//----------------仿知美二次开发 end--------------------
	
	
	downCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())-1 );
		}
	},
	//检查字数输入
	checkInputLength:function(obj,num){
		var len = getLength($(obj).val(), true);
		var wordNumObj = $('.wordNum');
		
		if(len==0){
			wordNumObj.css('color','').html('你还可以输入<strong id="strconunt">'+ (num-len) + '</strong>字');
			weibo.textareaStatus('off');
		}else if( len > num ){
			wordNumObj.css('color','red').html('已超出<strong id="strconunt">'+ (len-num) +'</strong>字');
			weibo.textareaStatus('off');
		}else if( len <= num ){
			wordNumObj.css('color','').html('你还可以输入<strong id="strconunt">'+ (num-len) + '</strong>字');
			weibo.textareaStatus('on');
		}
	},
	publish_type_box:function(type_num,content,mg_left){
		var __THEME__ = "<?php echo __THEME__;?>";
		var html = '<div class="talkPop"><div  style="position: relative; height: 7px; line-height: 3px;">'
		     + '<img class="talkPop_arrow" style="margin-left:'+ mg_left +'px;position:absolute;" src="'+__THEME__+'/images/zw_img.gif" /></div>'
             + '<div class="talkPop_box">'
			 + '<div class="close" id="weibo_close_handle"><a href="javascript:void(0)" class="del" onclick=" delTypeBox()" > </a></div>'
			 + '<div id="publish_type_content">'+content+'</div>'
			 + '</div></div>';
		$('input[name="publish_type"]').val( type_num );
		$('div.talkPop').remove();
		$("#publish_type_content_before").after( html );
	}
}

/**
weibo.prototype.plugin = function(name, fn) {
    this.prototype[name] = fn;  
    return this;  
}
**/
weibo = new weibo();

weibo.plugin = {};

function addtheme(){
	var text = '#请在这里输入自定义话题#';
	var   patt   =   new   RegExp(text,"g");  
	var content_publish = $('#content_publish');
	var result;
				
	if( content_publish.val().search(patt) == '-1' ){
		content_publish.val( content_publish.val() + text);
	}
	
	var textArea = document.getElementById('content_publish');
	
	result = patt.exec( content_publish.val() );
	
	var end = patt.lastIndex-1 ;
	var start = patt.lastIndex - text.length +1;
	
	if (document.selection) { //IE
		 var rng = textArea.createTextRange();
		 rng.collapse(true);
		 rng.moveEnd("character",end)
		 rng.moveStart("character",start)
		 rng.select();
	}else if (textArea.selectionStart || (textArea.selectionStart == '0')) { // Mozilla/Netscape…
        textArea.selectionStart = start;
        textArea.selectionEnd = end;
    }
    textArea.focus();
	weibo.checkInputLength('#content_publish',140);
	return ;
}
