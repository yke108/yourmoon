<?php
namespace Common\Service;
use Common\Basic\CsException;

class RuleService{

	private $model;

	public function __construct() {
		$this->model = D('SignRule');
	}

	// 获取列表
	public function getList($param = []) {
		$where = ['is_delete'=> 0];
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->model->where($where)->field($field)->order('rule_id asc')->select();
		$list = $list ? $list : [];
		$weekarray1 = array("0","1","2","3","4","5","6");
		$weekarray2 = array("日","一","二","三","四","五","六");
		foreach ($list as $key => $value) {
			isset($value['days']) && $list[$key]['days'] = str_replace($weekarray1, $weekarray2, $value['days']);
			isset($value['times']) && $list[$key]['times'] = json_decode($value['times'], true);
		}
		return $list ? $list : [];
	}

	// 获取数量
	public function getCount() {
		$where = ['is_delete'=> 0];
		$count = $this->model->where($where)->count();
		return $count ? $count : 0;
	}

	// 获取详情
	public function getInfo($rule_id) {
		$info = $this->model->where(['rule_id'=> $rule_id])->find();
		if (empty($info)) {
			return [];
		}
		$info['days'] = empty($info['days']) ? [] : explode(',', $info['days']);
		$info['times'] = json_decode($info['times'], true);
		return $info;
	}

	// 编辑
	public function edit($data) {
		if (empty($data['rule_name'])) {
			return false;
		}
		if (isset($data['rule_id']) && $data['rule_id'] > 0) {
			$ret = $this->model->where(['rule_id'=> $data['rule_id']])->save($data);
			return $ret !== false;
		} else {
			unset($data['rule_id']);
			$data['add_time'] = time();
			return $this->model->add($data);
		}
	}

	// 删除
	public function del($rule_id){
		return $this->model->where(['rule_id'=> $rule_id])->save(['is_delete'=> 1]);
	}
}