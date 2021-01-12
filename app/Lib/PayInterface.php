<?php
namespace App\Lib;

/**
 * Interface PayInterface
 * @package App\Lib
 */
interface PayInterface
{
    /**
     * 创建订单
     * @return mixed
     */
    public function createOrder($trade_no, $reason, $openid, $money);

}
