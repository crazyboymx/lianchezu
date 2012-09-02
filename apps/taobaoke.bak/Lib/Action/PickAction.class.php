<?php



class PickAction extends Action {
	
	public function index() {
		$map['uid']=$this->mid;
        $bcdisplay = M('weibo_bc')->where($map)->order('bc_id DESC')->findAll();
        $this->assign('bcdisplay',$bcdisplay); 
		
		
		$img['img']=$_GET['media'];
		$img['title']=$_GET['title'];
		$img['url']=$_GET['url'];
		$this->assign( $img );
		$this->display();
	}
	
	
	public function tools() {
		
		$this->display();
	}
	public function star() {
	
	
		
		$img=$_POST['img'];
		
		$imageInfo = @getimagesize($img);
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            if( !in_array($imageType,array('jpg','gif','png','jpeg')) ) {
               //$this->error('图片格式不对');
			   echo '0';exit;
			   
			   
	    }
		
		
		$extend = pathinfo($img); 
        $extend = strtolower($extend["extension"]); 
        $get_file = file_get_contents($img);
		$datePath = date('Y/md/H');
		$savePath = SITE_PATH.'/data/uploads/'.$datePath.'/';
		if( !file_exists( $savePath ) ) mk_dir( $savePath  );
		//仿知美二次开发
		if(!$extend||empty($extend)){
			$extend="jpg";
		}
		
                
    	   
		
		$filename = time().rand(1111,9999).rand(1,99).".".$extend;
        $fp = @fopen($savePath.$filename,"w");
        @fwrite($fp,$get_file);
        fclose($fp);//仿知美二次开发
		$thumbname = substr( $filename , 0 , strpos( $filename,'.' ) ).'_small.'.$extend;//仿知美二次开发
		$thumbhaibaoname = substr( $filename , 0 , strpos( $filename,'.' ) ).'_haibao.'.$extend;//仿知美二次开发
	 	$thumbmiddlename = substr( $filename , 0 , strpos( $filename,'.' ) ).'_middle.'.$extend;//仿知美二次开发
		include_once SITE_PATH.'/addons/libs/Image.class.php';
	    Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbname , '' , 60 , 60 );
		Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbhaibaoname , '' , 200 ,'auto' );
		Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbmiddlename , '' , 550 ,'auto' );
		
		$imgs['thumburl']="".$datePath.'/'.$thumbname;
		$imgs['thumbhaibaourl']="".$datePath.'/'.$thumbhaibaoname;
		$imgs['thumbmiddleurl']="".$datePath.'/'.$thumbmiddlename;
		$imgs['picurl']="".$datePath.'/'.$filename;
		
		///////////////////////////////////////////////////////////////////
		
///////////////////////////////////////////////////////////////////
function createcutphoto($orgpath,$objpath,$x,$y,$WIDTH,$HEIGHT){
	$width=0;
	$height=0;
	$Arr=getimagesize($orgpath);
	$width=$Arr[0];
	$height=$Arr[1];
	$x=($width-$WIDTH)/2;
	$y=($height-$HEIGHT)/2;
	list($owidth,$oheight) = getimagesize($orgpath);
	$n_image = imagecreatetruecolor($WIDTH,$HEIGHT);
	$image = imagecreatefromjpeg($orgpath);
	imagecopyresampled($n_image,$image,0,0,$x,$y,$owidth,$oheight,$owidth,$oheight);
	imagejpeg($n_image,$objpath,100);
}

function openImage($file, &$width, &$height) {
	$imageSize = getimagesize($file);
	if ($imageSize === false) {
		return null;
	}

	$width = $imageSize[0];
	$height = $imageSize[1];

	$imageTypes = imagetypes();
	if (($imageSize[2] === IMAGETYPE_GIF) && ($imageTypes & IMG_GIF)) {
		$image = imagecreatefromgif($file);
	} elseif (($imageSize[2] === IMAGETYPE_JPEG) && ($imageTypes & IMG_JPG)) {
		$image = imagecreatefromjpeg($file);
	} elseif (($imageSize[2] === IMAGETYPE_PNG) && ($imageTypes & IMG_PNG)) {
		$image = imagecreatefrompng($file);
	} elseif (($imageSize[2] === IMAGETYPE_WBMP) && ($imageTypes & IMG_WBMP)) {
		$image = imagecreatefromwbmp($file);
	} elseif (($imageSize[2] === IMAGETYPE_XBM)) {
		$image = imagecreatefromxbm($file);
	} else {
		$image = imagecreatefromgd2($file);
	}
	return $image;
}

function makeThumb($sFile, $dFile, $dWidth, $dHeight, $mode="FULL") {
	$sImage = openImage($sFile, $sWidth, $sHeight);
	if ($sImage === null) {
		return false;
	}

	$dImage = imagecreatetruecolor($dWidth, $dHeight);
	$cWhite = imagecolorallocate($dImage, 255, 255, 255);
	imagefill($dImage, 0, 0, $cWhite);

	$dX = 0;
	$dY = 0;
	if ($mode === "CENTER") {
		$ratio = min($dWidth / $sWidth, $dHeight / $sHeight);
		$dX = round(($dWidth - $sWidth * $ratio) / 2);
		$dY = round(($dHeight - $sHeight * $ratio) / 2);
		$dWidth = round($sWidth * $ratio);
		$dHeight = round($sHeight * $ratio);
	} elseif ($mode === "CUT") {
		$ratio = max($dWidth / $sWidth, $dHeight / $sHeight);
		$sWidth = round($dWidth / $ratio);
		$sHeight = round($dHeight / $ratio);
	}
	imagecopyresampled($dImage, $sImage, $dX, $dY, 0, 0, $dWidth, $dHeight, $sWidth, $sHeight);
	imagejpeg($dImage, $dFile);

	imagedestroy($sImage);
	imagedestroy($dImage);
	return true;
}
///////////////////////////////////////////////////////////////////		
		
//////////////////////二次开发 START//////////////////////////////
//请修改截取图片大小
$WIDTH=60;//宽度
$HEIGHT=60;//高度
//////////////////////二次开发 END////////////////////////////////

$sFile=SITE_PATH.'/data/uploads/'.$datePath.'/'.$filename;
$dFile=SITE_PATH.'/data/uploads/'.$datePath.'/'.$thumbname;
$Arr=getimagesize($sFile);

if ($Arr[0]>$Arr[1]){
	if ($Arr[1]>$WIDTH){
		$Height=$WIDTH;
		$Width=$Arr[0]-$Arr[0]*($Arr[1]-$WIDTH)/$Arr[1];
	}
}else{
	if ($Arr[0]>$HEIGHT){
		$Width=$HEIGHT;
		$Height=$Arr[1]-$Arr[1]*($Arr[0]-$HEIGHT)/$Arr[0];
	}
}

if ($Width>0 && $Height>0){
	makeThumb($sFile, $dFile, $Width, $Height, "FULL");
	if ($Width<$Height){
		$Arr=getimagesize($dFile);
		list($oWidth,$oHeight) = getimagesize($dFile);
		$N_Image = imagecreatetruecolor($Width,$Height);
		$Image = imagecreatefromjpeg($dFile);
		imagecopyresampled($N_Image,$Image,0,0,0,0,$Width,$Height,$Width,$Height);
		imagejpeg($N_Image,$dFile,100);
	}else{
		$Arr=getimagesize($dFile);
		list($oWidth,$oHeight) = getimagesize($dFile);
		$N_Image = imagecreatetruecolor($Width,$Height);
		$Image = imagecreatefromjpeg($dFile);
		imagecopyresampled($N_Image,$Image,0,0,0,0,$Width,$Height,$Width,$Height);
		imagejpeg($N_Image,$dFile,100);
	}
	createcutphoto($dFile,$dFile,0,0,$WIDTH,$HEIGHT);
}
///////////////////////////////////////////////////////////////////
		
		$hFile=SITE_PATH.'/data/uploads/'.$datePath.'/'.$thumbhaibaoname;
				$Mpic=getimagesize($hFile);
				
				$imgs['mwidth']   = $Mpic[0];	
				$imgs['mheight']   = $Mpic[1];
		
		
		$data['uid']=$this->mid;
		$data['bc_id']=$_POST['bc_id'];
		$data['from_data']=$_POST['from_data'];
		$data['content']=$_POST['content'];
		$data['ctime']=time();
		$data['type']=1;
		$data['from']=5;
		$data['type_data']=serialize($imgs);
		if($res=M('weibo')->add($data)){
		 echo $res;
		};
		
	}
	
	
}