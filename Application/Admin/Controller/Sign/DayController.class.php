<?php
namespace Admin\Controller\Sign;
use Admin\Controller\FController;
use Common\Basic\Pager;

class DayController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Sign');
    }
	
    // 列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getDayList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    // 详情
    public function detailAction() { 
        $sign_id = I('get.sign_id', 0, 'intval');
        $info = $this->service->getDayInfo($sign_id);
        if (empty($info)) {
            $this->error('打卡信息不存在');
        }
        $ret = $this->service->getDetailList(['sign_id'=> $sign_id, 'pagesize'=> 1000]);
        $info['list'] = $ret['list'];
        $this->assign('info', $info);
        $this->display();
    }
}