<?php
namespace Admin\Controller\Notice;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Notice');
    }
	
    // 列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    // 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['notice_id'] = I('post.notice_id', 0, 'intval');
            $data['title'] = I('post.title');
            $data['content'] = I('post.content');
            $data['admin_id'] = session('uid');
            if (empty($data['title']) || empty($data['content'])) {
                $this->error('标题或者内容不能为空');
            }
            $ret = $this->service->edit($data);
            if ($ret === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功', U('index'));
        } else {
            $notice_id = I('get.notice_id', 0, 'intval');
            $info = [];
            if ($notice_id > 0) { 
                $info = $this->service->getInfo($notice_id);
                if (empty($info)) {
                    $this->error('通知公告不存在');
                }
            }
            $this->assign('info', $info);
            $this->display();
        }
    }

    // 删除
    public function delAction() { 
        $notice_id = I('get.notice_id', 0, 'intval');
        $ret = $this->service->del($notice_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}