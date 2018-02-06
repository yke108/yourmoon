<?php
namespace Common\Service;
use Common\Basic\CsException;
use Common\Basic\UserConst;

class UserService{

	private $model;

	public function __construct() {
		$this->model = D('User');
	}

	// 获取列表
	public function getList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;

		$where = ['is_delete'=> 0];
		if (isset($param['start']) && !empty($param['start'])) {
			$where['add_time'][] = ['egt', strtotime($param['start'])];
		}
		if (isset($param['end']) && !empty($param['end'])) {
			$where['add_time'][] = ['lt', strtotime($param['end'])+86400];
		}
		if (isset($param['real_name']) && !empty($param['real_name'])) {
			$where['real_name'] = ['like', "%{$param['real_name']}%"];
		}
		if (isset($param['phone']) && !empty($param['phone'])) {
			$where['phone'] = $param['phone'];
		}
		if (isset($param['shop_no']) && !empty($param['shop_no'])) {
			$where['shop_no'] = $param['shop_no'];
		}
		if (isset($param['status'])) {
			$where['status'] = $param['status'];
		}
		$count = $this->model->where($where)->count();
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->model->where($where)->field($field)->page($param['p'], $param['pagesize'])->order('user_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			isset($value['shop_no']) && $list[$key]['shop_name'] = D('Shop')->where(['shop_no'=> $value['shop_no']])->getField('shop_name');
			isset($value['rule_id']) && $list[$key]['rule_name'] = D('SignRule')->where(['rule_id'=> $value['rule_id']])->getField('rule_name');
			isset($value['status']) && $list[$key]['status_name'] = UserConst::$statusList[$value['status']];
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取详情
	public function getInfo($user_id, $byField = "user_id") {
		$info = $this->model->where([$byField=> $user_id, 'is_delete'=> 0])->find();
		if (empty($info)) {
			return [];
		}
		$info['shop_name'] = D('Shop')->where(['shop_no'=> $info['shop_no']])->getField('shop_name');
		$info['long_avatar'] = picurl($info['avatar']);
		$info['status_name'] = UserConst::$statusList[$info['status']];
		return $info;
	}

	// 编辑
	public function edit($data) {
		if (!$this->model->create($data)) {
			throw new CsException($this->model->getError());
		}
		if (isset($data['user_no']) && isset($data['up_no'])) {
			if ($data['user_no'] == $data['up_no']) {
				throw new CsException("员工编号和上级编号不能相同");
			}
		}
		if (isset($data['user_id']) && $data['user_id'] > 0) {
			if (isset($data['password']) && !empty($data['password'])) {
				$data['salt'] = rand(1000,9999);
				$data['password'] = $this->model->createPassword($data['password'], $data['salt']);
			} else {
				unset($data['password']);
			}
			$ret = $this->model->where(['user_id'=> $data['user_id']])->save($data);
			if ($ret === false) {
				throw new CsException('编辑失败');
			}
		} else {
			$data['add_time'] = time();
			$data['status'] = UserConst::PASS_STATUE;
			$data['salt'] = rand(10000,9999);
			$data['password'] = $this->model->createPassword($data['password'], $data['salt']);
			$ret = $this->model->add($data);
			if (!$ret) {
				throw new CsException('注册失败');
			}
			return $ret;
		}
	}

	// 删除
	public function del($user_id){
		return $this->model->where(['user_id'=> $user_id])->save(['is_delete'=> 1, 'delete_time'=> NOW_TIME]);
	}

	// 改变状态
	public function updateStatus($user_id, $status){
		if (!isset(UserConst::$statusList[$status])) {
			return false;
		}
		return $this->model->where(['user_id'=> $user_id])->save(['status'=> $status]);
	}

	// 获取全部用户和规则
	public function getAllUserRule() {
		$userList = $this->model->where(['is_delete'=> 0, 'status'=> 1])->field('user_id,role_id')->select();
		if (empty($userList)) {
			return [];
		}
		foreach ($userList as $key => $value) {
			$rule_id = D('UserRole')->where(['role_id'=> $value['role_id']])->getField('rule_id');
			$ruleInfo = Service('Rule')->getInfo($rule_id);
			unset($ruleInfo['is_delete'], $ruleInfo['add_time']);
			$userList[$key]['rule'] = $ruleInfo;
		}
		return $userList;
	}

	// 登录
	public function checkPassword($phone, $password) {
		$userInfo = $this->getInfo($phone, 'user_no');
		if (empty($userInfo)) {
			$userInfo = $this->getInfo($phone, 'phone');
		}
		if (empty($userInfo)) {
			return false;
		}
		if ($this->model->createPassword($password, $userInfo['salt']) != $userInfo['password']) {
			return false;
		}
		return $userInfo;
	}

	// 获取下级用户ID
	public function getLowerUserIds($user_no) {
		$userIds = $this->model->where(['up_no'=> $user_no])->getField('user_id', true);
		return $userIds ? $userIds : [];
	}

	// 获取所有用户编号
	public function getAllUserNo() {
		$ret = $this->model->getField('user_no,1');
		return $ret ? $ret : [];
	}

	// 根据用户编号删除用户
	public function delByUserNo($user_no_list) {
		return $this->model->where(['user_no'=> ['in', $user_no_list]])->save(['is_delete'=> 1, 'delete_time'=> NOW_TIME]);
	}

	// 更新
	public function updateUser($data) {
		return $this->model->where(['user_no'=> $data['user_no']])->save($data);
	}

	// 添加
	public function addUser($data) {
		$data['status'] = UserConst::PASS_STATUE;
		$data['salt'] = rand(1000,9999);
		$data['password'] = $this->model->createPassword(md5($data['user_no']), $data['salt']);
		$data['add_time'] = NOW_TIME;
		return $this->model->add($data);
	}
}