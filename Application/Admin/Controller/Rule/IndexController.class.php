<?php
namespace Admin\Controller\Rule;
use Admin\Controller\FController;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Rule');
    }
	
    // 列表
    public function indexAction(){
        $list = $this->service->getList();
        $this->assign('list', $list);
        $this->display();
    }

    // 添加或修改
    public function editAction() {
        if (IS_POST) {
            $rule_id = I('post.rule_id', 0, 'intval');
            $rule_name = I('post.rule_name');
            $min_hour = I('post.min_hour', 0, 'floatval');
            $instruction = I('post.instruction');
            $days = I('post.days');
            $times = I('post.times');
            if (empty($rule_name) || empty($instruction)) {
                $this->error('参数错误');
            }
            // 自定义字段
            $fields = [];
            if (!empty($times)) {
                for ($i = 0; $i < count($times['start']); $i++) {
                    if ($times['time'][$i]) {
                        if (!strtotime($times['end'][$i]) || !strtotime($times['start'][$i])) {
                            $this->error('时间格式不正确');
                        }
                        $times['end'][$i] = date('H:i', strtotime($times['end'][$i]));
                        $times['start'][$i] = date('H:i', strtotime($times['start'][$i]));
                        if ($times['end'][$i] <= $times['start'][$i]) {
                            $this->error('结束时间必须大于开始时间');
                        }
                    } else {
                        $times['end'][$i] = $times['start'][$i] = '00:00';
                    }
                    $fields[] = [
                        'time'=> $times['time'][$i],
                        'start'=> $times['start'][$i],
                        'end'=> $times['end'][$i],
                        'location'=> $times['location'][$i],
                    ];
                }
            }
            // 添加或者更新的数据
            $data = [
                'rule_id'=> $rule_id,
                'rule_name'=> $rule_name,
                'min_hour'=> $min_hour,
                'instruction'=> $instruction,
                'days'=> implode(',', $days),
                'times'=> json_encode($fields),
            ];
            $ret = $this->service->edit($data);
            if ($ret === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功', U('index'));
        } else {
            $rule_id = I('get.rule_id', 0, 'intval');
            $info = [];
            if ($rule_id > 0) { 
                $info = $this->service->getInfo($rule_id);
                if (empty($info)) {
                    $this->error('规则不存在');
                }
            }
            $this->assign('info', $info);
            $this->display();
        }
    }

    // 删除
    public function delAction() { 
        $rule_id = I('get.rule_id', 0, 'intval');
        $ret = $this->service->del($rule_id);
        if ($ret === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功', U('index'));
    }
}