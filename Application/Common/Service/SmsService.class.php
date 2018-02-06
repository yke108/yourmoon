<?php
namespace Common\Service;
use Common\Basic\CsException;
// http://www.winic.org/api/SendMessage.asp
class SmsService{

    const UserRegister = 1;//用户注册
	const UserForgetPW = 2;//忘记密码

	public function send($phone, $verify_code, $type = 1){
		$content = '';
		if ($type == self::UserRegister) {
		    $content = "新员工注册验证码：".$verify_code."，请在10分钟内完成验证";
		} elseif ($type == self::UserForgetPW) {
            $content = "找回密码验证码：".$verify_code."，请在10分钟内完成验证";   
        }
		if (empty($content)) return false;
		$url="http://service.winic.org:8009/sys_port/gateway/index.asp?";
        $data = "id=%s&pwd=%s&to=%s&Content=%s&time=";
        $id = urlencode(iconv("utf-8","gb2312","letter2017"));
        $pwd = 'letter123456';
        $content = urlencode(iconv("UTF-8","GB2312",$content));
        $rdata = sprintf($data, $id, $pwd, $phone, $content);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        curl_close($ch);
        return explode('/', $result)[0] == '000';
	}
}
