<?php
class WidgetAction extends Action
{
	private $__type_website = '0';

	public function renderWidget()
	{
		//非登录下widget调用过滤
		if(!$this->mid){
			$access_widget = array();
			if(!in_array($_REQUEST['name'],$access_widget))exit;
		}
		$_REQUEST['name']  = t($_REQUEST['name']);
		$_REQUEST['param'] = unserialize(urldecode($_REQUEST['param']));
		echo empty($_REQUEST['name']) ? 'Invalid Param.' : W($_REQUEST['name'], $_REQUEST['param']);
	}

	// 插件的请求转发
	public function addonsRequest()
	{
		Addons::addonsHook(t($_REQUEST['addon']),t($_REQUEST['hook']));
	}

	// 关注“可能感兴趣的人”
	public function doFollowRelatedUser()
	{
		$_POST['uid'] = intval($_POST['uid']);

		if ($_POST['uid'] <= 0) {
			echo 0;
		} else {
			D('Follow', 'weibo')->dofollow($this->mid, $_POST['uid']);
			$related_user = unserialize($_SESSION['_widget_related_user']);
			if (empty($related_user)) {
				echo '';
			} else {
				$shifted_user = array_shift($related_user);
				$_SESSION['_widget_related_user'] = serialize($related_user);

				$html = '';
	            $html .= '<li id="related_user_' . $shifted_user['uid'] . '">';
	            $html .= 	'<div class="userPic">';
	            $html .= 		'<a title="" href="'.U("home/Space/index",array("uid"=>$shifted_user['uid'])).'">';
	            $html .= 			'<img src="'.getUserFace($shifted_user['uid'],'m').'" card="1">';
	            $html .=		'</a>';
	            $html .= 	'</div>';
	            $html .=	'<div class="interest_info">';
            	$html .= 		'<p><a href="'.U("home/Space/index",array("uid"=>$shifted_user['uid'])).'">'.getUserName($shifted_user['uid']).'</a></p>';
	            $html .= 		'<p><a href="javascript:void(0);" class="guanzhu" onclick="subscribe('.$shifted_user['uid'].')">加关注</a></p>';
	            $html .=		'<p class="cGray2">'.$shifted_user['reason'].'</p>';
	            $html .= 	'</div>';
	            $html .= '</li>';
	            echo $html;
			}
		}
	}

	//换一换可能感兴趣的人
	public function replaceRelatedUser()
	{
		$related_user = unserialize($_SESSION['_widget_related_user']);
		if (empty($related_user)) {
			echo '0';
		} else {
			$html  = '';
			$limit = min(intval($_POST['limit']), count($related_user));

			for ($i = 1; $i <= $limit; $i++) {
				$shifted_user = array_shift($related_user);
		        $html .= '<li id="related_user_' . $shifted_user['uid'] . '">';
		        $html .= 	'<div class="userPic">';
		        $html .= 		'<a title="" href="'.U("home/Space/index",array("uid"=>$shifted_user['uid'])).'">';
		        $html .= 			'<img src="'.getUserFace($shifted_user['uid'],'m').'" card="1">';
		        $html .=		'</a>';
		        $html .= 	'</div>';
		        $html .=	'<div class="interest_info">';
	            $html .= 		'<p><a class="fn" target="_self" rel="face" uid="'.$shifted_user['uid'].'" href="'.U("home/Space/index",array("uid"=>$shifted_user['uid'])).'">'.getUserName($shifted_user['uid']).'</a></p>';
		        $html .= 		'<p><a href="javascript:void(0);" class="guanzhu" onclick="subscribe('.$shifted_user['uid'].')">加关注</a></p>';
		        $html .=		'<p class="cGray2">'.$shifted_user['reason'].'</p>';
		        $html .= 	'</div>';
		        $html .= '</li>';
			}

			$_SESSION['_widget_related_user'] = serialize($related_user);
	        echo $html;
		}
	}

	// 发微博
	public function weibo() {
		// 解析参数
		$_REQUEST['param']	= unserialize(urldecode($_REQUEST['param']));
		$active_field	= $_REQUEST['param']['active_field'] == 'title' ? 'title' : 'body';
		$this->assign('has_status', $_REQUEST['param']['has_status']);
		$this->assign('is_success_status', $_REQUEST['param']['is_success_status']);
		$this->assign('status_title', t($_REQUEST['param']['status_title']));

		// 解析模板(统一使用模板的body字段)
		$_REQUEST['data']	= unserialize(urldecode($_REQUEST['data']));
		$content		= model('Template')->parseTemplate(t($_REQUEST['tpl_name']), array($active_field=>$_REQUEST['data']));
		//$content		= preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)/u','getContentUrl', $content);
		$this->assign('content', text($content[$active_field]));
		$this->assign('source',$_REQUEST['data']['source']);
		$this->assign('sourceUrl',$_REQUEST['data']['url']);
		$this->assign('type',$_REQUEST['data']['type']);
		$this->assign('type_data',$_REQUEST['data']['type_data']);
		$this->assign('button_title', t(urldecode($_REQUEST['button_title'])));
		$this->assign('addon_info',urldecode($_REQUEST['addon_info']));
		$this->display();
	}

	/**
	 * 站外资源分享到微博
	 *
	 * 须提供以下$_GET参数:
	 * <code>
	 * url:         站外资源的URL地址 (需经过urlencode)
	 * title:       站外资源的标题    (需经过urlencode)
	 * sourceTitle: 来源站点名称	   (需经过urlencode)
	 * sourceUrl:   来源站点的URL地址 (需经过urlencode)
	 * picUrl:      附带图片的URL地址 (需经过urlencode)
	 * </code>
	 */
	public function share()
	{
		$data['content']	= urldecode($_GET['title']) . ' ' . getShortUrl(urldecode($_GET['url']));
		$data['source']		= urldecode($_GET['sourceTitle']);
		$data['sourceUrl']	= urldecode($_GET['sourceUrl']);

		// 获取远程图片 => 生成临时图片
		if ($pic_url = urldecode($_GET['picUrl'])) {
            $imageInfo = getimagesize($pic_url);
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            if ('bmp' != $imageType) { // 禁止BMP格式的图片
	        	$save_path = SITE_PATH . '/data/uploads/temp'; // 临时图片地址
	    		$filename  = md5($pic_url) . '.' . $imageType; // 重复刷新时, 生成的文件名应一致
			    $img       = file_get_contents($pic_url);
			    $filepath  = $save_path.'/'.$filename;
			    $result    = file_put_contents($filepath, $img);
			    if ($result) {
					$data['type']	   = 1;
					$data['type_data'] = 'temp/' . $filename;
			    }
            }
		}

		$this->assign($data);
		$this->display();
	}

	public function doShare()
	{
		$data['content'] = $_POST['content'];
		$data['type'] = $type	= intval($_POST['type']);
		$data['type_data'] = $type_data = $_POST['type_data'];

		// 来自"来源站点"
		if ($_POST['source'] && $_POST['sourceUrl']) {
        	$from_data = serialize(array('source' => $_POST['source'], 'url' => $_POST['sourceUrl']));
		}

        $id = D('Weibo','weibo')->publish($this->mid, $data, $this->__type_website, $type, $type_data, '', $from_data);

        // 移除临时生成的图片文件
        if (strpos($type_data, 'temp/')) {
        	unlink(SITE_PATH . '/data/uploads/' . $type_data);
        }

        if ($id) {
        	X('Credit')->setUserCredit($this->mid,'share_to_weibo');
        	echo '1';
        } else {
        	echo '0';
        }
	}

	/**
	 * 举报
	 */
	public function denounce(){
		$post['from'] = $_POST['from'];
		$post['aid'] = $_POST['aid'];
		$post['uid'] = $_POST['uid'];
		$post['fuid'] = $_POST['fuid'];
		$post['content'] = $_POST['content'];
		$this->assign($post);
		$this->display();
	}

	public function doDenounce(){
		//查看是否已经有此条数据
		$map['from'] = trim( $_POST['from'] );
		$map['aid'] = intval( $_POST['aid'] );
		$map['uid'] = intval( $_POST['uid'] );
		$map['fuid'] = intval( $_POST['fuid'] );
		if( $isDenounce = M( 'Denounce' )->where( $map )->count() ){
			echo 'ui.error("您已经举报过此条信息。");';
		}else{
			$map['content'] = trim( $_POST['content'] );
			$map['reason'] = trim( $_POST['reason'] );
			$map['ctime'] = time();
			if( $id = M( 'Denounce' )->add( $map ) ){
				echo 'ui.success(L("report_success"));';
			}else{
				echo 'ui.error(L("report_failed"));';
			}
		}
	}

	/*
	 * 微博秀
	 */
	public function weiboShow()
	{
    	// 微博秀样式
    	$data['style']['width']  = $_GET['width'] < 190 ? 190 : ($_GET['width'] > 1024 ? 1024 : intval($_GET['width']));
    	$data['style']['height'] = $_GET['height'] < 75 ? 75 : ($_GET['height'] > 800 ? 800 : intval($_GET['height']));
    	$data['style']['skin']   = t($_GET['skin']);

		// 用户基本信息
		$data['user'] = D('User', 'home')->getUserByIdentifier($this->uid);
		// 微博列表
		$user_data['user_id'] = $this->uid;
		$data['weibolist'] = api('Statuses')->data($user_data)->user_timeline();
    	// 粉丝列表
    	$data['follower'] = D('Follow','weibo')->getList($this->uid, 'follower', 0, null, 20);

    	$data['uid'] = $this->uid;

    	$this->assign($data);
		$this->display();
	}
}
