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
        $adminUser = DB::table('admin_user')
            ->leftJoin('admin_group','admin_group.group_id','=','admin_user.group_id')
            ->leftJoin('admin_role','admin_role.role_id','=','admin_user.role_id')
            ->where(['admin_user.id' => $userId])
            ->select('admin_user.id','admin_user.username','admin_user.account','admin_user.group_id',
                'admin_user.role_id','admin_user.remark','admin_user.last_login_time','admin_user.created_time',
                'admin_user.sex','admin_group.group_name','admin_role.role_name')
            ->first();
        return $adminUser;
    }
}