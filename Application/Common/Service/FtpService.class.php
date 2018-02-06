<?php
namespace Common\Service;
use Common\Basic\CsException;

class FtpService{
	private $conn_id;
	public function __construct() {
		$host = C('FTP_HOST');
		$user = C('FTP_USER');
		$pwd = C('FTP_PWD');
		$ret = $this->conn_id = ftp_connect($host);
		ftp_login($this->conn_id, $user, $pwd);
	}

	public function __destruct() {
		ftp_close($con);
	}

	/**
	 * desc 读取FTP的CSV数据
	 * @param $ftp_path    FTP的CSV文件路径
	 * @param $field_list  输出字段数组
	 * @param $pk          主键（可选）
	 */
	public function readCsv($ftp_path, $field_list, $pk = "") {
		$path = UPLOAD_PATH.'csv';
		if (!file_exists($path)) {
			@mkdir($path, 0777);
		}
		$file = $path . '/' . uniqid().'.csv';
		$ret = ftp_get($this->conn_id, $file, $ftp_path, FTP_ASCII);
		if (!$ret) {
			return false;
		}
        $handle = fopen($file, 'r');  
        $list = [];
        $row = 0;
        while ($data = fgetcsv($handle)) {
        	if ($row++ > 0) {
        		$i = 0;
        		$info = [];
        		foreach ($field_list as $field) {
        			// $data[$i] = iconv('gb2312', 'utf-8', $data[$i]); 
        			$info[$field] = $data[$i++];
        		}
        		empty($pk) ? $list[] = $info : $list[$info[$pk]] = $info;
        	}
        }
        Service('Import')->addLog(['file_name'=> $ftp_path, 'add_time'=> NOW_TIME]);
        return $list;  
	}

	/**
	 * desc 写入FTP的CSV数据
	 * @param $ftp_path    FTP的CSV文件路径
	 * @param $data    	   CSV内容数据数组
	 */
	public function writeCsv($ftp_path, $data) {
		$path = UPLOAD_PATH.'csv';
		if (!file_exists($path)) {
			@mkdir($path, 0777);
		}
		$file = $path . '/' . uniqid().'.csv';
		$content = [];
		foreach ($data as $key => $value) {
			$row = implode(',', $value);
			// $row = iconv('utf-8', 'gb2312', $row); 
			$content[] = $row; 
		}
		$content = implode("\n", $content);
		file_put_contents($file, $content);

		$fp = fopen($file, "r");
		return ftp_fput($this->conn_id, $ftp_path, $fp, FTP_ASCII);
	}

	/**
	 * desc 返回指定目录的文件列表
	 * @param $dir_name    目录
	 */
	public function fileList($dir_name) {
		return ftp_nlist($this->conn_id, $dir_name);
	}
}