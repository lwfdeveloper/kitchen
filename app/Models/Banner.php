<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Banner
{
    protected $table = 'member';
    protected static $state = 1; //是否启用1-启动0-停用
    protected static $status = 1; //是否删除1-未删0-已删除
    protected static $type = 1; //轮播图使用场景1-首页2-其他页面

    /**
     * 获取banner列表
     * @param string $sort
     * @return array
     */
    public function queryBylist($sort = 'desc')
    {
        $data = DB::table('banner_img')->where([
            'type' => self::$type,
            'state' => self::$state,
            'status' => self::$status
        ])->orderBy('id',$sort)->get()->toArray();

        return $data;
    }

}
