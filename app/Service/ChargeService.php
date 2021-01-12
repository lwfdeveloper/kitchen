<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;
use App\Lib\WeiXin\WeixinPay;
use App\Models\VipCard as VipCardModel;
use App\Models\User as UserModel;

class ChargeService
{
    protected $weixinPay;
    protected $vipCardModel;
    protected $userModel;

    public function __construct(VipCardModel $vipCardModel,UserModel $userModel,WeixinPay $weixinPay)
    {
        $this->weixinPay = $weixinPay;
        $this->vipCardModel = $vipCardModel;
        $this->userModel = $userModel;
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
                $vipMoenyList = array_column($list,'money');
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

            $result = $this->weixinPay->CreateOrder($trade_no,$reason,$openid,$params['money']);

            return $result;
        }catch (\Exception $exception){
            return Result(0,$exception->getMessage());
        }
    }




}
