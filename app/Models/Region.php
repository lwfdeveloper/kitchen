<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Region
{
    protected $table = 'region';

    /**
     * 获取地址列表
     * @param $params
     */
    public function queryByList($params)
    {
        $data = DB::table('region')
                    ->select('code as value','name as label','father_code as parent')
                    ->when(isset($params['father_code']) ?? false,function ($query)use($params){
                        return  $query->where('father_code',$params['father_code']);
                    })
                    ->get();
        return $data->toArray();
    }
}
