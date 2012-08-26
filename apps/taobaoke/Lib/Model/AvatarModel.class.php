<?php 
/** 头像设置 **/
class AvatarModel extends Model{
	var $uid;
    
    function getSavePath(){
        $savePath = SITE_PATH.'/data/uploads/avatar/'.$this->uid;
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }
    
    //将远程图片转换成本地头像
   	public function saveAvatar($uid,$faceurl)
    {
    	$this->uid = $uid;
		$original = $this->getSavePath()."/original.jpg";
		$big   = $this->getSavePath()."/big.jpg";
		$middle = $this->getSavePath()."/middle.jpg";
		$small = $this->getSavePath()."/small.jpg";
		include( SITE_PATH.'/addons/libs/Image.class.php' );
		Image::thumb( $faceurl, $original, '', 180, 180);
		Image::thumb( $faceurl, $big, '', 180, 180);
		Image::thumb( $faceurl, $middle, '', 50, 50);
		Image::thumb( $faceurl, $small, '', 30, 30);
    }

    
    //上传头像
    function upload(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        $pic_id = time();//使用时间来模拟图片的ID.           
        $pic_path = $this->getSavePath().'/original.jpg';
        $pic_abs_path = __UPLOAD__.'/avatar/'.$this->uid.'/original.jpg';
        //保存上传图片.
        if(empty($_FILES['Filedata'])) {
        	$return['message'] = '对不起, 图片未上传成功, 请再试一下';
        	$return['code']    = '0';
        }else{
        
	        $file = @$_FILES['Filedata']['tmp_name'];
	        file_exists($pic_path) && @unlink($pic_path);
	        if(@copy($_FILES['Filedata']['tmp_name'], $pic_path) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $pic_path)) 
	        {
	        	@unlink($_FILES['Filedata']['tmp_name']);
	        	/*list($width, $height, $type, $attr) = getimagesize($pic_path);
	        	if($width < 10 || $height < 10 || $width > 3000 || $height > 3000 || $type == 4) {
	        		@unlink($pic_path);
	        		return -2;
	        	}*/
	        	include( SITE_PATH.'/addons/libs/Image.class.php' );
	        	Image::thumb( $pic_path, $pic_path , '' , 300 , 300 );
	        	list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($pic_path);
	        	
	        	$return['data']['picurl'] = 'data/uploads/avatar/'.$this->uid.'/original.jpg';
	        	$return['data']['picwidth'] = $sr_w;
	        	$return['data']['picheight'] = $sr_h;
	        	$return['code']    = '1';
	        } else {
	        	@unlink($_FILES['Filedata']['tmp_name']);
	        	$return['message'] = '对不起, 图片未上传成功';
	        	$return['code']    = '0';
	        }
	        
        }
        return json_encode( $return );
    }
	
	//上传封面 仿站二次开发
    function upload_fengmian(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        $pic_id = time();//使用时间来模拟图片的ID.           
        $pic_path = $this->getSavePath().'/fengmian.jpg';
        $pic_abs_path = __UPLOAD__.'/avatar/'.$this->uid.'/fengmian.jpg';
        //保存上传图片.
        if(empty($_FILES['Filedata_fengmian'])) {
        	$return['message'] = '对不起, 图片未上传成功, 请再试一下';
        	$return['code']    = '0';
        }else{
        
	        $file = @$_FILES['Filedata_fengmian']['tmp_name'];
	        file_exists($pic_path) && @unlink($pic_path);
	        if(@copy($_FILES['Filedata_fengmian']['tmp_name'], $pic_path) || @move_uploaded_file($_FILES['Filedata_fengmian']['tmp_name'], $pic_path)) 
	        {
	        	@unlink($_FILES['Filedata_fengmian']['tmp_name']);
	        	/*list($width, $height, $type, $attr) = getimagesize($pic_path);
	        	if($width < 10 || $height < 10 || $width > 3000 || $height > 3000 || $type == 4) {
	        		@unlink($pic_path);
	        		return -2;
	        	}*/
	        	include( SITE_PATH.'/addons/libs/Image.class.php' );
	        	Image::thumb( $pic_path, $pic_path , '' , 950 , 200 );
	        	list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($pic_path);
	        	
	        	$return['data']['picurl'] = 'data/uploads/avatar/'.$this->uid.'/fengmian.jpg';
	        	$return['data']['picwidth'] = $sr_w;
	        	$return['data']['picheight'] = $sr_h;
	        	$return['code']    = '1';
	        } else {
	        	@unlink($_FILES['Filedata_fengmian']['tmp_name']);
	        	$return['message'] = '对不起, 图片未上传成功';
	        	$return['code']    = '0';
	        }
	        
        }
        return json_encode( $return );
    }
    
    //保存图片
    function dosave($uid){
    	//header("Content-type: image/jpeg"); 
		$x1 = $_POST['x1'];//客户端选择区域左上角x轴坐标
		$y1 = $_POST['y1'];//客户端选择区域左上角y轴坐标
		$x2 = $_POST['x2'];//客户端选择区 的宽
		$y2 = $_POST['y2'];//客户端选择区 的高
		$w = $_POST['w'];//客户端选择区 的高
		$h = $_POST['h'];//客户端选择区 的高
		$src = SITE_PATH.'/'.$_POST['picurl'];//图片的路径
		
		// 获取源图的扩展名宽高
		list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($src);
		if($sr_type){
			//获取后缀名
			$ext = image_type_to_extension($sr_type,false);
		} else {
			echo "-1";
			exit;
		}
		
		$big_w = '180';
		$big_h = '180';
		
		$middle_w = '50';
		$middle_h = '50';
		
		$small_w  = '30';
		$small_h  = '30';
		
		$face_path      =   SITE_PATH.'/data/uploads/avatar/'.$uid;
		$big_name	    =	$face_path.'/big.jpg';		// 大图
		$middle_name	=	$face_path.'/middle.jpg';		// 中图
		$small_name		=	$face_path.'/small.jpg';
		
		$func	=	($ext != 'jpg')?'imagecreatefrom'.$ext:'imagecreatefromjpeg';
		$img_r	=	call_user_func($func,$src);
		
		$dst_r	=	ImageCreateTrueColor( $big_w, $big_h );
		$back	=	ImageColorAllocate( $dst_r, 255, 255, 255 );
		ImageFilledRectangle( $dst_r, 0, 0, $big_w, $big_h, $back );
		ImageCopyResampled( $dst_r, $img_r, 0, 0, $x1, $y1, $big_w, $big_h, $w, $h );
	
		ImagePNG($dst_r,$big_name);  // 生成大图

		// 开始切割大方块头像成中等方块头像
		$sdst_r	=	ImageCreateTrueColor( $middle_w, $middle_h );
		ImageCopyResampled( $sdst_r, $dst_r, 0, 0, 0, 0, $middle_w, $middle_h, $big_w, $big_w );
		ImagePNG($sdst_r,$middle_name);  // 生成中图
		
		
		// 开始切割大方块头像成中等方块头像
		$sdst_s	=	ImageCreateTrueColor( $small_w, $small_h );
		ImageCopyResampled( $sdst_s, $dst_r, 0, 0, 0, 0, $small_w, $small_h, $big_w, $big_w );
		ImagePNG($sdst_s,$small_name);  // 生成中图
		
		ImageDestroy($dst_r);
		ImageDestroy($sdst_r);
		ImageDestroy($sdst_s);
		ImageDestroy($img_r);
		echo '1';
    }
    
	//保存封面 仿站二次开发
    function dosave_fengmian($uid){
    	//header("Content-type: image/jpeg"); 
		$x1 = $_POST['x1'];//客户端选择区域左上角x轴坐标
		$y1 = $_POST['y1'];//客户端选择区域左上角y轴坐标
		$x2 = $_POST['x2'];//客户端选择区 的宽
		$y2 = $_POST['y2'];//客户端选择区 的高
		$w = $_POST['w'];//客户端选择区 的高
		$h = $_POST['h'];//客户端选择区 的高
		$src = SITE_PATH.'/'.$_POST['picurl'];//图片的路径
		
		// 获取源图的扩展名宽高
		list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($src);
		if($sr_type){
			//获取后缀名
			$ext = image_type_to_extension($sr_type,false);
		} else {
			echo "-1";
			exit;
		}
		
		$big_w = '180';
		$big_h = '180';
		
		$middle_w = '50';
		$middle_h = '50';
		
		$small_w  = '30';
		$small_h  = '30';
		
		$face_path      =   SITE_PATH.'/data/uploads/avatar/'.$uid;
		$big_name	    =	$face_path.'/big.jpg';		// 大图
		$middle_name	=	$face_path.'/middle.jpg';		// 中图
		$small_name		=	$face_path.'/small.jpg';
		
		$func	=	($ext != 'jpg')?'imagecreatefrom'.$ext:'imagecreatefromjpeg';
		$img_r	=	call_user_func($func,$src);
		
		$dst_r	=	ImageCreateTrueColor( $big_w, $big_h );
		$back	=	ImageColorAllocate( $dst_r, 255, 255, 255 );
		ImageFilledRectangle( $dst_r, 0, 0, $big_w, $big_h, $back );
		ImageCopyResampled( $dst_r, $img_r, 0, 0, $x1, $y1, $big_w, $big_h, $w, $h );
	
		ImagePNG($dst_r,$big_name);  // 生成大图

		// 开始切割大方块头像成中等方块头像
		$sdst_r	=	ImageCreateTrueColor( $middle_w, $middle_h );
		ImageCopyResampled( $sdst_r, $dst_r, 0, 0, 0, 0, $middle_w, $middle_h, $big_w, $big_w );
		ImagePNG($sdst_r,$middle_name);  // 生成中图
		
		
		// 开始切割大方块头像成中等方块头像
		$sdst_s	=	ImageCreateTrueColor( $small_w, $small_h );
		ImageCopyResampled( $sdst_s, $dst_r, 0, 0, 0, 0, $small_w, $small_h, $big_w, $big_w );
		ImagePNG($sdst_s,$small_name);  // 生成中图
		
		ImageDestroy($dst_r);
		ImageDestroy($sdst_r);
		ImageDestroy($sdst_s);
		ImageDestroy($img_r);
		echo '1';
    }
	
    function getcamera(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");

        $pic_id = time();
        
        //生成图片存放路径
        $new_avatar_path = $this->getSavePath().'/original.jpg';
        
        //将POST过来的二进制数据直接写入图片文件.
        $len = file_put_contents($this->getSavePath().'/original.jpg',file_get_contents("php://input"));
        
        //原始图片比较大，压缩一下. 效果还是很明显的, 使用80%的压缩率肉眼基本没有什么区别
        //$avtar_img = imagecreatefromjpeg($new_avatar_path);
       // imagejpeg($avtar_img,$new_avatar_path,80);
        //nix系统下有必要时可以使用 chmod($filename,$permissions);
        
        //输出新保存的图片位置, 测试时注意改一下域名路径, 后面的statusText是成功提示信息.
        //status 为1 是成功上传，否则为失败.
        $d = new pic_data();
        $d->data->photoId = $pic_id;
        //$d->data->urls[0] = 'http://sns.com/avatar_test/'.$new_avatar_path;
        $d->data->urls[0] = __UPLOAD__.'/avatar/'.$this->uid.'/original.jpg';
        $d->status = 1;
        $d->statusText = '上传成功!';
        
        $msg = json_encode($d);
        
        echo $msg;        
    }
}

class pic_data
{
	 public $data;
	 public $status;
	 public $statusText;
	public function __construct()
	{
		$this->data->urls = array();
	}
}
?>