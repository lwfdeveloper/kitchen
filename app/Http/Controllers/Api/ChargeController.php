<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\ChargeService;
use Illuminate\Support\Facades\Redis;
use App\Lib\WeiXin\WeixinPay;

class ChargeController extends Controller
{
    /**
     * 充值下单service层
     * @var $chargeService
     */
    protected $chargeService;

    public function __construct(ChargeService $chargeService)
    {
        parent::__construct();
        $this->chargeService = $chargeService;
    }

    /**
     * 充值vip卡
     * @return mixed
     */
    public function rechargeVip()
    {
        $params = $this->request->only(['user_id', 'money','openid']);
        $rule = [
            'money' => 'required|integer',
            'user_id' => 'required|integer',
            'openid' => 'required|string'
        ];
        $this->apiCheckParams($params, $rule);

        if ($params['money'] <= 0){
            return Result(0,'金额不能小于0!');
        }
        $result = $this->chargeService->chargeMember($params);
        return Result(200,'success',$result);
    }

    /**
     * 微信小程序支付回调接口
     * @param string $target
     * return void
     */
    public function chargeCallback($target = 'sg_mp')
    {
        $printError = function ($msg = 'FAIL', $code = 500) {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $msg . ']]></return_msg></xml>';
            exit();
        };
        $printSuccess = function () {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            exit();
        };

        try {
            $postStr = file_get_contents('php://input');

            if (empty($postStr)) {
                return Result(0,'暂无回调数据!');
            }
            $result = $this->chargeService->checkWeixinCallback($postStr);

            switch ($result){
                case 200:
                    return $printSuccess();
                    break;
                case 41010:
                    return $printError('签名有误!');
                    break;
                case 21010:
                    return $printError('已处理该笔订单!');
                    break;
                case 22001:
                    return $printError('该笔订单正在处理中!');
                    break;
                case 500:
                    return $printError('系统出错!');
                    break;
                case 30201:
                    return $printError('支付回调数据有误!');
                    break;
                default :
                    return $printError('类型有误!');
            }
            return $printError('服务器内部错误!');
        }catch (\Exception $e){
            return $printError($e->getMessage());
        }
    }

    /**
     * 测试回调
     * @param string $target
     */
    public function chargeCallback_new($target = 'sg_mp')
    {

        $printError = function ($msg = 'FAIL', $code = 500) {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $msg . ']]></return_msg></xml>';
            exit();
        };
        $printSuccess = function () {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            exit();
        };

        try {
            $stamptime = time();
            $postStr = file_get_contents('php://input');

            if (empty($postStr)) {
                return Result(0,'暂无回调数据!');
            }

            if ($data['return_code'] != 'SUCCESS' || $data['result_code'] != 'SUCCESS') return Result(0,'支付回调失败！');
            // 签名认证
            $sign = $data['sign'];
            unset($data['sign']);
            $pay = new WeixinPay;
            $localSign = $pay->Sign($data);

            if ($sign != $localSign) {
                return $printError('签名不正确!');
            }
            // 检查是否已经处理
            $order_no = $data['out_trade_no'];
            $cacheKey = 'laravel_mp_'.$order_no;

            $cacheValue = Redis::get($cacheKey);
            if ($cacheValue !== false) {
                if ($cacheValue == 2) {
                    // 已经处理这笔订单
                    return $printSuccess();
                } else {
                    return $printError('订单正在处理中');
                }
            } else {
                // 标记订单为处理中
                Redis::set($cacheKey, 1, 60);
            }
            $buildSql = function ()use ($order_no){
                $db = Db::name('order');
                $db = $db->where('transactionId', $order_no);
                return $db;
            };
            //修改数据库中订单状态变更为已支付订单
            $order = $buildSql()->find();
            if (empty($order)){
                return $printError('订单不存在');
            }
            if ($order['pay_status'] != 0) {
                return $printSuccess();
            }

            $total_balance = $order['total_balance'] / 100;
            $update = [
                'pay_status' => 1,
                'pay_time' => $stamptime,
                'update_time' => $stamptime,
                'last_time' => 0
            ];
            //医生用户订单页面付款，则不计算积分
            if($order['is_doctor'] == 1){
                $update = [
                    'pay_status' => 1,
                    'pay_time' => $stamptime,
                    'update_time' => $stamptime,
                    'last_time' => 0,
                    'deduction_total_balance' => 0
                ];
            }
            $buildSql()->update($update);
            //修改订单商品表状态
            Db::name('order_goods')->where('order_id',$order['id'])->update([
                'order_type' => 2,
                'updated_time' => $stamptime
            ]);

            /**
             * 根据用户是否订阅消息发送对应的订阅消息
             */
            if($order['send_type'] == 1){
                $this->SendMsg($order);
            }

            if($help_id > 0 && $total_balance >= 10){
                DB::name('help_order_record')->where('id',$help_id)->update([
                    'is_pay' => 1,
                    'updated_time' => time(),
                    'pay_time' => time()
                ]);
                //记录医生积分
                $this->recordDoctorPoins($order['member_id'],$help_id,$total_balance);
            }
            $redis->set($cacheKey, 2, 600);
            return $printSuccess();
        }catch (\Exception $e){
            if (isset($cacheValue)) {
                Redis::set($cacheKey, null);
            }
            return $printError('系统出错!');
        }
    }

}
