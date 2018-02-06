<?php
namespace Api\Controller;
use Api\Controller\FController;

class NoticeController extends FController {
	private $service;
	public function _initialize() {
		parent::_initialize();
		$this->service = Service('Notice');
    }

	/**
	 * desc 获取通知列表
	 * @param token  Token
	 * @param page
	 * @param pagesize
	 */
	public function getListAction() {
		$param['p'] = I('page', 1, 'intval');
		$param['pagesize'] = I('pagesize', 10, 'intval');
		$ret = $this->service->getList($param);
		$list = [];
		foreach ($ret['list'] as $key => $value) {
			$hasRead = $this->service->hasRead($value['notice_id'], $this->uid);
			$list[] = [
				'notice_id'=> $value['notice_id'],
				'title'=> $value['title'],
				'content'=> $value['content'],
				'hasRead'=> $hasRead,
				'add_time'=> date('Y-m-d', $value['add_time']),
			];
		}
        $this->apiReturn($list);
	}

	/**
	 * desc 获取通知详情
	 * @param token  Token
	 * @param notice_id
	 */
	public function getInfoAction() {
		$notice_id = I('notice_id', 0, 'intval');
		$info = $this->service->getInfo($notice_id);
		if (empty($info)) {
			$this->apiReturn('通知不存在');
		}
		// 阅读通知
		$this->service->readNotice($info['notice_id'], $this->uid);
		unset($info['admin_id'],$info['is_delete'],$info['username']);
		$info['add_time'] = date('Y-m-d', $info['add_time']);
        $this->apiReturn($info);
	}
}