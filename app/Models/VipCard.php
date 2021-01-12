<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class VipCard
{
    protected $table = 'vip_card';
    const STATUS_SUCCESS = 1; //启用

    /**
     * 获取vip充值卡数据
     * @return array
     */
    public function queryByList()
    {
        $list = DB::table('vip_card')->select('id','vip_name as name','money','card_img_url as url')->where('status',self::STATUS_SUCCESS)->get()->toArray();
        return $list;
    }



}