<?php
class IndexAction extends Action{
	
	public function _initialize() {
	}
	
	public function index() {
		U('taobaoke/User/index', '', true);
	}
}
