<?php
namespace Admin\Controller\Site;
use Admin\Controller\FController;

class LoginController extends FController {
	public function _initialize(){
		layout(false);
    }
	
	// 登录
    public function indexAction(){
		if(IS_POST){
			$param['username'] = I('post.username', '', 'trim,strtolower');
			$param['password'] = I('post.password');
			try {
				$adminInfo = Service('Admin')->login($param);
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			if ($adminInfo['status'] == 2) {
				$this->error('帐号已被冻结，请联系相关人员处理');
			}
			cookie('uid', $adminInfo['admin_id']);
			cookie('pwd', $adminInfo['password']);
			$this->sessionSet($adminInfo);
			$this->redirect('index/index');
		}
		$this->display();
    }
	
	// 退出登录
	public function logoutAction(){
		session(null);
		cookie('uid', null);
		cookie('pwd', null);
		header("Location:".U('site/login'));
	}
}