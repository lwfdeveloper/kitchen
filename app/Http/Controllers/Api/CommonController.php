<?php

namespace App\Http\Controllers\Api;

use App\Service\RegionService;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    /**
     * 地址service层
     * @var $regionService
     */
    protected $regionService;

    public function __construct(RegionService $regionService)
    {
        parent::__construct();
        $this->regionService = $regionService;
    }

    /**
     * 获取中国地址信息
     * return void
     */
    public function getRegionInfo()
    {
        $params = $this->request->only(['father_code']);
        $rule = [
            'father_code' => 'integer',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->regionService->getRegionList($params);
        return Result(200,'success',$result);
    }
}
