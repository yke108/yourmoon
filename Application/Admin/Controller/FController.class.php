<?php
namespace Admin\Controller;
use Think\Controller;

class FController extends Controller {
	private $_sidebar_in_name;
	private $_sidebar_act_name;
	
	public function _initialize(){
		$uid = session('uid');
		if (empty($uid)) {
			// cookie记录登录状态
			$adminService = Service('Admin');
			$cuid = cookie('uid');
			$cpwd = cookie('pwd');
			if (empty($cuid) || empty($cpwd)) {
				$this->sessLoginError();
			}
			$adminInfo = $adminService->getAdminInfo($cuid);
			if (empty($adminInfo) || $adminInfo['password'] != $cpwd) {
				$this->sessLoginError();
			}
			$this->sessionSet($adminInfo);
		}
		$this->purviewCheck();
		$this->_sidebar_in_name = strtolower(explode('/', CONTROLLER_NAME)[0]);
		$this->_sidebar_act_name = strtolower(str_replace('/', '_', CONTROLLER_NAME.'/'.ACTION_NAME));
		$this->assign('menulist', $this->getMenuList());
    }

    protected function sessLoginError() {
    	if (IS_AJAX){
			$this->error('未登录');
        } else {
			header("Location:".U('site/login'));
        }
    }

    protected function sessionSet($adminInfo) {
    	session('uid', $adminInfo['admin_id']);
		session('username', $adminInfo['username']);
		session('admin_name', $adminInfo['admin_name']);
		session('is_admin', $adminInfo['is_admin']);
		$action_list = Service('Access')->getActionByRoleIds($adminInfo['role_ids']);
		session('action_list', $action_list);
    }

    protected function sbset($ps, $in = ''){
		if (is_array($ps)){
			if(!empty($ps['in'])){
				$this->_sidebar_in_name = $ps['in'];
			} 
			if(!empty($ps['ac'])){
				$this->_sidebar_act_name = $ps['ac'];
			}
		} else {
			$this->_sidebar_act_name = $ps;
			if(!empty($in)){
				$this->_sidebar_in_name = $in;
			}
		}
		return $this;
	}
	
    protected function error($message='',$jumpUrl='',$ajax=false) {
		parent::error($message, $jumpUrl, $ajax);
		exit;
    }

    protected function success($message='',$jumpUrl='',$ajax=false) {
		parent::success($message, $jumpUrl, $ajax);
		exit;
    }
	
	protected function renderPartial($templateFile='',$content='',$prefix=''){
		layout(false);
		return $this->fetch($templateFile,$content,$prefix);
	}
	
	protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
		if(IS_AJAX){
			$ret = array(
				'status'=>2,
				'info'=>$this->renderPartial($templateFile, $content,$prefix),
			);
			$this->ajaxReturn($ret);
		} else {
			$this->assign('sidebar_group', $this->_sidebar_in_name);
			$this->assign('sidebar_menu', $this->_sidebar_act_name);
			$this->assign($this->_sidebar_in_name, 'in');
			$this->assign($this->_sidebar_act_name, ' class="active"');
	        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
		}
    }
	
    protected function _empty(){
        $this->redirect('index/index');
    }

    protected function purviewCheck($priv_str = '') {
		empty($priv_str) && $priv_str = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
		$action_list = session('action_list');
		$is_admin = session('is_admin');
		if ($is_admin == 0 && strpos(','.$action_list.',', ','.$priv_str.',') === false){
	    	$this->error('权限不够， 无法操作');
	    }
    }

    protected function getMenuList(){
        $menulist = [
        	'index'=> [
				'cls'=> 'glyphicon-home',
				'txt'=> '平台首页',
				'itm'=> [
					'index_index_index'=> [
						'url'=> 'index/index/index',
						'txt'=> '平台首页',
					],
				]
			],
			'notice'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '公告管理',
				'itm'=> [
					'notice_index_index'=> [
						'url'=> 'notice/index/index',
						'txt'=> '公告列表',
					],
				]
			],
			'user'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '员工管理',
				'itm'=> [
					'user_index_index'=> [
						'url'=> 'user/index/index',
						'txt'=> '员工列表',
					],
				]
			],
			'rule'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '岗位管理',
				'itm'=> [
					'rule_index_index'=> [
						'url'=> 'rule/index/index',
						'txt'=> '岗位列表',
					],
				]
			],
			'shop'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '门店管理',
				'itm'=> [
					'shop_index_index'=> [
						'url'=> 'shop/index/index',
						'txt'=> '门店列表',
					],
				]
			],
			'sign'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '打卡记录',
				'itm'=> [
					'sign_day_index'=> [
						'url'=> 'sign/day/index',
						'txt'=> '按日查询',
					],
					'sign_times_index'=> [
						'url'=> 'sign/times/index',
						'txt'=> '按次查询',
					],
				]
			],
			'apply'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '申请记录',
				'itm'=> [
					'apply_index_index'=> [
						'url'=> 'apply/index/index',
						'txt'=> '申请列表',
					],
				]
			],
			'export'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '导出管理',
				'itm'=> [
					'export_index_index'=> [
						'url'=> 'export/index/index',
						'txt'=> '设置导出',
					],
					'export_index_exportlog'=> [
						'url'=> 'export/index/exportlog',
						'txt'=> '导出日志',
					],
					'export_index_importlog'=> [
						'url'=> 'export/index/importlog',
						'txt'=> '导入日志',
					],
				]
			],
			'system'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '系统管理',
				'itm'=> [
					'system_config_index'=> [
						'url'=> 'system/config/index',
						'txt'=> '设置配置',
					],
					'system_about_index'=> [
						'url'=> 'system/about/index',
						'txt'=> '关于我们',
					],
					'system_android_index'=> [
						'url'=> 'system/android/index',
						'txt'=> '安卓版本控制',
					],
					'system_ios_index'=> [
						'url'=> 'system/ios/index',
						'txt'=> '苹果版本控制',
					],
					'system_suggest_index'=> [
						'url'=> 'system/suggest/index',
						'txt'=> 'App用户反馈',
					],
				]
			],
			'access'=> [
				'cls'=> 'glyphicon-align-justify',
				'txt'=> '权限管理',
				'itm'=> [
					'access_admin_index'=> [
						'url'=> 'access/admin/index',
						'txt'=> '管理员列表',
					],
					'access_role_index'=> [
						'url'=> 'access/role/index',
						'txt'=> '角色管理',
					],
					'access_action_index'=> [
						'url'=> 'access/action/index',
						'txt'=> '资源管理',
					],
				]
			],
		];
		// 有权限的菜单才展示
		if (session('is_admin') == 0) {
			$action_list = session('action_list');
			foreach ($menulist as $key => $value) {
				if ($key == 'index') continue;
				foreach ($value['itm'] as $k => $v) {
					if (strpos(','.$action_list.',', ','.$v['url'].',') === false) unset($menulist[$key]['itm'][$k]);
				}
			}
			foreach ($menulist as $key => $value) {
				if (empty($value['itm'])) unset($menulist[$key]);
			}
		}
		return $menulist;
    }
}