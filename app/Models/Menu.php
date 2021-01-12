<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Menu
{
    protected $table = 'recipes_group';
    const STATUS_SUCCESS = 0; //显示

    /**
     * 获取食谱分类数据
     * @return array
     */
    public function queryByList()
    {
        $list = DB::table('recipes_group')->select('id','name','gourp_id','img_url as url')->where('status',self::STATUS_SUCCESS)->get()->toArray();
        return $list;
    }



}