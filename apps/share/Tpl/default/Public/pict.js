
function like(id,type){
        var id = id ;
		var type = type ;
		id = id.toString();
        if(id=='' || id==0) return ;
        $.post(U('share/Index/like'),{id:id,type:type},function(text ){
              if( text == 1 ){
                  ui.success( "收藏成功" );
              }else if(text == 2){
			      ui.success( "您已收藏过此专辑" );
		      }else{
                  ui.error( "收藏失败" );
              }
          });
    }
	
function edit(id,act){
}

function show_hide(id){
	
    var target=document.getElementById('comment'+id);
	target.style.display=="block"? target.style.display="none":target.style.display="block";
    }



function deletePict(id){
	if(confirm('确定要删除该专辑单张吗？')){
		  $.post(U('share/Index/doDeletePict'),{id:id},function(result){
				switch(result){
					case "0":
						ui.error('删除失败');
						break;
					case "1":
					    ui.success( "删除成功" );
						location.href=U('share/index/Index');
						break;
					case "-1":
						ui.error("没有权限删除这条信息");
						break;
					case "-2":
						ui.error("网络故障，此信息已被删除");
						break;
					case "-3":
						ui.error("参数错误。请刷新页面再试");
						location.reload();
						break;
				}
		   });
	}
}

function photo_size(name){
    $(name +" img").each(function(){

        var width = 500;
        var height = 500;
        var image = $(this);
        image.addClass('hand');
        image.bind('click',function(){
            window.open(image.attr('src'),"图片显示",'width='+image.width()+',height='+image.height());
        });
        if (image.width() > image.height()){
            if(image.width()>width){
                image.width(width);
                image.height(width/image.width()*image.height());
            }
        }
        else{
            if(image.height()>height){
                image.height(height);
                image.width(height/image.height()*image.width());
            }
        }


    });
}
$(function(){
	photo_size("#pict");
});

function check(type){
	if($('#title').val() == ""){
		ui.error('标题没有填写');
		return false;
	}
	if($('#title').val().length >30) {
		ui.error('标题字数不能超过30个字符');
		return false;
	}
	return true;
}

