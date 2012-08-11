jQuery.extend(weibo.plugin, {
	tao:function(element, options){
	   
	    
	}
});


jQuery.extend(weibo.plugin.tao, {
	html:'<dl id="music_input" class="layer_send_music"><dt>请输入淘宝商品地址：</dt><dd><input name="publish_type_data" type="hidden" /><input name="tao_url" type="text" style="width:280px" class="text  mr5"  value="" id="tao_url" /><input type="button" class="btn_b" onclick="weibo.plugin.tao.searchshop()" value="搜索"></dd></dl><div style="display:none; padding:10px 10px 20px" id="music_add_complete">您将要分享的音乐：</div><div class="xiami_s_r"></div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options)
	},
	
	searchshop:function(){
		url = $("input[name='tao_url']").val();
			var spid=url.match(/[\?&]id=(.*?)&/);
		 if(!spid){
			 	spid=url.match(/[\?&]id=(.*)/);
			 }
			 if(!spid){
				alert('您填写的地址不正确哦亲');
			}else{
			$.post( U('home/widget/addonsRequest'),{addon:'Tao',hook:'searchshop',url:spid[1]},function(txt){
						txt = eval('('+txt+')');
						if(txt.s =='0'){alert(txt.error)}else{
								$(".xiami_s_r").html(txt.html);
								$("#content_publish").val('#商品分享# '+txt.t+'  价格：'+txt.p+'  '+txt.url);
								$("#publish_type_data").val(txt.s);
								weibo.publish_type_val('23');
								weibo.checkInputLength(_LENGTH_);
						}
					})
			
			}
		}
});
