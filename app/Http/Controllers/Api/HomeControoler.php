<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\{HomeService,SolorService,BillingService};

class HomeControoler extends Controller
{
    /**
     * 首页service层
     * @var $homeService
     */
    protected $homeService;

    /**
     * 节气service层
     * @var SolorService
     */
    protected $solorService;

    /**
     * 开单相关service层
     * @var SolorService
     */
    protected $billingService;

    public function __construct(HomeService $homeService,SolorService $solorService,BillingService $billingService)
    {
        parent::__construct();
        $this->homeService = $homeService;
        $this->solorService = $solorService;
        $this->billingService = $billingService;
    }

    /**
     * 获取首页banner图列表
     * return void
     */
    public function getHomeBannerImgList()
    {
        $result = $this->homeService->getHomeBannerList();
        return Result(200,'success',$result);
    }

    /**
     * 首页滚动消息内容
     * @param $user_id
     */
    public function scrollingMessage()
    {
        $params = [];
        $user_id = $this->request->input('user_id',0);

        if (!empty($user_id)){
            $params['user_id'] = $user_id;
            $result = $this->billingService->checkExistBillBill($params);
        }

        if (empty($user_id) || empty($result)){
            $result = $this->solorService->getSolorList();
        }

        return Result(200,'success',$result);
    }
}
