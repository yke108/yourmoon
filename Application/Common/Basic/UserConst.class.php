<?php
namespace Common\Basic;

class UserConst{
	const PASS_STATUE = 1;// 正常
	const FREEZE_STATUE = 2;// 已冻结

	static $statusList = [
		SELF::PASS_STATUE 	=> '正常',
		SELF::FREEZE_STATUE 	=> '已冻结',
	];
}