<?php
class TPTag{
   
    /**
       +----------------------------------------------------------
       * 标签库定义XML文件
       +----------------------------------------------------------
       * @var string
       * @access protected
       +----------------------------------------------------------
    */
    protected $tagPath = array();
    
    protected $other = "";

    /**
       +----------------------------------------------------------
       * 标签库名称
       +----------------------------------------------------------
       * @var string
       * @access protected
       +----------------------------------------------------------
    */
    protected $tagLib ='';

    /**
       +----------------------------------------------------------
       * 标签库标签列表
       +----------------------------------------------------------
       * @var string
       * @access protected
       +----------------------------------------------------------
    */
    protected $tagList = array();

    /**
       +----------------------------------------------------------
       * 标签库分析数组
       +----------------------------------------------------------
       * @var string
       * @access protected
       +----------------------------------------------------------
    */
    protected $parse = array();

    /**
       +----------------------------------------------------------
       * 标签库是否有效
       +----------------------------------------------------------
       * @var string
       * @access protected
       +----------------------------------------------------------
    */
    protected $valid = false;

    /**
       +----------------------------------------------------------
       * 当前模板对象
       +----------------------------------------------------------
       * @var object
       * @access protected
       +----------------------------------------------------------
    */
    protected $tpl;

    protected $comparison = array(' nheq '=>' !== ',' heq '=>' === ',' neq '=>' != ',' eq '=>' == ',' egt '=>' >= ',' gt '=>' > ',' elt '=>' <= ',' lt '=>' < ');

    /**
       +----------------------------------------------------------
       * 架构函数
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
    */
    public function __construct($tagLib)
    {
        $this->tagLib    = $tagLib;
        $this->setTpl();
        $this->_initialize();
        $this->load();
    }

    // 初始化标签库的定义文件
    public function _initialize() {
        //引入所有tag解析php文件
        
        $this->tagPath = $this->traversalDir(SITE_PATH.'/addons/plugins/Tags/'.$this->tagLib);
        
    }
    public function setOther($value){
    	$this->other = $value;
    }
    public function setTpl(){
    	$this->tpl       = service('Template');
    }

    public function load() {
        if(empty($this->tagPath)){
            $this->valid = false;
            return;
        }
        foreach($this->tagPath as $key=> $value){
            if(!is_file($value)){
                $this->valid = false;
                return;
            }
            $this->valid = true;
            include_once $value;
            $className = "Tag".ucfirst($key);
            $object = new $className;
            
            $content = $object->getContent();
            $nested  = $object->getNested();
            $attr    = $object->getAttr();
            
            $this->tagList[$key]['name'] = $key;
            $this->tagList[$key]['content'] = $content;
            $this->tagList[$key]['nested']  = $nested;
            $this->tagList[$key]['attribute'] = $attr;
            if(isset($object->alias) && !empty($object->alias)){
                    $alias  =   explode(',',$object->alias);
                        foreach ($alias as $tag1){
                            $this->tagList[$tag1] =  array(
                                'name'=>$tag1,
                                'content'=>$content,
                                'nested'=>(!empty($nested) && $nested != 'false')?$nested:0,
                                'attribute'=>isset($attr)?$attr:'',
                            	'alias' => $key,
                            );
                    }
            }
        }
    }

    /**
       +----------------------------------------------------------
       * 分析TagLib文件的信息是否有效
       * 有效则转换成数组
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param mixed $name 数据
       * @param string $value  数据表名
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function valid()
    {
        return $this->valid;
    }

    /**
       +----------------------------------------------------------
       * 获取TagLib名称
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function getTagLib()
    {
        return $this->tagLib;
    }

    /**
       +----------------------------------------------------------
       * 获取Tag列表
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function getTagList()
    {
        return $this->tagList;
    }

    /**
       +----------------------------------------------------------
       * 获取某个Tag属性的信息
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function getTagAttrList($tagName)
    {
        static $_tagCache   = array();
        $_tagCacheId        =   md5($this->tagLib.$tagName);
        if(isset($_tagCache[$_tagCacheId])) {
            return $_tagCache[$_tagCacheId];
        }
        $list = array();

        $tags = $this->tagList;
        $tagName = strtolower($tagName);
        
        if(isset($tags[$tagName]) && isset($tags[$tagName]['attribute'])){
            $attr = $tags[$tagName]['attribute'];
            foreach($attr as $value){
                $list[] = array('name'=>$value['name'],
                                'required'=>$value['required']);
            }
        }
        
        $_tagCache[$_tagCacheId]    =   $list;
        return $list;
    }

    /**
       +----------------------------------------------------------
       * TagLib标签属性分析 返回标签属性数组
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tagStr 标签内容
       +----------------------------------------------------------
       * @return array
       +----------------------------------------------------------
    */
    public function parseXmlAttr($attr,$tag)
    {
        //XML解析安全过滤
        $attr = str_replace('&','___', $attr);
        $xml =  '<tpl><tag '.$attr.' /></tpl>';
        $xml = simplexml_load_string($xml);
        if(!$xml) {
            throw_exception(L('_XML_TAG_ERROR_').' : '.$attr);
        }
        $xml = (array)($xml->tag->attributes());
        $array = array_change_key_case($xml['@attributes']);
        $attrs  = $this->getTagAttrList($tag);
        foreach($attrs as $val) {
            $name   = strtolower($val['name']);
            if( !isset($array[$name])) {
                $array[$name] = '';
            }else{
                $array[$name] = str_replace('___','&',$array[$name]);
            }
        }
        return $array;
    }

    /**
       +----------------------------------------------------------
       * 解析条件表达式
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $condition 表达式标签内容
       +----------------------------------------------------------
       * @return array
       +----------------------------------------------------------
    */
    public function parseCondition($condition) {
        $condition = str_ireplace(array_keys($this->comparison),array_values($this->comparison),$condition);
        $condition = preg_replace('/\$(\w+):(\w+)\s/is','$\\1->\\2 ',$condition);
        $condition = preg_replace('/\$(\w+)\.(\w+)\s/is','$\\1["\\2"] ',$condition);
        return $condition;
    }

    /**
       +----------------------------------------------------------
       * 自动识别构建变量
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $name 变量描述
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function autoBuildVar($name) {
        if(strpos($name,'.')) {
            $vars = explode('.',$name);
            $var  =  array_shift($vars);
            $name = '$'.$var;
            foreach ($vars as $key=>$val)
                $name .= '["'.$val.'"]';
        }elseif(!defined($name)) {
            $name = '$'.$name;
        }
        return $name;
    }

    /**
       +----------------------------------------------------------
       * 用于标签属性里面的特殊模板变量解析
       * 格式 以 Think. 打头的变量属于特殊模板变量
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $varStr  变量字符串
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseThinkVar($varStr){
        $vars = explode('.',$varStr);
        $vars[1] = strtoupper(trim($vars[1]));
        $parseStr = '';
        if(count($vars)>=3){
            $vars[2] = trim($vars[2]);
            switch($vars[1]){
                case 'SERVER':    $parseStr = '$_SERVER[\''.$vars[2].'\']';break;
                case 'GET':         $parseStr = '$_GET[\''.$vars[2].'\']';break;
                case 'POST':       $parseStr = '$_POST[\''.$vars[2].'\']';break;
                case 'COOKIE':    $parseStr = '$_COOKIE[\''.$vars[2].'\']';break;
                case 'SESSION':   $parseStr = '$_SESSION[\''.$vars[2].'\']';break;
                case 'ENV':         $parseStr = '$_ENV[\''.$vars[2].'\']';break;
                case 'REQUEST':  $parseStr = '$_REQUEST[\''.$vars[2].'\']';break;
                case 'CONST':     $parseStr = strtoupper($vars[2]);break;
                case 'LANG':       $parseStr = 'L("'.$vars[2].'")';break;
                case 'CONFIG':    $parseStr = 'C("'.$vars[2].'")';break;
            }
        }else if(count($vars)==2){
            switch($vars[1]){
                case 'NOW':       $parseStr = "date('Y-m-d g:i a',time())";break;
                case 'VERSION':  $parseStr = 'THINK_VERSION';break;
                case 'TEMPLATE':$parseStr = 'C("TMPL_FILE_NAME")';break;
                case 'LDELIM':    $parseStr = 'C("TMPL_L_DELIM")';break;
                case 'RDELIM':    $parseStr = 'C("TMPL_R_DELIM")';break;
                default:  if(defined($vars[1])) $parseStr = $vars[1];
            }
        }
        return $parseStr;
    }

    protected function traversalDir($path) {
		$result = array ();
		$file = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( ($path) ) );
		foreach ( $file as $key => $value ) {
			if (! strpos ( $value->getPathname (), ".svn" ) && strpos ( $value->getFilename (), ".php" )) {
				$path = explode ( DIRECTORY_SEPARATOR, $value->getPath () );
				$temp_key = strtolower ( array_pop ( $path ) );
				list ( $temp_value ) = explode ( '.', $value->getFilename () );
				$result [strtolower ( $temp_value )] = $value->getPathname ();
			}
		}
		return $result;
	}
}