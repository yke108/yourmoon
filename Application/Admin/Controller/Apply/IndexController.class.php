<?php
namespace Admin\Controller\Apply;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Apply');
    }
	
    // 列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getList($param);
        foreach ($result['list'] as $key => $value) {
            $userInfo = D('User')->where(['user_id'=> $value['user_id']])->field('shop_no,up_no')->find();
            $shop_name = '';
            if (!empty($userInfo['shop_no'])) {
                $shop_name = D('Shop')->where(['shop_no'=> $userInfo['shop_no']])->getField('shop_name');
            }
            $audit_name = '';
            if (!empty($userInfo['up_no'])) {
                $audit_name = D('User')->where(['user_no'=> $userInfo['up_no']])->getField('real_name');
            }
            $result['list'][$key]['shop_name'] = $shop_name;
            $result['list'][$key]['audit_name'] = $audit_name;
        }
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }
}