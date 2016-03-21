<?php

/**
 * 专门用作路由分发
 * view 专做视图加载
 * model 专做模型操作
 * @author wangchao
 *
 */
class Proprietor
{

	public static $_autoload = array();

	public static function Init()
	{
		define('DS', DIRECTORY_SEPARATOR); // 兼容目录 '/','\'
		define('SYS_PATH', realpath('..') . DS); // 系统根目录
		define('VIEW_PATH', APP_PATH . '/view/'); // 视图路径
		define('CONFIG', APP_PATH . '/config/'); // 配置路径
		define('CONTROLLER_PATH', APP_PATH . '/controller/'); // 控制器路径
		define('MODELS_PATH', APP_PATH . '/models/'); // 模型路径
		define('LIBRARL_PATH', APP_PATH . '/library/'); // 模型路径
		define('CACHE_PATH', APP_PATH . '/cache/'); // 缓存路径
		define('VIEWPOSTFIX', '.html');
		include_once __DIR__ . '/Pdo.php';
		include_once __DIR__ . '/Controller.php';
		include_once __DIR__ . '/ProprietorTool.php';
		include_once __DIR__ . '/ProprietorView.php';
		include_once __DIR__ . '/ProprietorModels.php';
		// 自动注册类
		spl_autoload_register(array(
			'Proprietor',
			'classAutoLoader'
		));
		
		// 注册错误屏蔽
		if (! DEBUG) {
			set_exception_handler(array(
				'Proprietor',
				'exceptionHandler'
			));
			set_error_handler(array(
				'Proprietor',
				'errorHandler'
			), E_ALL);
		}
		
		register_shutdown_function(array(
			'Proprietor',
			'shutdownCatchError'
		));
		
		self::autoroutes(); // 路由
	}

	/**
	 * 自动路由
	 */
	public static function autoroutes()
	{
		
		/**
		 * 1.解析路由
		 * (1).com/a.html(.php)
		 * (2).com/a/f/param1_name/param1_val...
		 * (3).com/a/f.html(.php)?v_n=v_v&v_n2=v_v2....
		 */
		$http_host = $_SERVER['HTTP_HOST'];
		// 添加本地访问URL解析支持
		$sub_request_str = '';
		if ($http_host == 'localhost' || $http_host == '127.0.0.1') {
			
			$url_in = realpath('.');
			
			$url_in_arr = explode(DS, $url_in);
			
			if ($url_in_arr && ! strpos($url_in_arr[count($url_in_arr) - 1], ':')) {
				$sub_request_str = array_pop($url_in_arr);
			}
		}
		
		$url = $_SERVER['REQUEST_URI'];
		
		if ($sub_request_str) {
			$url = substr($url, strpos($url, $sub_request_str) + strlen($sub_request_str));
		}
		
		if ($url{0} == '/') {
			$url = substr($url, 1);
		}
		
		if (substr($url, - 1, 1) == '/') {
			$url = substr($url, 0, strlen($url) - 1);
		}
		
		$url_array = explode('/', $url);
		
		$count_url_para = count($url_array);
		
		$controller = 'Index';
		$action = 'index';
		$param = array();
		if ($count_url_para == 1) {
			// .com/a.html(.php)
			$pre_result = preg_match('/^([A-Za-z0-9]+)(\.php|\.html)*/', $url_array[0], $html_url);
			if ($pre_result) {
				$controller = strtolower($html_url[1]);
				$controller = ucfirst($controller);
				$param = $_GET;
			}
		} elseif ($count_url_para == 2) {
			// (3).com/a/f.html(.php)?v_n=v_v&v_n2=v_v2....
			// 同样解析
			$pre_result1 = preg_match('/^([A-Za-z0-9]+)$/', $url_array[0], $html_url1);
			if ($pre_result1) {
				// 控制器
				$controller = strtolower($html_url1[1]);
				$controller = ucfirst($controller);
				$pre_result2 = preg_match('/^([A-Za-z0-9]+)(\.php|\.html)*/', $url_array[1], $html_url2);
				// 方法
				if ($pre_result2) {
					$action = $html_url2[1];
					$param = $_GET;
				} else {
					\ProprietorTool::showSysErrorPage('链接有误，方法名不存在');
				}
			} else {
				\ProprietorTool::showSysErrorPage('链接有误，控制器不存在');
			}
		} elseif ($count_url_para > 2) {
			// (2).com/a/f/param1_name/param1_val...
			$pre_result1 = preg_match('/^([A-Za-z0-9]+)$/', $url_array[0], $html_url1);
			if ($pre_result1) {
				// 控制器
				$controller = strtolower($html_url1[1]);
				$controller = ucfirst($controller);
				
				$pre_result2 = preg_match('/^([A-Za-z0-9]+)(\.php|\.html)*([\S\s])?/', $url_array[1], $html_url2);
				// 方法
				if ($pre_result2) {
					$action = $html_url2[1];
				} else {
					\ProprietorTool::showSysErrorPage('链接有误，方法名不存在');
				}
			} else {
				\ProprietorTool::showSysErrorPage('链接有误，控制器不存在');
			}
			$param = substr($url, (strpos($url, $html_url2[0]) + strlen($html_url2[0]) + 1));
			$param = explode('/', $param);
		}
		
		// 自动加载类
		
		// 获取当前的控制器和方法
		define('NOWCLASS', $controller);
		define('CLASSCONTROLLER', $controller . 'Controller');
		define('ACTION', $action . 'Action');
		
		$incude_php = CONTROLLER_PATH . CLASSCONTROLLER . '.php';
		
		include $incude_php;
		
		if (defined('NAMESAPCE')) {
			
			$namesapce_con = NAMESAPCE . '\\Controller\\' . CLASSCONTROLLER;
			
			$class = new $namesapce_con();
		} else {
			
			$class = new CLASSCONTROLLER();
		}
		// 添加参数支持
		@call_user_func(array(
			$class,
			ACTION
		), $param) or die('请求链接有误，请确认方法名[' . $action . ']或控制器[' . $controller . ']存在');
	}

	/**
	 * 自动加载类，将文件以键值对方式存储
	 * 
	 * @todo 加载进来的类需要存在数组中
	 */
	public static function autoload($path = '')
	{
		
		// 引入控制器
		if (is_dir(CONTROLLER_PATH)) {
			$dir_p = opendir(CONTROLLER_PATH);
			while (($file = readdir($dir_p)) !== false) {
				if ($file != '.' && $file != '..') {
					$con_pathinfo = pathinfo(CONTROLLER_PATH . $file);
					if (defined('NAMESAPCE')) {
						self::$_autoload[NAMESAPCE][$con_pathinfo['filename']] = CONTROLLER_PATH . $file;
					} else {
						self::$_autoload[$con_pathinfo['filename']] = CONTROLLER_PATH . $file;
					}
				}
			}
		}
		
		// 加载模型
		if (is_dir(MODELS_PATH)) {
			$dir_p = opendir(MODELS_PATH);
			while (($file = readdir($dir_p)) !== false) {
				if ($file != '.' && $file != '..') {
					$con_pathinfo = pathinfo(MODELS_PATH . $file);
					if (defined('NAMESAPCE')) {
						self::$_autoload[NAMESAPCE][$con_pathinfo['filename']] = MODELS_PATH . $file;
					} else {
						self::$_autoload[$con_pathinfo['filename']] = MODELS_PATH . $file;
					}
				}
			}
		}
		
		// 加载类库
		if (is_dir(LIBRARL_PATH)) {
			$dir_p = opendir(LIBRARL_PATH);
			while (($file = readdir($dir_p)) !== false) {
				if ($file != '.' && $file != '..') {
					$con_pathinfo = pathinfo(LIBRARL_PATH . $file);
					if (defined('NAMESAPCE')) {
						self::$_autoload[NAMESAPCE][$con_pathinfo['filename']] = LIBRARL_PATH . $file;
					} else {
						self::$_autoload[$con_pathinfo['filename']] = LIBRARL_PATH . $file;
					}
				}
			}
		}
	}

	/**
	 * 自动加载类
	 * 
	 * @param unknown $name        	
	 */
	public static function classAutoLoader($name)
	{
		if (! self::$_autoload) {
			self::autoload();
		}
		
		$name_arr = explode("\\", $name);
		$name = array_pop($name_arr);
		
		// 自动加载控制器
		// var_dump(self::$_autoload);die;
		if (defined('NAMESAPCE')) {
			
			if (! self::$_autoload[NAMESAPCE][$name]) {
				exit();
			}
			
			include self::$_autoload[NAMESAPCE][$name];
		} else {
			if (! self::$_autoload[$name]) {
				exit();
			}
			
			include self::$_autoload[$name];
		}
	}

	/**
	 * 记录警告信息
	 * 
	 * @param Exception $e        	
	 */
	public static function exceptionHandler(Exception $e)
	{
		self::log($e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode(), 'exception');
		self::showMsg(array(
			'msg' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'code' => $e->getCode()
		), 'exception');
	}

	/**
	 * 抓住致命错误
	 */
	public static function shutdownCatchError()
	{
		$_error = error_get_last();
		
		if ($_error && in_array($_error['type'], array(
			1,
			4,
			16,
			64,
			256,
			4096,
			E_ALL
		))) {
			
			$error = '致命错误:' . $_error['message'] . '</br>';
			
			$error .= '文件:' . $_error['file'] . '</br>';
			
			$error .= '在第' . $_error['line'] . '行</br>';
			
			self::showSysErrorPage($error);
		}
	}

	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		self::log($errstr, $errfile, $errline, $errno, 'SysError');
	}

	public static function log($msg, $errfile = '', $errline = '', $errno = '', $saveLogPrev = 'SysError')
	{
		$msg = date('Y-m-d H:i:s ') . $_SERVER['QUERY_STRING'] . '|' . $errfile . '|' . $errline . '|' . $errno . '|' . str_replace(array(
			"\r",
			"\n"
		), array(
			'\\r',
			'\\n'
		), $msg) . "\r\n";
		
		$log_path = APP_PATH . '/cache/log';
		
		if (! file_exists($log_path)) {
			mkdir($log_path, '0777', true);
		}
		
		error_log($msg, 3, $log_path . '/' . $saveLogPrev . '.log');
	}
}
