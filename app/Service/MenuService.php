<?php

namespace App\Service;

use Illuminate\Support\Facades\Redis;
use App\Models\Menu as MenuModel;

class MenuService
{
    protected $menuModel;
    protected $response = [];
    const imgPath = 'http://img.mymealwell.cn'; //oss映射图片域名地址
    const TESR_ID = 24; //测试使用

    public function __construct(MenuModel $menuModel)
    {
        $this->menuModel = $menuModel;
    }

    /**
     * 获取食谱分类列表
     * @return mixed
     */
    public function getMenuList()
    {
        $list = $this->menuModel->queryByList();
        //热门分类   菜式  场景
        $hotCategArr = [];$dishesArr = [];$sceneArr = [];

        foreach ($list as $k => $value){
            $value->url = self::imgPath.$value->url;
            $value->id == self::TESR_ID ? $value->status = 1 : $value->status = 0;
            switch ($value->gourp_id){
                case 1:
                    $hotCategArr[] = $value;
                    break;
                case 2:
                    $dishesArr[] = $value;
                    break;
                case 3:
                    $sceneArr[] = $value;
                    break;
                default :
                    return Result(0,'暂无其他分类!');
            }
        }
        $list = [
            'hot' => $hotCategArr,
            'dishes' => $dishesArr,
            'scene' => $sceneArr
        ];

        $this->response = $list;
        return $this->response;
    }





}
