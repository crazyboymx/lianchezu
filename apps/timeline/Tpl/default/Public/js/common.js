
$(function(){
    resize();
});

function resize(){
	$('.mainbox').masonry({
      itemSelector: '.box',
      columnWidth: 490,
      isFitWidth: true
    });
	
	$(".box").each(function(){
		var y=0;
		y = $(this).position().left;
		if(y<50){ 
		         $(this).children("li").css({"float":"right"});
		         $(this).children(".ti_bg").addClass("ltitle"); 
		}else{ $(this).children(".ti_bg").addClass("rtitle"); }
	});

}

function addblog(){
	ui.box.load(U('blog/Index/addAjaxBlog'),{title:'写日志'});
}

function eblog(id,mid){
	var id = id ;
	ui.box.load(U('blog/Index/eblog')+'&id='+id+'&mid='+mid,{title:'编辑日志'});
}

function deleteBlog( id ){
	if(confirm("是否确定删除这条日志")) {
		$.post(U('blog/Index/doDelBlog'),{id:id},function(data){
			if(data==1){
				$('#'+'r'+id+'bl').remove();
				ui.success('删除成功！');
                resize();
			}else{
				ui.error('删除失败！');
			}
		});
	}
}



//删除单张图片
function delphoto(id){
	var id = id ;
	if(confirm('你确定要删除这张图片么？')){
		$.post(U('photo/Manage/delete_photo'),{id:id},function(data){
			if(data==1){
				$('#'+'r'+id+'tu').remove();
				ui.success('删除成功！');
                resize();
			}else{
				ui.error('删除失败！');
			}
		});
	}
}


//编辑图片
function editphotoTab(id){
	var id = id ;
	ui.box.load(U('timeline/Index/edit_photo_tab')+'&id='+id,{title:'编辑图片'});
}

function do_update_photo(){
	var id		=	$('#photoId').val();
	var name	=	$('#name').val();
	if(!name)	{ 
		ui.error('图片名字不能为空！');
	}
	$.post(U('timeline/Index/do_update_photo'),{id:id,name:name},function(data){
	    if(data==1){
			    ui.box.close();
				ui.success('修改成功！');
				$('#'+id+'tu').text(name);
		}else{
			ui.box.close();
			ui.error('修改失败或图片信息无变化！');
		}
	});
}

function chosign(id){
	    var id=id;
    	$.post(U('timeline/Index/chosign'),{id:id},function(data){
	    if(data==1){
				ui.success('更换成功！');
				$("#picsign").attr("src",$("#"+id+'pic').attr("src"));
		}else{
			ui.error('更换失败！');
		}
	});

}
