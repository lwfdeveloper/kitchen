<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class HelpOrderRecord
{
    /** 医生开单记录 */
    protected $table = 'help_order_record';

    protected static $status = 1; //0-待确认1-已确认开单
    protected static $isPay = 0; //是否已支付完1-是0-否2-待支付

    /**
     * 查询是否有医生为您开单
     * @param $user_id
     */
    public function queryByExistDoctorFind($user_id,$sort = 'desc')
    {
        $data = DB::table("$this->table as t1")
                ->leftJoin('member as t2','t2.id','=','t1.doctor_id')
                ->where([
                    't1.user_id' => $user_id,
                    't1.status' => self::$status,
                    't1.is_pay' => self::$isPay
                ])
                ->select('t1.id as h_id','t1.doctor_id','t2.realname')
                ->orderBy('t1.id',$sort)
                ->first();

        return $data;
    }
}
