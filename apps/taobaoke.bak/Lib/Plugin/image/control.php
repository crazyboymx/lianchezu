<?php 
include_once 'function.php';

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

switch ($do_type){
	
	
	case 'before_publish': //发布前检验
    	if( $_FILES['pic'] ){
    		
    		if($_FILES['pic']['size'] > 2*1024*1024 ){
	        	$result['boolen']    = 0;
	        	$result['message']   = '图片太大，不能超过2M';
	        	exit( json_encode( $result ) );
    		}

    	    $imageInfo = getimagesize($_FILES['pic']['tmp_name']);
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            if( !in_array($imageType,array('jpg','gif','png','jpeg')) ) {
                $result['boolen']    = 0;
                $result['message']   = '图片格式错误';
                exit( json_encode( $result ) );
	        }

    		//执行上传操作
    		$savePath =  getSaveTempPath();
            //$filename = md5( time().$this->mid ).'.'.substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'],'.')+1);
            $filename = md5( time().$this->mid ).'.'.$imageType;
	    	if(@copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename)) 
	        {
			//////////////////////二次开发 START//////////////////////////////
			//	createcutphoto($savePath.'/'.$filename,$savePath.'/'.$filename,0,0,$WIDTH,$HEIGHT);
//////////////////////二次开发 END////////////////////////////////
			
	        	$result['boolen']    = 1;
	        	$result['type_data'] = 'temp/'.$filename;
	        	$result['file_name'] = $filename;
	        	$result['picurl']    =  __UPLOAD__.'/temp/'.$filename;
	        } else {
	        	$result['boolen']    = 0;
	        	$result['message']   = '上传失败';
	        }
    	}else{
        	$result['boolen']    = 0;
        	$result['message']   = '上传失败';
    	}
    	exit( json_encode( $result ) );		
		break;
		
	case 'before_publish_input': //发布前检验
		
		if($_POST){
                
    	    $imageInfo = @getimagesize($imgurl);
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            if( !in_array($imageType,array('jpg','gif','png','jpeg')) ) {
                $result['boolen']    = 0;
                $result['message']   = '图片格式错误';
                exit( json_encode( $result ) );
	       }
		$filename = md5( time().$this->mid ).'.'.$imageType;
		$picurl    = SITE_PATH.'/data/uploads/temp';
                
		if( !file_exists( $picurl) ) mk_dir( $picurl);
                
                include_once SITE_PATH.'/addons/libs/Image.class.php';
		 
                Image::thumb( $imgurl ,$picurl.'/'.$filename, '' , $imageInfo[0] , $imageInfo[1]);
                        
                
                
                
		$result['boolen']    = 1;
	        $result['type_data'] = 'temp/'.$filename;
	        $result['file_name'] = $filename;
	        $result['picurl']    =  __UPLOAD__.'/temp/'.$filename;
			
                exit( json_encode( $result ) );		
    		
    	
		}else{
        	$result['boolen']    = 0;
        	$result['message']   = '上传失败';
    	}
    	
		break;	
	case 'publish':  //发布处理
			if(!file_exists($type_data)){
				$type_data = '/data/uploads/'.$type_data;
			}else{
				$type_data = preg_replace("/^\./",'',$type_data);
			}
			
	 		preg_match('|\.(\w+)$|', basename($type_data), $ext);
	 		$fileext  = strtolower($ext[1]);
	 		$filename = md5($type_data) . '.' . $fileext;
			$datePath = date('Y/md/H');
	 		$savePath = SITE_PATH.'/data/uploads/'.$datePath;
        	if( !file_exists( $savePath ) ) mk_dir( $savePath  );
	 		$thumbname = substr( $filename , 0 , strpos( $filename,'.' ) ).'_small.' . $fileext;//仿知美二次开发by syh
			$thumbhaibaoname = substr( $filename , 0 , strpos( $filename,'.' ) ).'_haibao.' . $fileext;//仿知美二次开发by syh
	 		$thumbmiddlename = substr( $filename , 0 , strpos( $filename,'.' ) ).'_middle.' . $fileext;//仿知美二次开发by syh
	 		if( copy( SITE_PATH.$type_data , $savePath.'/'.$filename) ){
	 				include_once SITE_PATH.'/addons/libs/Image.class.php';
					Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbname , '' , 60 , 60 );
					Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbhaibaoname , '' , 200 ,'auto' );
					Image::thumb( $savePath.'/'.$filename , $savePath.'/'.$thumbmiddlename , '' , 550 ,'auto' );
		        	
					$typedata['thumburl'] = ($fileext=='gif')?''.$datePath.'/'.$filename:''.$datePath.'/'.$thumbname;//仿知美二次开发by syh
					$typedata['thumbhaibaourl'] = ($fileext=='gif')?''.$datePath.'/'.$filename:''.$datePath.'/'.$thumbhaibaoname;//仿知美二次开发by syh
		        	
					$typedata['thumbmiddleurl'] = ($fileext=='gif')?''.$datePath.'/'.$filename:''.$datePath.'/'.$thumbmiddlename;
		        	$typedata['picurl']   = ''.$datePath.'/'.$filename;
					
					///////////////////////////////////////////////////////////////////
$Width=0;
$Height=0;
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
				
				$typedata['mwidth']   = $Mpic[0];	
				$typedata['mheight']   = $Mpic[1];
					
	 		}
		break;
		
	case 'after_publish': //发布完成后的处理

		break;
}