<?php
namespace App\Lib;

/**
 * Interface RabbitmqInterface
 * @package App\Lib
 */
interface RabbitmqInterface
{
    /**
     * 创建通道
     * @return mixed
     */
    public function connectionAmqpCHannel();

    /**
     * 创建交换机
     * @return mixed
     */
    public function connectionAmqpExchange($exchange);
}
