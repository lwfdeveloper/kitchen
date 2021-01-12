<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\MenuService;

class MenuController extends Controller
{
    /**
     * 食谱分类service层
     * @var $menuService
     */
    protected $menuService;

    public function __construct(MenuService $MenuService)
    {
        parent::__construct();
        $this->menuService = $MenuService;
    }

    /**
     * 获取食谱分类列表集合
     * @return mixed
     */
    public function getList()
    {
       $result = $this->menuService->getMenuList();
       return Result(200,'获取列表成功',$result);
    }
}
