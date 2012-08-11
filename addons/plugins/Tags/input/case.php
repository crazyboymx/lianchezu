<?php
class TagCase extends TPTag{
     private $content;
     private $nested;
     private $attr;
     
     public function __construct(){
     	$this->content = "true";
     	$this->attr[]   = array('name'=>"value",'required'=>"true");
     	$this->attr[]   = array('name'=>"break",'required'=>"false");
     	$this->setTpl();
     }
     
     public function getContent(){
     	return $this->content;
     }
     public function getNested(){
     	return $this->nested;
     }
     
     public function getAttr(){
     	return $this->attr;
     }
     
     public function parse($attr,$content){
     	static $caseCount = 0;
   		$tag = $this->parseXmlAttr($attr,'case');
        $value = $tag['value'];
        if('$' == substr($value,0,1)) {
            $varArray = explode('|',$value);
            $value	=	array_shift($varArray);
            $value  =  $this->autoBuildVar(substr($value,1));
            if(count($varArray)>0)
                $value = $this->tpl->parseVarFunction($value,$varArray);
            $value   =  'case '.$value.': ';
        }elseif(strpos($value,'|')){
            $values  =  explode('|',$value);
            $value   =  '';
            foreach ($values as $val){
                $value   .=  'case "'.addslashes($val).'": ';
            }
        }else{
            $value	=	'case "'.$value.'": ';
        }
        if($caseCount == 0){
        	$parseStr = ''.$value.' ?>'.$content;
        }else{
        	 $parseStr = '<?php '.$value.' ?>'.$content;
        }
        $caseCount ++;
        if('' ==$tag['break'] || $tag['break']) {
            $parseStr .= '<?php break;?>';
        }
        return $parseStr;
     }
}