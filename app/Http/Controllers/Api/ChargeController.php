<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\ChargeService;

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

}
