<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\MemberService;
use App\Service\JwtAuth;

class UserController extends Controller
{
    /**
     * 用户service层
     * @var $memberService
     */
    protected $memberService;

    public function __construct(MemberService $memberService)
    {
        parent::__construct();
        $this->memberService = $memberService;
    }

    /**
     * 测试
     * @return mixed
     */
    public function index()
    {
//        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImZvbyI6ImJhciJ9.eyJpc3MiOiJodHRwczpcL1wvc2NmLWFwaS5teW1lYWx3ZWxsLmNuIiwiYXVkIjoiaHR0cHM6XC9cL3NjZi1hcGkubXltZWFsd2VsbC5jbiIsImp0aSI6IjJtRThkWmtWIiwiaWF0IjoxNjEwMDg4ODU4LCJleHAiOjE2MTAwOTYwNTgsInVzZXJfaWQiOjF9.eKRdv04jwCcq8YvqDTTJEuDyFLT8d6z7YHn_pXHlgv0';
////        $token1 = JwtAuth::parseToken($token);
//        $valiToken = JwtAuth::validateToken($token);
//        $result = $valiToken->getData();
////        if ($result->code != 200){
////            return response()->json($result);
////        }
//        return response()->json($result);
////        var_dump($token1->getClaim('user_id'));die;
//
//        $token = JwtAuth::createToken(1);
//        var_dump($token);die;
        $params = $this->request->only(['code', 'mobile']);
        $rule = [
            'mobile' => 'required|string',
            'code' => 'required|integer',
        ];
        $this->apiCheckParams($params, $rule);
        $result = $this->memberService->getUserInfo($params);
        return Result(200,'success',$result);

    }

    /**
     * 用户登录
     * @return mixed
     */
    public function login()
    {
        $params = $this->request->only(['code', 'mobile']);
        $rule = [
            'mobile' => 'required|string',
            'code' => 'required|integer',
        ];
       $this->apiCheckParams($params, $rule);
        $result = $this->memberService->getUserInfo($params);
        return Result(200,'success',$result);
    }
}
