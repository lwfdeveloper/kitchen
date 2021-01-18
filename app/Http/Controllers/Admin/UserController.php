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


    /**
     * 统计本年本月/近七天/半个月注册人数
     * @param $year 年份
     * @param $month 月份
     * @param $status 1-近七天2-近半月
     * @return mixed
     */
    public function getUserStatistics()
    {
        $params = $this->request->only(['year','month' ,'status']);
        $rule = [
            'year' => 'string|digits_between:4,4',
            'month' => 'digits_between:1,2|string',
            'status' => 'string'
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->adminUserService->getUserStatistics($params);
        return Result(200,'success',$result);
    }
}
