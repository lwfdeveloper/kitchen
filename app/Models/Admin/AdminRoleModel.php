<?php

namespace App\Models\Admin;

use Illuminate\Support\Facades\DB;

class AdminRoleModel
{
    protected $table = 'admin_role';

    /**
     * 获取角色列表
     * @param int $pageSize
     * @param string $desc
     * @return mixed
     */
    public function queryByList($pageSize = 10,$desc = 'asc')
    {
        $data = DB::table('admin_role')
                ->select('role_id','role_name','description','platform_id','create_uid','created_time')
                ->orderBy('role_id',$desc)
                ->paginate($pageSize)->toArray();
        return $data;
    }
}