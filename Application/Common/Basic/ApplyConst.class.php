<?php
namespace Common\Basic;

class ApplyConst{
	const REVIEW_STATUE = 0;// 待审批
	const PASS_STATUE = 1;// 通过
	const FAIL_STATUE = 2;// 不通过

	static $statusList = [
		SELF::REVIEW_STATUE => '待审批',
		SELF::PASS_STATUE 	=> '通过',
		SELF::FAIL_STATUE 	=> '不通过',
	];
}