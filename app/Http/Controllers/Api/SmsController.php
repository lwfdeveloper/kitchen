<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\SmsService;

class SmsController extends Controller
{
    /**
     * 消息service层
     * @var $menuService
     */
    private $smsService;

    public function __construct(SmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    /**
     * 发送手机短信验证码
     * @return mixed
     */
    public function sendSms()
    {
        $params = $this->request->only(['mobile','sign','type']);
        $rule = [
            'mobile' => 'required|mobile',
            'sign' => 'required|string',
            'type' => 'string'
        ];
        $this->apiCheckParams($params, $rule);

        if (!isset($params['type'])){
            $params['type'] = 'login';
        }
        $result = $this->smsService->sendSms($params);
        return Result(200,'success',$result);
    }
}
