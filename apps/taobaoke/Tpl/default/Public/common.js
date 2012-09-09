	
//----------------------------仿知美二次开发-----------------------//
$.extend(window.ui  || {},{
    loveok:function(o,text,count,weibo_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/love"),{id:weibo_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("fav_"+weibo_id).textContent;
					}else{
				     var count =document.getElementById("fav_"+weibo_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("favloe_"+weibo_id).innerHTML="<img src="+ _THEME_+"/images/liked2.png class=fl><span class=favCount id=fav_{$vo.weibo_id}>"+newcount+"</span><i></i>";
				
				
				document.getElementById("favloe_"+weibo_id).className='loveit';
				weibo.upfavCount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'"><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newfav_'+weibo_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_loveok" class="ts_confirm">是否确定删除此喜欢<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_loveok').css({"top":position.top+"px","left":position.left-($("#ts_ui_loveok").width()/2)+"px","display":"none"});
			  
		       $("#ts_ui_loveok .txt").html(text);
	          $('#ts_ui_loveok').show("fast");
			 
			   $("#ts_ui_loveok .btn_w").one('click',function(){
			$('#ts_ui_loveok').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_loveok').remove();
		  });
			   $("#ts_ui_loveok .btn_b").one('click',function(){
			$('#ts_ui_loveok').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_loveok').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},
		
	loveok2:function(o,text,count,weibo_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/love"),{id:weibo_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("fav_"+weibo_id).textContent;
					}else{
				     var count =document.getElementById("fav_"+weibo_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("favloe_"+weibo_id).innerHTML="取消";
				
				
				document.getElementById("favloe_"+weibo_id).className='hb unfavourite-btn';
				weibo.upfavCount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'"><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newfav_'+weibo_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_loveok" class="ts_confirm">是否确定删除此喜欢<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_loveok').css({"top":position.top+"px","left":position.left-($("#ts_ui_loveok").width()/2)+"px","display":"none"});
			  
		       $("#ts_ui_loveok .txt").html(text);
	          $('#ts_ui_loveok').show("fast");
			 
			   $("#ts_ui_loveok .btn_w").one('click',function(){
			$('#ts_ui_loveok').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_loveok').remove();
		  });
			   $("#ts_ui_loveok .btn_b").one('click',function(){
			$('#ts_ui_loveok').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_loveok').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},	
		
	
	tuijianok:function(o,text,count,weibo_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/tuijian"),{id55:weibo_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("tuijian_"+weibo_id).textContent;
					}else{
				     var count =document.getElementById("tuijian_"+weibo_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("tuijianloe_"+weibo_id).innerHTML="已推荐("+newcount+")";
				
				
				document.getElementById("tuijianloe_"+weibo_id).className='loveit';
				weibo.uprjcount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'" ><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newtuijian_'+weibo_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_tuijian" class="ts_confirm">你已推荐过,是否删除推荐<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_tuijian').css({"top":position.top+"px","left":position.left-($("#ts_ui_tuijian").width()/2)+"px","display":"none"});
			  
		       $("#ts_ui_tuijian .txt").html(text);
	          $('#ts_ui_tuijian').show("fast");
			 
			   $("#ts_ui_tuijian .btn_w").one('click',function(){
			$('#ts_ui_tuijian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_tuijian').remove();
		  });
			   $("#ts_ui_tuijian .btn_b").one('click',function(){
			$('#ts_ui_tuijian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_tuijian').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},
	
	//推荐达人
	darenok:function(o,text,count,weibo_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/daren"),{id:weibo_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("daren_"+weibo_id).textContent;
					}else{
				     var count =document.getElementById("daren_"+weibo_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("darenloe_"+weibo_id).innerHTML="已推荐("+newcount+")";
				
				
				document.getElementById("darenloe_"+weibo_id).className='loveit';
				weibo.updrcount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'" ><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newdaren_'+weibo_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_daren" class="ts_confirm">你已推荐过,是否删除推荐<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_daren').css({"top":position.top+"px","left":position.left-($("#ts_ui_daren").width()/2)+"px","display":"none"});
			  
		       $("#ts_ui_daren .txt").html(text);
	          $('#ts_ui_daren').show("fast");
			 
			   $("#ts_ui_daren .btn_w").one('click',function(){
			$('#ts_ui_daren').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_daren').remove();
		  });
			   $("#ts_ui_daren .btn_b").one('click',function(){
			$('#ts_ui_daren').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_daren').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},
	
	fengmianok:function(o,text,count,bc_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/fengmian"),{id:bc_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("fengmian_"+bc_id).textContent;
					}else{
				     var count =document.getElementById("fengmian_"+bc_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("fengmianloe_"+bc_id).innerHTML="已关注("+newcount+")";
				
				
				document.getElementById("fengmianloe_"+bc_id).className='longbutton unconcern';
				weibo.upfmcount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'" ><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newfengmian_'+bc_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_fengmian" class="ts_confirm">是否取消关注？<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_fengmian').css({"top":position.top+"px","left":position.left+"px","display":"none"});
			  
		       $("#ts_ui_fengmian .txt").html(text);
	          $('#ts_ui_fengmian').show("fast");
			 
			   $("#ts_ui_fengmian .btn_w").one('click',function(){
			$('#ts_ui_fengmian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_fengmian').remove();
		  });
			   $("#ts_ui_fengmian .btn_b").one('click',function(){
			$('#ts_ui_fengmian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_fengmian').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},
		
		//专辑详细页推荐
		tuijiantuge:function(o,text,count,bc_id,mid,fid,img){
	    var callback = $(o).attr('callback');
		$.post(U("taobaoke/Operate/fengmian"),{id:bc_id},function(res){
			if( res<1){
				//火狐不支持outerText
				if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){
					var count =document.getElementById("fengmian_"+bc_id).textContent;
					}else{
				     var count =document.getElementById("fengmian_"+bc_id).outerText;
					}
				var newcount=count=+count+1;
			    document.getElementById("fengmianloe_"+bc_id).innerHTML="已关注("+newcount+")";
				
				
				document.getElementById("fengmianloe_"+bc_id).className='winbtn unaddin';
				weibo.upfmcount('weibo');
				
				this.htmls='<a href="'+U('home/Space/index',['uid='+mid])+'" ><img src="'+img+'" width="24" height="24" style="float:right"/></a>  ';
				$('#newfengmian_'+bc_id).html(this.htmls);
			}else{
				this.html = '<div id="ts_ui_fengmian" class="ts_confirm">是否取消关注？<br><input type="button" value="确定"  class="btn_b mr5"><input type="button" value="取消"  class="btn_w"></div>';
				$('body').append(this.html);
				var position = $(o).offset();
		       $('#ts_ui_fengmian').css({"top":position.top+"px","left":position.left-($("#ts_ui_fengmian").width()/2)+"px","display":"none"});
			  
		       $("#ts_ui_fengmian .txt").html(text);
	          $('#ts_ui_fengmian').show("fast");
			 
			   $("#ts_ui_fengmian .btn_w").one('click',function(){
			$('#ts_ui_fengmian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_fengmian').remove();
		  });
			   $("#ts_ui_fengmian .btn_b").one('click',function(){
			$('#ts_ui_fengmian').fadeOut("fast");
			// 修改原因: ts_loveok .btn_b按钮会重复提交
			$('#ts_ui_fengmian').remove();
			eval(callback);
		});
				}
				
				
			});	 
		},
		
	//----------------------------仿知美二次开发  end ---------------------//	
});
