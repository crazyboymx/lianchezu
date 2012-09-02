jQuery.extend(weibo.plugin, {
    goods:function(element, options){
	   
	    
    }
});


jQuery.extend(weibo.plugin.goods, {
    html:'<div id="goods_input">请输入商品地址： <div><input name="publish_type_data" type="text" style="width: 235px" class="text mr5" value="" /><input type="button" class="btn_b" onclick="weibo.plugin.goods.add_goods()" value="添加"></div> </div>  ',
    click:function(options){
        weibo.publish_type_box(5,this.html,options)
    },
    add_goods:function(){
        var goods_url = $("input[name='publish_type_data']").val();
		$('#goods_input').html('正在分享宝贝,请稍后....');
		$('#content_publish').append('#分享宝贝#');
        if(goods_url=="")
        {
            alert("请正确输入网址");
            return false;
        }
        
        var yz=yz_url(goods_url);
        if(!yz)
        {
            alert("请正确输入网址");
            return false;
        }
        function GetUrlParam(url, paramName )
        {
            var oRegex = new RegExp( '[\?&]' + paramName + '=([^&]+)', 'i' ) ;
            var oMatch = oRegex.exec( url ) ;
            if ( oMatch && oMatch.length > 1 )
                return oMatch[1] ;  //返回值
            else
                return '' ;
        }
        
            
        gid=GetUrlParam(goods_url,"id");
        if(yz=="paipai.com"){
            gid=GetpaiID(goods_url);
        }
            
        if(gid==""){
            alert("请正确输入网址");
            return false;
        }
        $.post( U('weibo/plugins/before_publish'),{
            url:goods_url,
            plugin_id:5,
            id: gid,
            fenlei: yz
        },function(txt){
            txt = eval('('+txt+')');
            if(txt.taoke==1){
                ///前处理
                $('#goods_input').hide();
                $("#taobao").show();
                $('#taoke').show();
                $("#shop").hide('slow');
                $("#post").show('slow');
                $('#goods_img').attr("src",txt.data.pic_url+"_160x160.jpg");
                $('#click_url').attr("href",txt.data.click_url);
                $('#click_url_img').attr("href",txt.data.click_url);
                $('#click_url').text(txt.data.title);
                $('#click_url').attr("title",txt.data.title);
                $('#price').text(txt.data.price);
                $('.commission').text(txt.data.commission);
                $('.commission_rate').text(txt.data.commission_rate+"%");
                //后负值处理
                html1 = "<input name='publish_type_data' type='hidden' style='width:86%' value='"+txt.data.pic_url+"' />";					
                goods_url="<input name='goods_url' type='hidden' value='"+goods_url+"' />";
                g_url="<input name='g_url' type='hidden' value='"+txt.rand_url+"' />";
                cid="<input name='cid' type='hidden' value='"+txt.data.cid+"' />"; 
                seller_cids="<input name='seller_cids' type='hidden' value='"+txt.data.seller_cids+"' />"; 
                num_iid="<input name='num_iid' type='hidden' value='"+txt.data.num_iid+"' />";
                g_title="<input name='g_title' type='hidden' value='"+escape(txt.data.title)+"' />";
                g_host="<input type='hidden' name='g_host' value='"+txt.host+"' />";
               price_d="<input name='price' type='hidden' value='"+txt.data.price+"' />";
                commission="<input name='commission' type='hidden' value='"+txt.data.commission+"' />";
                commission_rate="<input name='commission_rate' type='hidden' value='"+txt.data.commission_rate+"' />";
                taoke_1="<input name='taoke' type='hidden' value='"+txt.taoke+"' />";
                $("#publish_type_content").html( html1+goods_url+g_url+g_title+price_d+commission+commission_rate+taoke_1+g_host+cid+seller_cids +num_iid);
                $(".talkPop").hide();
                                               
                weibo.textareaStatus('on');
            }else if(txt.taoke==0){
                                        
                $('#goods_input').hide();
                $("#taobao").show();
                if(txt.host=="taobao"){
                    $('#goods_img').attr("src",txt.data.pic_url+"_160x160.jpg");
                }else{
                    len=txt.data.pic_url.length;
                    pic=txt.data.pic_url.substring(0,len-3);
                    $('#goods_img').attr("src",pic+"160x160.jpg");
                }
                
                $('#click_url').attr("href",txt.data.click_url);
                $('#click_url_img').attr("href",txt.data.click_url);
                $('#click_url').text(txt.data.title);
                $('#click_url').attr("title",txt.data.title);
                $('#price').text(txt.data.price);
                $('#taoke').hide();
                g_host="<input type='text' name='g_host' value="+txt.host+" />";
                html  = "<input name='publish_type_data' type='hidden' style='width:86%' value="+txt.data.pic_url+" />";
                goods_url="<input name='goods_url' type='hidden' value="+txt.data.click_url+" />";
                cid="<input name='cid' type='hidden' value="+txt.data.cid+" />"; 
				num_iid="<input name='num_iid' type='hidden' value="+txt.data.num_iid+" />";
                seller_cids="<input name='seller_cids' type='hidden' value="+txt.data.seller_cids+" />"; 
                g_url="<input name='g_url' type='hidden' value="+txt.rand_url+" />";
                g_title="<input name='g_title' type='hidden' value="+escape(txt.data.title)+" />";
                price_d="<input name='price' type='hidden' value="+txt.data.price+" />";
                taoke_1="<input name='taoke' type='hidden' value="+txt.taoke+" />";
                $("#publish_type_content").html( html+goods_url+g_url+g_title+price_d+taoke_1+g_host+cid+seller_cids +num_iid);	
                $(".talkPop").hide();
                                              
                weibo.textareaStatus('on');
            }
                                        
                                        
           
        })
        
        
        
        
    }
});


//获得拍拍商品ID值
function GetpaiID(url)
{
    var string=url;
    var startIndex=string.lastIndexOf("/")+1;   
    var SubString=string.substr(startIndex,32);
    return SubString;
}
//验证URL
function yz_url(url)
{
    var re=/(^|:\/\/)(\w+\.)?(\w+\.\w+)(?=(\/|$))/;
    var m=url.match(re);
    if(m)
    {
        if(m[3]=="taobao.com" || m[3]=="tmall.com" || m[3]=="paipai.com"){
            return m[3];
        }else{
            return false;
        }
    }else{
        return false;
    }
}