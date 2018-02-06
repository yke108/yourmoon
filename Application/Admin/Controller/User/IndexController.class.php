<?php
namespace Admin\Controller\User;
use Admin\Controller\FController;
use Common\Basic\Pager;
use Common\Basic\UserConst;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('User');
    }

    // 员工列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $param['status'] = ['in', [UserConst::PASS_STATUE, UserConst::FREEZE_STATUE]];
        $result = $this->service->getList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get', $param);
        $this->display('index');
    }

    // 改变状态
    public function statusAction() { 
        $user_id = I('get.user_id', 0, 'intval');
        $status = I('get.status', 0, 'intval');
        $ret = $this->service->updateStatus($user_id, $status);
        if ($ret === false) {
            $this->error('操作失败');
        }
        $this->success('操作成功', U('index'));
    }

    // 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['user_id'] = I('post.user_id', 0, 'intval');
            $data['real_name'] = I('post.real_name');
            $data['phone'] = I('post.phone');
            $data['avatar'] = I('post.avatar');
            $data['password'] = I('post.password');
            $data['user_no'] = I('post.user_no');
            $data['shop_no'] = I('post.shop_no');
            $data['up_no'] = I('post.up_no');
            $data['rule_id'] = I('post.rule_id', 0, 'intval');
            $data['department'] = I('post.department');
            !empty($data['password']) && $data['password'] = md5($data['password']);
            try {
                $user_id = $this->service->edit($data);
            } catch(\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('操作成功', U('index'));
        } else {
            $user_id = I('get.user_id', 0, 'intval');
            $url = I('get.url');
            $info = [];
            if ($user_id > 0) { 
                $info = $this->service->getInfo($user_id);
                if (empty($info)) {
                    $this->error('员工不存在');
                }
            }
            // 门店列表
            $ret = Service('Shop')->getList(['pagesize'=> 10000, 'field'=> 'shop_no,shop_name']);
            $shopList = $ret['list'];
            // 岗位列表
            $ruleList = Service('Rule')->getList(['field'=> 'rule_id,rule_name']);
            $this->assign('info', $info);
            $this->assign('shopList', $shopList);
            $this->assign('ruleList', $ruleList);
            $this->assign('url', $url);
            $this->display();
        }
    }

    // 删除
    public function delAction() {
        $user_id = I('get.user_id', 0, 'intval');
        $ret = $this->service->del($user_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}