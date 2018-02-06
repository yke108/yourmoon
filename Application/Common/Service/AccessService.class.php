<?php
namespace Common\Service;
use Common\Basic\CsException;

class AccessService{
	public function actionList(){
		$actionDao = $this->actionDao();
		$al = $actionDao->getActions();
		$list = [];
	    foreach($al as $key=>$val){
	    	$val['action_code_alias'] = str_replace('/', '_', $val['action_code']);
			if ($val['parent_id'] == 0){
				$list[$val['action_code']] = $val;
			} else {
				$parent_code = $al[$val['parent_id']]['action_code'];
				$list[$parent_code]['children'][$val['action_code']] = $val;
			}
		}
		return $list;
	}

	public function getAction($id){
		return $this->actionDao()->getRecord($id);
	}
	
	public function editAction($params){
		$actionDao = $this->actionDao();
		$actionDao->startTrans();
		$rules = array(
			 array('action_name','require','名称是必须的！'), 
			 array('action_code','require','菜单编码是必须的！'), 
			 array('action_code','','菜单编码已存在', 0 ,'unique'), 
		);
		$data = array(
			'action_name'=>trim($params['action_name']),
			'action_code'=>trim($params['action_code']),
			'parent_id'=>intval($params['parent_id']),
		);
		
		if($params['action_id'] > 0){
			$data['action_id'] = $params['action_id'];
		}
		if (!$actionDao->validate($rules)->create($data)){
			$actionDao->rollback();
			throw new CsException($actionDao->getError(), 400);
		}
		if ($params['action_id'] > 0){
			$result = $actionDao->saveRecord($data);
			if ($result === false){
				$actionDao->rollback();
				throw new CsException('修改失败', 400);
			}
		} else {
			$result = $actionDao->addRecord($data);
			if ($result < 1){
				$actionDao->rollback();
				throw new CsException('添加失败', 400);
			}
			$params['action_id'] = $result;
		}
		$actionDao->commit();
	}
	
	public function actionDelete($action_id){
		$map = array(
			'action_id'=>$action_id,
			'parent_id'=>$action_id,
			'_logic'=>'or',
		);
        $ret = $this->actionDao()->deleteRecord($map);
        if (!$ret) {
        	throw new CsException("删除失败", 400);
        }
		return true;
	}

	public function getTopActionList() {
		return $this->actionDao()->where(['parent_id'=> 0])->field('action_id,action_name')->select();
	}

	// 获取角色列表
	public function getRoleList() {
		$list = $this->roleDao()->select();
		return $list ? $list : [];
	}

	// 获取角色详情
	public function getRoleInfo($role_id) {
		$info = $this->roleDao()->where(['role_id'=> $role_id])->find();
		return $info ? $info : [];
	}

	// 编辑添加角色
	public function editRole($data){
		$roleDao = $this->roleDao();
		$roleDao->startTrans();
		$rules = array(
			 array('role_name','require','名称是必须的！'), 
			 array('role_name','','名称已存在', 0 ,'unique'), 
			 array('action_list','require','权限是必须的！'), 
		);
		$data = array(
			'role_id'=>$data['role_id'],
			'role_name'=>trim($data['role_name']),
			'action_list'=> empty($data['purview']) ? '' : implode(',', $data['purview']),
			'role_describe'=>trim($data['role_describe']),
		);
		if (!$roleDao->validate($rules)->create($data)){
			$roleDao->rollback();
			throw new CsException($roleDao->getError(), 400);
		}
		if ($data['role_id'] > 0){
			$result = $roleDao->save($data);
			if ($result === false){
				$roleDao->rollback();
				throw new CsException('修改失败', 400);
			}
		} else {
			$result = $roleDao->add($data);
			if ($result < 1){
				$roleDao->rollback();
				throw new CsException('添加失败', 400);
			}
			$data['role_id'] = $result;
		}
		$roleDao->commit();
	}

	public function roleDelete($role_id){
		$map = array(
			'role_id'=>$role_id,
		);
        $ret = $this->roleDao()->where($map)->delete();
        if (!$ret) {
        	throw new CsException("删除失败", 400);
        }
		return true;
	}

	// 获取权限列表
	public function getActionByRoleIds($role_ids){
		if (empty($role_ids)) {
			return '';
		}
		$action_list_arr = $this->roleDao()->where(['role_id'=> ['in', $role_ids]])->getField('action_list', true);
		if (empty($action_list_arr)) {
			return '';
		}
		$res = [];
		foreach ($action_list_arr as $action_list) {
			if (!empty($action_list)) {
				$res = array_merge($res, explode(',', $action_list));
			}
		}
		if (empty($res)) {
			return '';
		}
		return implode(',', array_unique($res));
	}

	private function actionDao(){
		return D('Common/AdminAction');
	}
	private function roleDao(){
		return D('Common/AdminRole');
	}
}
