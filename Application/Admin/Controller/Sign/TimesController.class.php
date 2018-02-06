<?php
namespace Admin\Controller\Sign;
use Admin\Controller\FController;
use Common\Basic\Pager;

class TimesController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Sign');
    }
	
    // 列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getDetailList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        // 打卡点列表
        $ret = Service('Shop')->getList(['pagesize'=> 1000]);
        $shop_list = $ret['list'];
        $this->assign('list', $result['list']);
        $this->assign('shop_list', $shop_list);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    // 详情
    public function detailAction() { 
        $detail_id = I('get.detail_id', 0, 'intval');
        $info = $this->service->getDetailInfo($detail_id);
        if (empty($info)) {
            $this->error('打卡信息不存在');
        }
        // print_r($info);exit;
        $this->assign('info', $info);
        $this->display();
    }
}