<?php
namespace Admin\Controller\System;
use Admin\Controller\FController;
use Common\Basic\Pager;

class AndroidController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Config');
    }
	
    // 安卓版本控制
    public function indexAction(){
        if (IS_POST) {
            $android_latest_version = I('post.android_latest_version');
            $android_minimum_version = I('post.android_minimum_version');
            $android_update_info = I('post.android_update_info');
            $android_download_url = I('post.android_download_url');
            if (empty($android_latest_version) || empty($android_minimum_version) || empty($android_update_info) || empty($android_download_url)) {
                $this->error('参数错误');
            }
            $this->service->edit(['config_key'=> 'android_latest_version', 'config_value'=> $android_latest_version]);
            $this->service->edit(['config_key'=> 'android_minimum_version', 'config_value'=> $android_minimum_version]);
            $this->service->edit(['config_key'=> 'android_update_info', 'config_value'=> $android_update_info]);
            $this->service->edit(['config_key'=> 'android_download_url', 'config_value'=> $android_download_url]);
            $this->success('操作成功', U('index'));
        } else {
            $android_latest_version = $this->service->getConfig('android_latest_version');
            $android_minimum_version = $this->service->getConfig('android_minimum_version');
            $android_update_info = $this->service->getConfig('android_update_info');
            $android_download_url = $this->service->getConfig('android_download_url');
            $this->assign('android_latest_version', $android_latest_version);
            $this->assign('android_minimum_version', $android_minimum_version);
            $this->assign('android_update_info', $android_update_info);
            $this->assign('android_download_url', $android_download_url);
            $this->display();
        }
    }
}