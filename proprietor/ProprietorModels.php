<?php
namespace proprietor;

class ProprietorModels
{
	// 数据库连接
	protected $_DB;
	
	// 数据库名称
	private $db_name;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		$this->setDb();
		
		if (method_exists($this, 'initialize')) {
			$this->initialize();
		}
		
		$this->getDb();
	}

	/**
	 * 设置数据库
	 * 
	 * @param string $db_name
	 *        	数据库名称
	 * @return \proprietor\ProprietorModels
	 */
	public function setDb($db_name = 'db')
	{
		$this->db_name = $db_name;
	}

	/**
	 * 获取数据连接
	 * 
	 * @return PDO Class
	 */
	private function getDb()
	{
		if (! $this->_DB) {
			$this->_DB = \ProprietorTool::getDb($this->db_name);
		}
		return $this->_DB;
	}

	/**
	 * 获取完整表名
	 *
	 * @return string
	 */
	private function getTableName()
	{
		$table = strtolower(self::getModel());
		
		$config = \ProprietorTool::getconfig();
		if (isset($config['TABLE_PREFIX']) && $config['TABLE_PREFIX']) {
			$table = $config['TABLE_PREFIX'] . $table;
		}
		
		return $table;
	}

	/**
	 * 获得模型对应的表名（不带表前缀）
	 *
	 * @throws \Exception
	 * @return string $model_name (Test_User)
	 */
	private function getModel()
	{
		$child_class_name = get_class($this);
		// var_dump($child_class_name);
		// 命名空间不同处理 app\Model\TestModel | TestModel
		$sub_flag = strrpos($child_class_name, '\\');
		
		$sub_flag = $sub_flag === false ? 0 : $sub_flag + 1;
		
		// 获得截取后的类名 TestModel
		$child_class_name = substr($child_class_name, $sub_flag, strlen($child_class_name));
		
		// 处理成表名 Test
		// $child_class_name=str_replace ('Model','',$child_class_name);
		$child_class_name = substr($child_class_name, 0, - 5);
		
		preg_match_all('/([A-Z]{1}[a-z0-9]{0,})/', $child_class_name, $result);
		
		if (! $result) {
			throw new \Exception('Model name unlegal');
		}
		
		// 获得大写字母间隔的片段 Test
		$name_result = $result[0];
		// 处理成表名
		$model_name = '';
		if (count($name_result) == 1) {
			$model_name = $name_result[0];
			return $model_name;
		}
		
		foreach ($name_result as $item) {
			$model_name = $model_name . $item . '_';
		}
		$model_name = substr($model_name, 0, - 1);
		return $model_name;
	}

	/**
	 * 查找所有数据
	 * 
	 * @param string $sql
	 *        	查询 where 条件
	 * @param array $bind
	 *        	绑定的数据
	 */
	public static function find($sql = '', $bind = array())
	{
		$model = new static();
		
		return $model->_find($sql, $bind, false);
	}

	/**
	 * 查找一条数据
	 * 
	 * @param string $sql
	 *        	查询 where 条件
	 * @param array $bind
	 *        	绑定的数据
	 */
	public static function findFirst($sql = '', $bind = array())
	{
		$model = new static();
		
		return $model->_find($sql, $bind, true);
	}

	/**
	 * 新建数据
	 * 
	 * @param 插入数据 $data        	
	 * @param 绑定数据 $bind(绑定插入时使用)        	
	 */
	public static function create($data = array(), $bind = array())
	{
		$model = new static();
		
		return $model->_create($data, $bind);
	}

	/**
	 * 新建数据
	 * 
	 * @param 要插入的数据 $data        	
	 * @param unknown $bind        	
	 * @return boolean|lastInsertId
	 */
	private function _create($data = array(), $bind = array())
	{
		$table = $this->getTableName();
		
		$db = $this->_DB;
		
		if (is_array($data) && ! empty($data)) {
			$sql = 'INSERT INTO ' . $table . ' ';
			$param = '';
			$values = '';
			foreach ($data as $key => $val) {
				$param = $param . '`' . $key . '`,';
				$values = $values . ':' . $key . ',';
			}
			
			$param = '(' . substr($param, 0, strlen($param) - 1) . ')';
			
			$values = '(' . substr($values, 0, strlen($values) - 1) . ');';
			
			$sql = $sql . $param . ' VALUES ' . $values;
			
			$result = $db->insert($sql, $data);
		} else {
			// 扩展其他方式
			return false;
		}
		
		if ($result === false) {
			$this->wirteSQLLog(json_encode($db->errorInfo()));
			return false;
		}
		
		return $result;
	}

	/**
	 * 执行查找数据的方法
	 * 
	 * @param string $sql
	 *        	查询语句
	 * @param unknown $bind
	 *        	绑定数据
	 * @param string $is_first
	 *        	是否返回首条数据
	 * @return $result boolean|array
	 */
	private function _find($sql = '', $bind = array(), $is_first = false)
	{
		$table = $this->getTableName();
		
		$db = $this->_DB;
		
		$limit = $is_first ? ' LIMIT 1' : '';
		
		if ($sql) {
			$sql = 'SELECT * FROM `' . $table . '` WHERE ' . $sql . $limit;
		} else {
			$sql = 'SELECT * FROM `' . $table . '`' . $sql . $limit;
		}
		
		$result = $db->fetchAll($sql, $bind);
		
		if ($result === false) {
			$this->wirteSQLLog(json_encode($db->errorInfo()));
			return false;
		}
		if($is_first && $result){
			return $result[0];
		}
		return $result;
	}

	/**
	 * 记录错误信息
	 * 
	 * @param string $log
	 *        	错误日志信息
	 * @return boolean
	 */
	private function wirteSQLLog($log)
	{
		$config = \ProprietorTool::getconfig();
		$errorLog = '';
		if (isset($config['DB_ERROR_LOG']) && $config['DB_ERROR_LOG']) {
			
			$errorLog = $config['DB_ERROR_LOG'];
		}
		\ProprietorTool::writeLog($log, $config['DB_ERROR_LOG']);
		return true;
	}
}