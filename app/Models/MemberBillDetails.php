<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class MemberBillDetails
{
    protected $table = 'member_bill_details';

    /** 用户解除开方关系0-未1-是 */
    public  $isRelievArr = [
        'relieve' => 1,
        'notReleased' => 0
    ];
    private static $status = 0; //是否接受：0-未
    private static $acceptStatus = 1;//是否接：1-已接受
    private static $refuseStatus = 2;//是否接：2-拒绝

    /**
     * 查询是否有用户请医生开单
     * @param $doctor_id
     * return array
     */
    public function queryByExistUserFind($doctor_id,$sort = 'desc')
    {
        $data = DB::table("$this->table as t1")
                ->leftJoin('member as t2','t2.id','=','t1.user_id')
                ->where([
                    't1.doctor_id' => $doctor_id,
                    't1.status' => self::$status
                ])
                ->select('t1.id as bill_id','t1.doctor_id','t1.user_id','t2.realname')
                ->orderBy('t1.id',$sort)
                ->first();

        return $data;
    }


    /**
     * 查询是否有医生拒绝用户的邀请开单
     * @param $user_id
     * @param string $sort
     */
    public function queryByRefuseFind($user_id,$sort = 'desc')
    {
        $data = DB::table("$this->table as t1")
                ->leftJoin('member as t2','t2.id','=','t1.doctor_id')
                ->where([
                    't1.user_id' => $user_id
                ])
                ->select('t1.*','t2.realname')
                ->orderBy('t1.id',$sort)
                ->first();

        return $data;
    }
}
