<?php
namespace Common\Model;
use Think\Model;

class AdminModel extends Model{
	protected $tableName = 'admin';

	protected $_validate = array(     
		array('username','require','登录帐号不能为空'),
		array('username','','登录帐号已经存在', 0 ,'unique'), 
		array('admin_name','require','姓名不能为空'),
		array('role_ids','require','角色不能为空'),
	);

	public function password($password, $salt) {
		return md5(md5($password) . $salt);
	}
}