<?php
namespace Common\Service;
use Common\Basic\CsException;

class TokenService{

	private $model;

	public function __construct() {
		$this->model = D('UserToken');
	}

	// 验证TOKEN是否有效
	public function getUserId($token) {
		$user_id = $this->model->where(['token'=> $token, 'status'=> 1])->getField('user_id');
		return $user_id ? (int)$user_id : 0;
	}

	// 生成TOKEN
	public function makeToken($user_id) {
		$this->model->where(['user_id'=> $user_id, 'status'=> 1])->save(['status'=> 0]);
		$token = md5(NOW_TIME.rand(10000, 99999));
		if ($this->model->where(['token'=> $token])->count()) {
			return $this->makeToken($user_id);
		}
		$ret = $this->model->add([
			'user_id'=> $user_id,
			'token'=> $token,
			'status'=> 1,
			'ip'=> get_client_ip(),
			'add_time'=> NOW_TIME,
		]);
		if (!$ret) {
			return $this->makeToken($user_id);
		}
		return $token;
	}
}