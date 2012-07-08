<?php
class WeiboListWidget extends Widget
{
	public function render($data)
	{
		$data['type'] = $data['type'] ? $data['type'] : 'normal';
		$data['insert'] = 1 == $data['insert'] ? $data['insert'] : 0;
		switch($data['simple']){
		    case 0:
		        $template = "WeiboList.html";
		        break;
		    case 1:
		        $template = "WeiboNoLinkList.html";
		        break;
		    case 2:
		        $template = "GroupWeiboList.html";
		        break;
		}
		$content = $this->renderFile(ADDON_PATH . '/widgets/'.$template, $data);
		return $content;
	}
}