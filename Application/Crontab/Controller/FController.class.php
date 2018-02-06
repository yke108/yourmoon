<?php
namespace Crontab\Controller;
use Think\Controller;

class FController extends Controller {
	// 限制只能用CLI模式访问
	public function _initialize(){
		if (!IS_CLI) {
			// exit;
		}
    }
}