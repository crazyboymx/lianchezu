taobaoke = window.taobaoke || {};
$(function(){
    //转发
    $("#container").delegate(".item .forward-btn","click",function(){
        var $this= $(this),itemId = $this.closest("[id^=item_]").attr("id").replace(/^item_/i,"");
        ui.box.load(U('taobaoke/Operate/transpond')+"&id="+itemId,{title:'转发'});
        $(">.boxy-modal-blackout",document.body).css({zIndex:"98"});
        $("#tsbox").css({zIndex:"99"});
    });
   
    //创建新图格 
    window.setBcTab = function (){
        ui.box.load(U('taobaoke/Bc/setBcTab'),{title:'新图格'});
        $(">.boxy-modal-blackout",document.body).css({zIndex:"98"});
        $("#tsbox").css({zIndex:"99"});
    }
    taobaoke.quickpublish =  function (text){
        $.post(U('taobaoke/operate/quickpublish'),{text:text},function(txt){
            ui.box.show(txt,{title:'添加分享',closeable:true});
        }); 
    }
})
