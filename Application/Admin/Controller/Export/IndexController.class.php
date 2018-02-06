<?php
namespace Admin\Controller\Export;
use Admin\Controller\FController;
use Common\Basic\Pager;

class IndexController extends FController {
    private $service;
	public function _initialize(){
		parent::_initialize();
        $this->service = Service('Export');
    }
	
    // 列表
    public function indexAction(){
        $month = $this->getLastMonth();
        $this->assign('month', $month);
        if (!$this->service->isExist($month)) {
            $list = Service('Rule')->getList();
            $this->assign('list', $list);
            $this->display('rule');
        } else {
            $param = I('get.');
            $param['pagesize'] = 10;
            $param['month'] = $month;
            $result = $this->service->getMonthList($param);
            $pager = new Pager($result['count'], $param['pagesize']);
            $this->assign('list', $result['list']);
            $this->assign('pager', $pager->show());
            $this->assign('get',$param);
            $this->display();
        }
    }

    // 设置默认天数
    public function addDefaultDaysAction() {
        $days = I('post.days');
        $month = $this->getLastMonth();
        $all_days = date("t", strtotime($month));//总天数
        if (empty($days) || !is_array($days) || Service('Rule')->getCount() != count($days)) {
            $this->error('参数错误', U('index'));
        }
        if ($this->service->isExist($month)) {
            $this->error('请勿重复设置', U('index'));
        }
        $ruleList = Service('Rule')->getList();
        $arr = [];
        foreach ($ruleList as $key => $value) {
            $day = isset($days[$key]) ? (int)$days[$key] : 0;
            if ($day < 0) {
                $this->error('设置天数不能小于0');
            }
            if ($day > $all_days) {
                $this->error('设置天数不能大于'.$all_days);
            }
            $arr[$value['rule_id']] = $day;
        }
        $begin_time = strtotime($month);
        $end_time = strtotime($month) + 86400 * $all_days;
        $userList = M('User')->where("add_time < {$end_time} and (is_delete = 0 or (is_delete = 1 and delete_time > {$begin_time}))")
            ->field('user_id,user_no,real_name,rule_id')->select();
        $signService = Service('Sign');
        foreach ($userList as $key => $value) {
            $actual_days = $signService->getActualDays($value['user_id'], $month);
            $over_days = $actual_days > $arr[$value['rule_id']] ? $actual_days - $arr[$value['rule_id']] : 0;
            $data = [
                'user_id'=> $value['user_id'],
                'user_no'=> $value['user_no'],
                'real_name'=> $value['real_name'],
                'month'=> $month,
                'all_days'=> $all_days,
                'should_days'=> $arr[$value['rule_id']],
                'actual_days'=> $actual_days,
                'over_days'=> $over_days,
            ];
            $this->service->add($data);
        }
        $this->success('操作成功', U('index'));
    }

    // 修改
    public function editAction() { 
        if (IS_POST) {
            $data['id'] = I('post.id', 0, 'intval');
            $data['should_days'] = I('post.should_days', 0, 'intval');
            if (empty($data['id']) || $data['should_days'] < 0) {
                $this->error('设置天数不能小于0');
            }
            $info = $this->service->getInfo($data['id']);
            if (empty($info)) {
                $this->error('记录不存在');
            }
            if ($data['should_days'] > $info['all_days']) {
                $this->error('设置天数不能大于'.$info['all_days']);
            }
            $over_days = $info['actual_days'] > $data['should_days'] ? $info['actual_days'] - $data['should_days'] : 0;
            $data['over_days'] = $over_days;
            $ret = $this->service->edit($data);
            if ($ret === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功', U('index'));
        } else {
            $id = I('get.id', 0, 'intval');
            $info = $this->service->getInfo($id);
            if (empty($info)) {
                $this->error('记录不存在');
            }
            $this->assign('info', $info);
            $this->display();
        }
    }

    // 导出到FTP
    public function exportFtpAction() { 
        $month = $this->getLastMonth();
        $xlsData = $this->service->getMonthAllList($month);
        $xlsCell  = array(
            'user_no'=> '员工',
            'real_name'=> '姓名',
            'month'=> '月份',
            'all_days'=> '当月日历天数',
            'should_days'=> '应勤天数',
            'actual_days'=> '实勤天数',
            'over_days'=> '加班天数',
        );

        $data = [];
        $info = [];
        foreach ($xlsCell as $key => $value) {
            $info[] = $value;
        }
        $data[] = $info;
        foreach ($xlsData as $key => $value) {
            $info = [];
            foreach ($xlsCell as $k => $v) {
                $info[] = $value[$k];
            }
            $data[] = $info;
        }
        $ret = Service('Ftp')->writeCsv("huiyi/attendance".date('YmdHi').".csv", $data);
        if (!$ret) {
            $this->error('导出失败');
        }
        // 添加导出日志
        $this->service->addLog([
            'admin_id'=> session('uid'),
            'month'=> $month,
            'type'=> 2,
        ]);
        $this->success('导出成功');
    }

    // 导出到本地
    public function exportAction() { 
        $month = $this->getLastMonth();
        $list = $this->service->getMonthAllList($month);
        $xlsName  = "attendance".date('YmdHi');
        $xlsCell  = array(
            array('user_no','员工'),
            array('real_name','姓名'),
            array('month','月份'),
            array('all_days','当月日历天数'),
            array('should_days','应勤天数'),
            array('actual_days','实勤天数'),
            array('over_days','加班天数'),
        );
        $xlsData = $list;
        // 添加导出日志
        if (!M('export_log')->where(['type'=>1,'month'=>$month,'admin_id'=>session('uid'),'add_time'=>['egt', NOW_TIME - 2]])->count()) {
            $this->service->addLog([
                'admin_id'=> session('uid'),
                'month'=> $month,
                'type'=> 1,
            ]);
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
    }

    // 导出日志
    public function exportlogAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = $this->service->getLogList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    // 导入日志
    public function importlogAction(){
        $param = I('get.');
        $param['pagesize'] = 10;
        $result = Service('Import')->getLogList($param);
        $pager = new Pager($result['count'], $param['pagesize']);
        $this->assign('list', $result['list']);
        $this->assign('pager', $pager->show());
        $this->assign('get',$param);
        $this->display();
    }

    //上一个月
    private function getLastMonth() {
        $month = date('Y-m', NOW_TIME);
        $month = date('Y-m', strtotime("$month -1 month"));
        return $month;
    }
}