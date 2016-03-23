<?php
namespace app\Controller;

use proprietor\Controller;

use app\Models\TestModel;

class IndexController extends BaseController
{
	public function indexAction(){

		$this->display('index/index.html');
		
	}

}