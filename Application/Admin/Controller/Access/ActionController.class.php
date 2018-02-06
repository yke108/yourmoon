<?php
namespace Admin\Controller\Access;
use Admin\Controller\FController;
use Common\Basic\CsException;

class ActionController extends FController {
	private $service;
	public function _initialize(){
		parent::_initialize();
		$this->service = service('Access');
	}
	
    public function indexAction(){
    	$list = $this->service->actionList();
		$this->assign('list', $list);
		$this->display();
    }
	
	public function editAction(){
		if(IS_POST){
			$params = I('post.');
			try {
				$result = $this->service->editAction($params);
			} catch (CsException $e) {
				$this->error($e->getMessage());
			}
			$this->success('操作成功');
		} else {
			$action_id = I('get.action_id', 0, 'intval');
			$info = $this->service->getAction($action_id);
			$top_action_list = $this->service->getTopActionList();
			$this->assign('info', $info);
			$this->assign('top_action_list', $top_action_list);
			$this->display();
		}
	}
	
	public function delAction(){
		$action_id = I('get.action_id', 0, 'intval');
		$info = $this->service->getAction($action_id);
		if(empty($info)) $this->error('内容不存在');
		try {
			$result = $this->service->actionDelete($action_id);
		} catch (CsException $e) {
			$this->error($e->getMessage());
		}
		$this->success('删除成功', U('index'));
	}
}