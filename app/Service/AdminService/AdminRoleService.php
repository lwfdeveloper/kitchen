<?php

namespace App\Service\AdminService;

use Illuminate\Support\Facades\Redis;
use App\Models\Admin\AdminRoleModel;

class AdminRoleService
{
    protected $adminRoleModel;
    public function __construct(AdminRoleModel $adminRoleModel)
    {
        $this->adminRoleModel = $adminRoleModel;
    }

    /**
     * 获取角色列表
     * @param array $params
     */
    public function getRoleList(array $params)
    {
        $data = $this->adminRoleModel->queryByList($params);

        $result = [
            'data' => $data['data'] ?? [],
            'total' => $data['total'] ?? 0
        ];

        return $result;
    }




}
