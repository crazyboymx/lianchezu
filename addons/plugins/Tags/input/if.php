<?php
class TagIf extends TPTag{
     private $content;
     private $nested;
     private $attr;
     
     public function __construct(){
     	$this->content = "true";
     	$this->nested  = "true";
     	$this->attr[]   = array('condition'=>"true",'required'=>"true");
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
        $tag          = $this->parseXmlAttr($attr,'if');
        $condition   = $this->parseCondition($tag['condition']);
        dump($condition);
        $parseStr  = '<?php if('.$condition.'): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
     }
}