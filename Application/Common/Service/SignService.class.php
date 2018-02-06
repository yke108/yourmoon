<?php
namespace Common\Service;
use Common\Basic\CsException;

class SignService{

	private $dayModel;
	private $detailModel;

	public function __construct() {
		$this->dayModel = D('SignDay');
		$this->detailModel = D('SignDetail');
	}

	// 获取日列表
	public function getDayList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;

		$where = [];
		if (isset($param['start']) && !empty($param['start'])) {
			$where['add_time'][] = ['egt', strtotime($param['start'])];
		}
		if (isset($param['end']) && !empty($param['end'])) {
			$where['add_time'][] = ['lt', strtotime($param['end'])+86400];
		}
		if (isset($param['keyword']) && !empty($param['keyword'])) {
			$user_ids = D('User')->where(['real_name|phone|user_no'=> $param['keyword']])->getField('user_id', true);
			$where['user_id'] = !empty($user_ids) ? ['in', $user_ids] : -1;
		}
		if (isset($param['user_id']) && !empty($param['user_id'])) {
			$where['user_id'] = $param['user_id'];
		}
		if (isset($param['date']) && !empty($param['date'])) {
			$where['date'] = $param['date'];
		}
		if (isset($param['status']) && !empty($param['status'])) {
			$where['status'] = $param['status'];
		}
		$count = $this->dayModel->where($where)->count();
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->dayModel->where($where)->field($field)->page($param['p'], $param['pagesize'])->order('sign_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			if (isset($value['user_id'])) {
				$ret = D('User')->where(['user_id'=> $value['user_id']])->field('real_name,phone,user_no')->find();
				$list[$key]['real_name'] = $ret['real_name'];
				$list[$key]['phone'] = $ret['phone'];
				$list[$key]['user_no'] = $ret['user_no'];
			}
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取日详情
	public function getDayInfo($sign_id, $field = "*") {
		$info = $this->dayModel->where(['sign_id'=> $sign_id])->field($field)->find();
		isset($info['user_id']) && $info['real_name'] = D('User')->where(['user_id'=> $info['user_id']])->getField('real_name');
		isset($info['rule']) && $info['rule'] = json_decode($info['rule'], true);
		return $info;
	}
	public function getDayInfoByDate($user_id, $date, $field = "*") {
		$info = $this->dayModel->where(['user_id'=> $user_id, 'date'=> $date])->field($field)->find();
		isset($info['user_id']) && $info['real_name'] = D('User')->where(['user_id'=> $info['user_id']])->getField('real_name');
		isset($info['rule']) && $info['rule'] = json_decode($info['rule'], true);
		return $info;
	}

	// 获取次列表
	public function getDetailList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;

		$where = [];
		if (isset($param['start']) && !empty($param['start'])) {
			$where['add_time'][] = ['egt', strtotime($param['start'])];
		}
		if (isset($param['end']) && !empty($param['end'])) {
			$where['add_time'][] = ['lt', strtotime($param['end'])+86400];
		}
		if (isset($param['keyword']) && !empty($param['keyword'])) {
			$user_ids = D('User')->where(['real_name|phone|user_no'=> $param['keyword']])->getField('user_id', true);
			$where['user_id'] = !empty($user_ids) ? ['in', $user_ids] : -1;
		}
		if (isset($param['user_id']) && !empty($param['user_id'])) {
			$where['user_id'] = $param['user_id'];
		}
		if (isset($param['sign_id']) && !empty($param['sign_id'])) {
			$where['sign_id'] = $param['sign_id'];
		}
		if (isset($param['shop_id']) && !empty($param['shop_id'])) {
			$where['shop_id'] = $param['shop_id'];
		}
		if (isset($param['status']) && !empty($param['status'])) {
			$where['status'] = $param['status'];
		}
		$count = $this->detailModel->where($where)->count();
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->detailModel->where($where)->field($field)->page($param['p'], $param['pagesize'])->order('detail_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			if (isset($value['user_id'])) {
				$ret = D('User')->where(['user_id'=> $value['user_id']])->field('real_name,phone,user_no')->find();
				$list[$key]['real_name'] = $ret['real_name'];
				$list[$key]['phone'] = $ret['phone'];
				$list[$key]['user_no'] = $ret['user_no'];
			}
			if (isset($value['shop_no'])) {
				$list[$key]['shop_name'] = empty($value['shop_no']) ? "" : D('Shop')->where(['shop_no'=> $value['shop_no']])->getField('shop_name');
			}
			isset($value['image']) && $list[$key]['images'] = picurls($value['image']);
			isset($value['rule']) && $list[$key]['rule'] = json_decode($value['rule'], true);
			isset($value['status']) && $list[$key]['status_label'] = $value['status'] == 1 ? '正常' : ($value['status'] == 2 ? '时间异常' : '定位异常');
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取次详情
	public function getDetailInfo($detail_id, $field = "*") {
		$info = $this->detailModel->where(['detail_id'=> $detail_id])->field($field)->find();
		isset($info['user_id']) && $info['real_name'] = D('User')->where(['user_id'=> $info['user_id']])->getField('real_name');
		isset($info['shop_no']) && $info['shop_name'] = D('Shop')->where(['shop_no'=> $info['shop_no']])->getField('shop_name');
		if (isset($info['shop_no'])) {
			$info['shop_name'] = empty($info['shop_no']) ? "" : D('Shop')->where(['shop_no'=> $info['shop_no']])->getField('shop_name');
		}
		isset($info['rule']) && $info['rule'] = json_decode($info['rule'], true);
		isset($info['status']) && $info['status_label'] = $info['status'] == 1 ? '正常' : ($info['status'] == 2 ? '时间异常' : '定位异常');
		isset($info['image']) && $info['images'] = picurls($info['image']);
		return $info;
	}

	// 编辑日打卡
	public function editDay($data) {
		if (isset($data['sign_id']) && $data['sign_id'] > 0) {
			$ret = $this->dayModel->where(['sign_id'=> $data['sign_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = strtotime($data['date']);
			return $this->dayModel->add($data);
		}
	}

	// 编辑次打卡
	public function editDetail($data) {
		if (!$this->detailModel->create($data)) {
			throw new CsException($this->detailModel->getError());
		}
		if (isset($data['detail_id']) && $data['detail_id'] > 0) {
			$ret = $this->detailModel->where(['detail_id'=> $data['detail_id']])->save($data);
			if ($ret === false) throw new CsException('操作失败');
		} else {
			$data['add_time'] = NOW_TIME;
			$ret = $this->detailModel->add($data);
			if (!$ret) throw new CsException('操作失败');
		}
		return $ret;
	}

	// 获取某一天打卡记录
	public function getOneDayRecord($user_id, $date) {
		$res = ['info'=> [], 'list'=> [], 'rule'=> []];
		$info = $this->dayModel->where(['user_id'=> $user_id,'date'=> $date])->field('sign_id,date,rule,work_time,status')->find();
		if (empty($info)) {
			$rule_id = D('User')->where(['user_id'=> $user_id])->getField('rule_id');
			$res['rule'] = Service('Rule')->getInfo($rule_id);
			unset($res['rule']['is_delete'], $res['rule']['add_time']);
			return $res;
		}
		$res['info'] = $info;
		$res['rule'] = json_decode($info['rule'], true);
		$res['list'] = $this->getDetailList(['sign_id'=> $info['sign_id'], 'field'=> 'detail_id,image,address,status,add_time'])['list'];
		return $res;
	}

	// 获取某一天打卡状态，0-没有出勤，1-正常，2-异常
	public function getDateStatus($user_id, $date) {
		$date = date('Y-m-d', strtotime($date));
		$info = $this->dayModel->where(['user_id'=> $user_id,'date'=> $date])->field('sign_id,status')->find();
		if (empty($info)) {
			return 0;//没有出勤
		}
		if ($info['status'] == 2) {
			return 2;//异常
		}
		return 1;//正常
	}

	// 获取某个月的实勤天数
	public function getActualDays($user_id, $month) {
		$where = [
			'user_id'=> $user_id,
			'status'=> ['in', [1,3]],
			'add_time'=> [
				['egt', strtotime($month)],
				['lt', strtotime("$month +1 month")],
			],
		];
		$count = $this->dayModel->where($where)->count();
		return $count ? $count : 0;
	}
}