<?php
namespace Common\Service;
use Common\Basic\CsException;

class NoticeService{

	private $model;

	public function __construct() {
		$this->model = D('Notice');
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
			$where['title|content'] = ['like', "%{$param['keyword']}%"];
		}
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($param['p'], $param['pagesize'])->order('notice_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			$list[$key]['username'] = D('Admin')->where(['admin_id'=> $value['admin_id']])->getField('username');
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取详情
	public function getInfo($notice_id) {
		$info = $this->model->where(['notice_id'=> $notice_id])->find();
		if (empty($info)) {
			return [];
		}
		$info['username'] = D('Admin')->where(['admin_id'=> $info['admin_id']])->getField('username');
		return $info;
	}

	// 编辑
	public function edit($data) {
		if (empty($data['title']) || empty($data['content'])) {
			return false;
		}
		if (isset($data['notice_id']) && $data['notice_id'] > 0) {
			unset($data['admin_id']);
			$ret = $this->model->where(['notice_id'=> $data['notice_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = time();
			return $this->model->add($data);
		}
	}

	// 删除
	public function del($notice_id){
		return $this->model->where(['notice_id'=> $notice_id])->save(['is_delete'=> 1]);
	}

	// 是否已读
	public function hasRead($notice_id, $user_id){
		$ret = D('NoticeReadLog')->where(['notice_id'=> $notice_id, 'user_id'=> $user_id])->count();
		return $ret ? 1 : 0;
	}

	// 阅读通知
	public function readNotice($notice_id, $user_id){
		$ret = D('NoticeReadLog')->where(['notice_id'=> $notice_id, 'user_id'=> $user_id])->count();
		if (!$ret) {
			D('NoticeReadLog')->add(['notice_id'=> $notice_id, 'user_id'=> $user_id, 'add_time'=> NOW_TIME]);
		}
		return true;
	}

    // 获取用户的未读通知数量
    public function getUnreadCount($user_id){
        $read_count = $this->model->join('a left join __NOTICE_READ_LOG__ b on a.notice_id=b.notice_id')->where(['a.is_delete'=>0,'b.user_id'=>$user_id])->count();
        $total_count = $this->model->where(['is_delete'=>0])->count();
        return (int)$total_count - (int)$read_count;
    }
}