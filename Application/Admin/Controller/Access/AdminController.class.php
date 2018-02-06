<?php
namespace Admin\Controller\Access;
use Admin\Controller\FController;
use Common\Basic\Pager;

class AdminController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Admin');
    }
	
    // 列表
    public function indexAction(){
        $list = $this->service->getList();
        $this->assign('list', $list);
        $this->display();
    }

    // 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['admin_id'] = I('post.admin_id', 0, 'intval');
            $data['username'] = trim(I('post.username'));
            $data['admin_name'] = trim(I('post.admin_name'));
            $data['password'] = trim(I('post.password'));
            $role_ids = I('post.role_ids');
            $data['role_ids'] = empty($role_ids) ? '' : implode(',', $role_ids);
            if ($data['admin_id'] == 0 && empty($data['password'])) {
                $this->error('密码不能为空');
            }
            if (empty($data['password'])) {
                unset($data['password']);
            }
            if ($data['admin_id'] > 0) {
                if (D('Admin')->where(['admin_id'=> $data['admin_id']])->getField('is_admin')) {
                    $this->error("不能编辑超级管理员");
                }
            }
            try {
                $this->service->edit($data);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('操作成功', U('index'));
        } else {
            $admin_id = I('get.admin_id', 0, 'intval');
            $info = [];
            if ($admin_id > 0) { 
                $info = $this->service->getAdminInfo($admin_id);
                if (empty($info)) {
                    $this->error('信息不存在');
                }
            }
            $role_list = Service('Access')->getRoleList();
            $this->assign('role_list', $role_list);
            $this->assign('info', $info);
            $this->display();
        }
    }

    // 删除
    public function delAction() { 
        $admin_id = I('get.admin_id', 0, 'intval');
        if (D('Admin')->where(['admin_id'=> $admin_id])->getField('is_admin')) {
            $this->error("不能删除超级管理员");
        }
        $ret = $this->service->del($admin_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }

    // 修改状态
    public function statusAction() { 
        $admin_id = I('get.admin_id', 0, 'intval');
        $status = I('get.status', 0, 'intval');
        if ($admin_id < 1 || !in_array($status, [1,2])) {
            $this->error('参数错误');
        }
        if (D('Admin')->where(['admin_id'=> $admin_id])->getField('is_admin')) {
            $this->error("不能修改超级管理员状态");
        }
        $ret = $this->service->setStatus($admin_id, $status);
        if (!$ret) {
            $this->error('操作失败');
        }
        $this->success('操作成功', U('index'));
    }
}