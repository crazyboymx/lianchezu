function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}

$(document).ready(function() {
    $('.listt').masonry({
        itemSelector:'.listt li',
        columnWidth:245
    });
});
