<?php
namespace proprietor;

class Pdo extends \PDO
{

	protected $_errorInfo;

	protected $_errorCode;

	public function __construct($config)
	{
		if (empty($config)) {
			\Proprietor::showSysErrorPage('数据库配置错误');
		}
		$host = $config['host'];
		$dbname = $config['dbname'];
		$username = $config['username'];
		$password = $config['password'];
		$options = isset($config['options']) ? $config['options'] : array();
		// 数据库连接
		if ($host == 'localhost') {
			$dns = 'mysql:host=localhost';
		} else 
			if ($host[0] == '/') {
				$dns = 'mysql:unix_socket=' . $host;
			} else 
				if ($pos = strrpos($host, ':')) {
					$dns = 'mysql:host=' . substr($host, 0, $pos) . ';port=' . substr($host, $pos + 1);
				} else {
					$dns = 'mysql:host=' . $host;
				}
		
		if ($dbname) {
			$dns .= ';dbname=' . $dbname;
		}
		if (empty($options)) {
			parent::__construct($dns, $username, $password);
			return true;
		}
		$sql = null;
		if (isset($options['charset'])) {
			if (strtoupper($options['charset']) == 'UTF-8') {
				$options['charset'] = 'utf8';
			}
			$sql = "SET NAMES '" . $options['charset'] . "'";
			unset($options['charset']);
		}
		parent::__construct($dns, $username, $password, $options);
		if ($sql) {
			$this->exec($sql);
		}
	}

	/**
	 * 更新
	 * 
	 * @param 语句 $sql        	
	 * @param 绑定的数据 $bind        	
	 * @return boolean
	 */
	public function update($sql, $bind = array())
	{
		$result = $this->_prepare($sql, $bind);
		return $result ? $result->rowCount() : false;
	}

	/**
	 * 插入
	 * 
	 * @param 语句 $sql        	
	 * @param 绑定的数据 $bind        	
	 * @return boolean
	 */
	public function insert($sql, $bind = array())
	{
		$result = $this->_prepare($sql, $bind);
		return $result ? $this->lastInsertId() : false;
	}

	/**
	 * 删除
	 * 
	 * @param 条件 $where        	
	 * @param 绑定的数据 $bind        	
	 * @return boolean
	 */
	public function delete($where, $bind = array())
	{
		$result = $this->_prepare($where, $bind);
		return $result ? $result->rowCount() : false;
	}

	/**
	 * 查询一条
	 */
	public function fetch($where, $bind = array())
	{
		$result = $bind ? $this->_prepare($where, $bind) : $this->query($where);
		return $result ? $result->fetch(PDO::FETCH_ASSOC) : $result;
	}

	/**
	 * 查询全部
	 */
	public function fetchAll($where, $bind = array())
	{
		$result = $bind ? $this->_prepare($where, $bind) : $this->query($where);
		return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : $result;
	}

	public function begin()
	{
		parent::beginTransaction();
	}

	public function commit()
	{
		parent::commit();
	}

	public function rollBack()
	{
		parent::rollBack();
	}

	private function _prepare($condition, $bind = array())
	{
		$sth = $this->prepare($condition);
		if (strpos($condition, '?')) {
			$i = 1;
			foreach ($bind as $v) {
				$sth->bindValue($i ++, $v);
			}
		} else {
			foreach ($bind as $k => $v) {
				if ($k && $k[0] == ':') {
					$sth->bindValue($k, $v);
				} else {
					$sth->bindValue(':' . $k, $v);
				}
			}
		}
		
		if ($sth->execute()) {
			return $sth;
		}
		// var_dump($sth->errorInfo());echo $sql,print_r($arr);die;
		$this->setError($sth->errorInfo(), $sth->errorCode());
		return false;
	}

	public function query($sql)
	{
		$result = parent::query($sql);
		if ($result === false) {
			// var_dump($this->errorInfo());
			$this->setError($this->errorInfo(), $this->errorCode());
			return false;
		}
		
		return $result;
	}

	public function setError($info, $code)
	{
		$this->_errorInfo = $info;
		$this->_errorCode = $code;
		return $this;
	}

	public function errorInfo()
	{
		return $this->_errorInfo ? $this->_errorInfo : parent::errorInfo();
	}

	public function errorCode()
	{
		return $this->_errorCode ? $this->_errorCode : parent::errorCode();
	}
}