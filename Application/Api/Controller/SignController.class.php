<?php
namespace Api\Controller;
use Api\Controller\FController;

class SignController extends FController {
	private $service;

	public function _initialize() {
		parent::_initialize();
		$this->service = Service('Sign');
    }

	/**
	 * desc 打卡主页
	 * @param token  Token
	 */
	public function indexAction() {
		$date = date('Y-m-d', NOW_TIME);
		$record = $this->service->getOneDayRecord($this->uid, $date);
		$result = [
			'status'=> 1, // 1:可以打卡，2-不必打卡，3-打卡完成
			'now_time'=> NOW_TIME,
			'instruction'=> $record['rule']['instruction']
		];
		if (!in_array(date('w', NOW_TIME), $record['rule']['days'])) {
			$result['status'] = 2;
		} elseif (count($record['list']) >= count($record['rule']['times'])) {
			$result['status'] = 3;
		}
		if ($result['status'] == 1) {
			$result = array_merge($result, $record['rule']['times'][count($record['list'])]);
			$result['shop_name'] = $this->userinfo['shop_name'];
		}
		$result['list'] = $record['list'];
		$this->apiReturn($result);
	}

	/**
	 * desc 打卡
	 * @param  token
	 * @param  image
	 * @param  address
	 * @param  latitude
	 * @param  longitude
	 */
	public function addAction() {
		// 接收参数
		$image = trim(I('post.image'));
		$address = trim(I('post.address'));
		$latitude = I('post.latitude', '', 'floatval');
		$longitude = I('post.longitude', '', 'floatval');
		// 判断能否打卡
		$date = date('Y-m-d', NOW_TIME);
		$record = $this->service->getOneDayRecord($this->uid, $date);
		if (!in_array(date('w', NOW_TIME), $record['rule']['days'])) {
			$this->apiReturn('今日不需要打卡');
		} 
		if (count($record['list']) >= count($record['rule']['times'])) {
			$this->apiReturn('今日打卡次数已满');
		}
		$rule = $record['rule']['times'][count($record['list'])];
		$status = 1;// 正常
		$clock = date('H:i', NOW_TIME);
		if ($rule['time'] == 1 && ($clock < $rule['start'] || $clock > $rule['end'])) {
			$status = 2;// 时间异常
		} elseif ($rule['location'] == 1) {
			$shopInfo = Service('Shop')->getInfo($this->userinfo['shop_no'], 'shop_no', 'longitude,latitude');
			if (!empty($shopInfo)) {
				$distance = distance($longitude, $latitude, $shopInfo['longitude'], $shopInfo['latitude']);
				if ($distance > Service('Config')->getConfig('location_range')) {
					$status = 3;// 定位异常
				}
			} else {
				$status = 3;// 定位异常	
			}
		}
		if (empty($record['info'])) {
			// 添加日记录
			$sign_id = $this->service->editDay([
				'user_id'=> $this->uid,
				'date'=> $date,
				'rule'=> json_encode($record['rule']),
				'status'=> 2,
			]);
		} else {
			$sign_id = $record['info']['sign_id'];
		}
		// 添加次记录
		$data = [
			'sign_id'=> $sign_id,
			'user_id'=> $this->uid,
			'shop_no'=> $rule['location'] ? $this->userinfo['shop_no'] : "",
			'image'  => $image,
			'rule'  => json_encode($rule),
			'address'  => $address,
			'latitude'  => $latitude,
			'longitude'  => $longitude,
			'status'=> $status,
		];
		try {
			$detail_id = $this->service->editDetail($data);
		} catch (\Exception $e) {
			$this->apiReturn($e->getMessage());
		}
		$day_status = $status == 1 ? 1 : 2;// 天的异常状态
		// 计算工作时间
		if ($record['rule']['min_hour'] > 0) {
			$work_time = 0;
			foreach ($record['list'] as $key => $value) {
				if ($key % 2 == 0) {
					$t1 = date('H', $value['add_time']);
					$t1 += date('i', $value['add_time']) >= 30 ? 1 : 0.5;
				} else {
					$t2 = date('H', $value['add_time']);
					$t2 += date('i', $value['add_time']) >= 30 ? 0.5 : 0;
					$dt = $t2 - $t1;
					$work_time += $dt > 0 ? $dt : 0;
				}
			}
			if (count($record['list']) % 2 == 1) {
				$t2 = date('H', NOW_TIME);
				$t2 += date('i', NOW_TIME) >= 30 ? 0.5 : 0;
				$dt = $t2 - $t1;
				$work_time += ($dt > 0 ? $dt : 0);
				$this->service->editDay(['sign_id'=> $sign_id, 'work_time'=> $work_time]);
			}
			if ($work_time < $record['rule']['min_hour']) {
				$day_status = 2;
			}
		}
		// 改变当天打卡状态
		if ($day_status == 1 && (count($record['list']) + 1) == count($record['rule']['times'])) {
			foreach ($record['list'] as $key => $value) {
				if ($value['status'] != 1) {
					$day_status = 2;
					break;
				}
			}
			if ($day_status == 1) {
				$this->service->editDay(['sign_id'=> $sign_id, 'status'=> $day_status]);
			}
		}
		$this->apiReturn('打卡成功', 200, ['status'=> $status]);
	}

	/**
	 * desc 填写异常理由
	 * @param token
	 * @param detail_id
	 * @param reason
	 */
	public function writeReasonAction() {
		$this->checkEmpty([
			'detail_id'=> '打卡ID不能为空',
			'reason'=> '原因不能为空',
		]);
		$detail_id = I('post.detail_id', 0, 'intval');
		$reason = I('post.reason');
		$info = $this->service->getDetailInfo($detail_id);
		if (empty($info) || $info['user_id'] != $this->uid) {
			$this->apiReturn('打卡信息不存在');
		}
		if ($info['status'] == 1) {
			$this->apiReturn('没有异常不需要填写理由');
		}
		try {
			$this->service->editDetail(['detail_id'=> $detail_id, 'reason'=> $reason]);
		} catch (\Exception $e) {
			$this->apiReturn($e->getMessage());
		}
		$this->apiReturn([]);
	}

	/**
	 * desc 根据月份查询打卡记录
	 * @param token
	 * @param month Y-m
	 * @return list status 0-没有出勤，1-正常，2-异常
	 */
	public function monthAction() {
		$month = I('post.month');
		$month = empty($month) ? date('Y-m', NOW_TIME) : $month;
		$month = date('Y-m', strtotime($month));
		$list = [];
		for ($i=1; $i <= date("t", strtotime($month)); $i++) {
			$list[] = [
				'day'=> $i,
				'date'=> $month.'-'.$i,
				'status'=> $this->service->getDateStatus($this->uid, $month.'-'.$i)
			];
		}
		$this->apiReturn($list);
	}

	/**
	 * desc 根据日期查询打卡记录
	 * @param token
	 * @param date Y-m-d
	 */
	public function dateAction() {
		$date = I('post.date');
		$date = empty($date) ? date('Y-m-d', NOW_TIME) : $date;
		$date = date('Y-m-d', strtotime($date));
		$ret = $this->service->getOneDayRecord($this->uid, $date);
		unset($ret['info']['rule']);
		$ret['info'] = (object)$ret['info'];
		$this->apiReturn($ret);
	}
}