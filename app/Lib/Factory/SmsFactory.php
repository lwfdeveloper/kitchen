<?php
namespace App\Lib\Factory;

use App\Lib\SMS\AliSms;

class SmsFactory
{
    public static function factory($payType = 'aliyun')
    {
        switch ($payType) {
            case 'aliyun':
                return new AliSms;
            case 'ztsms': break;
            default :
                return Result(0,'type error');
        }
    }
}