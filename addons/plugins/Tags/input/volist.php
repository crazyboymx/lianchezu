<?php
class TagVolist extends TPTag{
     private $content;
     private $nested;
     private $attr;
     
     public function __construct(){
     	$this->content = "true";
     	$this->nested  = "true";
     	$this->attr[]   = array('name'=>"id",'required'=>"true");
     	$this->attr[]   = array('name'=>"name",'required'=>"true");
     	$this->attr[]   = array('name'=>"offset",'required'=>"false");
     	$this->attr[]   = array('name'=>"length",'required'=>"false");
     	$this->attr[]   = array('name'=>"key",'required'=>"false");
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
     
        static $_iterateParseCache = array();
        //如果已经解析过，则直接返回变量值
        $cacheIterateId = md5($attr.$content);
        if(isset($_iterateParseCache[$cacheIterateId]))
            return $_iterateParseCache[$cacheIterateId];
        $tag      = $this->parseXmlAttr($attr,'iterate');
        $name   = $tag['name'];
        $id        = $tag['id'];
        $empty  = isset($tag['empty'])?$tag['empty']:'';
        $key     =   !empty($tag['key'])?$tag['key']:'i';
        $mod    =   isset($tag['mod'])?$tag['mod']:'2';
        $name   = $this->autoBuildVar($name);
        $parseStr  =  '<?php if(is_array('.$name.')): $'.$key.' = 0;';
		if(isset($tag['length']) && '' !=$tag['length'] ) {
			$parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].','.$tag['length'].');';
		}elseif(isset($tag['offset'])  && '' !=$tag['offset']){
            $parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].');';
        }else{
            $parseStr .= ' $__LIST__ = '.$name.';';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "'.$empty.'" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$'.$id.'): ';
        $parseStr .= '++$'.$key.';';
        $parseStr .= '$mod = ($'.$key.' % '.$mod.' )?>';
        $parseStr .= $this->tpl->parse($content);
        $parseStr .= '<?php endforeach; endif; else: echo "'.$empty.'" ;endif; ?>';
        $_iterateParseCache[$cacheIterateId] = $parseStr;

        if(!empty($parseStr)) {
            return $parseStr;
        }
        return ;
     }
}