<?php
namespace Common\Service;
use Common\Basic\CsException;

class VerifyService{

	private $model;

	public function __construct() {
		$this->model = D('PhoneVerify');
	}

	// 添加
	public function add($phone, $verify_code, $type = 1) {
		$this->model->where(['phone'=> $phone, 'type'=> $type])->delete();
		$data['type'] = $type;
		$data['phone'] = $phone;
		$data['verify_code'] = md5($verify_code);
		$data['expire_time'] = NOW_TIME + 600;
		$data['add_time'] = NOW_TIME;
		return $this->model->add($data);
	}

	// 验证
	public function verify($phone, $verify_code, $type = 1) {
		$ret = $this->model->where([
			'phone'=> $phone,
			'verify_code'=> md5($verify_code),
			'type'=> $type,
			'expire_time'=> ['gt', NOW_TIME],
		])->count();
		return $ret ? true : false;
	}
}