<?php
namespace Common\Service;
use Common\Basic\CsException;

class RoleService{

	private $model;

	public function __construct() {
		$this->model = D('UserRole');
	}

	// 获取列表
	public function getList($param = []) {
		$where = ['is_delete'=> 0];
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->model->where($where)->field($field)->select();
		$list = $list ? $list : [];
		if (!isset($param['field'])) {
			foreach ($list as $key => $value) {
				$list[$key]['rule_name'] = D('SignRule')->where(['rule_id'=> $value['rule_id']])->getField('rule_name');
			}
		}
		return $list;
	}

	// 获取详情
	public function getInfo($role_id) {
		$info = $this->model->where(['role_id'=> $role_id])->find();
		if (empty($info)) {
			return [];
		}
		return $info;
	}

	// 编辑
	public function edit($data) {
		if (empty($data['role_name']) || empty($data['rule_id'])) {
			return false;
		}
		// 判断规则是否存在
		if (!D('SignRule')->where(['rule_id'=> $data['rule_id']])->count()) {
			return false;
		}
		// 判断上级角色是否存在
		if ($data['pid'] > 0) {
			if (!$this->model->where(['role_id'=> $data['pid']])->count() || $data['pid'] == $data['role_id']) {
				return false;
			}
		}
		if (isset($data['role_id']) && $data['role_id'] > 0) {
			$ret = $this->model->where(['role_id'=> $data['role_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = time();
			return $this->model->add($data);
		}
	}

	// 删除
	public function del($role_id){
		return $this->model->where(['role_id'=> $role_id])->save(['is_delete'=> 1]);
	}
}