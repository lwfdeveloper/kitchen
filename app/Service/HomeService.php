<?php
namespace App\Service;

use App\Models\Banner as BannerModel;

class HomeService
{
    protected $bannerModel;

    public function __construct(BannerModel $bannerModel)
    {
        $this->bannerModel = $bannerModel;
    }

    /**
     * 获取首页banner
     */
    public function getHomeBannerList()
    {
        $list = $this->bannerModel->queryBylist();

        return $list;
    }
}
