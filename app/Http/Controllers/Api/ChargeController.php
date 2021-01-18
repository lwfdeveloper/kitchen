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

}
