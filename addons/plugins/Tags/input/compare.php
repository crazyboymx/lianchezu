<?php
class TagCompare extends TPTag{
     private $content;
     private $nested;
     private $attr;
     public $alias = "eq,neq,gt,lt,egt,elt,heq,nheq,equal,notequal";
     
     public function __construct(){
     	$this->content = "true";
     	$this->nested  = "true";
     	$this->attr[]   = array('name'=>"name",'required'=>"true");
     	$this->attr[]   = array('name'=>"value",'required'=>"true");
     	$this->attr[]   = array('name'=>"type",'required'=>"false");
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
        $tag      = $this->parseXmlAttr($attr,'compare');
        $name   = $tag['name'];
        $value   = $tag['value'];
        $type    =   $tag['type']?$tag['type']:$this->other;
        $type    =   $this->parseCondition(' '.$type.' ');
        $varArray = explode('|',$name);
        $name   =   array_shift($varArray);
        $name = $this->autoBuildVar($name);
        if(count($varArray)>0)
            $name = $this->tpl->parseVarFunction($name,$varArray);
        if('$' == substr($value,0,1)) {
            $value  =  $this->autoBuildVar(substr($value,1));
        }else {
            $value  =   '"'.$value.'"';
        }
        $parseStr = '<?php if(('.$name.') '.$type.' '.$value.'): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
     }
}