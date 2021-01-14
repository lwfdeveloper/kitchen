<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\AdminService\AdminUserService;

class UserController extends Controller
{
    /**
     * 用户service层
     * @var $adminUserService
     */
    protected $adminUserService;

    public function __construct(AdminUserService $adminUserService)
    {
        parent::__construct();
        $this->adminUserService = $adminUserService;
    }

    /**
     * 管理员用户登录
     * @return mixed
     */
    public function userLogin()
    {
        $params = $this->request->only(['account', 'password']);
        $rule = [
            'account' => 'required|string',
            'password' => 'required|string',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->adminUserService->userLogin($params);
        return Result(200,'登录成功',$result);
    }
}
