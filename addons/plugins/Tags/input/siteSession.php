<?php
class TagSiteSession extends TPTag{
     private $content;
     private $nested;
     private $attr;
     public $alias = "session,notsession";
     
     
     public function __construct(){
     	$this->content = "true";
     	$this->attr[]   = array('name'=>"name",'required'=>"true");
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
        $tag      = $this->parseXmlAttr($attr,'session');
        $name   = $tag['name'];
        if($this->other == 'session'){
        	$parseStr = '<?php if(isset($_SESSION["'.$name.'"])): ?>'.$content.'<?php endif; ?>';
        }else{
        	$parseStr = '<?php if(!isset($_SESSION["'.$name.'"])): ?>'.$content.'<?php endif; ?>';
        }
        return $parseStr;
     }
}