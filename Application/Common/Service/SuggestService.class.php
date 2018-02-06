<?php
namespace Common\Service;
use Common\Basic\CsException;

class SuggestService{

	private $model;

	public function __construct() {
		$this->model = D('Suggest');
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
		if (isset($param['keyword']) && !empty($param['keyword'])) {
			$where['content'] = ['like', "%{$param['keyword']}%"];
		}
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($param['p'], $param['pagesize'])->order('suggest_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			$list[$key]['real_name'] = D('User')->where(['user_id'=> $value['user_id']])->getField('real_name');
			$list[$key]['phone'] = D('User')->where(['user_id'=> $value['user_id']])->getField('phone');
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取详情
	public function getInfo($suggest_id) {
		$info = $this->model->where(['suggest_id'=> $suggest_id])->find();
		return $info ? $info : [];
	}

	// 编辑
	public function edit($data) {
		if (empty($data['content'])) {
			return false;
		}
		if (isset($data['suggest_id']) && $data['suggest_id'] > 0) {
			$ret = $this->model->where(['suggest_id'=> $data['suggest_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = NOW_TIME;
			return $this->model->add($data);
		}
	}

	// 删除
	public function del($suggest_id){
		return $this->model->where(['suggest_id'=> $suggest_id])->save(['is_delete'=> 1]);
	}
}