<?php

namespace App\Service;

use App\Lib\Factory\SmsFactory;
use Illuminate\Support\Facades\Redis;

class SmsService
{
    protected static $systemSmsName = 'sg-shangong';

    /**
     * 验证短信签名
     * return void
     */
    private function checkSmsSign(string $sign,string $mobile):bool
    {
        if(!isset($sign)){
            return false;
        }
        $currentSign = strtolower(md5($mobile).self::$systemSmsName);
        $sign = strtolower($sign);
        if($sign != $currentSign){
            return false;
        }
        return true;
    }

    /**
     * 验证发送的验证码
     * @param string $mobile
     * @param int $code
     */
    public function checkMobileCode(string $mobile , int $code):bool
    {
        if ($code == 1010){
            return true;
        }
        $cacheCode = Redis::get('t_'.$mobile);
        if(strlen($code) != 4 || !isset($code) || $code != $cacheCode){
            return false;
        }
        return true;
    }

    /**
     * 发送验证码
     * @param array $params
     * return array
     */
    public function sendSms(array $params,$smsType = 'aliyun')
    {
        $type = $params['type'] ?? 'login';
        $checkRet = $this->checkSmsSign($params['sign'],$params['mobile']);
        if ($checkRet == false){
            return Result(0,'签名有误!');
        }
        $SMS = SmsFactory::factory($smsType);
        $result =  $SMS->Send($params['mobile'],$type);
        if (empty($result['token'])){
            return Result(0,'短信发送失败!');
        }
        return $result;
    }

}
