<?php
namespace Admin\Controller\Access;
use Admin\Controller\FController;
use Common\Basic\CsException;

class RoleController extends FController {
	private $service;
	public function _initialize(){
		parent::_initialize();
		$this->service = Service('Access');
    }
	
    public function indexAction(){
    	$list = $this->service->getRoleList();
    	$this->assign('list', $list);
		$this->display();
    }
	
	// 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['role_id'] = I('post.role_id', 0, 'intval');
            $data['role_name'] = I('post.role_name');
            $data['role_describe'] = I('post.role_describe');
            $data['purview'] = I('post.purview');
            try {
                $this->service->editRole($data);
            } catch (CsException $e) {
                $this->error($e->getMessage());
            }
            $this->success('操作成功', U('index'));
        } else {
            $role_id = I('get.role_id', 0, 'intval');
            $info = [];
            if ($role_id > 0) { 
                $info = $this->service->getRoleInfo($role_id);
                if (empty($info)) {
                    $this->error('角色不存在');
                }
                $info['action_list'] = explode(',', $info['action_list']);
            }
            //后台权限
	    	$purview_list = $this->service->actionList();
			$this->assign('purview_list', $purview_list);
            $this->assign('info', $info);
            $this->display();
        }
    }

    // 删除
    public function delAction(){
		$role_id = I('get.role_id', 0, 'intval');
		$info = $this->service->getRoleInfo($role_id);
		if(empty($info)) $this->error('内容不存在');
		try {
			$result = $this->service->roleDelete($role_id);
		} catch (CsException $e) {
			$this->error($e->getMessage());
		}
		$this->success('删除成功', U('index'));
	}
}