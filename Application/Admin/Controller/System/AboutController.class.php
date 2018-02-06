<?php
namespace Admin\Controller\System;
use Admin\Controller\FController;
use Common\Basic\Pager;

class AboutController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Config');
    }
	
    // 关于我们
    public function indexAction(){
        if (IS_POST) {
            $company_name = I('post.company_name');
            $company_desc = I('post.company_desc');
            if (empty($company_name) || empty($company_desc)) {
                $this->error('参数错误');
            }
            $this->service->edit(['config_key'=> 'company_name', 'config_value'=> $company_name]);
            $this->service->edit(['config_key'=> 'company_desc', 'config_value'=> $company_desc]);
            $this->success('操作成功', U('index'));
        } else {
            $company_name = $this->service->getConfig('company_name');
            $company_desc = $this->service->getConfig('company_desc');
            $this->assign('company_name', $company_name);
            $this->assign('company_desc', $company_desc);
            $this->display();
        }
    }
}