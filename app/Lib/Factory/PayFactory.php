<?php
namespace App\Lib\Factory;

use App\Lib\WeiXin\WeixinPay;

class PayFactory
{
    public static function factory($payType,$config = 'sgt_mp')
    {
        switch ($payType) {
            case 'weixin':
                return new WeixinPay($config);
                break;
            case 'alipay':
            //return new AliPay();
                break;
            default :
                return Result(0,'type error');
        }
    }
}