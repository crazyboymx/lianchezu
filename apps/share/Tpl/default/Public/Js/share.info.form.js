share_info = function(){};
share_info.prototype = {
	check_form:function(v_form)
	{
		if (getLength(v_form.name.value) == 0) {
			ui.error("专辑名称不能为空");
			v_form.name.focus();
			return false;
		} else if (getLength(v_form.name.value) > 20) {
			ui.error("专辑名称不能超过20个字");
			v_form.name.focus();
			return false;
		} else if (getLength(v_form.intro.value) == 0) {
			ui.error("专辑介绍不能为空");
			v_form.name.focus();
			return false;

		} else if (v_form.cid0.value <= 0) {
			ui.error("请选择专辑分类");
			v_form.cid0.focus();
			return false;
		} else if (getLength(v_form.intro.value) > 100) {
			ui.error("专辑简介不能超过100个字");
			v_form.intro.focus();
			return false;
		}
		return true;
	}
};
share_info = new share_info();