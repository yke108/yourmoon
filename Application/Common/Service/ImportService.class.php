<?php
namespace Common\Service;
use Common\Basic\CsException;

class ImportService{


	public function __construct() {
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
		$count = D('ImportLog')->where($where)->count();
		$list = D('ImportLog')->where($where)->page($param['p'], $param['pagesize'])->order('id desc')->select();
		$list = $list ? $list : [];
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	public function addLog($data) {
		$data['add_time'] = NOW_TIME;
		return D('ImportLog')->add($data);
	}
}