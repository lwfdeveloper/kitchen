<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;
use App\Models\VipCard as VipCardModel;
use App\Models\Order as OrderModel;
use App\Models\User as UserModel;
use App\Lib\Factory\PayFactory;

class ChargeService
{
    protected $vipCardModel;
    protected $userModel;
    protected $orderModel;
    const GIGN_ERROR=41010; //签名有误
    const ORDER_PROCESSED = 21010; //已处理该订单
    const IN_PROCESSED=22001; //处理中
    const SERVICE_ERROR=500;
    public static $orderSuccess = 200;
    public static $orderExisit = 31021; //订单不存在
    public static $returnCallbackDataError = 30201;

    public function __construct(VipCardModel $vipCardModel,UserModel $userModel,OrderModel $orderModel)
    {
        $this->vipCardModel = $vipCardModel;
        $this->userModel = $userModel;
        $this->orderModel = $orderModel;
    }

    /**
     * 充值会员卡
     * @param array $params
     */
    public function chargeMember(array $params)
    {
        try {
            $vipMoenyList = [];
            //验证充值的金额
            $list = $this->vipCardModel->queryByList();
            if (isset($list)){
                $eqList = array_map(function ($item){
                    $item->money =  $item->money * 100;
                    return $item;
                },$list);
                $vipMoenyList = array_column($eqList,'money');
            }else{
                return Result(0,'暂无启用的充值会员卡!');
            }
            if (!in_array($params['money'],$vipMoenyList)){
                return Result(0,'充值金额有误!');
            }
            $user = $this->userModel->queryByIdOne($params['user_id']);

            if (!isset($user)){
                return Result(0,'该用户不存在，充值失败!');
            }
            $trade_no = getWeixinTradeNo();
            $order_id = GetOrderNo($user->user_id);
            $reason = "用户{$user->user_id}支付订单:{$order_id}";
            $openid = $params['openid'] ?? '';

            $payFactory = PayFactory::factory('weixin');
            $result = $payFactory->CreateOrder($trade_no,$reason,$openid,$params['money']);

            return $result;
        }catch (\Exception $exception){
            return Result(0,$exception->getMessage());
        }
    }


    /**
     * 验证微信小程序支付回调数据
     * @param string $callbackData
     * @param string $payType 支付类型
     * return string
     */
    public function checkWeixinCallback(string $callbackData,$payType = 'weixin')
    {
        try {
            $xml = simplexml_load_string($callbackData, 'SimpleXMLElement', LIBXML_NOCDATA);
            $data = json_decode(json_encode($xml), true);
            //测试写入redis
            Redis::set('pay_ceshi_laravel',json_encode($data));

            if ($data['return_code'] != 'SUCCESS' || $data['result_code'] != 'SUCCESS') return self::$returnCallbackDataError;

            $sign = $data['sign'];
            unset($data['sign']);

            $payFactory = PayFactory::factory($payType);
            $localSign = $payFactory->Sign($data);

            if ($sign != $localSign) {
                return self::GIGN_ERROR;
            }

            // 检查是否已经处理
            $order_no = $data['out_trade_no'];
            $cacheKey = 'laravel_mp_'.$order_no;

            $cacheValue = Redis::get($cacheKey);
            if ($cacheValue !== false) {
                if ($cacheValue == 2) {
                    // 已经处理这笔订单
                    return self::ORDER_PROCESSED;
                } else {
                    return self::IN_PROCESSED;
                }
            } else {
                // 标记订单为处理中
                Redis::set($cacheKey, 1, 60);
            }

            /** 更新支付状态等操作 */
            $orderData = $this->orderModel->queryByTranFind($order_no);
            if (!isset($orderData)) return self::$orderExisit;
            //更新订单支付状态
            $this->orderModel->updateOrder($orderData->id);
            //更新redis中的订单完成状态
            Redis::set($cacheKey, 2, 600);
            return self::$orderSuccess;
        }catch (\Exception $exception){
            return self::SERVICE_ERROR;
        }
    }

}
