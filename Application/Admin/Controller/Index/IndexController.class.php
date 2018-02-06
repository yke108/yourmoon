<?php
namespace Admin\Controller\Index;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IndexController extends FController {
    public function _initialize(){
        parent::_initialize();
    }

    // 该控制器不需要判断权限
    protected function purviewCheck($priv_str = '') {
        return;
    }
    
    // 列表
    public function indexAction(){
        $this->display();
    }

    // 修改自已密码
    public function pwdAction() { 
        if (IS_POST) {
            $oldpassword = trim(I('post.oldpassword'));
            $repassword = trim(I('post.repassword'));
            $data['password'] = trim(I('post.password'));
            $data['admin_id'] = session('uid');
            if (empty($oldpassword) || empty($repassword) || empty($data['password'])) {
                $this->error('参数不完整');
            }
            if ($data['password'] != $repassword) {
                $this->error('两次输入的密码不一致，请重新输入');
            }
            $info = Service('Admin')->getAdminInfo($data['admin_id']);
            if (D('Admin')->password($oldpassword, $info['salt']) != $info['password']) {
                $this->error('原密码不正确');
            }
            try {
                Service('Admin')->edit($data);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('操作成功', U('index'));
        } else {
            $this->sbset(['in'=>'index', 'ac'=>'index_index_index']);
            $this->display();
        }
    }
}