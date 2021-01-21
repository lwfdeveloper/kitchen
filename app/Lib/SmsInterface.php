<?php
namespace App\Lib;

/**
 * Interface SmsInterface
 * @package App\Lib
 */
interface SmsInterface
{
    /**
     * @param $mobile   手机号码
     * @param $type     发送场景
     * @return mixed
     */
    public function Send($mobile,$type);
}
