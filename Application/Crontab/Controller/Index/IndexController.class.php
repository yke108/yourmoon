<?php
namespace Crontab\Controller\Index;
use Crontab\Controller\FController;

class IndexController extends FController {

    const dirname = 'flexfile';

    private $ftpService;
    private $userService;
    private $shopService;

	public function _initialize(){
		parent::_initialize();
        $this->ftpService = Service('Ftp');
        $this->userService = Service('User');
        $this->shopService = Service('Shop');

    }
	
    // 0 0 * * * php crontab/index.php index/index/index
    public function indexAction(){
        $file_list = $this->ftpService->fileList(self::dirname);
        /****************************************同步员工******************************************/
        $ftp_path = $this->getNewestFileName(self::dirname.'/APPin', $file_list);
        $field_list = ['user_no','real_name','department','up_no','rule_id','rule_name','phone','is_delete','delete_time'];
        $pk = 'user_no';
        // 远程数据
        $ftp_list = $this->ftpService->readCsv($ftp_path, $field_list, $pk);
        // 本地数据
        $local_list = $this->userService->getAllUserNo();
        // 添加或者更新
        foreach ($ftp_list as $user_no => $value) {
            $value['shop_no'] = '';
            $value['is_delete'] =  $value['is_delete'] == '在职' ? 0 : 1;
            $value['delete_time'] =  empty($value['delete_time']) ? 0 : strtotime($value['delete_time']);
            if (isset($local_list[$user_no])) {
                $this->userService->updateUser($value);
            } else {
                $this->userService->addUser($value);
            }
        }
        unset($ftp_list, $local_list);

        /****************************************同步店铺和员工******************************************/
        $ftp_path = $this->getNewestFileName(self::dirname.'/APPout', $file_list);
        $field_list = ['user_no','real_name','shop_no','shop_name','department','up_no','up_real_name','rule_id','rule_name','phone','is_delete','delete_time'];
        $pk = 'user_no';
        // 远程数据
        $ftp_list = $this->ftpService->readCsv($ftp_path, $field_list, $pk);
        // 本地数据
        $local_list = $this->userService->getAllUserNo();
        // 添加或者更新
        foreach ($ftp_list as $user_no => $value) {
            $value['is_delete'] =  $value['is_delete'] == '在职' ? 0 : 1;
            $value['delete_time'] =  empty($value['delete_time']) ? 0 : strtotime($value['delete_time']);
            if (isset($local_list[$user_no])) {
                $this->userService->updateUser($value);
            } else {
                $this->userService->addUser($value);
            }
        }

        // 本地数据
        $local_list = $this->shopService->getAllShopNo();
        $shop_list = [];
        foreach ($ftp_list as $user_no => $value) {
            if (!empty($value['shop_name']) && !empty($value['shop_no']) && !isset($shop_list[$value['shop_no']])) {
                $shop_list[$value['shop_no']] = [
                    'shop_no'=> $value['shop_no'],
                    'shop_name'=> $value['shop_name'],
                ];
            }
        }
        // 添加门店
        foreach ($shop_list as $key => $value) {
           $this->shopService->addShop($value);
        }
        unset($ftp_list, $local_list, $shop_list);
        exit;
    }

    /**
     * desc 获取最新文件名
     * @param $pre 文件前缀
     * @param $file_list 文件列表
     */
    private function getNewestFileName($pre, $file_list) {
        $res = '';
        foreach ($file_list as $key => $value) {
            if (strpos($value, $pre) === 0 && end(explode('.', $value)) == 'csv') {
                if (empty($res) || $res < $value) {
                    $res = $value;
                }
            }
        }
        return $res;
    }
}