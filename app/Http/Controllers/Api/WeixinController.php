<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\WeixinService;

class WeixinController extends Controller
{
    /**
     * 微信service层
     * @var $weixinService
     */
    protected $weixinService;

    public function __construct(WeixinService $weixinService)
    {
        parent::__construct();
        $this->weixinService = $weixinService;
    }

    /**
     * 创建微信小程序码
     * @return mixed
     */
    public function createQrcode()
    {
        $params = $this->request->only(['user_id']);
        $rule = [
            'user_id' => 'required|integer',
        ];
        $this->apiCheckParams($params, $rule);
        $params['width'] = 430;
        $params['scene'] = 'referee_id='.$params['user_id'];
        $params['path'] = 'pages/index/index';
        $result = $this->weixinService->createWeixinQrcode($params);
        return Result(200,'success',$result);
    }
}
