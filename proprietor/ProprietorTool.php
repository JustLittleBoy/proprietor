<?php
use proprietor\Pdo;

class ProprietorTool
{

	/**
	 * 配置缓存
	 */
	private static $config = array();

	/**
	 * 数据库连接缓存
	 */
	private static $DB = array();

	/**
	 * 获取数据库配置
	 *
	 * @param string $db_name
	 *        	数据库配置名 默认为db
	 * @return \proprietor\PDO
	 *
	 */
	public static function getDb($db_name = "db")
	{
		if (isset(self::$DB[$db_name])) {
			return self::$DB[$db_name];
		}
		if (! isset(self::$config['DB'])) {
			$config = self::getConfig('DB', $db_name);
		} else {
			$config = self::$config['DB'][$db_name];
		}
		
		if (empty($config)) {
			self::showSysErrorPage('数据库配置不存在，请确认配置');
		}
		self::$DB[$db_name] = new Pdo($config);
		// new \Pdo('mysql:host=127.0.0.1;dbname=test;','root','root');
		// var_dump(self::$DB[$db_name]);die;
		return self::$DB[$db_name];
	}

	/**
	 * 获取配置
	 *
	 * @param string $config_file
	 *        	需要获取的配置文件 默认 config(config.php)
	 * @param string $config_name
	 *        	需要获取的配置名称
	 * @return multitype: array 配置
	 *        
	 * @todo 可以优化是否读取所有配置文件
	 */
	public static function getConfig($config_file = 'config', $config_name = '')
	{
		// 加载配置 读取
		if (is_dir(CONFIG)) {
			$dir_p = opendir(CONFIG);
			while (($file = readdir($dir_p)) !== false) {
				if ($file != '.' && $file != '..') {
					$con_pathinfo = pathinfo(CONFIG . $file);
					if (! isset(self::$config[$con_pathinfo['filename']])) {
						self::$config[$con_pathinfo['filename']] = include CONFIG . $file;
					}
				}
			}
			
			if (! $config_name && isset(self::$config[$config_file])) {
				return self::$config[$config_file];
			} elseif ($config_name && isset(self::$config[$config_file][$config_name])) {
				return self::$config[$config_file][$config_name];
			} else {
				return array();
			}
		} else {
			return array();
		}
	}


	/**
	 * 错误提示
	 */
	public static function showSysErrorPage($msg)
	{
		include_once SYS_PATH . 'proprietor/error.php';
		die();
	}
	
	/**
	 * 写日志功能
	 * 
	 * @param string $msg
	 *        	存储的消息
	 * @param string $saveLogPrev
	 *        	存储的路径
	 */
	public static function writeLog($msg, $saveLogPrev = 'errorLog')
	{
		$msg = date('Y-m-d H:i:s ') . $_SERVER['QUERY_STRING'] . str_replace(array(
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