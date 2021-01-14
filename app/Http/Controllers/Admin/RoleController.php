<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\AdminService\AdminRoleService;

class RoleController extends Controller
{
    /**
     * 角色service层
     * @var $adminRoleService
     */
    protected $adminRoleService;

    public function __construct(AdminRoleService $adminRoleService)
    {
        parent::__construct();
        $this->adminRoleService = $adminRoleService;
    }

    /**
     * 获取角色列表
     * @return mixed
     */
    public function getRoleList()
    {
        $params = $this->request->only(['page']);
        $rule = [
            'page' => 'required|numeric|min:1|not_in:0',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->adminRoleService->getRoleList($params);
        return Result(200,'登录成功',$result);
    }
}
