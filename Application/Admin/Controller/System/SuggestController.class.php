<?php
namespace Admin\Controller\System;
use Admin\Controller\FController;
use Common\Basic\Pager;

class SuggestController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Suggest');
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

     // 删除
    public function delAction() { 
        $suggest_id = I('get.suggest_id', 0, 'intval');
        $ret = $this->service->del($suggest_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}