<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Order
{
    protected $table = 'order';

    /**
     * 根据微信商户订单号获取单个订单数据
     * @return array
     */
    public function queryByTranFind($transactionId)
    {
        $orderData = DB::table('order')
                ->select('id','transactionId','pay_status','total_balance','reason','pay_time','user_id','created_time')
                ->where('transactionId',$transactionId)
                ->first();
        return $orderData;
    }


    /**
     * 根据订单id更新支付状态
     * @param $id
     */
    public function updateOrder($id)
    {
        $row = DB::table('order')
            ->where('id',$id)
            ->update(['pay_status' => 1 ,'pay_time' => time()]);
        return $row;
    }



}