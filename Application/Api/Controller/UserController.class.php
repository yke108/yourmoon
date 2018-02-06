<?php
namespace Api\Controller;
use Api\Controller\FController;

class UserController extends FController {
	private $service;
	public function _initialize() {
		parent::_initialize();
		$this->service = Service('User');
    }

	/**
	 * desc 上传头像
	 * @param token  Token
	 * @param avatar  头像
	 */
	public function editAction() {
        $this->checkEmpty(['avatar'=> '头像不能为空']);
        $data['avatar'] = I('post.avatar');
        $data['user_id'] = $this->uid;
        try {
            $this->service->edit($data);
        } catch(\Exception $e) {
            $this->apiReturn($e->getMessage());
        }
        $userinfo = $this->service->getInfo($this->uid);
        unset($userinfo['password'],$userinfo['salt'],$userinfo['is_delete']);
        $this->apiReturn('保存成功', 200, $userinfo);
	}

	/**
	 * desc 获取用户信息
	 * @param token  Token
	 */
	public function getInfoAction() {
		$this->userinfo['long_avatar'] = picurl($this->userinfo['avatar']);
		$this->userinfo['rule_name'] = D('sign_rule')->where(['rule_id'=> $this->userinfo['rule_id']])->getField('rule_name');
		// 消息未读数
        $this->userinfo['notice_unread_count'] = Service('Notice')->getUnreadCount($this->uid);
		// 申请未读数
        $this->userinfo['apply_unread_count'] = Service('Apply')->getCount(['user_id'=> $this->uid, 'is_read'=> 0]);
		// 审批未读数
        $userIds = $this->service->getLowerUserIds($this->userinfo['user_no']);
        $userIds = $userIds ? ['in', $userIds] : null;
        $this->userinfo['audit_unread_count'] = Service('Apply')->getCount(['user_id'=> $userIds, 'status'=> 0]);
        $this->apiReturn($this->userinfo);
	}

	/**
	 * desc 提交意见
	 * @param token  Token
	 */
	public function suggestAction() {
		$this->checkEmpty(['content'=> '意见内容不能为空']);
		$content = trim(I('post.content'));
		$remark = trim(I('post.remark'));
		$ret = Service('Suggest')->edit(['user_id'=> $this->uid, 'content'=> $content, 'remark'=> $remark]);
        if (!$ret) {
        	$this->apiReturn('提交失败');
        }
        $this->apiReturn('提交成功', 200);
	}
}