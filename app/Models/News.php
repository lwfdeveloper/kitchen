<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class News
{
    protected $table = 'news';
    protected static $state = 1; //是否启用1-启用0-停用
    protected static $homeStatus = 1; //首页启用-1 ，不启用- 0
    /**
     * 根据solor_id获取文章
     * @param int $solor_id
     * @return array
     */
    public function queryBySolorIdFind($solor_id,$sort = 'desc')
    {
        $data = DB::table($this->table)->where(['solor_id' => $solor_id,'state' => self::$state])->orderBy('id',$sort)->first();
        return $data;
    }

    /**
     * 获取最新一篇文章
     * @param string $sort
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function queryByFind($sort = 'desc')
    {
        $data = DB::table($this->table)->where(['home_type' => self::$homeStatus,'state' => self::$state])->orderBy('id',$sort)->first();
        return $data;
    }
}
