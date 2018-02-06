<?php
namespace Common\Model;
use Think\Model;

class UserModel extends Model{
	protected $tableName = 'user';

	protected $_validate = array(     
		array('real_name','require','姓名不能为空！'),
		array('phone','require','手机不能为空！'),
		array('password','require','密码不能为空！', 0,'regex',1),
		array('phone','','手机号已经存在',0,'unique'), 
		array('phone','/^1\d{10}$/','手机格式不正确！',0,'regex'),
		array('user_no','require','员工编号不能为空！'),
		array('user_no','require','员工编号已经存在！'),
		array('up_no','checkUpNo','上级不存在！',0,'callback'),
		array('shop_no','checkShop','门店不存在！',0,'callback'),
		array('rule_id','checkRule','岗位不存在！',0,'callback'),
	);

	protected function checkShop($shop_no) {
		$ret = D('Shop')->where(['shop_no'=> $shop_no, 'is_delete'=> 0])->count();
		return empty($ret) ? false : true;
	}

	protected function checkUpNo($up_no) {
		if (empty($up_no)) {
			return true;
		}
		$ret = $this->where(['user_no'=> $up_no, 'is_delete'=> 0])->count();
		return empty($ret) ? false : true;
	}

	protected function checkRule($rule_id) {
		$ret = D('SignRule')->where(['rule_id'=> $rule_id, 'is_delete'=> 0])->count();
		return empty($ret) ? false : true;
	}

	// password md5
	// salt 随机字符串
	public function createPassword($password, $salt) {
		return md5($password.$salt);
	}
}