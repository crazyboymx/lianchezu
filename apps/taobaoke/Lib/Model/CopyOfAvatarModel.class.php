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
    function saveAvatar($uid,$faceurl){
    	$this->uid = $uid;
		$original = $this->getSavePath()."/original.jpg";
		$big   = $this->getSavePath()."/big.jpg";
		$small = $this->getSavePath()."/small.jpg";
		include( SITE_PATH.'/addons/libs/Image.class.php' );
		Image::thumb( $faceurl, $original , '' , 150 , 150 );
		Image::thumb( $faceurl, $big , '' , 120 , 120 );
		Image::thumb( $faceurl , $small , '' , 50 ,50 );
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
        	echo '<script type="text/javascript">alert("对不起, 图片未上传成功, 请再试一下");</script>';
        	exit();
        }
        
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
        } else {
        	@unlink($_FILES['Filedata']['tmp_name']);
        	echo '<script type="text/javascript">alert("对不起, 上传失败");</script>';
        }
        
        //写新上传照片的ID.
        echo '<script type="text/javascript">window.parent.hideLoading();window.parent.buildAvatarEditor("'.$pic_id.'","'.$pic_abs_path.'","photo");</script>'; 
    }
    
    //保存图片
    function dosave(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        
        //这里传过来会有两种类型，一先一后, big和small, 保存成功后返回一个json字串，客户端会再次post下一个.
        $type = isset($_GET['type'])?trim($_GET['type']):'small';
        $pic_id = trim($_GET['photoId']);
        //$orgin_pic_path = $_GET['photoServer']; //原始图片地址，备用.
        //$from = $_GET['from']; //原始图片地址，备用.
        
        //生成图片存放路径
        $new_avatar_path = $type.'.jpg';
        //将POST过来的二进制数据直接写入图片文件.
        $len = file_put_contents($this->getSavePath().'/'.$new_avatar_path,file_get_contents("php://input"));
        
        //原始图片比较大，压缩一下. 效果还是很明显的, 使用80%的压缩率肉眼基本没有什么区别
        //小图片 不压缩约6K, 压缩后 2K, 大图片约 50K, 压缩后 10K
    //    $avtar_img = imagecreatefromjpeg(SD_ROOT.'./'.$new_avatar_path);
      //  imagejpeg($avtar_img,SD_ROOT.'./'.$new_avatar_path,80);
        //nix系统下有必要时可以使用 chmod($filename,$permissions);
        
        //输出新保存的图片位置, 测试时注意改一下域名路径, 后面的statusText是成功提示信息.
        //status 为1 是成功上传，否则为失败.
        $d = new pic_data();
        //$d->data->urls[0] = 'http://sns.com/avatar_test/'.$new_avatar_path;
        $d->data->urls[0] = __UPLOAD__.'/avatar/'.$this->uid.'/'.$new_avatar_path;
        $d->status = 1;
        $d->statusText = '上传成功!';
        
        $msg = json_encode($d);
        
        echo $msg;
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