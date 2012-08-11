<?php

class ChangeStyleHooks extends Hooks {

    public static $defaultStyle = array(0=>"default",1=>"black",2=>"green",3=>"yellow",4=>"pink");

    public function init() {

    }

    public function home_account_tab($param) {
        $param['menu'][] = array(
            'act' => 'changestyle',
            'name' => '更换主题',
            'param' => array(
                'addon' => 'ChangeStyle',
                'hook' => 'home_account_show'
            )
        );
    }

    public function home_account_show() {
        if ('changestyle' != ACTION_NAME) {
            return;
        }

        $this->assign('defaultStyle',self::$defaultStyle);

        $this->display('home_changestyle_show');
    }

    public function home_index_right_top() {
        $this->assign('defaultStyle',self::$defaultStyle);
        $this->display('changestyle');
    }

    public function config_changestyle(){
        return false;
    }

    public function public_head($param) {
		$param['uid'] = empty($param['uid'])?$this->mid:$param['uid'];
        $map['uid'] = $param['uid'];



        $res =  M('user_changestyle')->where($map)->field('classname,diybg,diybgcolor')->find();

        $classname = $res['classname'];
        $diybg = $res['diybg'];
        $diybgcolor = $res['diybgcolor'];

        echo '<link href="' . $this->htmlPath . '/html/base.css" rel="stylesheet" type="text/css" />';

        if ($classname != null) {
            echo '<link href="' . $this->htmlPath . '/html/' . $classname . '.css" rel="stylesheet" type="text/css" />';
        }
        if ($diybg && !$classname) {
            echo '<style type="text/css">
                  .page_home{background-image: url(' . $diybg . ');background-color: ' . $diybgcolor . ';}
                  </style>';
        }
    }



    public function savestyle() {

        $data['uid'] = $this->mid;
        $data['classname'] = h(t($_POST['classname']));
        $data['diybg'] = h(t($_POST['diybg']));
        $data['diybgcolor'] = h(t($_POST['diybgcolor']));

        //判断重名
        $map = array('uid' => $this->mid);
        $_gid = M('user_changestyle')->getField('uid', $map);
        if ($_gid) {
            $res = M('user_changestyle')->where('uid=' . t($_gid))->save($data);
            ;
        } else {
            $res = M('user_changestyle')->add($data);
        }

        if (false !== $res) {
            echo 1;
        } else {
            echo 0;
        }
    }

}