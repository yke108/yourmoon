<?php
namespace Common\Model;
use Think\Model;

class SignDetailModel extends Model{
	protected $tableName = 'sign_detail';

	protected $_validate = array(     
		array('image','require','相片不能为空'),
		array('address','require','地址不能为空'),
		array('latitude','require','纬度不能为空'),
		array('latitude','checkLatitude','纬度格式不正确',0,'callback'),
		array('longitude','require','经度不能为空'),
		array('longitude','checkLongitude','经度格式不正确',0,'callback'),
	);

	// 验证纬度格式
	protected function checkLatitude($latitude) {
		if (!is_float($latitude)) {
			return false;
		}
		if ($latitude < -90 || $latitude > 90) {
			return false;
		}
		return true;
	}

	// 验证经度格式
	protected function checkLongitude($longitude) {
		if (!is_float($longitude)) {
			return false;
		}
		if ($longitude < -180 || $longitude > 180) {
			return false;
		}
		return true;
	}

}