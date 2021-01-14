<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;


class UserController extends Controller
{
    /**
     * 用户service层
     * @var $memberService
     */
    protected $memberService;

//    public function __construct(MemberService $memberService)
//    {
//        parent::__construct();
//        $this->memberService = $memberService;
//    }

    /**
     * 管理员用户登录
     * @return mixed
     */
    public function userLogin()
    {
        $result = [
            'id' => 1,
            'msg' => '测试admin路由'
        ];
        return Result(200,'success',$result);
    }
}
