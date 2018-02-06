<?php
namespace Api\Controller;
use Api\Controller\FController;

class ApplyController extends FController {
	private $service;

	public function _initialize() {
		parent::_initialize();
		$this->service = Service('Apply');
    }

	/**
	 * desc 申请考勤正常
	 * @param token  Token
	 * @param date  Y-m-d
	 * @param reason  
	 * @param image 图片，多张用逗号隔开
	 */
	public function addAction() {
		$this->checkEmpty([
			'date'=> '申请日期不能为空',
			'reason'=> '申请理由不能为空',
		]);
		$date = I('post.date');
		$reason = trim(I('post.reason'));
		$image = trim(I('post.image'));
		$date = date('Y-m-d', strtotime($date));
		if ($date == date('Y-m-d')) {
			$this->apiReturn('不能提交当天的申请');
		}
		// 判断是否已经申请过
		if ($this->service->isExist($this->uid, $date)) {
			$this->apiReturn('请勿重复申请');
		}
		// 非异常不能申请
		$ret = Service('Sign')->getDayInfoByDate($this->uid, $date, 'status');
		if (!empty($ret) && $ret['status'] != 2) {
			$this->apiReturn('当天考勤没有异常');
		}
		// 添加申请记录
		$ret = $this->service->edit([
			'user_id'=> $this->uid,
			'date'=> $date,
			'reason'=> $reason,
			'image'=> $image,
		]);
		if (!$ret) {
			$this->apiReturn('申请失败');
		}
        $this->apiReturn('申请成功', 200);
	}

	/**
	 * desc 申请列表
	 * @param token  Token
	 * @param status  状态：-1全部，0待审批，1通过，2不通过
	 * @param month  Y-m
	 * @param page  页码，默认：1
	 * @param pagesize  每页显示数量，默认：10
	 */
	public function applyListAction() {
		$param['status'] = I('post.status', 0, 'intval');
		$param['p'] = I('post.page', 1, 'intval');
		$param['pagesize'] = I('post.pagesize', 10, 'intval');
		$month = I('post.month');
		if (!empty($month)) {
			$param['start'] = $month . '-01';
			$param['end'] = date('Y-m-d', strtotime("$month +1 month -1 day"));
		}
		if ($param['status'] == -1) {
			unset($param['status']);
		}
		$param['user_id'] = $this->uid;
		$list = $this->service->getList($param)['list'];
		// 更新未读为已读
		$apply_id_list = [];
		foreach ($list as $value) {
            $apply_id_list[] = $value['apply_id'];
        }
        $this->service->updateRead($apply_id_list);
		$this->apiReturn($list);
	}

	/**
	 * desc 审批列表
	 * @param token  Token
	 * @param status  状态：-1全部，0待审批，1通过，2不通过
	 * @param month  Y-m
	 * @param real_name 姓名
	 * @param page  页码，默认：1
	 * @param pagesize  每页显示数量，默认：10
	 */
	public function auditListAction() {
		$param['status'] = I('post.status', 0, 'intval');
		$param['p'] = I('post.page', 1, 'intval');
		$param['pagesize'] = I('post.pagesize', 10, 'intval');
		$param['real_name'] = trim(I('post.real_name'));
		$month = I('post.month');
		if (!empty($month)) {
			$param['start'] = $month . '-01';
			$param['end'] = date('Y-m-d', strtotime("$month +1 month -1 day"));
		}
		if ($param['status'] == -1) {
			unset($param['status']);
		}
		$userIds = Service('User')->getLowerUserIds($this->userinfo['user_no']);
		if (empty($userIds)) {
			$this->apiReturn([]);
		}
		$param['user_id'] = ['in', $userIds];
		$list = $this->service->getList($param)['list'];
        $this->apiReturn($list);
	}

	/**
	 * desc 审批
	 * @param token
	 * @param apply_id
	 * @param comment  
	 * @param status  1-通过，2-不通过
	 */
	public function auditAction() {
		$this->checkEmpty([
			'apply_id'=> '申请ID不能为空',
			'status'=> '审批状态不能为空',
		]);
		$apply_id = I('post.apply_id', 0, 'intval');
		$status = I('post.status', 0, 'intval');
		$comment = trim(I('post.comment'));
		if (!in_array($status, [1,2])) {
			$this->apiReturn('审核状态不合法');
		}
		$info = $this->service->getInfo($apply_id);
		$userIds = Service('User')->getLowerUserIds($this->userinfo['user_no']);
		if (empty($info) || !in_array($info['user_id'], $userIds)) {
			$this->apiReturn('审批记录不存在');
		}
		if ($info['status'] != 0) {
			$this->apiReturn('请勿重复审批');
		}
		// 审批
		$ret = $this->service->edit([
			'apply_id'=> $apply_id,
			'comment'=> $comment,
			'audit_user'=> $this->uid,
			'audit_time'=> NOW_TIME,
			'status'=> $status,
			'is_read'=> 0,
		]);
		if (!$ret) {
			$this->apiReturn('审批失败');
		}
		$signService = Service('Sign');
		// 审批通过修改打卡表状态
		if ($status == 1) {
			$signInfo = $signService->getDayInfoByDate($info['user_id'], $info['date'], 'sign_id');
			if (empty($signInfo)) {
				$rule_id = Service('User')->getInfo($info['user_id'])['rule_id'];
				$ruleInfo = Service('Rule')->getInfo($rule_id);
				unset($ruleInfo['is_delete'], $ruleInfo['add_time']);
				$rule = json_encode($ruleInfo);
				$signService->editDay(['user_id'=> $info['user_id'], 'date'=> $info['date'], 'rule'=> $rule, 'status'=> 3]);
			} else {
				$signService->editDay(['sign_id'=> $signInfo['sign_id'], 'status'=> 3]);
			}
		}
        $this->apiReturn('审批成功', 200);
	}
}