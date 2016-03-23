<?php
namespace app\Models;

use proprietor\ProprietorModels;

class UserModel extends ProprietorModels{
	
	public function initialize(){
		//选择数据库
		$this->setDb('db');
	}

	public function createUser($username,$email,$password){
		// 创建数据
		
		$this->_DB->begin();
		
		$uid = self::create(array(
			'name' => $username,
			'email' => $email,
			'password' => md5($password)
		));
		if(!$uid){
			return false;
		}
		$goldid = GoldModel::create(array(
			'u_id' => $uid,
			'gold' => 0,
			'type' => 'integral'
		));
		
		if($uid && $goldid){

			$this->_DB->commit();
			
			return true;
		}
		
		return false;
	}
	
}