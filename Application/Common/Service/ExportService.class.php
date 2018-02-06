<?php
namespace Common\Service;
use Common\Basic\CsException;

class ExportService{

	private $model;

	public function __construct() {
		$this->model = D('Export');
	}

	// 判断月记录是否存在
	public function isExist($month) {
		$ret = $this->model->where(['month'=> $month])->count();
		return $ret ? true : false;
	}

	// 添加
	public function add($data) {
		return $this->model->add($data);
	}

	// 列表
	public function getMonthList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;
		$where = ['month'=> $param['month']];
		if (isset($param['real_name']) && !empty($param['real_name'])) {
			$where['real_name'] = ['like', "%{$param['real_name']}%"];
		}
		if (isset($param['user_no']) && !empty($param['user_no'])) {
			$where['user_no'] = $param['user_no'];
		}
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($param['p'], $param['pagesize'])->order('id asc')->select();
		return [
			'count'=> $count ? $count : 0,
			'list'=> $list ? $list : []
		];
	}

	// 获取一个月的全部数据
	public function getMonthAllList($month) {
		$where = ['month'=> $month];
		$list = $this->model->where($where)->field('id,user_id', true)->order('id asc')->select();
		return $list ? $list : [];
	}

	// 获取详情
	public function getInfo($id) {
		$info = $this->model->where(['id'=> $id])->find();
		return $info ? $info : [];
	}

	// 编辑
	public function edit($data) {
		if (!isset($data['id']) || $data['id'] < 1) {
			return false;
		}
		$ret = $this->model->where(['id'=> $data['id']])->save($data);
		return $ret !== false;
	}

	// 获取列表
	public function getLogList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;

		$where = [];
		if (isset($param['start']) && !empty($param['start'])) {
			$where['add_time'][] = ['egt', strtotime($param['start'])];
		}
		if (isset($param['end']) && !empty($param['end'])) {
			$where['add_time'][] = ['lt', strtotime($param['end'])+86400];
		}
		if (isset($param['admin_name']) && !empty($param['admin_name'])) {
			$admin_ids = M('Admin')->where(['admin_name'=> ['like', "%{$param['admin_name']}%"], 'is_delete'=> 0])->getField('admin_id', true);
			$admin_ids = !empty($admin_ids) ? ['in', $admin_ids] : null;
			$where['admin_id'] = $admin_ids;
		}
		$count = D('ExportLog')->where($where)->count();
		$list = D('ExportLog')->where($where)->page($param['p'], $param['pagesize'])->order('id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			$list[$key]['admin_name'] = D('Admin')->where(['admin_id'=> $value['admin_id']])->getField('admin_name');
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	public function addLog($data) {
		$data['add_time'] = NOW_TIME;
		return D('ExportLog')->add($data);
	}
}