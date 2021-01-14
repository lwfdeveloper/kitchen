<?php

namespace App\Models\Admin;

use Illuminate\Support\Facades\DB;

class AdminRoleMenuRelationModel
{
    protected $table = 'admin_role_menu_relation';
    public static $IS_SHOW=1;

    /**
     * 根据角色id获取菜单路由关联表数据
     * @param $role_id
     * return array
     */
    public function queryByList($role_id)
    {
        $data = DB::table('admin_role_menu_relation')
                ->leftJoin('admin_menu','admin_menu.menu_id','=','admin_role_menu_relation.menu_id')
                ->where(['admin_role_menu_relation.role_id' => $role_id ,'admin_menu.is_show' => self::$IS_SHOW])
                ->orderBy('admin_menu.sort','desc')
                ->select('admin_role_menu_relation.relation_id','admin_menu.menu_id','admin_menu.menu_name','admin_menu.parent_id','admin_menu.route','admin_menu.icon')
                ->get()->toArray();

        return $data;
    }

}