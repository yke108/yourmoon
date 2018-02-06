<?php
namespace Admin\Controller\System;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IosController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Config');
    }
	
    // 苹果版本控制
    public function indexAction(){
        if (IS_POST) {
            $ios_latest_version = I('post.ios_latest_version');
            $ios_minimum_version = I('post.ios_minimum_version');
            $ios_update_info = I('post.ios_update_info');
            $ios_download_url = I('post.ios_download_url');
            if (empty($ios_latest_version) || empty($ios_minimum_version) || empty($ios_update_info) || empty($ios_download_url)) {
                $this->error('参数错误');
            }
            $this->service->edit(['config_key'=> 'ios_latest_version', 'config_value'=> $ios_latest_version]);
            $this->service->edit(['config_key'=> 'ios_minimum_version', 'config_value'=> $ios_minimum_version]);
            $this->service->edit(['config_key'=> 'ios_update_info', 'config_value'=> $ios_update_info]);
            $this->service->edit(['config_key'=> 'ios_download_url', 'config_value'=> $ios_download_url]);
            $this->success('操作成功', U('index'));
        } else {
            $ios_latest_version = $this->service->getConfig('ios_latest_version');
            $ios_minimum_version = $this->service->getConfig('ios_minimum_version');
            $ios_update_info = $this->service->getConfig('ios_update_info');
            $ios_download_url = $this->service->getConfig('ios_download_url');
            $this->assign('ios_latest_version', $ios_latest_version);
            $this->assign('ios_minimum_version', $ios_minimum_version);
            $this->assign('ios_update_info', $ios_update_info);
            $this->assign('ios_download_url', $ios_download_url);
            $this->display();
        }
    }
}