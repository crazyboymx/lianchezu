<?php
/*本插件由thinksns 插件生成器 生成*/
class RadioAddons extends SimpleAddons {
	protected $version    = '1.1';
	protected $author     = '杨维杰';
	protected $site       = 'http://diandian.com/270687';
	protected $info       = '给ThinkSNS添加电台功能';
	protected $pluginName = '我的电台';
	protected $tsVersion  = '2.5';
	
	/**
	* getHooksInfo
	* 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
	* @access public
	* @return void
	*/
	public function getHooksInfo() {
		$this->apply('home_index_left_tab','home_index_left_tab');
	}

	//添加首页左侧的链接
	public function home_index_left_tab($param){
		$url =	Addons::createAddonShow('Radio','index',array());
		echo '<li><a href="'.$url.'"><img src="'.$this->url.'/html/images/ico.png"/>我的电台</a></li>';
	}

	//写入文件数据
	public function save_config($param){
		// 检测目录是否存在
		$dirname = $this->path.'/';
		if(!is_dir($dirname)){
			return $dirname.'不存在';
		}
		// 整理内容
		$config_content = "<?php\nreturn ".var_export($param['config'], true).";";
		$config_content = str_replace('	', "\t", $config_content);
		if(!file_put_contents($dirname.$param['file'], $config_content)){
			return '写入'.$config_file.'失败';
		}
		// 保存成功
		return true;
	}

	//电台管理
	public function option($param){
		$data = include 'type.php';
		if($_GET['do']){
			$do = $_GET ['do'];
			switch ($do) {
				case 'change':
					$id = abs(intval($_GET['id']));
					(!empty($data[$id])) ? $data[$id]['isopen'] = ($data[$id]['isopen'] + 1) % 2 : "";
					if($this->update_cache($data))
						$this->success('更改状态成功');
					else
						$this->error('更改状态失败');
					break;
				case 'del':
					$id = abs(intval($_GET['id']));
					$img = $this->path.'/html/images/'.$data[$id]['image'];
					unset($data[$id]);
					if($this->update_cache($data) && unlink($img))
						$this->success('删除成功');
					else
						$this->error('删除失败');
					break;
				default:
			}
		}
		if($_POST){//修改顺序
			$arrNewType = array ();
			foreach ($data as $k => $v) {
				$newId = (isset($_POST['id'.$k])) ? abs(intval($_POST['id'.$k])) : $k;
				if(!empty($arrNewType[$newId])){
					$arrNewType[] = $data[$k];
				}else{
					$arrNewType[$newId] = $data[$k];
				}
			}
			if($this->update_cache($arrNewType))
				$this->success('更改顺序成功');
			else
				$this->error('更改顺序失败');
		}
	}

	//新建电台
	public function create($param){
		$data = include 'type.php';
		$id = ($_GET['id'] !== null)? abs(intval($_GET['id'])) : '';
		if($data[$id]){
			$this->assign('id',$id);
			$this->assign('arrCurType',$data[$id]);
		}
		$this->display('create');
	}

	//使用相关
	public function ask($param){
		$this->display('ask');
	}

	//电台首页
	public function index($param){
		$type = (isset($_GET['id'])) ? abs(intval($_GET ['id'])) :(($_COOKIE['radio_type'])?$_COOKIE['radio_type']:0);
		$result = include 'type.php';
		$openArray= array();
		foreach ($result as $k=>$v){
			($v['isopen'] == 1) ? $openArray[$k] = $v : ""; 
		}
		$arrAllType = $result = $openArray;
		if(empty($result[$type])){
			$type = array_rand($result);
		}
		$curType = $result[$type];
		if(isset($_GET['id']))	setcookie('radio_type',$type,time()+60*60*24*30);
		$title = (empty ( $curType )) ? '电台' : $curType ['title'];
		global $ts;
		$ts['site']['page_title'] =$title;
		$this->assign('url_path',$this->url.'/html/');
		$this->assign('arrAllType',$arrAllType);
		$this->assign('curType',$curType);
		$this->display('index');
	}

	//更新缓存
	public function update_cache($param){
		ksort($param);
		return $this->save_config(array(
			'config'=>$param,
			'file'=>'type.php'
		));
	}

	//后台首页
	public function admin($param){
		$data = include 'type.php';
		$url = $this->url.'/html/';
		$ask_url = Addons::adminPage('ask');
		$this->assign('ask_url',$ask_url);
		$this->assign('url_path',$url);
		$this->assign('data',$data);
		$this->display('options');
	}

	//添加和编辑表单
	public function add_edit($param){
		$data = include 'type.php';
		$newType['name'] = htmlspecialchars($_POST['name']);
		$newType['title'] = htmlspecialchars($_POST['title']);
		$newType['url'] = htmlspecialchars($_POST['url']);
		$newType['isopen'] =1;
		if(isset($_POST['id'])){//编辑
			$id = abs(intval($_POST['id'])); 
			$newType['isopen'] = $data[$id]['isopen'];
			if(!$_FILES['image']['name']){
				$newType['image'] = $data[$id]['image'];
				$data[$id] = $newType;
				$this->update_cache($data);
				$this->success('编辑成功');
			}else{
				if($newType['image'] = $this->upload_image($_FILES['image'])){
					@unlink($this->path.'/html/images/'.$data[$id]['image']);
					$data[$id] = $newType;
					$this->update_cache($data);
					$this->success('编辑成功');
				}else{
					$this->error('上传图片失败，编辑失败，请检查目录权限');
				}
			}
		}else{//添加
			if(!$newType['name'] || !$newType['title'] || !$newType['url'])
				$this->error(请填写所有表单项);
			($_FILES['image']['name']) ? $fileName = $this->upload_image($_FILES['image']) : "";
			if(empty($fileName)){
				$this->error('上传图片失败，添加电台失败');
			}
			$newType['image'] = $fileName;
			$data[] = $newType;
			$data = $this->update_cache($data);
			$this->success('添加电台成功');
		}
	}

	//上传图片
	public function upload_image($param){
		if(is_uploaded_file($param['tmp_name'])){
			$suffixArray = explode('.', $param['name']);
			$suffix = array_pop($suffixArray);
			$suffix = strtolower($suffix);
			$fileName = time().".".$suffix;
			$suffixs =	array('jpg','png','gif','jpeg');
			$dir = $this->path.'/html/images/'.$fileName;
			if (in_array($suffix,$suffixs) && !move_uploaded_file($param['tmp_name'],$dir))
				return "";
			return $fileName;
		}
	}


	public function start(){
		return true;
	}

	public function install(){
		 $oldTypeArray = array (
			0 => array ('title' => '电台','name' => '豆瓣FM','image' => 'db.jpg','url' => 'http://douban.fm/partner/baidu/doubanradio?bd_user=419899187&bd_sig=7af6c9595491149f56b3593dfae1bf4a&canvas_pos=platform','isopen' => 1, ),
			1 => array ('title' => '电台','name' => '儿童音乐电台','image' => 'et.jpg','url' => 'http://app.baidu.com/132254?canvas_pos=platform','isopen' => 1,),
				2 => array ('title' => '电台','name' => '酷狗电台','image' => 'kg.jpg','url' => 'http://topic.kugou.com/radio/baiduNew.htm','isopen' => 1,),
				3 => array ('title' => '电台','name' => 'ting!电台','image' => 't.jpg','url' => 'http://ting.baidu.com/app/baidu/tingradio?bd_user=419899187&bd_sig=b6525ab18cda25206d9f111d9a6572c5&canvas_pos=platform','isopen' => 1,),
 			4 => array ('title' => '电台','name' => '乐酷电台','image' => 'yk.jpg','url' => 'http://music.sina.com.cn/app/baidu/index.php','isopen' => 1,),
			5 => array ('title' => '电台','name' => '酷我电台','image' => 'kw.jpg','url' => 'http://player.kuwo.cn/webmusic/webdiantai/kuwoBaiduPlay.jsp','isopen' => 1,),
 			6 => array ('name' => '虾米电台','title' => '虾米电台-新歌','url' => 'http://kuang.xiami.com/kuang/play/xiamiradio?bd_user=419899187&bd_sig=13e0af162ce677c9c67d927d490d3667&canvas_pos=platform','isopen' => 1,'image' => '1336400162.jpg',),
			7 => array ('name' => '多米电台','title' => '多米-好听的英文歌','url' => 'http://app.duomiyy.com/songplayer/baidu?lid=100004&canvas_pos=platform&bd_user=419899187&bd_sig=9ef35d1f41cf40b0c949d8e19cb65b65&canvas_pos=platform','isopen' => 1,'image' => '1336400799.jpg',),
			8 => array ('name' => 'NBA篮球音乐','title' => 'NBA篮球音乐','url' => 'http://www.xiami.com/kuang/appbaidu/id/536?bd_user=419899187&bd_sig=fce6f4e26dd8c181e6921e4a81527a7e&canvas_pos=platform','isopen' => 1,'image' => '1336399501.jpg',),
			9 => array ('name' => '音悦台','title' => '音悦台','url' => 'http://www.yinyuetai.com/webqq/index1','isopen' => 1,'image' => '1336401766.jpg',)
		 );
		$this->save_config(array('config'=>$oldTypeArray,'file'=>'old_type.php'));//保存原始数据
		$this->save_config(array('config'=>$oldTypeArray,'file'=>'type.php'));//新建数据文件
		return true;
	}

	public function uninstall(){
		return parent::uninstall();
	}

	public function adminMenu(){
		return array(
			'admin'=>'电台管理',
			'create'=>'新建电台',
			'ask'=>'使用相关',
		);
	}
}