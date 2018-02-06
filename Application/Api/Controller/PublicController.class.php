<?php
namespace Api\Controller;
use Api\Controller\FController;

class PublicController extends FController {
	public function _initialize() {
		$this->checkSign();
    }
	
	/**
	 * desc 上传图片
	 */
    public function uploadAction() {
	    $upload = new \Think\Upload(); // 实例化上传类
	    $upload->maxSize   =     31457280 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg',);// 设置附件上传类型
	    $upload->rootPath  =     UPLOAD_PATH;  // 设置附件上传根目录
	    $upload->savePath  =     'editor/'; // 设置附件上传（子）目录
		$upload->subName   =      array('date', 'Ym');
		$info = $upload->upload();
		if($info) {
			$list = [];
			foreach ($info as $key => $value) {
				$list[] = [
					'short_url'=>$value['savepath'].$value['savename'],
					'url' => picurl($value['savepath'].$value['savename']),
				];
			}
			$this->apiReturn($list);
		} else {
			$this->apiReturn($upload->getError());
		}
	}

	/**
	 * desc 获取手机验证码
	 * @param phone  手机号
	 * @param type   1-注册，2-找回密码
	 */
	public function getVerifyAction() {
        $phone = I('post.phone');
        $type = I('post.type', 1, 'intval');
        if (!in_array($type, [1,2])) {
        	$this->apiReturn("类型不正确");
        }
        if (empty($phone)) {
        	$this->apiReturn("手机号不能为空");
        }
        if (!preg_match('/^1\d{10}$/', $phone)) {
        	$this->apiReturn("手机号格式不正确");
        }
        $userinfo = Service('User')->getInfo($phone, 'phone');
        if (!empty($userinfo) && $type == 1) {
        	$this->apiReturn("手机号已经存在");
        } elseif (empty($userinfo) && $type == 2) {
			$this->apiReturn("手机号不存在");
        }
        $verify_code = rand(1000, 9999);
        $ret = Service('Verify')->add($phone, $verify_code, $type);
        if (!$ret) {
        	$this->apiReturn("获取验证码失败");
        }
        $ret = Service('Sms')->send($phone, $verify_code, $type);
        if (!$ret) {
        	$this->apiReturn("发送短信失败");
        }
        $this->apiReturn('短信发送成功', 200);
	}

	/**
	 * desc 注册
	 * @param phone  手机号
	 * @param password  MD5密码
	 * @param verify_code  验证码
	 * @param invite_code  邀请码
	 */
	public function registerAction() {
        $data['phone'] = I('post.phone');
        $data['password'] = I('post.password');
        $verify_code = I('post.verify_code');
        $invite_code = I('post.invite_code');
        if (empty($verify_code)) {
        	$this->apiReturn("验证码不能为空");
        }
        if (empty($invite_code)) {
        	$this->apiReturn("邀请码不能为空");
        }
        $ret = Service('Verify')->verify($data['phone'], $verify_code, 1);
        if (!$ret) {
        	$this->apiReturn("验证码不正确");
        }
        if ($invite_code != Service('Config')->getConfig('invite_code')) {
        	$this->apiReturn("邀请码不正确");
        }
        try {
            $user_id = Service('User')->edit($data);
        } catch(\Exception $e) {
        	$msg = $e->getMessage();
        	$code = $msg == '手机号已经存在' ? 306 : 400;
            $this->apiReturn($e->getMessage(), $code);
        }
        $token = Service('Token')->makeToken($user_id);
        $this->apiReturn('注册成功', 200, ['token'=> $token]);
	}

	/**
	 * desc 登录
	 * @param phone  手机号
	 * @param password  MD5密码
	 */
	public function loginAction() {
		$this->checkEmpty([
			'phone'=> '手机号不能为空',
			'password'=> '密码不能为空',
		]);
		$phone = I('post.phone');
		$password = I('post.password');
		$userinfo = Service('User')->checkPassword($phone, $password);
		if (empty($userinfo)) {
			$this->apiReturn("手机号或者密码不正确");
		}
		if ($userinfo['status'] == 2 || $userinfo['status'] == 3) {
    		$this->apiReturn('帐号'.$userinfo['status_name']);
    	}
		$token = Service('Token')->makeToken($userinfo['user_id']);
		if (empty($userinfo['real_name'])) {
    		$this->apiReturn('请先完善信息', 305, ['token'=> $token]);
    	}
    	Service('User')->edit(['user_id'=> $userinfo['user_id'], 'password'=> $password]);
    	unset($userinfo['password'],$userinfo['salt'],$userinfo['is_delete']);
    	$userinfo['token'] = $token;
    	$this->apiReturn('登录成功', 200, $userinfo);
	}

	/**
	 * desc 门店列表
	 */
	public function shopListAction() {
		$data['shopList'] = Service('Shop')->getList(['pagesize'=> 1000, 'field'=> 'shop_no,shop_name'])['list'];
		$this->apiReturn($data);
	}

	/**
	 * desc 忘记密码
	 * @param phone  手机号
	 * @param password  新MD5密码
	 * @param verify_code  验证码
	 */
	public function forgetPasswordAction() {
		$this->checkEmpty([
			'phone'=> '手机号不能为空',
			'password'=> '密码不能为空',
			'verify_code'=> '验证码不能为空',
		]);
		$phone = I('post.phone');
		$password = I('post.password');
		$verify_code = I('post.verify_code');
		$userinfo = Service('User')->getInfo($phone, 'phone');
		if (empty($userinfo)) {
			$this->apiReturn("手机不存在");
		}
		$ret = Service('Verify')->verify($phone, $verify_code, 2);
		if (!$ret) {
			$this->apiReturn("验证码不正确");
		}
		if ($userinfo['status'] == 2 || $userinfo['status'] == 3) {
    		$this->apiReturn('帐号'.$userinfo['status_name']);
    	}
		$token = Service('Token')->makeToken($userinfo['user_id']);
		if (empty($userinfo['real_name'])) {
    		$this->apiReturn('请先完善信息', 305, ['token'=> $token]);
    	}
    	Service('User')->edit(['user_id'=> $userinfo['user_id'], 'password'=> $password]);
    	$this->apiReturn('找回密码成功', 200, ['token'=> $token, 'status'=> $userinfo['status']]);
	}

	/**
	 * desc 获取版本更新信息
	 * @param type  1-安卓，2-苹果
	 */
	public function appVersionAction() {
		$type = I('post.type', 1, 'intval');
		$password = I('post.password');
		$verify_code = I('post.verify_code');
		$configService = Service('Config');
		$pre = $type == 1 ? 'android_' : 'ios_';
		$data = [
			'latest_version' => $configService->getConfig($pre . 'latest_version'),
			'minimum_version' => $configService->getConfig($pre . 'minimum_version'),
			'update_info' => $configService->getConfig($pre . 'update_info'),
			'download_url' => $configService->getConfig($pre . 'download_url'),
		];
    	$this->apiReturn($data);
	}

	/**
	 * desc 关于我们
	 * @param 
	 */
	public function aboutUsAction() {
		$configService = Service('Config');
		$data = [
			'company_name' => $configService->getConfig('company_name'),
			'company_desc' => $configService->getConfig('company_desc'),
		];
    	$this->apiReturn($data);
	}

	/**
	 * desc DEMO
	 */
	public function demoAction() {
		$this->apiReturn();
	}
}