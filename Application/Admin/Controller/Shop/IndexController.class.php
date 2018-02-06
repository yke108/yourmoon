<?php
namespace Admin\Controller\Shop;
use Admin\Controller\FController;
use Common\Basic\Pager;
use Common\Basic\ShopConst;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Shop');
    }
	
    // 列表
    public function indexAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    // 添加或修改
    public function editAction() { 
        if (IS_POST) {
            $data['shop_id'] = I('post.shop_id', 0, 'intval');
            $data['shop_no'] = trim(I('post.shop_no'));
            $data['shop_name'] = trim(I('post.shop_name'));
            $data['region_code'] = I('post.region_code');
            $data['address'] = I('post.address');
            $data['summary'] = I('post.summary');
            $lnglat = I('post.lnglat');
            list($data['longitude'], $data['latitude']) = explode(',', $lnglat);
            $ret = $this->service->edit($data);
            if ($ret === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功', U('index'));
        } else {
            $this->sbset(['in'=>'shop', 'ac'=>'shop_index_index']);
            $shop_id = I('get.shop_id', 0, 'intval');
            $info = [];
            if ($shop_id > 0) { 
                $info = $this->service->getInfo($shop_id);
                if (empty($info)) {
                    $this->error('门店不存在');
                }
            }
            // 添加时默认一个北京的经纬度
            if (!isset($info['latitude']) || $info['latitude'] == 0) {
                $info['latitude'] = '39.904725';
                $info['longitude'] = '116.407215';
            }
            $region_list = Service('Region')->getAllRegions(false);
            $this->assign('info', $info);
            $this->assign('region_list', $region_list);
            $this->display();
        }
    }

    // 删除
    public function delAction() { 
        $shop_id = I('get.shop_id', 0, 'intval');
        $ret = $this->service->del($shop_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}