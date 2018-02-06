<?php
namespace Admin\Controller\Role;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Role');
    }
	
    // 列表
    public function indexAction(){
        $list = $this->service->getList();
        $list = genTree($list, 'role_id');
        $this->assign('list', $list);
        $this->display();
    }

    // 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['role_id'] = I('post.role_id', 0, 'intval');
            $data['rule_id'] = I('post.rule_id', 0, 'intval');
            $data['pid'] = I('post.pid', 0, 'intval');
            $data['role_name'] = I('post.role_name');
            if (empty($data['role_name'])) {
                $this->error('角色名称不能为空');
            }
            if ($data['rule_id'] < 1) {
                $this->error('必须选择规则');
            }
            $ret = $this->service->edit($data);
            if ($ret === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功', U('index'));
        } else {
            $role_id = I('get.role_id', 0, 'intval');
            $info = [];
            if ($role_id > 0) { 
                $info = $this->service->getInfo($role_id);
                if (empty($info)) {
                    $this->error('角色不存在');
                }
            }
            $role_list = $this->service->getList();
            $rule_list = Service('Rule')->getList(['pagesize'=>1000]);
            $this->assign('info', $info);
            $this->assign('role_list', $role_list);
            $this->assign('rule_list', $rule_list['list']);
            $this->display();
        }
    }

    // 删除
    public function delAction() { 
        $role_id = I('get.role_id', 0, 'intval');
        $ret = $this->service->del($role_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}