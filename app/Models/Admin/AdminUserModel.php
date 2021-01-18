<?php

namespace App\Models\Admin;

use Illuminate\Support\Facades\DB;

class AdminUserModel
{
    protected $table = 'admin_user';
    const STATUS_SUCCESS = 1; //启用
    const IS_DEL = 0; //未删除


    /**
     * 根据管理员用户密码查询
     * @param string $account
     * @param string $password
     * return array
     */
    public function queryAccountByFind(string $account,string $password)
    {
        $adminUser = DB::table('admin_user')
                    ->select('id','username','account','group_id','role_id','remark','last_login_time','created_time','sex','status')
                    ->where([ 'account' => $account , 'password' => $password ])
                    ->first();
        return $adminUser;
    }


    /**
     * 获取后台管理员相关信息
     * ps：所属组织部门，角色等
     * @param $userId
     * return array
     */
    public function getUserInfo($userId)
    {
        $adminUser = DB::table('admin_user as t1')
            ->leftJoin('admin_group as t2','t2.group_id','=','t1.group_id')
            ->leftJoin('admin_role as t3','t3.role_id','=','t1.role_id')
            ->where(['t1.id' => $userId])
            ->select('t1.id','t1.username','t1.account','t1.group_id',
                't1.role_id','t1.remark','t1.last_login_time','t1.created_time',
                't1.sex','t2.group_name','t3.role_name')
            ->first();
        return $adminUser;
    }


    /**
     * 统计注册人数
     * @param $params
     */
    public function queryByCountRegister($params)
    {
        $buildSql = function ()use ($params){
            $db = DB::table('member')
                ->select(DB::raw('COUNT(`id`) AS `total`'),DB::raw('DAY(`created_time`) AS `day`'))
                ->groupBy(DB::raw('DAY(`created_time`)'))
                ->when(isset($params['year']) ?? false,function ($query)use($params){
                    return $query->whereYear('created_time',$params['year']);
                })
                ->when(isset($params['month']) ?? false ,function ($query)use($params){
                    return $query->whereMonth('created_time',$params['month']);
                })->when(isset($params['date']) ?? false , function ($query)use($params){
                    return $query->where('created_time', '>=',$params['date'])->where('created_time','<=',date('Y-m-d'));
                });
            return $db;
        };
        return $buildSql()->get()->toArray();
    }
}