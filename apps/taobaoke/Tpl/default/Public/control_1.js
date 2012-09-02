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
        if(goods_url=="")
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
        function GetpaiID(url)
        {
            var string=url;
            var startIndex=string.lastIndexOf("/")+1;   
            var SubString=string.substr(startIndex,32);
            return SubString;
        }
        var re=/(^|:\/\/)(\w+\.)?(\w+\.\w+)(?=(\/|$))/;
        var m=goods_url.match(re);
        
        if(m){
            if(m[3]=="taobao.com" || m[3]=="tmall.com" || m[3]=="paipai.com"){
                var gid=GetUrlParam(goods_url,"id");
                if(m[3]=="paipai.com"){
                    gid=GetpaiID(goods_url);
                }
                $.post( U('weibo/plugins/before_publish'),{
                    url:goods_url,
                    plugin_id:5,
                    id: gid,
                    fenlei: m[3]
                },function(txt){
                    txt = eval('('+txt+')');
                    if(txt.taoke==1){

                        $('#goods_input').hide();
                        $("#taobao").show();
                        $('#taoke').show();
                        $('#taobao_goods_images').show();
                        $("#shop").hide('slow');
                        $("#post").show('slow');
                        $('#goods_img').attr("src",txt.data.pic_url+"_80x80.jpg");
                        $('#click_url').attr("href",txt.data.click_url);
                        $('#click_url_img').attr("href",txt.data.click_url);
                        $('#click_url').text(txt.data.title);
                        $('#click_url').attr("title",txt.data.title);
                                               
                        $('#price').text(txt.data.price);
                        $('.commission').text(txt.data.commission);
                        $('.commission_rate').text(txt.data.commission_rate+"%");
                        html = "<input name='publish_type_data' type='hidden' style='width:86%' value="+txt.data.pic_url+" />";					
                        goods_url="<input name='goods_url' type='hidden' value="+txt.data.click_url+" />";
                        g_url="<input name='g_url' type='hidden' value="+txt.rand_url+" />";
                                                
                        g_title="<input name='g_title' type='hidden' value="+escape(txt.data.title)+" />";
                        g_host="<input type='text' name='g_host' value="+txt.host+" />";
                        price="<input name='price' type='hidden' value="+txt.data.price+" />";
                        commission="<input name='commission' type='hidden' value="+txt.data.commission+" />";
                        commission_rate="<input name='commission_rate' type='hidden' value="+txt.data.commission_rate+" />";
                        taoke="<input name='taoke' type='hidden' value="+txt.taoke+" />";
                        $("#publish_type_content").html( html+goods_url+g_url+g_title+price+commission+commission_rate+taoke+g_host );
                        $(".talkPop").hide();
                        if(!$("#pickpic").length){
                            $("<input type='hidden' name='pickpic' id='pickpic' value='"+txt.data.pic_url+"' />").appendTo($("#publish_type_content")[0]);
                        }
                                               
                        weibo.textareaStatus('on');
                    }else if(txt.taoke==0){
                                        
                        $("#taobao_goods_images").show();
                        $('#goods_input').hide();
                        $("#taobao").show();
                        $('#goods_img').attr("src",txt.data.pic_url);
                        $('#click_url').attr("href",txt.data.click_url);
                        $('#click_url_img').attr("href",txt.data.click_url);
                        $('#click_url').text(txt.data.title);
                        $('#click_url').attr("title",txt.data.title);
                        $('#price').text(txt.data.price);
                        $('#taoke').hide();
                        g_host="<input type='text' name='g_host' value="+txt.host+" />";
                        html  = "<input name='publish_type_data' type='hidden' style='width:86%' value="+txt.data.pic_url+" />";
                        goods_url="<input name='goods_url' type='hidden' value="+txt.data.click_url+" />";
                        g_url="<input name='g_url' type='hidden' value="+txt.rand_url+" />";
                        g_title="<input name='g_title' type='hidden' value="+escape(txt.data.title)+" />";
                        price="<input name='price' type='hidden' value="+txt.data.price+" />";
                        taoke="<input name='taoke' type='hidden' value="+txt.taoke+" />";
                        $("#publish_type_content").html( html+goods_url+g_url+g_title+price+taoke+g_host );	
                        if(!$("#pickpic").length){
                            $("<input type='hidden' name='pickpic' id='pickpic' value='"+txt.data.pic_url+"' />").appendTo($("#publish_type_content")[0]);
                        }
                        $(".talkPop").hide();
                                              
                        weibo.textareaStatus('on');
                    }
                                        
                                        
                //alert();
                })
            //alert(GetUrlParam(url,"id"));
            }else{
                alert("请正确输入网址");
            }
        }else{
            alert("请正确输入网址");
        }
        
    }
});

