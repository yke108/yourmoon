<?php
namespace Admin\Controller\System;
use Admin\Controller\FController;
use Common\Basic\Pager;

class ConfigController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Config');
    }
	
    // 设置配置
    public function indexAction(){
        if (IS_POST) {
            $location_range = I('post.location_range', 0, 'intval');
            if (empty($location_range)) {
                $this->error('参数错误');
            }
            $this->service->edit(['config_key'=> 'location_range', 'config_value'=> $location_range]);
            $this->success('操作成功', U('index'));
        } else {
            $location_range = $this->service->getConfig('location_range');
            $this->assign('location_range', $location_range);
            $this->display();
        }
    }
}