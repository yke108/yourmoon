<?php
namespace Common\Service;
use Common\Basic\CsException;

class AdminService{
	private $model;

	public function __construct() {
		$this->model = D('Admin');
	}

	public function getAdminInfo($admin_id) {
		$ret = $this->model->where(['admin_id'=> $admin_id])->find();
		return $ret ? $ret : [];
	}

	public function login($param) {
		if (empty($param['username']) || empty($param['password'])) {
			throw new CsException("帐号或者密码不能为空", 400);
		}
		$adminInfo = $this->model->where(['username'=> $param['username'], 'is_delete'=> 0])->find();
		if (empty($adminInfo)) {
			throw new CsException("帐号不存在", 400);
		}
		if ($this->model->password($param['password'], $adminInfo['salt']) != $adminInfo['password']) {
			throw new CsException("密码不正确", 400);
		}
		$salt = rand(1000, 9999);
		$password = md5(md5($param['password']) . $salt);
		$this->model->where(['username'=> $param['username']])->save(['salt'=> $salt, 'password'=> $password]);
		return $adminInfo;
	}

	// 获取列表
	public function getList() {
		$where = ['is_delete'=> 0];
		$list = $this->model->where($where)->order('admin_id asc')->select();
		foreach ($list as $key => $value) {
			$role_label = '';
			if (!empty($value['role_ids'])) {
				$role_name = D('AdminRole')->where(['role_id'=> ['in', $value['role_ids']]])->getField('role_name', true);
				if (!empty($role_name)) {
					$role_label = implode(',', $role_name);
				}
			}
			$list[$key]['role_label'] = $role_label;
		}
		$list = $list ? $list : [];
		return $list;
	}

	// 编辑
	public function edit($data) {
		if (!$this->model->create($data)) {
			throw new CsException($this->model->getError());
		}
		if (isset($data['password']) && !empty($data['password'])) {
			$data['salt'] = rand(1000, 9999);
			$data['password'] = $this->model->password($data['password'], $data['salt']);
		}
		if (isset($data['admin_id']) && $data['admin_id'] > 0) {
			$ret = $this->model->where(['admin_id'=> $data['admin_id']])->save($data);
			if ($ret === false) {
				throw new CsException("编辑失败");
			}
		} else {
			$data['add_time'] = NOW_TIME;
			$ret = $this->model->add($data);
			if (!$ret) {
				throw new CsException("添加失败");
			}
		}
		return $ret;
	}

	// 删除
	public function del($admin_id){
		return $this->model->where(['admin_id'=> $admin_id])->save(['is_delete'=> 1]);
	}

	// 删除
	public function setStatus($admin_id, $status){
		$ret = $this->model->where(['admin_id'=> $admin_id])->save(['status'=> $status]);
		return $ret !== false;
	}
}