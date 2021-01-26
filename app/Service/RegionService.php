<?php
namespace App\Service;

use Illuminate\Support\Facades\Redis;
use App\Models\Region as RegionModel;

class RegionService
{
    protected $regionModel;
    protected $list = [];

    public function __construct(RegionModel $regionModel)
    {
        $this->regionModel = $regionModel;
    }
    /**
     * 获取地址信息
     * @param array $params
     */
    public function getRegionList(array $params)
    {
        $result = Redis::get('region');
        if(!empty($result) && !isset($params['father_code'])){
            $result = json_decode($result,true);
        }else{
            $result = $this->regionModel->queryByList($params);
            $result = $this->regionChilds($result);
            if (isset($result) && !isset($params['father_code'])){
                Redis::set('region',json_encode($result));
            }
        }
        return $result;
    }

    /**
     * 递归实现地址三级联动
     * @param $data
     * @param int $pid
     * @param int $level
     */
    private function regionChilds($data , $pid = 0 , $level = 1)
    {
        $list = $this->list;
        foreach ($data as $k => $item){
            if($item->parent == $pid){
                $item->level = $level;
                $result = $this->regionChilds($data,$item->value,$level + 1);
                if (!empty($result)){
                    $item->children = $result;
                }
                $list[] = $item;
            }
        }
        return $list;
    }
}
