<?php
namespace Api\Controller;
use Think\Controller;

class FController extends Controller {
	protected $uid = 0;
	protected $userinfo = [];
	public function _initialize(){
		// 验证签名
		$this->checkSign();
        $this->checkToken();
    }

    // 验证签名
    protected function checkSign() {
    	if (APP_DEBUG && I('post.nosign')) {
    		return true;
    	}
    	$post = I('post.');
		if(empty($post['vsign']) || empty($post['rstr']) || empty($post['client']) || empty($post['version'])){
			$this->apiReturn('签名失败', 301);
		}
		$post['secKey'] = md5('AnApiForFood2China118');
		$vsign = $post['vsign'];
		unset($post['vsign']);
		sort($post,SORT_STRING);
		$expSign = md5(implode($post));
		if($vsign != $expSign) {
			$this->apiReturn('签名失败', 301);
		}
    }

    // 验证TOKEN
    protected function checkToken() {
    	$token = I('post.token');
    	if (empty($token)) {
    		$this->apiReturn('未登录', 302);
    	}
    	$this->uid = Service('Token')->getUserId($token);
    	if (!$this->uid) {
    		$this->apiReturn('登录超时', 303);
    	}
    	$this->userinfo = Service('User')->getInfo($this->uid);
        unset($this->userinfo['password'],$this->userinfo['salt'],$this->userinfo['is_delete']);
    	if (empty($this->userinfo)) {
    		$this->apiReturn('账号已删除', 304);
    	}
        if (empty($this->userinfo['real_name'])) {
            $this->apiReturn('请先完善资料', 305, ['token'=> I('post.token')]);
        }
        if ($this->userinfo['status'] != 1) {
            $this->apiReturn('帐号'.$this->userinfo['status_name']);
        }
    }

    // 200 成功
    // 301 签名失败
    // 302 未登录
    // 303 登录超时
    // 304 帐号已删除
    // 305 请先完善资料
    // 306 手机号已经存在
    // 400 失败，给用户显示提示语
    protected function apiReturn($msg = '', $code = 400, $data = [], $list = []) {
    	if (is_array($msg) || empty($msg)) {
    		if (isset($msg[0])) {
    			$list = $msg;
    		} elseif (!empty($msg)) {
    			$data = $msg;
    		}
    		$msg  = "成功";
    		$code = 200;
    	}
    	$this->ajaxReturn([
    		'code'=> $code,
    		'msg' => $msg,
    		'data'=> (object)$data,
    		'list'=> $list,
    	]);
    }

    // 提交字段不能为空
    protected function checkEmpty($param) {
        $post = I('post.');
        foreach ($param as $field => $msg) {
            if (!isset($post[$field]) || empty(trim($post[$field]))) {
                $this->apiReturn($msg);
            }
        }
    }

    public function _empty() {
        $this->apiReturn('api不存在');
    }
}