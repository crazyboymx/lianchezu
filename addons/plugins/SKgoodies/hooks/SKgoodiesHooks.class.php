<?php
/**
 *      ThinkSNS SK书签栏采集小工具 插件 v1.0 (C)2012-2099 啊.Qin.
 *      This is NOT a freeware, use is subject to license terms
 *		任何人修改代码需要告诉作者，并开源。不得商业出售！
 *      author  啊.Qin <172376799#qq.com>
 *      $Id: SKgoodiesHooks.class.php 17 2012-04-29 08:44:24Z 阿Qin $
 */
 
class SKgoodiesHooks extends Hooks {


    public function init() {

    }

	
    public function home_account_tab($param) {
        $param['menu'][] = array(
            'act' => 'skgoodies',
            'name' => '采集小工具',
            'param' => array(
                'addon' => 'SKgoodies',
                'hook' => 'home_account_show'
            )
        );
    }

    public function home_account_show() {
        if ('skgoodies' != ACTION_NAME) {
            return;
        }
		// 模板中变量和JS混合解析问题 从模板中移植到这里
		$bookmarkleturl = "javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','".SITE_URL."/index.php?app=home&mod=Widget&act=addonsRequest&addon=SKgoodies&hook=bookmarklet&r='+Math.random()*99999999);document.body.appendChild(e)})());";
		$this->assign('bookmarkleturl', $bookmarkleturl);
        $this->display('SKgoodies');
    }

	/**
	 * 站外资源分享到微博
	 *
	 * 须提供以下$_GET参数:
	 * <code>
	 * url:         站外资源的URL地址 (需经过urlencode)
	 * alt：			媒体描述 (需经过urlencode)
	 * title:       站外资源的标题    (需经过urlencode)
	 * sourceTitle: 来源站点名称	   (需经过urlencode)
	 * sourceUrl:   来源站点的URL地址 (需经过urlencode)
	 * picUrl:      附带图片的URL地址 (需经过urlencode)
	 * </code>
	 */
	public function SKshare()
	{
		$data['content']	= urldecode($_GET['title']) . ' ' . getShortUrl(urldecode($_GET['url']));
		$data['alt']		= urldecode($_GET['alt']);
		$data['source']		= urldecode($_GET['sourceTitle']);
		$data['sourceUrl']	= urldecode($_GET['sourceUrl']);
		$pic_url = urldecode($_GET['pic_url']);

		// 获取远程图片 => 生成临时图片
		if ($pic_url && !$_GET['v']) {
			
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
            } else {
				$this->error('图片处理失败');
			}
		}else if($_GET['v']) {//视频处理
	
			$link = urldecode($_GET['url']);
			$parseLink = parse_url($link);
			if(preg_match("/(youku.com|youtube.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com)$/i", $parseLink['host'], $hosts)) {
				$data['type']   = 3;
				$data['type_data'] = $link;
			}else{
				$this->error('视频信息处理失败');
			}

		}

		$this->assign($data);
		$this->display('share');
	}
	
	//发布微博
	public function doShare()
	{
		$data['content'] = $_POST['content'];
		$type	= intval($_POST['type']);
		$type_data = $_POST['type_data'];

		// 来自"收藏夹小工具"
        $from_data = serialize(array('source' => '收藏夹小工具', 'url' => SITE_URL.'/index.php?app=home&mod=Account&act=skgoodies&addon=SKgoodies&hook=home_account_show'));

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
	
	//关键 JS输出 
    public function bookmarklet() {

        $this->display('bookmarklet', 'utf-8', 'text/javascript');
		
    }

}