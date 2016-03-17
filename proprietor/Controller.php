<?php
namespace proprietor;

use proprietor\ProprietorView;


class Controller
{

	protected $view = null;

	private static $_instance = array();
	
	public function __construct()
	{
		if (method_exists($this, 'initialize')) {
			$this->initialize();
		}
	}

	/**
	 * 获取静态变量
	 * 
	 * @param unknown $name        	
	 */
	public function __get($name)
	{
		switch ($name) {
			case '_db':
				//@todo 使用getdb方法
				$this->_db = \ProprietorTool::getDb();
				return $this->_db;
			case '_view':
				$this->_view = new ProprietorView();
				return $this->_view;
			case '_config':
				if(file_exists(CONFIG)){
					//@todo 处理成读取所有config文件下的所有配置
					$this->_config = \ProprietorTool::getconfig();
				}else{
					$this->_config =array();
				}
				return $this->_config;
			default:
				$this->{$name} = '';
		}
		return $this->{$name};
	}
	
	public function isPost(){
		return $_SERVER["REQUEST_METHOD"] =="POST";
	}
	
	public function isAjax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
	}
	
	public function assign($key,$val){
		return $this->_view->assign($key,$val);
	}
	
	public function display($view=''){
		$this->_view->display($view);
	}
	
	public function getPost($name,$reg='',$default=''){
		
		$val=isset($_POST[$name])?$_POST[$name]:$default;
		
		//处理val
		
		return $val;
	}
	
	public function getQuery($name,$reg='',$default=''){
		
		$val=isset($_GET[$name])?$_GET[$name]:$default;
	
		//处理val
	
		return $val;
	}
	
	public function getParam($name,$reg='',$default=''){
	
		if(isset($_GET[$name])){
			$val = $_GET[$name];
		}
		if(isset($_POST[$name])){
			$val = $_POST[$name];
		}
		if(isset($_COOKIE[$name])){
			$val = $this->_COOKIE[$name];
		}
		if(isset($_SERVER[$name])){
			$val = $_SERVER[$name];
		}
		if(isset($_ENV[$name])){
			$val = $_ENV[$name];
		}
		if($val){
			return $val;
		}
		return $default;
	}
	
}
