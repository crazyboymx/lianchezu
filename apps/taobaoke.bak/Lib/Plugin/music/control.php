<?php 
include 'function.php';

switch ($do_type){
	case 'before_publish':
			
    $ts=$_POST['s'];
	$url="http://kuang.xiami.com/app/nineteen/search/key/".$ts."/diandian/1/page/".$_POST['page']."";
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
	$contents = curl_exec($ch);
	$b=json_decode($contents);
	
	echo $contents;

		break;
		
	case 'publish':
	     $typedata['mp3id'] = $type_data['mp3id'];
		 $typedata['logo'] =  $type_data['logo'];
		 
		 $zhuanji_m =  $type_data['logo'];
		 
		 $zhuanji_change = array("_1" => "_2");
		 
		 $zhuanji =	strtr($zhuanji_m,$zhuanji_change);
		 
		 $Mpic=getimagesize($zhuanji);
				
					$typedata['mwidth']   = $Mpic[0];	
					$typedata['mheight']   = $Mpic[1];
	
	
		break;
}
?>