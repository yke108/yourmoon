<?php
namespace Common\Service;
use Common\Basic\CsException;
use Common\Basic\ApplyConst;

class ApplyService{

	private $model;

	public function __construct() {
		$this->model = D('SignApply');
	}

	// 获取列表
	public function getList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;
		$where = [];
		if (isset($param['start']) && !empty($param['start'])) {
			$where['add_time'][] = ['egt', strtotime($param['start'])];
		}
		if (isset($param['end']) && !empty($param['end'])) {
			$where['add_time'][] = ['lt', strtotime($param['end'])+86400];
		}
		if (isset($param['user_id']) && !empty($param['user_id'])) {
			$where['user_id'][] = $param['user_id'];
		}
		if (isset($param['status'])) {
			$where['status'] = $param['status'];
		}
		if (isset($param['real_name']) && !empty($param['real_name'])) {
			$userIds = D('User')->where(['real_name'=> ["like", "%{$param['real_name']}%"]])->getField('user_id', true);
			$where['user_id'][] = (!empty($userIds) ? ['in', $userIds] : -1);
		}
		if (isset($param['shop_name']) && !empty($param['shop_name'])) {
			$shopNos = D('Shop')->where(['shop_name'=> ["like", "%{$param['shop_name']}%"]])->getField('shop_no', true);
			$userIds = [];
			if (!empty($shopNos)) {
				$userIds = D('User')->where(['shop_no'=> ['in', $shopNos]])->getField('user_id', true);
			}
			$where['user_id'][] = (!empty($userIds) ? ['in', $userIds] : -1);
		}
		$count = $this->model->where($where)->count();
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->model->where($where)->field($field)->page($param['p'], $param['pagesize'])->order('apply_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			isset($value['user_id']) && $list[$key]['real_name'] = D('User')->where(['user_id'=> $value['user_id']])->getField('real_name');
			isset($value['audit_user']) && $list[$key]['audit_name'] = (string)D('User')->where(['user_id'=> $value['audit_user']])->getField('real_name');
			isset($value['status']) && $list[$key]['status_label'] = ApplyConst::$statusList[$value['status']];
            isset($value['image']) && $list[$key]['images'] = picurls($value['image']);
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取详情
	public function getInfo($apply_id) {
		$info = $this->model->where(['apply_id'=> $apply_id])->find();
		if (empty($info)) {
			return [];
		}
		$info['real_name'] = D('User')->where(['user_id'=> $info['user_id']])->getField('real_name');
		$info['status_label'] = ApplyConst::$statusList[$info['status']];
		return $info;
	}

	// 判断那天有没申请过
	public function isExist($user_id, $date) {
		$ret = $this->model->where(['user_id'=> $user_id, 'date'=> $date])->count();
		return $ret ? true : false;
	}

	// 编辑
	public function edit($data) {
		if (isset($data['apply_id']) && $data['apply_id'] > 0) {
			$ret = $this->model->where(['apply_id'=> $data['apply_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = time();
			return $this->model->add($data);
		}
	}

	// 批量更新未读申请记录
    public function updateRead($apply_id_list) {
	    if (empty($apply_id_list)) {
	        return false;
        }
        return $this->model->where(['apply_id'=> ['in', $apply_id_list], 'is_read'=> 0])->save(['is_read'=> 1]);
    }

    // 获取数量
    public function getCount($map) {
        $count = $this->model->where($map)->count();
        return $count ? $count : 0;
    }
}