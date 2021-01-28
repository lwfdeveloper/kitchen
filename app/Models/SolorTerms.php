<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class SolorTerms
{
    protected $table = 'solor_terms';

    /**
     * 根据月份获取节气列表
     * @param $month
     */
    public function queryByMonthlist($month = 1)
    {
        $data = DB::table($this->table)->where('month',$month)->get()->toArray();
        return $data;
    }
}
