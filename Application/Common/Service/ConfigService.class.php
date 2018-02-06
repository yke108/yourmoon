<?php
namespace Common\Service;
use Common\Basic\CsException;

class ConfigService{

	private $model;

	public function __construct() {
		$this->model = D('Config');
	}

	// 获取列表
	public function getList($param) {
		$list = $this->model->select();
		$list = $list ? $list : [];
		return $list;
	}

	// 获取配置
	public function getConfig($config_key) {
		$ret = $this->model->where(['config_key'=> $config_key])->getField('config_value');
		return $ret ? $ret : '';
	}

	// 编辑
	public function edit($data) {
		if (empty($data['config_key']) || !isset($data['config_value'])) {
			return false;
		}
		$ret = $this->model->where(['config_key'=> $data['config_key']])->save(['config_value'=> $data['config_value']]);
		return $ret !== false;
	}
}