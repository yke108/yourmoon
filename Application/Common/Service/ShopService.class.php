<?php
namespace Common\Service;
use Common\Basic\CsException;
use Common\Basic\ShopConst;

class ShopService{

	private $model;

	public function __construct() {
		$this->model = D('Shop');
	}

	// 获取列表
	public function getList($param) {
		$param['p'] < 1 && $param['p'] = 1;
		$param['pagesize'] < 1 && $param['pagesize'] = 10;

		$where = ['is_delete'=> 0];
		if (isset($param['shop_name']) && !empty($param['shop_name'])) {
			$where['shop_name'] = ['like', "%{$param['shop_name']}%"];
		}
		$count = $this->model->where($where)->count();
		$field = isset($param['field']) ? $param['field'] : '*';
		$list = $this->model->where($where)->field($field)->page($param['p'], $param['pagesize'])->order('shop_id desc')->select();
		$list = $list ? $list : [];
		foreach ($list as $key => $value) {
			isset($value['region_code']) && $list[$key]['region_name'] = D('Region')->getRegionName($value['region_code']);
		}
		return array(
			'list' => $list,
			'count' => $count ? $count : 0,
		);
	}

	// 获取详情
	public function getInfo($shop_id, $byField = "shop_id", $field = "*") {
		$info = $this->model->where([$byField=> $shop_id])->field($field)->find();
		isset($info['region_code']) && $info['region_name'] = D('Region')->getRegionName($info['region_code']);
		return $info ? $info : [];
	}

	// 编辑
	public function edit($data) {
		if (empty($data['shop_name']) || empty($data['shop_no'])) {
			return false;
		}
		if (isset($data['shop_id']) && $data['shop_id'] > 0) {
			$ret = $this->model->where(['shop_id'=> $data['shop_id']])->save($data);
			return $ret !== false;
		} else {
			$data['add_time'] = time();
			return $this->model->add($data);
		}
	}

	// 删除
	public function del($shop_id){
		return $this->model->where(['shop_id'=> $shop_id])->save(['is_delete'=> 1]);
	}

	// 获取所有店铺编号
	public function getAllShopNo() {
		$ret = $this->model->getField('shop_no,1');
		return $ret ? $ret : [];
	}

	// 根据店铺编号删除用户
	public function delByShopNo($shop_no_list) {
		return $this->model->where(['shop_no'=> ['in', $shop_no_list]])->save(['is_delete'=> 1, 'delete_time'=> NOW_TIME]);
	}

	// 更新
	public function updateShop($data) {
		return $this->model->where(['shop_no'=> $data['shop_no']])->save($data);
	}

	// 添加
	public function addShop($data) {
		$data['add_time'] = NOW_TIME;
		return $this->model->add($data);
	}
}